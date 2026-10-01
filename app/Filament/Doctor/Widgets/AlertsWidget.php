<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use Filament\Widgets\Widget;

class AlertsWidget extends Widget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static string $view = 'filament.doctor.widgets.alerts-widget';

    public function getAlerts(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $alerts = [];

        // Patients without visit in 6+ months
        $inactivePatients = Patient::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->whereDoesntHave('appointments', function ($q) {
                $q->where('starts_at', '>=', now()->subMonths(6));
            })
            ->count();

        if ($inactivePatients > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-clock',
                'title' => "{$inactivePatients} pacientes sin visita",
                'desc' => 'Hace más de 6 meses que no vienen. Envíales un recordatorio.',
            ];
        }

        // Overdue payments — con abono también cuentan: siguen debiendo.
        $overduePayments = Payment::where('clinic_id', $clinicId)
            ->withBalance()
            ->where('payment_date', '<', now()->subDays(7))
            ->count();

        if ($overduePayments > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'heroicon-o-exclamation-triangle',
                'title' => "{$overduePayments} pagos vencidos",
                'desc' => 'Tienen más de 7 días pendientes.',
            ];
        }

        // Pacientes de mañana a los que falta recordarles. Se cuenta por
        // paciente (uno con tres citas recibe un solo mensaje) y no entran los
        // que ya confirmaron. Se quita al mandar el recordatorio.
        $noReminder = Appointment::where('clinic_id', $clinicId)
            ->whereDate('starts_at', today()->addDay())
            ->where('reminder_sent', false)
            ->where('status', 'scheduled')
            ->distinct()
            ->count('patient_id');

        if ($noReminder > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-chat-bubble-left-ellipsis',
                'title' => $noReminder . ($noReminder === 1 ? ' paciente mañana sin recordatorio' : ' pacientes mañana sin recordatorio'),
                'desc' => 'Tóquelo para verlos y mandarles WhatsApp.',
                'url' => \App\Filament\Doctor\Resources\AppointmentResource::getUrl('index', [
                    'tableFilters' => ['sin_recordatorio' => ['isActive' => true], 'upcoming' => ['isActive' => false]],
                ], panel: 'doctor'),
            ];
        }

        // Today's income
        $todayIncome = Payment::cobradoEntre($clinicId, today(), today());

        if ($todayIncome > 0) {
            $alerts[] = [
                'type' => 'success',
                'icon' => 'heroicon-o-banknotes',
                'title' => 'Ingresos hoy: $' . number_format($todayIncome, 0),
                'desc' => 'Buen trabajo.',
            ];
        }

        return $alerts;
    }
}
