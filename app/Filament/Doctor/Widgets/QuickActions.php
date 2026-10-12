<?php

namespace App\Filament\Doctor\Widgets;

use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    public static function canView(): bool
    {
        // El que va empezando solo ve «Agendar mi primera cita» (11-oct-2026).
        if (auth()->user()?->clinic?->vaEmpezando()) {
            return false;
        }

        return true;
    }

    protected static ?int $sort = -5;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.doctor.widgets.quick-actions';
}
