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

    public const PRICE_TIERS = [
        'free' => 'Gratis en mi plan',
        '49' => '$49/mes',
        '99' => '$99/mes',
        '199' => '$199/mes',
        '299plus' => '$299+/mes',
    ];

    public const PRICE_TIER_VALUES = [
        'free' => 0,
        '49' => 49,
        '99' => 99,
        '199' => 199,
        '299plus' => 299,
    ];

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
