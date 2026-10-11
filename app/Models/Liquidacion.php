<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Lo que se le pagó a un doctor de comisión en un periodo (ver LiquidacionDelDoctor). */
class Liquidacion extends Model
{
    use BelongsToClinic;

    protected $table = 'liquidaciones';

    protected $fillable = ['clinic_id', 'doctor_id', 'user_id', 'expense_id', 'desde', 'hasta', 'monto'];

    protected function casts(): array
    {
        return ['desde' => 'date', 'hasta' => 'date', 'monto' => 'decimal:2'];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
