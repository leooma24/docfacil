<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el acceso al portal del paciente cuando la clínica del usuario
 * no tiene el feature `patient_portal` (solo Pro y Clínica lo incluyen).
 *
 * Si la clínica dueña del paciente está en Free o Básico, el paciente ve una
 * página 403 con mensaje y un enlace a contactar la clínica para que actualice.
 */
class VerifyPatientPortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->clinic_id) {
            return $next($request);
        }

        $clinic = $user->clinic;
        if (!$clinic || !$clinic->hasFeature('patient_portal')) {
            abort(403, 'Tu clínica no tiene activado el portal del paciente en su plan actual. Contacta a tu consultorio.');
        }

        return $next($request);
    }
}
