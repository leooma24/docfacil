<?php

namespace App\Console\Commands;

use App\Models\Prospect;
use App\Services\LeadScoringService;
use Illuminate\Console\Command;

/**
 * Recalcula lead_score para todos los prospectos. Se corre vía cron
 * (definido en routes/console.php) cada noche para que el panel de
 * ventas refleje engagement reciente y decay automático.
 *
 * Manualmente:  php artisan docfacil:recalculate-lead-scores
 */
class RecalculateLeadScores extends Command
{
    protected $signature = 'docfacil:recalculate-lead-scores {--chunk=200}';
    protected $description = 'Recalcula lead_score de todos los prospectos para priorizar outreach';

    public function handle(LeadScoringService $scorer): int
    {
        $start = microtime(true);
        $total = Prospect::count();
        $chunk = (int) $this->option('chunk');
        $processed = 0;
        $changed = 0;

        $this->info("Recalculando scores para {$total} prospectos...");

        Prospect::with('emailEvents')->chunkById($chunk, function ($batch) use ($scorer, &$processed, &$changed) {
            foreach ($batch as $p) {
                $newScore = $scorer->calculate($p);
                if ($p->lead_score !== $newScore) {
                    $p->updateQuietly(['lead_score' => $newScore]);
                    $changed++;
                }
                $processed++;
            }
        });

        $elapsed = round(microtime(true) - $start, 2);
        $this->info("✅ Procesados: {$processed} · Cambiaron: {$changed} · Tiempo: {$elapsed}s");

        // Distribución por bucket (insight para Omar)
        $hot = Prospect::where('lead_score', '>=', LeadScoringService::HOT_THRESHOLD)->count();
        $warm = Prospect::whereBetween('lead_score', [LeadScoringService::WARM_THRESHOLD, LeadScoringService::HOT_THRESHOLD - 1])->count();
        $cold = Prospect::whereBetween('lead_score', [LeadScoringService::COLD_THRESHOLD, LeadScoringService::WARM_THRESHOLD - 1])->count();
        $frozen = Prospect::where('lead_score', '<', LeadScoringService::COLD_THRESHOLD)->count();

        $this->newLine();
        $this->info("📊 Distribución actual:");
        $this->line("   🔥 Calientes (80+):  {$hot}");
        $this->line("   🌡️ Tibios (50-79):   {$warm}");
        $this->line("   🧊 Fríos (30-49):    {$cold}");
        $this->line("   ❄️ Congelados (<30): {$frozen}");

        return 0;
    }
}
