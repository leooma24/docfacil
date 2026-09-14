<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Services\WhatsAppService;
use App\Support\ZonaHoraria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class SendAppointmentReminders extends Command
{
    protected $signature = 'docfacil:send-reminders';

    protected $description = 'Send WhatsApp reminders: 24h before, 2h before, and follow-up for missed appointments';

    public function handle(WhatsAppService $whatsapp): int
    {
        $sent24h = $sent2h = $sentFollowup = 0;

        // Consultorio por consultorio, cada uno a su hora: "dentro de 2 horas"
        // en Los Mochis no es lo mismo que en la Ciudad de México, y las citas
        // están guardadas con la hora local de cada uno.
        Clinic::query()
            ->withActiveFeature('whatsapp_reminders')
            ->each(function (Clinic $clinic) use ($whatsapp, &$sent24h, &$sent2h, &$sentFollowup) {
                ZonaHoraria::aLaHoraDe($clinic, function () use ($clinic, $whatsapp, &$sent24h, &$sent2h, &$sentFollowup) {
                    $sent24h += $this->send24hReminders($whatsapp, $clinic);
                    $sent2h += $this->send2hReminders($whatsapp, $clinic);
                    $sentFollowup += $this->sendFollowups($whatsapp, $clinic);
                });
            });

        $this->info("24h reminders: {$sent24h}");
        $this->info("2h reminders: {$sent2h}");
        $this->info("Follow-ups: {$sentFollowup}");

        return Command::SUCCESS;
    }

    /**
     * Send reminder 24 hours before appointment (between 20 and 28 hours window).
     */
    protected function send24hReminders(WhatsAppService $whatsapp, Clinic $clinic): int
    {
        $appointments = Appointment::withoutGlobalScopes()
            ->with(['patient', 'doctor.user', 'clinic', 'service'])
            ->where('clinic_id', $clinic->id)
            ->whereNull('reminder_24h_sent_at')
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereBetween('starts_at', [now()->addHours(20), now()->addHours(28)])
            ->get();

        $count = 0;
        foreach ($appointments as $appt) {
            $phone = $appt->patient->phone;
            if (empty($phone)) continue;

            $name = $appt->patient->first_name;
            $clinicName = $appt->clinic->name ?? 'tu clínica';
            $service = $appt->service->name ?? 'consulta';
            $date = $appt->starts_at->translatedFormat('l d \d\e F');
            $time = $appt->starts_at->format('H:i');

            // Links firmados 1-click para confirmar/cancelar. Validos hasta
            // 2 horas despues del inicio de la cita (para dejar margen).
            $ttl = $appt->starts_at->copy()->addHours(2);
            $confirmUrl = URL::temporarySignedRoute(
                'appointment.confirm',
                $ttl,
                ['appointment' => $appt->id, 'action' => 'confirm']
            );
            $cancelUrl = URL::temporarySignedRoute(
                'appointment.confirm',
                $ttl,
                ['appointment' => $appt->id, 'action' => 'cancel']
            );

            $message = "*Recordatorio de cita*\n\n"
                . "Hola *{$name}*, te recordamos tu cita en *{$clinicName}*:\n\n"
                . "Fecha: {$date}\n"
                . "Hora: {$time} hrs\n"
                . "Servicio: {$service}\n\n"
                . "Confirmar: {$confirmUrl}\n"
                . "Cancelar: {$cancelUrl}\n\n"
                . "¡Te esperamos!";

            if ($whatsapp->sendMessage($phone, $message)) {
                $appt->update(['reminder_24h_sent_at' => now(), 'reminder_sent' => true]);
                $count++;
                $this->line("24h sent: {$name}");
            }
        }
        return $count;
    }

    /**
     * Send short reminder 2 hours before appointment (between 1 and 3 hours window).
     */
    protected function send2hReminders(WhatsAppService $whatsapp, Clinic $clinic): int
    {
        $appointments = Appointment::withoutGlobalScopes()
            ->with(['patient', 'clinic'])
            ->where('clinic_id', $clinic->id)
            ->whereNull('reminder_2h_sent_at')
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereBetween('starts_at', [now()->addHours(1), now()->addHours(3)])
            ->get();

        $count = 0;
        foreach ($appointments as $appt) {
            $phone = $appt->patient->phone;
            if (empty($phone)) continue;

            $name = $appt->patient->first_name;
            $time = $appt->starts_at->format('H:i');
            $clinicName = $appt->clinic->name ?? 'tu clínica';

            $message = "Hola *{$name}*, tu cita en *{$clinicName}* es en *{$time} hrs* (aprox. 2 horas).\n\n"
                . "¡Te esperamos! Si tuviste un imprevisto, avísanos por este medio.";

            if ($whatsapp->sendMessage($phone, $message)) {
                $appt->update(['reminder_2h_sent_at' => now()]);
                $count++;
                $this->line("2h sent: {$name}");
            }
        }
        return $count;
    }

    /**
     * Send follow-up to patients who didn't show up.
     */
    protected function sendFollowups(WhatsAppService $whatsapp, Clinic $clinic): int
    {
        $appointments = Appointment::withoutGlobalScopes()
            ->with(['patient', 'clinic'])
            ->where('clinic_id', $clinic->id)
            ->whereNull('followup_sent_at')
            ->where('status', 'no_show')
            ->where('starts_at', '>=', now()->subDays(3))
            ->where('starts_at', '<', now()->subHours(2))
            ->get();

        $count = 0;
        foreach ($appointments as $appt) {
            $phone = $appt->patient->phone;
            if (empty($phone)) continue;

            $name = $appt->patient->first_name;
            $clinicName = $appt->clinic->name ?? 'tu clínica';

            $message = "Hola *{$name}*, notamos que no pudiste asistir a tu cita en *{$clinicName}*.\n\n"
                . "¿Todo bien? Si quieres, podemos reagendarla para el día que te acomode.\n\n"
                . "Responde este mensaje y te ayudamos a encontrar un nuevo horario. 😊";

            if ($whatsapp->sendMessage($phone, $message)) {
                $appt->update(['followup_sent_at' => now()]);
                $count++;
                $this->line("Followup sent: {$name}");
            }
        }
        return $count;
    }
}
