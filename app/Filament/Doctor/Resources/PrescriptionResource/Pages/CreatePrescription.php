<?php

namespace App\Filament\Doctor\Resources\PrescriptionResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\PrescriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrescription extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = PrescriptionResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;

        return $data;
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nueva receta',
            'icon'     => '💊',
            'kicker'   => '➕ Crear receta',
            'subtitle' => 'Genera una receta con tu cédula, clínica y firma. Descargable como PDF al guardar.',
            'gradient' => '#8b5cf6 0%, #a855f7 40%, #c084fc 100%',
            'accent'   => '#8b5cf6',
        ];
    }
}
