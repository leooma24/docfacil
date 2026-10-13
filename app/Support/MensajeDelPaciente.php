<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\MensajeDePaciente;
use App\Models\User;
use App\Services\WhatsAppService;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Un paciente le escribió al número de DocFácil (12-oct-2026).
 *
 * Los recordatorios salen de un solo número para todos los consultorios. Se
 * reconoce de quién es por el último recordatorio que se le mandó a ese
 * teléfono en la semana (el del paciente o el de su responsable), se le pasa
 * el mensaje a ese consultorio en la campanita y al paciente se le avisa una
 * sola vez cada 12 horas. Con la IA apagada, antes se perdía.
 */
class MensajeDelPaciente
{
    public static function recibir(string $from, string $texto): void
    {
        $diez = substr(preg_replace('/\D/', '', $from), -10);
        if (strlen($diez) !== 10) {
            return;
        }

        $cita = self::ultimaCitaRecordada($diez);
        $yaSeLeContesto = MensajeDePaciente::where('telefono', $diez)->where('created_at', '>=', now()->subHours(12))->exists();

        MensajeDePaciente::create([
            'clinic_id' => $cita?->clinic_id,
            'patient_id' => $cita?->patient_id,
            'appointment_id' => $cita?->id,
            'telefono' => $diez,
            'texto' => mb_substr($texto, 0, 2000),
        ]);

        if ($cita) {
            self::avisarAlConsultorio($cita, $diez, $texto);
        }

        if (! $yaSeLeContesto) {
            app(WhatsAppService::class)->sendMessage($from, $cita
                ? "Gracias. Le pasamos su mensaje a {$cita->clinic?->name}; le contestan desde su WhatsApp."
                : 'Este número solo manda recordatorios de citas de DocFácil. Para cualquier cosa, escríbale directo a su consultorio.');
        }
    }

    private static function ultimaCitaRecordada(string $diez): ?Appointment
    {
        // En la demo todo le llega a Omar (DemoSoloAOmar): lo suyo es de la demo.
        $esOmar = substr(\App\Http\Middleware\DemoSoloAOmar::numero(), -10) === $diez;

        return Appointment::withoutGlobalScopes()
            ->with(['patient' => fn ($q) => $q->withoutGlobalScopes()->with('responsable'), 'clinic'])
            ->where(fn ($q) => $q->where('reminder_24h_sent_at', '>=', now()->subDays(7))
                ->orWhere('reminder_2h_sent_at', '>=', now()->subDays(7)))
            ->get()
            ->filter(function (Appointment $cita) use ($diez, $esOmar) {
                if ($esOmar && $cita->clinic?->is_demo) {
                    return true;
                }
                $telefono = substr(preg_replace('/\D/', '', (string) $cita->patient?->telefonoDeContacto()), -10);

                return $telefono === $diez;
            })
            ->sortByDesc(fn (Appointment $c) => max($c->reminder_24h_sent_at?->timestamp ?? 0, $c->reminder_2h_sent_at?->timestamp ?? 0))
            ->first();
    }

    private static function avisarAlConsultorio(Appointment $cita, string $diez, string $texto): void
    {
        $nombre = trim(($cita->patient?->first_name ?? '') . ' ' . ($cita->patient?->last_name ?? '')) ?: 'Un paciente';

        foreach (User::where('clinic_id', $cita->clinic_id)->whereIn('role', ['doctor', 'staff'])->get() as $usuario) {
            Notification::make()
                ->title("{$nombre} le escribió por WhatsApp")
                ->body('«' . mb_strimwidth($texto, 0, 300, '…') . '»')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->iconColor('info')
                ->actions([
                    Action::make('contestar')->label('Contestarle por WhatsApp')
                        ->url('https://wa.me/52' . $diez, shouldOpenInNewTab: true)->markAsRead(),
                    Action::make('ver')->label('Ver paciente')
                        ->url(\App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $cita->patient_id], panel: 'doctor')),
                ])
                ->sendToDatabase($usuario);
        }
    }
}
