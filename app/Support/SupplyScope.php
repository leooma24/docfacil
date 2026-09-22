<?php

namespace App\Support;

/**
 * Cuántas veces aplica una línea, según en qué dientes se trabajó.
 *
 * Todo el motor sale de una sola entrada —los dientes— y cuatro formas de
 * contarla. Aquí viven las cuentas, y la más fina es la de la anestesia, que
 * depende de la arcada.
 */
class SupplyScope
{
    /**
     * Los dientes en notación FDI, normalizados.
     *
     * Acepta lo que el doctor escribe: "16", "46-47", "16, 15, 14". Un rango
     * solo se expande dentro del mismo cuadrante — del 46 al 47 sí, del 16 al
     * 26 no, porque son lados distintos de la boca.
     */
    public static function teeth(array $raw): array
    {
        $dientes = [];

        foreach ($raw as $crudo) {
            foreach (preg_split('/[,\s]+/', (string) $crudo) ?: [] as $parte) {
                if ($parte === '') {
                    continue;
                }

                if (preg_match('/^(\d{2})\s*-\s*(\d{2})$/', $parte, $trozos)) {
                    $desde = (int) $trozos[1];
                    $hasta = (int) $trozos[2];

                    if (intdiv($desde, 10) === intdiv($hasta, 10) && $hasta >= $desde) {
                        for ($diente = $desde; $diente <= $hasta; $diente++) {
                            $dientes[] = $diente;
                        }
                    } else {
                        $dientes[] = $desde;
                        $dientes[] = $hasta;
                    }

                    continue;
                }

                if (preg_match('/^\d{2}$/', $parte)) {
                    $dientes[] = (int) $parte;
                }
            }
        }

        return array_values(array_unique(array_filter($dientes, [self::class, 'isValidFdi'])));
    }

    /** Agrupados por cuadrante: [1 => [5, 6], 4 => [6, 7]]. */
    public static function byQuadrant(array $raw): array
    {
        $grupos = [];

        foreach (self::teeth($raw) as $diente) {
            $grupos[intdiv($diente, 10)][] = $diente % 10;
        }

        foreach ($grupos as &$posiciones) {
            sort($posiciones);
        }

        return $grupos;
    }

    /** Cuántos cuadrantes se tocaron. */
    public static function quadrants(array $raw): int
    {
        return count(self::byQuadrant($raw));
    }

    /**
     * Grupos de dientes pegados, contando todos los cuadrantes.
     *
     * Es la regla del maxilar, donde el hueso es poroso y la infiltración baña
     * diente por diente: 16 y 14 son dos dosis, no una.
     */
    public static function contiguousZones(array $raw): int
    {
        $zonas = 0;

        foreach (self::byQuadrant($raw) as $posiciones) {
            $anterior = null;

            foreach ($posiciones as $posicion) {
                if ($anterior === null || $posicion !== $anterior + 1) {
                    $zonas++;
                }

                $anterior = $posicion;
            }
        }

        return $zonas;
    }

    /**
     * Dosis de anestesia. Depende de la arcada.
     *
     * Arriba (cuadrantes 1 y 2) el hueso es poroso y cada grupo contiguo lleva
     * su dosis. Abajo (cuadrantes 3 y 4) el hueso es denso, la infiltración
     * local casi no funciona y la única vía es el bloqueo troncular, que duerme
     * el cuadrante completo: los dientes separados van en la misma dosis.
     *
     * Por eso 16 y 14 son 2 dosis, y 44 y 47 son 1.
     */
    public static function anesthesiaZones(array $raw): int
    {
        $dosis = 0;

        foreach (self::byQuadrant($raw) as $cuadrante => $posiciones) {
            if ($cuadrante <= 2) {
                $anterior = null;

                foreach ($posiciones as $posicion) {
                    if ($anterior === null || $posicion !== $anterior + 1) {
                        $dosis++;
                    }

                    $anterior = $posicion;
                }

                continue;
            }

            $dosis++;
        }

        return $dosis;
    }

    /**
     * Cuántas veces aplica una línea con este alcance.
     *
     * Es la única puerta: el cobro y el inventario usan la misma cuenta, así
     * que no pueden desfasarse.
     */
    public static function count(?string $scope, array $teeth): float
    {
        return match ($scope) {
            WorkUnit::TOOTH => (float) count(self::teeth($teeth)),
            WorkUnit::QUADRANT => (float) self::quadrants($teeth),
            WorkUnit::CONTIGUOUS_ZONE => (float) self::anesthesiaZones($teeth),
            default => 1.0,
        };
    }

    private static function isValidFdi(int $diente): bool
    {
        $cuadrante = intdiv($diente, 10);
        $posicion = $diente % 10;

        return $cuadrante >= 1 && $cuadrante <= 4 && $posicion >= 1 && $posicion <= 8;
    }
}
