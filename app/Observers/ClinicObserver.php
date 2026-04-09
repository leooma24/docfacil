<?php

namespace App\Observers;

use App\Mail\CommissionEarnedMail;
use App\Models\Clinic;
use App\Models\Commission;
use App\Models\Prospect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClinicObserver
{
    /**
     * Bloquea reasignación de sold_by_user_id post-creación.
     * Una clínica vendida no puede cambiar de vendedor dueño —
     * eso evita manipulación de comisiones de otros vendedores.
     */
    public function updating(Clinic $clinic): void
    {
        if ($clinic->isDirty('sold_by_user_id')
            && $clinic->getOriginal('sold_by_user_id') !== null) {
            throw new \LogicException(
                'sold_by_user_id es inmutable una vez asignado. ' .
                'Contacta al admin si necesitas transferir la venta.'
            );
        }
    }

    public function updated(Clinic $clinic): void
    {
        // Tier 1: primer pago recibido
        if ($clinic->wasChanged('first_payment_received_at')
            && $clinic->first_payment_received_at
            && $clinic->sold_by_user_id) {
            $this->createCommission($clinic, 'first');
        }

        // Tier 2: segundo pago recibido (no cancelada)
        if ($clinic->wasChanged('second_payment_received_at')
            && $clinic->second_payment_received_at
            && $clinic->sold_by_user_id
            && !$clinic->cancelled_at) {
            $this->createCommission($clinic, 'second');
        }

        // Clawback: cancelación dentro de 90 días desde la venta.
        // Comparamos directo con addDays para evitar ambigüedades de signo en diffInDays.
        if ($clinic->wasChanged('cancelled_at') && $clinic->cancelled_at) {
            if ($clinic->sold_at && $clinic->cancelled_at->lessThan($clinic->sold_at->copy()->addDays(90))) {
                $this->clawback($clinic);
            }
        }
    }

    private function createCommission(Clinic $clinic, string $tier): void
    {
        // Plan debe calificar
        if (!in_array($clinic->plan, Commission::COMMISSIONABLE_PLANS)) {
            return;
        }

        // Atomicidad: el unique index (clinic_id, tier) garantiza que aunque
        // dos procesos lleguen aquí concurrentemente, solo uno insertará.
        // El otro caerá en QueryException que atrapamos silenciosamente.
        try {
            $commission = Commission::create([
                'user_id' => $clinic->sold_by_user_id,
                'clinic_id' => $clinic->id,
                'prospect_id' => Prospect::where('converted_clinic_id', $clinic->id)->value('id'),
                'tier' => $tier,
                'amount' => Commission::halfAmount($clinic->plan),
                'plan_at_sale' => $clinic->plan,
                'status' => 'pending',
                'earned_at' => now(),
            ]);

            // Notificar al vendedor por email (no bloquear si falla)
            $this->notifyCommissionEarned($commission);
        } catch (\Illuminate\Database\QueryException $e) {
            // 23000 = integrity constraint violation (duplicate key)
            if ($e->getCode() !== '23000') {
                throw $e;
            }
        }
    }

    private function notifyCommissionEarned(Commission $commission): void
    {
        try {
            $commission->load(['user', 'clinic']);
            if ($commission->user && $commission->user->email) {
                Mail::to($commission->user->email)->send(new CommissionEarnedMail($commission));
            }
        } catch (\Throwable $e) {
            // No bloquear la creación de comisión si el email falla
            Log::warning('No se pudo enviar email de comisión ganada', [
                'commission_id' => $commission->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function clawback(Clinic $clinic): void
    {
        Commission::where('clinic_id', $clinic->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'notes' => 'Cancelada: clínica canceló en <90 días desde venta',
            ]);

        Commission::where('clinic_id', $clinic->id)
            ->where('status', 'paid')
            ->update([
                'status' => 'clawed_back',
                'notes' => 'Clawback: clínica canceló en <90 días — descontar del próximo corte',
            ]);
    }
}
