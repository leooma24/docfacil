<?php

namespace App\Support;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Support\Facades\URL;

/**
 * El aviso de privacidad del consultorio para sus pacientes.
 *
 * Los datos de salud son sensibles. La Ley Federal de Protección de Datos
 * Personales en Posesión de los Particulares (DOF 20-mar-2025) pide que el
 * paciente los autorice de forma expresa y demostrable (art. 8) y que conozca
 * el aviso cuando se le piden sus datos (arts. 15 y 16). Antes la agenda
 * pública y el check-in pedían alergias, tipo de sangre y motivo de consulta
 * sin aviso ni casilla.
 *
 * El responsable de los datos es el consultorio. DocFácil le da el aviso ya
 * armado con sus datos y guarda la prueba de que el paciente lo aceptó.
 */
class AvisoDePrivacidad
{
    /** Cambia cuando cambia el texto del aviso: cada aceptación es de una versión. */
    public const VERSION = '2026-09-14';

    /** Por dónde lo aceptó, como se le enseña al doctor. */
    public const MEDIOS = [
        'agenda_publica' => 'En la agenda en línea',
        'check_in' => 'En el check-in del consultorio',
        'liga' => 'Con la liga que le mandó el consultorio',
        'consultorio' => 'Firmado en papel en el consultorio',
    ];

    /** Días que sirve la liga que se manda por WhatsApp. */
    public const DIAS_VIGENCIA_LIGA = 30;

    public static function registrarAceptacion(Patient $paciente, string $medio): void
    {
        $paciente->forceFill([
            'aviso_privacidad_aceptado_at' => now(),
            'aviso_privacidad_version' => self::VERSION,
            'aviso_privacidad_medio' => $medio,
        ])->save();
    }

    /** ¿Ya aceptó la versión vigente del aviso? */
    public static function aceptoElVigente(Patient $paciente): bool
    {
        return $paciente->aviso_privacidad_aceptado_at !== null
            && $paciente->aviso_privacidad_version === self::VERSION;
    }

    public static function urlDelConsultorio(Clinic $clinica): string
    {
        return route('aviso-privacidad.show', $clinica->slug);
    }

    /** Liga firmada: nadie puede aceptar el aviso a nombre de otro paciente. */
    public static function urlParaAceptar(Patient $paciente): string
    {
        return URL::temporarySignedRoute(
            'aviso-privacidad.formulario',
            now()->addDays(self::DIAS_VIGENCIA_LIGA),
            ['slug' => $paciente->clinic->slug, 'paciente' => $paciente->id],
        );
    }

    /**
     * El mensaje ya armado para mandarlo desde el WhatsApp del consultorio.
     *
     * wa.me y no la API a propósito: sale del número del consultorio, sin
     * costo por conversación.
     */
    public static function urlWhatsApp(Patient $paciente): string
    {
        $telefono = preg_replace('/\D/', '', (string) $paciente->phone);

        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
        }

        $mensaje = 'Hola ' . trim((string) $paciente->first_name) . ", te escribo de *{$paciente->clinic->name}*.\n\n"
            . "Para cuidar tus datos de salud necesitamos que leas y aceptes nuestro aviso de privacidad. Es un minuto:\n\n"
            . self::urlParaAceptar($paciente) . "\n\n"
            . 'La liga vence en ' . self::DIAS_VIGENCIA_LIGA . ' días.';

        return "https://wa.me/{$telefono}?text=" . urlencode($mensaje);
    }
}
