<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Services\WhatsAppService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayAppointments extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Citas de hoy';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->where('clinic_id', auth()->user()->clinic_id)
                    ->whereDate('starts_at', today())
                    ->with(['patient', 'doctor.user', 'service'])
                    ->orderBy('starts_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Hora')
                    ->dateTime('H:i')
                    ->size('lg')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}")
                    ->description(fn ($record) => $record->patient->phone ?? '')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio')
                    ->placeholder('Sin servicio')
                    ->description(fn ($record) => $record->service ? '$' . number_format($record->service->price, 0) : ''),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'scheduled' => 'Programada',
                        'confirmed' => 'Confirmada',
                        'in_progress' => 'En consulta',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'no_show' => 'No asistió',
                    })
                    ->colors([
                        'warning' => 'scheduled',
                        'info' => 'confirmed',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => fn ($state) => in_array($state, ['cancelled', 'no_show']),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('start_consultation')
                    ->label('Iniciar consulta')
                    ->icon('heroicon-o-play-circle')
                    ->color('primary')
                    ->url(fn (Appointment $record) => route('filament.doctor.pages.consulta', ['appointment' => $record->id]))
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed'])),
                Tables\Actions\Action::make('in_progress')
                    ->label('En consulta')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->visible(fn (Appointment $record) => $record->status === 'in_progress')
                    ->url(fn (Appointment $record) => route('filament.doctor.pages.consulta', ['appointment' => $record->id])),
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (Appointment $record) => !empty($record->patient->phone) && in_array($record->status, ['scheduled', 'confirmed']))
                    ->url(function (Appointment $record) {
                        $phone = preg_replace('/\D/', '', $record->patient->phone);
                        if (strlen($phone) === 10) $phone = '52' . $phone;
                        $clinicName = $record->clinic->name ?? 'DocFácil';
                        $time = $record->starts_at->format('H:i');

                        // Links firmados 1-clic para confirmar/cancelar — validos
                        // hasta 2hrs despues del inicio (mismo patron que cron).
                        $ttl = $record->starts_at->copy()->addHours(2);
                        $confirmUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'appointment.confirm',
                            $ttl,
                            ['appointment' => $record->id, 'action' => 'confirm']
                        );
                        $cancelUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'appointment.confirm',
                            $ttl,
                            ['appointment' => $record->id, 'action' => 'cancel']
                        );

                        $msg = urlencode(
                            "Hola {$record->patient->first_name}, te recordamos tu cita hoy en *{$clinicName}*:\n\n" .
                            "Hora: {$time} hrs\n\n" .
                            "Confirmar: {$confirmUrl}\n" .
                            "Cancelar: {$cancelUrl}\n\n" .
                            "¡Te esperamos!"
                        );
                        return "https://wa.me/{$phone}?text={$msg}";
                    })
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('no_show')
                    ->label('No asistió')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed']) && $record->starts_at->isPast())
                    ->action(fn (Appointment $record) => $record->update(['status' => 'no_show'])),
            ])
            ->emptyStateHeading('No hay citas para hoy')
            ->emptyStateDescription('Tu agenda está libre. ¡Buen momento para revisar pendientes!')
            ->emptyStateIcon('heroicon-o-calendar')
            ->paginated(false);
    }
}
