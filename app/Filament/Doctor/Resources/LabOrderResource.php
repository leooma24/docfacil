<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Concerns\GatedByPlanFeature;
use App\Filament\Doctor\Resources\LabOrderResource\Pages;
use App\Models\Appointment;
use App\Models\LabOrder;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

/**
 * Laboratorio: la libreta de lo que se mandó a hacer (12-oct-2026).
 *
 * Lo que evita: "la paciente llega a su cita y la corona no está", y no saber
 * cuánto se le debe al laboratorio. No se conecta con el laboratorio ni le
 * manda nada: es la libreta del consultorio, bien hecha.
 */
class LabOrderResource extends Resource
{
    use GatedByPlanFeature;

    protected static ?string $model = LabOrder::class;

    protected static ?string $slug = 'laboratorio';

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?string $navigationLabel = 'Laboratorio';

    protected static ?string $modelLabel = 'Orden de laboratorio';

    protected static ?string $pluralModelLabel = 'Laboratorio';

    protected static ?int $navigationSort = 3;

    protected static function planFeature(): string
    {
        return 'laboratorio';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::clinicHasPlanFeature();
    }

    public static function canAccess(): bool
    {
        return static::clinicHasPlanFeature();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    /** Lo que no ha llegado, para el globito del menú. */
    public static function getNavigationBadge(): ?string
    {
        if (! static::clinicHasPlanFeature()) {
            return null;
        }
        $n = LabOrder::where('clinic_id', auth()->user()->clinic_id)->whereNull('llego_at')->count();

        return $n > 0 ? (string) $n : null;
    }

    public static function form(Form $form): Form
    {
        $clinicId = fn () => auth()->user()->clinic_id;

        return $form->schema([
            Forms\Components\Section::make('El trabajo')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('patient_id')
                        ->label('Paciente')
                        ->relationship('patient', 'first_name', fn ($query) => $query->where('clinic_id', auth()->user()->clinic_id))
                        ->getOptionLabelFromRecordUsing(fn (Patient $p) => trim("{$p->first_name} {$p->last_name}"))
                        ->searchable(['first_name', 'last_name'])
                        ->rule(fn () => Rule::exists('patients', 'id')->where('clinic_id', auth()->user()->clinic_id))
                        ->required()
                        ->live(),
                    Forms\Components\TextInput::make('trabajo')
                        ->label('¿Qué es?')
                        ->placeholder('Corona de zirconia')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('diente')->label('Diente')->placeholder('36')->maxLength(20),
                    Forms\Components\TextInput::make('color')->label('Color')->placeholder('A2')->maxLength(20),
                    Forms\Components\TextInput::make('laboratorio')
                        ->label('Laboratorio')
                        ->required()
                        ->maxLength(255)
                        ->datalist(fn () => LabOrder::where('clinic_id', auth()->user()->clinic_id)->distinct()->orderBy('laboratorio')->pluck('laboratorio')->all()),
                    Forms\Components\TextInput::make('costo')
                        ->label('Cuánto cobra el laboratorio')
                        ->numeric()->prefix('$')->minValue(0)->default(0)
                        ->visible(fn () => auth()->user()->veElDinero()),
                ]),
            Forms\Components\Section::make('Fechas')
                ->columns(3)
                ->schema([
                    Forms\Components\DatePicker::make('enviada_at')->label('Se mandó')->default(today())->native(false)->displayFormat('d/m/Y')->required(),
                    Forms\Components\DatePicker::make('prometida_para')->label('Prometieron regresarlo')->native(false)->displayFormat('d/m/Y'),
                    Forms\Components\Select::make('appointment_id')
                        ->label('Se coloca en la cita')
                        ->helperText('Si llega la fecha y no ha llegado, le avisa antes.')
                        ->options(fn (Forms\Get $get) => $get('patient_id')
                            ? Appointment::where('clinic_id', auth()->user()->clinic_id)
                                ->where('patient_id', $get('patient_id'))
                                ->where('starts_at', '>=', now()->startOfDay())
                                ->whereIn('status', ['scheduled', 'confirmed'])
                                ->orderBy('starts_at')->get()
                                ->mapWithKeys(fn ($c) => [$c->id => $c->starts_at->locale('es')->isoFormat('ddd D [de] MMM, HH:mm')])
                                ->all()
                            : [])
                        ->rule(fn () => Rule::exists('appointments', 'id')->where('clinic_id', auth()->user()->clinic_id)),
                ]),
            Forms\Components\Textarea::make('notas')->label('Notas')->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn (LabOrder $r) => trim($r->patient?->first_name . ' ' . $r->patient?->last_name))
                    ->url(fn (LabOrder $r) => \App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $r->patient_id], panel: 'doctor')),
                Tables\Columns\TextColumn::make('trabajo')
                    ->label('Trabajo')
                    ->description(fn (LabOrder $r) => collect([$r->diente ? "Diente {$r->diente}" : null, $r->color ? "Color {$r->color}" : null])->filter()->implode(' · ') ?: null),
                Tables\Columns\TextColumn::make('laboratorio')->label('Laboratorio'),
                Tables\Columns\TextColumn::make('enviada_at')->label('Se mandó')->date('d/m'),
                Tables\Columns\TextColumn::make('prometida_para')
                    ->label('Prometido')
                    ->date('d/m')
                    ->placeholder('—')
                    ->color(fn (LabOrder $r) => $r->atrasada() ? 'danger' : null)
                    ->description(fn (LabOrder $r) => $r->atrasada() ? 'Atrasado' : null),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->state(fn (LabOrder $r) => match ($r->estado()) { 'entregada' => 'Entregada', 'llego' => 'Ya llegó', default => 'Por llegar' })
                    ->badge()
                    ->color(fn ($state) => match ($state) { 'Entregada' => 'gray', 'Ya llegó' => 'success', default => 'warning' }),
                Tables\Columns\TextColumn::make('appointment.starts_at')
                    ->label('Cita')
                    ->formatStateUsing(fn (LabOrder $r) => $r->appointment?->starts_at?->locale('es')->isoFormat('ddd D/MM HH:mm'))
                    ->placeholder('—')
                    ->color(fn (LabOrder $r) => $r->enRiesgo() ? 'danger' : null)
                    ->description(fn (LabOrder $r) => $r->enRiesgo() ? 'No ha llegado' : null),
                Tables\Columns\TextColumn::make('costo')
                    ->label('Costo')
                    ->formatStateUsing(fn ($state, LabOrder $r) => '$' . number_format((float) $state, 0) . ($r->pagada_at ? ' · pagado' : ''))
                    ->visible(fn () => auth()->user()->veElDinero()),
            ])
            ->filters([
                // Pendiente es lo que no se le ha entregado al paciente: lo que no ha
                // llegado y lo que ya llegó y espera su cita.
                Tables\Filters\Filter::make('pendientes')->label('Pendientes (por llegar o por entregar)')->query(fn ($query) => $query->whereNull('entregada_at'))->default(),
                Tables\Filters\Filter::make('por_llegar')->label('Solo por llegar')->query(fn ($query) => $query->whereNull('llego_at')),
                Tables\Filters\Filter::make('por_pagar')->label('Por pagar')->query(fn ($query) => $query->whereNull('pagada_at'))
                    ->visible(fn () => auth()->user()->veElDinero()),
            ])
            ->actions([
                Tables\Actions\Action::make('llego')
                    ->label('Llegó')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LabOrder $r) => ! $r->llego_at)
                    ->action(fn (LabOrder $r) => $r->update(['llego_at' => now()]) && Notification::make()->title('Anotado: ya llegó')->success()->send()),
                Tables\Actions\Action::make('entregada')
                    ->label('Entregada')
                    ->icon('heroicon-o-hand-raised')
                    ->color('gray')
                    ->visible(fn (LabOrder $r) => $r->llego_at && ! $r->entregada_at)
                    ->action(fn (LabOrder $r) => $r->update(['entregada_at' => now()])),
                Tables\Actions\Action::make('pagada')
                    ->label('Pagada')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('Queda como gasto de laboratorio de hoy, y entra en el corte del mes.')
                    ->visible(fn (LabOrder $r) => ! $r->pagada_at && auth()->user()->veElDinero())
                    ->action(fn (LabOrder $r) => $r->marcarPagada()),
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->defaultSort('enviada_at', 'desc')
            ->emptyStateHeading('Nada por llegar del laboratorio')
            ->emptyStateDescription('Cuando mande una corona o un puente, anótelo aquí: le avisa si su cita llega y el trabajo no.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLabOrders::route('/'),
            'create' => Pages\CreateLabOrder::route('/create'),
            'edit' => Pages\EditLabOrder::route('/{record}/edit'),
        ];
    }
}
