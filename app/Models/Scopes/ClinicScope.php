<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope que filtra todos los queries por clinic_id del usuario autenticado.
 *
 * Cumple con LFPDPPP art. 19 (separación de datos entre responsables) y
 * NOM-004 punto 5.5 (confidencialidad del expediente). Evita que un bug
 * en Filament/controller exponga pacientes de otra clínica.
 *
 * Se aplica en modelos que tienen una columna `clinic_id`. Para saltarlo
 * intencionalmente (jobs, comandos, admin global), usar:
 *   Model::withoutGlobalScope(ClinicScope::class)->...
 */
class ClinicScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Sólo aplica cuando hay usuario autenticado con clinic_id.
        // Jobs en cola, seeders, comandos y acceso de admins sin clinic quedan fuera
        // y deben declarar explícitamente withoutGlobalScope para cualquier escritura.
        $user = auth()->user();

        if ($user && $user->clinic_id) {
            $builder->where($model->getTable() . '.clinic_id', $user->clinic_id);
        }
    }
}
