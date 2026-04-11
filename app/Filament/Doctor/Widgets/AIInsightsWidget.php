<?php

namespace App\Filament\Doctor\Widgets;

use App\Services\ClinicInsightsAIService;
use Filament\Widgets\Widget;

class AIInsightsWidget extends Widget
{
    protected static ?int $sort = -3;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.doctor.widgets.ai-insights';

    public function getInsights(): ?array
    {
        return app(ClinicInsightsAIService::class)->getInsights(auth()->user()->clinic_id);
    }

    public function refresh(): void
    {
        app(ClinicInsightsAIService::class)->invalidate(auth()->user()->clinic_id);
    }
}
