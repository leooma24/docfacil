<?php

namespace App\Http\Middleware;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Support\ZonaHoraria;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cada consultorio ve la app a su hora. La razón completa está en ZonaHoraria.
 */
class UsarHoraDelConsultorio
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($clinica = $this->consultorio($request)) {
            ZonaHoraria::usar(ZonaHoraria::delConsultorio($clinica));
        }

        return $next($request);
    }

    private function consultorio(Request $request): ?Clinic
    {
        // Páginas públicas del consultorio: agendar, horarios libres, check-in.
        if ($request->is('clinica/*') && is_string($slug = $request->route('slug'))) {
            return Clinic::withoutGlobalScopes()->where('slug', $slug)->first();
        }

        // Confirmación de la cita desde el WhatsApp del paciente.
        $cita = $request->route('appointment');

        if ($cita instanceof Appointment) {
            return Clinic::withoutGlobalScopes()->find($cita->clinic_id);
        }

        // El panel del doctor, incluidas las peticiones de Livewire.
        $clinicId = $request->user()?->clinic_id;

        return $clinicId ? Clinic::withoutGlobalScopes()->find($clinicId) : null;
    }
}
