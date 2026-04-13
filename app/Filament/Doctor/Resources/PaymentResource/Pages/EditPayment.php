<?php

namespace App\Filament\Doctor\Resources\PaymentResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    use HasFormHero;

    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormHeroConfig(): array
    {
        $amount = number_format($this->record->amount ?? 0, 2);
        $patient = $this->record->patient ?? null;
        $name = $patient ? trim($patient->first_name . ' ' . $patient->last_name) : 'Cobro';

        return [
            'title'    => 'Editar cobro',
            'icon'     => '💰',
            'kicker'   => '✏️ ' . $name . ' · $' . $amount,
            'subtitle' => 'Actualiza monto, método de pago o estado del cobro.',
            'gradient' => '#10b981 0%, #059669 40%, #047857 100%',
            'accent'   => '#059669',
        ];
    }
}
