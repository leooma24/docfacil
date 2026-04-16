<?php

namespace App\Console\Commands;

use App\Models\Prospect;
use App\Services\OsmClinicScraper;
use App\Services\WhatsAppExtractor;
use Illuminate\Console\Command;

class ImportarProspectos extends Command
{
    protected $signature = 'docfacil:importar-prospectos
        {ciudad : hermosillo, obregon, culiacan, mazatlan, los-mochis, guasave}
        {--limit=100 : Máximo de clínicas a traer de OSM}
        {--specialty= : Filtrar por especialidad (dental, medico, clinica)}
        {--dry-run : No guardar, solo mostrar resultado}
        {--skip-web : Saltar el paso de scrapear sitios web para WhatsApp}';

    protected $description = 'Importa clínicas desde OpenStreetMap y extrae WhatsApp de sus sitios web';

    public function handle(OsmClinicScraper $osm, WhatsAppExtractor $whatsapp): int
    {
        $ciudad = str_replace('-', ' ', $this->argument('ciudad'));
        $limit = (int) $this->option('limit');
        $specialty = $this->option('specialty');
        $dryRun = $this->option('dry-run');
        $skipWeb = $this->option('skip-web');

        $this->info("Consultando OpenStreetMap para: {$ciudad} (límite: {$limit})");

        try {
            $clinics = $osm->clinicsForCity($ciudad, $limit);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        if (empty($clinics)) {
            $this->warn('No se encontraron clínicas en OSM.');
            return Command::SUCCESS;
        }

        $this->info('OSM devolvió ' . count($clinics) . ' clínicas con nombre.');

        if ($specialty) {
            $clinics = array_filter($clinics, fn($c) => $c['specialty'] === $specialty);
            $this->info('Filtradas por especialidad "' . $specialty . '": ' . count($clinics));
        }

        $cityNormalized = ucwords(mb_strtolower($ciudad));
        $stats = ['new' => 0, 'updated' => 0, 'skipped' => 0, 'with_whatsapp' => 0, 'with_web' => 0];

        $bar = $this->output->createProgressBar(count($clinics));
        $bar->start();

        foreach ($clinics as $clinic) {
            $bar->advance();

            $waPhone = null;
            if (!$skipWeb && !empty($clinic['website'])) {
                $stats['with_web']++;
                $waPhone = $whatsapp->extractFromUrl($clinic['website']);
                if ($waPhone) $stats['with_whatsapp']++;
            }

            $phone = $waPhone ?: $clinic['phone'];
            $hasWhatsapp = (bool) $waPhone;

            // Omitir si no hay forma de contacto
            if (!$phone && !$clinic['email']) {
                $stats['skipped']++;
                continue;
            }

            $data = [
                'name'         => $clinic['name'],
                'clinic_name'  => $clinic['name'],
                'phone'        => $phone,
                'email'        => $clinic['email'],
                'website'      => $clinic['website'],
                'has_whatsapp' => $hasWhatsapp,
                'address'      => $clinic['address'],
                'latitude'     => $clinic['latitude'],
                'longitude'    => $clinic['longitude'],
                'city'         => $cityNormalized,
                'specialty'    => $clinic['specialty'],
                'source'       => 'prospecting',
                'status'       => 'new',
                'contact_day'  => 0,
            ];

            if ($dryRun) {
                $stats['new']++;
                continue;
            }

            $existing = Prospect::where('osm_id', $clinic['osm_id'])->first();

            if ($existing) {
                $existing->update(array_filter($data, fn($v) => !is_null($v)));
                $stats['updated']++;
            } else {
                Prospect::create(['osm_id' => $clinic['osm_id']] + $data);
                $stats['new']++;
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Nuevos', 'Actualizados', 'Omitidos (sin contacto)', 'Con web', 'Con WhatsApp'],
            [[$stats['new'], $stats['updated'], $stats['skipped'], $stats['with_web'], $stats['with_whatsapp']]]
        );

        if ($dryRun) {
            $this->warn('DRY-RUN: no se guardó nada.');
        }

        return Command::SUCCESS;
    }
}
