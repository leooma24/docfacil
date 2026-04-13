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
        'contact_day', 'last_contact_method', 'next_contact_at', 'outreach_started_at',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'converted_at' => 'datetime',
            'last_followup_at' => 'datetime',
            'next_followup_at' => 'datetime',
            'next_contact_at' => 'datetime',
            'outreach_started_at' => 'datetime',
            'contact_day' => 'integer',
        ];
    }

    /**
     * Cadencia de contacto: día actual → próximo día.
     */
    public const CADENCE = [0 => 1, 1 => 3, 3 => 7, 7 => 14, 14 => 30, 30 => null];

    public function advanceContactDay(string $method): void
    {
        $nextDay = self::CADENCE[$this->contact_day] ?? null;

        if ($nextDay === null) {
            // Cadencia terminada
            $this->update([
                'last_contact_method' => $method,
                'last_followup_at' => now(),
                'next_contact_at' => null,
            ]);
            return;
        }

        $daysUntilNext = $nextDay - $this->contact_day;

        $this->update([
            'contact_day' => $nextDay,
            'last_contact_method' => $method,
            'last_followup_at' => now(),
            'next_contact_at' => now()->addDays($daysUntilNext),
            'outreach_started_at' => $this->outreach_started_at ?? now(),
        ]);
    }

    /**
     * Días desde el inicio del outreach.
     */
    public function daysSinceOutreach(): ?int
    {
        return $this->outreach_started_at?->diffInDays(now());
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
