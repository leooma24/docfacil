<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Los festivos de México de un año, para proponérselos al consultorio
 * (día 6 del plan, 10-oct-2026). Son propuestas: nada se cierra solo.
 *
 * Oficiales: los descansos obligatorios de la Ley Federal del Trabajo
 * (art. 74), con los lunes que la ley mueve. De costumbre: los que muchos
 * consultorios cierran aunque la ley no lo pida.
 */
class FestivosDeMexico
{
    /** @return list<array{fecha: string, nombre: string, oficial: bool}> */
    public static function de(int $anio): array
    {
        $lunes = fn (int $mes, string $cual) => CarbonImmutable::create($anio, $mes, 1)->modify("{$cual} monday of this month");
        $pascua = self::domingoDePascua($anio);

        $festivos = [
            [CarbonImmutable::create($anio, 1, 1), 'Año Nuevo', true],
            [$lunes(2, 'first'), 'Día de la Constitución', true],
            [$lunes(3, 'third'), 'Natalicio de Benito Juárez', true],
            [$pascua->subDays(3), 'Jueves Santo', false],
            [$pascua->subDays(2), 'Viernes Santo', false],
            [CarbonImmutable::create($anio, 5, 1), 'Día del Trabajo', true],
            [CarbonImmutable::create($anio, 9, 16), 'Independencia', true],
            [CarbonImmutable::create($anio, 11, 2), 'Día de Muertos', false],
            [$lunes(11, 'third'), 'Revolución Mexicana', true],
            [CarbonImmutable::create($anio, 12, 12), 'Día de la Virgen de Guadalupe', false],
            [CarbonImmutable::create($anio, 12, 25), 'Navidad', true],
        ];
        // Cambio de Poder Ejecutivo: 1 de octubre, cada seis años (2030, 2036…).
        if (($anio - 2024) % 6 === 0) {
            $festivos[] = [CarbonImmutable::create($anio, 10, 1), 'Cambio de Poder Ejecutivo Federal', true];
        }

        return collect($festivos)
            ->sortBy(fn ($f) => $f[0]->timestamp)
            ->map(fn ($f) => ['fecha' => $f[0]->toDateString(), 'nombre' => $f[1], 'oficial' => $f[2]])
            ->values()->all();
    }

    /** Los que vienen de hoy a un año. */
    public static function proximos(): array
    {
        $hoy = today()->toDateString();
        $limite = today()->addYear()->toDateString();

        return collect([...self::de((int) today()->year), ...self::de((int) today()->year + 1)])
            ->filter(fn ($f) => $f['fecha'] >= $hoy && $f['fecha'] <= $limite)
            ->values()->all();
    }

    /** Domingo de Pascua (cómputo gregoriano anónimo), sin la extensión calendar. */
    private static function domingoDePascua(int $y): CarbonImmutable
    {
        $a = $y % 19;
        $b = intdiv($y, 100);
        $c = $y % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return CarbonImmutable::create($y, $mes, $dia);
    }
}
