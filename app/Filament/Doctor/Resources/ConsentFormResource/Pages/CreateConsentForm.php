<?php

namespace App\Filament\Doctor\Resources\ConsentFormResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\ConsentFormResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConsentForm extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = ConsentFormResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    /** Desde el perfil del paciente (?patient=) llega con él puesto. */
    protected function fillForm(): void
    {
        parent::fillForm();

        $paciente = \App\Models\Patient::where('clinic_id', auth()->user()->clinic_id)->find(request('patient'));
        if ($paciente) {
            $this->form->fill(array_merge($this->form->getRawState(), ['patient_id' => $paciente->id]));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;
        $data['content'] = strip_tags($data['content'] ?? '', '<p><br><ul><ol><li><strong><em><u><h1><h2><h3><h4>');

        return $data;
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nuevo consentimiento',
            'subtitle' => 'Escriba el procedimiento y el texto. El paciente firma con el dedo en la tablet o el celular.',
            'gradient' => '#6366f1 0%, #8b5cf6 40%, #a855f7 100%',
            'accent'   => '#6366f1',
        ];
    }
}
