<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Un gasto del consultorio.
 *
 * No es contabilidad para el SAT — eso lo hace su contador. Es lo que el
 * doctor necesita para contestarse "¿cuánto me quedó este mes?" sin abrir
 * una hoja de cálculo aparte.
 */
class Expense extends Model
{
    use BelongsToClinic;
    use LogsActivity;

    /**
     * Las categorías de un consultorio dental de verdad.
     *
     * El laboratorio va aparte de los materiales a propósito: en odontología
     * es de los gastos más grandes y el doctor lo quiere ver solo.
     */
    public const CATEGORIAS = [
        'materiales' => 'Materiales dentales',
        'laboratorio' => 'Laboratorio (prótesis, coronas)',
        'renta' => 'Renta del local',
        'nomina' => 'Sueldos y honorarios',
        'servicios' => 'Luz, agua, internet, teléfono',
        'equipo' => 'Equipo e instrumental',
        'mantenimiento' => 'Mantenimiento y reparaciones',
        'publicidad' => 'Publicidad y marketing',
        'impuestos' => 'Impuestos y contador',
        'capacitacion' => 'Cursos y capacitación',
        'otros' => 'Otros',
    ];

    public const FORMAS_DE_PAGO = [
        'cash' => 'Efectivo',
        'card' => 'Tarjeta',
        'transfer' => 'Transferencia',
    ];

    protected $fillable = [
        'clinic_id', 'created_by', 'category', 'concept', 'amount',
        'expense_date', 'payment_method', 'supplier', 'notes',
        'receipt_path', 'is_recurring', 'last_generated_on',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'last_generated_on' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category', 'concept', 'amount', 'expense_date'])
            ->logOnlyDirty();
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categoriaLegible(): string
    {
        return self::CATEGORIAS[$this->category] ?? $this->category;
    }

    // ── Consultas ────────────────────────────────────────────────

    public function scopeEntre(Builder $query, \DateTimeInterface $desde, \DateTimeInterface $hasta): Builder
    {
        return $query->whereBetween('expense_date', [
            $desde->format('Y-m-d'),
            $hasta->format('Y-m-d'),
        ]);
    }

    /**
     * Cuánto gastó el consultorio en un periodo.
     */
    public static function totalEntre(int $clinicId, \DateTimeInterface $desde, \DateTimeInterface $hasta): float
    {
        return (float) static::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->entre($desde, $hasta)
            ->sum('amount');
    }

    /**
     * El desglose por categoría, de mayor a menor.
     *
     * Es lo primero que quiere ver el doctor: no "gasté $48,000" sino "el
     * laboratorio se llevó $18,000 de esos".
     *
     * @return array<string, float>  categoría legible => total
     */
    public static function porCategoria(int $clinicId, \DateTimeInterface $desde, \DateTimeInterface $hasta): array
    {
        return static::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->entre($desde, $hasta)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(fn ($fila) => [
                self::CATEGORIAS[$fila->category] ?? $fila->category => (float) $fila->total,
            ])
            ->all();
    }
}
