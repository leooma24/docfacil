<?php

namespace App\Filament\Doctor\Pages;

use App\Services\PredictiveInsightsAIService;
use Filament\Pages\Page;

class PredictiveInsights extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Inteligencia IA';

    protected static ?string $title = 'Inteligencia del Consultorio';

    protected static ?string $slug = 'inteligencia';

    protected static ?int $navigationSort = 95;

    protected static ?string $navigationGroup = 'Consultorio';

    protected static string $view = 'filament.doctor.pages.predictive-insights';

    public ?array $insights = null;
    public bool $loading = false;

    public function mount(): void
    {
        $this->loadInsights();
    }

    public function loadInsights(): void
    {
        $this->loading = true;
        $this->insights = app(PredictiveInsightsAIService::class)->getPredictions(auth()->user()->clinic_id);
        $this->loading = false;
    }

    public function refreshInsights(): void
    {
        app(PredictiveInsightsAIService::class)->invalidate(auth()->user()->clinic_id);
        $this->loadInsights();
    }
}
