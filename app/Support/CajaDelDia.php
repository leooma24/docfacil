<?php

namespace App\Support;

use App\Models\Clinic;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * La caja del día: lo que entró, por forma de pago.
 *
 * Del dentista (10-oct-2026): "Lupita cuadra la caja contra la libreta y casi
 * nunca cuadra exacto, porque alguien pagó con transferencia y no lo anotó".
 * Cuenta cada abono el día que entró (los recibos), igual que
 * Payment::cobradoEntre(): el corte del mes y la caja del día no pueden decir
 * números distintos.
 */
class CajaDelDia
{
    public const FORMAS = [
        'cash' => 'Efectivo',
        'card' => 'Tarjeta',
        'transfer' => 'Transferencia',
        'other' => 'Otro',
    ];

    /**
     * @return array{porMetodo: array<string, float>, total: float, movimientos: Collection<int, array>}
     */
    public static function de(int $clinicId, CarbonInterface $dia): array
    {
        $recibos = PaymentReceipt::withoutGlobalScopes()
            ->with(['payment' => fn ($q) => $q->withoutGlobalScopes()->with(['patient' => fn ($p) => $p->withoutGlobalScopes(), 'service'])])
            ->where('clinic_id', $clinicId)
            ->whereBetween('paid_at', [$dia->format('Y-m-d') . ' 00:00:00', $dia->format('Y-m-d') . ' 23:59:59'])
            ->orderBy('paid_at')
            ->get();

        $porMetodo = array_fill_keys(array_keys(self::FORMAS), 0.0);
        foreach ($recibos as $r) {
            $clave = array_key_exists($r->payment_method, $porMetodo) ? $r->payment_method : 'other';
            $porMetodo[$clave] = round($porMetodo[$clave] + (float) $r->amount, 2);
        }

        return [
            'porMetodo' => $porMetodo,
            'total' => round(array_sum($porMetodo), 2),
            'movimientos' => $recibos->map(fn (PaymentReceipt $r) => [
                'hora' => $r->paid_at?->format('H:i'),
                'paciente' => trim(($r->payment?->patient?->first_name ?? '') . ' ' . ($r->payment?->patient?->last_name ?? '')),
                'concepto' => self::concepto($r->payment),
                'monto' => (float) $r->amount,
                'metodo' => self::FORMAS[$r->payment_method] ?? 'Otro',
                'cobro' => $r->payment_id,
                'factura' => (bool) $r->payment?->factura_solicitada,
                'facturaEnviada' => (bool) $r->payment?->factura_enviada_at,
            ])->values(),
        ];
    }

    /** Las facturas que pidieron y todavía no se mandan, sean del día que sean. */
    public static function facturasPorMandar(int $clinicId): Collection
    {
        return Payment::withoutGlobalScopes()->with(['patient' => fn ($p) => $p->withoutGlobalScopes()])
            ->where('clinic_id', $clinicId)
            ->where('factura_solicitada', true)
            ->whereNull('factura_enviada_at')
            ->orderBy('payment_date')
            ->get();
    }

    /** Lo que lleva el recibo: lo que cuesta, lo que se ha dado (abono por abono) y lo que falta. */
    public static function datosDelRecibo(Payment $cobro): array
    {
        $cobro->loadMissing(['patient', 'service', 'receipts']);
        $clinica = Clinic::withoutGlobalScopes()->find($cobro->clinic_id);
        $pagado = round((float) $cobro->receipts->sum('amount'), 2);

        return [
            'cobro' => $cobro,
            'clinica' => $clinica,
            'paciente' => trim($cobro->patient?->first_name . ' ' . $cobro->patient?->last_name),
            'concepto' => self::concepto($cobro),
            'total' => (float) $cobro->amount,
            'abonos' => $cobro->receipts->sortBy('paid_at')->map(fn ($r) => [
                'fecha' => $r->paid_at?->format('d/m/Y'),
                'metodo' => self::FORMAS[$r->payment_method] ?? 'Otro',
                'monto' => (float) $r->amount,
            ])->values(),
            'pagado' => $pagado,
            'saldo' => max(0, round((float) $cobro->amount - $pagado, 2)),
        ];
    }

    private static function concepto(?Payment $cobro): string
    {
        return $cobro?->service?->name ?: ($cobro?->notes ?: 'Consulta');
    }
}
