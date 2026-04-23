<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use BelongsToClinic;
    protected $fillable = [
        'clinic_id', 'name', 'description', 'price',
        'duration_minutes', 'category', 'is_active', 'recall_months',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'recall_months' => 'integer',
        ];
    }

    public function hasRecall(): bool
    {
        return !empty($this->recall_months) && $this->recall_months > 0;
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
