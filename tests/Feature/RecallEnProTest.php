<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\ClinicAddon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El recall pasó de add-on escondido a venir en el plan Pro.
 *
 * Estaba detrás de un add-on de $49 que nadie compró, siendo que es lo que
 * más dinero le genera a un dentista: el paciente que debía volver a los 6
 * meses y no volvió ya es suyo, nada más hay que hablarle.
 *
 * Sigue siendo comprable suelto para quien está en Básico.
 */
class RecallEnProTest extends TestCase
{
    use RefreshDatabase;

    private function clinica(string $plan, array $extra = []): Clinic
    {
        return Clinic::create(array_merge([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-' . uniqid(),
            'plan' => $plan,
            'plan_ends_at' => $plan === 'free' ? null : now()->addMonth(),
            'onboarding_status' => 'completed',
        ], $extra));
    }

    // ── Quién lo trae incluido ───────────────────────────────────

    public function test_el_plan_pro_ya_trae_recall(): void
    {
        $this->assertTrue($this->clinica('profesional')->hasFeature('recall_automation'));
    }

    public function test_el_plan_clinica_tambien(): void
    {
        $this->assertTrue($this->clinica('clinica')->hasFeature('recall_automation'));
    }

    public function test_el_plan_basico_no_lo_trae(): void
    {
        // Sigue siendo el add-on de $49 para ellos: es la escalera al Pro.
        $basico = $this->clinica('basico', ['trial_ends_at' => now()->subDay()]);

        $this->assertFalse($basico->hasFeature('recall_automation'));
    }

    public function test_el_plan_free_no_lo_trae(): void
    {
        $free = $this->clinica('free', ['trial_ends_at' => now()->subDay()]);

        $this->assertFalse($free->hasFeature('recall_automation'));
    }

    public function test_durante_la_prueba_de_15_dias_si_lo_trae(): void
    {
        // La prueba da Pro completo, para que alcance a ver lo que hace.
        $nuevo = $this->clinica('free', ['trial_ends_at' => now()->addDays(15)]);

        $this->assertTrue($nuevo->hasFeature('recall_automation'));
    }

    public function test_un_basico_lo_puede_comprar_suelto(): void
    {
        $basico = $this->clinica('basico', ['trial_ends_at' => now()->subDay()]);

        ClinicAddon::create([
            'clinic_id' => $basico->id,
            'addon_slug' => 'recall_automation',
            'status' => 'active',
            'monthly_price' => 49,
            'billing_cycle' => 'monthly',
            'started_at' => now(),
        ]);

        $this->assertTrue($basico->fresh()->hasFeature('recall_automation'));
    }

    // ── No venderle a nadie lo que ya tiene ──────────────────────

    public function test_al_pro_no_se_le_vuelve_a_vender(): void
    {
        $this->assertTrue($this->clinica('profesional')->planIncluyeFeature('recall_automation'));
    }

    public function test_al_basico_si_se_le_puede_ofrecer(): void
    {
        $basico = $this->clinica('basico', ['trial_ends_at' => now()->subDay()]);

        $this->assertFalse($basico->planIncluyeFeature('recall_automation'));
    }

    public function test_planIncluyeFeature_ignora_los_addons_comprados(): void
    {
        // Comprado no es lo mismo que incluido: si lo cancela, lo pierde.
        $basico = $this->clinica('basico', ['trial_ends_at' => now()->subDay()]);

        ClinicAddon::create([
            'clinic_id' => $basico->id,
            'addon_slug' => 'recall_automation',
            'status' => 'active',
            'monthly_price' => 49,
            'billing_cycle' => 'monthly',
            'started_at' => now(),
        ]);

        $this->assertTrue($basico->fresh()->hasFeature('recall_automation'));
        $this->assertFalse($basico->fresh()->planIncluyeFeature('recall_automation'));
    }

    public function test_un_pro_vencido_ya_no_lo_incluye(): void
    {
        $vencido = $this->clinica('profesional', [
            'plan_ends_at' => now()->subDay(),
            'trial_ends_at' => now()->subMonths(2),
        ]);

        $this->assertFalse($vencido->planIncluyeFeature('recall_automation'));
        $this->assertFalse($vencido->hasFeature('recall_automation'));
    }

    // ── Que la lista de planes y el catálogo no se contradigan ───

    public function test_el_addon_sigue_en_el_catalogo_para_los_de_basico(): void
    {
        $this->assertNotNull(config('addons.recall_automation'));
        $this->assertTrue(config('addons.recall_automation.available'));
    }

    public function test_recall_aparece_en_la_lista_de_features_del_pro(): void
    {
        $this->assertContains('recall_automation', Clinic::featuresForPlan('profesional'));
        $this->assertNotContains('recall_automation', Clinic::featuresForPlan('basico'));
    }
}
