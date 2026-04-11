<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Central AI gatekeeper.
 *
 * All AI features must check AI::enabled() before making any call.
 * This lets us disable AI globally via .env (AI_ENABLED=false).
 *
 * Per-plan features can additionally check AI::planCanUseFeature().
 */
class AI
{
    /**
     * Is AI globally enabled for the app?
     * When false, all AI calls return null silently.
     */
    public static function enabled(): bool
    {
        return (bool) config('services.ai.enabled', false);
    }

    /**
     * Does a specific plan have access to this AI feature?
     */
    public static function planCanUseFeature(string $plan, string $feature): bool
    {
        if (!self::enabled()) {
            return false;
        }

        // Per-plan feature matrix. Adjust as needed.
        $matrix = [
            'free' => [],
            'basico' => [
                'voice_dictation', // browser Web Speech API - free
            ],
            'profesional' => [
                'voice_dictation',
                'patient_summary',
                'smart_dictation',
                'dx_suggestions',
                'consent_templates',
                'clinic_insights',
                'predictive_insights',
                'chatbot',
                'command_palette_ai',
                'message_generator',
                'whatsapp_bot',
                'live_consultation',
            ],
            'clinica' => [
                'voice_dictation',
                'patient_summary',
                'smart_dictation',
                'dx_suggestions',
                'consent_templates',
                'clinic_insights',
                'predictive_insights',
                'chatbot',
                'command_palette_ai',
                'message_generator',
                'whatsapp_bot',
                'live_consultation',
            ],
        ];

        return in_array($feature, $matrix[$plan] ?? []);
    }

    /**
     * Check if the current authenticated clinic can use a feature.
     */
    public static function currentClinicCanUse(string $feature): bool
    {
        if (!self::enabled()) {
            return false;
        }

        $plan = auth()->user()?->clinic?->plan ?? 'free';
        return self::planCanUseFeature($plan, $feature);
    }

    /**
     * Has today's total spend hit the kill-switch limit?
     */
    public static function dailyLimitReached(): bool
    {
        $limit = (float) config('services.ai.max_daily_cost_usd', 5);
        if ($limit <= 0) return false;

        $todayKey = 'ai_spend:' . now()->format('Y-m-d');
        $spent = (float) Cache::get($todayKey, 0);

        return $spent >= $limit;
    }

    /**
     * Track spend (call from services after each successful API hit).
     */
    public static function trackSpend(float $usdAmount): void
    {
        $todayKey = 'ai_spend:' . now()->format('Y-m-d');
        Cache::increment($todayKey, (int) round($usdAmount * 10000)); // store as hundred-thousandths
    }

    /**
     * Log a single AI call to the database for analytics.
     */
    public static function log(string $feature, int $tokensIn, int $tokensOut, bool $success = true, ?string $errorCode = null): void
    {
        try {
            $model = config('services.ai.' . config('services.ai.provider', 'deepseek') . '.model', 'deepseek-chat');
            $cost = \App\Models\AiUsageLog::calculateCost($model, $tokensIn, $tokensOut);

            \App\Models\AiUsageLog::create([
                'clinic_id' => auth()->user()?->clinic_id,
                'user_id' => auth()->id(),
                'feature' => $feature,
                'provider' => config('services.ai.provider', 'deepseek'),
                'model' => $model,
                'tokens_in' => $tokensIn,
                'tokens_out' => $tokensOut,
                'cost_usd' => $cost,
                'success' => $success,
                'error_code' => $errorCode,
            ]);

            if ($success) {
                self::trackSpend($cost);
            }
        } catch (\Throwable $e) {
            // Never fail the main request due to logging issues
            \Illuminate\Support\Facades\Log::warning('AI log failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get today's spend in USD.
     */
    public static function todaySpendUsd(): float
    {
        $todayKey = 'ai_spend:' . now()->format('Y-m-d');
        return ((int) Cache::get($todayKey, 0)) / 10000;
    }
}
