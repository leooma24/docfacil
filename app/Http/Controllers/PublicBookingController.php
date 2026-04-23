<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Portal publico de solicitud de cita.
 *
 * V1: formulario simple — paciente llena datos + servicio + fecha/hora
 * preferida, creamos la cita con status='scheduled' y notificamos a los
 * doctores de la clinica. El doctor confirma o re-agenda desde el panel.
 *
 * Feature-gated por 'public_booking' (Pro+). Si no esta activa la pagina
 * regresa 403.
 *
 * Antispam: honeypot field + throttle via routes.
 */
class PublicBookingController extends Controller
{
    public function show(string $slug)
    {
        $clinic = Clinic::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        abort_unless($clinic->hasFeature('public_booking'), 403, 'Este consultorio no tiene agenda pública habilitada.');

        $services = $clinic->services()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']);
        $doctors = $clinic->doctors()->with('user')->get()->map(fn ($d) => [
            'id' => $d->id,
            'name' => $d->user?->name ?? 'Doctor',
            'specialty' => $d->specialty,
        ]);

        return view('public-booking.form', compact('clinic', 'services', 'doctors'));
    }

    public function store(Request $request, string $slug)
    {
        $clinic = Clinic::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        abort_unless($clinic->hasFeature('public_booking'), 403);

        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'service_id' => 'nullable|exists:services,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'preferred_at' => 'required|date|after:now',
            'notes' => 'nullable|string|max:500',
            'honeypot' => 'nullable|size:0',
        ]);

        // Honeypot: humanos dejan vacio este campo; bots lo llenan.
        if (!empty($data['honeypot'])) {
            Log::warning('Public booking honeypot', ['slug' => $slug, 'ip' => $request->ip()]);
            return view('public-booking.success', ['clinic' => $clinic]);
        }

        // Validar que el servicio y doctor pertenezcan a esta clinica
        if (!empty($data['service_id'])) {
            $svcOk = $clinic->services()->where('id', $data['service_id'])->exists();
            abort_unless($svcOk, 422);
        }
        if (!empty($data['doctor_id'])) {
            $docOk = $clinic->doctors()->where('id', $data['doctor_id'])->exists();
            abort_unless($docOk, 422);
        } else {
            // Si no se especifica doctor, asignar el primero activo
            $firstDoctor = $clinic->doctors()->first();
            $data['doctor_id'] = $firstDoctor?->id;
        }

        if (empty($data['doctor_id'])) {
            abort(422, 'El consultorio no tiene doctores configurados.');
        }

        // Match paciente existente por telefono en esta clinica, o crear nuevo
        $patient = Patient::where('clinic_id', $clinic->id)
            ->where('phone', $data['phone'])
            ->first();

        if (!$patient) {
            $patient = Patient::create([
                'clinic_id' => $clinic->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'is_active' => true,
            ]);
        }

        $startsAt = \Carbon\Carbon::parse($data['preferred_at']);
        $endsAt = $startsAt->copy()->addMinutes(30);

        $appointment = Appointment::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $data['doctor_id'],
            'service_id' => $data['service_id'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'scheduled',
            'notes' => trim('[Solicitud portal público] ' . ($data['notes'] ?? '')),
        ]);

        $this->notifyClinic($clinic, $appointment, $patient);

        return view('public-booking.success', [
            'clinic' => $clinic,
            'appointment' => $appointment,
        ]);
    }

    protected function notifyClinic(Clinic $clinic, Appointment $appointment, Patient $patient): void
    {
        $recipients = $clinic->users()->whereIn('role', ['doctor', 'staff'])->get();
        if ($recipients->isEmpty()) return;

        $when = $appointment->starts_at->translatedFormat('l d \d\e F, H:i');

        foreach ($recipients as $recipient) {
            try {
                Notification::make()
                    ->title("Nueva solicitud de cita: {$patient->first_name} {$patient->last_name}")
                    ->icon('heroicon-o-calendar-days')
                    ->iconColor('success')
                    ->body("Horario solicitado: {$when}. Tel: {$patient->phone}. Confirma o re-agenda desde el panel.")
                    ->actions([
                        Action::make('ver')
                            ->label('Ver cita')
                            ->url('/doctor/citas/' . $appointment->id . '/edit')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($recipient);
            } catch (\Throwable $e) {
                Log::warning('Public booking notification failed', ['error' => $e->getMessage()]);
            }
        }
    }
}
