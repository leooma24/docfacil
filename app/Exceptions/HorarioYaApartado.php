<?php

namespace App\Exceptions;

/**
 * Alguien más se adelantó y apartó ese horario.
 *
 * Existe para distinguir la carrera de dos pacientes reservando al mismo
 * tiempo de cualquier otro error: eso no es una falla, es una condición
 * normal que hay que contarle al paciente con calma.
 */
class HorarioYaApartado extends \RuntimeException
{
}
