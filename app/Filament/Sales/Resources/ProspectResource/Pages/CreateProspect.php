<?php

namespace App\Filament\Sales\Resources\ProspectResource\Pages;

use App\Filament\Sales\Resources\ProspectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProspect extends CreateRecord
{
    protected static string $resource = ProspectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-asignar al vendedor que está creando
        $data['assigned_to_sales_rep_id'] = auth()->id();
        $data['source'] = $data['source'] ?? 'prospecting';
        return $data;
    }
}
