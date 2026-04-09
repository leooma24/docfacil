<?php

namespace App\Filament\Sales\Widgets;

use App\Models\Commission;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class LeaderboardWidget extends Widget
{
    protected static string $view = 'filament.sales.widgets.leaderboard';

    protected int | string | array $columnSpan = 'full';

    public function getRanking(): array
    {
        return User::where('role', 'sales')
            ->leftJoin('commissions', function ($j) {
                $j->on('commissions.user_id', '=', 'users.id')
                  ->whereMonth('commissions.earned_at', now()->month)
                  ->whereYear('commissions.earned_at', now()->year);
            })
            ->select('users.id', 'users.name', DB::raw('COUNT(DISTINCT commissions.clinic_id) as closed'), DB::raw('COALESCE(SUM(commissions.amount), 0) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('closed')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($u, $i) => [
                'position' => $i + 1,
                'name' => $u->name,
                'closed' => (int) $u->closed,
                'total' => (float) $u->total,
                'is_me' => $u->id === auth()->id(),
            ])
            ->toArray();
    }
}
