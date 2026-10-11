<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * El cierre de la caja de un día: lo que debía haber en efectivo, lo que se
 * contó en el cajón y la diferencia, con quién cerró y a qué hora. Si se
 * cierra dos veces el mismo día, vale el último.
 */
class CorteDeCaja extends Model
{
    use BelongsToClinic;

    protected $table = 'cortes_de_caja';

    protected $fillable = ['clinic_id', 'user_id', 'fecha', 'esperado', 'contado', 'diferencia', 'notas'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'esperado' => 'decimal:2',
            'contado' => 'decimal:2',
            'diferencia' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
