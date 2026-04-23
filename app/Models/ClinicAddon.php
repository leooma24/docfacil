<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicAddon extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'addon_slug', 'status',
        'monthly_price', 'billing_cycle',
        'trial_ends_at', 'started_at', 'cancelled_at', 'ends_at',
        'stripe_subscription_item_id',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'trial_ends_at' => 'datetime',
            'started_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Add-on esta activo = trial sin vencer, o active sin ends_at pasado.
     */
    public function isActive(): bool
    {
        if ($this->status === 'cancelled') {
            // Permiso residual hasta ends_at (paga hasta fin de periodo)
            return $this->ends_at !== null && $this->ends_at->isFuture();
        }
        if ($this->status === 'trial') {
            return $this->trial_ends_at === null || $this->trial_ends_at->isFuture();
        }
        return $this->status === 'active';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'active')
              ->orWhere(function ($q2) {
                  $q2->where('status', 'trial')
                     ->where(function ($q3) {
                         $q3->whereNull('trial_ends_at')
                            ->orWhere('trial_ends_at', '>', now());
                     });
              })
              ->orWhere(function ($q2) {
                  $q2->where('status', 'cancelled')
                     ->where('ends_at', '>', now());
              });
        });
    }
}
