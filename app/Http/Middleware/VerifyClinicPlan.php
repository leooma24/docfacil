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

        // Check trial expiration for free plan
        if ($clinic->plan === 'free' && $clinic->trial_ends_at && $clinic->trial_ends_at->isPast()) {
            // Allow access but show warning via session
            session()->flash('trial_expired', true);
        }

        // Plan limits
        $limits = $this->getPlanLimits($clinic->plan);

        if ($limits) {
            $patientCount = $clinic->patients()->count();
            $monthlyAppointments = $clinic->appointments()
                ->whereMonth('starts_at', now()->month)
                ->whereYear('starts_at', now()->year)
                ->count();

            if ($patientCount >= $limits['patients'] && $request->routeIs('*.patients.create')) {
                session()->flash('plan_limit', "Has alcanzado el límite de {$limits['patients']} pacientes en tu plan. Actualiza tu plan para continuar.");
            }

            if ($limits['appointments'] && $monthlyAppointments >= $limits['appointments'] && $request->routeIs('*.appointments.create')) {
                session()->flash('plan_limit', "Has alcanzado el límite de {$limits['appointments']} citas/mes en tu plan. Actualiza tu plan para continuar.");
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
