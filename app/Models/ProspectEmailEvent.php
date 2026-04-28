<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectEmailEvent extends Model
{
    protected $fillable = [
        'prospect_id', 'email_type', 'event_type',
        'destination_url', 'ip', 'user_agent',
    ];

    public const EMAIL_TYPES = [
        'beta_invite' => 'Email 1 — Invitación',
        'followup' => 'Email 2 — Seguimiento',
        'last_chance' => 'Email 3 — Última',
    ];

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }
}
