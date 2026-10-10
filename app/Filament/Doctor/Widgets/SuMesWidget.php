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
        $mes = SuMes::de($user->clinic_id, now());
        $veDinero = $user->veElDinero();
        $nombreMes = now()->locale('es')->isoFormat('MMMM');

        return [
            'mes' => $mes,
            'veDinero' => $veDinero,
            'nombreMes' => $nombreMes,
            'mesPasado' => now()->subMonthNoOverflow()->locale('es')->isoFormat('MMMM'),
            'resumen' => self::resumen($mes, $veDinero, $nombreMes),
        ];
    }

    /**
     * El mes en una frase, solo con lo que pasó: "En octubre mandó 12
     * recordatorios, 8 pacientes confirmaron por la liga y le aceptaron 3
     * presupuestos por $24,000.00". null si no hubo nada.
     */
    public static function resumen(array $mes, bool $veDinero, string $nombreMes): ?string
    {
        $n = fn (string $clave) => $mes[$clave]['este'];
        $plural = fn (int $cuantos, string $uno, string $varios) => $cuantos . ' ' . ($cuantos === 1 ? $uno : $varios);
        $partes = [];

        if ($n('recordatorios') > 0) {
            $partes[] = 'mandó ' . $plural($n('recordatorios'), 'recordatorio', 'recordatorios');
        }
        if ($n('confirmaron') > 0) {
            $partes[] = $plural($n('confirmaron'), 'paciente confirmó', 'pacientes confirmaron') . ' por la liga';
        }
        if ($n('presupuestos') > 0) {
            $partes[] = 'le aceptaron ' . $plural($n('presupuestos'), 'presupuesto', 'presupuestos')
                . ($veDinero ? ' por $' . number_format($n('presupuestosMonto'), 2) : '');
        }
        if ($veDinero && $n('cobradoDeAntes') > 0) {
            $partes[] = 'cobró $' . number_format($n('cobradoDeAntes'), 2) . ' de saldos de antes';
        }

        if ($partes === []) {
            return null;
        }

        $ultima = array_pop($partes);

        return 'En ' . $nombreMes . ' ' . ($partes ? implode(', ', $partes) . ' y ' : '') . $ultima . '.';
    }
}
