<?php

namespace App\Support;

/**
 * Helper para firmar/verificar tokens de baja (unsubscribe) de la lista
 * de correos del pipeline de prospects. HMAC-SHA256 con APP_KEY.
 *
 * Format: base64url(prospect_id|issued_at).hmac
 *
 * No expira para que un prospect pueda dar de baja en cualquier momento,
 * incluso si el correo es viejo. El HMAC valida que nadie pueda forjar
 * un token para desuscribir a otros prospects.
 */
class ProspectUnsubscribeToken
{
    public static function make(int $prospectId): string
    {
        $payload = $prospectId . '|' . time();
        $signature = self::hmac($payload);
        return self::b64encode($payload) . '.' . $signature;
    }

    /**
     * Retorna prospect_id si el token es valido, null si fue manipulado.
     */
    public static function verify(string $token): ?int
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) return null;

        [$payloadB64, $signature] = $parts;
        $payload = self::b64decode($payloadB64);
        if ($payload === null) return null;

        $expected = self::hmac($payload);
        if (!hash_equals($expected, $signature)) return null;

        $bits = explode('|', $payload, 2);
        if (count($bits) < 1) return null;

        $prospectId = (int) $bits[0];
        return $prospectId > 0 ? $prospectId : null;
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
