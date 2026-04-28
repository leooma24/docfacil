<?php

namespace App\Support;

/**
 * Helper para firmar/decodificar tokens de tracking de clicks en correos
 * de prospects. Usa HMAC-SHA256 con APP_KEY como secreto.
 *
 * Format: base64url(prospect_id|email_type|destination_b64).hmac
 *
 * El prospect_id va en el payload (no como secret — el HMAC valida que
 * nadie pueda forjar uno). El destino tambien va dentro del payload para
 * evitar open redirects.
 */
class ProspectTrackingToken
{
    /**
     * Genera un token firmado para un prospect + tipo de email + destino.
     */
    public static function make(int $prospectId, string $emailType, string $destinationUrl): string
    {
        $payload = $prospectId . '|' . $emailType . '|' . self::b64encode($destinationUrl);
        $signature = self::hmac($payload);
        return self::b64encode($payload) . '.' . $signature;
    }

    /**
     * Decodifica un token firmado. Retorna [prospect_id, email_type, destination_url]
     * o null si el token es invalido/manipulado.
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

        $bits = explode('|', $payload, 3);
        if (count($bits) !== 3) return null;

        [$prospectId, $emailType, $destB64] = $bits;
        $destination = self::b64decode($destB64);
        if ($destination === null) return null;

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
