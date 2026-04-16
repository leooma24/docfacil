<?php

namespace App\Console\Commands;

use App\Models\Commission;
use Illuminate\Console\Command;
use Stripe\StripeClient;

/**
 * Crea (o reusa) los 6 productos/precios en Stripe para los 3 planes x 2 ciclos
 * y emite las líneas `STRIPE_PRICE_*` listas para pegar en .env.
 *
 * Idempotente: si ya existe un producto con el mismo nombre, lo reusa. Si ya existe
 * un precio con el mismo monto + intervalo + producto, lo reusa.
 *
 * Uso: php artisan stripe:setup-prices [--dry-run]
 */
class StripeSetupPrices extends Command
{
    protected $signature = 'stripe:setup-prices {--dry-run : Solo mostrar qué se crearía}';

    protected $description = 'Crea los productos y precios de planes en Stripe (basico, pro, clinica × mensual, anual)';

    public function handle(): int
    {
        $secret = config('services.stripe.secret');
        if (!$secret) {
            $this->error('STRIPE_SECRET no configurado en .env');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $stripe = new StripeClient($secret);

        $plans = [
            'basico' => ['label' => 'DocFácil Básico', 'key' => 'basico'],
            'profesional' => ['label' => 'DocFácil Pro', 'key' => 'pro'],
            'clinica' => ['label' => 'DocFácil Clínica', 'key' => 'clinica'],
        ];

        $envLines = [];

        foreach ($plans as $plan => $info) {
            $monthly = Commission::monthlyPriceForPlan($plan);
            $annual = Commission::annualPriceForPlan($plan);

            // Producto (1 por plan)
            $product = $this->findOrCreateProduct($stripe, $info['label'], $plan, $dryRun);
            $this->line("<fg=cyan>Producto</> {$info['label']} → " . ($product['id'] ?? '(dry-run)'));

            // Precio mensual
            $priceMonthly = $this->findOrCreatePrice(
                $stripe, $product['id'] ?? null, $monthly, 'month', "{$info['key']}_monthly", $dryRun
            );
            $envLines["STRIPE_PRICE_" . strtoupper($info['key']) . "_MONTHLY"] = $priceMonthly['id'] ?? 'DRY_RUN';
            $this->line("  Mensual \${$monthly}/mes → " . ($priceMonthly['id'] ?? '(dry-run)'));

            // Precio anual
            $priceAnnual = $this->findOrCreatePrice(
                $stripe, $product['id'] ?? null, $annual, 'year', "{$info['key']}_annual", $dryRun
            );
            $envLines["STRIPE_PRICE_" . strtoupper($info['key']) . "_ANNUAL"] = $priceAnnual['id'] ?? 'DRY_RUN';
            $this->line("  Anual   \${$annual}/año → " . ($priceAnnual['id'] ?? '(dry-run)'));
        }

        $this->newLine();
        $this->info('Agrega estas líneas a tu .env:');
        $this->line('');
        foreach ($envLines as $key => $value) {
            $this->line("{$key}={$value}");
        }

        return self::SUCCESS;
    }

    private function findOrCreateProduct(StripeClient $stripe, string $label, string $planKey, bool $dryRun): ?array
    {
        $existing = $stripe->products->search([
            'query' => "metadata['docfacil_plan']:'{$planKey}'",
            'limit' => 1,
        ]);

        if (!empty($existing->data)) {
            return $existing->data[0]->toArray();
        }

        if ($dryRun) {
            $this->line("  <fg=yellow>[dry-run]</> crear producto {$label}");
            return null;
        }

        $created = $stripe->products->create([
            'name' => $label,
            'metadata' => ['docfacil_plan' => $planKey],
        ]);
        return $created->toArray();
    }

    private function findOrCreatePrice(
        StripeClient $stripe,
        ?string $productId,
        int $amountMxn,
        string $interval,
        string $lookupKey,
        bool $dryRun,
    ): ?array {
        if (!$productId) {
            return null;
        }

        // Paginamos todos los precios activos del producto — no asumir <100.
        $allPrices = [];
        $params = ['product' => $productId, 'active' => true, 'limit' => 100];
        do {
            $page = $stripe->prices->all($params);
            foreach ($page->data as $p) {
                $allPrices[] = $p;
            }
            if ($page->has_more) {
                $params['starting_after'] = end($page->data)->id;
            }
        } while ($page->has_more);

        // Si ya hay un precio con el monto+interval+moneda exactos, lo reusamos.
        foreach ($allPrices as $p) {
            $sameInterval = $p->recurring && $p->recurring->interval === $interval;
            $sameAmount = $p->unit_amount === $amountMxn * 100;
            $sameCurrency = $p->currency === 'mxn';
            if ($sameInterval && $sameAmount && $sameCurrency) {
                return $p->toArray();
            }
        }

        if ($dryRun) {
            $this->line("  <fg=yellow>[dry-run]</> crear precio \${$amountMxn} MXN cada 1 {$interval}");
            return null;
        }

        $fullLookupKey = "docfacil_{$lookupKey}";

        // Si existe un precio (probablemente viejo con monto distinto) usando ese lookup_key,
        // lo liberamos antes de crear el nuevo — Stripe no permite duplicar lookup_keys.
        foreach ($allPrices as $p) {
            if (($p->lookup_key ?? null) === $fullLookupKey) {
                $stripe->prices->update($p->id, ['lookup_key' => null]);
                $this->line("  <fg=yellow>↻</> liberado lookup_key del precio anterior {$p->id}");
            }
        }

        $created = $stripe->prices->create([
            'product' => $productId,
            'unit_amount' => $amountMxn * 100, // Stripe usa centavos
            'currency' => 'mxn',
            'recurring' => ['interval' => $interval],
            'lookup_key' => $fullLookupKey,
            'metadata' => ['docfacil_lookup' => $lookupKey],
        ]);
        return $created->toArray();
    }
}
