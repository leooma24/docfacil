<?php

namespace App\Filament\Resources\SalesRepResource\Pages;

use App\Filament\Resources\SalesRepResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesRep extends CreateRecord
{
    protected static string $resource = SalesRepResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // role está en $guarded — debemos usar forceFill
        $user = new User();
        $user->forceFill(array_merge($data, [
            'role' => 'sales',
            'is_active_sales_rep' => $data['is_active_sales_rep'] ?? true,
        ]))->save();
        return $user;
    }
}
