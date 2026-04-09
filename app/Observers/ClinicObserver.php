<?php

namespace App\Observers;

use App\Models\Clinic;
use App\Models\Commission;
use App\Models\Prospect;

class ClinicObserver
{
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

        // Clawback: cancelación dentro de 90 días
        if ($clinic->wasChanged('cancelled_at') && $clinic->cancelled_at) {
            if ($clinic->sold_at && $clinic->cancelled_at->diffInDays($clinic->sold_at) < 90) {
                $this->clawback($clinic);
            }
        }
    }

    private function createCommission(Clinic $clinic, string $tier): void
    {
        // Evitar duplicados
        if (Commission::where('clinic_id', $clinic->id)->where('tier', $tier)->exists()) {
            return;
        }

        // Plan debe calificar
        if (!in_array($clinic->plan, Commission::COMMISSIONABLE_PLANS)) {
            return;
        }

        Commission::create([
            'user_id' => $clinic->sold_by_user_id,
            'clinic_id' => $clinic->id,
            'prospect_id' => Prospect::where('converted_clinic_id', $clinic->id)->value('id'),
            'tier' => $tier,
            'amount' => Commission::halfAmount($clinic->plan),
            'plan_at_sale' => $clinic->plan,
            'status' => 'pending',
            'earned_at' => now(),
        ]);
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
