<?php

namespace App\Support;

use App\Models\Prospect;

/**
 * Anotar que se mandó el WhatsApp de la cadencia: lo mismo desde la cola del día que desde el tablero de Omar.
 * Un segundo clic en menos de 5 minutos no cuenta dos veces.
 */
class EnvioDeVenta
{
    /** @return bool false si ya se había anotado hace menos de 5 minutos */
    public static function registrar(Prospect $prospecto): bool
    {
        if ($prospecto->last_followup_at && $prospecto->last_followup_at->isAfter(now()->subMinutes(5))) {
            return false;
        }

        if ($prospecto->status === 'new') {
            $prospecto->update([
                'status' => 'contacted',
                'contacted_at' => $prospecto->contacted_at ?? now(),
            ]);
        }

        $prospecto->advanceContactDay('whatsapp');

        return true;
    }
}
