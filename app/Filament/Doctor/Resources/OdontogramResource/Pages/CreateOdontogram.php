<?php

namespace App\Filament\Doctor\Resources\OdontogramResource\Pages;

use App\Filament\Doctor\Resources\OdontogramResource;
use App\Models\OdontogramTooth;
use Filament\Resources\Pages\CreateRecord;

class CreateOdontogram extends CreateRecord
{
    protected static string $resource = OdontogramResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Redirect to edit so user can use the odontogram editor
        $this->redirect(OdontogramResource::getUrl('edit', ['record' => $this->record]));
    }
}
