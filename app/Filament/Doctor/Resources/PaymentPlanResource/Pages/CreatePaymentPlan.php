<?php

namespace App\Filament\Doctor\Resources\PaymentPlanResource\Pages;

use App\Filament\Doctor\Resources\PaymentPlanResource;
use App\Models\PaymentPlan;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePaymentPlan extends CreateRecord
{
    protected static string $resource = PaymentPlanResource::class;

    protected static ?string $title = 'Nuevo plan de pagos';

    protected function fillForm(): void
    {
        parent::fillForm();

        // Desde un presupuesto aceptado (?presupuesto=) o desde el perfil (?patient=).
        $this->form->fill(array_merge(
            $this->form->getRawState(),
            array_filter(['patient_id' => request('patient')]),
            PaymentPlanResource::datosDesdePresupuesto((int) request('presupuesto') ?: null),
        ));
    }

    protected function handleRecordCreation(array $data): Model
    {
        $pagado = (bool) ($data['enganche_pagado'] ?? false);
        $metodo = $data['payment_method'] ?? 'cash';
        unset($data['enganche_pagado'], $data['payment_method']);

        return PaymentPlan::crear(
            $data + ['clinic_id' => auth()->user()->clinic_id, 'doctor_id' => auth()->user()->doctor?->id],
            $pagado ? $metodo : null,
        );
    }

    protected function getRedirectUrl(): string
    {
        return PaymentPlanResource::getUrl('view', ['record' => $this->record]);
    }
}
