<?php

namespace App\Support;

use App\Models\Doctor;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * La comisión de un doctor en un periodo (día 8 del plan, 10-oct-2026).
 *
 * Como en Dentalink: un porcentaje general y montos fijos por tratamiento,
 * y se paga solo lo ya cobrado. Cada abono que entró en el periodo le da su
 * parte: si la resina de $1,200 al 30% le deja $360 y el paciente pagó la
 * mitad, le tocan $180 ahora y $180 cuando pague el resto.
 *
 * El cobro es del doctor de su cita o, si es de un plan de pagos, del doctor
 * del plan. Los anticipos no cuentan: el dinero se cuenta cuando se usa para
 * pagar un tratamiento.
 */
class LiquidacionDelDoctor
{
    /** Los cobros de tratamientos de ese doctor. */
    public static function cobrosDe(Doctor $doctor): Collection
    {
        return Payment::withoutGlobalScopes()
            ->with(['service', 'patient' => fn ($q) => $q->withoutGlobalScopes(), 'receipts'])
            ->where('clinic_id', $doctor->clinic_id)
            ->where('es_anticipo', false)
            ->where('status', '!=', 'refunded')
            ->where(fn ($q) => $q
                ->whereHas('appointment', fn ($c) => $c->withoutGlobalScopes()->where('doctor_id', $doctor->id))
                ->orWhereHas('paymentPlan', fn ($p) => $p->withoutGlobalScopes()->where('doctor_id', $doctor->id)))
            ->orderBy('payment_date')->orderBy('id')
            ->get();
    }

    /** Cuánto le toca al doctor de ese cobro completo, y cómo se calculó. */
    public static function comisionDe(Doctor $doctor, Payment $cobro): array
    {
        $montos = (array) ($doctor->comision_montos ?? []);
        $total = round((float) $cobro->amount, 2);

        if ($cobro->service_id && isset($montos[$cobro->service_id]) && (float) $montos[$cobro->service_id] > 0) {
            $comision = round(min($total, (float) $montos[$cobro->service_id]), 2);

            return [$comision, 'Monto fijo por ' . ($cobro->service?->name ?? 'este tratamiento') . ': $' . number_format($comision, 0)];
        }

        $porcentaje = (float) ($doctor->comision_porcentaje ?? 0);
        $comision = round($total * $porcentaje / 100, 2);

        return [$comision, self::pct($porcentaje) . '% de $' . number_format($total, 0) . ' = $' . number_format($comision, 0)];
    }

    /**
     * @return array{renglones: list<array>, total: float, porCobrar: float}
     */
    public static function de(Doctor $doctor, CarbonInterface $desde, CarbonInterface $hasta): array
    {
        $ini = $desde->format('Y-m-d') . ' 00:00:00';
        $fin = $hasta->format('Y-m-d') . ' 23:59:59';
        $renglones = [];
        $porCobrar = 0.0;

        foreach (self::cobrosDe($doctor) as $cobro) {
            [$comision, $como] = self::comisionDe($doctor, $cobro);
            $total = round((float) $cobro->amount, 2);
            $factor = $total > 0 ? $comision / $total : 0;
            $pagadoTotal = $cobro->status === 'paid' ? $total : round(min($total, (float) $cobro->receipts->sum('amount')), 2);
            $enPeriodo = round((float) $cobro->receipts->filter(fn (PaymentReceipt $r) => $r->paid_at && $r->paid_at->between($ini, $fin))->sum('amount'), 2);
            $porCobrar += round(($total - $pagadoTotal) * $factor, 2);

            $nacioEnPeriodo = $cobro->payment_date && $cobro->payment_date->between($ini, $fin);
            if ($enPeriodo == 0.0 && ! $nacioEnPeriodo) {
                continue;
            }

            $ganado = round($enPeriodo * $factor, 2);
            $renglones[] = [
                'cobro' => $cobro,
                'paciente' => trim(($cobro->patient?->first_name ?? '') . ' ' . ($cobro->patient?->last_name ?? '')),
                'concepto' => $cobro->service?->name ?: ($cobro->notes ?: 'Consulta'),
                'total' => $total,
                'comision' => $comision,
                'pagado' => $pagadoTotal,
                'enPeriodo' => $enPeriodo,
                'ganado' => $ganado,
                'semaforo' => $pagadoTotal >= $total ? 'pagado' : ($pagadoTotal > 0 ? 'parcial' : 'sin_pagar'),
                'explicacion' => $como . '. El paciente ha pagado $' . number_format($pagadoTotal, 0) . ' de $' . number_format($total, 0)
                    . ($enPeriodo != $pagadoTotal ? ' ($' . number_format($enPeriodo, 0) . ' en este periodo)' : '')
                    . ', así que le tocan $' . number_format($ganado, 0)
                    . ($pagadoTotal < $total ? ' ahora y $' . number_format(round(($total - $pagadoTotal) * $factor, 2), 0) . ' cuando termine de pagar.' : '.'),
            ];
        }

        return [
            'renglones' => $renglones,
            'total' => round(array_sum(array_column($renglones, 'ganado')), 2),
            'porCobrar' => round($porCobrar, 2),
        ];
    }

    private static function pct(float $p): string
    {
        return rtrim(rtrim(number_format($p, 2), '0'), '.');
    }
}
