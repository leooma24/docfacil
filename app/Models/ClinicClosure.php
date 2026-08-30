<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un periodo en que el consultorio cierra aunque sea su horario normal:
 * vacaciones, un día feriado, un congreso.
 */
class ClinicClosure extends Model
{
    use BelongsToClinic;

    protected $fillable = ['clinic_id', 'starts_on', 'ends_on', 'reason'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /** Cómo se lee el periodo: un día suelto o un rango. */
    public function periodo(): string
    {
        if ($this->starts_on->isSameDay($this->ends_on)) {
            return $this->starts_on->translatedFormat('d \d\e F');
        }

        return $this->starts_on->translatedFormat('d \d\e F')
            . ' al ' . $this->ends_on->translatedFormat('d \d\e F');
    }
}
