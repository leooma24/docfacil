<?php
/**
 * Dedupe a CSV of new dentist prospects against a JSON export of existing prospects in prod.
 *
 * Usage:
 *   php tools/dedupe-prospects.php <input.csv> <existing.json> <output.csv> <report.txt>
 *
 * Dedup rules (any match = duplicate, skipped):
 *   1. phone match (last 10 digits, normalized)
 *   2. email match (lowercase, trim)
 *   3. business_name + city match (normalized lowercase, no accents, no punctuation)
 *
 * Additional filter:
 *   - Skip rows without phone (phone is mandatory per ICP).
 */

if ($argc < 5) {
    fwrite(STDERR, "Usage: php dedupe-prospects.php <input.csv> <existing.json> <output.csv> <report.txt>\n");
    exit(1);
}

[$_, $inputCsv, $existingJson, $outputCsv, $reportFile] = $argv;

function normPhone(?string $raw): string
{
    if (!$raw) return '';
    $digits = preg_replace('/\D+/', '', $raw);
    return substr($digits, -10);
}

function normText(?string $raw): string
{
    if (!$raw) return '';
    $s = mb_strtolower(trim($raw), 'UTF-8');
    $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u']);
    $s = preg_replace('/[^a-z0-9]+/', '', $s);
    return $s;
}

// Load existing prospects from prod
$existing = json_decode(file_get_contents($existingJson), true);
$existingPhones = [];
$existingEmails = [];
$existingBizCity = [];

foreach ($existing as $p) {
    if (!empty($p['phone'])) {
        $ph = normPhone($p['phone']);
        if ($ph !== '') $existingPhones[$ph] = true;
    }
    if (!empty($p['email'])) {
        $existingEmails[strtolower(trim($p['email']))] = true;
    }
    $biz = normText($p['clinic_name'] ?? $p['name'] ?? '');
    $city = normText($p['city'] ?? '');
    if ($biz !== '' && $city !== '') {
        $existingBizCity["{$biz}|{$city}"] = true;
    }
}

echo sprintf("Loaded %d existing prospects (phones=%d, emails=%d, biz+city=%d)\n",
    count($existing), count($existingPhones), count($existingEmails), count($existingBizCity));

// Read input CSV
$fhIn = fopen($inputCsv, 'r');
if (!$fhIn) { fwrite(STDERR, "Cannot open {$inputCsv}\n"); exit(1); }

$header = fgetcsv($fhIn);
$col = array_flip($header);

$rows = [];
$counters = [
    'total' => 0,
    'no_phone' => 0,
    'dup_phone' => 0,
    'dup_email' => 0,
    'dup_bizcity' => 0,
    'kept' => 0,
];
$dupReport = [];

while (($row = fgetcsv($fhIn)) !== false) {
    $counters['total']++;
    $r = array_combine($header, $row);

    $ph = normPhone($r['phone'] ?? '');
    if ($ph === '' || strlen($ph) < 10) {
        $counters['no_phone']++;
        $dupReport[] = "SKIP no_phone: {$r['business_name']}";
        continue;
    }

    if (isset($existingPhones[$ph])) {
        $counters['dup_phone']++;
        $dupReport[] = "SKIP dup_phone: {$r['business_name']} (phone {$ph})";
        continue;
    }

    $em = strtolower(trim($r['email'] ?? ''));
    if ($em !== '' && isset($existingEmails[$em])) {
        $counters['dup_email']++;
        $dupReport[] = "SKIP dup_email: {$r['business_name']} (email {$em})";
        continue;
    }

    $biz = normText($r['business_name'] ?? '');
    $city = normText($r['city'] ?? '');
    $bizKey = "{$biz}|{$city}";
    if ($biz !== '' && $city !== '' && isset($existingBizCity[$bizKey])) {
        $counters['dup_bizcity']++;
        $dupReport[] = "SKIP dup_bizcity: {$r['business_name']} / {$r['city']}";
        continue;
    }

    $rows[] = $r;
    $counters['kept']++;

    // Prevent intra-batch duplicates
    $existingPhones[$ph] = true;
    if ($em !== '') $existingEmails[$em] = true;
    if ($biz !== '' && $city !== '') $existingBizCity[$bizKey] = true;
}
fclose($fhIn);

// Write output CSV
$fhOut = fopen($outputCsv, 'w');
fputcsv($fhOut, $header);
foreach ($rows as $r) {
    fputcsv($fhOut, array_map(fn ($c) => $r[$c] ?? '', $header));
}
fclose($fhOut);

// Write report
$report = "Dedupe Report — " . date('Y-m-d H:i:s') . "\n";
$report .= str_repeat('=', 50) . "\n";
$report .= "Input CSV:    {$inputCsv}\n";
$report .= "Existing:     {$existingJson} (" . count($existing) . " prospects)\n";
$report .= "Output CSV:   {$outputCsv}\n\n";
$report .= "Counters:\n";
foreach ($counters as $k => $v) {
    $report .= sprintf("  %-15s %d\n", $k, $v);
}
$report .= "\nDetails:\n";
foreach ($dupReport as $line) {
    $report .= "  - {$line}\n";
}
file_put_contents($reportFile, $report);

echo "\n{$report}";
