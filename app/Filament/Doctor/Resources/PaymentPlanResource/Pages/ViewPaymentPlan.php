<?php

namespace App\Filament\Doctor\Resources\PaymentPlanResource\Pages;

use App\Filament\Doctor\Resources\PaymentPlanResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentPlan extends ViewRecord
{
    protected static string $resource = PaymentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('cancelar')
                ->label('Cancelar plan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status !== 'cancelled')
                ->requiresConfirmation()
                ->modalHeading('¿Cancelar este plan de pagos?')
                ->modalDescription('Se quitan las mensualidades que no se han pagado. Lo que el paciente ya pagó se queda en la caja. Si el plan estaba mal, después puede hacer uno nuevo.')
                ->modalSubmitActionLabel('Sí, cancelarlo')
                ->action(function () {
                    $this->record->cancelar();
                    \Filament\Notifications\Notification::make()->title('Plan cancelado')->success()->send();
                }),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->description . ' — ' . $this->record->patient?->full_name;
    }
}
