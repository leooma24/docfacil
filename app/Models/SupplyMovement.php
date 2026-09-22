<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un movimiento del kardex: la entrada, la salida o la merma de un insumo.
 *
 * Es la fuente de verdad del inventario. El stock se suma de aquí en vez de
 * guardarse en una columna, porque una columna mutable se desincroniza al
 * primer doble clic o al primer reintento, y cuando el número deja de ser
 * cierto el doctor abandona la función.
 *
 * `quantity` siempre va en positivo; el signo lo pone `type`. Ver
 * Supply::register(), que es la única puerta de escritura.
 */
class SupplyMovement extends Model
{
    use BelongsToClinic;

    /** Los tipos que existen, como los lee el doctor. */
    public const TYPES = [
        'in' => 'Entrada',
        'out' => 'Salida',
        'waste' => 'Merma',
    ];

    /**
     * Por qué se perdió el material.
     *
     * Es una categoría y no una nota porque el contador suma por causa: las
     * mermas se deducen fiscalmente como pérdidas permitidas, y "cuánto se fue
     * en caducidades este mes" no se puede sacar de un texto libre.
     *
     * La merma normativa no está aquí a propósito: el sobrante de amalgama y
     * los restos extraídos no son movimientos de inventario.
     */
    public const WASTE_REASONS = [
        'expired' => 'Caducó',
        'spoiled' => 'Se echó a perder al usarlo',
        'lost' => 'Se perdió o se robó',
        'other' => 'Otro',
    ];

    /**
     * La suma que da el stock.
     *
     * La entrada suma y todo lo demás resta. Cuando la fase 2 agregue el
     * descuento automático por consulta, entra como una salida más y esta
     * expresión no cambia.
     */
    public const STOCK_SUM = "COALESCE(SUM(CASE WHEN type = 'in' THEN quantity ELSE -quantity END), 0)";

    protected $fillable = [
        'clinic_id', 'supply_id', 'supply_lot_id', 'user_id', 'type', 'quantity',
        'unit_cost', 'reason', 'waste_reason', 'reference_type', 'reference_id', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'occurred_at' => 'datetime',
        ];
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    /** El lote del que entró, si se capturó. Opcional. */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(SupplyLot::class, 'supply_lot_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addsToStock(): bool
    {
        return $this->type === 'in';
    }

    /** Lo que este movimiento le hizo al stock, con signo. */
    public function effect(): float
    {
        return $this->addsToStock() ? (float) $this->quantity : -(float) $this->quantity;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isWaste(): bool
    {
        return $this->type === 'waste';
    }

    /** El motivo, como lo lee el contador. */
    public function wasteReasonLabel(): ?string
    {
        if (! $this->isWaste()) {
            return null;
        }

        return self::WASTE_REASONS[$this->waste_reason] ?? 'Sin motivo';
    }

    /**
     * Lo que vale el movimiento: cantidad por costo unitario.
     *
     * Es el número que se lleva al contador. Una merma sin costo no se puede
     * deducir, y por eso Supply::register() congela el del catálogo.
     */
    public function value(): float
    {
        return round((float) $this->quantity * (float) ($this->unit_cost ?? 0), 2);
    }
}
