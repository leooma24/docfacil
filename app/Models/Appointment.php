<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Appointment extends Model
{
    use LogsActivity, BelongsToClinic;

    /**
     * Cuántas veces se mueve una cita antes de que ya no valga la pena.
     *
     * Tres es donde se nota: el que ya movió tres veces casi nunca llega a
     * la cuarta. No es una regla dura, es cuándo conviene llamarle.
     */
    public const REAGENDADAS_PARA_PREOCUPARSE = 3;

    protected static function booted(): void
    {
        // Cada vez que a una cita le cambian la hora, se cuenta.
        //
        // El dato ya quedaba en la bitácora de actividad, pero ahí nadie lo
        // mira: para el doctor era invisible que el paciente de las 4 ya
        // había movido su cita tres veces.
        static::updating(function (self $cita) {
            if ($cita->isDirty('starts_at') && $cita->getOriginal('starts_at')) {
                $cita->veces_reagendada = (int) $cita->veces_reagendada + 1;
            }
        });
    }

    /** ¿Esta cita ya se movió tantas veces que conviene confirmarla a mano? */
    public function seHaMovidoDeMas(): bool
    {
        return (int) $this->veces_reagendada >= self::REAGENDADAS_PARA_PREOCUPARSE;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'starts_at', 'notes'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Cita {$eventName}");
    }

    protected $fillable = [
        'clinic_id', 'doctor_id', 'patient_id', 'service_id',
        'starts_at', 'ends_at', 'status', 'notes', 'reminder_sent',
        'consultation_data',
        'reminder_24h_sent_at', 'reminder_2h_sent_at', 'followup_sent_at', 'confirmed_at',
        'review_request_sent_at',
        'veces_reagendada',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'reminder_sent' => 'boolean',
            'consultation_data' => 'array',
            'reminder_24h_sent_at' => 'datetime',
            'reminder_2h_sent_at' => 'datetime',
            'followup_sent_at' => 'datetime',
            'review_request_sent_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'veces_reagendada' => 'integer',
        ];
    }

    /**
     * Estados que de verdad ocupan el sillón.
     *
     * Una cita cancelada o a la que el paciente no llegó libera el horario;
     * una completada ya pasó y no estorba para agendar de nuevo ahí.
     */
    public const ESTADOS_QUE_OCUPAN = ['scheduled', 'confirmed', 'in_progress'];

    /**
     * Citas del mismo doctor que chocan con este horario.
     *
     * Dos citas se traslapan si una empieza antes de que la otra termine y
     * termina después de que la otra empieza. Que una acabe justo cuando la
     * siguiente empieza (11:00 y 11:00) no es traslape.
     *
     * @param  int|null  $exceptoId  La cita que se está editando, para que no
     *                               choque consigo misma.
     */
    public static function traslapes(
        int $clinicId,
        ?int $doctorId,
        \DateTimeInterface $inicio,
        \DateTimeInterface $fin,
        ?int $exceptoId = null,
        int $margenMinutos = 0,
    ): \Illuminate\Database\Eloquent\Collection {
        if (! $doctorId) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        // El margen es el tiempo de limpiar y esterilizar entre pacientes: no
        // es parte de la cita, pero tampoco se puede regalar. Con margen, dos
        // citas que solo se tocan de punta a punta ya cuentan como choque.
        $desde = \Carbon\CarbonImmutable::instance(\Carbon\Carbon::instance($inicio))->subMinutes($margenMinutos);
        $hasta = \Carbon\CarbonImmutable::instance(\Carbon\Carbon::instance($fin))->addMinutes($margenMinutos);

        return static::withoutGlobalScopes()
            ->with('patient')
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->whereIn('status', self::ESTADOS_QUE_OCUPAN)
            ->when($exceptoId, fn ($q) => $q->where('id', '!=', $exceptoId))
            ->where('starts_at', '<', $hasta)
            ->where('ends_at', '>', $desde)
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * Aviso cuando la cita cabe, pero queda pegada a la de junto.
     *
     * No bloquea: al doctor que está viendo su propia agenda hay que dejarlo
     * meter la urgencia de las 3 de la tarde. Nada más se le dice, para que
     * sepa que ese día va a arrancar corriendo.
     *
     * Devuelve null si no hay margen configurado o si el espacio alcanza.
     */
    public static function avisoDeEspacio(
        int $clinicId,
        ?int $doctorId,
        \DateTimeInterface $inicio,
        \DateTimeInterface $fin,
        ?int $exceptoId = null,
    ): ?string {
        $margen = Clinic::withoutGlobalScopes()->find($clinicId)?->minutosEntreCitas() ?? 0;

        if ($margen < 1) {
            return null;
        }

        $conMargen = self::traslapes($clinicId, $doctorId, $inicio, $fin, $exceptoId, $margen);
        $sinMargen = self::traslapes($clinicId, $doctorId, $inicio, $fin, $exceptoId);

        // Lo que choca de verdad ya lo reporta mensajeDeTraslape. Aquí solo
        // interesa lo que entró por el margen.
        $pegadas = $conMargen->whereNotIn('id', $sinMargen->pluck('id'));

        if ($pegadas->isEmpty()) {
            return null;
        }

        $vecina = $pegadas->first();
        $paciente = trim(($vecina->patient->first_name ?? '') . ' ' . ($vecina->patient->last_name ?? ''));
        $de = $paciente !== '' ? " de {$paciente}" : '';

        return "Queda pegada a la cita de {$vecina->starts_at->format('H:i')} a "
            . "{$vecina->ends_at->format('H:i')}{$de}. Tienes {$margen} minutos "
            . 'configurados entre citas para limpiar y esterilizar.';
    }

    /**
     * Cuánto antes, como mínimo, se puede apartar una cita.
     *
     * Sin esto el portal aceptaba una cita para dentro de dos minutos, que
     * no le sirve a nadie: el paciente no alcanza a llegar y el doctor no
     * alcanza a verla.
     */
    public const ANTICIPACION_MINIMA_MINUTOS = 60;

    /**
     * Un doctor del consultorio que tenga ese horario libre.
     *
     * Cuando el paciente elige "cualquiera" en el portal no había a quién
     * revisar: traslapes() salía vacío sin doctor y dos pacientes podían
     * apartar la misma hora. Ahora se le asigna uno que de verdad esté libre,
     * y si no hay ninguno, el horario simplemente no se ofrece.
     */
    public static function doctorLibreEn(
        int $clinicId,
        \DateTimeInterface $inicio,
        \DateTimeInterface $fin,
    ): ?int {
        $doctores = \App\Models\Doctor::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->orderBy('id')
            ->pluck('id');

        $margen = Clinic::withoutGlobalScopes()->find($clinicId)?->minutosEntreCitas() ?? 0;

        foreach ($doctores as $doctorId) {
            if (self::traslapes($clinicId, $doctorId, $inicio, $fin, null, $margen)->isEmpty()) {
                return $doctorId;
            }
        }

        return null;
    }

    /**
     * Mensaje listo para mostrarle al doctor, o null si el horario está libre.
     */
    public static function mensajeDeTraslape(
        int $clinicId,
        ?int $doctorId,
        \DateTimeInterface $inicio,
        \DateTimeInterface $fin,
        ?int $exceptoId = null,
        int $margenMinutos = 0,
    ): ?string {
        $choques = self::traslapes($clinicId, $doctorId, $inicio, $fin, $exceptoId, $margenMinutos);

        if ($choques->isEmpty()) {
            return null;
        }

        $primera = $choques->first();
        $paciente = trim(($primera->patient->first_name ?? '') . ' ' . ($primera->patient->last_name ?? ''));
        $horario = $primera->starts_at->format('H:i') . ' a ' . $primera->ends_at->format('H:i');

        $mensaje = "Ese horario choca con la cita de {$horario}";
        $mensaje .= $paciente !== '' ? " de {$paciente}." : '.';

        if ($choques->count() > 1) {
            $mensaje .= ' (y ' . ($choques->count() - 1) . ' más)';
        }

        return $mensaje;
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
