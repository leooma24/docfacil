<?php

namespace App\Filament\Sales\Widgets;

use App\Models\Commission;
use App\Models\Prospect;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class LeaderboardWidget extends Widget
{
    protected static string $view = 'filament.sales.widgets.leaderboard';

    protected int|string|array $columnSpan = 'full';

    public function getRanking(): array
    {
        $month = now()->month;
        $year = now()->year;

        return User::where('role', 'sales')
            ->get()
            ->map(function ($user) use ($month, $year) {
                $prospects = Prospect::where('assigned_to_sales_rep_id', $user->id);

                $contacts = (clone $prospects)
                    ->whereMonth('contacted_at', $month)->whereYear('contacted_at', $year)
                    ->count();

                $demos = (clone $prospects)
                    ->whereNotNull('demo_completed_at')
                    ->whereMonth('demo_completed_at', $month)->whereYear('demo_completed_at', $year)
                    ->count();

                $conversions = (clone $prospects)
                    ->where('status', 'converted')
                    ->whereMonth('converted_at', $month)->whereYear('converted_at', $year)
                    ->count();

                $commissionTotal = Commission::where('user_id', $user->id)
                    ->whereMonth('earned_at', $month)->whereYear('earned_at', $year)
                    ->sum('amount');

                $activeProspects = (clone $prospects)
                    ->whereNotIn('status', ['converted', 'lost'])
                    ->count();

                $convRate = $contacts > 0 ? round(($conversions / $contacts) * 100) : 0;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'contacts' => $contacts,
                    'demos' => $demos,
                    'conversions' => $conversions,
                    'commission' => (float) $commissionTotal,
                    'active' => $activeProspects,
                    'conv_rate' => $convRate,
                    'is_me' => $user->id === auth()->id(),
                ];
            })
            ->sortByDesc('conversions')
            ->sortByDesc('commission')
            ->values()
            ->take(10)
            ->toArray();
    }
}
