<?php

namespace App\Filament\Doctor\Resources\MedicalRecordResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\MedicalRecordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicalRecord extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = MedicalRecordResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;

        return $data;
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nueva consulta',
            'icon'     => '📋',
            'kicker'   => '➕ Registrar consulta',
            'subtitle' => 'Registra motivo, diagnóstico, tratamiento y notas. Cumple con NOM-004 y es inmutable al guardar.',
            'gradient' => '#ef4444 0%, #f97316 40%, #f59e0b 100%',
            'accent'   => '#ef4444',
        ];
    }
}
