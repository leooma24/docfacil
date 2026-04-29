<?php

namespace App\Console\Commands;

use App\Models\Prospect;
use App\Models\ProspectEmailEvent;
use Illuminate\Console\Command;

/**
 * Analiza la base de prospectos para extraer insights agregados (sin PII):
 *   - Top objeciones reales
 *   - Distribución por ciudad y especialidad
 *   - Fragmentos anónimos de notes (sin nombre, email ni teléfono)
 *   - Click-through rate por ciudad y tipo de correo
 *
 * Output: .agents/customer-research-output.md (gitignored)
 *
 * Uso: php artisan docfacil:customer-research
 */
class CustomerResearch extends Command
{
    protected $signature = 'docfacil:customer-research';
    protected $description = 'Genera reporte agregado de prospectos para customer research (sin PII)';

    public function handle(): int
    {
        $output = "# Customer Research Report — DocFácil\n\n";
        $output .= "_Generado: " . now()->format('Y-m-d H:i') . " · Solo agregados, sin PII._\n\n";

        // 1. TOP OBJECTIONS
        $output .= "## 1. Top objeciones reales (de `prospects.objections_faced`)\n\n";
        $counts = [];
        Prospect::whereNotNull('objections_faced')->pluck('objections_faced')->each(function ($obj) use (&$counts) {
            if (is_array($obj)) {
                foreach ($obj as $o) {
                    $counts[$o] = ($counts[$o] ?? 0) + 1;
                }
            }
        });
        arsort($counts);
        $totalObjs = array_sum($counts);
        if ($totalObjs > 0) {
            $output .= "| # | Objeción | Veces | % |\n|---|---|---|---|\n";
            $i = 1;
            foreach (array_slice($counts, 0, 17, true) as $o => $c) {
                $pct = round($c / $totalObjs * 100);
                $output .= "| {$i} | {$o} | {$c} | {$pct}% |\n";
                $i++;
            }
        } else {
            $output .= "_Sin objeciones registradas todavía._\n";
        }

        // 2. PROSPECTS POR CIUDAD
        $output .= "\n## 2. Prospectos por ciudad (top 30)\n\n";
        $output .= "| Ciudad | Cantidad |\n|---|---|\n";
        Prospect::selectRaw('city, count(*) as c')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city')
            ->orderByDesc('c')
            ->limit(30)
            ->get()
            ->each(function ($r) use (&$output) {
                $output .= "| {$r->city} | {$r->c} |\n";
            });

        // 3. ESPECIALIDADES
        $output .= "\n## 3. Especialidades top\n\n";
        $output .= "| Especialidad | Cantidad |\n|---|---|\n";
        Prospect::selectRaw('specialty, count(*) as c')
            ->whereNotNull('specialty')
            ->where('specialty', '!=', '')
            ->groupBy('specialty')
            ->orderByDesc('c')
            ->limit(20)
            ->get()
            ->each(function ($r) use (&$output) {
                $output .= "| {$r->specialty} | {$r->c} |\n";
            });

        // 4. NOTES — fragmentos anonimizados
        $output .= "\n## 4. Frases verbatim (anonimizadas)\n\n";
        $output .= "_Notes con contenido > 30 chars. Stripped de cualquier nombre/teléfono/email._\n\n";
        $notesProcessed = 0;
        Prospect::whereNotNull('notes')
            ->where('notes', '!=', '')
            ->whereRaw('LENGTH(notes) > 30')
            ->limit(50)
            ->get()
            ->each(function ($p) use (&$output, &$notesProcessed) {
                $note = $p->notes;
                // Strip emails
                $note = preg_replace('/[\w\.\-+]+@[\w\.\-]+/', '[email]', $note);
                // Strip phones (10 dígitos seguidos o con espacios/guiones)
                $note = preg_replace('/\b(\+?52\s?)?(\d{2,3}[\s\-]?\d{3,4}[\s\-]?\d{4})\b/', '[teléfono]', $note);
                // Truncate
                if (strlen($note) > 250) {
                    $note = substr($note, 0, 247) . '...';
                }
                $city = $p->city ?: 'Sin ciudad';
                $output .= "- _[{$city}]_ \"" . trim($note) . "\"\n";
                $notesProcessed++;
            });
        if ($notesProcessed === 0) {
            $output .= "_Sin notes con contenido relevante._\n";
        }

        // 5. CLICK-THROUGH POR CIUDAD
        $output .= "\n## 5. Click-rate de correos por ciudad (señal de intención)\n\n";
        $clicksByCity = ProspectEmailEvent::join('prospects', 'prospect_email_events.prospect_id', '=', 'prospects.id')
            ->selectRaw('prospects.city, count(*) as clicks, count(distinct prospect_email_events.prospect_id) as unique_prospects')
            ->where('prospect_email_events.event_type', 'click')
            ->whereNotNull('prospects.city')
            ->groupBy('prospects.city')
            ->orderByDesc('clicks')
            ->limit(20)
            ->get();
        if ($clicksByCity->count() > 0) {
            $output .= "| Ciudad | Clicks totales | Prospectos únicos |\n|---|---|---|\n";
            foreach ($clicksByCity as $r) {
                $output .= "| {$r->city} | {$r->clicks} | {$r->unique_prospects} |\n";
            }
        } else {
            $output .= "_Sin clicks registrados todavía._\n";
        }

        // 6. CLICK-THROUGH POR TIPO DE CORREO
        $output .= "\n## 6. Click-rate por tipo de correo\n\n";
        $clicksByType = ProspectEmailEvent::selectRaw('email_type, count(*) as clicks, count(distinct prospect_id) as unique_prospects')
            ->where('event_type', 'click')
            ->groupBy('email_type')
            ->orderByDesc('clicks')
            ->get();
        if ($clicksByType->count() > 0) {
            $output .= "| Tipo de correo | Clicks | Prospectos únicos |\n|---|---|---|\n";
            foreach ($clicksByType as $r) {
                $output .= "| {$r->email_type} | {$r->clicks} | {$r->unique_prospects} |\n";
            }
        } else {
            $output .= "_Sin clicks registrados todavía._\n";
        }

        // 7. ESTADO DEL PIPELINE
        $output .= "\n## 7. Estado del pipeline\n\n";
        $output .= "| Estado | Cantidad |\n|---|---|\n";
        Prospect::selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->orderByDesc('c')
            ->get()
            ->each(function ($r) use (&$output) {
                $status = $r->status ?: '_(sin estado)_';
                $output .= "| {$status} | {$r->c} |\n";
            });

        // 8. CIUDADES SIN PÁGINA pSEO
        $output .= "\n## 8. Ciudades con prospectos pero SIN página de software-dental\n\n";
        $reflection = new \ReflectionClass(\App\Http\Controllers\CityLandingController::class);
        $citiesProp = $reflection->getProperty('cities');
        $citiesProp->setAccessible(true);
        $existingCitySlugs = array_keys($citiesProp->getValue(new \App\Http\Controllers\CityLandingController()));
        $output .= "Ciudades con página existente: " . count($existingCitySlugs) . "\n\n";
        $allCities = Prospect::selectRaw('city, count(*) as c')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city')
            ->orderByDesc('c')
            ->get();

        $output .= "| Ciudad | Prospectos | Slug sugerido |\n|---|---|---|\n";
        $missingCount = 0;
        foreach ($allCities as $r) {
            $slug = \Illuminate\Support\Str::slug($r->city);
            if (! in_array($slug, $existingCitySlugs, true)) {
                $output .= "| {$r->city} | {$r->c} | {$slug} |\n";
                $missingCount++;
                if ($missingCount >= 25) break;
            }
        }
        if ($missingCount === 0) {
            $output .= "_Todas las ciudades con prospectos ya tienen página._\n";
        }

        // Guardar
        $path = base_path('.agents/customer-research-output.md');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, $output);

        $this->info("Reporte generado en: {$path}");
        $this->info("Líneas: " . substr_count($output, "\n"));
        $this->info("Tamaño: " . round(strlen($output) / 1024, 2) . " KB");

        return 0;
    }
}
