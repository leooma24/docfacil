<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Citas';

    protected static ?string $modelLabel = 'Cita';

    protected static ?string $pluralModelLabel = 'Citas';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles de la Cita')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->label('Paciente')
                            ->relationship('patient')
                            ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('first_name')->label('Nombre')->required(),
                                Forms\Components\TextInput::make('last_name')->label('Apellidos')->required(),
                                Forms\Components\TextInput::make('phone')->label('Teléfono')->tel(),
                                Forms\Components\TextInput::make('email')->label('Email')->email(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $data['clinic_id'] = auth()->user()->clinic_id;
                                return Patient::create($data)->id;
                            }),
                        Forms\Components\Select::make('doctor_id')
                            ->label('Doctor')
                            ->relationship('doctor')
                            ->getOptionLabelFromRecordUsing(fn (Doctor $record) => $record->user?->name ?? '')
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('service_id')
                            ->label('Servicio')
                            ->relationship('service', 'name')
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $service = Service::find($state);
                                    if ($service) {
                                        $set('duration_hint', "{$service->duration_minutes} min - \${$service->price}");
                                    }
                                }
                            }),
                        Forms\Components\Placeholder::make('duration_hint')
                            ->label('Duración / Precio')
                            ->content(function (Forms\Get $get) {
                                $service = $get('service_id') ? Service::find($get('service_id')) : null;
                                return $service ? "{$service->duration_minutes} min - \${$service->price}" : '-';
                            }),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Inicio')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minutesStep(15),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Fin')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minutesStep(15),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'scheduled' => 'Programada',
                                'confirmed' => 'Confirmada',
                                'in_progress' => 'En curso',
                                'completed' => 'Completada',
                                'cancelled' => 'Cancelada',
                                'no_show' => 'No asistió',
                            ])
                            ->default('scheduled')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Fecha/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio')
                    ->placeholder('Sin servicio'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'scheduled',
                        'info' => 'confirmed',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => fn ($state) => in_array($state, ['cancelled', 'no_show']),
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'scheduled' => 'Programada',
                        'confirmed' => 'Confirmada',
                        'in_progress' => 'En curso',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'no_show' => 'No asistió',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'scheduled' => 'Programada',
                        'confirmed' => 'Confirmada',
                        'in_progress' => 'En curso',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'no_show' => 'No asistió',
                    ]),
                Tables\Filters\Filter::make('today')
                    ->label('Hoy')
                    ->query(fn ($query) => $query->whereDate('starts_at', today())),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Próximas')
                    ->query(fn ($query) => $query->where('starts_at', '>=', now()))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('start_consultation')
                    ->label('Consulta')
                    ->icon('heroicon-o-play-circle')
                    ->color('primary')
                    ->url(fn (Appointment $record) => route('filament.doctor.pages.consultation', ['appointment' => $record->id]))
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed', 'in_progress'])),
                Tables\Actions\Action::make('charge')
                    ->label('Cobrar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['completed', 'in_progress']))
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->default(fn (Appointment $record) => $record->service?->price),
                        Forms\Components\Select::make('payment_method')
                            ->label('Método de pago')
                            ->options([
                                'cash' => 'Efectivo',
                                'card' => 'Tarjeta',
                                'transfer' => 'Transferencia',
                            ])
                            ->default('cash')
                            ->required(),
                    ])
                    ->action(function (Appointment $record, array $data) {
                        \App\Models\Payment::create([
                            'clinic_id' => auth()->user()->clinic_id,
                            'patient_id' => $record->patient_id,
                            'appointment_id' => $record->id,
                            'service_id' => $record->service_id,
                            'amount' => $data['amount'],
                            'payment_method' => $data['payment_method'],
                            'status' => 'paid',
                            'payment_date' => now()->toDateString(),
                        ]);
                    }),
                Tables\Actions\Action::make('complete')
                    ->label('Completar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed', 'in_progress']))
                    ->action(fn (Appointment $record) => $record->update(['status' => 'completed'])),
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (Appointment $record) => !empty($record->patient->phone) && in_array($record->status, ['scheduled', 'confirmed']))
                    ->requiresConfirmation()
                    ->modalDescription('¿Enviar recordatorio por WhatsApp al paciente?')
                    ->action(function (Appointment $record) {
                        $whatsapp = app(\App\Services\WhatsAppService::class);
                        $success = $whatsapp->sendAppointmentReminder(
                            to: $record->patient->phone,
                            patientName: $record->patient->full_name,
                            doctorName: $record->doctor->user->name ?? '',
                            dateTime: $record->starts_at->translatedFormat('l d \d\e F, H:i') . ' hrs',
                            clinicName: $record->clinic->name ?? 'DocFácil',
                        );
                        if ($success) {
                            $record->update(['reminder_sent' => true]);
                        }
                    }),
                Tables\Actions\Action::make('reschedule')
                    ->label('Re-agendar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed']))
                    ->form([
                        Forms\Components\DateTimePicker::make('new_date')
                            ->label('Nueva fecha y hora')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minutesStep(15)
                            ->default(fn (Appointment $record) => $record->starts_at),
                    ])
                    ->action(function (Appointment $record, array $data) {
                        $duration = $record->starts_at->diffInMinutes($record->ends_at);
                        $newStart = \Carbon\Carbon::parse($data['new_date']);
                        $record->update([
                            'starts_at' => $newStart,
                            'ends_at' => $newStart->copy()->addMinutes($duration),
                        ]);
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed']))
                    ->action(fn (Appointment $record) => $record->update(['status' => 'cancelled'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('starts_at', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
