<?php

namespace App\Support;

use App\Models\Clinic;
use Illuminate\Support\Str;

/**
 * La hora de cada consultorio.
 *
 * La app corre en hora del centro. Un consultorio en Los Mochis va una hora
 * atrás y uno en Cancún una hora adelante: sin su zona, "ahora" estaba mal
 * para ellos. La agenda pública escondía horarios que todavía no pasaban (o
 * ofrecía los que ya pasaron), el escritorio elegía mal al siguiente
 * paciente y el check-in anotaba otra hora.
 *
 * Las citas se guardan con la hora que se capturó, así que no se mueven: lo
 * único que cambia es qué hora es "ahora" para ese consultorio.
 */
class ZonaHoraria
{
    public const CENTRO = 'America/Mexico_City';

    /** Como las reconoce un doctor, no como las nombra PHP. */
    public const OPCIONES = [
        'America/Mexico_City' => 'Centro: CDMX, Guadalajara, Monterrey, Puebla, Mérida…',
        'America/Mazatlan' => 'Pacífico: Sinaloa, Nayarit y Baja California Sur',
        'America/Hermosillo' => 'Sonora',
        'America/Tijuana' => 'Baja California: Tijuana, Mexicali, Ensenada',
        'America/Cancun' => 'Quintana Roo: Cancún, Playa del Carmen, Chetumal',
        'America/Ciudad_Juarez' => 'Ciudad Juárez',
        'America/Matamoros' => 'Tamaulipas frontera: Matamoros, Reynosa, Nuevo Laredo',
        'America/Ojinaga' => 'Ojinaga, Chihuahua',
    ];

    /**
     * Ciudades que no dejan lugar a duda. Las que se repiten en otro estado
     * (La Paz, Nogales, Loreto, "Obregón") se dejan fuera: para esas manda
     * el estado.
     */
    private const POR_CIUDAD = [
        'America/Ciudad_Juarez' => ['ciudad juarez', 'cd juarez'],
        'America/Mazatlan' => ['los mochis', 'culiacan', 'mazatlan', 'guasave', 'guamuchil', 'navolato', 'escuinapa', 'tepic', 'los cabos', 'cabo san lucas', 'san jose del cabo', 'ciudad constitucion'],
        'America/Hermosillo' => ['hermosillo', 'ciudad obregon', 'cd obregon', 'navojoa', 'guaymas', 'san luis rio colorado', 'caborca', 'huatabampo'],
        'America/Tijuana' => ['tijuana', 'mexicali', 'ensenada', 'tecate', 'rosarito'],
        'America/Cancun' => ['cancun', 'playa del carmen', 'chetumal', 'tulum', 'cozumel', 'isla mujeres', 'bacalar'],
        'America/Matamoros' => ['matamoros', 'reynosa', 'nuevo laredo', 'rio bravo', 'valle hermoso', 'ciudad miguel aleman'],
        'America/Ojinaga' => ['ojinaga'],
    ];

    /**
     * Los estados que van con otra hora.
     *
     * Tamaulipas y Chihuahua NO están aquí a propósito: la mayor parte de los
     * dos va con la hora del centro, y solo sus municipios fronterizos siguen
     * el horario de verano de Estados Unidos. Esos entran por ciudad, en
     * POR_CIUDAD — mapear el estado entero mandaría Tampico o Delicias a la
     * hora equivocada media mitad del año.
     */
    private const POR_ESTADO = [
        'sinaloa' => 'America/Mazatlan',
        'nayarit' => 'America/Mazatlan',
        'baja california sur' => 'America/Mazatlan',
        'bcs' => 'America/Mazatlan',
        'sonora' => 'America/Hermosillo',
        'baja california' => 'America/Tijuana',
        'bc' => 'America/Tijuana',
        'quintana roo' => 'America/Cancun',
        'q roo' => 'America/Cancun',
        'qroo' => 'America/Cancun',
    ];

    /** La zona del consultorio: la que eligió, o la que le toca por dónde está. */
    public static function delConsultorio(Clinic $clinica): string
    {
        $zona = $clinica->timezone ?: self::sugerida($clinica->state, $clinica->city);

        return self::esValida($zona) ? $zona : self::CENTRO;
    }

    /**
     * La que le toca por su ciudad o su estado. Null si es del centro o no se sabe.
     */
    public static function sugerida(?string $estado, ?string $ciudad): ?string
    {
        $ciudad = self::normalizar($ciudad);
        $estado = self::normalizar($estado);

        // Bahía de Banderas es Nayarit, pero va con la hora del centro, igual
        // que Puerto Vallarta que le queda enfrente.
        if (self::menciona($ciudad, ['bahia de banderas', 'nuevo vallarta'])) {
            return null;
        }

        foreach (self::POR_CIUDAD as $zona => $ciudades) {
            if (self::menciona($ciudad, $ciudades)) {
                return $zona;
            }
        }

        return self::POR_ESTADO[$estado] ?? null;
    }

    /** Pone la hora del consultorio para lo que resta de esta petición. */
    public static function usar(string $zona): void
    {
        date_default_timezone_set($zona);
        config(['app.timezone' => $zona]);
    }

    /**
     * Corre algo a la hora de un consultorio y regresa a la hora de antes.
     *
     * Para los comandos programados, que recorren todos los consultorios en
     * la misma corrida.
     */
    public static function aLaHoraDe(Clinic $clinica, callable $hacer): mixed
    {
        $antes = date_default_timezone_get();

        date_default_timezone_set(self::delConsultorio($clinica));

        try {
            return $hacer();
        } finally {
            date_default_timezone_set($antes);
        }
    }

    private static function esValida(?string $zona): bool
    {
        return $zona !== null
            && array_key_exists($zona, self::OPCIONES)
            && in_array($zona, timezone_identifiers_list(), true);
    }

    private static function normalizar(?string $texto): string
    {
        return Str::of((string) $texto)->lower()->ascii()->replace(['.', ','], ' ')->squish()->toString();
    }

    private static function menciona(string $texto, array $nombres): bool
    {
        foreach ($nombres as $nombre) {
            if (preg_match('/\b' . preg_quote($nombre, '/') . '\b/', $texto)) {
                return true;
            }
        }

        return false;
    }
}
