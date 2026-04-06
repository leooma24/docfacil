<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MedicalRecord extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['diagnosis', 'treatment', 'chief_complaint'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Expediente {$eventName}");
    }

    protected $fillable = [
        'clinic_id', 'patient_id', 'doctor_id', 'appointment_id',
        'visit_date', 'chief_complaint', 'diagnosis', 'treatment',
        'notes', 'vital_signs', 'attachments',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'vital_signs' => 'array',
            'attachments' => 'array',
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
