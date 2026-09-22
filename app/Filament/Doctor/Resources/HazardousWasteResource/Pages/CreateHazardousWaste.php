<?php

namespace App\Filament\Doctor\Resources\HazardousWasteResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\HazardousWasteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHazardousWaste extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = HazardousWasteResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Registrar residuo',
            'icon'     => '⚠️',
            'kicker'   => '🧾 Cumplimiento',
            'subtitle' => 'Qué residuo, cuánto y en qué contenedor. El número de manifiesto se puede anotar después.',
            'gradient' => '#f59e0b 0%, #d97706 40%, #b45309 100%',
            'accent'   => '#f59e0b',
        ];
    }
}
