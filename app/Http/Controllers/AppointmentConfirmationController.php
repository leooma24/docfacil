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
        // Sin acción, la página solo se enseña. Antes el valor por omisión era
        // 'confirm', así que el paciente que abría a ver de qué se trataba
        // dejaba su cita confirmada sin haber decidido nada.
        $action = $request->query('action');
        // Una cita confirmada todavía se puede cancelar: al paciente le
        // salen imprevistos, y es mejor que avise a que no llegue.
        $cerradas = ['cancelled', 'completed', 'no_show'];
        $alreadyHandled = in_array($appointment->status, $action === 'cancel' ? $cerradas : [...$cerradas, 'confirmed']);

        // El recordatorio junta en un mensaje todas las citas del paciente ese
        // día, con una sola liga: lo que decida aplica a todas.
        $delDia = \App\Support\RecordatorioDeCita::delMismoDia($appointment);
        if ($delDia->isEmpty()) {
            $delDia = collect([$appointment]);
        }
        $horas = $delDia->map(fn ($c) => $c->starts_at->format('H:i'))->implode(' y ');

        // Solo procesar si la cita esta pendiente y no ha pasado
        if ($action && !$alreadyHandled && $appointment->starts_at->isFuture()) {
            foreach ($delDia as $cita) {
                if (! $cita->starts_at->isFuture()) {
                    continue;
                }
                $cita->update($action === 'cancel'
                    ? ['status' => 'cancelled', 'cancellation_reason' => 'Cancelada por paciente vía WhatsApp (1-clic)']
                    : ['status' => 'confirmed', 'confirmed_at' => now()]);
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
            'horas' => $horas,
        ]);
    }
}
