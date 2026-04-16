<?php

namespace Tests\Feature;

use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Valida que cada plan ofrece exactamente lo que prometemos en la landing.
 * Si estos tests fallan, la promesa al cliente y el código divergen.
 */
class PlanFeatureGateTest extends TestCase
{
    use RefreshDatabase;

    private function clinic(string $plan, array $overrides = []): Clinic
    {
        static $counter = 0;
        $counter++;
        return Clinic::create(array_merge([
            'name' => "Clínica Test {$counter}",
            'slug' => "test-{$plan}-{$counter}",
            'plan' => $plan,
            'is_active' => true,
            'plan_ends_at' => $plan === 'free' ? null : now()->addMonths(3),
        ], $overrides));
    }

    public function test_free_plan_has_no_paid_features(): void
    {
        $clinic = $this->clinic('free');

        $paidFeatures = [
            'pdf_prescriptions', 'whatsapp_reminders', 'whatsapp_payment', 'qr_checkin',
            'odontogram', 'consent_forms', 'patient_portal', 'multi_doctor',
            'advanced_reports', 'smart_alerts', 'unlimited_doctors', 'multi_branch',
        ];

        foreach ($paidFeatures as $feature) {
            $this->assertFalse($clinic->hasFeature($feature), "Free no debería tener '{$feature}'");
        }
    }

    public function test_basico_unlocks_landing_promises(): void
    {
        $clinic = $this->clinic('basico');

        // Prometidos en landing para Básico
        $this->assertTrue($clinic->hasFeature('pdf_prescriptions'));
        $this->assertTrue($clinic->hasFeature('whatsapp_reminders'));
        $this->assertTrue($clinic->hasFeature('whatsapp_payment'));
        $this->assertTrue($clinic->hasFeature('qr_checkin'));

        // Exclusivos de Pro+: Básico NO debería tenerlos
        $this->assertFalse($clinic->hasFeature('odontogram'));
        $this->assertFalse($clinic->hasFeature('consent_forms'));
        $this->assertFalse($clinic->hasFeature('patient_portal'));
        $this->assertFalse($clinic->hasFeature('multi_doctor'));
    }

    public function test_pro_unlocks_all_basico_plus_pro_features(): void
    {
        $clinic = $this->clinic('profesional');

        // Todo lo de Básico
        $this->assertTrue($clinic->hasFeature('pdf_prescriptions'));
        $this->assertTrue($clinic->hasFeature('whatsapp_reminders'));
        $this->assertTrue($clinic->hasFeature('qr_checkin'));

        // Exclusivos de Pro
        $this->assertTrue($clinic->hasFeature('odontogram'));
        $this->assertTrue($clinic->hasFeature('consent_forms'));
        $this->assertTrue($clinic->hasFeature('patient_portal'));
        $this->assertTrue($clinic->hasFeature('multi_doctor'));
        $this->assertTrue($clinic->hasFeature('advanced_reports'));
        $this->assertTrue($clinic->hasFeature('smart_alerts'));

        // Exclusivos de Clínica: Pro NO debería tenerlos
        $this->assertFalse($clinic->hasFeature('unlimited_doctors'));
        $this->assertFalse($clinic->hasFeature('multi_branch'));
    }

    public function test_clinica_has_everything(): void
    {
        $clinic = $this->clinic('clinica');

        $allFeatures = [
            'pdf_prescriptions', 'whatsapp_reminders', 'whatsapp_payment', 'qr_checkin',
            'odontogram', 'consent_forms', 'patient_portal', 'multi_doctor',
            'advanced_reports', 'smart_alerts',
            'unlimited_doctors', 'multi_branch', 'commissions_between_doctors', 'dedicated_onboarding',
        ];

        foreach ($allFeatures as $feature) {
            $this->assertTrue($clinic->hasFeature($feature), "Clínica debería tener '{$feature}'");
        }
    }

    public function test_expired_plan_loses_paid_features(): void
    {
        $clinic = $this->clinic('profesional', ['plan_ends_at' => now()->subDay()]);

        $this->assertFalse($clinic->hasFeature('odontogram'));
        $this->assertFalse($clinic->hasFeature('consent_forms'));
        $this->assertFalse($clinic->hasFeature('patient_portal'));
    }

    public function test_expired_trial_loses_features(): void
    {
        $clinic = $this->clinic('free', ['trial_ends_at' => now()->subDay()]);

        // Free con trial expirado: ningún feature (aunque estuvieran en Free, que son cero)
        $this->assertFalse($clinic->hasFeature('pdf_prescriptions'));
    }

    public function test_active_feature_scope_only_returns_clinics_with_active_plan(): void
    {
        $activePro = $this->clinic('profesional', ['plan_ends_at' => now()->addMonth()]);
        $expiredPro = $this->clinic('profesional', ['plan_ends_at' => now()->subDay()]);
        $freeClinic = $this->clinic('free');
        $basico = $this->clinic('basico', ['plan_ends_at' => now()->addMonth()]);

        $ids = Clinic::withActiveFeature('whatsapp_reminders')->pluck('id')->toArray();

        $this->assertContains($activePro->id, $ids);
        $this->assertContains($basico->id, $ids);
        $this->assertNotContains($expiredPro->id, $ids);
        $this->assertNotContains($freeClinic->id, $ids);
    }
}
