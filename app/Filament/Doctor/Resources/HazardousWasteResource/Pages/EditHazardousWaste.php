<?php

namespace App\Filament\Doctor\Resources\HazardousWasteResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\HazardousWasteResource;
use Filament\Resources\Pages\EditRecord;

class EditHazardousWaste extends EditRecord
{
    use HasFormHero;

    protected static string $resource = HazardousWasteResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Editar residuo',
            'icon'     => '⚠️',
            'kicker'   => '✏️ Corregir el registro',
            'subtitle' => 'Aquí sí se puede corregir: es un registro de cumplimiento, no un movimiento de inventario.',
            'gradient' => '#f59e0b 0%, #d97706 40%, #b45309 100%',
            'accent'   => '#f59e0b',
        ];
    }
}
