<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentForm extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'patient_id', 'doctor_id',
        'title', 'content', 'procedure_name',
        'risks', 'alternatives', 'signature',
        'signed_at', 'signed_ip',
        'testigo_nombre', 'testigo_firma',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    /**
     * Un consentimiento firmado ya no se toca.
     *
     * Antes se podía cambiar el texto después de que el paciente firmó, o
     * borrarlo: lo que el paciente autorizó dejaba de existir tal como lo
     * firmó (NOM-004 5.11 y 10.1.1; NOM-013 9.6). Si cambia el procedimiento
     * o el plan de tratamiento, va una carta nueva (NOM-013 9.6.2).
     */
    protected static function booted(): void
    {
        static::saving(function (self $consentimiento) {
            // La firma dibujada es la firma: la fecha y la hora se guardan en
            // ese momento, sin depender de que alguien le dé "Marcar firmado".
            if (filled($consentimiento->signature) && $consentimiento->signed_at === null) {
                $consentimiento->signed_at = now();
                $consentimiento->signed_ip = request()?->ip();
            }
        });

        static::updating(function (self $consentimiento) {
            if ($consentimiento->getOriginal('signed_at') === null) {
                return;
            }

            // Lo único que se puede agregar después es el testigo, que firma
            // después del paciente, y solo una vez.
            $cambios = array_diff(
                array_keys($consentimiento->getDirty()),
                ['testigo_nombre', 'testigo_firma', 'updated_at'],
            );

            if ($cambios !== [] || filled($consentimiento->getOriginal('testigo_firma'))) {
                throw new \LogicException(
                    'Este consentimiento ya está firmado y no se puede modificar. '
                    . 'Si cambió el procedimiento, crea uno nuevo.'
                );
            }
        });

        static::deleting(function (self $consentimiento) {
            if ($consentimiento->signed_at !== null) {
                throw new \LogicException(
                    'Este consentimiento ya está firmado y se conserva con el expediente (NOM-004).'
                );
            }
        });
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

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }
}
