<?php

namespace App\Http\Controllers;

use App\Models\Clinic;

/**
 * "Ya no quiero recibirlo", desde el correo del corte del mes.
 *
 * Solo apaga ese correo. Los avisos de su cuenta (su plan, su contraseña)
 * le siguen llegando, y el corte lo sigue teniendo dentro de DocFácil.
 */
class CorteSinCorreoController extends Controller
{
    public function __invoke(Clinic $clinic)
    {
        $clinic->update(['corte_por_correo' => false]);

        return response()->view('emails.unsubscribed', [
            'success' => true,
            'mensaje' => 'Ya no te mandaremos el corte del mes por correo. Lo sigues teniendo en DocFácil, en Consultorio → Corte, y lo puedes volver a prender en tu Configuración.',
        ]);
    }
}
