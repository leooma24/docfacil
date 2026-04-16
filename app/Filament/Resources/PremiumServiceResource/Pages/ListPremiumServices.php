<?php

namespace App\Filament\Resources\PremiumServiceResource\Pages;

use App\Filament\Resources\PremiumServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPremiumServices extends ListRecords
{
    protected static string $resource = PremiumServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
