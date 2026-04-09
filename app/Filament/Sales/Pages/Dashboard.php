<?php

namespace App\Filament\Sales\Pages;

use App\Filament\Sales\Widgets\LeaderboardWidget;
use App\Filament\Sales\Widgets\MyStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            MyStatsWidget::class,
            LeaderboardWidget::class,
        ];
    }
}
