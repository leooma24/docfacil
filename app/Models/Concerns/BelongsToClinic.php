<?php

namespace App\Models\Concerns;

use App\Models\Scopes\ClinicScope;

/**
 * Trait que aplica ClinicScope a los modelos que tienen clinic_id.
 * Cumple con LFPDPPP (datos sensibles segregados por responsable).
 */
trait BelongsToClinic
{
    protected static function bootBelongsToClinic(): void
    {
        static::addGlobalScope(new ClinicScope);

        // Auto-fill clinic_id al crear si no viene en el payload
        static::creating(function ($model) {
            if (empty($model->clinic_id) && auth()->check() && auth()->user()->clinic_id) {
                $model->clinic_id = auth()->user()->clinic_id;
            }
        });
    }
}
