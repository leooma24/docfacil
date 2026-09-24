<?php

namespace App\Filament\Sales\Widgets;

use App\Support\CargaDeTrabajo;
use Filament\Widgets\Widget;

/**
 * "Hoy toca esto", al abrir el escritorio.
 *
 * La cola del día ya existía como pantalla aparte, y una pantalla aparte es
 * una que hay que acordarse de abrir. Aquí lo primero que se ve al entrar es
 * qué hay que hacer y en qué orden, sin números que haya que interpretar.
 *
 * El orden no es casual: contestarle a quien escribió va antes que prospectar,
 * porque conseguir que alguien levante la mano cuesta mucho más que mandar un
 * mensaje nuevo.
 */
class TareasDeHoyWidget extends Widget
{
    protected static string $view = 'filament.sales.widgets.tareas-de-hoy';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $repId = (int) auth()->id();

        return [
            'tareas' => CargaDeTrabajo::tareasDeHoy($repId),
            'numeros' => CargaDeTrabajo::numeros($repId),
        ];
    }
}
