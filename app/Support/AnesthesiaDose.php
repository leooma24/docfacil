<?php

namespace App\Support;

use App\Models\Clinic;

/**
 * La dosis de anestesia, contra el peso del paciente.
 *
 * El peso ya se captura en los signos vitales de la consulta, así que el dato
 * está a la mano. Lo que NO se inventa aquí es el límite: depende del
 * anestésico y lo configura el consultorio.
 *
 * Por eso todo devuelve `null` cuando falta algo —el peso o la configuración—
 * en vez de suponer. Una app clínica que dice "está bien" sin poder saberlo es
 * más peligrosa que una que se calla.
 */
class AnesthesiaDose
{
    /**
     * El volumen del cartucho dental estándar.
     *
     * Es también el default de la columna, pero se repite aquí a propósito:
     * después de un `create()` el modelo no trae los defaults de la base, así
     * que sin esto el cálculo salía nulo hasta recargar el consultorio.
     */
    public const ML_PER_CARTRIDGE = 1.8;

    /** Cuántos miligramos trae un cartucho. Null si falta la concentración. */
    public static function mgPerCartridge(Clinic $clinic): ?float
    {
        $concentracion = (float) $clinic->anesthetic_mg_ml;
        $volumen = (float) $clinic->anesthetic_ml_per_cartridge ?: self::ML_PER_CARTRIDGE;

        if ($concentracion <= 0) {
            return null;
        }

        return round($concentracion * $volumen, 2);
    }

    /** ¿Está configurado el límite por peso? */
    public static function isConfigured(Clinic $clinic): bool
    {
        return (float) $clinic->anesthetic_max_mg_kg > 0
            && self::mgPerCartridge($clinic) !== null;
    }

    /**
     * El máximo de cartuchos para ese peso.
     *
     * Null cuando falta el peso o la configuración: no hay número que dar.
     */
    public static function maxCartridges(Clinic $clinic, ?float $weightKg): ?float
    {
        if (! self::isConfigured($clinic) || $weightKg === null || $weightKg <= 0) {
            return null;
        }

        $maximoMg = (float) $clinic->anesthetic_max_mg_kg * $weightKg;
        $porCartucho = self::mgPerCartridge($clinic);

        return round($maximoMg / $porCartucho, 1);
    }

    /**
     * Todo lo que la pantalla necesita para ser honesta.
     *
     * `exceeds` es null cuando no se puede saber, y eso es distinto de false:
     * "no se pasa" y "no hay con qué compararlo" no pueden verse igual.
     */
    public static function status(Clinic $clinic, ?float $weightKg, float $proposedCartridges): array
    {
        $maximo = self::maxCartridges($clinic, $weightKg);

        return [
            'configured' => self::isConfigured($clinic),
            'weight' => $weightKg,
            'cartridges' => $proposedCartridges,
            'maximum' => $maximo,
            'mg_per_cartridge' => self::mgPerCartridge($clinic),
            'exceeds' => $maximo === null ? null : $proposedCartridges > $maximo,
            'message' => self::message($clinic, $weightKg, $proposedCartridges, $maximo),
        ];
    }

    private static function message(Clinic $clinic, ?float $weightKg, float $cartridges, ?float $maximum): string
    {
        if (! self::isConfigured($clinic)) {
            return 'Sin límite configurado. La dosis máxima depende del anestésico que uses: configúralo en Ajustes y aquí se compara solo.';
        }

        if ($weightKg === null || $weightKg <= 0) {
            return 'Falta el peso del paciente en los signos vitales: sin peso no se puede comparar la dosis.';
        }

        if ($maximum === null) {
            return 'No se pudo calcular el máximo.';
        }

        return $cartridges > $maximum
            ? "Se pasa del máximo calculado ({$maximum} cartuchos para {$weightKg} kg)."
            : "Dentro del máximo calculado ({$maximum} cartuchos para {$weightKg} kg).";
    }
}
