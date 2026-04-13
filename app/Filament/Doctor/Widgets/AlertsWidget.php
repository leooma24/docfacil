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

        // Overdue payments
        $overduePayments = Payment::where('clinic_id', $clinicId)
            ->where('status', 'pending')
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

        // Tomorrow appointments without reminder
        $noReminder = Appointment::where('clinic_id', $clinicId)
            ->whereDate('starts_at', now()->addDay())
            ->where('reminder_sent', false)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->count();

        if ($noReminder > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-chat-bubble-left-ellipsis',
                'title' => "{$noReminder} citas mañana sin recordatorio",
                'desc' => 'Envía WhatsApp para reducir inasistencias.',
            ];
        }

        // Today's income
        $todayIncome = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereDate('payment_date', today())
            ->sum('amount');

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
