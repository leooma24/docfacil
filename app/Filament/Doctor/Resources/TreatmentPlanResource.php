<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\TreatmentPlanResource\Pages;
use App\Models\Patient;
use App\Models\Service;
use App\Models\TreatmentPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;

class TreatmentPlanResource extends Resource
{
    protected static ?string $slug = 'presupuestos';

    protected static ?string $model = TreatmentPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Presupuestos';

    protected static ?string $modelLabel = 'Presupuesto';

    protected static ?string $pluralModelLabel = 'Presupuestos';

    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->clinic?->hasFeature('treatment_plans');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información general')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('patient_id')
                        ->label('Paciente')
                        ->relationship('patient')
                        ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('doctor_id')
                        ->label('Doctor a cargo')
                        ->options(fn () => \App\Models\Doctor::where('clinic_id', auth()->user()->clinic_id)
                            ->with('user')->get()
                            ->mapWithKeys(fn ($d) => [$d->id => $d->user?->name ?? 'Doctor ' . $d->id]))
                        ->default(fn () => auth()->user()->doctor?->id)
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->label('Título del presupuesto')
                        ->placeholder('Ej: Tratamiento de ortodoncia 18 meses')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label('Descripción / introducción')
                        ->placeholder('Qué incluye el plan, objetivo clínico, consideraciones previas...')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\DatePicker::make('valid_until')
                        ->label('Válido hasta')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now()->addDays(30)),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'draft' => 'Borrador',
                            'sent' => 'Enviado',
                            'accepted' => 'Aceptado',
                            'rejected' => 'Rechazado',
                            'completed' => 'Completado',
                            'cancelled' => 'Cancelado',
                        ])
                        ->default('draft')
                        ->required(),
                ]),

            Forms\Components\Section::make('Servicios y costos')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('service_id')
                                ->label('Servicio (opcional)')
                                ->options(fn () => Service::where('clinic_id', auth()->user()->clinic_id)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $service = Service::find($state);
                                        if ($service) {
                                            $set('description', $service->name);
                                            $set('unit_price', $service->price);
                                        }
                                    }
                                }),
                            Forms\Components\TextInput::make('description')
                                ->label('Descripción')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('tooth_number')
                                ->label('Diente (FDI)')
                                ->placeholder('Ej: 16')
                                ->maxLength(10),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Cantidad')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->minValue(1),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Precio unitario')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->minValue(0),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->orderColumn('sort_order')
                        ->reorderable()
                        ->addActionLabel('Agregar servicio'),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('discount')
                            ->label('Descuento (monto)')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\Placeholder::make('total_preview')
                            ->label('Total estimado')
                            ->content(fn ($record) => $record ? '$' . number_format((float) $record->total, 2) : 'Guarda para calcular'),
                    ]),
                ]),

            Forms\Components\Section::make('Notas internas')
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notas solo para ti (no aparecen en el PDF)')
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient?->first_name} {$record->patient?->last_name}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('MXN')
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Borrador',
                        'sent' => 'Enviado',
                        'accepted' => 'Aceptado',
                        'rejected' => 'Rechazado',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'sent',
                        'success' => fn ($state) => in_array($state, ['accepted', 'completed']),
                        'danger' => fn ($state) => in_array($state, ['rejected', 'cancelled']),
                    ]),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Enviado')
                    ->since()
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'sent' => 'Enviado',
                        'accepted' => 'Aceptado',
                        'rejected' => 'Rechazado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (TreatmentPlan $record) => route('treatment-plan.pdf', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('send_whatsapp')
                    ->label('Enviar por WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (TreatmentPlan $record) => !empty($record->patient?->phone) && in_array($record->status, ['draft', 'sent']))
                    ->requiresConfirmation()
                    ->modalHeading('Enviar presupuesto por WhatsApp')
                    ->modalDescription('Se generará un link único para que el paciente acepte en línea y se abrirá WhatsApp con el mensaje listo.')
                    ->action(function (TreatmentPlan $record) {
                        if (empty($record->public_token)) {
                            $record->generatePublicToken();
                        }
                        $record->update([
                            'status' => $record->status === 'draft' ? 'sent' : $record->status,
                            'sent_at' => $record->sent_at ?? now(),
                        ]);

                        $phone = preg_replace('/\D/', '', $record->patient->phone);
                        if (strlen($phone) === 10) $phone = '52' . $phone;

                        $acceptUrl = URL::signedRoute('treatment-plan.accept', ['token' => $record->public_token]);
                        $pdfUrl = route('treatment-plan.public', ['token' => $record->public_token]);

                        $clinicName = $record->clinic->name ?? 'tu consultorio';
                        $firstName = $record->patient->first_name ?: 'hola';
                        $total = number_format((float) $record->total, 2);

                        $msg = "Hola {$firstName}, te comparto el plan de tratamiento que armamos en *{$clinicName}*:\n\n"
                            . "*{$record->title}*\n"
                            . "Total: *\${$total} MXN*\n\n"
                            . "Ver el presupuesto: {$pdfUrl}\n\n"
                            . "Si te parece bien, puedes aceptarlo aquí: {$acceptUrl}\n\n"
                            . "Cualquier duda me la platicas por aquí.";

                        Notification::make()
                            ->title('Presupuesto listo para enviar')
                            ->body('Se abrirá WhatsApp con el mensaje pre-armado.')
                            ->success()
                            ->send();

                        return redirect()->away("https://wa.me/{$phone}?text=" . urlencode($msg));
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTreatmentPlans::route('/'),
            'create' => Pages\CreateTreatmentPlan::route('/create'),
            'edit' => Pages\EditTreatmentPlan::route('/{record}/edit'),
        ];
    }
}
