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

    public static function shouldRegisterNavigation(): bool
    {
        return static::hasAccess();
    }

    public static function canAccess(): bool
    {
        return static::hasAccess();
    }

    /**
     * Insights predictivos requieren 1) feature flag AI_ENABLED global,
     * 2) plan Pro o Clínica (lo prometemos en la landing como "Alertas inteligentes").
     */
    protected static function hasAccess(): bool
    {
        if (!config('services.ai.enabled', false)) {
            return false;
        }
        $clinic = auth()->user()?->clinic;
        return $clinic && $clinic->hasFeature('smart_alerts');
    }

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
