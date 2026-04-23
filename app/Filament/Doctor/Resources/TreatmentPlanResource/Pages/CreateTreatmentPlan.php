<?php

namespace App\Filament\Doctor\Resources\TreatmentPlanResource\Pages;

use App\Filament\Doctor\Resources\TreatmentPlanResource;
use App\Models\TreatmentPlan;
use Filament\Resources\Pages\CreateRecord;

class CreateTreatmentPlan extends CreateRecord
{
    protected static string $resource = TreatmentPlanResource::class;

    protected function afterCreate(): void
    {
        /** @var TreatmentPlan $record */
        $record = $this->record;
        $record->recalculateTotal();
    }
}
