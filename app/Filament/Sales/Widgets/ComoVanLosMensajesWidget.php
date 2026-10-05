<?php

namespace App\Filament\Sales\Widgets;

use App\Support\MensajesDeVenta;
use Filament\Widgets\Widget;

/**
 * Cuántos contestan a cada versión de cada mensaje de la cadencia.
 *
 * Para decidir con datos si el mensaje nuevo funciona mejor que el de antes.
 * Con menos de 30 envíos de una versión, el porcentaje todavía dice poco y la
 * tarjeta lo avisa.
 */
class ComoVanLosMensajesWidget extends Widget
{
    protected static string $view = 'filament.sales.widgets.como-van-los-mensajes';

    protected int|string|array $columnSpan = 'full';

    /** Menos que esto, el porcentaje todavía no dice nada. */
    public const POCOS = 30;

    protected function getViewData(): array
    {
        return ['filas' => MensajesDeVenta::resultados((int) auth()->id())];
    }
}
