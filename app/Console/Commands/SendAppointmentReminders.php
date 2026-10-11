<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Services\WhatsAppService;
use App\Support\PlantillasDeWhatsapp;
use App\Support\ZonaHoraria;
use Illuminate\Console\Command;

/**
 * Recordatorios automáticos por la API oficial de WhatsApp (12-oct-2026).
 *
 * Un día antes y, si no ha confirmado, dos horas antes, con botones
 * "Confirmo" / "Necesito cambiar". Solo para consultorios que lo prendieron en
 * Configuración, y solo si el interruptor general del servidor
 * (WHATSAPP_AUTOMATICOS) está prendido. Va a quien paga (la mamá si es un
 * niño), no repite a quien ya se le mandó a mano y respeta el tope diario.
 *
 * Antes (apagado el 14-sep) mandaba texto libre a todos los consultorios con
 * la función, sin que nadie lo pidiera.
 */
class SendAppointmentReminders extends Command
{
    protected $signature = 'docfacil:send-reminders';

    protected $description = 'Recordatorios automáticos por WhatsApp (plantillas) para los consultorios que los prendieron';

    public function handle(WhatsAppService $whatsapp): int
    {
        if (! config('services.whatsapp.automaticos')) {
            $this->info('Recordatorios automáticos apagados (WHATSAPP_AUTOMATICOS).');

            return self::SUCCESS;
        }

        $enviados = 0;
        Clinic::query()
            ->where('recordatorios_automaticos', true)
            ->withActiveFeature('whatsapp_reminders')
            ->each(function (Clinic $clinic) use ($whatsapp, &$enviados) {
                ZonaHoraria::aLaHoraDe($clinic, function () use ($clinic, $whatsapp, &$enviados) {
                    $enviados += $this->mandar($whatsapp, $clinic);
                });
            });

        $this->info("Recordatorios enviados: {$enviados}");

        return self::SUCCESS;
    }

    private function mandar(WhatsAppService $whatsapp, Clinic $clinic): int
    {
        $quedan = max(0, (int) $clinic->recordatorios_por_dia - $this->mandadosHoy($clinic));
        $enviados = 0;

        $tandas = [
            // Un día antes: las que no se han recordado (ni a mano).
            ['docfacil_recordatorio_cita', 'reminder_24h_sent_at', fn ($q) => $q
                ->where('status', 'scheduled')
                ->where('reminder_sent', false)
                ->whereBetween('starts_at', [now()->addHours(20), now()->addHours(28)])],
            // Dos horas antes: solo las que siguen sin confirmar.
            ['docfacil_recordatorio_hoy', 'reminder_2h_sent_at', fn ($q) => $q
                ->where('status', 'scheduled')
                ->whereBetween('starts_at', [now()->addMinutes(90), now()->addMinutes(150)])],
        ];

        foreach ($tandas as [$plantilla, $columna, $filtro]) {
            $citas = Appointment::withoutGlobalScopes()
                ->with(['patient.responsable', 'clinic'])
                ->where('clinic_id', $clinic->id)
                ->whereNull($columna)
                ->where($filtro)
                ->orderBy('starts_at')
                ->get()
                // Un mensaje por paciente aunque tenga dos citas ese día.
                ->unique(fn ($c) => $c->patient_id . '|' . $c->starts_at->toDateString());

            foreach ($citas as $cita) {
                if ($enviados >= $quedan) {
                    return $enviados;
                }
                $telefono = $cita->patient?->telefonoDeContacto();
                if (! $telefono || $cita->patient->noQuiereWhatsapp()) {
                    continue;
                }

                $ok = $whatsapp->sendTemplate($telefono, $plantilla, PlantillasDeWhatsapp::parametros($plantilla, $cita), PlantillasDeWhatsapp::IDIOMA, [
                    PlantillasDeWhatsapp::payload($cita, 'confirmar'),
                    PlantillasDeWhatsapp::payload($cita, 'cambiar'),
                ]);

                if ($ok) {
                    $cita->forceFill([$columna => now(), 'reminder_sent' => true, 'reminder_sent_at' => $cita->reminder_sent_at ?? now()])->saveQuietly();
                    $enviados++;
                }
            }
        }

        return $enviados;
    }

    private function mandadosHoy(Clinic $clinic): int
    {
        return Appointment::withoutGlobalScopes()->where('clinic_id', $clinic->id)
            ->where(fn ($q) => $q->whereDate('reminder_24h_sent_at', today())->orWhereDate('reminder_2h_sent_at', today()))
            ->count();
    }
}
