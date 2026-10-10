<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Odontogram extends Model
{
    use BelongsToClinic, LogsActivity;

    /** Bitácora (NOM-024, trazabilidad): quién creó o cambió Odontograma. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['notes', 'evaluation_date', 'patient_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $evento) => "Odontograma {$evento}");
    }
    protected $fillable = [
        'clinic_id', 'patient_id', 'doctor_id',
        'evaluation_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_date' => 'date',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function teeth(): HasMany
    {
        return $this->hasMany(OdontogramTooth::class)->orderBy('tooth_number');
    }
}
