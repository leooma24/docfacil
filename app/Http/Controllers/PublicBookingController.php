<?php

namespace App\Http\Controllers;

use App\Exceptions\HorarioYaApartado;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Models\Patient;
use App\Services\HuecosDisponibles;
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

    /**
     * Horarios libres para que el paciente elija de una lista en vez de
     * escribir una fecha a ciegas y que se la rechacemos.
     *
     * Lo consume el JavaScript del formulario cada vez que el paciente
     * cambia de servicio o de doctor.
     */
    public function horariosLibres(Request $request, string $slug)
    {
        $clinic = Clinic::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        abort_unless($clinic->hasFeature('public_booking'), 403);

        $duracion = 30;
        if ($request->filled('service_id')) {
            $servicio = $clinic->services()->find($request->input('service_id'));
            $duracion = (int) ($servicio->duration_minutes ?? 30) ?: 30;
        }

        $doctorId = $request->filled('doctor_id') ? (int) $request->input('doctor_id') : null;

        $agenda = HuecosDisponibles::proximosDias($clinic, $doctorId, $duracion);

        return response()->json([
            'dias' => collect($agenda)->map(fn (array $horas, string $fecha) => [
                'fecha' => $fecha,
                'nombre' => HuecosDisponibles::nombreDelDia($fecha),
                'horas' => $horas,
            ])->values(),
        ]);
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
            // Con anticipacion minima: una cita para dentro de dos minutos
            // no le sirve a nadie.
            'preferred_at' => 'required|date|after:' . now()->addMinutes(Appointment::ANTICIPACION_MINIMA_MINUTOS - 1)->toDateTimeString(),
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
        if (! empty($data['doctor_id'])) {
            $docOk = $clinic->doctors()->where('id', $data['doctor_id'])->exists();
            abort_unless($docOk, 422);
        } elseif ($clinic->doctors()->doesntExist()) {
            abort(422, 'El consultorio no tiene doctores configurados.');
        }

        // Cuando el paciente elige "cualquiera" NO se le asigna el primer
        // doctor aqui. Antes se hacia asi, sin mirar si estaba libre: al
        // primer doctor se le apilaban todas las solicitudes encimadas.
        // Mas abajo, ya dentro de la transaccion, se busca uno que si tenga
        // el horario libre.

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

        // La duracion sale del servicio que pidio el paciente. Antes se
        // apartaban 30 minutos fijos, asi que una endodoncia de 90 minutos
        // dejaba el sillon ocupado con media hora en la agenda.
        $minutos = 30;
        if (! empty($data['service_id'])) {
            $servicio = $clinic->services()->find($data['service_id']);
            $minutos = (int) ($servicio->duration_minutes ?? 30) ?: 30;
        }
        $endsAt = $startsAt->copy()->addMinutes($minutos);

        // Que no pida cita cuando el consultorio esta cerrado. Sin esto se
        // podia apartar el domingo a las 3 de la mañana. Al doctor no se lo
        // bloqueamos: el es el dueño de su agenda y puede atender una
        // urgencia fuera de horario si asi lo decide.
        if (! $clinic->cabeLaCita($startsAt, $endsAt)) {
            return back()
                ->withInput()
                ->withErrors(['preferred_at' => $clinic->horarioDelDia($startsAt)]);
        }

        // La reserva va dentro de una transaccion con candado, y el traslape se
        // vuelve a revisar adentro.
        //
        // Sin esto habia una carrera real: dos pacientes que ven la misma lista
        // de horarios pasan los dos la validacion y se crean las dos citas. El
        // selector de horarios lo hace mas probable, porque los dos ven
        // exactamente los mismos botones.
        //
        // Si el paciente eligio "cualquiera", aqui se le asigna un doctor que
        // de verdad este libre: antes no se revisaba nada y dos pacientes
        // podian apartar la misma hora sin doctor.
        $doctorId = ! empty($data['doctor_id']) ? (int) $data['doctor_id'] : null;

        try {
            $appointment = DB::transaction(function () use ($clinic, $patient, $data, $startsAt, $endsAt, $doctorId) {
                // El candado serializa las reservas del mismo consultorio y dia,
                // que es donde puede haber choque.
                Appointment::withoutGlobalScopes()
                    ->where('clinic_id', $clinic->id)
                    ->whereDate('starts_at', $startsAt->toDateString())
                    ->lockForUpdate()
                    ->get(['id']);

                if ($doctorId) {
                    if (Appointment::traslapes($clinic->id, $doctorId, $startsAt, $endsAt)->isNotEmpty()) {
                        throw new HorarioYaApartado();
                    }
                } else {
                    $doctorId = Appointment::doctorLibreEn($clinic->id, $startsAt, $endsAt);

                    if (! $doctorId) {
                        throw new HorarioYaApartado();
                    }
                }

                return Appointment::create([
                    'clinic_id' => $clinic->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctorId,
                    'service_id' => $data['service_id'] ?? null,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'status' => 'scheduled',
                    'notes' => trim('[Solicitud portal público] ' . ($data['notes'] ?? '')),
                ]);
            });
        } catch (HorarioYaApartado) {
            return back()
                ->withInput()
                ->withErrors(['preferred_at' => 'Ese horario se acaba de apartar. Elige otra hora y con gusto te atendemos.']);
        }

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
