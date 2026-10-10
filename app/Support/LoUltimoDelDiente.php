<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\ConsultationProcedure;
use App\Models\MedicalRecord;
use App\Models\TreatmentPlanItem;
use Illuminate\Support\Collection;

/**
 * Al abrir la cita: lo último que se le hizo en cada diente y lo que falta.
 *
 * El dentista de la entrevista (10-oct-2026) se tardaba 5 minutos entre
 * paciente y paciente buscando en la hoja de papel "qué conductos, qué
 * longitud". Aquí sale el procedimiento, la fecha, lo que anotó ese día y lo
 * que sigue pendiente del presupuesto en ese mismo diente.
 */
class LoUltimoDelDiente
{
    /**
     * @return Collection<int, array{diente: string, hecho: ?string, cuando: ?\Carbon\Carbon, nota: ?string, falta: list<string>}>
     */
    public static function paraLaCita(Appointment $cita, int $cuantos = 4): Collection
    {
        $hechos = ConsultationProcedure::withoutGlobalScopes()
            ->with(['service', 'appointment'])
            ->where('clinic_id', $cita->clinic_id)
            ->whereNotNull('tooth_number')
            ->whereHas('appointment', fn ($q) => $q->withoutGlobalScopes()
                ->where('patient_id', $cita->patient_id)
                ->where('status', 'completed')
                ->whereKeyNot($cita->id))
            ->get()
            ->sortByDesc(fn ($p) => $p->appointment->starts_at)
            ->unique('tooth_number');

        $notas = MedicalRecord::withoutGlobalScopes()
            ->whereIn('appointment_id', $hechos->pluck('appointment_id')->unique())
            ->get()
            ->keyBy('appointment_id');

        $faltan = TreatmentPlanItem::whereNull('completed_at')
            ->whereNotNull('tooth_number')
            ->whereHas('treatmentPlan', fn ($q) => $q->withoutGlobalScopes()
                ->where('clinic_id', $cita->clinic_id)
                ->where('patient_id', $cita->patient_id)
                ->where('status', 'accepted'))
            ->get()
            ->groupBy('tooth_number');

        $dientes = $hechos->pluck('tooth_number')->merge($faltan->keys())->unique();

        // Primero el diente que se trabaja hoy (si la cita viene del presupuesto).
        $deHoy = $cita->treatmentPlanItem?->tooth_number;

        return $dientes
            ->map(function ($diente) use ($hechos, $notas, $faltan) {
                $hecho = $hechos->firstWhere('tooth_number', $diente);
                $nota = $hecho ? $notas->get($hecho->appointment_id) : null;

                return [
                    'diente' => (string) $diente,
                    'hecho' => $hecho ? ($hecho->service?->name ?? 'Se trabajó') : null,
                    'cuando' => $hecho?->appointment->starts_at,
                    'nota' => $nota ? \Illuminate\Support\Str::limit(trim((string) ($nota->treatment ?: $nota->notes)), 120) ?: null : null,
                    'falta' => $faltan->get($diente, collect())->pluck('description')->values()->all(),
                ];
            })
            ->sortByDesc(fn ($fila) => [$fila['diente'] === (string) $deHoy, $fila['cuando']?->timestamp ?? 0])
            ->take($cuantos)
            ->values();
    }
}
