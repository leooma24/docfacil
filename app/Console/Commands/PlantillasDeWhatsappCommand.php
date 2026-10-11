<?php

namespace App\Console\Commands;

use App\Support\PlantillasDeWhatsapp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Las plantillas de WhatsApp en Meta: verlas, mandarlas a revisión y ver si
 * ya se aprobaron. Sin opciones solo enseña el texto.
 *
 *   php artisan docfacil:whatsapp-plantillas            # el texto
 *   php artisan docfacil:whatsapp-plantillas --enviar   # a revisión de Meta
 *   php artisan docfacil:whatsapp-plantillas --estado   # aprobadas o no
 */
class PlantillasDeWhatsappCommand extends Command
{
    protected $signature = 'docfacil:whatsapp-plantillas {--enviar} {--estado}';

    protected $description = 'Ver, mandar a revisión de Meta y revisar las plantillas de WhatsApp de DocFácil';

    public function handle(): int
    {
        $token = config('services.whatsapp.token');
        $cuenta = config('services.whatsapp.business_account_id');
        $url = "https://graph.facebook.com/v21.0/{$cuenta}/message_templates";

        if (! $this->option('enviar') && ! $this->option('estado')) {
            foreach (array_keys(PlantillasDeWhatsapp::PLANTILLAS) as $nombre) {
                $p = PlantillasDeWhatsapp::PLANTILLAS[$nombre];
                $this->line("{$nombre}\n  {$p['cuerpo']}\n  [" . implode('] [', $p['botones']) . "]\n");
            }

            return self::SUCCESS;
        }

        if (! $token || ! $cuenta) {
            $this->error('Faltan WHATSAPP_TOKEN o WHATSAPP_BUSINESS_ACCOUNT_ID.');

            return self::FAILURE;
        }

        if ($this->option('estado')) {
            $r = Http::withToken($token)->get($url, ['fields' => 'name,status,category,rejected_reason', 'limit' => 100]);
            foreach ($r->json('data', []) as $t) {
                if (isset(PlantillasDeWhatsapp::PLANTILLAS[$t['name']])) {
                    $this->line("{$t['name']}: {$t['status']}" . (! empty($t['rejected_reason']) && $t['rejected_reason'] !== 'NONE' ? " ({$t['rejected_reason']})" : ''));
                }
            }

            return self::SUCCESS;
        }

        foreach (array_keys(PlantillasDeWhatsapp::PLANTILLAS) as $nombre) {
            $r = Http::withToken($token)->post($url, PlantillasDeWhatsapp::paraMeta($nombre));
            $r->successful()
                ? $this->info("{$nombre}: enviada ({$r->json('status')})")
                : $this->error("{$nombre}: " . ($r->json('error.error_user_msg') ?? $r->json('error.message') ?? $r->status()));
        }

        return self::SUCCESS;
    }
}
