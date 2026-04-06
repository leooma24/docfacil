<?php

namespace App\Filament\Doctor\Resources\ConsentFormResource\Pages;

use App\Filament\Doctor\Resources\ConsentFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConsentForms extends ListRecords
{
    protected static string $resource = ConsentFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo Consentimiento'),
        ];
    }
}
