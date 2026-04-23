<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Payment;
use Filament\Widgets\Widget;

/**
 * Pacientes con adeudos vencidos — vista rapida con click-to-WhatsApp
 * para recordatorio de cobro. Se muestra solo si hay al menos 1 adeudo
 * vencido en la clinica del doctor.
 */
class OverdueDebtorsWidget extends Widget
{
    protected static string $view = 'filament.doctor.widgets.overdue-debtors';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Payment::where('clinic_id', auth()->user()->clinic_id)
            ->overdue()
            ->exists();
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
