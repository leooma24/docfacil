<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Prospect extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'clinic_name', 'city',
        'specialty', 'source', 'status', 'notes',
        'contacted_at', 'converted_at', 'address',
        'assigned_to_sales_rep_id', 'converted_clinic_id',
        'last_followup_at', 'next_followup_at',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'converted_at' => 'datetime',
            'last_followup_at' => 'datetime',
            'next_followup_at' => 'datetime',
        ];
    }

    public function lifecycleEmails(): MorphMany
    {
        return $this->morphMany(LifecycleEmail::class, 'emailable');
    }

    public function assignedSalesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_sales_rep_id');
    }

    public function convertedClinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'converted_clinic_id');
    }
}
