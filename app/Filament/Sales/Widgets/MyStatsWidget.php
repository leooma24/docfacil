<?php

namespace App\Filament\Sales\Widgets;

use App\Models\Commission;
use App\Models\Prospect;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        $activeProspects = Prospect::where('assigned_to_sales_rep_id', $userId)
            ->whereNotIn('status', ['lost', 'converted'])
            ->count();

        $contactsToday = Prospect::where('assigned_to_sales_rep_id', $userId)
            ->whereDate('last_followup_at', today())
            ->count();

        $demosThisWeek = Prospect::where('assigned_to_sales_rep_id', $userId)
            ->whereNotNull('demo_completed_at')
            ->where('demo_completed_at', '>=', now()->startOfWeek())
            ->count();

        $convertedThisMonth = Prospect::where('assigned_to_sales_rep_id', $userId)
            ->where('status', 'converted')
            ->whereMonth('converted_at', now()->month)
            ->whereYear('converted_at', now()->year)
            ->count();

        $pendingFollowups = Prospect::where('assigned_to_sales_rep_id', $userId)
            ->whereNotIn('status', ['converted', 'lost'])
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('next_contact_at')->where('next_contact_at', '<=', now());
                })->orWhere(function ($q2) {
                    $q2->where('contact_day', 0)->where('status', 'new');
                });
            })
            ->count();

        $pendingAmount = Commission::where('user_id', $userId)
            ->where('status', 'pending')
            ->sum('amount');

        return [
            Stat::make('Contactos hoy', $contactsToday)
                ->description('Meta: 8/día')
                ->icon('heroicon-o-phone-arrow-up-right')
                ->color($contactsToday >= 8 ? 'success' : ($contactsToday >= 4 ? 'warning' : 'danger')),

            Stat::make('Seguimientos pendientes', $pendingFollowups)
                ->description($pendingFollowups > 0 ? 'Requieren acción' : 'Al corriente')
                ->icon('heroicon-o-clock')
                ->color($pendingFollowups > 0 ? 'danger' : 'success'),

            Stat::make('Demos esta semana', $demosThisWeek)
                ->description('Pipeline activo: ' . $activeProspects)
                ->icon('heroicon-o-computer-desktop')
                ->color('info'),

            Stat::make('Cierres del mes', $convertedThisMonth)
                ->description('Comisiones pendientes: $' . number_format($pendingAmount))
                ->icon('heroicon-o-trophy')
                ->color('success'),
        ];
    }
}
