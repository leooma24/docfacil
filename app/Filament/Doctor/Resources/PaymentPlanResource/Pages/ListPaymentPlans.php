<?php

namespace App\Filament\Doctor\Resources\PaymentPlanResource\Pages;

use App\Filament\Doctor\Resources\PaymentPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentPlans extends ListRecords
{
    protected static string $resource = PaymentPlanResource::class;

    // Filament pone mayúscula a cada palabra del nombre: "Planes De Pago".
    protected static ?string $title = 'Planes de pago';

    public function getBreadcrumb(): ?string
    {
        return 'Lista';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nuevo plan de pagos')];
    }
}
