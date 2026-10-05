<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\WaitlistEntry;
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
    /**
     * Se cancele desde donde se cancele (la cita abierta, el paciente desde su
     * liga, la lista), los demás del consultorio se enteran del hueco con los
     * mismos candidatos que ve quien canceló (WaitlistEntry::candidatosPara) y
     * con "Ofrecer a ..." para cada uno.
     *
     * Antes solo avisaba si la cita era dentro de 72 horas y buscaba los
     * candidatos a su manera, así que el aviso y la pantalla decían cosas
     * distintas. Lo que importa es si el día cae en lo que el paciente pidió.
     */
    public function updated(Appointment $appointment): void
    {
        if (!$appointment->wasChanged('status')) return;

        // Completada por donde sea: su tratamiento del presupuesto queda hecho.
        if ($appointment->status === 'completed') {
            $appointment->cerrarSuTratamiento();

            return;
        }

        if ($appointment->status !== 'cancelled') return;
        if (!$appointment->starts_at || !$appointment->starts_at->isFuture()) return;

        $clinic = $appointment->clinic;
        if (!$clinic || !$clinic->hasFeature('waitlist')) return;

        $candidatos = WaitlistEntry::candidatosPara($appointment, 3);
        if ($candidatos->isEmpty()) return;

        $this->notifyClinicUsers($appointment, $candidatos);
    }

    protected function notifyClinicUsers(Appointment $appointment, $candidatos): void
    {
        $slotDate = $appointment->starts_at->translatedFormat('l d \d\e F, H:i');
        $names = $candidatos->map(fn ($e) => trim(($e->patient->first_name ?? '') . ' ' . ($e->patient->last_name ?? '')))->filter()->join(', ');

        // A quien canceló desde la pantalla ya se le avisó ahí mismo.
        $recipients = $appointment->clinic->users()
            ->whereIn('role', ['doctor', 'staff'])
            ->when(auth()->id(), fn ($q, $yo) => $q->whereKeyNot($yo))
            ->get();

        foreach ($recipients as $recipient) {
            try {
                Notification::make()
                    ->title('Se liberó el ' . $slotDate)
                    ->icon('heroicon-o-user-group')
                    ->iconColor('warning')
                    ->body('En lista de espera para ese día: ' . $names . '.')
                    ->actions(WaitlistEntry::botonesParaElHueco($appointment, $candidatos))
                    ->sendToDatabase($recipient);
            } catch (\Throwable $e) {
                Log::warning('Waitlist notification failed', ['error' => $e->getMessage()]);
            }
        }
    }
}
