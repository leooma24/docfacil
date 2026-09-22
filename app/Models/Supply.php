<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un insumo del consultorio.
 *
 * El stock NO es una columna: sale de sumar su kardex. Ver
 * SupplyMovement para por qué. Aquí vive también la conversión entre la
 * unidad en la que se compra (una caja de 50 guantes) y la unidad en la que
 * se consume (un guante), que es lo que hace que el inventario diga la verdad.
 */
class Supply extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'name', 'sku', 'category', 'unit', 'purchase_unit',
        'units_per_purchase', 'min_stock', 'cost_per_unit',
        'preferred_supplier', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'units_per_purchase' => 'decimal:3',
            'min_stock' => 'decimal:3',
            'cost_per_unit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(SupplyMovement::class);
    }

    /** Los lotes de este insumo. Opcionales: los guantes no caducan. */
    public function lots(): HasMany
    {
        return $this->hasMany(SupplyLot::class);
    }

    /**
     * Reparte el consumo entre los lotes, FEFO.
     *
     * Primero se consume lo que caduca antes, que es lo que hay que hacer
     * físicamente en el anaquel. El stock sin lote —las entradas que no lo
     * capturaron— se consume al final, porque para el sistema no caduca.
     *
     * Se calcula al leer y no se guarda: un `remaining` en la tabla se
     * desincroniza al primer ajuste, igual que un `current_stock`.
     *
     * @return array{lots: \Illuminate\Support\Collection<int, SupplyLot>, untracked: float}
     */
    public function fefoAllocation(): array
    {
        $lotes = $this->lots()
            // Los que no caducan van al final: `expires_on IS NULL` da 0 en los
            // que sí tienen fecha.
            ->orderByRaw('expires_on IS NULL')
            ->orderBy('expires_on')
            ->orderBy('id')
            ->get();

        $consumido = (float) $this->movements()
            ->whereIn('type', ['out', 'waste'])
            ->sum('quantity');

        foreach ($lotes as $lote) {
            $deEsteLote = min($consumido, (float) $lote->quantity);

            $lote->remaining = round((float) $lote->quantity - $deEsteLote, 3);
            $consumido = round($consumido - $deEsteLote, 3);
        }

        $entradoSinLote = (float) $this->movements()
            ->where('type', 'in')
            ->whereNull('supply_lot_id')
            ->sum('quantity');

        return [
            'lots' => $lotes,
            'untracked' => round(max(0, $entradoSinLote - $consumido), 3),
        ];
    }

    /**
     * Los lotes que todavía tienen existencia y caducan pronto.
     *
     * Solo mira los que capturaron fecha: un lote sin caducidad no avisa, que
     * es distinto de suponer que está vencido.
     */
    public function lotsExpiringSoon(int $dias = 30): \Illuminate\Support\Collection
    {
        return collect($this->fefoAllocation()['lots'])
            ->filter(fn (SupplyLot $lote) => $lote->remaining > 0 && $lote->expiresWithin($dias))
            ->values();
    }

    /** La caducidad más próxima entre los lotes con existencia, si hay. */
    public function nextExpiry(): ?\Illuminate\Support\Carbon
    {
        return collect($this->fefoAllocation()['lots'])
            ->filter(fn (SupplyLot $lote) => $lote->remaining > 0 && $lote->expires_on !== null)
            ->sortBy('expires_on')
            ->first()
            ?->expires_on;
    }

    /** El stock, sumado del kardex. */
    public function currentStock(): float
    {
        return round((float) $this->movements()
            ->selectRaw(SupplyMovement::STOCK_SUM . ' as stock')
            ->value('stock'), 3);
    }

    /**
     * Trae el stock de cada fila en la misma consulta.
     *
     * Sin esto, una tabla de 60 insumos dispara 60 consultas — y el stock es
     * justo la columna que el doctor mira primero.
     */
    public function scopeWithStock(Builder $query): Builder
    {
        return $query->addSelect([
            'stock_on_hand' => SupplyMovement::query()
                ->selectRaw(SupplyMovement::STOCK_SUM)
                ->whereColumn('supply_movements.supply_id', 'supplies.id'),
        ]);
    }

    /** El stock ya calculado si la consulta usó withStock(); si no, lo calcula. */
    public function stockOnHand(): float
    {
        return isset($this->attributes['stock_on_hand'])
            ? round((float) $this->attributes['stock_on_hand'], 3)
            : $this->currentStock();
    }

    /**
     * ¿Ya pegó en el punto de reorden?
     *
     * Con min_stock en cero no avisa: un insumo sin punto de reorden definido
     * no debe llenar la pantalla de alertas.
     */
    public function belowMinimum(): bool
    {
        return (float) $this->min_stock > 0
            && $this->stockOnHand() <= (float) $this->min_stock;
    }

    /** Cuántas unidades de consumo trae lo que se compró. */
    public function toConsumptionUnits(float $purchaseQuantity): float
    {
        $factor = (float) $this->units_per_purchase;

        return round($purchaseQuantity * ($factor > 0 ? $factor : 1), 3);
    }

    /** Lo que sale una unidad de consumo, si la compra costó $precioDeCompra. */
    public function costPerConsumptionUnit(float $purchasePrice, float $purchaseQuantity): float
    {
        $unidades = $this->toConsumptionUnits($purchaseQuantity);

        return $unidades > 0 ? round($purchasePrice / $unidades, 2) : 0.0;
    }

    /**
     * Lo que sale una unidad de consumo cuando entró esta cantidad.
     *
     * A diferencia de costPerConsumptionUnit(), la cantidad aquí YA viene en
     * unidad de consumo — que es como la captura el doctor ("entraron 50
     * guantes"), no como la compró.
     */
    public function entryCost(float $totalPrice, float $consumptionUnits): float
    {
        return $consumptionUnits > 0
            ? round($totalPrice / $consumptionUnits, 2)
            : 0.0;
    }

    /**
     * La única puerta de escritura al kardex.
     *
     * Valida el tipo y que la cantidad vaya en positivo: el signo lo pone el
     * tipo de movimiento, nunca el número. Una salida capturada como -5
     * sumaría cinco al inventario y nadie lo vería en un reporte.
     */
    public function register(string $type, float $quantity, array $data = []): SupplyMovement
    {
        if (! array_key_exists($type, SupplyMovement::TYPES)) {
            throw new \InvalidArgumentException("Tipo de movimiento desconocido: {$type}");
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La cantidad de un movimiento va en positivo.');
        }

        if ($type === 'waste') {
            // El motivo tiene que ser uno que el contador pueda sumar.
            if (isset($data['waste_reason']) && ! array_key_exists($data['waste_reason'], SupplyMovement::WASTE_REASONS)) {
                throw new \InvalidArgumentException("Motivo de merma desconocido: {$data['waste_reason']}");
            }

            // Una merma vale lo que costó el insumo. Sin costo no hay nada que
            // deducir, así que se congela el del catálogo si no vino otro.
            $data['unit_cost'] ??= $this->cost_per_unit;
        }

        return $this->movements()->create(array_merge([
            'clinic_id' => $this->clinic_id,
            'type' => $type,
            'quantity' => $quantity,
            'occurred_at' => now(),
            'user_id' => auth()->id(),
        ], $data));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
