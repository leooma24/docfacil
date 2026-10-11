<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Cómo quedó una llamada de seguimiento (ver App\Support\Seguimiento). */
class Seguimiento extends Model
{
    use BelongsToClinic;

    protected $fillable = ['clinic_id', 'patient_id', 'user_id', 'motivo', 'resultado', 'volver_el'];

    protected function casts(): array
    {
        return ['volver_el' => 'date'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
