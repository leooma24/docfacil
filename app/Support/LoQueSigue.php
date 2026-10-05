<?php

namespace App\Support;

use App\Filament\Doctor\Resources\AppointmentResource;
use App\Filament\Doctor\Resources\PatientResource;
use App\Filament\Doctor\Resources\PaymentPlanResource;
use App\Filament\Doctor\Resources\PaymentResource;
use App\Filament\Doctor\Resources\TreatmentPlanResource;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;

/**
 * Lo que toca hacer con un paciente, en orden de urgencia y cada cosa con su
 * liga: lo que debe vencido, su próxima cita, lo aceptado sin agendar, el
 * presupuesto sin respuesta, la siguiente mensualidad y si faltan sus alergias.
 *
 * Cada renglón: ['tipo', 'titulo', 'detalle', 'url', 'accion', 'tono'].
 */
class LoQueSigue
{
    public static function para(Patient $paciente): array
    {
        $lista = [];
        $dinero = fn ($n) => '$' . number_format((float) $n, 0);

        $vencidos = Payment::withoutGlobalScopes()->where('patient_id', $paciente->id)->overdue()->orderBy('due_date')->get();
        if ($vencidos->isNotEmpty()) {
            $lista[] = [
                'tipo' => 'saldo',
                'titulo' => 'Debe ' . $dinero($vencidos->sum(fn ($p) => $p->remaining)) . ' vencido',
                'detalle' => 'Desde el ' . $vencidos->first()->due_date->format('d/m/Y')
                    . ($vencidos->count() > 1 ? ' · ' . $vencidos->count() . ' cobros' : ''),
                'url' => $vencidos->count() === 1
                    ? PaymentResource::getUrl('edit', ['record' => $vencidos->first()->id], panel: 'doctor')
                    : PaymentResource::getUrl('index', ['tableSearch' => $paciente->first_name . ' ' . $paciente->last_name, 'tableFilters' => ['overdue' => ['isActive' => true]]], panel: 'doctor'),
                'accion' => 'Cobrar',
                'tono' => 'rojo',
            ];
        }

        $cita = Appointment::withoutGlobalScopes()->where('patient_id', $paciente->id)
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->where('starts_at', '>=', now()->subHours(2))
            ->with(['service', 'treatmentPlanItem'])
            ->orderBy('starts_at')->first();
        if ($cita) {
            $lista[] = [
                'tipo' => 'cita',
                'titulo' => 'Próxima cita: ' . $cita->starts_at->locale('es')->isoFormat('ddd D [de] MMM, HH:mm'),
                'detalle' => $cita->treatmentPlanItem?->description ?? $cita->service?->name ?? 'Consulta',
                'url' => AppointmentResource::getUrl('edit', ['record' => $cita->id], panel: 'doctor'),
                'accion' => 'Ver cita',
                'tono' => 'verde',
            ];
        }

        $porAgendar = TreatmentPlanItem::whereHas('treatmentPlan', fn ($q) => $q->withoutGlobalScopes()
                ->where('patient_id', $paciente->id)->where('status', 'accepted'))
            ->whereNull('completed_at')
            ->orderBy('sort_order')->get()
            ->reject(fn (TreatmentPlanItem $i) => $i->citaPendiente());
        if ($porAgendar->isNotEmpty()) {
            $n = $porAgendar->count();
            $lista[] = [
                'tipo' => 'por_agendar',
                'titulo' => $n . ($n === 1 ? ' tratamiento aceptado' : ' tratamientos aceptados') . ' por agendar',
                'detalle' => $porAgendar->take(3)->pluck('description')->implode(' · ') . ($n > 3 ? ' · …' : ''),
                'url' => TreatmentPlanResource::urlParaAgendar($porAgendar->first()->treatmentPlan),
                'accion' => 'Agendar',
                'tono' => 'ambar',
            ];
        }

        $enviado = TreatmentPlan::withoutGlobalScopes()->where('patient_id', $paciente->id)->where('status', 'sent')->latest('sent_at')->first();
        if ($enviado) {
            $lista[] = [
                'tipo' => 'presupuesto',
                'titulo' => 'Presupuesto sin respuesta: ' . $dinero($enviado->total),
                'detalle' => 'Enviado ' . ($enviado->sent_at?->locale('es')->diffForHumans() ?? '') . ' · ' . $enviado->title,
                'url' => TreatmentPlanResource::getUrl('edit', ['record' => $enviado->id], panel: 'doctor'),
                'accion' => 'Ver presupuesto',
                'tono' => 'ambar',
            ];
        }

        $plan = PaymentPlan::withoutGlobalScopes()->where('patient_id', $paciente->id)->where('status', 'active')->oldest()->first();
        $mensualidad = $plan?->siguiente();
        if ($mensualidad && ! $mensualidad->is_overdue) {
            $lista[] = [
                'tipo' => 'mensualidad',
                'titulo' => 'Siguiente mensualidad: ' . $dinero($mensualidad->remaining),
                'detalle' => ($mensualidad->installment_number ? 'Mensualidad ' . $mensualidad->installment_number . ' de ' . $plan->installments_count . ' · ' : '')
                    . 'vence el ' . $mensualidad->due_date?->format('d/m/Y'),
                'url' => PaymentPlanResource::getUrl('view', ['record' => $plan->id], panel: 'doctor'),
                'accion' => 'Ver plan',
                'tono' => 'gris',
            ];
        }

        if (blank($paciente->allergies)) {
            $lista[] = [
                'tipo' => 'alergias',
                'titulo' => 'Alergias sin registrar',
                'detalle' => 'Pregúntele antes de recetar o anestesiar.',
                'url' => PatientResource::getUrl('edit', ['record' => $paciente->id], panel: 'doctor'),
                'accion' => 'Registrar',
                'tono' => 'ambar',
            ];
        }

        if (! $cita) {
            array_splice($lista, $vencidos->isNotEmpty() ? 1 : 0, 0, [[
                'tipo' => 'sin_cita',
                'titulo' => 'Sin próxima cita',
                'detalle' => $porAgendar->isNotEmpty() ? 'Tiene tratamientos aceptados esperando fecha.' : 'Agéndele su siguiente visita.',
                'url' => AppointmentResource::getUrl('create', ['patient' => $paciente->id], panel: 'doctor'),
                'accion' => 'Agendar',
                'tono' => 'gris',
            ]]);
        }

        return $lista;
    }
}
