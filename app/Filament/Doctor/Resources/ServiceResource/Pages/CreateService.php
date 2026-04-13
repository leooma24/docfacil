<?php

namespace App\Filament\Doctor\Resources\ServiceResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = ServiceResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;

        return $data;
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nuevo servicio',
            'icon'     => '🩺',
            'kicker'   => '➕ Agregar al catálogo',
            'subtitle' => 'Nombre, precio y duración del servicio. Se usa al agendar citas y registrar cobros.',
            'gradient' => '#f59e0b 0%, #f97316 40%, #ea580c 100%',
            'accent'   => '#f59e0b',
        ];
    }
}
