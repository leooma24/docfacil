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
}
