<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Patient;
use App\Support\AvisoDePrivacidad;
use Illuminate\Http\Request;

/**
 * El aviso de privacidad del consultorio, y la liga para que el paciente lo
 * acepte desde su celular. La razón completa está en AvisoDePrivacidad.
 */
class AvisoDePrivacidadController extends Controller
{
    public function show(string $slug)
    {
        return view('aviso-privacidad.consultorio', [
            'clinic' => $this->consultorio($slug),
            'paciente' => null,
        ]);
    }

    public function formulario(string $slug, int $paciente)
    {
        $clinic = $this->consultorio($slug);

        return view('aviso-privacidad.consultorio', [
            'clinic' => $clinic,
            'paciente' => $this->pacienteDe($clinic, $paciente),
        ]);
    }

    public function aceptar(Request $request, string $slug, int $paciente)
    {
        $clinic = $this->consultorio($slug);
        $paciente = $this->pacienteDe($clinic, $paciente);

        $request->validate(
            ['acepta_aviso' => 'accepted'],
            ['acepta_aviso.accepted' => 'Marca la casilla para aceptar el aviso.'],
        );

        AvisoDePrivacidad::registrarAceptacion($paciente, 'liga');

        return view('aviso-privacidad.gracias', ['clinic' => $clinic, 'paciente' => $paciente]);
    }

    private function consultorio(string $slug): Clinic
    {
        return Clinic::withoutGlobalScopes()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    /** El paciente, y solo si es de este consultorio. */
    private function pacienteDe(Clinic $clinic, int $id): Patient
    {
        return Patient::withoutGlobalScopes()
            ->where('clinic_id', $clinic->id)
            ->findOrFail($id);
    }
}
