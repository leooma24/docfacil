<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorConsultationSettings extends Model
{
    protected $table = 'doctor_consultation_settings';

    protected $fillable = [
        'doctor_id',
        'enabled_fields',
        'inherits_clinic_config',
    ];

    protected $casts = [
        'enabled_fields' => 'array',
        'inherits_clinic_config' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
