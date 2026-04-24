<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Referral extends Model
{
    /**
     * Cap lifetime de recompensas en cascade: 12 meses (1 año).
     * Despues de esto, el referente deja de recibir meses gratis por
     * los pagos recurrentes de este referido.
     */
    public const CASCADE_REWARD_CAP = 12;

    /**
     * Dias de credito que el referente gana cada vez que el referido paga.
     */
    public const CASCADE_REWARD_DAYS = 30;

    /**
     * Ventana minima entre recompensas para evitar doble-counting si un
     * webhook dispara activatePlan 2 veces el mismo mes.
     */
    public const CASCADE_MIN_GAP_DAYS = 20;

    protected $fillable = [
        'referrer_id', 'referrer_code', 'referred_email',
        'referred_user_id', 'status', 'reward_type',
        'reward_days', 'rewarded_at',
        'cascade_rewards_granted', 'last_cascade_reward_at',
    ];

    protected function casts(): array
    {
        return [
            'rewarded_at' => 'datetime',
            'last_cascade_reward_at' => 'datetime',
            'cascade_rewards_granted' => 'integer',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public static function processReferral(User $newUser, string $referralCode): void
    {
        $referrer = User::where('referral_code', $referralCode)->first();

        if (!$referrer || $referrer->id === $newUser->id) {
            return;
        }

        $referral = self::where('referrer_code', $referralCode)
            ->where('referred_email', $newUser->email)
            ->first();

        if (!$referral) {
            $referral = self::create([
                'referrer_id' => $referrer->id,
                'referrer_code' => $referralCode,
                'referred_email' => $newUser->email,
                'referred_user_id' => $newUser->id,
                'status' => 'registered',
            ]);
        } else {
            $referral->update([
                'referred_user_id' => $newUser->id,
                'status' => 'registered',
            ]);
        }

        // Reward: extend trial by 15 days for both
        if ($referrer->clinic) {
            $currentEnd = $referrer->clinic->trial_ends_at ?? now();
            $referrer->clinic->update([
                'trial_ends_at' => $currentEnd->addDays(15),
            ]);
        }

        if ($newUser->clinic) {
            $currentEnd = $newUser->clinic->trial_ends_at ?? now();
            $newUser->clinic->update([
                'trial_ends_at' => $currentEnd->addDays(15),
            ]);
        }

        $referral->update([
            'status' => 'rewarded',
            'reward_type' => 'trial_extension',
            'reward_days' => 15,
            'rewarded_at' => now(),
        ]);
    }

    /**
     * Cascade reward: cuando el referido paga (primer pago o renovacion),
     * el referente gana 30 dias gratis, hasta un cap de 12 recompensas
     * totales (1 año completo).
     *
     * Se llama desde Clinic::activatePlan() cada vez que un pago se
     * procesa (Stripe subscription_create, Stripe renewal, SPEI approve).
     *
     * Idempotente: si se llama dos veces dentro de CASCADE_MIN_GAP_DAYS
     * no aplica la segunda. Si ya se alcanzo el cap, no aplica.
     */
    public static function grantCascadeReward(\App\Models\Clinic $paidClinic): void
    {
        // Necesitamos el user/doctor dueño para buscar su referral
        $referredUser = $paidClinic->users()->where('role', 'doctor')->first();
        if (!$referredUser) return;

        $referral = self::where('referred_user_id', $referredUser->id)
            ->where('status', 'rewarded')
            ->first();

        if (!$referral) return;
        if ($referral->cascade_rewards_granted >= self::CASCADE_REWARD_CAP) return;

        // Idempotencia: si se dio reward hace menos de CASCADE_MIN_GAP_DAYS, saltar
        if ($referral->last_cascade_reward_at
            && $referral->last_cascade_reward_at->isAfter(now()->subDays(self::CASCADE_MIN_GAP_DAYS))) {
            return;
        }

        $referrer = $referral->referrer;
        if (!$referrer || !$referrer->clinic) return;

        $referrerClinic = $referrer->clinic;
        $days = self::CASCADE_REWARD_DAYS;

        // Si el referente tiene plan pagado activo → extender plan_ends_at
        // (gana 1 mes adicional de servicio pagado sin cobrarle)
        // Si esta en trial/free → extender trial_ends_at
        if ($referrerClinic->planIsPaid() && $referrerClinic->planIsActive()) {
            $currentEnd = $referrerClinic->plan_ends_at ?? now();
            $referrerClinic->update([
                'plan_ends_at' => $currentEnd->addDays($days),
            ]);
        } else {
            $currentEnd = $referrerClinic->trial_ends_at ?? now();
            $referrerClinic->update([
                'trial_ends_at' => $currentEnd->addDays($days),
            ]);
        }

        $referral->update([
            'cascade_rewards_granted' => $referral->cascade_rewards_granted + 1,
            'last_cascade_reward_at' => now(),
        ]);

        Log::info('Referral cascade reward granted', [
            'referral_id' => $referral->id,
            'referrer_user_id' => $referrer->id,
            'referred_clinic_id' => $paidClinic->id,
            'days' => $days,
            'total_rewards' => $referral->cascade_rewards_granted,
        ]);
    }
}
