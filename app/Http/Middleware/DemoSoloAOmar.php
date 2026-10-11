<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En la demo, todo WhatsApp va al número de Omar (11-oct-2026).
 *
 * Los pacientes de ejemplo tienen teléfonos que parecen de verdad y podrían
 * ser de alguien. En vez de cuidar cada uno de los botones que arman una liga
 * de WhatsApp, aquí se cambia el número en lo que sale hacia el navegador:
 * páginas, respuestas de Livewire y redirecciones. Los recordatorios
 * automáticos lo hacen en su comando (SendAppointmentReminders).
 */
class DemoSoloAOmar
{
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        if (! auth()->user()?->clinic?->is_demo) {
            return $respuesta;
        }

        $omar = self::numero();

        if ($respuesta instanceof RedirectResponse) {
            $respuesta->setTargetUrl(self::cambia($respuesta->getTargetUrl(), $omar));

            return $respuesta;
        }

        $tipo = (string) $respuesta->headers->get('Content-Type');
        if (str_contains($tipo, 'text/html') || str_contains($tipo, 'application/json')) {
            $contenido = $respuesta->getContent();
            if (is_string($contenido) && str_contains($contenido, 'wa.me')) {
                $respuesta->setContent(self::cambia($contenido, $omar));
            }
        }

        return $respuesta;
    }

    /** 52 + los 10 dígitos de Omar. */
    public static function numero(): string
    {
        $n = preg_replace('/\D/', '', (string) config('services.notifications.phone', '6682493398'));

        return strlen($n) === 10 ? '52' . $n : $n;
    }

    /** wa.me/52XXXXXXXXXX (también escrito wa.me\/ en JSON) → wa.me/<Omar>. */
    public static function cambia(string $texto, string $omar): string
    {
        return preg_replace('#wa\.me(\\\\?/)\d{8,15}#', 'wa.me${1}' . $omar, $texto);
    }
}
