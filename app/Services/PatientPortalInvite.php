<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Facades\URL;

/**
 * Arma el acceso al portal para un paciente.
 *
 * No guardamos tokens en una tabla: la liga va firmada por Laravel (HMAC) y
 * caduca sola. Alcanza porque el único dato que lleva es el id del paciente,
 * y quien la abre todavía tiene que elegir su contraseña.
 *
 * El envío es por WhatsApp, que es por donde los consultorios realmente le
 * hablan a sus pacientes. El correo va de refuerzo cuando existe.
 */
class PatientPortalInvite
{
    /** Cuánto dura la liga antes de caducar. */
    public const DIAS_VIGENCIA = 7;

    /**
     * Liga firmada para que el paciente elija su contraseña.
     */
    public static function link(Patient $patient): string
    {
        return URL::temporarySignedRoute(
            'paciente.activar',
            now()->addDays(self::DIAS_VIGENCIA),
            ['patient' => $patient->id],
        );
    }

    /**
     * Mensaje de WhatsApp listo para enviar, con la liga dentro.
     */
    public static function mensajeWhatsApp(Patient $patient): string
    {
        $nombre = $patient->first_name ?: 'Hola';
        $consultorio = $patient->clinic->name ?? 'tu consultorio';

        return "Hola {$nombre}, te escribo de *{$consultorio}*.\n\n"
            . "Ya puedes consultar tus citas, recetas y pagos en línea cuando quieras. "
            . "Entra aquí y elige tu contraseña:\n\n"
            . self::link($patient) . "\n\n"
            . 'La liga vence en ' . self::DIAS_VIGENCIA . ' días.';
    }

    /**
     * URL de wa.me con el mensaje ya cargado.
     *
     * Usamos wa.me y no la API de WhatsApp a propósito: el mensaje sale del
     * número del consultorio, sin costo por conversación ni marca DocFácil.
     */
    public static function urlWhatsApp(Patient $patient): string
    {
        $telefono = preg_replace('/\D/', '', (string) $patient->phone);
        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
        }

        return "https://wa.me/{$telefono}?text=" . urlencode(self::mensajeWhatsApp($patient));
    }
}
