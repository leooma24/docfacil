<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Payment;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

/**
 * Produccion individual por doctor — tabla del mes en curso.
 * Cubre las promesas landing del plan Clinica:
 *   - "Reportes por doctor"
 *   - "Produccion individual por doctor"
 *
 * Feature-gated por per_doctor_reports (solo plan Clinica). Solo se muestra
 * si el consultorio tiene 2+ doctores activos — una clinica unidoctora no
 * gana nada con esto.
 */
class DoctorProductionWidget extends Widget
{
    protected static string $view = 'filament.doctor.widgets.doctor-production';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $clinic = auth()->user()?->clinic;
        if (!$clinic || !$clinic->hasFeature('per_doctor_reports')) {
            return false;
        }
        return $clinic->doctors()->count() >= 2;
    }

    public function getViewData(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        // Citas agrupadas por doctor
        $appointmentStats = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [$start, $end])
            ->select(
                'doctor_id',
                DB::raw('COUNT(*) as total_appointments'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw("SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show")
            )
            ->groupBy('doctor_id')
            ->get()
            ->keyBy('doctor_id');

        // Ingresos agrupados por doctor (via appointment.doctor_id)
        $incomeStats = Payment::where('payments.clinic_id', $clinicId)
            ->where('payments.status', 'paid')
            ->whereBetween('payments.payment_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('payments.appointment_id')
            ->join('appointments', 'payments.appointment_id', '=', 'appointments.id')
            ->select(
                'appointments.doctor_id',
                DB::raw('SUM(payments.amount) as income')
            )
            ->groupBy('appointments.doctor_id')
            ->get()
            ->keyBy('doctor_id');

        $doctors = Doctor::where('clinic_id', $clinicId)
            ->with('user')
            ->get()
            ->map(function (Doctor $doctor) use ($appointmentStats, $incomeStats) {
                $apptRow = $appointmentStats->get($doctor->id);
                $incomeRow = $incomeStats->get($doctor->id);
                $total = (int) ($apptRow->total_appointments ?? 0);
                $completed = (int) ($apptRow->completed ?? 0);
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user?->name ?? 'Sin nombre',
                    'specialty' => $doctor->specialty ?? '—',
                    'total' => $total,
                    'completed' => $completed,
                    'cancelled' => (int) ($apptRow->cancelled ?? 0),
                    'no_show' => (int) ($apptRow->no_show ?? 0),
                    'income' => (float) ($incomeRow->income ?? 0),
                    'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
                ];
            })
            ->sortByDesc('income')
            ->values();

        return [
            'doctors' => $doctors,
            'month' => $start->translatedFormat('F Y'),
            'total_income' => $doctors->sum('income'),
            'total_appointments' => $doctors->sum('total'),
        ];
    }
}
