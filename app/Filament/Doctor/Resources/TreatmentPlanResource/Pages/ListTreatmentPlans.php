<?php

namespace App\Filament\Doctor\Resources\TreatmentPlanResource\Pages;

use App\Filament\Doctor\Resources\TreatmentPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTreatmentPlans extends ListRecords
{
    protected static string $resource = TreatmentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nuevo presupuesto')];
    }
}
