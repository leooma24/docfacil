<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un mensaje de venta que se mandó: qué paso de la cadencia y qué versión. */
class ProspectMensaje extends Model
{
    protected $table = 'prospect_mensajes';

    protected $fillable = ['prospect_id', 'user_id', 'paso', 'version', 'canal', 'estimado', 'enviado_at'];

    protected function casts(): array
    {
        return ['paso' => 'integer', 'estimado' => 'boolean', 'enviado_at' => 'datetime'];
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }
}
