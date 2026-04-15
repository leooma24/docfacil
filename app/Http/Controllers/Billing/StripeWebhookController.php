<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
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

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'invoice.payment_succeeded' => $this->handlePaymentSucceeded($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            default => Log::info('Stripe webhook evento ignorado', ['type' => $event->type]),
        };

        return response('ok', 200);
    }

    private function handleCheckoutCompleted($session): void
    {
        $metadata = (array) ($session->metadata ?? []);
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

    private function handlePaymentSucceeded($invoice): void
    {
        // Renovaciones recurrentes: extender plan_ends_at
        $customerId = $invoice->customer ?? null;
        if (!$customerId) {
            return;
        }
        $clinic = Clinic::where('stripe_id', $customerId)->first();
        if (!$clinic || !$clinic->plan || $clinic->plan === 'free') {
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
