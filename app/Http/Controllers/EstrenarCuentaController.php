<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\InvitacionAlDoctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * El doctor llega aquí desde la liga firmada que se le mandó y elige su
 * contraseña. Al terminar ya está dentro de su panel.
 *
 * Las dos rutas van con middleware `signed`: sin firma válida no se entra, así
 * que no hace falta guardar tokens en ninguna tabla.
 */
class EstrenarCuentaController extends Controller
{
    public function show(Request $request, User $user)
    {
        $this->siYaLaUso($request, $user);

        return view('doctor.estrenar', [
            'doctor' => $user,
            'clinica' => $user->clinic,
        ]);
    }

    public function store(Request $request, User $user)
    {
        $this->siYaLaUso($request, $user);

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], ['password' => 'contraseña']);

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            // Llegó por una liga que se le mandó a mano: no le pedimos que
            // además verifique el correo.
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        auth()->login($user);
        $request->session()->regenerate();

        return redirect('/doctor');
    }

    /**
     * La liga lleva una huella de la contraseña que tenía la cuenta cuando se
     * generó. En cuanto el doctor elige la suya, la huella deja de coincidir y
     * la liga muere: de un solo uso, sin llevar la cuenta en ningún lado.
     */
    private function siYaLaUso(Request $request, User $user): void
    {
        $huella = (string) $request->query('h');

        abort_unless(
            hash_equals(InvitacionAlDoctor::huella($user), $huella),
            403,
            'Esta liga ya se usó. Entra con tu correo y tu contraseña.',
        );
    }
}
