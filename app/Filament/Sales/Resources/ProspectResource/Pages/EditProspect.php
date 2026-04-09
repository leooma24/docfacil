<?php

namespace App\Filament\Sales\Resources\ProspectResource\Pages;

use App\Filament\Sales\Resources\ProspectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProspect extends EditRecord
{
    protected static string $resource = ProspectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
