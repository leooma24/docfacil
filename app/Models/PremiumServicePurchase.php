<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Compra de un servicio premium por parte de una clínica.
 * Maneja el workflow: pending_payment → paid → in_progress → delivered.
 */
class PremiumServicePurchase extends Model
{
    use BelongsToClinic;
    use LogsActivity;

    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PAID = 'paid';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_PENDING_PAYMENT => 'Pendiente de pago',
        self::STATUS_PAID => 'Pagado, en cola',
        self::STATUS_IN_PROGRESS => 'En ejecución',
        self::STATUS_DELIVERED => 'Entregado',
        self::STATUS_CANCELLED => 'Cancelado',
        self::STATUS_REFUNDED => 'Reembolsado',
    ];

    protected $fillable = [
        'clinic_id', 'user_id', 'premium_service_id', 'assigned_to',
        'service_name_snapshot', 'amount_mxn', 'pricing_type',
        'status',
        'payment_method', 'stripe_session_id', 'stripe_subscription_id', 'spei_payment_id',
        'intake_data', 'client_notes',
        'delivery_files', 'delivery_notes',
        'paid_at', 'started_at', 'delivered_at', 'cancelled_at',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_mxn' => 'decimal:2',
            'intake_data' => 'array',
            'delivery_files' => 'array',
            'paid_at' => 'datetime',
            'started_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'assigned_to', 'internal_notes'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $event) => "Compra servicio premium {$event}");
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(PremiumService::class, 'premium_service_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function speiPayment(): BelongsTo
    {
        return $this->belongsTo(SpeiPayment::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [
            self::STATUS_PAID,
            self::STATUS_IN_PROGRESS,
            self::STATUS_DELIVERED,
        ], true);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PAYMENT => 'warning',
            self::STATUS_PAID => 'primary',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED, self::STATUS_REFUNDED => 'danger',
            default => 'gray',
        };
    }

    /**
     * Marca la compra como pagada — dispara el inicio del workflow.
     * Idempotente: si ya está marcada como pagada (o más allá), retorna false sin tocar.
     * Todos los IDs de Stripe/SPEI se escriben en un solo update para evitar inconsistencias
     * si falla un update intermedio.
     */
    public function markPaid(
        string $method,
        ?string $stripeSession = null,
        ?int $speiId = null,
        ?string $stripeSubscriptionId = null,
    ): bool {
        if ($this->isPaid()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_PAID,
            'payment_method' => $method,
            'stripe_session_id' => $stripeSession,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'spei_payment_id' => $speiId,
            'paid_at' => now(),
        ]);

        return true;
    }
}
