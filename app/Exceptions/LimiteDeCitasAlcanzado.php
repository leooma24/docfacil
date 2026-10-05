<?php

namespace App\Exceptions;

use App\Models\Clinic;

/**
 * El consultorio ya agendó las citas del mes que incluye su plan (Free: 10).
 *
 * Igual que el tope de pacientes, se cierra en el modelo Appointment: las
 * citas nacen en muchos lados (el formulario, el calendario, la consulta sin
 * cita, el presupuesto, la lista de espera, la agenda pública).
 */
class LimiteDeCitasAlcanzado extends \RuntimeException
{
    public function __construct(public readonly ?Clinic $clinic = null)
    {
        parent::__construct(
            $clinic?->mensajeDeTopeDeCitas()
                ?? 'Llegaste al tope de citas del mes de tu plan.'
        );
    }
}
