<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\PremiumServicePurchase;
use App\Services\Billing\PremiumPurchaseNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

/**
 * Pagos one-time / monthly / cotización para servicios premium del marketplace.
 * Distinto del flujo de plan de suscripción del SaaS.
 */
class PremiumServiceCheckoutController extends Controller
{
    public function __construct(
        private PremiumPurchaseNotifier $notifier,
    ) {}

    /**
     * Stripe Checkout para servicios premium one-time o monthly.
     */
    public function stripe(Request $request, PremiumServicePurchase $purchase): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->clinic_id === $purchase->clinic_id, 403);
        abort_unless($purchase->status === PremiumServicePurchase::STATUS_PENDING_PAYMENT, 409);

        $secret = config('services.stripe.secret');
        if (!$secret) {
            return redirect()->route('filament.doctor.pages.servicios-premium')
                ->with('error', 'Pagos con tarjeta todavía no están habilitados. Intenta por SPEI.');
        }

        $clinic = $purchase->clinic;
        $stripe = new StripeClient($secret);

        try {
            // Asegura customer (también puede fallar si Stripe API está caída)
            if (!$clinic->stripe_id) {
                $customer = $stripe->customers->create([
                    'email' => $user->email,
                    'name' => $clinic->name,
                    'metadata' => ['clinic_id' => $clinic->id],
                ]);
                $clinic->update(['stripe_id' => $customer->id]);
            }

            $isMonthly = $purchase->pricing_type === 'monthly';

            $sessionParams = [
                'mode' => $isMonthly ? 'subscription' : 'payment',
                'customer' => $clinic->stripe_id,
                'success_url' => route('premium.checkout.success', ['purchase' => $purchase->id]),
                'cancel_url' => route('filament.doctor.pages.servicios-premium'),
                'metadata' => [
                    'premium_purchase_id' => $purchase->id,
                    'clinic_id' => $clinic->id,
                ],
                'locale' => 'es-419',
                'adaptive_pricing' => ['enabled' => false],
            ];

            // Para items one-time o monthly creamos line_items dinámicos en MXN.
            // No requiere productos pre-creados en Stripe — se crea price_data inline.
            $priceData = [
                'currency' => 'mxn',
                'unit_amount' => (int) ($purchase->amount_mxn * 100),
                'product_data' => [
                    'name' => $purchase->service_name_snapshot,
                    'metadata' => ['premium_service_slug' => $purchase->service?->slug ?? ''],
                ],
            ];

            if ($isMonthly) {
                $priceData['recurring'] = ['interval' => 'month'];

                // Necesitamos metadata en la subscription (no solo en el session) para que
                // el handler de customer.subscription.deleted sepa que es una premium service sub.
                $sessionParams['subscription_data'] = [
                    'metadata' => [
                        'premium_purchase_id' => $purchase->id,
                        'clinic_id' => $clinic->id,
                    ],
                ];
            }

            $sessionParams['line_items'] = [[
                'quantity' => 1,
                'price_data' => $priceData,
            ]];

            $session = $stripe->checkout->sessions->create($sessionParams);

            $purchase->update(['stripe_session_id' => $session->id]);

            return redirect()->away($session->url);
        } catch (\Throwable $e) {
            Log::error('Premium service Stripe checkout failed', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('filament.doctor.pages.servicios-premium')
                ->with('error', 'No pudimos iniciar el pago. Intenta por SPEI o contáctanos.');
        }
    }

    /**
     * Pago por SPEI para servicio premium.
     * MVP: notifica a Omar para que coordine pago manual por WhatsApp.
     * Iteración 2: implementar upload de comprobante similar al SpeiCheckout del SaaS.
     */
    public function spei(Request $request, PremiumServicePurchase $purchase): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->clinic_id === $purchase->clinic_id, 403);

        $purchase->update(['payment_method' => 'spei']);

        $this->notifier->notify($purchase, 'pago SPEI solicitado — coordinar manualmente');

        return redirect()->route('filament.doctor.pages.servicios-premium')
            ->with('success', '¡Solicitud recibida! Omar te contactará por WhatsApp en menos de 4 horas con los datos para la transferencia SPEI.');
    }

    /**
     * Custom quote: simplemente notifica a Omar para cotizar manualmente.
     */
    public function quote(Request $request, PremiumServicePurchase $purchase): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->clinic_id === $purchase->clinic_id, 403);

        $this->notifier->notify($purchase, 'cotización solicitada');

        return redirect()->route('filament.doctor.pages.servicios-premium')
            ->with('success', '¡Solicitud de cotización recibida! Omar te contactará en menos de 24 hrs por WhatsApp.');
    }

    /**
     * Pantalla de éxito tras pago Stripe.
     * El estado real cambia via webhook (handlePremiumPurchaseCompleted en StripeWebhookController).
     */
    public function success(Request $request, PremiumServicePurchase $purchase): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->clinic_id === $purchase->clinic_id, 403);

        return redirect()->route('filament.doctor.pages.servicios-premium')
            ->with('success', '¡Pago recibido! Tu servicio se activará en unos minutos. Recibirás un correo de confirmación.');
    }

}
