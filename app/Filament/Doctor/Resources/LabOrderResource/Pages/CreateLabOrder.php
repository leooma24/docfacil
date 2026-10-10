<?php

namespace App\Filament\Doctor\Resources\LabOrderResource\Pages;

use App\Filament\Doctor\Resources\LabOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLabOrder extends CreateRecord
{
    protected static string $resource = LabOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
