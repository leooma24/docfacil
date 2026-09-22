<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un lote de un insumo, con su caducidad.
 *
 * Es opcional: los guantes no caducan y capturarlos sería trabajo sin provecho.
 * Cuando no hay lote, el insumo simplemente no avisa de nada, que es distinto
 * de inventar una fecha.
 *
 * Lo que queda de un lote NO se guarda: se calcula repartiendo el consumo FEFO
 * (primero lo que caduca antes). Ver Supply::lotesConExistencia().
 */
class SupplyLot extends Model
{
    use BelongsToClinic;

    protected $table = 'supply_lots';

    protected $fillable = [
        'clinic_id', 'supply_id', 'lot_number', 'expires_on',
        'quantity', 'unit_cost', 'supplier',
    ];

    protected function casts(): array
    {
        return [
            'expires_on' => 'date',
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
        ];
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(SupplyMovement::class);
    }

    /**
     * ¿Caduca dentro de los próximos días?
     *
     * Un lote sin fecha no caduca para el sistema: no se sabe, y suponerlo
     * llenaría la pantalla de avisos falsos.
     */
    public function expiresWithin(int $dias): bool
    {
        // Comparación directa en vez de restar días: `diffInDays` cambió de
        // signo entre versiones de Carbon, y un aviso de caducidad que se
        // dispara al revés es peor que no tenerlo.
        return $this->expires_on !== null
            && $this->expires_on->isFuture()
            && $this->expires_on->lte(now()->addDays($dias));
    }

    public function isExpired(): bool
    {
        return $this->expires_on !== null && $this->expires_on->isPast();
    }

    public function label(): string
    {
        $partes = [];

        if (filled($this->lot_number)) {
            $partes[] = 'Lote ' . $this->lot_number;
        }

        if ($this->expires_on) {
            $partes[] = 'caduca ' . $this->expires_on->format('d/m/Y');
        }

        return $partes ? implode(' · ', $partes) : 'Sin lote ni caducidad';
    }
}
