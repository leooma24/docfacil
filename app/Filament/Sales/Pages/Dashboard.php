<?php

namespace App\Filament\Sales\Pages;

use App\Filament\Sales\Widgets\MyStatsWidget;
use App\Filament\Sales\Widgets\TareasDeHoyWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Primero qué hay que hacer, después cómo vamos.
     *
     * Los números sirven para revisar al final del día; al entrar en la
     * mañana lo que hace falta es saber con qué empezar.
     */
    public function getWidgets(): array
    {
        return [
            TareasDeHoyWidget::class,
            MyStatsWidget::class,
        ];
    }
}
