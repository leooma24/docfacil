<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * Los horarios libres de un día, listos para ofrecérselos al paciente.
 *
 * Antes el paciente escribía una fecha y hora a mano y nosotros se la
 * rechazábamos si estaba ocupada o fuera de horario. Eso son rebotes: cada
 * intento fallido es una oportunidad de que se vaya. Es mejor enseñarle
 * nada más lo que sí puede elegir.
 *
 * Respeta todo lo que ya sabe el consultorio: horario de atención, descanso
 * de comida, días cerrados y las citas que el doctor ya tiene.
 */
class HuecosDisponibles
{
    /** Cada cuántos minutos se ofrece un hueco. */
    public const PASO_MINUTOS = 30;

    /** Cuántos días hacia adelante se puede agendar. */
    public const DIAS_A_FUTURO = 60;

    /**
     * Horas libres de un día, en formato "HH:MM".
     *
     * @param  int  $duracion  Cuánto dura la cita, para no ofrecer un hueco
     *                         donde no cabe.
     */
    public static function delDia(
        Clinic $clinic,
        CarbonImmutable $dia,
        ?int $doctorId,
        int $duracion = 30,
    ): array {
        $duracion = max(15, $duracion);

        // Un día pasado no tiene huecos que ofrecer.
        if ($dia->isBefore(CarbonImmutable::today())) {
            return [];
        }

        $horario = $clinic->horario()[self::claveDelDia($dia)] ?? null;

        if (! $horario || empty($horario['abre']) || empty($horario['cierra'])) {
            return [];
        }

        if ($clinic->cierraEse($dia)) {
            return [];
        }

        $ocupadas = self::citasDelDia($clinic, $dia, $doctorId);

        $huecos = [];
        $cursor = $dia->setTimeFromTimeString($horario['abre']);
        $cierre = $dia->setTimeFromTimeString($horario['cierra']);

        while ($cursor->addMinutes($duracion)->lessThanOrEqualTo($cierre)) {
            $fin = $cursor->addMinutes($duracion);

            if (self::esOfrecible($clinic, $cursor, $fin, $ocupadas)) {
                $huecos[] = $cursor->format('H:i');
            }

            $cursor = $cursor->addMinutes(self::PASO_MINUTOS);
        }

        return $huecos;
    }

    /**
     * Los próximos días que tienen al menos un hueco.
     *
     * Sirve para no mandar al paciente a picarle a un calendario a ciegas.
     *
     * @return array<string, array<int, string>>  fecha => horas libres
     */
    public static function proximosDias(
        Clinic $clinic,
        ?int $doctorId,
        int $duracion = 30,
        int $cuantosDias = 14,
    ): array {
        $agenda = [];
        $dia = CarbonImmutable::today();
        $limite = $dia->addDays(self::DIAS_A_FUTURO);

        while (count($agenda) < $cuantosDias && $dia->lessThan($limite)) {
            $huecos = self::delDia($clinic, $dia, $doctorId, $duracion);

            if ($huecos !== []) {
                $agenda[$dia->toDateString()] = $huecos;
            }

            $dia = $dia->addDay();
        }

        return $agenda;
    }

    /** ¿Ese hueco se puede ofrecer? */
    private static function esOfrecible(
        Clinic $clinic,
        CarbonImmutable $inicio,
        CarbonImmutable $fin,
        \Illuminate\Support\Collection $ocupadas,
    ): bool {
        // No ofrecer horas que ya pasaron, ni las de los próximos minutos: al
        // paciente no le sirve una cita para dentro de dos minutos porque no
        // alcanza a llegar.
        if ($inicio->lessThan(now()->addMinutes(Appointment::ANTICIPACION_MINIMA_MINUTOS))) {
            return false;
        }

        // Horario, comida y cierres los sabe el consultorio.
        if (! $clinic->cabeLaCita($inicio, $fin)) {
            return false;
        }

        // Y que el doctor no lo tenga ya tomado.
        return $ocupadas->every(
            fn (array $cita) => $inicio->greaterThanOrEqualTo($cita['fin'])
                || $fin->lessThanOrEqualTo($cita['inicio'])
        );
    }

    /** Las citas que ya ocupan ese día. */
    private static function citasDelDia(
        Clinic $clinic,
        CarbonImmutable $dia,
        ?int $doctorId,
    ): \Illuminate\Support\Collection {
        return Appointment::withoutGlobalScopes()
            ->where('clinic_id', $clinic->id)
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereIn('status', Appointment::ESTADOS_QUE_OCUPAN)
            ->whereDate('starts_at', $dia->toDateString())
            ->get(['starts_at', 'ends_at'])
            ->map(fn (Appointment $cita) => [
                'inicio' => CarbonImmutable::parse($cita->starts_at),
                'fin' => CarbonImmutable::parse($cita->ends_at),
            ]);
    }

    /** Cómo llama el modelo Clinic al día de esa fecha. */
    private static function claveDelDia(CarbonImmutable $dia): string
    {
        return array_keys(Clinic::DIAS)[(int) $dia->format('N') - 1];
    }

    /**
     * El día, escrito como se lo mostramos al paciente.
     *
     * Solo la primera letra en mayúscula: "Lunes 31 de agosto". En CSS,
     * capitalize se la ponía a cada palabra y quedaba "Lunes 31 De Agosto".
     */
    public static function nombreDelDia(string $fecha): string
    {
        return \Illuminate\Support\Str::ucfirst(
            Carbon::parse($fecha)->translatedFormat('l j \d\e F')
        );
    }
}
