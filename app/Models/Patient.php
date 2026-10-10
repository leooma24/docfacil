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

        // Cambiar alergias, notas o casillas cuenta como revisar: quien lo
        // cambió ya lo vio, así que no se le vuelve a preguntar "¿sigue igual?".
        static::saving(function (self $paciente) {
            if ($paciente->isDirty(['allergies', 'medical_notes', 'riesgos']) && ! $paciente->isDirty('riesgos_revisados_at')) {
                $paciente->riesgos_revisados_at = now();
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
        // Casillas de lo importante (ver AlertasClinicas::OPCIONES) y cuándo
        // se confirmó por última vez que sigue igual.
        'riesgos', 'riesgos_revisados_at',
        // Quien responde por él: la mamá del niño, el papá que paga.
        'responsable_id',
        // Cuenta del paciente en el portal. Faltaba aqui, asi que Eloquent
        // descartaba la asignacion sin decir nada y el paciente nunca
        // quedaba ligado a su usuario.
        'user_id',
        // Prueba de que aceptó el aviso de privacidad (ver AvisoDePrivacidad).
        'aviso_privacidad_aceptado_at', 'aviso_privacidad_version', 'aviso_privacidad_medio',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'riesgos' => 'array',
            'riesgos_revisados_at' => 'datetime',
            'aviso_privacidad_aceptado_at' => 'datetime',
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

    /** Se le preguntó y dijo que no tiene: "Ninguna conocida". */
    public const SIN_ALERGIAS = 'Ninguna conocida';

    /**
     * Las notas médicas más lo que el doctor marcó con casillas, en un solo
     * texto: es lo que lee AlertasClinicas para avisar al recetar.
     */
    public function notasParaAlertas(): string
    {
        $marcadas = collect($this->riesgos ?? [])
            ->map(fn ($clave) => \App\Support\AlertasClinicas::OPCIONES[$clave] ?? null)
            ->filter()->implode(', ');

        return trim((string) $this->medical_notes . "\n" . $marcadas);
    }

    /** Sus archivos: la foto de su hoja vieja, radiografías, estudios. */
    public function archivos(): HasMany
    {
        return $this->hasMany(PatientFile::class);
    }

    /** Quien responde por él (la mamá del niño, el papá que paga). */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(self::class, 'responsable_id');
    }

    /** Los que tienen a este paciente como responsable (sus hijos). */
    public function dependientes(): HasMany
    {
        return $this->hasMany(self::class, 'responsable_id');
    }

    /**
     * A qué WhatsApp se le escribe: el de su responsable si tiene, si no el
     * suyo. "Mi contacto es el WhatsApp de la mamá" (dentista, 10-oct-2026).
     */
    public function telefonoDeContacto(): ?string
    {
        $suyo = filled($this->phone) ? $this->phone : null;

        return $this->responsable && filled($this->responsable->phone) ? $this->responsable->phone : $suyo;
    }

    /** A quién se saluda en el mensaje: al responsable si lo hay. */
    public function nombreDeContacto(): string
    {
        return trim((string) ($this->responsable?->first_name ?: $this->first_name));
    }

    /**
     * Lo que debe toda su familia (el responsable y sus dependientes), por
     * persona. Desde el niño o desde la mamá, se ve lo mismo.
     *
     * @return array{total: float, porPersona: array<string, float>}
     */
    public function deudaFamiliar(): array
    {
        $cabeza = $this->responsable ?? $this;
        $familia = collect([$cabeza])->merge($cabeza->dependientes()->get());

        $saldos = Payment::withoutGlobalScopes()
            ->where('clinic_id', $this->clinic_id)
            ->whereIn('patient_id', $familia->pluck('id'))
            ->withBalance()->yaToca()
            ->selectRaw('patient_id, SUM(amount - amount_paid) as saldo')
            ->groupBy('patient_id')
            ->pluck('saldo', 'patient_id');

        $porPersona = $familia
            ->mapWithKeys(fn ($p) => [$p->first_name => round((float) ($saldos[$p->id] ?? 0), 2)])
            ->filter(fn ($s) => $s > 0)
            ->all();

        return ['total' => round(array_sum($porPersona), 2), 'porPersona' => $porPersona];
    }

    /** Tiene alergias de verdad (no vacío y no "Ninguna conocida"). */
    public function tieneAlergias(): bool
    {
        return filled($this->allergies) && $this->allergies !== self::SIN_ALERGIAS;
    }

    /**
     * El paciente del consultorio con ese teléfono, aunque uno esté escrito
     * "668 123 4567" y el otro "6681234567" (se comparan los últimos 10 dígitos).
     */
    public static function porTelefono(int $clinicId, ?string $telefono): ?self
    {
        $digitos = substr(preg_replace('/\D/', '', (string) $telefono), -10);
        if (strlen($digitos) < 10) {
            return null;
        }

        return static::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->where('phone', 'like', '%' . substr($digitos, -4))
            ->get()
            ->first(fn (self $p) => substr(preg_replace('/\D/', '', (string) $p->phone), -10) === $digitos);
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
