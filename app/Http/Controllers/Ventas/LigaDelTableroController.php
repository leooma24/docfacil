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
    /** Abre WhatsApp con el mensaje del paso en el que va. No anota nada: eso lo hace "Sí, lo envié". */
    public function enviar(Prospect $prospecto)
    {
        if ($r = $this->permiso($prospecto)) {
            return $r;
        }

        return redirect()->away(ProspectResource::buildContextualWhatsappUrl($prospecto));
    }

    /** "Sí, lo envié": anota el envío en el CRM, igual que la cola del día, y lo confirma. */
    public function registrar(Prospect $prospecto)
    {
        if ($r = $this->permiso($prospecto)) {
            return $r;
        }
        $nuevo = EnvioDeVenta::registrar($prospecto);

        return response()->view('ventas.anotado', ['prospecto' => $prospecto->fresh(), 'nuevo' => $nuevo]);
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
