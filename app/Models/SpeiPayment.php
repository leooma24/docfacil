<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SpeiPayment extends Model
{
    use LogsActivity;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'clinic_id', 'user_id',
        'plan', 'billing_cycle', 'amount', 'reference_code',
        'receipt_path', 'receipt_original_name', 'receipt_mime', 'receipt_size_bytes',
        'client_notes',
        'status', 'reviewed_by', 'reviewed_at', 'review_notes',
        'plan_activated_until',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
            'plan_activated_until' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'reviewed_by', 'review_notes'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $event) => "Pago SPEI {$event}");
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($q)
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function receiptUrl(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }
        return Storage::disk('public')->url($this->receipt_path);
    }

    /**
     * Genera un código de referencia único legible para que el cliente lo ponga como concepto.
     * Formato: DOCF-{clinicId}-{YYMMDD}-{rand4}
     */
    public static function generateReferenceCode(int $clinicId): string
    {
        return sprintf(
            'DOCF-%d-%s-%s',
            $clinicId,
            now()->format('ymd'),
            strtoupper(substr(bin2hex(random_bytes(2)), 0, 4)),
        );
    }

    /**
     * Cuántos días queda activo el plan tras aprobar este pago según el ciclo.
     */
    public function durationInDays(): int
    {
        return $this->billing_cycle === 'annual' ? 365 : 30;
    }
}
