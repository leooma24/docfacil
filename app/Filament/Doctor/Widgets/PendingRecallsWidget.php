<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Models\Service;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

/**
 * Recalls pendientes esta semana — pacientes que hace X meses recibieron
 * un servicio con recall_months y ya les toca regresar. Lista compacta
 * con click-to-wa.me para abrir el mensaje en el WhatsApp del doctor.
 *
 * Feature-gated por recall_automation (add-on $49/mes).
 *
 * Query logic: para cada paciente, encontramos su CITA MAS RECIENTE
 * que haya sido completada con un servicio que tiene recall_months.
 * Calculamos la fecha "due" = completed_at + recall_months. Si esa
 * fecha cae entre hoy y los próximos 14 días (o ya es pasado), aparece
 * en el widget. Excluimos pacientes que ya tienen una cita futura
 * agendada (ya saben que regresan).
 */
class PendingRecallsWidget extends Widget
{
    protected static string $view = 'filament.doctor.widgets.pending-recalls';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $clinic = auth()->user()?->clinic;
        if (!$clinic || !$clinic->hasFeature('recall_automation')) {
            return false;
        }
        // Solo mostrar si hay al menos 1 servicio con recall_months configurado
        return Service::where('clinic_id', auth()->user()->clinic_id)
            ->whereNotNull('recall_months')
            ->where('recall_months', '>', 0)
            ->exists();
    }

    public function getViewData(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $clinic = auth()->user()->clinic;
        $clinicName = $clinic?->name ?? 'tu consultorio';

        // Subquery: la cita más reciente completada con servicio de recall por paciente
        $latestRecallAppointments = Appointment::query()
            ->where('appointments.clinic_id', $clinicId)
            ->where('appointments.status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->whereNotNull('services.recall_months')
            ->where('services.recall_months', '>', 0)
            ->select(
                'appointments.patient_id',
                DB::raw('MAX(appointments.starts_at) as last_recall_visit')
            )
            ->groupBy('appointments.patient_id')
            ->get();

        if ($latestRecallAppointments->isEmpty()) {
            return ['recalls' => collect(), 'total_due' => 0];
        }

        // Pacientes con cita futura agendada — excluir (ya regresarán)
        $patientIdsWithFutureAppts = Appointment::where('clinic_id', $clinicId)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('starts_at', '>', now())
            ->pluck('patient_id')
            ->unique()
            ->values();

        $dueRecalls = collect();
        foreach ($latestRecallAppointments as $row) {
            if ($patientIdsWithFutureAppts->contains($row->patient_id)) continue;

            // Obtener el servicio + recall_months de esa cita para calcular due
            $appt = Appointment::with(['patient', 'service'])
                ->where('clinic_id', $clinicId)
                ->where('patient_id', $row->patient_id)
                ->where('starts_at', $row->last_recall_visit)
                ->first();

            if (!$appt || !$appt->service || !$appt->service->recall_months) continue;
            if (!$appt->patient || empty($appt->patient->phone)) continue;

            $dueAt = $appt->starts_at->copy()->addMonths((int) $appt->service->recall_months);
            $daysUntilDue = (int) now()->startOfDay()->diffInDays($dueAt->startOfDay(), false);

            // Mostrar: ya vencidos + los que vencen en <=14 días
            if ($daysUntilDue > 14) continue;

            $patient = $appt->patient;
            $firstName = $patient->first_name ?: 'hola';
            $phoneDigits = preg_replace('/\D/', '', (string) $patient->phone);
            if (strlen($phoneDigits) === 10) $phoneDigits = '52' . $phoneDigits;

            $serviceName = $appt->service->name;
            $monthsAgo = (int) $appt->starts_at->diffInMonths(now());

            $message = "¡Hola {$firstName}! Te escribimos de *{$clinicName}*.\n\n"
                . "Hace {$monthsAgo} meses te hicimos *{$serviceName}* y ya te toca tu seguimiento. "
                . "Recordarte es parte de cuidarte bien.\n\n"
                . "¿Te apartamos una cita esta o la próxima semana? Responde cuándo te acomoda mejor y lo arreglamos.";

            $dueRecalls->push([
                'patient_id' => $patient->id,
                'patient_name' => trim(($patient->first_name ?: '') . ' ' . ($patient->last_name ?: '')),
                'service_name' => $serviceName,
                'last_visit' => $appt->starts_at->format('d/m/Y'),
                'months_since' => $monthsAgo,
                'days_until_due' => $daysUntilDue,
                'status' => $daysUntilDue < 0
                    ? 'overdue'
                    : ($daysUntilDue <= 7 ? 'due_soon' : 'upcoming'),
                'wa_url' => !empty($phoneDigits) && strlen($phoneDigits) >= 12
                    ? "https://wa.me/{$phoneDigits}?text=" . urlencode($message)
                    : null,
                'phone' => $patient->phone,
            ]);
        }

        $dueRecalls = $dueRecalls->sortBy('days_until_due')->values();

        return [
            'recalls' => $dueRecalls,
            'total_due' => $dueRecalls->count(),
        ];
    }
}
