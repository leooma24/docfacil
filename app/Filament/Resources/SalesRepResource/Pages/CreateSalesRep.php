<?php

namespace App\Filament\Resources\SalesRepResource\Pages;

use App\Filament\Resources\SalesRepResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesRep extends CreateRecord
{
    protected static string $resource = SalesRepResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'sales';
        $data['is_active_sales_rep'] = $data['is_active_sales_rep'] ?? true;
        return $data;
    }
}
