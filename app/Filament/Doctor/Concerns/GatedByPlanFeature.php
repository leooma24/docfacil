<?php

namespace App\Filament\Doctor\Concerns;

/**
 * Recursos/pages del panel Doctor que solo están disponibles a ciertos planes
 * usan este trait + definen `planFeature()`. Oculta del menú + bloquea acceso
 * directo por URL cuando el plan actual no tiene el feature.
 *
 * La fuente de verdad de qué feature pertenece a qué plan es
 * `App\Models\Clinic::featuresForPlan()`. Si cambia ahí, el gate se ajusta solo.
 */
trait GatedByPlanFeature
{
    /**
     * Nombre del feature según Clinic::featuresForPlan(). El recurso debe
     * implementarlo. Ejemplo: return 'odontogram';
     */
    abstract protected static function planFeature(): string;

    protected static function clinicHasPlanFeature(): bool
    {
        $user = auth()->user();
        if (!$user || !$user->clinic) {
            return false;
        }
        return $user->clinic->hasFeature(static::planFeature());
    }
}
