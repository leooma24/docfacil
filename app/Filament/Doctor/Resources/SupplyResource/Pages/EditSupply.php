<?php

namespace App\Filament\Doctor\Resources\SupplyResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\SupplyResource;
use Filament\Resources\Pages\EditRecord;

class EditSupply extends EditRecord
{
    use HasFormHero;

    protected static string $resource = SupplyResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Editar insumo',
            'icon'     => '📦',
            'kicker'   => '✏️ Ajustar el catálogo',
            'subtitle' => 'Cambiar el punto de reorden o la conversión de compra. El stock no se toca aquí: se mueve con movimientos.',
            'gradient' => '#14b8a6 0%, #0d9488 40%, #0f766e 100%',
            'accent'   => '#14b8a6',
        ];
    }
}
