<?php

namespace App\Support;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Payment;

/**
 * El estado de cuenta de un paciente en una línea: «Pagó $600 de $800, debe
 * $200» (10-oct-2026). Antes el saldo solo se veía sumando la pestaña Cobros.
 *
 * Cuenta lo que ya toca: las mensualidades de un plan que todavía no vencen
 * no son deuda de hoy (ver Payment::scopeYaToca). Lo devuelto no cuenta.
 */
class EstadoDeCuenta
{
    public static function de(Patient $paciente): array
    {
        $cobros = Payment::withoutGlobalScopes()
            ->where('clinic_id', $paciente->clinic_id)
            ->where('patient_id', $paciente->id)
            ->where('status', '!=', 'refunded')
            ->yaToca()
            ->with(['service', 'receipts'])
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get()
            ->map(function (Payment $c) {
                $total = round((float) $c->amount, 2);
                $pagado = $c->status === 'paid' ? $total : round(min($total, (float) $c->receipts->sum('amount')), 2);

                return [
                    'fecha' => $c->payment_date?->format('d/m/Y'),
                    'concepto' => $c->service?->name ?: ($c->notes ?: 'Consulta'),
                    'total' => $total,
                    'pagado' => $pagado,
                    'debe' => round($total - $pagado, 2),
                    'abonos' => $c->receipts->sortBy('paid_at')->map(fn ($r) => [
                        'fecha' => $r->paid_at?->format('d/m/Y'),
                        'forma' => CajaDelDia::FORMAS[$r->payment_method] ?? 'Otro',
                        'monto' => (float) $r->amount,
                    ])->values()->all(),
                ];
            });

        return [
            'paciente' => $paciente,
            'clinica' => Clinic::withoutGlobalScopes()->find($paciente->clinic_id),
            'cobros' => $cobros->all(),
            'total' => round($cobros->sum('total'), 2),
            'pagado' => round($cobros->sum('pagado'), 2),
            'debe' => round($cobros->sum('debe'), 2),
        ];
    }
}
