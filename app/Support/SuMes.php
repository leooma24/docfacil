<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\PaymentReceipt;
use App\Models\TreatmentPlan;
use Carbon\CarbonInterface;

/**
 * Lo que pasó este mes en el consultorio, contra el mes pasado.
 *
 * Del dentista (10-oct-2026): "tendría que verlo en números, no que me lo
 * platiquen". Solo se cuenta lo que pasó: recordatorios mandados, citas que
 * el paciente confirmó, inasistencias, presupuestos aceptados y lo cobrado de
 * saldos que venían de antes. Nada de "le ahorró $X": eso sería inventar.
 */
class SuMes
{
    /**
     * @return array<string, array{este: int|float, pasado: int|float}>
     */
    public static function de(int $clinicId, CarbonInterface $cuando): array
    {
        $este = [$cuando->copy()->startOfMonth(), $cuando->copy()->endOfMonth()];
        $pasado = [$cuando->copy()->subMonthNoOverflow()->startOfMonth(), $cuando->copy()->subMonthNoOverflow()->endOfMonth()];

        $par = fn (callable $f) => ['este' => $f(...$este), 'pasado' => $f(...$pasado)];
        $citas = fn () => Appointment::withoutGlobalScopes()->where('clinic_id', $clinicId);
        $planes = fn ($desde, $hasta) => TreatmentPlan::withoutGlobalScopes()->where('clinic_id', $clinicId)->whereBetween('accepted_at', [$desde, $hasta]);

        return [
            'recordatorios' => $par(fn ($d, $h) => $citas()->whereBetween('reminder_sent_at', [$d, $h])->count()),
            'confirmaron' => $par(fn ($d, $h) => $citas()->whereBetween('confirmed_at', [$d, $h])->count()),
            'inasistencias' => $par(fn ($d, $h) => $citas()->where('status', 'no_show')->whereBetween('starts_at', [$d, $h])->count()),
            'presupuestos' => $par(fn ($d, $h) => $planes($d, $h)->count()),
            'presupuestosMonto' => $par(fn ($d, $h) => round((float) $planes($d, $h)->sum('total'), 2)),
            // Lo que entró este mes de cobros que venían de meses anteriores:
            // el saldo que antes se quedaba en la libreta.
            'cobradoDeAntes' => $par(fn ($d, $h) => round((float) PaymentReceipt::withoutGlobalScopes()
                ->where('payment_receipts.clinic_id', $clinicId)
                ->whereBetween('payment_receipts.paid_at', [$d, $h])
                ->join('payments', 'payments.id', '=', 'payment_receipts.payment_id')
                ->whereDate('payments.payment_date', '<', $d->toDateString())
                ->sum('payment_receipts.amount'), 2)),
        ];
    }
}
