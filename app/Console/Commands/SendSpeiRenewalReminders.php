<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Avisa a clínicas con plan pagado vía SPEI que su plan vence en 5 días,
 * para que transfieran y suban el nuevo comprobante antes de perder el servicio.
 * Solo SPEI — en Stripe la renovación es automática.
 */
class SendSpeiRenewalReminders extends Command
{
    protected $signature = 'docfacil:send-spei-reminders';

    protected $description = 'Notifica a clínicas SPEI que su plan está por vencer';

    public function handle(WhatsAppService $whatsapp): int
    {
        $targetDate = now()->addDays(5)->toDateString();

        $clinics = Clinic::query()
            ->where('payment_method', 'spei')
            ->whereNotNull('plan_ends_at')
            ->whereDate('plan_ends_at', $targetDate)
            ->where('plan', '!=', 'free')
            ->with('users')
            ->get();

        $this->info("Encontradas {$clinics->count()} clínicas para recordar.");

        $waOk = 0; $waFail = 0; $emailOk = 0; $emailFail = 0;

        foreach ($clinics as $clinic) {
            $owner = $clinic->users->first();
            $email = $owner?->email ?? $clinic->email;
            $phone = $clinic->phone;

            $planLabel = \App\Models\Clinic::displayNameForPlan($clinic->plan);
            $endsAt = $clinic->plan_ends_at->format('d/m/Y');
            $renewUrl = url('/doctor/pago-spei?plan=' . $clinic->plan . '&cycle=' . ($clinic->billing_cycle ?? 'monthly'));

            $waBody = "DocFacil: Tu plan {$planLabel} vence el {$endsAt}. "
                . "Para renovar, haz la transferencia SPEI y sube el comprobante: {$renewUrl}\n\n"
                . "Si quieres olvidarte de este tramite, considera el plan anual (2 meses gratis).";

            $emailBody = "Tu plan {$planLabel} vence el {$endsAt}.\n\n"
                . "Para renovar, haz la transferencia SPEI y sube el comprobante: {$renewUrl}\n\n"
                . "Si necesitas ayuda, contactanos por WhatsApp al 668 249 3398.\n\n"
                . "Cansado del tramite mensual? Considera el plan anual y ahorra 2 meses.";

            if ($phone) {
                try {
                    $ok = $whatsapp->sendMessage($phone, $waBody);
                    $ok ? $waOk++ : $waFail++;
                } catch (\Throwable $e) {
                    $waFail++;
                    Log::warning('SPEI reminder WA failed', ['clinic_id' => $clinic->id, 'err' => $e->getMessage()]);
                }
            }

            if ($email) {
                try {
                    Mail::raw($emailBody, fn ($m) => $m->to($email)->subject("[DocFacil] Tu plan vence el {$endsAt}"));
                    $emailOk++;
                } catch (\Throwable $e) {
                    $emailFail++;
                    Log::warning('SPEI reminder email failed', ['clinic_id' => $clinic->id, 'err' => $e->getMessage()]);
                }
            }
        }

        $this->info("WhatsApp: {$waOk} ok, {$waFail} fallidos. Email: {$emailOk} ok, {$emailFail} fallidos.");

        if ($waFail > 0 || $emailFail > 0) {
            Log::warning('SPEI renewal reminders con fallos', compact('waOk', 'waFail', 'emailOk', 'emailFail'));
        }

        return self::SUCCESS;
    }
}
