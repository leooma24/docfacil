<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\URL;

/**
 * Arma la liga con la que un doctor estrena su cuenta.
 *
 * Se usa cuando se le deja el consultorio armado en vez de pedirle que se
 * registre él: la cuenta nace con una contraseña al azar que nadie conoce, y
 * esta liga es lo único que le da entrada.
 *
 * Antes se mandaba la de "olvidé mi contraseña", que dura 60 minutos. Es la
 * herramienta equivocada: quien pide un reset lo abre en el momento, pero a
 * quien le regalas una cuenta la abre cuando sale de consulta. La primera
 * fundadora la abrió cinco horas después y la rebotó dos veces.
 *
 * No guardamos tokens en una tabla: la liga va firmada por Laravel (HMAC) y
 * caduca sola, igual que la del paciente (ver PatientPortalInvite).
 */
class InvitacionAlDoctor
{
    /** Cuánto dura la liga antes de caducar. */
    public const DIAS_VIGENCIA = 7;

    /**
     * Liga firmada para que el doctor elija su contraseña.
     *
     * Lleva una huella de la contraseña actual. En cuanto elige la suya, la
     * huella cambia y la liga deja de servir: queda de un solo uso sin tener
     * que llevar la cuenta en ningún lado.
     */
    public static function liga(User $doctor): string
    {
        return URL::temporarySignedRoute(
            'doctor.estrenar',
            now()->addDays(self::DIAS_VIGENCIA),
            [
                'user' => $doctor->id,
                'h' => self::huella($doctor),
            ],
        );
    }

    /**
     * Huella de la contraseña actual, para que la liga sea de un solo uso.
     */
    public static function huella(User $doctor): string
    {
        return substr(hash_hmac('sha256', (string) $doctor->password, config('app.key')), 0, 16);
    }

    /**
     * Mensaje de WhatsApp listo para enviar, con la liga dentro.
     */
    public static function mensajeWhatsApp(User $doctor): string
    {
        $consultorio = $doctor->clinic->name ?? 'su consultorio';

        return "{$doctor->name}, aquí está su acceso a *{$consultorio}*.\n\n"
            . "Entre y elija su contraseña; ya adentro va a ver su agenda y sus pacientes:\n\n"
            . self::liga($doctor) . "\n\n"
            . 'La liga sirve durante ' . self::DIAS_VIGENCIA . ' días, ábrala cuando pueda.';
    }

    /**
     * URL de wa.me con el mensaje ya cargado.
     *
     * wa.me y no la API de WhatsApp, a propósito: sale del número de quien lo
     * manda, sin costo por conversación.
     */
    public static function urlWhatsApp(User $doctor, ?string $telefono = null): string
    {
        $numero = preg_replace('/\D/', '', (string) ($telefono ?? $doctor->clinic?->phone));

        if (strlen($numero) === 10) {
            $numero = '52' . $numero;
        }

        return "https://wa.me/{$numero}?text=" . urlencode(self::mensajeWhatsApp($doctor));
    }
}
