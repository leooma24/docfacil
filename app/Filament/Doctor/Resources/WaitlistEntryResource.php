<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\WaitlistEntryResource\Pages;
use App\Models\Patient;
use App\Models\Service;
use App\Models\WaitlistEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Doctor\Concerns\SearchesPatientName;

class WaitlistEntryResource extends Resource
{
    use SearchesPatientName;

    protected static ?string $slug = 'lista-de-espera';

    protected static ?string $model = WaitlistEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Pacientes';

    protected static ?string $navigationLabel = 'Lista de espera';

    protected static ?string $modelLabel = 'Paciente en espera';

    protected static ?string $pluralModelLabel = 'Lista de espera';

    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->clinic?->hasFeature('waitlist');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del paciente en lista')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->label('Paciente')
                            ->relationship('patient')
                            ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('service_id')
                            ->label('Servicio (opcional)')
                            ->relationship('service', 'name')
                            ->preload()
                            ->helperText('Déjelo en blanco si le sirve cualquier servicio'),
                        Forms\Components\Select::make('doctor_id')
                            ->label('Doctor preferido (opcional)')
                            ->options(fn () => \App\Models\Doctor::where('clinic_id', auth()->user()->clinic_id)
                                ->with('user')->get()
                                ->mapWithKeys(fn ($d) => [$d->id => $d->user?->name ?? 'Doctor ' . $d->id]))
                            ->searchable()
                            ->helperText('Déjelo en blanco si le sirve cualquier doctor'),
                        Forms\Components\DatePicker::make('desired_from')
                            ->label('Disponible desde')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('desired_to')
                            ->label('Disponible hasta')
                            ->default(now()->addMonth())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->after('desired_from'),
                        Forms\Components\Select::make('priority')
                            ->label('Prioridad')
                            ->options([0 => 'Normal', 1 => 'Urgente'])
                            ->default(0)
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'waiting' => 'En espera',
                                'notified' => 'Notificado',
                                'booked' => 'Agendado',
                                'expired' => 'Expirado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->default('waiting')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.first_name')
                    // El nombre lleva al perfil del paciente: de cualquier lista se llega a todo lo suyo.
                    ->url(fn ($record) => \App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $record->patient_id], panel: 'doctor'))
                    ->color('primary')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient?->first_name} {$record->patient?->last_name}")
                    ->searchable(query: self::buscarPorNombreDePaciente()),
                Tables\Columns\TextColumn::make('service.name')
                    ->visibleFrom('md')
                    ->label('Servicio')
                    ->placeholder('Cualquiera'),
                Tables\Columns\TextColumn::make('desired_from')
                    ->visibleFrom('md')
                    ->label('Desde')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('desired_to')
                    ->visibleFrom('xl')
                    ->label('Hasta')
                    ->date('d/m/Y'),
                Tables\Columns\BadgeColumn::make('priority')
                    ->visibleFrom('md')
                    ->label('Prioridad')
                    ->formatStateUsing(fn ($state) => $state == 1 ? 'Urgente' : 'Normal')
                    ->colors(['danger' => fn ($state) => $state == 1, 'gray' => fn ($state) => $state == 0]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'waiting' => 'En espera',
                        'notified' => 'Notificado',
                        'booked' => 'Agendado',
                        'expired' => 'Expirado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'waiting',
                        'info' => 'notified',
                        'success' => 'booked',
                        'gray' => fn ($state) => in_array($state, ['expired', 'cancelled']),
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->visibleFrom('2xl')
                    ->label('Agregado')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'waiting' => 'En espera',
                        'notified' => 'Notificado',
                        'booked' => 'Agendado',
                        'expired' => 'Expirado',
                        'cancelled' => 'Cancelado',
                    ])
                    ->multiple()
                    // Al ofrecerle el lugar pasa a "Notificado": debe seguir a
                    // la vista para apartarle la cita cuando conteste.
                    ->default(['waiting', 'notified']),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('Ofrecer slot')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (WaitlistEntry $record) => !empty($record->patient?->phone) && $record->status === 'waiting')
                    ->form([
                        Forms\Components\DateTimePicker::make('slot_start')
                            ->label('Fecha y hora disponible')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minutesStep(15)
                            // Ofrecer un horario que ya pasó deja muy mal
                            // frente al paciente; el calendario abre en el
                            // mes actual y era facil picarle a un dia viejo.
                            ->minDate(now())
                            // Desde el aviso de una cancelación ya trae el hueco.
                            ->default(fn ($livewire) => method_exists($livewire, 'huecoCita') && $livewire->huecoCita()
                                ? $livewire->huecoCita()->starts_at
                                : now()->addDay()->setTime(10, 0)),
                    ])
                    ->action(function (WaitlistEntry $record, array $data, $livewire) {
                        $hueco = method_exists($livewire, 'huecoCita') ? $livewire->huecoCita() : null;
                        // El mismo mensaje y la misma anotación que "Ofrecer a ..."
                        // del aviso de la cancelación (WaitlistEntry::ofrecer).
                        $whatsapp = $record->ofrecer(\Carbon\Carbon::parse($data['slot_start']), $hueco);

                        Notification::make()
                            ->title('Estado actualizado a Notificado')
                            ->body('Se abrirá WhatsApp con el mensaje listo.')
                            ->success()
                            ->send();

                        return redirect()->away($whatsapp);
                    }),
                // El paciente dijo que sí: se le crea la cita en el hueco.
                Tables\Actions\Action::make('apartar')
                    ->label('Apartar el horario')
                    ->icon('heroicon-o-calendar-days')
                    ->color('primary')
                    ->visible(fn (WaitlistEntry $record) => $record->status === 'notified')
                    ->modalHeading('Apartarle el horario')
                    ->modalDescription(fn (WaitlistEntry $record) => ($hueco = \App\Models\Appointment::find($record->notified_for_appointment_id))
                        ? 'Se le agenda ' . $hueco->starts_at->locale('es')->isoFormat('dddd D [de] MMMM, HH:mm') . '.'
                        : 'Elija la fecha y hora que aceptó.')
                    ->form(fn (WaitlistEntry $record) => $record->notified_for_appointment_id ? [] : [
                        Forms\Components\DateTimePicker::make('starts_at')->label('Fecha y hora')->required()->native(false)->displayFormat('d/m/Y H:i')->minutesStep(15),
                    ])
                    ->action(function (WaitlistEntry $record, array $data) {
                        $hueco = \App\Models\Appointment::find($record->notified_for_appointment_id);
                        $inicio = $hueco?->starts_at ?? \Carbon\Carbon::parse($data['starts_at']);
                        $fin = $hueco?->ends_at ?? $inicio->copy()->addMinutes((int) ($record->service?->duration_minutes ?: 30));
                        $doctor = $hueco?->doctor_id ?? $record->doctor_id ?? auth()->user()->doctor?->id;

                        if ($choque = \App\Models\Appointment::mensajeDeTraslape($record->clinic_id, $doctor, $inicio, $fin, $hueco?->id)) {
                            Notification::make()->title('Ese horario ya se ocupó')->body($choque)->warning()->send();

                            return;
                        }

                        \App\Models\Appointment::create([
                            'clinic_id' => $record->clinic_id,
                            'doctor_id' => $doctor,
                            'patient_id' => $record->patient_id,
                            'service_id' => $record->service_id ?? $hueco?->service_id,
                            'starts_at' => $inicio,
                            'ends_at' => $fin,
                            'status' => 'scheduled',
                        ]);
                        $record->update(['status' => 'booked']);

                        Notification::make()->title('Horario apartado')
                            ->body(($record->patient?->first_name ?? 'El paciente') . ' quedó agendado ' . $inicio->locale('es')->isoFormat('dddd D [de] MMMM, HH:mm') . '.')
                            ->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            // Sin esto Filament dice "No se encontraron registros", que no
            // le dice al doctor que hacer ni con que llenarlo.
            ->emptyStateHeading('Nadie en lista de espera')
            ->emptyStateDescription('Anote aquí a quien quiera adelantar su cita. Cuando alguien le cancele, sabe a quién ofrecerle el hueco.')
            ->emptyStateIcon('heroicon-o-clock')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaitlistEntries::route('/'),
            'create' => Pages\CreateWaitlistEntry::route('/create'),
            'edit' => Pages\EditWaitlistEntry::route('/{record}/edit'),
        ];
    }
}
