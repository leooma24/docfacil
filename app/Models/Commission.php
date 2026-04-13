<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Commission extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id', 'clinic_id', 'prospect_id', 'tier',
        'amount', 'plan_at_sale', 'status',
        'earned_at', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'paid_at', 'notes'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Comisión {$eventName}");
    }

    /**
     * Planes que califican para comisión.
     * Solo el plan Free NO paga. Todos los planes de pago (Básico, Pro, Clínica) sí pagan.
     */
    public const COMMISSIONABLE_PLANS = ['basico', 'profesional', 'clinica'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    public function scopePaid($q)
    {
        return $q->where('status', 'paid');
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    /**
     * Precio mensual en MXN por plan.
     */
    public static function monthlyPriceForPlan(string $plan): int
    {
        return match ($plan) {
            'free' => 0,
            'basico' => 149,
            'profesional' => 299,
            'clinica' => 499,
            default => 0,
        };
    }

    /**
     * Mitad de la comisión 3× para un plan.
     * Total = 3 × precio mensual (se paga en 2 exhibiciones: 50% en 1er pago + 50% en 2do).
     * Retorna 0 si el plan no califica (Free).
     */
    public static function halfAmount(string $plan): float
    {
        if (!in_array($plan, self::COMMISSIONABLE_PLANS)) {
            return 0;
        }
        // Total 3x la mensualidad / 2 = 1.5x por mitad
        return round(self::monthlyPriceForPlan($plan) * 1.5, 2);
    }
}
