<?php

namespace App\Http\Controllers;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use App\Support\AvisoDePrivacidad;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function show(string $slug)
    {
        $clinic = $this->consultorioConCheckIn($slug);

        return view('checkin.form', compact('clinic'));
    }

    public function store(Request $request, string $slug)
    {
        $clinic = $this->consultorioConCheckIn($slug);

        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'allergies' => 'nullable|string|max:500',
            'reason_for_visit' => 'nullable|string|max:500',
            'honeypot' => 'nullable|size:0',
            // Aquí el paciente escribe alergias, tipo de sangre y por qué viene:
            // datos de salud. Antes se guardaban sin aviso ni casilla; sin su
            // consentimiento expreso ya no (ley de datos personales, art. 8).
            'acepta_aviso' => 'accepted',
        ], [
            'acepta_aviso.accepted' => 'Para registrarte, acepta el aviso de privacidad.',
        ]);

        if (!empty($data['honeypot'])) {
            return back();
        }

        // El mismo teléfono es el mismo paciente, aunque lo escriba con espacios.
        $existing = Patient::porTelefono($clinic->id, $data['phone'] ?? null);

        if ($existing) {
            // Al paciente que ya existe no se le escribe en el expediente desde
            // aqui: esta pantalla no sabe quien esta del otro lado. Antes se le
            // anexaba el motivo a sus notas medicas, asi que cualquiera con su
            // telefono podia escribirle texto libre en el historial.
            // La aceptacion del aviso si se registra, porque la liga viene
            // firmada desde el QR que esta pegado en la recepcion.
            AvisoDePrivacidad::registrarAceptacion($existing, 'check_in');

            // Lo que sí se hace es avisar que llegó: la cita de hoy queda
            // marcada y el doctor lo ve. La pantalla dice lo mismo tenga o no
            // cita, para no revelar quién se atiende aquí.
            if ($cita = Appointment::deHoyPara($existing)) {
                $cita->marcarLlegada();
            } else {
                $this->avisarQueEstaEnSala($clinic, $existing, false);
            }

            return view('checkin.success', ['clinic' => $clinic]);
        }

        // El que llegó al tope es el consultorio, pero quien está parado
        // frente a la pantalla es el paciente. A él no le sirve saber de
        // planes: se le manda con la recepción y ya el doctor verá.
        if (! $clinic->puedeAgregarPacientes()) {
            Log::warning('Check-in rechazado: el consultorio llegó al tope de pacientes', [
                'clinic_id' => $clinic->id,
                'plan' => $clinic->plan,
            ]);

            return back()
                ->withInput()
                ->withErrors(['first_name' => 'No pudimos registrarte desde aquí. Pasa con recepción y con gusto te dan de alta.']);
        }

        $paciente = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'gender' => $data['gender'] ?? null,
            'blood_type' => $data['blood_type'] ?? null,
            'allergies' => $data['allergies'] ?? null,
            'medical_notes' => !empty($data['reason_for_visit'])
                ? "[" . now()->format('d/m/Y H:i') . "] Motivo: " . $data['reason_for_visit']
                : null,
            'is_active' => true,
        ]);

        AvisoDePrivacidad::registrarAceptacion($paciente, 'check_in');
        $this->avisarQueEstaEnSala($clinic, $paciente, true);

        return view('checkin.success', ['clinic' => $clinic]);
    }

    /** Llegó alguien sin cita de hoy: el consultorio lo sabe y abre su perfil en un clic. */
    protected function avisarQueEstaEnSala(Clinic $clinic, Patient $paciente, bool $nuevo): void
    {
        $nombre = trim($paciente->first_name . ' ' . $paciente->last_name);
        foreach (User::where('clinic_id', $clinic->id)->whereIn('role', ['doctor', 'staff'])->get() as $usuario) {
            Notification::make()
                ->title("{$nombre} está en la sala de espera")
                ->body($nuevo ? 'Se registró con el QR. Es paciente nuevo.' : 'Se registró con el QR. No tiene cita hoy.')
                ->icon('heroicon-o-map-pin')
                ->iconColor('info')
                ->actions([
                    NotificationAction::make('atender')
                        ->label('Iniciar consulta')
                        ->url(\App\Filament\Doctor\Pages\Consultation::urlParaPaciente($paciente))
                        ->markAsRead(),
                    NotificationAction::make('ver')
                        ->label('Ver paciente')
                        ->url(PatientProfile::getUrl(['patient' => $paciente->id], panel: 'doctor'))
                        ->markAsRead(),
                ])
                ->sendToDatabase($usuario);
        }
    }

    /**
     * El consultorio del slug, solo si esta activo y su plan incluye el
     * check-in por QR. Antes la ruta estaba abierta para cualquier
     * consultorio, incluidos los del plan gratis que no lo pagan.
     */
    protected function consultorioConCheckIn(string $slug): Clinic
    {
        $clinic = Clinic::where('slug', $slug)->where('is_active', true)->firstOrFail();

        abort_unless($clinic->hasFeature('qr_checkin'), 404);

        return $clinic;
    }
}
