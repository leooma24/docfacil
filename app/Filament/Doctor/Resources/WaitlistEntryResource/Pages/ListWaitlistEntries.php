<?php

namespace App\Filament\Doctor\Resources\WaitlistEntryResource\Pages;

use App\Filament\Doctor\Resources\WaitlistEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaitlistEntries extends ListRecords
{
    protected static string $resource = WaitlistEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Agregar a lista de espera'),
        ];
    }
}
