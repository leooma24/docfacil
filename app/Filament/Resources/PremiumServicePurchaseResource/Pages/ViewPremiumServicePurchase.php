<?php

namespace App\Filament\Resources\PremiumServicePurchaseResource\Pages;

use App\Filament\Resources\PremiumServicePurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPremiumServicePurchase extends ViewRecord
{
    protected static string $resource = PremiumServicePurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
