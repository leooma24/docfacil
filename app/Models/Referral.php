<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 'referrer_code', 'referred_email',
        'referred_user_id', 'status', 'reward_type',
        'reward_days', 'rewarded_at',
    ];

    protected function casts(): array
    {
        return [
            'rewarded_at' => 'datetime',
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
}
