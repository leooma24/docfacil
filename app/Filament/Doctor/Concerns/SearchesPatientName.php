<?php

namespace App\Filament\Doctor\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Búsqueda por nombre de paciente en los listados del panel doctor.
 *
 * Las columnas muestran "nombre apellido" pero se apoyan en la relación
 * `patient.first_name`, así que Filament solo buscaba en el nombre de pila:
 * teclear el apellido, o el nombre completo, no devolvía nada. Aquí partimos
 * lo que escribió el doctor y exigimos que cada palabra aparezca en el nombre
 * o en el apellido, que es como uno busca a un paciente en la vida real.
 */
trait SearchesPatientName
{
    /**
     * Para pasar a ->searchable(query: ...) en columnas que salen de la
     * relación `patient`.
     */
    public static function buscarPorNombreDePaciente(): Closure
    {
        return static fn (Builder $query, string $search): Builder => $query
            ->whereHas('patient', static function (Builder $paciente) use ($search): void {
                foreach (preg_split('/\s+/', trim($search)) ?: [] as $palabra) {
                    if ($palabra === '') {
                        continue;
                    }

                    $paciente->where(static fn (Builder $q) => $q
                        ->where('first_name', 'like', "%{$palabra}%")
                        ->orWhere('last_name', 'like', "%{$palabra}%"));
                }
            });
    }
}
