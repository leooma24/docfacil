<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoMode
{
    private const DEMO_EMAIL = 'demo@docfacil.com';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->email !== self::DEMO_EMAIL) {
            return $next($request);
        }

        // Block all write operations for demo account
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Allow Livewire requests (needed for navigation/filtering)
            if ($request->is('livewire/*')) {
                // Block only Livewire calls that modify data
                $payload = $request->input('components.0.calls.0.method', '');
                $blockMethods = ['create', 'save', 'delete', 'forceDelete', 'restore'];
                if (in_array($payload, $blockMethods)) {
                    return response()->json(['effects' => ['html' => ''], 'serverMemo' => []], 200);
                }
                return $next($request);
            }

            // Allow logout
            if ($request->routeIs('*.auth.logout')) {
                return $next($request);
            }

            // Block everything else
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'La cuenta demo es solo lectura. Crea tu cuenta gratis para usar todas las funciones.',
                ], 403);
            }

            return back()->with('demo_readonly', true);
        }

        return $next($request);
    }
}
