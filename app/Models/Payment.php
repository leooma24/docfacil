<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'patient_id', 'appointment_id', 'service_id',
        'amount', 'amount_paid', 'payment_method', 'status', 'notes',
        'payment_date', 'due_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'payment_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Saldo pendiente = amount - amount_paid. Nunca negativo.
     */
    protected function remaining(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, (float) $this->amount - (float) $this->amount_paid)
        );
    }

    /**
     * Vencido = tiene saldo pendiente Y due_date < hoy.
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->remaining > 0
                && $this->due_date !== null
                && $this->due_date->isPast()
        );
    }

    /**
     * Scope de cobros con saldo pendiente (pending o partial).
     */
    public function scopeWithBalance(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    /**
     * Scope de cobros vencidos (saldo pendiente + due_date pasada).
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->withBalance()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString());
    }

    /**
     * Lo que de verdad entró a la caja en un periodo.
     *
     * Antes cada widget hacía su propia cuenta: where('status','paid')
     * ->sum('amount'). Eso deja fuera los abonos — un paciente que dio
     * $2,000 de un tratamiento de $5,000 contaba como cero, y el doctor veía
     * menos ingreso del que había recibido.
     *
     * Aquí un cobro liquidado cuenta completo y uno a plazos cuenta lo
     * abonado. Todo lo que muestre ingresos usa esto, para que el escritorio
     * y el corte del mes no digan números distintos.
     */
    public static function cobradoEntre(
        int $clinicId,
        \DateTimeInterface $desde,
        \DateTimeInterface $hasta,
    ): float {
        return (float) static::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->whereBetween('payment_date', [$desde->format('Y-m-d'), $hasta->format('Y-m-d')])
            ->selectRaw('SUM(CASE WHEN status = ? THEN amount ELSE amount_paid END) as cobrado', ['paid'])
            ->value('cobrado');
    }

    /**
     * Lo que sigue pendiente de cobrar de ese periodo.
     */
    public static function porCobrarEntre(
        int $clinicId,
        \DateTimeInterface $desde,
        \DateTimeInterface $hasta,
    ): float {
        return (float) static::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('payment_date', [$desde->format('Y-m-d'), $hasta->format('Y-m-d')])
            ->selectRaw('SUM(amount - amount_paid) as saldo')
            ->value('saldo');
    }
}
