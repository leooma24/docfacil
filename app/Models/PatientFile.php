<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un archivo del paciente (foto de su hoja vieja, radiografía, estudio), en el disco privado. */
class PatientFile extends Model
{
    use BelongsToClinic;

    protected $fillable = ['clinic_id', 'patient_id', 'path', 'nombre', 'mime', 'size', 'nota', 'subido_por'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function esImagen(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }
}
