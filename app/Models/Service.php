<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use BelongsToClinic;
    protected $fillable = [
        'clinic_id', 'name', 'description', 'price', 'unit',
        'duration_minutes', 'category', 'is_active', 'recall_months',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'recall_months' => 'integer',
        ];
    }

    public function hasRecall(): bool
    {
        return !empty($this->recall_months) && $this->recall_months > 0;
    }

    /** La receta de insumos: qué gasta este servicio. */
    public function recipe(): HasMany
    {
        return $this->hasMany(ServiceSupply::class);
    }

    /**
     * Las líneas que este servicio descontaría, dados los dientes trabajados.
     *
     * Es lo que la propuesta del cierre de consulta va a mostrar. Se agrupa por
     * insumo: si dos líneas gastan el mismo, se suman — diez renglones de "1
     * guante" en el kardex no le sirven a nadie.
     */
    public function recipeFor(array $teeth): array
    {
        $porInsumo = [];

        foreach ($this->recipe()->with('supply')->get() as $linea) {
            if (! $linea->supply) {
                continue;
            }

            $cantidad = $linea->quantityFor($teeth);

            if ($cantidad <= 0) {
                continue;
            }

            $id = $linea->supply_id;

            $porInsumo[$id] ??= [
                'supply' => $linea->supply,
                'quantity' => 0.0,
                'scope' => $linea->effectiveScope(),
                'optional' => true,
            ];

            $porInsumo[$id]['quantity'] = round($porInsumo[$id]['quantity'] + $cantidad, 3);
            // Si una sola línea no es opcional, el insumo no lo es.
            $porInsumo[$id]['optional'] = $porInsumo[$id]['optional'] && $linea->is_optional;
        }

        return array_values($porInsumo);
    }

    /** Cuántas líneas de la receta piden revisión de alcance. */
    public function recipeLinesNeedingReview(): int
    {
        // Usa la relación ya cargada si la hay: el filtro de la tabla trae
        // todos los servicios de golpe y no tiene por qué disparar una
        // consulta por cada uno.
        $lineas = $this->relationLoaded('recipe')
            ? $this->recipe
            : $this->recipe()->with(['supply', 'service'])->get();

        return $lineas
            ->filter(fn (ServiceSupply $linea) => $linea->needsScopeReview())
            ->count();
    }

    /**
     * Cómo se cobra: por visita, por diente o por cuadrante.
     *
     * "Curetaje (por cuadrante)" a $800 se cobraba una vez aunque fueran dos
     * cuadrantes, porque el precio no decía de qué era.
     */
    public function unitLabel(): string
    {
        return \App\Support\WorkUnit::label($this->unit);
    }

    /** ¿El precio es por unidad de trabajo, o una línea por visita? */
    public function isBilledPerUnit(): bool
    {
        return $this->unit !== \App\Support\WorkUnit::VISIT;
    }

    /** La unidad que el nombre deja entrever, si la dice. */
    public function suggestedUnit(): ?string
    {
        return \App\Support\WorkUnit::suggestedFromName($this->name);
    }

    /**
     * ¿El nombre pide una unidad distinta de la que tiene capturada?
     *
     * Pasa con los servicios que ya existían: "Curetaje (por cuadrante)" a
     * $2,500 quedó como "por visita" porque el sistema no sabía de unidades, y
     * así se sigue cobrando una vez aunque sean dos cuadrantes.
     */
    public function needsUnitReview(): bool
    {
        return \App\Support\WorkUnit::differsFromName($this->unit, $this->name);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
