<?php

namespace App\Models;

use App\Exceptions\ExpedienteQueSeConserva;
use App\Exceptions\LimiteDePacientesAlcanzado;
use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Patient extends Model
{
    use LogsActivity, BelongsToClinic;

    /**
     * El tope de pacientes del plan se cierra aquí, no en las pantallas.
     *
     * Hay siete caminos que crean pacientes —el formulario, el importador, la
     * consulta, la visita sin cita, agendar, el check-in con QR y el portal
     * público— y parcharlos uno por uno deja huecos. Este es el único lugar
     * por el que pasan todos.
     *
     * Solo bloquea agregar. A un consultorio que ya venía con más pacientes
     * de los que su plan permite —porque se le acabó la prueba, por ejemplo—
     * no se le esconde ni uno: son expedientes clínicos suyos.
     */
    protected static function booted(): void
    {
        static::creating(function (self $paciente) {
            // Corre después del trait, que ya rellenó clinic_id.
            if (empty($paciente->clinic_id)) {
                return;
            }

            $clinica = Clinic::withoutGlobalScopes()->find($paciente->clinic_id);

            if ($clinica && ! $clinica->puedeAgregarPacientes()) {
                throw new LimiteDePacientesAlcanzado($clinica);
            }
        });

        // Borrar al paciente se llevaba en cascada todo su expediente, porque
        // así están las llaves foráneas. Igual que el tope, se cierra aquí
        // para que ningún camino —la ficha, el borrado en grupo— se lo salte.
        static::deleting(function (self $paciente) {
            if ($paciente->tieneExpediente()) {
                throw new ExpedienteQueSeConserva($paciente->id);
            }
        });
    }


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

    public function consentForms(): HasMany
    {
        return $this->hasMany(ConsentForm::class);
    }

    public function odontograms(): HasMany
    {
        return $this->hasMany(Odontogram::class);
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class);
    }

    /**
     * ¿Ya tiene algo de expediente clínico? Entonces se conserva.
     *
     * Sin filtro de consultorio a propósito: esto decide si se puede borrar,
     * y un filtro mal puesto diría "no tiene nada" y lo dejaría borrar.
     */
    public function tieneExpediente(): bool
    {
        return $this->medicalRecords()->withoutGlobalScopes()->exists()
            || $this->prescriptions()->withoutGlobalScopes()->exists()
            || $this->consentForms()->withoutGlobalScopes()->exists()
            || $this->odontograms()->withoutGlobalScopes()->exists()
            || $this->treatmentPlans()->withoutGlobalScopes()->exists();
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
