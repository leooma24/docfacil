<?php

namespace App\Console\Commands;

use App\Models\PremiumServicePurchase;
use App\Models\Scopes\ClinicScope;
use Illuminate\Console\Command;

/**
 * Marca como cancelled las compras de servicios premium que llevan más de 24 hrs
 * en pending_payment sin completarse. Evita que la tabla acumule basura indefinida.
 *
 * Schedule: diario en routes/console.php.
 */
class CleanupStalePremiumPurchases extends Command
{
    protected $signature = 'docfacil:cleanup-stale-premium-purchases {--hours=24} {--dry-run}';

    protected $description = 'Cancela compras de servicios premium en pending_payment con más de N horas (default 24)';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = (bool) $this->option('dry-run');
        $threshold = now()->subHours($hours);

        // Explícito: comando corre sin usuario autenticado → nos saltamos el ClinicScope
        // para barrer TODAS las clínicas, no solo las del user actual (que sería null).
        $stale = PremiumServicePurchase::query()
            ->withoutGlobalScope(ClinicScope::class)
            ->where('status', PremiumServicePurchase::STATUS_PENDING_PAYMENT)
            ->where('created_at', '<', $threshold)
            ->get();

        if ($stale->isEmpty()) {
            $this->info("No hay compras stale (>{$hours}h) que cancelar.");
            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Encontradas {$stale->count()} compras stale para cancelar:");

        $cancelled = 0;
        foreach ($stale as $p) {
            $this->line(sprintf(
                '  · #%d clínica %d %s ($%s) — %s',
                $p->id,
                $p->clinic_id,
                $p->service_name_snapshot,
                number_format($p->amount_mxn, 0),
                $p->created_at->diffForHumans(),
            ));

            if ($dryRun) {
                continue;
            }

            $p->update([
                'status' => PremiumServicePurchase::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'internal_notes' => trim(($p->internal_notes ?? '') . "\n[auto-cleanup] Pending por más de {$hours} hrs sin completar pago"),
            ]);
            $cancelled++;
        }

        $this->info($dryRun
            ? "Dry-run completo. Re-corre sin --dry-run para cancelar."
            : "Canceladas: {$cancelled}.");

        return self::SUCCESS;
    }
}
