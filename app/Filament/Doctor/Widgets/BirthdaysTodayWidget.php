<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Patient;
use Filament\Widgets\Widget;

/**
 * Cumpleaneros de hoy — lista compacta con click-to-WhatsApp.
 *
 * Feature-gated por whatsapp_reminders (Basico+). Se muestra solo si hay
 * al menos 1 paciente que cumple anios hoy y tiene telefono.
 *
 * Patron intencional: NO envia mensaje automaticamente. El doctor da clic
 * en "Felicitar" y abre wa.me con el mensaje pre-armado en SU propio
 * WhatsApp — asi la felicitacion sale del numero de la clinica (no del
 * de DocFacil) y no consume API de Meta. Cuando el doctor quiera
 * auto-send, conecta su propia WA Business API (feature futura).
 */
class BirthdaysTodayWidget extends Widget
{
    protected static string $view = 'filament.doctor.widgets.birthdays-today';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        $clinic = auth()->user()?->clinic;
        if (!$clinic || !$clinic->hasFeature('whatsapp_reminders')) {
            return false;
        }
        $today = now();
        return Patient::where('clinic_id', auth()->user()->clinic_id)
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->whereNotNull('phone')
            ->exists();
    }

    public function getViewData(): array
    {
        $today = now();
        $clinic = auth()->user()?->clinic;
        $clinicName = $clinic?->name ?? 'tu consultorio';

        $patients = Patient::where('clinic_id', auth()->user()->clinic_id)
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->whereNotNull('phone')
            ->get()
            ->map(function (Patient $patient) use ($clinicName) {
                $name = $patient->first_name ?: 'hola';
                $phoneDigits = preg_replace('/\D/', '', (string) $patient->phone);
                if (strlen($phoneDigits) === 10) $phoneDigits = '52' . $phoneDigits;

                $message = "*¡Feliz cumpleaños, {$name}!*\n\n"
                    . "Todo el equipo de *{$clinicName}* te desea un día increíble. "
                    . "Gracias por confiar en nosotros para cuidar tu salud.\n\n"
                    . "Que cumplas muchos más.";

                return [
                    'id' => $patient->id,
                    'name' => trim(($patient->first_name ?: '') . ' ' . ($patient->last_name ?: '')),
                    'age' => $patient->birth_date ? $patient->birth_date->age : null,
                    'phone_display' => $patient->phone,
                    'wa_url' => "https://wa.me/{$phoneDigits}?text=" . urlencode($message),
                ];
            });

        return ['patients' => $patients];
    }
}
