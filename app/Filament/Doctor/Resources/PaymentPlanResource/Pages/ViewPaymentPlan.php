<?php

namespace App\Filament\Doctor\Resources\PaymentPlanResource\Pages;

use App\Filament\Doctor\Resources\PaymentPlanResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentPlan extends ViewRecord
{
    protected static string $resource = PaymentPlanResource::class;

    public function getTitle(): string
    {
        return $this->record->description . ' — ' . $this->record->patient?->full_name;
    }
}
