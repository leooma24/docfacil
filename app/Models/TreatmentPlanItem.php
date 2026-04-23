<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentPlanItem extends Model
{
    protected $fillable = [
        'treatment_plan_id', 'service_id',
        'description', 'quantity', 'unit_price', 'subtotal',
        'tooth_number', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (TreatmentPlanItem $item) {
            $item->subtotal = (float) $item->unit_price * (int) $item->quantity;
        });
    }

    public function treatmentPlan(): BelongsTo { return $this->belongsTo(TreatmentPlan::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
}
