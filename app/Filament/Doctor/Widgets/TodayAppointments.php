<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Services\WhatsAppService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayAppointments extends BaseWidget
{
    protected static ?int $sort = -4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Citas de hoy y mañana';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->where('clinic_id', auth()->user()->clinic_id)
                    // Hoy y mañana: el dentista usualmente confirma de noche
                    // las del dia siguiente, o en la mañana las del mismo dia.
                    ->whereBetween('starts_at', [today()->startOfDay(), today()->addDay()->endOfDay()])
                    ->with(['patient', 'doctor.user', 'service'])
                    ->orderBy('starts_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('day_label')
                    ->visibleFrom('sm')
                    ->label('Día')
                    ->state(fn ($record) => $record->starts_at->isToday() ? 'Hoy' : 'Mañana')
                    ->badge()
                    ->color(fn ($state) => $state === 'Hoy' ? 'primary' : 'gray'),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Hora')
                    ->dateTime('H:i')
                    ->size('lg')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('patient.first_name')
                    // El nombre lleva al perfil del paciente: de cualquier lista se llega a todo lo suyo.
                    ->url(fn ($record) => \App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $record->patient_id], panel: 'doctor'))
                    ->color('primary')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}")
                    ->description(fn ($record) => $record->patient->phone ?? '')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alertas')
                    ->label('Alertas')
                    ->state(fn ($record) => [...(\App\Models\LabOrder::pendienteParaCita($record->id) ? ['Lab: no ha llegado'] : []), ...\App\Support\AlertasClinicas::etiquetas($record->patient)])
                    ->badge()
                    ->color('danger')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('service.name')
                    ->visibleFrom('md')
                    ->label('Servicio')
                    ->placeholder('Sin servicio')
                    ->description(fn ($record) => $record->service ? '$' . number_format($record->service->price, 0) : ''),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->visibleFrom('2xl')
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
                    ])
                    // Ya está en la sala de espera (QR o recepción), y desde cuándo.
                    ->description(fn (Appointment $record) => $record->arrived_at && in_array($record->status, ['scheduled', 'confirmed'])
                        ? 'Llegó ' . $record->arrived_at->format('H:i') . ' · espera ' . $record->arrived_at->diffForHumans(now(), \Carbon\CarbonInterface::DIFF_ABSOLUTE, true)
                        : null),
            ])
            ->actions([
                // Las secundarias van como icono y solo WhatsApp lleva
                // etiqueta. La tabla se salía 175 px del ancho de la pantalla y
                // lo primero que se cortaba era esta columna: el doctor veía
                // las citas y no el botón para avisarles, que es justo lo que
                // vino a hacer.
                Tables\Actions\Action::make('start_consultation')
                    ->label('Iniciar consulta')
                    ->iconButton()
                    ->tooltip('Iniciar consulta')
                    ->icon('heroicon-o-play-circle')
                    ->color('primary')
                    ->url(fn (Appointment $record) => route('filament.doctor.pages.consulta', ['appointment' => $record->id]))
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed'])),
                Tables\Actions\Action::make('llego')
                    // Para cuando el paciente no usa el QR: recepción lo marca.
                    ->label('Llegó')
                    ->iconButton()
                    ->tooltip('Marcar que ya llegó')
                    ->icon('heroicon-o-map-pin')
                    ->color('success')
                    ->visible(fn (Appointment $record) => ! $record->arrived_at && $record->starts_at->isToday() && in_array($record->status, ['scheduled', 'confirmed']))
                    ->action(fn (Appointment $record) => $record->marcarLlegada()),
                Tables\Actions\Action::make('in_progress')
                    ->label('En consulta')
                    ->iconButton()
                    ->tooltip('En consulta')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->visible(fn (Appointment $record) => $record->status === 'in_progress')
                    ->url(fn (Appointment $record) => route('filament.doctor.pages.consulta', ['appointment' => $record->id])),
                Tables\Actions\Action::make('whatsapp')
                    // Pasa por DocFácil para dejarla recordada (y a las demás
                    // citas del paciente ese día) y abre WhatsApp con un solo
                    // mensaje. Ver la ruta cita.recordar.
                    ->label(fn (Appointment $record) => $record->reminder_sent ? 'Recordado' : 'WhatsApp')
                    ->icon(fn (Appointment $record) => $record->reminder_sent ? 'heroicon-o-check-circle' : 'heroicon-o-chat-bubble-left-ellipsis')
                    ->color(fn (Appointment $record) => $record->reminder_sent ? 'gray' : 'success')
                    ->tooltip(fn (Appointment $record) => $record->reminder_sent ? 'Ya se le mandó el recordatorio. Tóquelo para mandarlo otra vez.' : 'Mandar recordatorio por WhatsApp')
                    ->visible(fn (Appointment $record) => !empty($record->patient->telefonoDeContacto()) && in_array($record->status, ['scheduled', 'confirmed']))
                    ->url(fn (Appointment $record) => route('cita.recordar', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('no_show')
                    ->iconButton()
                    ->tooltip('Marcar que no asistió')
                    ->label('No asistió')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed']) && $record->starts_at->isPast())
                    ->action(fn (Appointment $record) => $record->update(['status' => 'no_show'])),
            ])
            ->emptyStateHeading('No hay citas para hoy')
            ->emptyStateDescription('Su agenda está libre. ¡Buen momento para revisar pendientes!')
            ->emptyStateIcon('heroicon-o-calendar')
            ->paginated(false);
    }
}
