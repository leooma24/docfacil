<?php

namespace App\Http\Controllers;

use App\Models\ProspectEmailEvent;
use App\Support\ProspectTrackingToken;
use Illuminate\Http\Request;

/**
 * Endpoint de tracking de clicks en correos del pipeline de prospects.
 *
 * El flujo: prospect da clic en CTA -> /t/c/{token} -> registramos
 * evento -> redirect 302 a destino real.
 *
 * El token incluye prospect_id + email_type + destination_url firmados
 * con HMAC-SHA256. Validacion estricta: si el token es invalido o el
 * destino apunta a un host externo (anti open redirect), respondemos 403.
 */
class TrackController extends Controller
{
    public function click(Request $request, string $token)
    {
        $verified = ProspectTrackingToken::verify($token);

        if ($verified === null) {
            abort(403, 'Token inválido');
        }

        [$prospectId, $emailType, $destination] = $verified;

        try {
            ProspectEmailEvent::create([
                'prospect_id' => $prospectId,
                'email_type' => $emailType,
                'event_type' => 'click',
                'destination_url' => $destination,
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            // Aun si falla el log, seguimos al destino — no queremos romper UX
            \Illuminate\Support\Facades\Log::warning('TrackController click log failed', [
                'token_prefix' => substr($token, 0, 12),
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->away($destination, 302);
    }
}
