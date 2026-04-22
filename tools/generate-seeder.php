<?php
/**
 * Generate a Laravel seeder from a deduped CSV of prospects.
 *
 * Usage: php tools/generate-seeder.php <input.csv> <class_name> <output.php>
 */

if ($argc < 4) {
    fwrite(STDERR, "Usage: php generate-seeder.php <input.csv> <ClassName> <output.php>\n");
    exit(1);
}

[$_, $csv, $className, $out] = $argv;

$GLOBALS['batch_id'] = "research-cdmx-pilot-20260421";

$fh = fopen($csv, 'r');
$header = fgetcsv($fh);
$rows = [];
while (($row = fgetcsv($fh)) !== false) {
    if (count($row) < count($header)) continue;
    $rows[] = array_combine($header, $row);
}
fclose($fh);

$items = [];
foreach ($rows as $r) {
    $phone = preg_replace('/\D/', '', $r['phone']);
    if (strlen($phone) < 10) continue;
    $phone = substr($phone, -10);

    $whatsapp = preg_replace('/\D/', '', $r['whatsapp'] ?? '');
    if ($whatsapp !== '' && strlen($whatsapp) >= 10) {
        $whatsapp = substr($whatsapp, -10);
    } else {
        $whatsapp = null;
    }

    // Compose notes JSON with enrichment data for the sales rep
    $notes = ['batch' => $GLOBALS['batch_id']];
    foreach (['colonia', 'instagram', 'website', 'maps_url', 'rating', 'reviews_count', 'source_channel', 'notes'] as $k) {
        if (!empty($r[$k])) {
            $notes[$k] = $r[$k];
        }
    }

    $item = [
        'name' => $r['doctor_name'] !== '' ? $r['doctor_name'] : $r['business_name'],
        'clinic_name' => $r['business_name'],
        'specialty' => $r['specialty'] ?: 'Odontología General',
        'city' => $r['city'],
        'address' => $r['address'],
        'phone' => $phone,
        'has_whatsapp' => $whatsapp !== null,
        'email' => $r['email'] ?: null,
        'notes' => !empty($notes) ? json_encode($notes, JSON_UNESCAPED_UNICODE) : null,
    ];
    $items[] = $item;
}

function phpExport($v, int $indent = 0): string
{
    $pad = str_repeat('    ', $indent);
    if ($v === null) return 'null';
    if (is_bool($v)) return $v ? 'true' : 'false';
    if (is_int($v) || is_float($v)) return (string) $v;
    if (is_string($v)) return "'" . str_replace("'", "\\'", $v) . "'";
    if (is_array($v)) {
        $isAssoc = array_keys($v) !== range(0, count($v) - 1);
        $out = "[\n";
        foreach ($v as $k => $vv) {
            $out .= str_repeat('    ', $indent + 1);
            if ($isAssoc) {
                $out .= "'" . $k . "' => " . phpExport($vv, $indent + 1) . ",\n";
            } else {
                $out .= phpExport($vv, $indent + 1) . ",\n";
            }
        }
        $out .= $pad . ']';
        return $out;
    }
    return 'null';
}

$dataExport = phpExport($items, 2);

$count = count($items);
$batchId = $GLOBALS['batch_id'];

$php = <<<PHP
<?php

namespace Database\Seeders;

use App\Models\Prospect;
use Illuminate\Database\Seeder;

/**
 * Pilot CDMX dentist batch — generated from dentistas-cdmx-dedup.csv.
 *
 * {$count} registros descubiertos vía WebSearch + WebFetch (Google Maps + sitios propios),
 * deduplicados contra los 403 prospectos existentes en prod.
 *
 * Todos tienen teléfono válido (10 dígitos MX). Se asignan a Omar (user_id=100)
 * con source='prospecting' para que el cron send-prospect-emails los tome automático.
 * El batch identifier vive en notes.batch = '{$batchId}' para analytics.
 *
 * Ejecutar con: php artisan db:seed --class=ProspectDentistasCDMXPilotSeeder --force
 */
class ProspectDentistasCDMXPilotSeeder extends Seeder
{
    public function run(): void
    {
        \$prospects = {$dataExport};

        \$this->command->info('Insertando ' . count(\$prospects) . ' dentistas CDMX (pilot)...');

        \$inserted = 0;
        \$skipped = 0;

        foreach (\$prospects as \$prospect) {
            // Idempotencia por phone (último match) — no volver a insertar si ya existe
            \$existing = Prospect::where('phone', \$prospect['phone'])->first();
            if (\$existing) {
                \$skipped++;
                continue;
            }

            Prospect::create(array_merge(\$prospect, [
                'source' => 'prospecting', // matches SendProspectEmails cron filter
                'status' => 'new',
                'assigned_to_sales_rep_id' => 100,
                'contact_day' => 0,
                'next_contact_at' => now()->addHour(),
                'outreach_started_at' => now(),
            ]));
            \$inserted++;
        }

        \$this->command->info("✓ {\$inserted} insertados | {\$skipped} duplicados saltados | Total en seeder: " . count(\$prospects));
    }
}

PHP;

file_put_contents($out, $php);
echo "Generated {$out} with {$count} prospects.\n";
