<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoMode
{
    private const DEMO_EMAIL = 'demo@docfacil.com';

    // Livewire methods that are safe (read-only navigation/filtering)
    private const ALLOWED_LIVEWIRE_METHODS = [
        'gotoPage', 'previousPage', 'nextPage', 'sortTable',
        'tableSearch', 'resetTableSearch', 'toggleTableReorder',
        'mountTableAction', 'unmountTableAction',
        'mountAction', 'unmountAction',
        'callMountedAction', 'callMountedTableAction',
        'goToStep', 'prevStep', 'nextStep',
        'setTab', 'setTool', 'selectTooth', 'applyTool',
        'toggleChat', 'askFaq',
        '$refresh', '$set',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->email !== self::DEMO_EMAIL) {
            return $next($request);
        }

        // Allow GET requests (read-only)
        if ($request->isMethod('GET')) {
            return $next($request);
        }

        // Allow logout
        if ($request->routeIs('*.auth.logout')) {
            return $next($request);
        }

        // Livewire requests - allowlist approach
        if ($request->is('livewire/*')) {
            $components = $request->input('components', []);

            foreach ($components as $component) {
                $calls = $component['calls'] ?? [];
                foreach ($calls as $call) {
                    $method = $call['method'] ?? '';

                    // Allow safe methods
                    if (in_array($method, self::ALLOWED_LIVEWIRE_METHODS)) {
                        continue;
                    }

                    // Allow methods starting with $ (Livewire internals)
                    if (str_starts_with($method, '$')) {
                        continue;
                    }

                    // Allow 'updated' hooks (form field changes for display only)
                    if (str_starts_with($method, 'updated')) {
                        continue;
                    }

                    // Block everything else (save, create, delete, saveAndComplete, etc.)
                    return response()->json([
                        'effects' => ['html' => ''],
                        'serverMemo' => [],
                    ], 200);
                }
            }

            return $next($request);
        }

        // Block all other POST/PUT/PATCH/DELETE
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'La cuenta demo es solo lectura. Crea tu cuenta gratis para usar todas las funciones.',
            ], 403);
        }

        return back()->with('demo_readonly', true);
    }
}
