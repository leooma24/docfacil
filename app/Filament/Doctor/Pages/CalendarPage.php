<?php

namespace App\Filament\Doctor\Pages;

use App\Filament\Doctor\Widgets\CalendarWidget;
use Filament\Pages\Page;

class CalendarPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $title = 'Calendario';

    protected static ?string $slug = 'calendario';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.doctor.pages.calendar-page';

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }
}
