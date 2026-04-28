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

class WaitlistEntryResource extends Resource
{
    protected static ?string $slug = 'lista-de-espera';

    protected static ?string $model = WaitlistEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Lista de espera';

    protected static ?string $modelLabel = 'Paciente en espera';

    protected static ?string $pluralModelLabel = 'Lista de espera';

    protected static ?int $navigationSort = 8;

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
                            ->helperText('Déjalo en blanco si le sirve cualquier servicio'),
                        Forms\Components\Select::make('doctor_id')
                            ->label('Doctor preferido (opcional)')
                            ->options(fn () => \App\Models\Doctor::where('clinic_id', auth()->user()->clinic_id)
                                ->with('user')->get()
                                ->mapWithKeys(fn ($d) => [$d->id => $d->user?->name ?? 'Doctor ' . $d->id]))
                            ->searchable()
                            ->helperText('Déjalo en blanco si le sirve cualquier doctor'),
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
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient?->first_name} {$record->patient?->last_name}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio')
                    ->placeholder('Cualquiera'),
                Tables\Columns\TextColumn::make('desired_from')
                    ->label('Desde')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('desired_to')
                    ->label('Hasta')
                    ->date('d/m/Y'),
                Tables\Columns\BadgeColumn::make('priority')
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
                    ->default('waiting'),
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
                            ->minutesStep(15),
                    ])
                    ->action(function (WaitlistEntry $record, array $data) {
                        $phone = preg_replace('/\D/', '', $record->patient->phone);
                        if (strlen($phone) === 10) $phone = '52' . $phone;
                        $firstName = $record->patient->first_name ?: 'hola';
                        $clinicName = $record->clinic->name ?? 'tu consultorio';
                        $slot = \Carbon\Carbon::parse($data['slot_start']);
                        $date = $slot->translatedFormat('l d \d\e F');
                        $time = $slot->format('H:i');

                        $message = "Hola {$firstName}, te escribo de *{$clinicName}*. Estás en nuestra lista de espera y se acaba de liberar un horario:\n\nFecha: {$date}\nHora: {$time} hrs\n\nSi te acomoda responde *SÍ* y te lo aparto. ¡Es primer llegado, primer servido!";

                        $record->update(['status' => 'notified', 'notified_at' => now()]);
                        Notification::make()
                            ->title('Estado actualizado a Notificado')
                            ->body('Se abrirá WhatsApp con el mensaje listo.')
                            ->success()
                            ->send();
                        return redirect()->away("https://wa.me/{$phone}?text=" . urlencode($message));
                    }),
                Tables\Actions\EditAction::make(),
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
