<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;
    protected function getStats(): array
    {
        $clinicId = auth()->user()->clinic_id;

        $todayAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereDate('starts_at', today())
            ->count();

        $weekAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $totalPatients = Patient::where('clinic_id', $clinicId)->count();

        $monthlyIncome = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $pendingPayments = Payment::where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->sum('amount');

        $noShows = Appointment::where('clinic_id', $clinicId)
            ->where('status', 'no_show')
            ->whereMonth('starts_at', now()->month)
            ->count();

        return [
            Stat::make('Citas hoy', $todayAppointments)
                ->description('Programadas para hoy')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
            Stat::make('Citas esta semana', $weekAppointments)
                ->description('Lun - Dom')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            Stat::make('Total pacientes', $totalPatients)
                ->description('Registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Ingresos del mes', '$' . number_format($monthlyIncome, 2))
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Cobros pendientes', '$' . number_format($pendingPayments, 2))
                ->description('Por cobrar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Inasistencias', $noShows)
                ->description('Este mes')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
