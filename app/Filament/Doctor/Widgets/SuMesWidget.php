<?php

namespace App\Filament\Doctor\Widgets;

use App\Support\SuMes;
use Filament\Widgets\Widget;

/**
 * "Su mes en DocFácil": lo que pasó, en números de verdad, contra el mes
 * pasado. Los montos solo los ve quien ve el dinero del consultorio.
 */
class SuMesWidget extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.doctor.widgets.su-mes';

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'mes' => SuMes::de($user->clinic_id, now()),
            'veDinero' => $user->veElDinero(),
            'nombreMes' => now()->locale('es')->isoFormat('MMMM'),
        ];
    }
}
