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

            // Recalcular score en tiempo real — el click es señal de intent caliente,
            // queremos que Omar lo vea HOY en su panel sin esperar al cron de las 2:30am.
            $prospectForScore = Prospect::find($prospectId);
            if ($prospectForScore) {
                $newScore = app(\App\Services\LeadScoringService::class)->calculate($prospectForScore);
                $prospectForScore->updateQuietly(['lead_score' => $newScore]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('TrackController click log failed', [
                'token_prefix' => substr($token, 0, 12),
                'error' => $e->getMessage(),
            ]);
        }

        // Cargar el prospect una vez (lo usamos para pre-llenar + atribuir
        // sales_rep + marcar email como verificado en sesion).
        $prospect = Prospect::with('assignedSalesRep')->find($prospectId);

        if ($prospect && $prospect->email) {
            // Email verification skip: el prospect demostro acceso a su inbox al
            // dar clic en el link. Marcamos la sesion para que Register.php
            // setee email_verified_at=now() si registra con el mismo email.
            // El match por email previene que cualquier visitante de la session
            // se aproveche del flag.
            $request->session()->put('prospect_email_verified', [
                'prospect_id' => $prospect->id,
                'email' => $prospect->email,
                'verified_at' => now()->toIso8601String(),
            ]);
        }

        // Pre-llenar el destino con los datos del prospect si vamos a /doctor/register.
        // Los campos del Filament Register page ya leen estos query params como default.
        $destination = $this->prefillRegistrationUrl($destination, $prospect);

        return redirect()->away($destination, 302);
    }

    private function prefillRegistrationUrl(string $url, ?Prospect $prospect): string
    {
        if (!$prospect) return $url;

        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        if (!str_contains($path, '/doctor/register')) {
            return $url;
        }

        parse_str($parsed['query'] ?? '', $params);

        // Solo agregar si no estan ya en el URL (no sobreescribir)
        $params['name'] = $params['name'] ?? ($prospect->name ?: '');
        $params['email'] = $params['email'] ?? ($prospect->email ?: '');
        if ($prospect->clinic_name) $params['clinic_name'] = $params['clinic_name'] ?? $prospect->clinic_name;
        if ($prospect->phone) $params['phone'] = $params['phone'] ?? $prospect->phone;
        if ($prospect->city) $params['city'] = $params['city'] ?? $prospect->city;
        if ($prospect->specialty) $params['specialty'] = $params['specialty'] ?? $prospect->specialty;

        // Atribucion al sales rep asignado para tracking de comision (si tiene
        // codigo de vendedor activo). Register.php detecta ?vnd= y asocia la
        // venta a ese rep para Commission::generateForSale.
        if (!isset($params['vnd']) && $prospect->assignedSalesRep
            && $prospect->assignedSalesRep->is_active_sales_rep
            && $prospect->assignedSalesRep->sales_rep_code) {
            $params['vnd'] = $prospect->assignedSalesRep->sales_rep_code;
        }

        $newQuery = http_build_query(array_filter($params, fn ($v) => $v !== '' && $v !== null));
        $base = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . $path;
        return $base . '?' . $newQuery;
    }
}
