<?php

namespace App\Filament\Doctor\Resources\SupplyResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\SupplyResource;
use App\Models\Supply;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupplies extends ListRecords
{
    use HasListHero;

    protected static string $resource = SupplyResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo Insumo'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;

        // Se traen completos para poder calcular stock y valor: son decenas de
        // insumos por consultorio, no miles.
        $insumos = Supply::where('clinic_id', $clinicId)->withStock()->get();

        $total = $insumos->count();
        $activos = $insumos->where('is_active', true)->count();
        $belowMinimum = $insumos->filter(fn (Supply $i) => $i->belowMinimum())->count();
        $valor = $insumos->sum(fn (Supply $i) => $i->stockOnHand() * (float) $i->cost_per_unit);

        return [
            'title'    => 'Insumos',
            'icon'     => '📦',
            'kicker'   => '🧾 Tu material',
            'subtitle' => 'Lo que tienes en el consultorio y lo que está por acabarse. El stock sale del kardex de movimientos.',
            'gradient' => '#14b8a6 0%, #0d9488 40%, #0f766e 100%',
            'accent'   => '#14b8a6',
            'stats' => [
                ['label' => '📦 Total',        'value' => number_format($total)],
                ['label' => '✅ Activos',      'value' => number_format($activos)],
                ['label' => '⚠️ Bajo mínimo',  'value' => number_format($belowMinimum)],
                ['label' => '💰 Valor',        'value' => '$' . number_format($valor)],
            ],
        ];
    }
}
