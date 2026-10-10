<?php

namespace App\Http\Controllers;

use App\Support\ExportarDatos;

/**
 * El doctor baja todo lo de su consultorio. Funciona en cualquier plan, aun
 * vencido: sus datos son suyos (ver la pantalla "Sus datos").
 */
class ExportarDatosController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        // Todo el expediente de todos los pacientes: solo el doctor, no la asistente.
        abort_if(! $user->clinic_id || $user->esAsistente(), 403);

        $exportar = new ExportarDatos($user->clinic);
        $ruta = $exportar->zip();

        activity()
            ->performedOn($user->clinic)
            ->causedBy($user)
            ->log('Bajó todos los datos del consultorio');

        return response()->download($ruta, $exportar->nombre(), ['Content-Type' => 'application/zip'])->deleteFileAfterSend();
    }
}
