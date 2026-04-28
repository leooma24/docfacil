<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use Filament\Notifications\Notification;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Pedir reseña en Google — add-on $49/mes (google_reviews).
 *
 * Lista las citas completadas en las ultimas 48 horas cuyo paciente
 * tiene telefono y no se le ha pedido reseña aun. Click-to-wa.me con
 * mensaje pre-armado + link directo a dejar reseña en Google Maps
 * (de la URL que configuro el doctor en /configuracion).
 *
 * Marca appointment.review_request_sent_at al usar la accion — evita
 * pedir dos veces por la misma cita.
 */
class PendingReviewRequestsWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '⭐ Pide reseñas en Google';

    public static function canView(): bool
    {
        $clinic = auth()->user()?->clinic;
        if (!$clinic || !$clinic->hasFeature('google_reviews')) return false;
        // Si el doctor no configuro su URL de Google aun, mostrar el
        // widget igual con un mensaje de 'configura primero'
        return true;
    }

    public function getTableDescription(): ?string
    {
        $clinic = auth()->user()?->clinic;
        if (empty($clinic?->google_review_url)) {
            return '⚠️ Primero pega tu URL de reseña Google en Mi cuenta > Configuración para activar los botones.';
        }
        return 'Citas completadas en las últimas 48h. Un clic manda al paciente a dejarte reseña.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->where('clinic_id', auth()->user()->clinic_id)
                    ->where('status', 'completed')
                    ->whereNull('review_request_sent_at')
                    ->whereBetween('starts_at', [now()->subHours(48), now()])
                    ->whereHas('patient', fn ($q) => $q->whereNotNull('phone'))
                    ->with(['patient', 'service', 'doctor.user'])
                    ->orderBy('starts_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}"),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio')
                    ->placeholder('Consulta')
                    ->limit(22),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Hace')
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('pedir_resena')
                    ->label('Pedir reseña')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn () => !empty(auth()->user()?->clinic?->google_review_url))
                    ->url(function (Appointment $record) {
                        $clinic = auth()->user()->clinic;
                        $phone = preg_replace('/\D/', '', (string) $record->patient->phone);
                        if (strlen($phone) === 10) $phone = '52' . $phone;
                        if (strlen($phone) < 12) return null;

                        $firstName = $record->patient->first_name ?: 'hola';
                        $clinicName = $clinic->name ?? 'el consultorio';
                        $googleUrl = $clinic->google_review_url;

                        $msg = "¡Hola {$firstName}!\n\n"
                            . "Te escribo de *{$clinicName}*. Espero que hayas tenido una buena experiencia con nosotros.\n\n"
                            . "Si te gustó cómo te atendimos, ¿nos ayudarías con una reseña corta en Google? Para nosotros es enorme — ayuda a que más personas encuentren el consultorio.\n\n"
                            . "Dejar reseña: {$googleUrl}\n\n"
                            . "¡Gracias!";

                        return "https://wa.me/{$phone}?text=" . urlencode($msg);
                    })
                    ->openUrlInNewTab()
                    ->after(fn (Appointment $record) => $record->update(['review_request_sent_at' => now()])),
                Tables\Actions\Action::make('skip')
                    ->label('Saltar')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('¿Saltar esta reseña?')
                    ->modalDescription('No se le pedirá reseña a este paciente para esta cita. Puedes pedir a otros pacientes.')
                    ->action(function (Appointment $record) {
                        $record->update(['review_request_sent_at' => now()]);
                        Notification::make()->title('Cita marcada como saltada')->success()->send();
                    }),
            ])
            ->emptyStateHeading('Sin reseñas por pedir')
            ->emptyStateDescription(fn () => empty(auth()->user()?->clinic?->google_review_url)
                ? 'Configura tu URL de Google en Mi cuenta > Configuración para empezar.'
                : 'Completa una cita para empezar a pedir reseñas a tus pacientes.')
            ->paginated(false);
    }
}
