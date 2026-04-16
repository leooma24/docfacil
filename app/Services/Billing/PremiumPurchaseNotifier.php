<?php

namespace App\Services\Billing;

use App\Models\PremiumServicePurchase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Centraliza las notificaciones a admins sobre eventos de compras de servicios premium.
 * Antes esta lógica estaba duplicada en PremiumServiceCheckoutController y StripeWebhookController.
 */
class PremiumPurchaseNotifier
{
    /**
     * Manda email a los admins configurados en services.notifications.emails.
     * Falla silenciosamente con Log::warning — no rompe el flujo si SMTP cae.
     *
     * @param  string  $event  Verbo corto del evento, ej. "pago Stripe confirmado", "SPEI solicitado".
     */
    public function notify(PremiumServicePurchase $purchase, string $event): void
    {
        $emails = $this->adminEmails();
        if (empty($emails)) {
            Log::warning('PremiumPurchaseNotifier: sin emails admin configurados');
            return;
        }

        $subject = sprintf(
            '[DocFacil] %s — %s',
            $event,
            $purchase->service_name_snapshot,
        );

        $body = sprintf(
            "Evento en compra de servicio premium:\n\n"
            . "Clínica: %s (#%d)\n"
            . "Servicio: %s\n"
            . "Monto: $%s MXN\n"
            . "Método: %s\n"
            . "Estado actual: %s\n"
            . "Evento: %s\n\n"
            . "Ver en admin: %s",
            $purchase->clinic->name ?? '—',
            $purchase->clinic_id,
            $purchase->service_name_snapshot,
            number_format($purchase->amount_mxn, 2),
            $purchase->payment_method ?? 'no definido',
            $purchase->statusLabel(),
            $event,
            url('/admin/premium-service-purchases/' . $purchase->id),
        );

        $sent = 0;
        foreach ($emails as $email) {
            try {
                Mail::raw($body, fn ($m) => $m->to($email)->subject($subject));
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('PremiumPurchaseNotifier: email falló', [
                    'email' => $email,
                    'purchase_id' => $purchase->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($sent === 0 && count($emails) > 0) {
            // Ningún email se mandó — admin queda ciego. Loggear como error (no warning).
            Log::error('PremiumPurchaseNotifier: TODOS los emails admin fallaron', [
                'purchase_id' => $purchase->id,
                'event' => $event,
                'emails_attempted' => count($emails),
            ]);
        }
    }

    /**
     * @return string[]
     */
    private function adminEmails(): array
    {
        return collect(explode(',', (string) config('services.notifications.emails', 'leooma24@gmail.com')))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values()
            ->all();
    }
}
