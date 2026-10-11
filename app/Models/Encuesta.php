<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** «¿Cómo le fue en su visita?»: estrellas y un comentario que solo ve el consultorio. */
class Encuesta extends Model
{
    use BelongsToClinic;

    protected $fillable = ['clinic_id', 'patient_id', 'appointment_id', 'doctor_id', 'estrellas', 'comentario'];

    protected function casts(): array
    {
        return ['estrellas' => 'integer'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
