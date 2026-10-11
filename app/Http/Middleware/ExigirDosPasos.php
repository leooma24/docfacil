<?php

namespace App\Http\Middleware;

use App\Filament\Doctor\Pages\Security;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si el dueño pidió la verificación en dos pasos a todo el equipo (día 8 del
 * plan, 10-oct-2026), quien no la tenga va a la pantalla de Seguridad a
 * activarla antes de ver pacientes. Solo en cargas de página: las llamadas de
 * Livewire de esa misma pantalla tienen que poder activarla.
 */
class ExigirDosPasos
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->clinic?->exigir_dos_pasos || $usuario->hasTwoFactorEnabled()) {
            return $next($request);
        }

        if (! $request->isMethod('GET') || $request->routeIs('filament.doctor.pages.seguridad', 'filament.doctor.auth.logout')) {
            return $next($request);
        }

        return redirect(Security::getUrl());
    }
}
