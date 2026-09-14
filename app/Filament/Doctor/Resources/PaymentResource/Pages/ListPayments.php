<?php

namespace App\Filament\Doctor\Resources\PaymentResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    use HasListHero;

    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo Cobro'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;

        // Los mismos cálculos que el escritorio y el corte: aquí contaban
        // solo los liquidados, y un abono de hoy no aparecía en "Cobrado hoy".
        $today = Payment::cobradoEntre($clinicId, today(), today());
        $month = Payment::cobradoEntre($clinicId, now()->startOfMonth(), now()->endOfMonth());
        $pending = Payment::saldoPorCobrar($clinicId);
        $countMonth = Payment::where('clinic_id', $clinicId)
            ->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->where(fn ($q) => $q->where('status', 'paid')->orWhere('amount_paid', '>', 0))
            ->count();

        return [
            'title'    => 'Cobros',
            'icon'     => '💰',
            'kicker'   => '💳 Tu flujo de caja',
            'subtitle' => 'Todos los cobros realizados y pendientes. Registra pagos en efectivo o envía links por WhatsApp.',
            'gradient' => '#10b981 0%, #059669 40%, #047857 100%',
            'accent'   => '#059669',
            'stats' => [
                ['label' => '💵 Cobrado hoy',     'value' => '$' . number_format($today)],
                ['label' => '📅 Este mes',         'value' => '$' . number_format($month)],
                ['label' => '⏳ Pendiente',        'value' => '$' . number_format($pending)],
                ['label' => '🧾 Pagos del mes',    'value' => number_format($countMonth)],
            ],
        ];
    }
}
