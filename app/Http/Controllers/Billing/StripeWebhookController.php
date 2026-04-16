<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Commission;
use App\Models\PremiumServicePurchase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Endpoint que Stripe llama para notificar eventos de pago.
     * Rutado como POST /stripe/webhook (sin CSRF — ver VerifyCsrfToken).
     */
    public function handle(Request $request): Response
    {
        $secret = config('services.stripe.webhook.secret');
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (!$secret) {
            // Sin secret configurado, no podemos verificar la firma → rechazamos.
            Log::warning('Stripe webhook recibido sin webhook secret configurado');
            return response('webhook no configurado', 501);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature ?? '', $secret);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature invalid', ['error' => $e->getMessage()]);
            return response('firma inválida', 400);
        }

        // Idempotencia: si ya procesamos este event_id antes, no repetir.
        try {
            DB::table('stripe_webhook_events')->insert([
                'event_id' => $event->id,
                'event_type' => $event->type,
                'received_at' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Violación de unique constraint = ya recibido, Stripe retry.
            Log::info('Stripe webhook duplicado ignorado', ['event_id' => $event->id, 'type' => $event->type]);
            return response('ok (duplicate)', 200);
        }

        try {
            match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
                'invoice.payment_succeeded' => $this->handlePaymentSucceeded($event->data->object),
                'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
                default => Log::info('Stripe webhook evento ignorado', ['type' => $event->type]),
            };

            DB::table('stripe_webhook_events')
                ->where('event_id', $event->id)
                ->update(['processed_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler falló', [
                'event_id' => $event->id,
                'type' => $event->type,
                'error' => $e->getMessage(),
            ]);
            // Importante: re-lanzamos para que Laravel devuelva 500 y Stripe reintente.
            throw $e;
        }

        return response('ok', 200);
    }

    private function handleCheckoutCompleted($session): void
    {
        $metadata = (array) ($session->metadata ?? []);

        // Caso 1: compra de servicio premium del marketplace
        if (!empty($metadata['premium_purchase_id'])) {
            $this->handlePremiumPurchaseCompleted($session, $metadata);
            return;
        }

        // Caso 2: suscripción al SaaS (plan)
        $clinicId = $metadata['clinic_id'] ?? null;
        $plan = $metadata['plan'] ?? null;
        $cycle = $metadata['billing_cycle'] ?? 'monthly';
        $soldByUserId = $metadata['sold_by_user_id'] ?? null;

        $clinic = $clinicId ? Clinic::find($clinicId) : null;
        if (!$clinic || !$plan) {
            Log::warning('checkout.session.completed sin metadata válida', compact('clinicId', 'plan'));
            return;
        }

        $clinic->activatePlan($plan, $cycle, 'stripe');

        if ($soldByUserId && is_numeric($soldByUserId)) {
            Commission::generateForSale(
                clinic: $clinic,
                userId: (int) $soldByUserId,
                plan: $plan,
                billingCycle: $cycle,
                paymentMethod: 'stripe',
            );
        }

        Log::info('Plan activado vía Stripe', ['clinic_id' => $clinic->id, 'plan' => $plan, 'cycle' => $cycle]);
    }

    private function handlePremiumPurchaseCompleted($session, array $metadata): void
    {
        $purchase = PremiumServicePurchase::find($metadata['premium_purchase_id']);
        if (!$purchase) {
            Log::warning('Stripe webhook: PremiumServicePurchase no encontrado', $metadata);
            return;
        }
        if ($purchase->isPaid()) {
            return; // ya marcado, evita doble proceso
        }

        $purchase->markPaid('stripe', $session->id ?? null);

        // Notificar a admins que hay un servicio nuevo en cola para ejecutar
        $this->notifyPremiumPurchasePaid($purchase);

        Log::info('Servicio premium pagado via Stripe', [
            'purchase_id' => $purchase->id,
            'service' => $purchase->service_name_snapshot,
            'amount' => $purchase->amount_mxn,
        ]);
    }

    private function notifyPremiumPurchasePaid(PremiumServicePurchase $purchase): void
    {
        $emails = collect(explode(',', (string) config('services.notifications.emails', 'leooma24@gmail.com')))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->all();

        $body = sprintf(
            "Pago confirmado de servicio premium:\n\nClinica: %s (#%d)\nServicio: %s\nMonto: $%s MXN\nMetodo: Stripe\n\nIniciar ejecucion: %s",
            $purchase->clinic->name ?? '—',
            $purchase->clinic_id,
            $purchase->service_name_snapshot,
            number_format($purchase->amount_mxn, 2),
            url('/admin/premium-service-purchases/' . $purchase->id),
        );

        foreach ($emails as $email) {
            try {
                Mail::raw($body, fn ($m) => $m->to($email)->subject('[DocFacil] Servicio premium pagado - ' . $purchase->service_name_snapshot));
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar correo admin de servicio premium pagado', ['err' => $e->getMessage()]);
            }
        }
    }

    private function handlePaymentSucceeded($invoice): void
    {
        // El primer pago de una suscripción también dispara invoice.payment_succeeded.
        // Como checkout.session.completed ya activó el plan, aquí saltamos para evitar
        // extender el ciclo dos veces (quedaría +60 días en el primer mes).
        if (($invoice->billing_reason ?? null) === 'subscription_create') {
            Log::info('Stripe invoice.payment_succeeded: primer pago ignorado (ya activado en checkout)', [
                'invoice_id' => $invoice->id ?? null,
            ]);
            return;
        }

        // Renovaciones recurrentes: extender plan_ends_at
        $customerId = $invoice->customer ?? null;
        if (!$customerId) {
            Log::error('Stripe payment_succeeded sin customer_id', ['invoice_id' => $invoice->id ?? null]);
            return;
        }
        $clinic = Clinic::where('stripe_id', $customerId)->first();
        if (!$clinic) {
            Log::error('Stripe payment_succeeded sin clínica encontrada — renewal orphan', [
                'customer_id' => $customerId,
                'invoice_id' => $invoice->id ?? null,
            ]);
            return;
        }
        if (!$clinic->plan || $clinic->plan === 'free') {
            Log::warning('Stripe payment_succeeded ignorado: clínica en plan free', [
                'clinic_id' => $clinic->id,
                'customer_id' => $customerId,
            ]);
            return;
        }

        $clinic->activatePlan($clinic->plan, $clinic->billing_cycle ?? 'monthly', 'stripe');
        Log::info('Plan renovado vía Stripe', ['clinic_id' => $clinic->id]);
    }

    private function handleSubscriptionDeleted($subscription): void
    {
        $customerId = $subscription->customer ?? null;
        if (!$customerId) {
            return;
        }
        $clinic = Clinic::where('stripe_id', $customerId)->first();
        if (!$clinic) {
            return;
        }

        $clinic->update([
            'auto_renew' => false,
            'cancelled_at' => now(),
        ]);
        Log::info('Suscripción Stripe cancelada', ['clinic_id' => $clinic->id]);
    }
}
