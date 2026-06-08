<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

/**
 * Detecta el patrón de cadencia de citas de un paciente con un doctor.
 *
 * Caso de uso: ortodoncia / controles periódicos. El doctor ve "Visita rápida"
 * y queremos pre-llenar la próxima cita sin que tenga que pensar.
 *
 * Ej. paciente con citas cada ~4 semanas → sugerir +4 semanas desde hoy.
 * Si no hay patrón claro o son menos de 2 visitas, devuelve null (el doctor
 * captura manualmente).
 */
class AppointmentPatternService
{
    /**
     * Sugiere fecha de próxima cita basado en las últimas N citas COMPLETADAS
     * del paciente con cualquier doctor de la clínica.
     *
     * Algoritmo:
     *  - Toma las últimas 4 citas completadas
     *  - Calcula intervalos entre ellas (días)
     *  - Promedio de intervalos → días a sumar
     *  - Mínimo 7 días, máximo 180 días (cordura)
     *
     * @return Carbon|null
     */
    public static function suggestNextDate(int $patientId, int $clinicId): ?Carbon
    {
        $past = Appointment::where('clinic_id', $clinicId)
            ->where('patient_id', $patientId)
            ->where('status', 'completed')
            ->orderByDesc('starts_at')
            ->limit(4)
            ->pluck('starts_at')
            ->toArray();

        if (count($past) < 2) {
            return null;
        }

        // Intervalos en días entre citas consecutivas (ya ordenadas desc)
        $intervals = [];
        for ($i = 0; $i < count($past) - 1; $i++) {
            $intervals[] = abs($past[$i]->diffInDays($past[$i + 1]));
        }

        $avg = (int) round(array_sum($intervals) / count($intervals));

        // Cordura: rangos razonables
        if ($avg < 7) $avg = 7;
        if ($avg > 180) $avg = 180;

        return now()->addDays($avg)->setHour(10)->setMinute(0)->setSecond(0);
    }

    /**
     * Ejecuta una "visita rápida" desde un Appointment.
     * Extraído del Filament Action para poder testear sin Livewire/Filament.
     *
     * @param  array{note?:string, charge?:bool, amount?:int|float|string, payment_method?:string,
     *               next_appointment_date?:string, next_service_id?:int|string|null}  $data
     */
    public static function executeQuickVisit(\App\Models\Appointment $appointment, array $data): void
    {
        $clinicId = $appointment->clinic_id;

        // 1. Si hay nota, crear MedicalRecord ligero
        if (! empty($data['note'])) {
            \App\Models\MedicalRecord::create([
                'clinic_id' => $clinicId,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'visit_date' => now()->toDateString(),
                'notes' => $data['note'],
            ]);
        }

        // 2. Marcar cita actual como completada
        $appointment->update(['status' => 'completed']);

        // 3. Cobro opcional
        if (! empty($data['charge']) && ! empty($data['amount']) && $data['amount'] > 0) {
            \App\Models\Payment::create([
                'clinic_id' => $clinicId,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'service_id' => $appointment->service_id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'status' => 'paid',
                'payment_date' => now()->toDateString(),
            ]);
        }

        // 4. Próxima cita si capturó fecha
        if (! empty($data['next_appointment_date'])) {
            $starts = Carbon::parse($data['next_appointment_date']);
            $serviceId = $data['next_service_id'] ?? $appointment->service_id;
            $duration = \App\Models\Service::find($serviceId)?->duration_minutes ?? 30;

            \App\Models\Appointment::create([
                'clinic_id' => $clinicId,
                'doctor_id' => $appointment->doctor_id,
                'patient_id' => $appointment->patient_id,
                'service_id' => $serviceId,
                'starts_at' => $starts,
                'ends_at' => $starts->copy()->addMinutes($duration),
                'status' => 'scheduled',
            ]);
        }
    }
}
