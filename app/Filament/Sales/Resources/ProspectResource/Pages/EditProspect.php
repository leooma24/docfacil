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

    /**
     * Defensa en profundidad: incluso si el front manda assigned_to_sales_rep_id
     * vía request manipulado, lo removemos antes de persistir. El getEloquentQuery
     * del Resource ya bloquea acceso a registros ajenos, pero un update dirigido
     * podría intentar reasignar. Esto lo previene.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['assigned_to_sales_rep_id']);
        unset($data['converted_clinic_id']);
        unset($data['converted_at']);
        return $data;
    }
}
