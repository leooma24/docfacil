<?php

namespace App\Filament\Doctor\Widgets;

use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.doctor.widgets.quick-actions';
}
