<?php

namespace App\Support;

/**
 * Helper para firmar/decodificar tokens de tracking de clicks en correos
 * de prospects. Usa HMAC-SHA256 con APP_KEY como secreto.
 *
 * Format: base64url(prospect_id|email_type|destination_b64|issued_at).hmac
 *
 * El prospect_id va en el payload (no como secret — el HMAC valida que
 * nadie pueda forjar uno). El destino tambien va dentro del payload para
 * evitar open redirects. issued_at es timestamp Unix para validar TTL.
 */
class ProspectTrackingToken
{
    /**
     * Tokens expiran 60 dias despues de generados. Cubre la cadencia de 3
     * correos (max 6 dias entre 1ro y ultimo) + ~2 meses de gracia para que
     * un prospect que haya tomado vacaciones pueda volver al inbox y dar
     * clic. Despues de 60 dias el link genera 403.
     */
    public const TOKEN_TTL_DAYS = 60;

    /**
     * Genera un token firmado para un prospect + tipo de email + destino.
     * Incluye issued_at para validar caducidad en verify().
     */
    public static function make(int $prospectId, string $emailType, string $destinationUrl): string
    {
        $payload = $prospectId . '|' . $emailType . '|' . self::b64encode($destinationUrl) . '|' . time();
        $signature = self::hmac($payload);
        return self::b64encode($payload) . '.' . $signature;
    }

    /**
     * Decodifica un token firmado. Retorna [prospect_id, email_type, destination_url]
     * o null si el token es invalido/manipulado/expirado.
     */
    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) return null;

        [$payloadB64, $signature] = $parts;
        $payload = self::b64decode($payloadB64);
        if ($payload === null) return null;

        $expected = self::hmac($payload);
        if (!hash_equals($expected, $signature)) return null;

        $bits = explode('|', $payload, 4);
        // Compatibilidad con tokens v1 sin timestamp (3 campos): los aceptamos
        // sin chequeo de TTL para no romper links viejos en transicion. Tokens
        // v2 (4 campos) tienen TTL forzado.
        if (count($bits) === 3) {
            $bits[] = (string) time(); // sin caducidad para v1
        }
        if (count($bits) !== 4) return null;

        [$prospectId, $emailType, $destB64, $issuedAt] = $bits;
        $destination = self::b64decode($destB64);
        if ($destination === null) return null;

        // Validar TTL: token expira 60 dias despues de generado
        $issuedAtTs = (int) $issuedAt;
        if ($issuedAtTs > 0 && (time() - $issuedAtTs) > (self::TOKEN_TTL_DAYS * 86400)) {
            return null; // expirado
        }

        // Solo permitir destinos a nuestro propio dominio (anti open redirect)
        $allowedHost = parse_url(config('app.url'), PHP_URL_HOST);
        $destHost = parse_url($destination, PHP_URL_HOST);
        if ($destHost !== $allowedHost && $destHost !== null) return null;

        return [(int) $prospectId, $emailType, $destination];
    }

    private static function hmac(string $payload): string
    {
        return self::b64encode(hash_hmac('sha256', $payload, config('app.key'), true));
    }

    private static function b64encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function b64decode(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        return $decoded === false ? null : $decoded;
    }
}
