<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/whatsapp',
            'billing/stripe/webhook',
        ]);

        // Security headers en TODAS las respuestas (X-Frame-Options,
        // X-Content-Type-Options, Referrer-Policy, HSTS en prod, etc.)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // A dónde mandar a un visitante NO autenticado.
        //
        // Laravel por defecto busca una ruta llamada 'login', que aquí no
        // existe: cada panel Filament tiene la suya (/doctor/login,
        // /admin/login, ...). Sin esto, cualquier ruta protegida fuera de
        // un panel (ej. /api/cie10/search, /billing/spei-receipts/{id})
        // reventaba con 500 "Route [login] not defined" en vez de redirigir.
        //
        // Elegimos el login según el prefijo de la URL pedida; el panel
        // doctor es el default porque es el producto principal.
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return null; // deja que responda 401 en vez de redirigir
            }

            return match (true) {
                $request->is('admin', 'admin/*') => '/admin/login',
                $request->is('paciente', 'paciente/*') => '/paciente/login',
                $request->is('ventas', 'ventas/*') => '/ventas/login',
                default => '/doctor/login',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
