<?php

namespace App\Filament\Doctor\Resources\PaymentResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;

        return PaymentResource::cuadrarLoQueQuedo($data);
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nuevo cobro',
            'subtitle' => 'Registre un cobro. Puede enlazarlo a una cita y enviar link de pago por WhatsApp.',
            'gradient' => '#10b981 0%, #059669 40%, #047857 100%',
            'accent'   => '#059669',
        ];
    }
}
