<?php

namespace App\Filament\Doctor\Resources\PaymentPlanResource\Pages;

use App\Filament\Doctor\Resources\PaymentPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentPlans extends ListRecords
{
    protected static string $resource = PaymentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nuevo plan de pagos')];
    }
}
