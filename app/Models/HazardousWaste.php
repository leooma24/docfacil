<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un residuo que la ley obliga a manejar aparte.
 *
 * Del lado del inventario esto NO existe: el material ya salió del kardex
 * cuando se mezcló, así que no genera ningún movimiento. Vive en su propia
 * tabla justo para que la suma del stock no tenga que saber excluirlo — una
 * resta que se olvida una vez deja el inventario desviado para siempre.
 *
 * Lo que sí hace es dejar la trazabilidad que exige la norma: qué residuo, de
 * qué consulta, en qué contenedor y con qué número de registro salió.
 */
class HazardousWaste extends Model
{
    use BelongsToClinic;

    /** Los materiales que la norma obliga a separar. */
    public const MATERIALS = [
        'leftover_amalgam' => 'Sobrante de amalgama',
        'extracted_amalgam' => 'Amalgama extraída del paciente',
        'mercury' => 'Mercurio',
        'biological' => 'Residuo biológico (RPBI)',
        'other' => 'Otro',
    ];

    protected $fillable = [
        'clinic_id', 'appointment_id', 'supply_id', 'user_id',
        'material', 'quantity', 'unit', 'disposed_on',
        'container', 'manifest_number', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'disposed_on' => 'date',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function materialLabel(): string
    {
        return self::MATERIALS[$this->material] ?? $this->material;
    }

    /** ¿Le falta el número de registro con el que salió del consultorio? */
    public function isMissingManifest(): bool
    {
        return blank($this->manifest_number);
    }
}
