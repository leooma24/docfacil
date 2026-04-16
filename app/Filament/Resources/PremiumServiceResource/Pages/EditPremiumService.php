<?php

namespace App\Filament\Resources\PremiumServiceResource\Pages;

use App\Filament\Resources\PremiumServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPremiumService extends EditRecord
{
    protected static string $resource = PremiumServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
