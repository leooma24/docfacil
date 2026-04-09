<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentForm extends Model
{
    use BelongsToClinic;
    protected $fillable = [
        'clinic_id', 'patient_id', 'doctor_id',
        'title', 'content', 'procedure_name',
        'risks', 'alternatives', 'signature',
        'signed_at', 'signed_ip',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }
}
