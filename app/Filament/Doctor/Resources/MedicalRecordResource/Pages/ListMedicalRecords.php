<?php

namespace App\Filament\Doctor\Resources\MedicalRecordResource\Pages;

use App\Filament\Doctor\Resources\MedicalRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicalRecords extends ListRecords
{
    protected static string $resource = MedicalRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nueva Consulta'),
        ];
    }
}
