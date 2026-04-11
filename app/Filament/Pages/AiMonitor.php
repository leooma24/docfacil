<?php

namespace App\Filament\Pages;

use App\Models\AiUsageLog;
use Filament\Pages\Page;

class AiMonitor extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'Monitor IA';

    protected static ?string $title = 'Monitor de Inteligencia Artificial';

    protected static ?string $slug = 'ai-monitor';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.ai-monitor';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public function getDataProperty(): array
    {
        $today = AiUsageLog::whereDate('created_at', today());
        $month = AiUsageLog::where('created_at', '>=', now()->startOfMonth());

        return [
            'ai_enabled' => (bool) config('services.ai.enabled'),
            'provider' => config('services.ai.provider'),
            'daily_limit' => (float) config('services.ai.max_daily_cost_usd', 5),
            'today_cost' => (float) $today->clone()->sum('cost_usd'),
            'today_calls' => $today->clone()->count(),
            'today_failures' => $today->clone()->where('success', false)->count(),
            'month_cost' => (float) $month->clone()->sum('cost_usd'),
            'month_calls' => $month->clone()->count(),
            'top_features' => AiUsageLog::selectRaw('feature, COUNT(*) as calls, SUM(cost_usd) as cost')
                ->where('created_at', '>=', now()->startOfMonth())
                ->groupBy('feature')
                ->orderByDesc('cost')
                ->limit(10)
                ->get(),
            'top_clinics' => AiUsageLog::selectRaw('clinic_id, COUNT(*) as calls, SUM(cost_usd) as cost')
                ->whereNotNull('clinic_id')
                ->where('created_at', '>=', now()->startOfMonth())
                ->groupBy('clinic_id')
                ->orderByDesc('cost')
                ->with('clinic:id,name,plan')
                ->limit(10)
                ->get(),
            'last_7_days' => AiUsageLog::selectRaw('DATE(created_at) as date, SUM(cost_usd) as cost, COUNT(*) as calls')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];
    }
}
