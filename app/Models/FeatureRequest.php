<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureRequest extends Model
{
    public const STATUSES = [
        'proposed' => 'Propuesta',
        'in_review' => 'En revisión',
        'in_progress' => 'En construcción',
        'shipped' => 'Entregada',
        'rejected' => 'No viable',
    ];

    /**
     * Escalera del poll "¿Cuánto pagarías al mes por esta feature?".
     * El último escalón es abierto ("+") y su valor debe alcanzar al menos
     * el plan más barato (Básico, $499/mes; ver Commission::monthlyPriceForPlan),
     * para que la señal de pricing no quede sesgada a la baja.
     */
    public const PRICE_TIERS = [
        'free' => 'Gratis en mi plan',
        '99' => '$99/mes',
        '249' => '$249/mes',
        '499' => '$499/mes',
        '999plus' => '$999+/mes',
    ];

    /**
     * Valor numérico (MXN/mes) de cada escalón. Incluye los escalones de la
     * escalera anterior ('49', '199', '299plus') para que los votos y propuestas
     * ya guardados sigan resolviendo su valor real en vez de caer a 0.
     */
    public const PRICE_TIER_VALUES = [
        'free' => 0,
        '49' => 49,        // histórico
        '99' => 99,
        '199' => 199,      // histórico
        '249' => 249,
        '299plus' => 299,  // histórico
        '499' => 499,
        '999plus' => 999,
    ];

    /**
     * Etiquetas de escalones históricos que ya no se ofrecen como opción pero
     * pueden seguir presentes en registros viejos. Solo para mostrar.
     */
    public const LEGACY_PRICE_TIERS = [
        '49' => '$49/mes',
        '199' => '$199/mes',
        '299plus' => '$299+/mes',
    ];

    /**
     * Etiqueta legible de un escalón, vigente o histórico.
     */
    public static function tierLabel(?string $tier): ?string
    {
        if ($tier === null) {
            return null;
        }

        return self::PRICE_TIERS[$tier] ?? self::LEGACY_PRICE_TIERS[$tier] ?? null;
    }

    public const RELEASE_TYPES = [
        'paid' => 'Add-on de pago',
        'free' => 'Incluido gratis',
    ];

    protected $fillable = [
        'submitted_by_user_id', 'submitted_by_clinic_id',
        'title', 'description',
        'status', 'proposed_price_tier',
        'votes_count',
        'shipped_at', 'shipped_notes',
        'release_type', 'winner_month',
    ];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'votes_count' => 'integer',
        ];
    }

    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function submittedByClinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'submitted_by_clinic_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(FeatureVote::class);
    }

    /**
     * Score para el ranking mensual: votos × precio promedio de willingness
     * (excluye 'free' del calculo de pago para no diluir). Si todos votan
     * free, el score es bajo (feature poco monetizable, candidata a gratis).
     */
    public function getMonetizableScoreAttribute(): float
    {
        $votes = $this->votes()->get();
        if ($votes->isEmpty()) return 0;

        $paidVotes = $votes->filter(fn ($v) => $v->willingness_to_pay !== 'free');
        if ($paidVotes->isEmpty()) return 0;

        $avgPrice = $paidVotes->avg(fn ($v) => self::PRICE_TIER_VALUES[$v->willingness_to_pay] ?? 0);
        return $votes->count() * $avgPrice;
    }

    public function getFreeScoreAttribute(): int
    {
        // Score simple por votos (para decidir ganadora gratis)
        return $this->votes_count;
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereIn('status', ['proposed', 'in_review']);
    }

    public function scopeShipped(Builder $q): Builder
    {
        return $q->where('status', 'shipped');
    }

    public function scopeInProgress(Builder $q): Builder
    {
        return $q->where('status', 'in_progress');
    }
}
