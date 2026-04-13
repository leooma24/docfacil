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
            'icon'     => '✍️',
            'kicker'   => '➕ Crear consentimiento',
            'subtitle' => 'Genera el texto del consentimiento. El paciente firma digital con dedo en tablet o celular.',
            'gradient' => '#6366f1 0%, #8b5cf6 40%, #a855f7 100%',
            'accent'   => '#6366f1',
        ];
    }
}
