<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\CarbonImmutable;

/**
 * Los números del corte: cuánto entró, cuánto salió, cuánto quedó.
 *
 * Viven aquí y no en la página Corte porque el correo de cada mes dice lo
 * mismo. Si cada uno hiciera su cuenta, tarde o temprano el correo le diría
 * al doctor una cifra y la pantalla otra.
 */
class NumerosDelCorte
{
    /**
     * El "por cobrar" va aparte a propósito: es dinero que todavía no entra,
     * y meterlo en el ingreso le pintaría al doctor un mes que no tuvo.
     */
    public static function calcular(
        int $clinicId,
        CarbonImmutable $desde,
        CarbonImmutable $hasta,
        CarbonImmutable $antesDesde,
        CarbonImmutable $antesHasta,
    ): array {
        $ingresos = Payment::cobradoEntre($clinicId, $desde, $hasta);
        $gastos = Expense::totalEntre($clinicId, $desde, $hasta);
        $utilidad = $ingresos - $gastos;

        $ingresosAntes = Payment::cobradoEntre($clinicId, $antesDesde, $antesHasta);
        $gastosAntes = Expense::totalEntre($clinicId, $antesDesde, $antesHasta);

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'utilidad' => $utilidad,
            // Margen: de cada $100 que entraron, cuánto se quedó el doctor.
            'margen' => $ingresos > 0 ? ($utilidad / $ingresos) * 100 : null,
            'por_cobrar' => Payment::porCobrarEntre($clinicId, $desde, $hasta),
            'categorias' => Expense::porCategoria($clinicId, $desde, $hasta),
            'ingresos_antes' => $ingresosAntes,
            'gastos_antes' => $gastosAntes,
            'utilidad_antes' => $ingresosAntes - $gastosAntes,
            'cambio_ingresos' => self::cambio($ingresos, $ingresosAntes),
            'cambio_gastos' => self::cambio($gastos, $gastosAntes),
            'cambio_utilidad' => self::cambio($utilidad, $ingresosAntes - $gastosAntes),
            'hay_datos' => $ingresos > 0 || $gastos > 0,
        ];
    }

    /** Cuánto cambió contra el periodo anterior, en porcentaje. */
    private static function cambio(float $ahora, float $antes): ?float
    {
        // Sin base contra qué comparar, el porcentaje no dice nada: un mes
        // que arranca de cero siempre saldría "+100%".
        if (abs($antes) < 0.01) {
            return null;
        }

        return (($ahora - $antes) / abs($antes)) * 100;
    }
}
