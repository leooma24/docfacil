<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * El paciente llega aquí desde la liga firmada que le mandó su consultorio
 * y elige su contraseña. Al terminar queda dentro del portal.
 *
 * Las dos rutas van con middleware `signed`: sin firma válida no se entra,
 * así que no hace falta guardar tokens en ninguna tabla.
 */
class PatientPortalActivationController extends Controller
{
    public function show(Patient $patient)
    {
        if ($problema = $this->noSePuedeActivar($patient)) {
            return $problema;
        }

        return view('paciente.activar', [
            'patient' => $patient,
            'clinica' => $patient->clinic,
        ]);
    }

    public function store(Request $request, Patient $patient)
    {
        if ($problema = $this->noSePuedeActivar($patient)) {
            return $problema;
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], ['password' => 'contraseña']);

        $user = new User();
        $user->name = trim("{$patient->first_name} {$patient->last_name}");
        $user->email = $patient->email;
        $user->password = Hash::make($request->input('password'));
        $user->role = 'patient';
        $user->clinic_id = $patient->clinic_id;
        // Llegó por una liga firmada que le mandó su consultorio: no le
        // pedimos que además verifique el correo.
        $user->email_verified_at = now();
        $user->save();

        $patient->update(['user_id' => $user->id]);

        auth()->login($user);

        return redirect('/paciente');
    }

    /**
     * Motivos por los que una liga válida ya no sirve. Devuelve la respuesta
     * a mostrar, o null si se puede seguir.
     */
    private function noSePuedeActivar(Patient $patient)
    {
        // El consultorio se cambió a un plan sin portal.
        if (! $patient->clinic?->hasFeature('patient_portal')) {
            abort(403, 'Este consultorio no tiene activado el portal del paciente.');
        }

        // Ya la usó antes: que entre por el login normal.
        if ($patient->user_id) {
            return redirect('/paciente/login')
                ->with('status', 'Tu acceso ya estaba activo. Entra con tu correo y contraseña.');
        }

        // Sin correo no hay con qué entrar.
        if (empty($patient->email)) {
            abort(422, 'Tu consultorio necesita registrar tu correo antes de darte acceso.');
        }

        // El correo ya lo usa otra cuenta (por ejemplo el propio doctor).
        if (User::where('email', $patient->email)->exists()) {
            abort(409, 'Ese correo ya tiene una cuenta en DocFácil. Pídele a tu consultorio que registre otro.');
        }

        return null;
    }
}
