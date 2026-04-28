<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    public function accept(string $token)
    {
        $invitation = DoctorInvitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            abort(410, 'Esta invitación ha expirado.');
        }

        return view('invitations.accept', compact('invitation'));
    }

    public function store(Request $request, string $token)
    {
        $invitation = DoctorInvitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            abort(410, 'Esta invitación ha expirado.');
        }

        $validated = $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::forceCreate([
            'name' => $invitation->name,
            'email' => $invitation->email,
            'password' => Hash::make($validated['password']),
            'role' => 'doctor',
            'clinic_id' => $invitation->clinic_id,
            // Email verificado: ya demostró acceso al inbox al dar clic
            // al link de invitación. Y aceptó terminos al completar registro.
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
        ]);

        Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $invitation->clinic_id,
            'specialty' => $invitation->specialty,
        ]);

        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        auth()->login($user);

        return redirect('/doctor');
    }
}
