<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Support\SalaDeEspera;

/**
 * La pantalla de la sala de espera, para una tele o tablet. Va firmada como
 * la del QR de llegada: con solo el nombre del consultorio no se puede ver
 * quién tiene cita hoy.
 */
class SalaDeEsperaController extends Controller
{
    public function __invoke(string $slug)
    {
        $clinica = Clinic::where('slug', $slug)->where('is_active', true)->firstOrFail();

        abort_unless($clinica->hasFeature('pantalla_sala'), 404);

        return view('sala.pantalla', ['clinica' => $clinica] + SalaDeEspera::para($clinica));
    }
}
