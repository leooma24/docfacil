<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\ProspectEmailEvent;
use App\Support\ProspectTrackingToken;
use Illuminate\Http\Request;

/**
 * Endpoint de tracking de clicks en correos del pipeline de prospects.
 *
 * El flujo: prospect da clic en CTA -> /t/c/{token} -> registramos
 * evento -> pre-llenamos URL de registro con sus datos -> redirect 302.
 *
 * El token incluye prospect_id + email_type + destination_url firmados
 * con HMAC-SHA256. Validacion estricta: si el token es invalido o el
 * destino apunta a un host externo (anti open redirect), respondemos 403.
 *
 * UX boost: si el destino es la pagina de registro, le anexamos los
 * datos del prospect (name, email, clinic_name, phone, city, specialty)
 * para que el form llegue pre-llenado. Cero friccion: el dentista solo
 * pone su contraseña + cedula y se registra.
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
            \Illuminate\Support\Facades\Log::warning('TrackController click log failed', [
                'token_prefix' => substr($token, 0, 12),
                'error' => $e->getMessage(),
            ]);
        }

        // Pre-llenar el destino con los datos del prospect si vamos a /doctor/register.
        // Los campos del Filament Register page ya leen estos query params como default.
        $destination = $this->prefillRegistrationUrl($destination, $prospectId);

        return redirect()->away($destination, 302);
    }

    private function prefillRegistrationUrl(string $url, int $prospectId): string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        if (!str_contains($path, '/doctor/register')) {
            return $url;
        }

        $prospect = Prospect::find($prospectId);
        if (!$prospect) return $url;

        parse_str($parsed['query'] ?? '', $params);

        // Solo agregar si no estan ya en el URL (no sobreescribir)
        $params['name'] = $params['name'] ?? ($prospect->name ?: '');
        $params['email'] = $params['email'] ?? ($prospect->email ?: '');
        if ($prospect->clinic_name) $params['clinic_name'] = $params['clinic_name'] ?? $prospect->clinic_name;
        if ($prospect->phone) $params['phone'] = $params['phone'] ?? $prospect->phone;
        if ($prospect->city) $params['city'] = $params['city'] ?? $prospect->city;
        if ($prospect->specialty) $params['specialty'] = $params['specialty'] ?? $prospect->specialty;

        $newQuery = http_build_query(array_filter($params, fn ($v) => $v !== '' && $v !== null));
        $base = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . $path;
        return $base . '?' . $newQuery;
    }
}
