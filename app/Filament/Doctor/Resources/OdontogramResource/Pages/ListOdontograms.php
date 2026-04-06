<?php

namespace App\Filament\Doctor\Resources\OdontogramResource\Pages;

use App\Filament\Doctor\Resources\OdontogramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOdontograms extends ListRecords
{
    protected static string $resource = OdontogramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo Odontograma'),
        ];
    }
}
