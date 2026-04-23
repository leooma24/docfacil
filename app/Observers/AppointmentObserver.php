<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\WaitlistEntry;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Cuando se cancela una cita proxima (<=72h) y la clinica tiene lista de
 * espera activada, notifica via Filament database notifications a los
 * usuarios de la clinica con matches de waitlist que calzan con el slot
 * cancelado. El doctor/asistente ve la notificacion y decide a quien
 * ofrecer el hueco por WhatsApp manual.
 *
 * NO manda WhatsApp automatico — ese flow requeriria que la clinica tenga
 * su propia WA Business API configurada. Para el setup actual cada clinica
 * usa su propio WhatsApp personal con click-to-wa.me.
 */
class AppointmentObserver
{
    public function updated(Appointment $appointment): void
    {
        if (!$appointment->wasChanged('status')) return;
        if ($appointment->status !== 'cancelled') return;
        if (!$appointment->starts_at || !$appointment->starts_at->isFuture()) return;
        if ($appointment->starts_at->diffInHours(now()) > 72) return;

        $clinic = $appointment->clinic;
        if (!$clinic || !$clinic->hasFeature('waitlist')) return;

        $matches = $this->findWaitlistMatches($appointment);
        if ($matches->isEmpty()) return;

        $this->notifyClinicUsers($appointment, $matches);
    }

    protected function findWaitlistMatches(Appointment $appointment)
    {
        $date = $appointment->starts_at->toDateString();

        $query = WaitlistEntry::where('clinic_id', $appointment->clinic_id)
            ->where('status', 'waiting')
            ->whereDate('desired_from', '<=', $date)
            ->whereDate('desired_to', '>=', $date)
            ->with(['patient']);

        if ($appointment->service_id) {
            $query->where(function ($q) use ($appointment) {
                $q->whereNull('service_id')->orWhere('service_id', $appointment->service_id);
            });
        }
        if ($appointment->doctor_id) {
            $query->where(function ($q) use ($appointment) {
                $q->whereNull('doctor_id')->orWhere('doctor_id', $appointment->doctor_id);
            });
        }

        return $query
            ->orderByDesc('priority')
            ->orderBy('created_at')
            ->limit(3)
            ->get();
    }

    protected function notifyClinicUsers(Appointment $appointment, $matches): void
    {
        $slotDate = $appointment->starts_at->translatedFormat('l d \d\e F, H:i');
        $count = $matches->count();
        $names = $matches->pluck('patient.first_name')->filter()->join(', ');

        $recipients = $appointment->clinic->users()->whereIn('role', ['doctor', 'staff'])->get();
        if ($recipients->isEmpty()) return;

        foreach ($recipients as $recipient) {
            try {
                Notification::make()
                    ->title("{$count} paciente(s) en lista de espera para el slot cancelado")
                    ->icon('heroicon-o-user-group')
                    ->iconColor('warning')
                    ->body("Se canceló la cita del {$slotDate}. Candidatos en lista: {$names}. Abre Lista de espera para contactarlos.")
                    ->actions([
                        Action::make('ver')
                            ->label('Ver lista de espera')
                            ->url('/doctor/lista-de-espera')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($recipient);
            } catch (\Throwable $e) {
                Log::warning('Waitlist notification failed', ['error' => $e->getMessage()]);
            }
        }
    }
}
