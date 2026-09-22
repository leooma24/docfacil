<?php

namespace App\Support;

use App\Models\ConsultationProcedure;
use Illuminate\Support\Collection;

/**
 * Lo que una consulta va a descontar del inventario.
 *
 * El sistema propone y el doctor confirma. El trabajo de esto no es acertar
 * siempre, es que la propuesta se pueda revisar en cinco segundos: por eso cada
 * línea dice de dónde salió su cantidad.
 *
 * Hay dos decisiones que hacen que la cuenta sea correcta, y las dos son
 * fáciles de equivocar:
 *
 *  1. **Se agrupa por servicio, no por renglón.** La contigüidad de los dientes
 *     cruza los renglones: resina en el 16 y en el 15 son dos procedimientos
 *     pero UNA zona de anestesia. Calculados por separado darían dos.
 *
 *  2. **Las líneas por visita se cuentan una vez para toda la consulta.** La
 *     visita pasa una sola vez: si dos servicios piden guantes, no se gastan el
 *     doble. Se toma la mayor de las dos cantidades.
 */
class SupplyProposal
{
    /**
     * @param  Collection<int, ConsultationProcedure>|array  $procedures
     * @return array<int, array{supply: \App\Models\Supply, quantity: float, scope: string, detail: string, optional: bool}>
     */
    public static function for(Collection|array $procedures): array
    {
        $procedures = collect($procedures);

        if ($procedures->isEmpty()) {
            return [];
        }

        $porInsumo = [];

        foreach ($procedures->groupBy('service_id') as $delServicio) {
            $servicio = $delServicio->first()->service;

            if (! $servicio) {
                continue;
            }

            $dientes = SupplyScope::teeth($delServicio->pluck('tooth_number')->all());

            foreach ($servicio->recipe()->with('supply')->get() as $linea) {
                if (! $linea->supply) {
                    continue;
                }

                $alcance = $linea->effectiveScope();
                $id = $linea->supply_id;

                $porInsumo[$id] ??= [
                    'supply' => $linea->supply,
                    'scope' => $alcance,
                    'quantity' => 0.0,
                    'optional' => true,
                    'origenes' => [],
                ];

                // Va ANTES de la rama: si se saltara en las líneas de visita,
                // los guantes y las puntas —que siempre se usan— vendrían
                // desmarcados y el doctor los tendría que palomear a mano.
                $porInsumo[$id]['optional'] = $porInsumo[$id]['optional'] && $linea->is_optional;

                if ($alcance === WorkUnit::VISIT) {
                    $cantidad = round((float) $linea->quantity * (float) $linea->waste_factor, 3);

                    // Una vez por consulta; entre servicios, la mayor.
                    $porInsumo[$id]['quantity'] = max($porInsumo[$id]['quantity'], $cantidad);
                    $porInsumo[$id]['origenes'][] = $servicio->name . ': una vez por consulta';

                    continue;
                }

                $veces = SupplyScope::count($alcance, $dientes);

                $porInsumo[$id]['quantity'] = round(
                    $porInsumo[$id]['quantity'] + (float) $linea->quantity * $veces * (float) $linea->waste_factor,
                    3,
                );

                $porInsumo[$id]['origenes'][] = $servicio->name . ': ' . self::inWords($alcance, $veces);
            }
        }

        return collect($porInsumo)
            ->filter(fn (array $linea) => $linea['quantity'] > 0)
            ->map(fn (array $linea) => [
                'supply' => $linea['supply'],
                'quantity' => $linea['quantity'],
                'scope' => $linea['scope'],
                'detail' => implode('; ', array_unique($linea['origenes'])),
                'optional' => $linea['optional'],
            ])
            ->sortBy(fn (array $linea) => $linea['supply']->name)
            ->values()
            ->all();
    }

    /** La cantidad, dicha como la cuenta que la produjo. */
    private static function inWords(string $scope, float $occurrences): string
    {
        $cantidad = rtrim(rtrim(number_format($occurrences, 3, '.', ''), '0'), '.');

        return match ($scope) {
            WorkUnit::TOOTH => $occurrences == 1 ? '1 diente' : "{$cantidad} dientes",
            WorkUnit::QUADRANT => $occurrences == 1 ? '1 cuadrante' : "{$cantidad} cuadrantes",
            WorkUnit::CONTIGUOUS_ZONE => $occurrences == 1 ? '1 zona contigua' : "{$cantidad} zonas contiguas",
            default => '1 visita',
        };
    }
}
