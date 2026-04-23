<?php

namespace App\Http\Controllers;

use App\Models\TreatmentPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TreatmentPlanController extends Controller
{
    /**
     * PDF descargable (auth requerida — solo el doctor de la misma clinica).
     */
    public function downloadPdf(TreatmentPlan $treatmentPlan)
    {
        abort_unless(auth()->check() && auth()->user()->clinic_id === $treatmentPlan->clinic_id, 403);

        $treatmentPlan->load(['patient', 'doctor.user', 'clinic', 'items.service']);

        $pdf = Pdf::loadView('pdf.treatment-plan', ['plan' => $treatmentPlan]);
        return $pdf->stream("presupuesto-{$treatmentPlan->id}.pdf");
    }

    /**
     * Vista publica del presupuesto (sin auth, por public_token). Paciente
     * lo ve en su celular y tiene boton para aceptar.
     */
    public function publicShow(string $token)
    {
        $plan = TreatmentPlan::where('public_token', $token)
            ->whereIn('status', ['sent', 'accepted', 'rejected'])
            ->with(['patient', 'doctor.user', 'clinic', 'items.service'])
            ->firstOrFail();

        return response()->view('treatment-plan.public', [
            'plan' => $plan,
            'acceptUrl' => \Illuminate\Support\Facades\URL::signedRoute('treatment-plan.accept', ['token' => $token]),
            'rejectUrl' => \Illuminate\Support\Facades\URL::signedRoute('treatment-plan.reject', ['token' => $token]),
        ]);
    }

    /**
     * Aceptar plan desde link firmado. Registra IP y timestamp.
     */
    public function accept(Request $request, string $token)
    {
        abort_unless($request->hasValidSignature(), 403, 'Enlace expirado');

        $plan = TreatmentPlan::where('public_token', $token)
            ->where('status', 'sent')
            ->firstOrFail();

        $plan->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'accepted_ip' => $request->ip(),
        ]);

        Log::info('Treatment plan accepted', [
            'plan_id' => $plan->id,
            'ip' => $request->ip(),
        ]);

        $plan->load(['patient', 'doctor.user', 'clinic']);

        return response()->view('treatment-plan.accepted', ['plan' => $plan]);
    }

    public function reject(Request $request, string $token)
    {
        abort_unless($request->hasValidSignature(), 403, 'Enlace expirado');

        $plan = TreatmentPlan::where('public_token', $token)
            ->whereIn('status', ['sent', 'accepted'])
            ->firstOrFail();

        $plan->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return response()->view('treatment-plan.rejected', ['plan' => $plan->load('clinic')]);
    }
}
