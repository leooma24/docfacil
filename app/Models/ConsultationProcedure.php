<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use App\Support\WorkUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un procedimiento hecho en la consulta: qué servicio, en qué diente y cuántas
 * veces.
 *
 * De aquí salen las cuatro cuentas del motor de insumos. Sin esta tabla, la
 * anestesia no se puede calcular (necesita los dientes), el curetaje se cobra
 * mal (necesita los cuadrantes) y nada que dependa del diente existe.
 *
 * `unit` y `unit_price` son una foto del momento: un procedimiento es un hecho
 * histórico y no se mueve si mañana cambia el catálogo.
 */
class ConsultationProcedure extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'appointment_id', 'service_id', 'tooth_number',
        'quantity', 'unit', 'unit_price', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Lo que suma la línea. Se calcula en vez de guardarse: dos columnas que
     * dicen lo mismo terminan diciendo cosas distintas.
     */
    public function total(): float
    {
        return round((float) $this->unit_price * (int) $this->quantity, 2);
    }

    public function unitLabel(): string
    {
        return WorkUnit::label($this->unit);
    }

    public function description(): string
    {
        $partes = [$this->service?->name ?? 'Procedimiento'];

        if ($this->tooth_number) {
            $partes[] = "diente {$this->tooth_number}";
        }

        if ((int) $this->quantity > 1) {
            $partes[] = "×{$this->quantity}";
        }

        return implode(' · ', $partes);
    }
}
