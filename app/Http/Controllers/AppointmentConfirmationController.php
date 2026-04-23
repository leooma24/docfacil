<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Confirmacion de cita en 1 clic desde WhatsApp.
 *
 * El link se manda en el recordatorio 24h por WhatsApp — el paciente da clic
 * y se marca confirmed_at + status='confirmed'. Tambien soporta cancelar.
 *
 * Ruta firmada con TTL (signed middleware de Laravel). Sin auth — el
 * paciente no necesita cuenta.
 */
class AppointmentConfirmationController extends Controller
{
    public function show(Request $request, Appointment $appointment)
    {
        $action = $request->query('action', 'confirm');
        $alreadyHandled = in_array($appointment->status, ['confirmed', 'cancelled', 'completed', 'no_show']);

        // Solo procesar si la cita esta pendiente y no ha pasado
        if (!$alreadyHandled && $appointment->starts_at->isFuture()) {
            if ($action === 'cancel') {
                $appointment->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Cancelada por paciente vía WhatsApp (1-clic)',
                ]);
            } else {
                $appointment->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
            }

            Log::info('Appointment 1-click action', [
                'appointment_id' => $appointment->id,
                'action' => $action,
                'ip' => $request->ip(),
            ]);
        }

        $appointment->load(['patient', 'clinic', 'doctor.user']);

        return response()->view('appointment-confirmation', [
            'appointment' => $appointment->fresh(),
            'action' => $action,
            'alreadyHandled' => $alreadyHandled,
        ]);
    }
}
