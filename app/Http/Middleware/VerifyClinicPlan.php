<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyClinicPlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->clinic_id) {
            return $next($request);
        }

        $clinic = $user->clinic;

        if (!$clinic || !$clinic->is_active) {
            abort(403, 'Tu consultorio ha sido desactivado. Contacta soporte.');
        }

        // Allow access to special pages
        if ($request->routeIs('filament.doctor.pages.actualizar-plan', 'filament.doctor.pages.configuracion')) {
            return $next($request);
        }

        // Redirect new doctors to onboarding
        if ($clinic->onboarding_status === 'pending' && !$request->routeIs('filament.doctor.pages.configuracion')) {
            return redirect()->route('filament.doctor.pages.configuracion');
        }

        // Check if plan/trial/beta has expired
        $isExpired = false;

        // Free plan with expired trial
        if ($clinic->plan === 'free' && $clinic->trial_ends_at && $clinic->trial_ends_at->isPast()) {
            $isExpired = true;
        }

        // Beta tester with expired beta
        if ($clinic->is_beta && $clinic->beta_ends_at && $clinic->beta_ends_at->isPast()) {
            $isExpired = true;
        }

        // Redirect to upgrade page on write operations if expired
        if ($isExpired && $request->routeIs('*.create', '*.edit', '*.store', '*.update')) {
            return redirect()->route('filament.doctor.pages.actualizar-plan');
        }

        // Plan limits - BLOCK
        $limits = $this->getPlanLimits($clinic->plan);

        if ($limits && !$isExpired) {
            if ($limits['patients'] && $request->routeIs('*.patients.create')) {
                $patientCount = $clinic->patients()->count();
                if ($patientCount >= $limits['patients']) {
                    return redirect()->route('filament.doctor.pages.actualizar-plan');
                }
            }

            if ($limits['appointments'] && $request->routeIs('*.appointments.create')) {
                $monthlyAppointments = $clinic->appointments()
                    ->whereMonth('starts_at', now()->month)
                    ->whereYear('starts_at', now()->year)
                    ->count();
                if ($monthlyAppointments >= $limits['appointments']) {
                    return redirect()->route('filament.doctor.pages.actualizar-plan');
                }
            }

            if ($limits['doctors'] && $request->routeIs('*.doctor-invitations.create')) {
                $doctorCount = $clinic->doctors()->count();
                if ($doctorCount >= $limits['doctors']) {
                    return redirect()->route('filament.doctor.pages.actualizar-plan');
                }
            }
        }

        return $next($request);
    }

    private function getPlanLimits(string $plan): ?array
    {
        return match ($plan) {
            'free' => ['doctors' => 1, 'patients' => 30, 'appointments' => 20],
            'basico' => ['doctors' => 1, 'patients' => 200, 'appointments' => null],
            'profesional' => ['doctors' => 3, 'patients' => null, 'appointments' => null],
            'clinica' => null,
            default => ['doctors' => 1, 'patients' => 30, 'appointments' => 20],
        };
    }

    /**
     * Feature gates per plan. Use Plan::hasFeature($plan, 'ai_dictation').
     */
    public static function planHasFeature(string $plan, string $feature): bool
    {
        $features = [
            'free' => [],
            'basico' => ['voice_dictation', 'patient_ai_summary', 'ai_dx_suggestions', 'pdf_prescriptions'],
            'profesional' => ['voice_dictation', 'patient_ai_summary', 'ai_dx_suggestions', 'pdf_prescriptions',
                'smart_dictation', 'ai_consent_templates', 'ai_insights', 'whatsapp_payment', 'qr_checkin',
                'patient_portal', 'multi_doctor'],
            'clinica' => ['voice_dictation', 'patient_ai_summary', 'ai_dx_suggestions', 'pdf_prescriptions',
                'smart_dictation', 'ai_consent_templates', 'ai_insights', 'whatsapp_payment', 'qr_checkin',
                'patient_portal', 'multi_doctor', 'unlimited_doctors', 'multi_branch', 'priority_support'],
        ];
        return in_array($feature, $features[$plan] ?? []);
    }
}
