<?php

namespace App\Support;

use App\Models\Appointment;

/**
 * Las plantillas de WhatsApp que manda DocFácil por la API oficial.
 *
 * Meta solo deja escribirle primero a alguien con una plantilla aprobada.
 * Son de categoría UTILITY (avisos de una cita que la persona ya tiene) y van
 * de usted, sin emojis, como todo lo que sale a pacientes. Salen del número de
 * DocFácil, así que el texto dice de qué consultorio son y el pie lo aclara.
 *
 * Los botones llevan un dato firmado (payload) con la cita y la acción, para
 * que al tocarlos sepamos qué cita confirmar sin que nadie lo pueda falsificar.
 */
class PlantillasDeWhatsapp
{
    public const IDIOMA = 'es_MX';

    public const PLANTILLAS = [
        'docfacil_recordatorio_cita' => [
            'cuerpo' => 'Hola {{1}}, le recordamos {{2}} en {{3}} el {{4}} a las {{5}}. ¿Nos confirma que viene?',
            'ejemplo' => ['Ana', 'su cita', 'Consultorio Sonrisas', 'jueves 15 de octubre', '11:00'],
            'botones' => ['Confirmo', 'Necesito cambiar'],
        ],
        'docfacil_recordatorio_hoy' => [
            'cuerpo' => 'Hola {{1}}, le esperamos hoy a las {{2}} en {{3}} para {{4}}. ¿Nos confirma que viene?',
            'ejemplo' => ['Ana', '11:00', 'Consultorio Sonrisas', 'su cita'],
            'botones' => ['Confirmo', 'Necesito cambiar'],
        ],
    ];

    public const PIE = 'Mensaje enviado por DocFácil a nombre de su consultorio.';

    /** Lo que se le manda a Meta para crear la plantilla. */
    public static function paraMeta(string $nombre): array
    {
        $p = self::PLANTILLAS[$nombre];

        return [
            'name' => $nombre,
            'language' => self::IDIOMA,
            'category' => 'UTILITY',
            'components' => [
                ['type' => 'BODY', 'text' => $p['cuerpo'], 'example' => ['body_text' => [$p['ejemplo']]]],
                ['type' => 'FOOTER', 'text' => self::PIE],
                ['type' => 'BUTTONS', 'buttons' => array_map(fn ($t) => ['type' => 'QUICK_REPLY', 'text' => $t], $p['botones'])],
            ],
        ];
    }

    /**
     * Los datos de una cita para la plantilla, en el orden de sus {{n}}.
     *
     * @return list<string>
     */
    public static function parametros(string $nombre, Appointment $cita): array
    {
        $cita->loadMissing(['patient.responsable', 'clinic']);
        $quien = $cita->patient?->nombreDeContacto() ?: 'Hola';
        $deQuien = $cita->patient?->responsable_id ? 'la cita de ' . $cita->patient->first_name : 'su cita';
        $consultorio = $cita->clinic?->name ?? 'su consultorio';
        $hora = $cita->starts_at->format('H:i');

        return match ($nombre) {
            'docfacil_recordatorio_hoy' => [$quien, $hora, $consultorio, $deQuien],
            default => [$quien, $deQuien, $consultorio, $cita->starts_at->locale('es')->isoFormat('dddd D [de] MMMM'), $hora],
        };
    }

    /** El dato del botón: "cita:15:confirmar:firma". */
    public static function payload(Appointment $cita, string $accion): string
    {
        return "cita:{$cita->id}:{$accion}:" . self::firma($cita->id, $accion);
    }

    /**
     * Lee el dato de un botón tocado. null si no es nuestro o la firma no cuadra.
     *
     * @return array{cita: int, accion: string}|null
     */
    public static function leer(?string $payload): ?array
    {
        if (! preg_match('/^cita:(\d+):(confirmar|cambiar):([a-f0-9]+)$/', (string) $payload, $m)) {
            return null;
        }

        return hash_equals(self::firma((int) $m[1], $m[2]), $m[3]) ? ['cita' => (int) $m[1], 'accion' => $m[2]] : null;
    }

    private static function firma(int $citaId, string $accion): string
    {
        return substr(hash_hmac('sha256', "cita:{$citaId}:{$accion}", (string) config('app.key')), 0, 20);
    }
}
