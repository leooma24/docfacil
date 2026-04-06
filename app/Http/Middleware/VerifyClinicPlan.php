<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyClinicPlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->clinic_id) {
            return $next($request);
        }

        $clinic = $user->clinic;

        if (!$clinic || !$clinic->is_active) {
            abort(403, 'Tu consultorio ha sido desactivado. Contacta soporte.');
        }

        // Block expired trials
        if ($clinic->plan === 'free' && $clinic->trial_ends_at && $clinic->trial_ends_at->isPast()) {
            if ($request->routeIs('*.create', '*.edit', '*.store', '*.update')) {
                abort(403, 'Tu prueba gratuita ha expirado. Actualiza tu plan para continuar usando DocFácil.');
            }
        }

        // Plan limits - BLOCK, not warn
        $limits = $this->getPlanLimits($clinic->plan);

        if ($limits) {
            // Patient limit
            if ($limits['patients'] && $request->routeIs('*.patients.create')) {
                $patientCount = $clinic->patients()->count();
                if ($patientCount >= $limits['patients']) {
                    abort(403, "Has alcanzado el límite de {$limits['patients']} pacientes en tu plan. Actualiza para continuar.");
                }
            }

            // Appointment limit
            if ($limits['appointments'] && $request->routeIs('*.appointments.create')) {
                $monthlyAppointments = $clinic->appointments()
                    ->whereMonth('starts_at', now()->month)
                    ->whereYear('starts_at', now()->year)
                    ->count();
                if ($monthlyAppointments >= $limits['appointments']) {
                    abort(403, "Has alcanzado el límite de {$limits['appointments']} citas/mes en tu plan. Actualiza para continuar.");
                }
            }

            // Doctor limit
            if ($limits['doctors'] && $request->routeIs('*.doctor-invitations.create')) {
                $doctorCount = $clinic->doctors()->count();
                if ($doctorCount >= $limits['doctors']) {
                    abort(403, "Tu plan permite máximo {$limits['doctors']} doctor(es). Actualiza para invitar más.");
                }
            }
        }

        return $next($request);
    }

    private function getPlanLimits(string $plan): ?array
    {
        return match ($plan) {
            'free' => ['doctors' => 1, 'patients' => 30, 'appointments' => 20],
            'basico' => ['doctors' => 1, 'patients' => 200, 'appointments' => null],
            'profesional' => ['doctors' => 3, 'patients' => null, 'appointments' => null],
            'clinica' => null,
            default => ['doctors' => 1, 'patients' => 30, 'appointments' => 20],
        };
    }
}
