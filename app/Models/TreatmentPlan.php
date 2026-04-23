<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentPlan extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'patient_id', 'doctor_id',
        'title', 'description',
        'subtotal', 'discount', 'total',
        'status', 'public_token',
        'sent_at', 'accepted_at', 'accepted_ip',
        'rejected_at', 'valid_until', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'valid_until' => 'date',
        ];
    }

    public function clinic(): BelongsTo { return $this->belongsTo(Clinic::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
    public function items(): HasMany { return $this->hasMany(TreatmentPlanItem::class)->orderBy('sort_order'); }

    public function generatePublicToken(): string
    {
        $this->public_token = bin2hex(random_bytes(32));
        $this->save();
        return $this->public_token;
    }

    public function recalculateTotal(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $this->subtotal = $subtotal;
        $this->total = max(0, $subtotal - (float) $this->discount);
        $this->save();
    }
}
