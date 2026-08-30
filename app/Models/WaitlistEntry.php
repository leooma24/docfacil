<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitlistEntry extends Model
{
    use HasFactory, BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'service_id',
        'doctor_id',
        'desired_from',
        'desired_to',
        'priority',
        'notes',
        'status',
        'notified_at',
        'notified_for_appointment_id',
    ];

    protected function casts(): array
    {
        return [
            'desired_from' => 'date',
            'desired_to' => 'date',
            'notified_at' => 'datetime',
            'priority' => 'integer',
        ];
    }

    /**
     * Quién de la lista de espera cabe en un hueco que se acaba de liberar.
     *
     * Cuando un paciente cancela, el doctor no tiene por qué acordarse de a
     * quién le urgía esa fecha. Filtra por la ventana que pidió el paciente
     * y por el doctor, si es que pidió uno en particular, y pone primero a
     * los urgentes y a los que llevan más tiempo esperando.
     */
    public static function candidatosPara(\App\Models\Appointment $hueco, int $limite = 5): \Illuminate\Database\Eloquent\Collection
    {
        $dia = $hueco->starts_at->toDateString();

        return static::withoutGlobalScopes()
            ->with(['patient', 'service'])
            ->where('clinic_id', $hueco->clinic_id)
            ->where('status', 'waiting')
            // El dia liberado cae dentro de la ventana que pidio el paciente.
            ->whereDate('desired_from', '<=', $dia)
            ->whereDate('desired_to', '>=', $dia)
            // Si pidio doctor especifico, tiene que ser ese. Si le da igual,
            // entra para cualquiera.
            ->where(fn ($q) => $q->whereNull('doctor_id')->orWhere('doctor_id', $hueco->doctor_id))
            ->orderByDesc('priority')   // urgentes primero
            ->orderBy('created_at')     // y de esos, quien lleva mas esperando
            ->limit($limite)
            ->get();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
