<?php

namespace App\Filament\Doctor\Resources\SupplyMovementResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\SupplyMovementResource;
use App\Models\SupplyMovement;
use Filament\Resources\Pages\ListRecords;

class ListSupplyMovements extends ListRecords
{
    use HasListHero;

    protected static string $resource = SupplyMovementResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        // Sin acción de crear: se captura desde el catálogo de Insumos, donde
        // el insumo está enfrente y su stock se ve.
        return [];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;

        $delMes = SupplyMovement::where('clinic_id', $clinicId)
            ->whereMonth('occurred_at', now()->month)
            ->whereYear('occurred_at', now()->year);

        $movimientos = (clone $delMes)->count();
        $mermas = (clone $delMes)->where('type', 'waste')->count();
        $gastado = (clone $delMes)->where('type', 'in')
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(unit_cost, 0)), 0) as total')
            ->value('total');

        // Lo que se fue en mermas, en pesos: es el número que se lleva al
        // contador para deducirlo como pérdida permitida.
        $mermaEnPesos = (clone $delMes)->where('type', 'waste')
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(unit_cost, 0)), 0) as total')
            ->value('total');

        return [
            'title'    => 'Movimientos de insumos',
            'icon'     => '🔄',
            'kicker'   => '📋 El kardex',
            'subtitle' => 'Todo lo que entró, salió o se perdió. Es de solo lectura: un movimiento es un hecho, y corregirlo es agregar otro.',
            'gradient' => '#64748b 0%, #475569 40%, #334155 100%',
            'accent'   => '#64748b',
            'stats' => [
                ['label' => '📋 Este mes',       'value' => number_format($movimientos)],
                ['label' => '💸 Comprado',       'value' => '$' . number_format((float) $gastado)],
                ['label' => '⚠️ Merma del mes',  'value' => '$' . number_format((float) $mermaEnPesos)],
                ['label' => '🧾 Mermas',         'value' => number_format($mermas)],
            ],
        ];
    }
}
