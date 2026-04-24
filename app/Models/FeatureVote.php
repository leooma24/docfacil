<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureVote extends Model
{
    protected $fillable = [
        'feature_request_id', 'clinic_id', 'user_id',
        'willingness_to_pay',
    ];

    public function featureRequest(): BelongsTo
    {
        return $this->belongsTo(FeatureRequest::class);
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
     * Mantiene el cache votes_count en feature_requests sincronizado.
     */
    protected static function booted(): void
    {
        static::created(function (FeatureVote $vote) {
            FeatureRequest::where('id', $vote->feature_request_id)->increment('votes_count');
        });

        static::deleted(function (FeatureVote $vote) {
            FeatureRequest::where('id', $vote->feature_request_id)->decrement('votes_count');
        });
    }
}
