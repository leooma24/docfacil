<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use App\Support\SupplyScope;
use App\Support\WorkUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una línea de la receta: este servicio gasta este insumo, así.
 *
 * El alcance (`scope`) es lo que hace correcta la cuenta. Vacío significa "como
 * se cobre el servicio", que es lo común — un curetaje por cuadrante gasta sus
 * insumos por cuadrante. Pero tiene que poder sobrescribirse: la anestesia de
 * ese mismo curetaje va por zona contigua, porque el bloqueo troncular duerme
 * el cuadrante entero aunque los dientes estén separados.
 */
class ServiceSupply extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'service_id', 'supply_id', 'quantity', 'scope',
        'is_optional', 'waste_factor', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'waste_factor' => 'decimal:3',
            'is_optional' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    /** El alcance de la línea: el suyo, o el del servicio. */
    public function effectiveScope(): string
    {
        if (WorkUnit::isValid($this->scope)) {
            return $this->scope;
        }

        return WorkUnit::isValid($this->service?->unit)
            ? $this->service->unit
            : WorkUnit::VISIT;
    }

    /** El que le tocaría por la categoría de su insumo, si la categoría dice algo. */
    public function suggestedScope(): ?string
    {
        return WorkUnit::scopeSuggestedByCategory($this->supply?->category);
    }

    /**
     * ¿La categoría del insumo pide un alcance distinto del que tiene?
     *
     * Es el caso de la anestesia dentro de un curetaje: sin revisar, se
     * descontaría por cuadrante cuando va por zona contigua.
     */
    public function needsScopeReview(): bool
    {
        $sugerido = $this->suggestedScope();

        return $sugerido !== null && $sugerido !== $this->effectiveScope();
    }

    /**
     * Cuánto se gasta de este insumo, dados los dientes trabajados.
     *
     * Es la cuenta completa: cantidad por unidad del alcance, las veces que
     * aplica, y la merma normal.
     */
    public function quantityFor(array $teeth): float
    {
        $veces = SupplyScope::count($this->effectiveScope(), $teeth);

        return round((float) $this->quantity * $veces * (float) $this->waste_factor, 3);
    }

    /** Para qué sirve la línea, si aplica a todos los casos. */
    public function scopeLabel(): string
    {
        $efectivo = WorkUnit::label($this->effectiveScope());

        return $this->scope === null
            ? $efectivo . ' (como el servicio)'
            : $efectivo;
    }
}
