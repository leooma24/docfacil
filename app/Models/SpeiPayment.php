<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Pagos SPEI manuales con aprobación administrativa.
 *
 * Usa BelongsToClinic defensivamente: el admin global (sin clinic_id) ve todos
 * los pagos, pero si un doctor accediera al modelo (hoy no hay UI), solo vería
 * los de su clínica. Previene futuras filtraciones si se expone el recurso
 * al panel Doctor para ver historial propio.
 */
class SpeiPayment extends Model
{
    use BelongsToClinic;
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

    /**
     * URL temporal firmada (5 min) para ver el comprobante.
     * El archivo vive en el disco privado, no es accesible públicamente.
     */
    public function receiptUrl(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }
        // temporaryUrl solo funciona con drivers que lo soporten (S3). Para disco local usamos un signed route.
        return route('spei.receipt.download', ['payment' => $this->id]);
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
