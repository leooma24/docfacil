<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Support\ProspectUnsubscribeToken;
use Illuminate\Http\Request;

/**
 * One-click unsubscribe para correos del pipeline de prospects.
 *
 * Cumple con LFPDPPP (art. 16) y con buenas practicas anti-spam: el link
 * es de 1 clic, no requiere login, y bloquea futuros envios al setear
 * unsubscribed_at + status='lost'. SendProspectEmails respeta ese flag.
 */
class UnsubscribeController extends Controller
{
    public function handle(Request $request, string $token)
    {
        $prospectId = ProspectUnsubscribeToken::verify($token);

        if ($prospectId === null) {
            // Mostramos confirmacion generica (no revelamos si el token es valido o no
            // para evitar enumeracion de IDs).
            return response()->view('emails.unsubscribed', [
                'success' => false,
            ], 200);
        }

        $prospect = Prospect::find($prospectId);
        if ($prospect && $prospect->unsubscribed_at === null) {
            $prospect->update([
                'unsubscribed_at' => now(),
                'status' => 'lost',
                'next_contact_at' => null,
                'notes' => trim(($prospect->notes ? $prospect->notes . "\n" : '') .
                    '[' . now()->format('d/m H:i') . '] Baja por link de unsubscribe en correo.'),
            ]);
        }

        return response()->view('emails.unsubscribed', [
            'success' => true,
            'email' => $prospect?->email,
        ], 200);
    }
}
