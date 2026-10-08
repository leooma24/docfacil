<?php

namespace App\Http\Controllers\Ventas;

use App\Filament\Sales\Resources\ProspectResource;
use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Support\EnvioDeVenta;
use App\Support\SiguientePaso;

/**
 * Los botones del tablero de Omar (cactus-seguimiento). Abren WhatsApp con el mensaje armado; "enviar" además
 * anota el envío en el CRM, igual que la cola del día. Solo el vendedor dueño del prospecto, con su sesión.
 */
class LigaDelTableroController extends Controller
{
    public function enviar(Prospect $prospecto)
    {
        if ($r = $this->permiso($prospecto)) {
            return $r;
        }
        // El mensaje es el del paso en el que va; se arma antes de avanzar la cadencia.
        $liga = ProspectResource::buildContextualWhatsappUrl($prospecto);
        EnvioDeVenta::registrar($prospecto);

        return redirect()->away($liga);
    }

    public function responder(Prospect $prospecto)
    {
        if ($r = $this->permiso($prospecto)) {
            return $r;
        }
        $liga = SiguientePaso::para($prospecto)['mensaje'] ?? null;

        $tel = preg_replace('/\D/', '', (string) $prospecto->phone);

        return redirect()->away($liga ?: 'https://wa.me/'.(strlen($tel) === 10 ? '52'.$tel : $tel));
    }

    private function permiso(Prospect $prospecto)
    {
        if (! auth()->check()) {
            return redirect('/ventas/login');
        }
        abort_unless((int) $prospecto->assigned_to_sales_rep_id === (int) auth()->id(), 403);

        return null;
    }
}
