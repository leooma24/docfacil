<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Patient extends Model
{
    use LogsActivity, BelongsToClinic;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'phone', 'email', 'allergies', 'medical_notes'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Paciente {$eventName}");
    }

    protected $fillable = [
        'clinic_id', 'first_name', 'last_name', 'email', 'phone',
        'birth_date', 'gender', 'address', 'allergies',
        'medical_notes', 'blood_type', 'is_active',
        // Cuenta del paciente en el portal. Faltaba aqui, asi que Eloquent
        // descartaba la asignacion sin decir nada y el paciente nunca
        // quedaba ligado a su usuario.
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Cuántas veces este paciente ha movido sus citas en los últimos meses.
     *
     * Es la señal más barata de que no va a llegar: el que reagenda tres
     * veces casi nunca aparece a la cuarta. Al doctor le sirve saberlo
     * ANTES de guardarle el lugar, no después.
     */
    public function vecesQueHaMovidoCitas(int $meses = 6): int
    {
        return (int) $this->appointments()
            ->where('starts_at', '>=', now()->subMonths($meses))
            ->sum('veces_reagendada');
    }

    /** ¿Conviene confirmarle la cita a mano antes de apartarle el horario? */
    public function reagendaDeMas(int $meses = 6): bool
    {
        return $this->vecesQueHaMovidoCitas($meses) >= Appointment::REAGENDADAS_PARA_PREOCUPARSE;
    }
}
