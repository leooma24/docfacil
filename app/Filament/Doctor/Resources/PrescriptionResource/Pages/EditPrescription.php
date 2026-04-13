<?php

namespace App\Filament\Doctor\Resources\PrescriptionResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\PrescriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrescription extends EditRecord
{
    use HasFormHero;

    protected static string $resource = PrescriptionResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormHeroConfig(): array
    {
        $patient = $this->record->patient ?? null;
        $name = $patient ? trim($patient->first_name . ' ' . $patient->last_name) : 'Receta';

        return [
            'title'    => 'Editar receta',
            'icon'     => '💊',
            'kicker'   => '✏️ ' . $name,
            'subtitle' => 'Actualiza medicamentos, dosis o notas de la receta.',
            'gradient' => '#8b5cf6 0%, #a855f7 40%, #c084fc 100%',
            'accent'   => '#8b5cf6',
        ];
    }
}
