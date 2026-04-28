<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeCheckoutController extends Controller
{
    /**
     * Crea una Stripe Checkout Session para el plan+ciclo elegidos y redirige al usuario.
     *
     * Se activa cuando hay STRIPE_SECRET + el price ID correspondiente configurados.
     * Si aún no hay credenciales, devolvemos un mensaje amigable al usuario.
     */
    public function checkout(Request $request, string $plan, string $cycle): RedirectResponse
    {
        abort_unless(in_array($plan, ['basico', 'profesional', 'clinica'], true), 404);
        abort_unless(in_array($cycle, ['monthly', 'annual'], true), 404);

        $user = $request->user();
        abort_unless($user && $user->clinic, 403);

        $secret = config('services.stripe.secret');
        $priceKey = $plan . '_' . $cycle; // basico_monthly, pro_annual, etc.
        $priceId = config("services.stripe.prices.{$priceKey}");

        if (!$secret || !$priceId) {
            return redirect()
                ->route('filament.doctor.pages.actualizar-plan')
                ->with('error', 'Pagos con tarjeta todavía no están habilitados. Mientras tanto puedes pagar por SPEI.');
        }

        $clinic = $user->clinic;

        try {
            $stripe = new StripeClient($secret);

            // Aseguramos que la clínica tenga customer en Stripe
            if (!$clinic->stripe_id) {
                $customer = $stripe->customers->create([
                    'email' => $user->email,
                    'name' => $clinic->name,
                    'metadata' => ['clinic_id' => $clinic->id],
                ]);
                $clinic->update(['stripe_id' => $customer->id]);
            }

            $session = $stripe->checkout->sessions->create([
                'mode' => 'subscription',
                'customer' => $clinic->stripe_id,
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'success_url' => route('stripe.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('filament.doctor.pages.actualizar-plan'),
                'metadata' => [
                    'clinic_id' => $clinic->id,
                    'user_id' => $user->id,
                    'plan' => $plan,
                    'billing_cycle' => $cycle,
                    'sold_by_user_id' => (string) ($clinic->sold_by_user_id ?? ''),
                ],
                'locale' => 'es-419',
                // Forzar MXN — sin esto Stripe hace conversión a USD para visitantes fuera de México
                'adaptive_pricing' => ['enabled' => false],
                // Restringir a México (misma razón)
                'billing_address_collection' => 'auto',
            ]);

            return redirect()->away($session->url);
        } catch (\Throwable $e) {
            Log::error('Stripe checkout failed', [
                'error' => $e->getMessage(),
                'clinic_id' => $clinic->id,
            ]);
            return redirect()
                ->route('filament.doctor.pages.actualizar-plan')
                ->with('error', 'No pudimos iniciar el pago con tarjeta. Intenta por SPEI o contacta soporte.');
        }
    }

    /**
     * Pantalla a la que Stripe regresa al usuario tras pagar.
     * El evento real de activación llega por webhook; aquí solo agradecemos.
     */
    public function success(Request $request)
    {
        // Analytics: subscription_upgraded en el proximo render. El plan/cycle
        // exactos los obtenemos del clinic actual (ya actualizado por el webhook).
        $clinic = auth()->user()?->clinic;
        if ($clinic) {
            session()->push('analytics_events', [
                'name' => 'subscription_upgraded',
                'params' => [
                    'plan' => $clinic->plan,
                    'billing_cycle' => $clinic->billing_cycle ?? 'monthly',
                    'value_mxn' => match ($clinic->plan) {
                        'basico' => ($clinic->billing_cycle === 'annual' ? 4990 : 499),
                        'profesional' => ($clinic->billing_cycle === 'annual' ? 9990 : 999),
                        'clinica' => ($clinic->billing_cycle === 'annual' ? 19990 : 1999),
                        default => 0,
                    },
                    'gateway' => 'stripe',
                ],
            ]);
        }

        return redirect()
            ->route('filament.doctor.pages.actualizar-plan')
            ->with('success', '¡Pago recibido! Tu plan se activará en unos segundos. Si no ves el cambio, recarga la página.');
    }
}
