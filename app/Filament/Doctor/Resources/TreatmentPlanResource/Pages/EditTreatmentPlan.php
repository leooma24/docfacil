<?php

namespace App\Filament\Doctor\Resources\TreatmentPlanResource\Pages;

use App\Filament\Doctor\Resources\TreatmentPlanResource;
use App\Models\TreatmentPlan;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTreatmentPlan extends EditRecord
{
    protected static string $resource = TreatmentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        /** @var TreatmentPlan $record */
        $record = $this->record;
        $record->recalculateTotal();
    }
}
