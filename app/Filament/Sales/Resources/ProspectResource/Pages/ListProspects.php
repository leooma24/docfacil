<?php

namespace App\Filament\Sales\Resources\ProspectResource\Pages;

use App\Filament\Sales\Resources\ProspectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProspects extends ListRecords
{
    protected static string $resource = ProspectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
