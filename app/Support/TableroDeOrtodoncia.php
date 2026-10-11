<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Los pacientes en tratamiento de ortodoncia (día 7 del plan, 10-oct-2026):
 * quién va atrasado en su control, quién no tiene su siguiente cita y a quién
 * se le venció la mensualidad.
 *
 * Está en ortodoncia quien tiene un plan de pagos activo de ortodoncia (por
 * su servicio o su descripción) o tuvo un control en los últimos 90 días.
 */
class TableroDeOrtodoncia
{
    /** Días entre controles antes de considerarlo atrasado. */
    public const DIAS_ENTRE_CONTROLES = 35;

    public static function serviciosDe(int $clinicId): Collection
    {
        return Service::withoutGlobalScopes()->where('clinic_id', $clinicId)
            ->whereRaw('LOWER(category) = ?', ['ortodoncia'])->pluck('id');
    }

    /** ¿El consultorio hace ortodoncia? Para mostrarlo en el menú. */
    public static function laHace(int $clinicId): bool
    {
        return self::serviciosDe($clinicId)->isNotEmpty();
    }

    /**
     * @return Collection<int, array{paciente: Patient, plan: ?PaymentPlan, ultimo: ?Carbon, proximo: ?Carbon, atrasado: bool, vencido: float, delMes: ?Payment}>
     */
    public static function de(int $clinicId): Collection
    {
        $servicios = self::serviciosDe($clinicId);

        $planes = PaymentPlan::withoutGlobalScopes()->where('clinic_id', $clinicId)->where('status', 'active')
            ->where(fn ($q) => $q->whereIn('service_id', $servicios)->orWhereRaw('LOWER(description) LIKE ?', ['%ortodon%']))
            ->get()->keyBy('patient_id');

        $citas = $servicios->isEmpty() ? collect() : Appointment::withoutGlobalScopes()->where('clinic_id', $clinicId)
            ->whereIn('service_id', $servicios)
            ->where('starts_at', '>=', now()->subYear())
            ->orderBy('starts_at')->get()->groupBy('patient_id');

        $recientes = $citas->filter(fn ($deUno) => $deUno->contains(fn (Appointment $c) => $c->status === 'completed' && $c->starts_at->gte(now()->subDays(90))))->keys();
        $pacientes = Patient::withoutGlobalScopes()->where('clinic_id', $clinicId)
            ->whereIn('id', $planes->keys()->merge($recientes)->unique())
            ->orderBy('first_name')->get();

        return $pacientes->map(function (Patient $p) use ($planes, $citas) {
            $suyas = $citas->get($p->id, collect());
            $ultimo = $suyas->where('status', 'completed')->last()?->starts_at;
            $proximo = $suyas->whereIn('status', Appointment::ESTADOS_QUE_OCUPAN)->first(fn ($c) => $c->starts_at->gte(now()))?->starts_at;
            $plan = $planes->get($p->id);

            return [
                'paciente' => $p,
                'plan' => $plan,
                'ultimo' => $ultimo,
                'proximo' => $proximo,
                'atrasado' => ! $proximo && (! $ultimo || $ultimo->lt(now()->subDays(self::DIAS_ENTRE_CONTROLES))),
                'vencido' => $plan ? round((float) $plan->vencidas()->get()->sum(fn (Payment $c) => $c->remaining), 2) : 0.0,
                'delMes' => $plan?->payments()->whereBetween('due_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->first(),
            ];
        })->values();
    }

    /**
     * Lo que se cobra en el control: las mensualidades de su plan que ya
     * vencieron o vencen este mes. Así el control del mes y su mensualidad
     * son un solo cobro.
     */
    public static function mensualidadesPorCobrar(Appointment $cita): Collection
    {
        if (! $cita->service_id || ! self::serviciosDe($cita->clinic_id)->contains($cita->service_id)) {
            return collect();
        }

        $plan = PaymentPlan::withoutGlobalScopes()->where('clinic_id', $cita->clinic_id)->where('patient_id', $cita->patient_id)
            ->where('status', 'active')->latest('id')->first();

        return $plan ? $plan->payments()->withBalance()
            ->whereDate('due_date', '<=', now()->endOfMonth()->toDateString())
            ->orderBy('due_date')->get() : collect();
    }
}
