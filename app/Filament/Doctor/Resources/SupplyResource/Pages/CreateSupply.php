<?php

namespace App\Filament\Doctor\Resources\SupplyResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\SupplyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupply extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = SupplyResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;

        return $data;
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nuevo insumo',
            'icon'     => '📦',
            'kicker'   => '➕ Agregar al catálogo',
            'subtitle' => 'Con su punto de reorden y su unidad. El stock empieza en cero: se llena con el primer movimiento.',
            'gradient' => '#14b8a6 0%, #0d9488 40%, #0f766e 100%',
            'accent'   => '#14b8a6',
        ];
    }
}
