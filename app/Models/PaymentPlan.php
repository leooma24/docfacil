<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Un tratamiento que se paga en partes: enganche y N mensualidades. Cada
 * parte es un cobro (Payment) con su vencimiento, así que se abona, se
 * recuerda por WhatsApp y entra al corte igual que cualquier cobro.
 */
class PaymentPlan extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'patient_id', 'doctor_id', 'service_id', 'treatment_plan_id',
        'description', 'total', 'down_payment', 'installments_count', 'installment_amount',
        'first_due_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'down_payment' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'installments_count' => 'integer',
            'first_due_date' => 'date',
        ];
    }

    public const ESTADOS = ['active' => 'Al corriente', 'completed' => 'Liquidado', 'cancelled' => 'Cancelado'];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function treatmentPlan(): BelongsTo { return $this->belongsTo(TreatmentPlan::class); }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('installment_number');
    }

    /**
     * Crea el plan con su enganche (de hoy) y sus mensualidades, una por mes
     * desde la primera fecha. Los centavos que no se reparten exactos van en
     * la última. Si se da la forma de pago, el enganche queda pagado hoy.
     */
    public static function crear(array $datos, ?string $enganchePagadoCon = null): self
    {
        $total = round((float) $datos['total'], 2);
        $enganche = round((float) ($datos['down_payment'] ?? 0), 2);
        $n = (int) $datos['installments_count'];

        if ($total <= 0 || $n < 1 || $enganche < 0 || $enganche >= $total) {
            throw new \InvalidArgumentException('Revise el total, el enganche y el número de mensualidades: el enganche tiene que ser menor que el total.');
        }

        $restante = round($total - $enganche, 2);
        $mensualidad = floor($restante / $n * 100) / 100;

        return DB::transaction(function () use ($datos, $total, $enganche, $n, $restante, $mensualidad, $enganchePagadoCon) {
            $plan = self::create(array_merge($datos, [
                'total' => $total,
                'down_payment' => $enganche,
                'installments_count' => $n,
                'installment_amount' => $mensualidad,
                'status' => 'active',
            ]));

            $base = [
                'clinic_id' => $plan->clinic_id,
                'patient_id' => $plan->patient_id,
                'service_id' => $plan->service_id,
                'payment_plan_id' => $plan->id,
                'amount_paid' => 0,
                'status' => 'pending',
                'payment_method' => $enganchePagadoCon ?? 'cash',
            ];

            if ($enganche > 0) {
                Payment::create(array_merge($base, [
                    'installment_number' => 0,
                    'amount' => $enganche,
                    'payment_date' => today(),
                    'due_date' => today(),
                    'notes' => $plan->description . ' — enganche',
                ], $enganchePagadoCon ? ['status' => 'paid', 'amount_paid' => $enganche] : []));
            }

            $primera = Carbon::parse($datos['first_due_date']);
            for ($k = 1; $k <= $n; $k++) {
                $vence = $primera->copy()->addMonthsNoOverflow($k - 1);
                $monto = $k === $n ? round($restante - $mensualidad * ($n - 1), 2) : $mensualidad;
                Payment::create(array_merge($base, [
                    'installment_number' => $k,
                    'amount' => $monto,
                    'payment_date' => $vence,
                    'due_date' => $vence,
                    'notes' => "{$plan->description} — mensualidad {$k} de {$n}",
                ]));
            }

            return $plan;
        });
    }

    public function pagado(): float
    {
        return (float) PaymentReceipt::withoutGlobalScopes()->whereIn('payment_id', $this->payments()->select('id'))->sum('amount');
    }

    public function saldo(): float
    {
        return round(max(0, (float) $this->total - $this->pagado()), 2);
    }

    /** Lo que ya venció y no se ha pagado completo. */
    public function vencidas()
    {
        return $this->payments()->withBalance()->whereDate('due_date', '<', today());
    }

    /** La siguiente parte por pagar (vencida o no). */
    public function siguiente(): ?Payment
    {
        return $this->payments()->withBalance()->orderBy('due_date')->first();
    }

    /**
     * Cancelar un plan mal hecho o que el paciente dejó: se quitan las partes
     * que no se han pagado, la que lleva abono se cierra en lo que se dio, y
     * lo pagado se queda (ya entró a la caja).
     */
    public function cancelar(): void
    {
        DB::transaction(function () {
            foreach ($this->payments()->withBalance()->get() as $parte) {
                if ((float) $parte->amount_paid > 0) {
                    $parte->update(['amount' => $parte->amount_paid, 'status' => 'paid']);
                } else {
                    $parte->delete();
                }
            }
            $this->update(['status' => 'cancelled']);
        });
    }

    /** Se liquida al pagarse todo; si se reabre un cobro vuelve a activo. */
    public function actualizarEstado(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }

        $debe = $this->payments()->withBalance()->exists();
        $this->update(['status' => $debe ? 'active' : 'completed']);
    }
}
