<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicConsultationSettings extends Model
{
    protected $table = 'clinic_consultation_settings';

    protected $fillable = [
        'clinic_id',
        'enabled_fields',
    ];

    protected $casts = [
        'enabled_fields' => 'array',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
