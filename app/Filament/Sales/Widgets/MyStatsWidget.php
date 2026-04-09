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

        $convertedThisMonth = Prospect::where('assigned_to_sales_rep_id', $userId)
            ->where('status', 'converted')
            ->whereMonth('converted_at', now()->month)
            ->whereYear('converted_at', now()->year)
            ->count();

        $pendingAmount = Commission::where('user_id', $userId)
            ->where('status', 'pending')
            ->sum('amount');

        $paidThisMonth = Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        return [
            Stat::make('Prospectos activos', $activeProspects)
                ->description('Sin contar perdidos ni convertidos')
                ->icon('heroicon-o-funnel')
                ->color('info'),

            Stat::make('Conversiones este mes', $convertedThisMonth)
                ->description(now()->translatedFormat('F Y'))
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Comisiones pendientes', '$' . number_format($pendingAmount, 2))
                ->description('Por cobrar')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Pagadas este mes', '$' . number_format($paidThisMonth, 2))
                ->description(now()->translatedFormat('F Y'))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
