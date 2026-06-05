<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica headers de seguridad estándar en TODAS las respuestas HTML.
 *
 * - X-Frame-Options: previene clickjacking (sitio NO embebible en iframes)
 * - X-Content-Type-Options: previene MIME-sniffing
 * - Referrer-Policy: limita info enviada en navegación cross-origin
 * - Strict-Transport-Security: fuerza HTTPS por 1 año (solo en prod)
 * - Permissions-Policy: deshabilita APIs que no usamos (camera/mic son
 *   excepción — el dictado por voz las requiere)
 *
 * NO incluye CSP — su rollout requiere análisis previo (Vite assets,
 * Tailwind inline styles, AlpineJS eval). Mejor agregar en sprint
 * dedicado para evitar romper la UI.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // microphone permitido (dictado por voz en consulta)
        // camera permitido (firma digital con cámara)
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), payment=(), usb=(), interest-cohort=()'
        );

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
