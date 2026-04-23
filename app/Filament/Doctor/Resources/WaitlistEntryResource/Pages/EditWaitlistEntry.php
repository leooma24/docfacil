<?php

namespace App\Filament\Doctor\Resources\WaitlistEntryResource\Pages;

use App\Filament\Doctor\Resources\WaitlistEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWaitlistEntry extends EditRecord
{
    protected static string $resource = WaitlistEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
