<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Se intentó borrar a un paciente que ya tiene expediente clínico.
 *
 * Las llaves foráneas de la base borran en cascada todo lo del paciente:
 * notas, recetas, consentimientos, odontogramas y planes. La NOM-004 (5.4)
 * pide conservar el expediente al menos 5 años desde el último acto médico,
 * y la NOM-013 (5.14) lo mismo en dental.
 */
class ExpedienteQueSeConserva extends RuntimeException
{
    public function __construct(public readonly int $pacienteId)
    {
        parent::__construct(
            'Este paciente tiene expediente clínico y no se puede borrar: '
            . 'la NOM-004 pide conservarlo al menos 5 años.'
        );
    }
}
