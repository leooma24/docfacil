<?php

namespace App\Console\Commands;

use App\Models\Expense;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * Vuelve a capturar los gastos que se repiten cada mes.
 *
 * La renta, la nómina y el internet se pagan todos los meses por el mismo
 * monto. Sin esto el doctor los captura doce veces al año y deja de hacerlo
 * al tercero — y en cuanto deja de capturar, el corte del mes miente y ya no
 * le sirve para nada.
 */
class GenerarGastosRecurrentes extends Command
{
    protected $signature = 'docfacil:gastos-recurrentes {--dry-run : Solo dice qué haría}';

    protected $description = 'Genera los gastos marcados como "se repite cada mes"';

    public function handle(): int
    {
        $hoy = CarbonImmutable::today();
        $seco = (bool) $this->option('dry-run');
        $creados = 0;

        // El original es el que trae la marca. Las copias que este comando
        // genera nacen sin ella, para que no se multipliquen solas.
        $plantillas = Expense::withoutGlobalScopes()
            ->where('is_recurring', true)
            ->get();

        foreach ($plantillas as $plantilla) {
            $siguiente = $this->siguienteFecha($plantilla, $hoy);

            if (! $siguiente) {
                continue;
            }

            // Si ya se generó el de este mes, no se repite. Esto hace que el
            // comando se pueda correr dos veces el mismo día sin duplicar.
            if ($plantilla->last_generated_on
                && $plantilla->last_generated_on->isSameMonth($siguiente)) {
                continue;
            }

            $yaExiste = Expense::withoutGlobalScopes()
                ->where('clinic_id', $plantilla->clinic_id)
                ->where('concept', $plantilla->concept)
                ->where('category', $plantilla->category)
                ->whereYear('expense_date', $siguiente->year)
                ->whereMonth('expense_date', $siguiente->month)
                ->exists();

            if ($yaExiste) {
                $plantilla->forceFill(['last_generated_on' => $siguiente])->saveQuietly();
                continue;
            }

            $this->line("  {$plantilla->concept} → {$siguiente->toDateString()} (\${$plantilla->amount})");

            if ($seco) {
                continue;
            }

            $copia = $plantilla->replicate(['receipt_path', 'last_generated_on']);
            $copia->expense_date = $siguiente;
            // La copia no se repite sola: la marca se queda en el original.
            $copia->is_recurring = false;
            $copia->save();

            $plantilla->forceFill(['last_generated_on' => $siguiente])->saveQuietly();
            $creados++;
        }

        $this->info($seco
            ? "Se generarían {$plantillas->count()} revisados."
            : "Gastos recurrentes generados: {$creados}");

        return self::SUCCESS;
    }

    /**
     * El mismo día del mes siguiente al último generado.
     *
     * Si el gasto es el 31 y el mes solo tiene 30, cae el 30 en vez de
     * brincarse al mes que sigue.
     */
    private function siguienteFecha(Expense $plantilla, CarbonImmutable $hoy): ?CarbonImmutable
    {
        $ultima = CarbonImmutable::parse(
            $plantilla->last_generated_on ?? $plantilla->expense_date
        );

        $siguiente = $ultima->addMonthNoOverflow();

        // Todavía no le toca.
        return $siguiente->isAfter($hoy) ? null : $siguiente;
    }
}
