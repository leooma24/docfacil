<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\StripeClient;

/**
 * Registra el webhook endpoint de DocFácil en Stripe y devuelve el signing secret.
 * Idempotente: si ya existe un endpoint apuntando a la misma URL, lo reusa.
 *
 * Uso: php artisan stripe:setup-webhook
 */
class StripeSetupWebhook extends Command
{
    protected $signature = 'stripe:setup-webhook';

    protected $description = 'Crea o reusa el webhook endpoint de DocFácil en Stripe';

    public function handle(): int
    {
        $secret = config('services.stripe.secret');
        if (!$secret) {
            $this->error('STRIPE_SECRET no configurado en .env');
            return self::FAILURE;
        }

        $url = route('stripe.webhook'); // https://docfacil.tu-app.co/billing/stripe/webhook

        $events = [
            'checkout.session.completed',
            'invoice.payment_succeeded',
            'invoice.payment_failed',
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ];

        $stripe = new StripeClient($secret);

        // Buscar si ya existe uno con esta URL
        $existing = $stripe->webhookEndpoints->all(['limit' => 100]);
        foreach ($existing->data as $ep) {
            if ($ep->url === $url) {
                $this->warn("Ya existe un webhook con esta URL (id: {$ep->id}).");
                $this->line("Eventos actuales: " . implode(', ', $ep->enabled_events));
                $this->newLine();
                $this->line("⚠ El signing secret solo se muestra al momento de crear el endpoint.");
                $this->line("Si necesitas el secret y no lo tienes guardado, elimínalo en Stripe Dashboard y vuelve a correr este comando.");
                $this->line("O recupéralo desde Dashboard → Developers → Webhooks → (endpoint) → Signing secret → Reveal.");
                return self::SUCCESS;
            }
        }

        $this->info("Creando webhook endpoint para: {$url}");

        $endpoint = $stripe->webhookEndpoints->create([
            'url' => $url,
            'enabled_events' => $events,
            'description' => 'DocFácil — activación de planes y comisiones',
        ]);

        $this->info("✓ Webhook creado: {$endpoint->id}");
        $this->newLine();
        $this->line("Eventos habilitados:");
        foreach ($endpoint->enabled_events as $e) {
            $this->line("  • {$e}");
        }
        $this->newLine();
        $this->info('Agrega esta línea a tu .env:');
        $this->line('');
        $this->line("STRIPE_WEBHOOK_SECRET={$endpoint->secret}");

        return self::SUCCESS;
    }
}
