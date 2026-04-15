<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\SpeiPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SpeiReviewService
{
    public function __construct(
        protected WhatsAppService $whatsapp,
    ) {}

    /**
     * Aprueba un pago SPEI:
     * - marca el registro,
     * - activa el plan en la clínica,
     * - genera las comisiones correspondientes si hay vendedor,
     * - notifica al cliente por email + WhatsApp.
     */
    public function approve(SpeiPayment $payment, User $reviewer, ?string $notes = null): void
    {
        if (!$payment->isPending()) {
            return;
        }

        DB::transaction(function () use ($payment, $reviewer, $notes) {
            $clinic = $payment->clinic()->lockForUpdate()->first();
            if (!$clinic) {
                throw new \RuntimeException("Pago SPEI {$payment->id} sin clínica.");
            }

            // Activa el plan por el número de días según ciclo
            $clinic->activatePlan($payment->plan, $payment->billing_cycle, 'spei');

            // Genera comisiones si la clínica fue vendida por alguien
            if ($clinic->sold_by_user_id) {
                Commission::generateForSale(
                    clinic: $clinic,
                    userId: $clinic->sold_by_user_id,
                    plan: $payment->plan,
                    billingCycle: $payment->billing_cycle,
                    paymentMethod: 'spei',
                );
            }

            $payment->update([
                'status' => SpeiPayment::STATUS_APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $notes,
                'plan_activated_until' => $clinic->plan_ends_at,
            ]);
        });

        $this->notifyClient($payment, 'approved');
    }

    /**
     * Rechaza un pago SPEI. Motivo es obligatorio y se envía al cliente.
     */
    public function reject(SpeiPayment $payment, User $reviewer, string $reason): void
    {
        if (!$payment->isPending()) {
            return;
        }

        $payment->update([
            'status' => SpeiPayment::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $reason,
        ]);

        $this->notifyClient($payment, 'rejected');
    }

    /**
     * Manda email + WhatsApp al cliente con el resultado de su pago.
     */
    protected function notifyClient(SpeiPayment $payment, string $result): void
    {
        $clinic = $payment->clinic;
        if (!$clinic) {
            return;
        }

        $owner = $clinic->users()->orderBy('id')->first();
        $email = $owner?->email ?? $clinic->email;
        $phone = $clinic->phone;

        $planLabel = ucfirst($payment->plan === 'profesional' ? 'Pro' : $payment->plan);
        $cycleLabel = $payment->billing_cycle === 'annual' ? 'anual' : 'mensual';
        $amount = number_format($payment->amount, 2);

        if ($result === 'approved') {
            $endsAt = $payment->fresh()->plan_activated_until?->format('d/m/Y') ?? '—';
            $subject = '✅ Pago aprobado · DocFácil';
            $body = "Aprobamos tu pago SPEI por \${$amount} MXN del plan {$planLabel} ({$cycleLabel}).\n\n"
                . "Tu plan queda activo hasta: {$endsAt}\n\n"
                . "Entra a DocFácil: " . url('/doctor');
            $waMessage = "✅ DocFácil: Aprobamos tu pago SPEI de \${$amount} por el plan {$planLabel} ({$cycleLabel}). Tu plan queda activo hasta {$endsAt}. ¡Gracias!";
        } else {
            $reason = $payment->review_notes ?: 'No pudimos verificar el comprobante.';
            $subject = '⚠ Pago SPEI rechazado · DocFácil';
            $body = "No pudimos aprobar tu pago SPEI por \${$amount} MXN.\n\n"
                . "Motivo: {$reason}\n\n"
                . "Puedes intentar de nuevo o contactar a Omar por WhatsApp al 668 249 3398.";
            $waMessage = "⚠ DocFácil: No pudimos aprobar tu pago SPEI de \${$amount}. Motivo: {$reason}. Contáctanos si tienes dudas: wa.me/526682493398";
        }

        if ($email) {
            try {
                Mail::raw($body, fn ($m) => $m->to($email)->subject($subject));
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar correo SPEI al cliente', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            }
        }

        if ($phone) {
            try {
                $this->whatsapp->sendMessage($phone, $waMessage);
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar WhatsApp SPEI al cliente', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
