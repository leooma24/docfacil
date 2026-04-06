<?php

namespace App\Filament\Doctor\Resources\ConsentFormResource\Pages;

use App\Filament\Doctor\Resources\ConsentFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConsentForm extends EditRecord
{
    protected static string $resource = ConsentFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
