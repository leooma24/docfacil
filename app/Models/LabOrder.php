<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * Una orden de laboratorio: la corona, el puente, la guarda que se mandó a
 * hacer. Es la libreta del laboratorio del dentista (12-oct-2026): "¿cómo sé
 * si ya llegó? Pues cuando llega el mensajero con la cajita".
 */
class LabOrder extends Model
{
    use BelongsToClinic;

    /** Con cuántos días de anticipación avisar que la cita viene y el trabajo no ha llegado. */
    public const DIAS_DE_AVISO = 3;

    protected $fillable = [
        'clinic_id', 'patient_id', 'appointment_id', 'laboratorio', 'trabajo', 'diente', 'color', 'costo',
        'enviada_at', 'prometida_para', 'llego_at', 'entregada_at', 'pagada_at', 'expense_id', 'notas', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'costo' => 'decimal:2',
            'enviada_at' => 'date',
            'prometida_para' => 'date',
            'llego_at' => 'datetime',
            'entregada_at' => 'datetime',
            'pagada_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /** enviada → llego → entregada. */
    public function estado(): string
    {
        return match (true) {
            (bool) $this->entregada_at => 'entregada',
            (bool) $this->llego_at => 'llego',
            default => 'enviada',
        };
    }

    /** Ya pasó la fecha que prometió el laboratorio y no ha llegado. */
    public function atrasada(): bool
    {
        return ! $this->llego_at && $this->prometida_para && $this->prometida_para->lt(today());
    }

    /** Su cita es en unos días y el trabajo no ha llegado: hay que llamarle al laboratorio o reagendar. */
    public function enRiesgo(): bool
    {
        $cita = $this->appointment;

        return ! $this->llego_at && $cita
            && in_array($cita->status, ['scheduled', 'confirmed'], true)
            && $cita->starts_at->gte(now()->startOfDay())
            && $cita->starts_at->lte(now()->addDays(self::DIAS_DE_AVISO)->endOfDay());
    }

    /**
     * Pagada: queda como gasto de laboratorio con la fecha de hoy, así cae en
     * el corte del mes. Tocarlo dos veces no lo duplica.
     */
    public function marcarPagada(): void
    {
        if ($this->pagada_at || $this->expense_id) {
            return;
        }

        $this->loadMissing('patient');
        $gasto = Expense::create([
            'clinic_id' => $this->clinic_id,
            'created_by' => auth()->id(),
            'category' => 'laboratorio',
            'concept' => "Laboratorio: {$this->trabajo} · " . trim($this->patient?->first_name . ' ' . $this->patient?->last_name),
            'amount' => $this->costo,
            'expense_date' => today(),
            'supplier' => $this->laboratorio,
        ]);

        $this->update(['pagada_at' => now(), 'expense_id' => $gasto->id]);
    }

    /**
     * Lo que se le debe a cada laboratorio: lo que no se ha pagado.
     *
     * @return array{total: float, porLaboratorio: array<string, float>}
     */
    public static function loQueSeDebe(int $clinicId): array
    {
        $porLab = static::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->whereNull('pagada_at')
            ->selectRaw('laboratorio, SUM(costo) as debe')
            ->groupBy('laboratorio')
            ->orderBy('laboratorio')
            ->pluck('debe', 'laboratorio')
            ->map(fn ($v) => round((float) $v, 2))
            ->filter(fn ($v) => $v > 0)
            ->all();

        return ['total' => round(array_sum($porLab), 2), 'porLaboratorio' => $porLab];
    }

    /** Las órdenes que no han llegado y cuya cita es en los próximos días. */
    public static function enRiesgoDe(int $clinicId): Collection
    {
        return static::withoutGlobalScopes()->with(['patient', 'appointment'])
            ->where('clinic_id', $clinicId)
            ->whereNull('llego_at')
            ->whereNotNull('appointment_id')
            ->get()
            ->filter(fn (self $o) => $o->enRiesgo())
            ->sortBy(fn (self $o) => $o->appointment->starts_at)
            ->values();
    }

    /** Las que ya pasaron su fecha prometida y no han llegado. */
    public static function atrasadasDe(int $clinicId): Collection
    {
        return static::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->whereNull('llego_at')
            ->whereNotNull('prometida_para')
            ->whereDate('prometida_para', '<', today())
            ->get();
    }

    /** ¿Esta cita espera un trabajo que no ha llegado? Para la marca en la lista de citas. */
    public static function pendienteParaCita(int $appointmentId): bool
    {
        return static::withoutGlobalScopes()->where('appointment_id', $appointmentId)->whereNull('llego_at')->exists();
    }
}
