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
        return [
            Actions\Action::make('send_whatsapp')
                ->label('Enviar por WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->visible(fn () => ! empty($this->record->patient?->phone) && in_array($this->record->status, ['draft', 'sent']))
                ->action(function () {
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);

                    return TreatmentPlanResource::enviarPorWhatsapp($this->record->fresh());
                }),
            // El presupuesto aceptado de un tratamiento largo se paga en partes.
            Actions\Action::make('plan_de_pagos')
                ->label('Hacer plan de pagos')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->visible(fn () => $this->record->status === 'accepted')
                ->url(fn () => \App\Filament\Doctor\Resources\PaymentPlanResource::getUrl('create', ['presupuesto' => $this->record->id], panel: 'doctor')),
            Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn () => route('treatment-plan.pdf', $this->record))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var TreatmentPlan $record */
        $record = $this->record;
        $record->recalculateTotal();
    }
}
