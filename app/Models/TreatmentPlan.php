<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentPlan extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'patient_id', 'doctor_id',
        'title', 'description',
        'subtotal', 'discount', 'total',
        // Día 4 (10-oct-2026): descuento en %, cuánto detalle ve el paciente
        // y su firma al aceptar.
        'descuento_porcentaje', 'detalle_al_enviar', 'firma_paciente', 'firmado_por',
        'status', 'public_token',
        'sent_at', 'accepted_at', 'accepted_ip',
        'rejected_at', 'last_reminded_at', 'valid_until', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'descuento_porcentaje' => 'decimal:2',
            'total' => 'decimal:2',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'last_reminded_at' => 'datetime',
            'valid_until' => 'date',
        ];
    }

    public function clinic(): BelongsTo { return $this->belongsTo(Clinic::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
    public function items(): HasMany { return $this->hasMany(TreatmentPlanItem::class)->orderBy('sort_order'); }

    /** El siguiente tratamiento, en orden, que no está hecho ni tiene cita. */
    public function siguientePorAgendar(): ?TreatmentPlanItem
    {
        return $this->items()->whereNull('completed_at')->get()
            ->first(fn (TreatmentPlanItem $item) => ! $item->citaPendiente());
    }

    public function generatePublicToken(): string
    {
        $this->public_token = bin2hex(random_bytes(32));
        $this->save();
        return $this->public_token;
    }

    /**
     * Lo opcional ("Opción B") no suma al total. Con descuento en porcentaje,
     * el monto del descuento sale de ahí.
     */
    public function recalculateTotal(): void
    {
        $subtotal = round((float) $this->items()->where('opcional', false)->sum('subtotal'), 2);
        $this->subtotal = $subtotal;
        if ((float) $this->descuento_porcentaje > 0) {
            $this->discount = round($subtotal * min(100, (float) $this->descuento_porcentaje) / 100, 2);
        }
        $this->total = max(0, $subtotal - (float) $this->discount);
        $this->save();
    }

    /**
     * Los tratamientos por etapa, en el orden en que aparece cada etapa. Sin
     * etapas, todo queda en una sola con nombre vacío.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, TreatmentPlanItem>>
     */
    public function porEtapas(): \Illuminate\Support\Collection
    {
        $items = $this->relationLoaded('items') ? $this->items->sortBy('sort_order') : $this->items()->get();

        return $items->groupBy(fn (TreatmentPlanItem $i) => trim((string) $i->etapa));
    }

    /** completo: precio de cada tratamiento; solo_total: solo el total; sin_precios: ningún monto. */
    public function muestra(string $que): bool
    {
        $detalle = $this->detalle_al_enviar ?: 'completo';

        return match ($que) {
            'precios' => $detalle === 'completo',
            'total' => $detalle !== 'sin_precios',
            default => true,
        };
    }
}
