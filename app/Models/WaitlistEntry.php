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

    /** El mensaje para ofrecerle el horario, de usted como todo lo que sale a pacientes. */
    public function mensajeDeOferta(\DateTimeInterface $horario): string
    {
        $horario = \Carbon\Carbon::instance($horario);
        $nombre = trim((string) $this->patient?->first_name) ?: 'Hola';
        $consultorio = $this->clinic?->name ?? 'su consultorio';

        return "Hola {$nombre}, le escribo de {$consultorio}. Está en nuestra lista de espera y se acaba de liberar un horario:\n\n"
            . 'Fecha: ' . $horario->locale('es')->isoFormat('dddd D [de] MMMM') . "\n"
            . 'Hora: ' . $horario->format('H:i') . " hrs\n\n"
            . 'Si le acomoda, contésteme SÍ y se lo aparto. Es para el primero que confirme.';
    }

    /**
     * Le ofrece el horario: queda "Notificado" (con el hueco, si viene de una
     * cancelación, para apartárselo con un clic cuando diga que sí) y regresa
     * la liga de WhatsApp con el mensaje. El mensaje lo manda el doctor desde
     * su WhatsApp; DocFácil no envía nada.
     */
    public function ofrecer(\DateTimeInterface $horario, ?\App\Models\Appointment $hueco = null): ?string
    {
        $telefono = preg_replace('/\D/', '', (string) $this->patient?->phone);
        if ($telefono === '') {
            return null;
        }
        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
        }

        $this->update([
            'status' => 'notified',
            'notified_at' => now(),
            'notified_for_appointment_id' => $hueco && \Carbon\Carbon::instance($horario)->equalTo($hueco->starts_at) ? $hueco->id : null,
        ]);

        return "https://wa.me/{$telefono}?text=" . urlencode($this->mensajeDeOferta($horario));
    }

    /**
     * Los botones del aviso de una cancelación: "Ofrecer a Diego" por cada
     * candidato y, al final, la lista de espera para ese hueco.
     *
     * @return array<int, \Filament\Notifications\Actions\Action>
     */
    public static function botonesParaElHueco(\App\Models\Appointment $hueco, \Illuminate\Support\Collection $candidatos): array
    {
        // Dos botones caben en el aviso sin que se encimen: los primeros dos
        // (urgentes y quien lleva más esperando). Los demás, en la lista.
        $botones = $candidatos
            ->filter(fn (self $entrada) => ! empty($entrada->patient?->phone))
            ->take(2)
            ->map(fn (self $entrada) => \Filament\Notifications\Actions\Action::make('ofrecer_' . $entrada->id)
                ->label('Ofrecer a ' . (strtok(trim((string) $entrada->patient?->first_name), ' ') ?: 'paciente'))
                ->url(route('lista-espera.ofrecer', ['entrada' => $entrada->id, 'cita' => $hueco->id]))
                ->openUrlInNewTab()
                ->button())
            ->values()
            ->all();

        $botones[] = \Filament\Notifications\Actions\Action::make('lista')
            ->label('Ver lista de espera')
            ->url($hueco->ligaAListaDeEspera())
            ->link();

        return $botones;
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
