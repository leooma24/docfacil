<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\TreatmentPlan;
use Illuminate\Support\Collection;

/**
 * Lo que se quedó a medias con cada paciente: presupuestos sin respuesta y
 * tratamientos aceptados que nadie ha terminado de agendar.
 *
 * Del dentista (10-oct-2026): "cuando dice 'lo voy a pensar', ahí se queda;
 * no tenemos una lista de presupuestos pendientes". Y su límite: un
 * recordatorio amable al mes, que él decida mandar. Aquí vive la regla para
 * que la pantalla, el aviso del escritorio y el botón digan lo mismo.
 */
class PendientesDelConsultorio
{
    /** Cuántos días pasan antes de considerar que un presupuesto "se quedó". */
    public const DIAS_SIN_RESPUESTA = 7;

    /** Cada cuántos días se le puede recordar al mismo paciente. */
    public const DIAS_ENTRE_RECORDATORIOS = 30;

    /**
     * Presupuestos que el paciente tiene y no ha contestado, los más viejos
     * primero. `toca` dice si ya se puede recordar (al mes del último).
     *
     * @return Collection<int, array{plan: TreatmentPlan, dias: int, toca: bool, debe: float}>
     */
    public static function sinRespuesta(int $clinicId): Collection
    {
        $planes = TreatmentPlan::withoutGlobalScopes()->with('patient')
            ->where('clinic_id', $clinicId)
            ->where('status', 'sent')
            ->whereNotNull('sent_at')
            ->where('sent_at', '<=', now()->subDays(self::DIAS_SIN_RESPUESTA))
            ->orderBy('sent_at')
            ->get();

        $deudas = self::deudas($clinicId, $planes->pluck('patient_id'));

        return $planes->map(fn (TreatmentPlan $plan) => [
            'plan' => $plan,
            'dias' => (int) $plan->sent_at->diffInDays(now()),
            'toca' => self::tocaRecordar($plan),
            'debe' => (float) ($deudas[$plan->patient_id] ?? 0),
        ])->values();
    }

    /**
     * Tratamientos aceptados con algo por agendar. Los que ya empezaron van
     * primero: ya están a medias, y eso es lo que se pierde.
     *
     * @return Collection<int, array{plan: TreatmentPlan, avance: string, hechos: int, total: int, siguiente: ?string, debe: float}>
     */
    public static function aMedias(int $clinicId): Collection
    {
        $planes = TreatmentPlan::withoutGlobalScopes()->with(['patient', 'items'])
            ->where('clinic_id', $clinicId)
            ->where('status', 'accepted')
            ->get();

        $deudas = self::deudas($clinicId, $planes->pluck('patient_id'));

        return $planes
            ->map(function (TreatmentPlan $plan) use ($deudas) {
                $total = $plan->items->count();
                $hechos = $plan->items->whereNotNull('completed_at')->count();
                $siguiente = $plan->siguientePorAgendar();

                return $siguiente ? [
                    'plan' => $plan,
                    'avance' => "{$hechos} de {$total}",
                    'hechos' => $hechos,
                    'total' => $total,
                    'siguiente' => $siguiente->description,
                    'toca' => self::tocaRecordar($plan),
                    'debe' => (float) ($deudas[$plan->patient_id] ?? 0),
                ] : null;
            })
            ->filter()
            ->sortBy([['hechos', 'desc'], fn ($a, $b) => $a['plan']->accepted_at <=> $b['plan']->accepted_at])
            ->values();
    }

    /** Un recordatorio al mes por paciente y presupuesto: nada de escribirle cada tres días. */
    private static function tocaRecordar(TreatmentPlan $plan): bool
    {
        return ! $plan->last_reminded_at
            || $plan->last_reminded_at->lte(now()->subDays(self::DIAS_ENTRE_RECORDATORIOS));
    }

    /** Cuántos presupuestos tocan hoy: lo que cuenta el aviso del escritorio. */
    public static function cuantosTocan(int $clinicId): int
    {
        return self::sinRespuesta($clinicId)->where('toca', true)->count();
    }

    /** Lo que ya se debe por paciente (las mensualidades que no vencen no cuentan). */
    private static function deudas(int $clinicId, Collection $pacientes): Collection
    {
        if ($pacientes->isEmpty()) {
            return collect();
        }

        return Payment::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->whereIn('patient_id', $pacientes->unique())
            ->withBalance()
            ->yaToca()
            ->selectRaw('patient_id, SUM(amount - amount_paid) as saldo')
            ->groupBy('patient_id')
            ->pluck('saldo', 'patient_id');
    }
}
