<?php

namespace App\Console\Commands;

use App\Mail\LeadHeatedUpMail;
use App\Models\Prospect;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Manda una alerta de "lead caliente" de prueba a los destinatarios
 * configurados (config/services.notifications). NO modifica datos —
 * usa el primer prospect en BD solo para construir el mensaje.
 *
 * Uso:  php artisan docfacil:test-lead-alert
 */
class TestLeadAlert extends Command
{
    protected $signature = 'docfacil:test-lead-alert {--email-only : Skip WhatsApp, solo email}';
    protected $description = 'Manda alerta de lead caliente de prueba (sin modificar BD)';

    public function handle(): int
    {
        $prospect = Prospect::orderBy('id', 'desc')->first();
        if (! $prospect) {
            $this->error('No hay prospectos en BD.');
            return 1;
        }

        $score = 87; // valor de prueba
        $adminEmail = collect(explode(',', (string) config('services.notifications.emails', '')))
            ->map(fn ($e) => trim($e))->filter()->first();
        $adminPhone = config('services.notifications.phone');

        $this->line("Prospect de prueba: #{$prospect->id} {$prospect->cleanName()}");
        $this->line("Email destinatario: {$adminEmail}");
        $this->line("Teléfono destinatario: " . ($adminPhone ?: '(no configurado)'));
        $this->newLine();

        // Email
        if (! empty($adminEmail)) {
            try {
                Mail::to($adminEmail)->send(new LeadHeatedUpMail($prospect, $score));
                $this->info('✅ Email enviado a ' . $adminEmail);
            } catch (\Throwable $e) {
                $this->error('❌ Email falló: ' . $e->getMessage());
            }
        } else {
            $this->warn('⚠️  No hay email configurado en services.notifications.emails');
        }

        // WhatsApp
        if (! $this->option('email-only') && ! empty($adminPhone)) {
            if (empty(config('services.whatsapp.token'))) {
                $this->warn('⚠️  WhatsApp token no configurado. Skipeando WhatsApp.');
            } else {
                $name = $prospect->cleanName() ?: 'Sin nombre';
                $msg = "🔥 *PRUEBA · Lead caliente · Score {$score}*\n\n" .
                    "*{$name}*" .
                    ($prospect->specialty ? " · {$prospect->specialty}" : '') .
                    ($prospect->city ? "\n📍 {$prospect->city}" : '') .
                    "\n\n_Este es un mensaje de prueba. Sistema funcionando._";

                $sent = app(WhatsAppService::class)->sendMessage($adminPhone, $msg);
                if ($sent) {
                    $this->info('✅ WhatsApp enviado a ' . $adminPhone);
                } else {
                    $this->error('❌ WhatsApp falló (revisa storage/logs/laravel.log)');
                }
            }
        }

        return 0;
    }
}
