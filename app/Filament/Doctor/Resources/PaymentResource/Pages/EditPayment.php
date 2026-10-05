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
            \App\Filament\Doctor\Actions\CobrarAbono::make('registrarAbono', fn () => collect([$this->record->fresh()]))
                ->label('Registrar pago')
                ->visible(fn () => $this->record->remaining > 0 && $this->record->status !== 'paid')
                // El formulario se pone al día: si luego le dan Guardar, no
                // regresa lo pagado al saldo de antes.
                ->after(function () {
                    $this->record->refresh();
                    $this->refreshFormData(['amount_paid', 'status']);
                }),
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
