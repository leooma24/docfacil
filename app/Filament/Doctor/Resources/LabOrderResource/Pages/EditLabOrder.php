<?php

namespace App\Filament\Doctor\Resources\LabOrderResource\Pages;

use App\Filament\Doctor\Resources\LabOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLabOrder extends EditRecord
{
    protected static string $resource = LabOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
