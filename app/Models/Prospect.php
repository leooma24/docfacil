<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Prospect extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'website', 'has_whatsapp',
        'osm_id', 'latitude', 'longitude',
        'clinic_name', 'city',
        'specialty', 'source', 'status', 'notes',
        'contacted_at', 'converted_at', 'address',
        'assigned_to_sales_rep_id', 'converted_clinic_id',
        'last_followup_at', 'next_followup_at',
        'contact_day', 'last_contact_method', 'next_contact_at', 'outreach_started_at',
        'objections_faced', 'demo_scheduled_at', 'demo_completed_at',
        'conversation_log', 'lead_score',
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
            'demo_scheduled_at' => 'datetime',
            'demo_completed_at' => 'datetime',
            'contact_day' => 'integer',
            'objections_faced' => 'array',
            'conversation_log' => 'array',
            'lead_score' => 'integer',
            'has_whatsapp' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Catálogo de objeciones con claves cortas para tracking.
     */
    public const OBJECTION_CATALOG = [
        'price_expensive' => 'Está caro / No tengo presupuesto',
        'price_excel' => '¿Por qué pagar si uso Excel?',
        'price_free' => 'Hay opciones gratis',
        'price_unsure' => 'No sé si lo voy a usar',
        'tech_not_techie' => 'No soy tecnológico',
        'tech_paper_works' => 'El papel me funciona bien',
        'tech_has_system' => 'Ya tengo otro sistema',
        'tech_internet' => '¿Y si se cae el internet?',
        'trust_data' => '¿Mis datos están seguros?',
        'trust_who' => '¿Quién está detrás? No los conozco',
        'trust_disappear' => '¿Y si desaparecen?',
        'trust_think' => 'Necesito pensarlo',
        'timing_not_now' => 'Ahorita no es buen momento',
        'timing_more_patients' => 'Cuando tenga más pacientes',
        'specific_govt' => 'Solo atiendo IMSS/ISSSTE',
        'specific_old' => 'Ya estoy viejo para esto',
        'specific_small' => 'Mi consultorio es muy pequeño',
    ];

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
