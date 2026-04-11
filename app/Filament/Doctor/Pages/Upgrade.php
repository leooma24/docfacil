<?php

namespace App\Filament\Doctor\Pages;

use Filament\Pages\Page;

class Upgrade extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';

    protected static ?string $navigationLabel = 'Mi Plan';

    protected static ?string $title = 'Actualizar Plan';

    protected static ?string $slug = 'actualizar-plan';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.doctor.pages.upgrade';

    public function getClinic()
    {
        return auth()->user()->clinic;
    }

    public function isExpired(): bool
    {
        $clinic = $this->getClinic();
        if (!$clinic) return false;

        if ($clinic->plan === 'free' && $clinic->trial_ends_at?->isPast()) return true;
        if ($clinic->is_beta && $clinic->beta_ends_at?->isPast()) return true;

        return false;
    }

    public function isFounder(): bool
    {
        return $this->getClinic()?->is_founder ?? false;
    }

    public function getFounderPrice(): string
    {
        return number_format($this->getClinic()?->founder_price ?? 299, 0);
    }
}
