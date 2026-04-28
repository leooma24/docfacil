<?php

namespace App\Console\Commands;

use App\Models\Prospect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Imports prospect CSV batches dropped into database/discovery-batches/ by
 * the cloud discovery trigger. Dedupes against existing prospects by phone,
 * email, and (business_name + city). Moves processed CSVs to the imported/
 * subdirectory so they don't run twice.
 *
 * Scheduled to run every 30 minutes in routes/console.php.
 */
class ImportProspectBatches extends Command
{
    protected $signature = 'docfacil:import-prospect-batches';

    protected $description = 'Auto-import prospect CSV batches from database/discovery-batches/';

    private const BATCH_DIR = 'database/discovery-batches';

    private const IMPORTED_DIR = 'database/discovery-batches/imported';

    private const SALES_REP_ID = 100; // Omar

    public function handle(): int
    {
        $batchPath = base_path(self::BATCH_DIR);
        $importedPath = base_path(self::IMPORTED_DIR);

        if (! File::exists($batchPath)) {
            $this->info("No batch directory yet ({$batchPath}). Nothing to do.");
            return self::SUCCESS;
        }

        File::ensureDirectoryExists($importedPath);

        $csvs = File::glob("{$batchPath}/*.csv");
        if (empty($csvs)) {
            $this->info('No CSVs pending import.');
            return self::SUCCESS;
        }

        $existingIndex = $this->buildExistingIndex();

        $totalInserted = 0;
        $totalSkipped = 0;

        foreach ($csvs as $csv) {
            [$inserted, $skipped] = $this->importCsv($csv, $existingIndex);
            $totalInserted += $inserted;
            $totalSkipped += $skipped;

            // Move to imported/ with timestamp prefix to prevent collisions
            $basename = basename($csv);
            $target = "{$importedPath}/" . date('YmdHis') . "_{$basename}";
            File::move($csv, $target);
            $this->line("  → moved to imported/" . basename($target));
        }

        $message = "Import complete: {$totalInserted} inserted, {$totalSkipped} duplicates skipped from " . count($csvs) . ' CSVs.';
        $this->info($message);
        Log::info("[ImportProspectBatches] {$message}");

        return self::SUCCESS;
    }

    /**
     * Build in-memory lookup of existing prospects for O(1) dedup checks.
     */
    private function buildExistingIndex(): array
    {
        $phones = [];
        $emails = [];
        $bizCity = [];

        Prospect::select('phone', 'email', 'clinic_name', 'name', 'city')
            ->chunk(500, function ($chunk) use (&$phones, &$emails, &$bizCity) {
                foreach ($chunk as $p) {
                    if ($p->phone) {
                        $phones[$this->normalizePhone($p->phone)] = true;
                    }
                    if ($p->email) {
                        $emails[strtolower(trim($p->email))] = true;
                    }
                    $biz = $this->normalizeText($p->clinic_name ?: $p->name);
                    $city = $this->normalizeText($p->city);
                    if ($biz && $city) {
                        $bizCity["{$biz}|{$city}"] = true;
                    }
                }
            });

        return ['phones' => $phones, 'emails' => $emails, 'bizCity' => $bizCity];
    }

    /**
     * Import one CSV into prospects, skipping duplicates. Updates the
     * in-memory index so duplicates within the same run are also caught.
     */
    private function importCsv(string $csv, array &$index): array
    {
        $this->info("Importing: " . basename($csv));

        $handle = fopen($csv, 'r');
        if (! $handle) {
            $this->error("  Cannot open {$csv}");
            return [0, 0];
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return [0, 0];
        }

        $batchId = pathinfo($csv, PATHINFO_FILENAME);
        $inserted = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($header)) continue;
            $r = array_combine($header, $row);

            $phone = $this->normalizePhone($r['phone'] ?? '');
            if (strlen($phone) < 10) {
                $skipped++;
                continue;
            }

            if (isset($index['phones'][$phone])) {
                $skipped++;
                continue;
            }

            $email = strtolower(trim($r['email'] ?? ''));
            if ($email !== '' && isset($index['emails'][$email])) {
                $skipped++;
                continue;
            }

            $biz = $this->normalizeText($r['business_name'] ?? '');
            $city = $this->normalizeText($r['city'] ?? '');
            $bizKey = "{$biz}|{$city}";
            if ($biz && $city && isset($index['bizCity'][$bizKey])) {
                $skipped++;
                continue;
            }

            // Compose notes JSON with discovery metadata for the sales rep
            $notes = ['batch' => $batchId];
            foreach (['colonia', 'instagram', 'website', 'maps_url', 'rating', 'reviews_count', 'source_channel'] as $k) {
                if (! empty($r[$k])) {
                    $notes[$k] = $r[$k];
                }
            }
            if (! empty($r['notes'])) {
                $notes['description'] = $r['notes'];
            }

            Prospect::create([
                'name' => $r['doctor_name'] !== '' ? $r['doctor_name'] : $r['business_name'],
                'clinic_name' => $r['business_name'],
                'specialty' => $r['specialty'] ?: 'Odontología General',
                'city' => $r['city'],
                'address' => $r['address'] ?? null,
                'phone' => $phone,
                'has_whatsapp' => ! empty($r['whatsapp']),
                'email' => $r['email'] ?: null,
                'notes' => json_encode($notes, JSON_UNESCAPED_UNICODE),
                'source' => 'prospecting',
                'status' => 'new',
                'assigned_to_sales_rep_id' => self::SALES_REP_ID,
                'contact_day' => 0,
                'next_contact_at' => now()->addHour(),
                'outreach_started_at' => now(),
            ]);

            // Update index so intra-batch dups are caught
            $index['phones'][$phone] = true;
            if ($email) $index['emails'][$email] = true;
            if ($biz && $city) $index['bizCity'][$bizKey] = true;

            $inserted++;
        }

        fclose($handle);

        $this->line("  ✓ {$inserted} inserted, {$skipped} duplicates skipped");
        return [$inserted, $skipped];
    }

    private function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);
        return substr($digits, -10);
    }

    private function normalizeText(?string $raw): string
    {
        if (! $raw) return '';
        $s = mb_strtolower(trim($raw), 'UTF-8');
        $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u']);
        return preg_replace('/[^a-z0-9]+/', '', $s);
    }
}
