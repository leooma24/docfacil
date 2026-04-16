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
        'amount', 'plan_at_sale', 'billing_cycle', 'payment_method', 'payout_type',
        'status', 'earned_at', 'paid_at', 'notes',
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
            'basico' => 499,       // UI: "Básico"
            'profesional' => 999,  // UI: "Pro"
            'clinica' => 1999,     // UI: "Clínica"
            default => 0,
        };
    }

    /**
     * Precio anual = mensual × 10 (2 meses gratis).
     */
    public static function annualPriceForPlan(string $plan): int
    {
        $monthly = self::monthlyPriceForPlan($plan);
        return $monthly > 0 ? $monthly * 10 : 0;
    }

    /**
     * Precio total del ciclo elegido para un plan.
     */
    public static function priceForCycle(string $plan, string $cycle): int
    {
        return $cycle === 'annual'
            ? self::annualPriceForPlan($plan)
            : self::monthlyPriceForPlan($plan);
    }

    /**
     * Comisión total 3× la mensualidad para un plan (igual para mensual o anual).
     */
    public static function totalCommissionForPlan(string $plan): float
    {
        if (!in_array($plan, self::COMMISSIONABLE_PLANS)) {
            return 0;
        }
        return round(self::monthlyPriceForPlan($plan) * 3, 2);
    }

    /**
     * Mitad de la comisión 3× para un plan (se usa cuando el payout es split mensual).
     */
    public static function halfAmount(string $plan): float
    {
        return round(self::totalCommissionForPlan($plan) / 2, 2);
    }

    /**
     * Crea las comisiones que le corresponden a un vendedor por una venta.
     *
     * - Ventas ANUALES → 1 sola comisión con tier='first' y payout_type='lump_sum' por el 100% (3× mensualidad).
     * - Ventas MENSUALES → 2 comisiones tier 'first'/'second', payout_type='split', 50% cada una.
     *
     * Idempotente: si ya hay comisiones pending/paid para la combinación clínica+vendedor+plan,
     * retorna vacío y no duplica. Esto protege contra:
     * - Reintentos de webhook Stripe (checkout.session.completed llega 2+ veces).
     * - Renovaciones mensuales SPEI (cada aprobación llama este método).
     * - Clínica que cancela y re-contrata — considerar como misma venta.
     *
     * Las comisiones quedan en status='pending' hasta que se confirme el pago del cliente.
     */
    public static function generateForSale(
        Clinic $clinic,
        int $userId,
        string $plan,
        string $billingCycle,
        string $paymentMethod = 'stripe',
        ?int $prospectId = null,
    ): array {
        if (!in_array($plan, self::COMMISSIONABLE_PLANS)) {
            return [];
        }

        // Guardia de idempotencia: si ya hay comisiones vivas para esta venta, no duplicar.
        $alreadyHasCommissions = self::query()
            ->where('clinic_id', $clinic->id)
            ->where('user_id', $userId)
            ->where('plan_at_sale', $plan)
            ->whereIn('status', ['pending', 'paid'])
            ->exists();

        if ($alreadyHasCommissions) {
            \Log::info('Commission::generateForSale skipped (ya existen comisiones)', [
                'clinic_id' => $clinic->id,
                'user_id' => $userId,
                'plan' => $plan,
            ]);
            return [];
        }

        $common = [
            'user_id' => $userId,
            'clinic_id' => $clinic->id,
            'prospect_id' => $prospectId,
            'plan_at_sale' => $plan,
            'billing_cycle' => $billingCycle,
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'earned_at' => now(),
        ];

        if ($billingCycle === 'annual') {
            $total = self::totalCommissionForPlan($plan);
            return [self::create(array_merge($common, [
                'tier' => 'first',
                'amount' => $total,
                'payout_type' => 'lump_sum',
                'notes' => 'Pago único (venta anual)',
            ]))];
        }

        $half = self::halfAmount($plan);
        return [
            self::create(array_merge($common, [
                'tier' => 'first',
                'amount' => $half,
                'payout_type' => 'split',
                'notes' => '50% - primer pago del cliente',
            ])),
            self::create(array_merge($common, [
                'tier' => 'second',
                'amount' => $half,
                'payout_type' => 'split',
                'notes' => '50% - segundo pago del cliente',
            ])),
        ];
    }
}
