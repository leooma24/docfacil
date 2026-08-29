<?php

namespace Tests\Feature;

use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Un consultorio recien registrado trae 15 dias con Pro completo, para que
 * alcance a ver todo lo que el producto hace antes de decidir. Antes salia
 * con plan 'free' y hasFeature() le devolvia false a TODO: ni recetas PDF
 * ni recordatorios. El doctor entraba a un producto vacio.
 *
 * Al vencer la prueba se queda con lo que pague, o cae a Free.
 */
class TrialFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function consultorio(array $datos): Clinic
    {
        return Clinic::create(array_merge([
            'name' => 'Consultorio Test',
            'onboarding_status' => 'completed',
        ], $datos));
    }

    /** Lo que un dentista tiene que poder probar durante sus 15 dias. */
    private const FEATURES_PRO = [
        'pdf_prescriptions', 'whatsapp_reminders', 'qr_checkin', 'odontogram',
        'consent_forms', 'waitlist', 'public_booking', 'advanced_reports',
        'patient_portal',
    ];

    public function test_en_la_prueba_trae_pro_completo(): void
    {
        $c = $this->consultorio(['plan' => 'free', 'trial_ends_at' => now()->addDays(15)]);

        $this->assertTrue($c->enPruebaVigente());

        foreach (self::FEATURES_PRO as $feature) {
            $this->assertTrue($c->hasFeature($feature), "En la prueba deberia tener {$feature}.");
        }
    }

    public function test_la_prueba_no_regala_lo_exclusivo_de_clinica(): void
    {
        $c = $this->consultorio(['plan' => 'free', 'trial_ends_at' => now()->addDays(15)]);

        $this->assertFalse($c->hasFeature('unlimited_doctors'));
        $this->assertFalse($c->hasFeature('per_doctor_reports'));
    }

    public function test_al_vencer_la_prueba_sin_pagar_se_queda_sin_features(): void
    {
        $c = $this->consultorio(['plan' => 'free', 'trial_ends_at' => now()->subDay()]);

        $this->assertFalse($c->enPruebaVigente());

        foreach (self::FEATURES_PRO as $feature) {
            $this->assertFalse($c->hasFeature($feature), "Prueba vencida: no deberia tener {$feature}.");
        }
    }

    public function test_si_paga_basico_se_queda_con_basico(): void
    {
        $c = $this->consultorio(['plan' => 'basico', 'plan_ends_at' => now()->addMonth()]);

        $this->assertTrue($c->hasFeature('odontogram'));
        $this->assertTrue($c->hasFeature('pdf_prescriptions'));
        // Lo de Pro ya no:
        $this->assertFalse($c->hasFeature('consent_forms'));
        $this->assertFalse($c->hasFeature('waitlist'));
    }

    public function test_si_paga_pro_se_queda_con_pro(): void
    {
        $c = $this->consultorio(['plan' => 'profesional', 'plan_ends_at' => now()->addMonth()]);

        foreach (self::FEATURES_PRO as $feature) {
            $this->assertTrue($c->hasFeature($feature), "Pro pagado deberia tener {$feature}.");
        }
    }

    public function test_la_landing_sigue_mostrando_free_como_free(): void
    {
        // El regalo de la prueba es permiso en tiempo real, no cambia lo que
        // el plan Free dice que incluye.
        $this->assertSame([], Clinic::featuresForPlan('free'));
    }

    public function test_un_plan_pagado_vencido_no_revive_como_prueba(): void
    {
        $c = $this->consultorio([
            'plan' => 'profesional',
            'plan_ends_at' => now()->subDay(),
            'trial_ends_at' => now()->addDays(10),
        ]);

        $this->assertFalse($c->enPruebaVigente());
        $this->assertFalse($c->hasFeature('odontogram'));
    }
}
