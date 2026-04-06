<?php

namespace App\Filament\Doctor\Resources\ConsentFormResource\Pages;

use App\Filament\Doctor\Resources\ConsentFormResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConsentForm extends CreateRecord
{
    protected static string $resource = ConsentFormResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;
        $data['content'] = strip_tags($data['content'] ?? '', '<p><br><ul><ol><li><strong><em><u><h1><h2><h3><h4>');

        return $data;
    }
}
