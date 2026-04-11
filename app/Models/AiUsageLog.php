<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'clinic_id', 'user_id', 'feature', 'provider', 'model',
        'tokens_in', 'tokens_out', 'cost_usd', 'success', 'error_code',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'cost_usd' => 'decimal:6',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pricing per model per 1M tokens (USD). Update if provider changes prices.
     */
    public const PRICING = [
        'deepseek-chat' => ['in' => 0.27, 'out' => 1.10],
        'gpt-4o-mini' => ['in' => 0.15, 'out' => 0.60],
        'claude-haiku-4-5-20251001' => ['in' => 1.00, 'out' => 5.00],
    ];

    /**
     * Calculate cost from token counts.
     */
    public static function calculateCost(string $model, int $tokensIn, int $tokensOut): float
    {
        $rates = self::PRICING[$model] ?? self::PRICING['deepseek-chat'];
        return round(($tokensIn * $rates['in'] + $tokensOut * $rates['out']) / 1_000_000, 6);
    }
}
