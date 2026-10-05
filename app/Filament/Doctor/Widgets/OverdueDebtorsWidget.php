<?php

namespace App\Filament\Doctor\Widgets;

use App\Filament\Doctor\Actions\CobrarAbono;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;

/**
 * Pacientes con adeudos vencidos. "Cobrar" registra el pago ahí mismo con lo
 * que debe ya puesto; "Recordarle" abre WhatsApp con el mensaje. Se muestra
 * solo si hay al menos 1 adeudo vencido en la clinica del doctor.
 */
class OverdueDebtorsWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $view = 'filament.doctor.widgets.overdue-debtors';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Payment::where('clinic_id', auth()->user()->clinic_id)
            ->overdue()
            ->exists();
    }

    public function cobrarAction(): Action
    {
        return CobrarAbono::make('cobrar', fn (array $arguments) => Payment::where('clinic_id', auth()->user()->clinic_id)
            ->whereKey($arguments['payment'] ?? null)
            ->get());
    }

    public function getViewData(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $clinic = auth()->user()->clinic;
        $clinicName = $clinic?->name ?? 'tu consultorio';

        $payments = Payment::where('clinic_id', $clinicId)
            ->overdue()
            ->with(['patient', 'service'])
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->map(function (Payment $payment) use ($clinicName) {
                $patient = $payment->patient;
                $phoneDigits = preg_replace('/\D/', '', (string) ($patient?->phone ?? ''));
                if (strlen($phoneDigits) === 10) $phoneDigits = '52' . $phoneDigits;

                $remaining = number_format((float) $payment->remaining, 2);
                $daysOverdue = (int) now()->diffInDays($payment->due_date, false) * -1;
                $firstName = $patient?->first_name ?: 'hola';
                $servicePart = $payment->service?->name
                    ? " de *{$payment->service->name}*"
                    : '';

                $msg = "Hola {$firstName}, te escribo de *{$clinicName}*. Tienes un saldo pendiente{$servicePart} de *\${$remaining} MXN* con fecha límite del " . $payment->due_date->format('d/m/Y') . ".\n\nSi ya lo pagaste, avísame y lo descuento. Si no, cuando te acomode pasa o me avisas y lo ajustamos. ¡Gracias!";

                return [
                    'id' => $payment->id,
                    'name' => trim(($patient?->first_name ?? '') . ' ' . ($patient?->last_name ?? '')),
                    'remaining' => $remaining,
                    'days_overdue' => $daysOverdue,
                    'due_date' => $payment->due_date->format('d/m/Y'),
                    'wa_url' => !empty($phoneDigits) && strlen($phoneDigits) >= 12
                        ? "https://wa.me/{$phoneDigits}?text=" . urlencode($msg)
                        : null,
                ];
            });

        $totalOverdue = Payment::where('clinic_id', $clinicId)
            ->overdue()
            ->sum(\Illuminate\Support\Facades\DB::raw('amount - amount_paid'));

        return [
            'payments' => $payments,
            'total_overdue' => $totalOverdue,
        ];
    }
}
