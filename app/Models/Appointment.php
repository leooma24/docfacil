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
    ): \Illuminate\Database\Eloquent\Collection {
        if (! $doctorId) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return static::withoutGlobalScopes()
            ->with('patient')
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->whereIn('status', self::ESTADOS_QUE_OCUPAN)
            ->when($exceptoId, fn ($q) => $q->where('id', '!=', $exceptoId))
            ->where('starts_at', '<', $fin)
            ->where('ends_at', '>', $inicio)
            ->orderBy('starts_at')
            ->get();
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

        foreach ($doctores as $doctorId) {
            if (self::traslapes($clinicId, $doctorId, $inicio, $fin)->isEmpty()) {
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
    ): ?string {
        $choques = self::traslapes($clinicId, $doctorId, $inicio, $fin, $exceptoId);

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
