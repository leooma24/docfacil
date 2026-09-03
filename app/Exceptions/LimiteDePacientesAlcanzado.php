<?php

namespace App\Exceptions;

use App\Models\Clinic;

/**
 * El consultorio ya llegó al tope de pacientes de su plan.
 *
 * Se lanza desde el modelo Patient, no desde las pantallas: hay siete
 * caminos distintos que crean pacientes (el formulario, el importador, la
 * consulta, la visita sin cita, agendar, el check-in con QR y el portal
 * público) y parcharlos uno por uno deja huecos. Cerrarlo en el modelo es
 * el único lugar por el que pasan todos.
 */
class LimiteDePacientesAlcanzado extends \RuntimeException
{
    public function __construct(public readonly ?Clinic $clinic = null)
    {
        parent::__construct(
            $clinic?->mensajeDeTopeDePacientes()
                ?? 'Llegaste al tope de pacientes de tu plan.'
        );
    }
}
