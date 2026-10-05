<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Support\Collection;

/**
 * Lo que dice la pantalla de la sala de espera: quién está en consulta y
 * quién sigue.
 *
 * En la sala hay otras personas y esto es información de salud, así que de
 * cada paciente sale el nombre y la inicial del apellido ("Ana R.") y nada
 * más: ni el nombre completo ni a qué viene.
 */
class SalaDeEspera
{
    /** Cuántos se enseñan en "Siguen": en una tele no caben más con letra que se lea. */
    public const CUANTOS_SIGUEN = 5;

    /**
     * @return array{enConsulta: Collection, siguen: Collection}
     */
    public static function para(Clinic $clinica): array
    {
        $deHoy = fn () => Appointment::withoutGlobalScopes()
            ->where('clinic_id', $clinica->id)
            ->whereDate('starts_at', today())
            ->with(['patient', 'doctor.user'])
            ->orderBy('starts_at');

        $enConsulta = $deHoy()->where('status', 'in_progress')->get()
            ->map(fn (Appointment $cita) => [
                'nombre' => self::nombreCorto($cita->patient),
                'doctor' => $cita->doctor?->user?->displayName(),
            ]);

        // El que ya pasó su hora por más de una hora y no ha llegado no se
        // queda en la lista estorbando: probablemente no viene.
        $siguen = $deHoy()->whereIn('status', ['scheduled', 'confirmed'])
            ->where(fn ($q) => $q->whereNotNull('arrived_at')->orWhere('starts_at', '>=', now()->subHour()))
            ->limit(self::CUANTOS_SIGUEN)
            ->get()
            ->map(fn (Appointment $cita) => [
                'nombre' => self::nombreCorto($cita->patient),
                'hora' => $cita->starts_at->format('H:i'),
                'llego' => $cita->arrived_at !== null,
            ]);

        return ['enConsulta' => $enConsulta, 'siguen' => $siguen];
    }

    /** "Ana Sofía Ruiz Pérez" → "Ana R.". */
    public static function nombreCorto(?Patient $paciente): string
    {
        $nombre = strtok(trim((string) $paciente?->first_name), ' ') ?: 'Paciente';
        $inicial = mb_substr(trim((string) $paciente?->last_name), 0, 1);

        return $inicial !== '' ? $nombre . ' ' . mb_strtoupper($inicial) . '.' : $nombre;
    }
}
