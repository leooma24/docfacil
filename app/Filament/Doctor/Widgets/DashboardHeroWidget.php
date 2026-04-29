<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use Filament\Widgets\Widget;

class DashboardHeroWidget extends Widget
{
    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.doctor.widgets.dashboard-hero';

    public function getData(): array
    {
        $user = auth()->user();
        $clinicId = $user->clinic_id;

        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Buenos días' : ($hour < 19 ? 'Buenas tardes' : 'Buenas noches');

        // Totales de la clínica para detectar empty state (primera vez)
        $totalPatients = Patient::where('clinic_id', $clinicId)->count();
        $totalAppointments = Appointment::where('clinic_id', $clinicId)->count();

        $todayAppts = Appointment::where('clinic_id', $clinicId)
            ->whereDate('starts_at', today())
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress', 'completed'])
            ->count();

        $todayIncome = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereDate('payment_date', today())
            ->sum('amount');

        $pendingPayments = Payment::where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->sum('amount');

        $newPatients = Patient::where('clinic_id', $clinicId)
            ->whereDate('created_at', today())
            ->count();

        // Estado de empty: nuevo (sin pacientes), iniciando (con pacientes pero sin citas), normal
        $emptyState = match (true) {
            $totalPatients === 0                              => 'fresh',
            $totalAppointments === 0                          => 'has_patients',
            default                                            => 'normal',
        };

        return [
            'greeting' => $greeting,
            'name' => $user->name ?? 'Doctor',
            'date' => now()->translatedFormat('l d \d\e F'),
            'today_appts' => $todayAppts,
            'today_income' => $todayIncome,
            'pending_payments' => $pendingPayments,
            'new_patients' => $newPatients,
            'total_patients' => $totalPatients,
            'total_appointments' => $totalAppointments,
            'empty_state' => $emptyState,
            'patients_create_url' => \App\Filament\Doctor\Resources\PatientResource::getUrl('create'),
            'appointments_create_url' => \App\Filament\Doctor\Resources\AppointmentResource::getUrl('create'),
        ];
    }
}
