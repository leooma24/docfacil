<?php

namespace App\Support;

use App\Models\Patient;
use Illuminate\Support\Str;

/**
 * El paciente que contesta "ya no me manden" (10-oct-2026). WhatsApp exige
 * respetarlo; si no, baja la calidad del número de DocFácil.
 *
 * Solo frases claras: "ya no puedo ir" es para cambiar la cita, no para
 * darse de baja.
 */
class NoQuiereWhatsapp
{
    /** Mensajes que, completos, son darse de baja. */
    private const SOLAS = ['baja', 'stop', 'alto', 'parar', 'cancelar mensajes', 'darme de baja'];

    /** Frases que, dentro del mensaje, son darse de baja. */
    private const FRASES = [
        'no me manden', 'no me envien', 'no me escriban', 'no me mande', 'no me envie', 'no me escriba',
        'no quiero recibir', 'no quiero mensajes', 'no quiero recordatorios',
        'dejen de mandar', 'dejen de enviar', 'dejen de escribir', 'darme de baja',
    ];

    public static function loPide(string $texto): bool
    {
        $t = trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z ]/', '', Str::lower(Str::ascii($texto)))));

        return in_array($t, self::SOLAS, true) || Str::contains($t, self::FRASES);
    }

    /**
     * Marca a todos los pacientes con ese número, en todos los consultorios:
     * lo pidió al número de DocFácil, del que salen los de todos.
     */
    public static function marcar(string $from): int
    {
        $diez = substr(preg_replace('/\D/', '', $from), -10);
        if (strlen($diez) !== 10) {
            return 0;
        }

        $limpio = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '')";

        return Patient::withoutGlobalScopes()
            ->whereNotNull('phone')
            ->whereRaw("{$limpio} LIKE ?", ['%' . $diez])
            ->where('no_quiere_whatsapp', false)
            ->update(['no_quiere_whatsapp' => true, 'no_quiere_whatsapp_at' => now()]);
    }
}
