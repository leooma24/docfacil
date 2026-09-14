<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use App\Models\Concerns\Lockable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MedicalRecord extends Model
{
    use LogsActivity, BelongsToClinic, Lockable;

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
        // Extended vitals (configurables por especialidad — ver SpecialtyService::FIELD_CATALOG)
        'respiratory_rate', 'oxygen_saturation', 'height',
        'head_circumference', 'cie10_codes',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'vital_signs' => 'array',
            'attachments' => 'array',
            'locked_at' => 'datetime',
            'cie10_codes' => 'array',
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

    /**
     * Quién elaboró la nota y cuándo quedó registrada.
     *
     * La NOM-004 (5.10) pide que toda nota lleve fecha, hora y nombre completo
     * de quien la elabora. La fecha de consulta es solo el día: la hora sale de
     * cuándo se guardó, que además ya no se puede cambiar.
     */
    public function autoria(): string
    {
        $doctor = $this->doctor;

        return collect([
            $doctor?->user?->name ?? 'Médico sin registrar',
            $doctor?->license_number ? 'Céd. Prof. ' . $doctor->license_number : null,
            $this->created_at ? $this->created_at->format('d/m/Y') . ' a las ' . $this->created_at->format('H:i') : null,
        ])->filter()->implode(' · ');
    }
}
