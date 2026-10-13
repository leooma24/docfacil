<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lo que un paciente le escribió al número de DocFácil (12-oct-2026). Sin
 * BelongsToClinic a propósito: llega por el webhook, sin nadie con sesión,
 * y puede no ser de ningún consultorio.
 */
class MensajeDePaciente extends Model
{
    protected $table = 'mensajes_de_pacientes';

    protected $fillable = ['clinic_id', 'patient_id', 'appointment_id', 'telefono', 'texto'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
