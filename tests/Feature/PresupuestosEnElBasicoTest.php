<?php

namespace Tests\Feature;

use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los presupuestos eran un add-on de $129/mes que ningún plan traía: el
 * dentista los usaba en la prueba (el video de venta los enseña) y al día 16
 * se le apagaban aunque pagara el Básico. Ahora vienen en todos los planes
 * de pago, y la tienda de add-ons ya no los vende.
 */
class PresupuestosEnElBasicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_basico_pagado_trae_presupuestos(): void
    {
        foreach (['basico', 'profesional', 'clinica'] as $plan) {
            $clinica = Clinic::create(['name' => $plan, 'plan' => $plan, 'plan_ends_at' => now()->addMonth()]);
            $this->assertTrue($clinica->hasFeature('treatment_plans'), $plan);
            $this->assertTrue($clinica->planIncluyeFeature('treatment_plans'), $plan);
        }
    }

    public function test_en_la_prueba_tambien(): void
    {
        $clinica = Clinic::create(['name' => 'Nuevo', 'plan' => 'free', 'trial_ends_at' => now()->addDays(10)]);

        $this->assertTrue($clinica->hasFeature('treatment_plans'));
    }

    public function test_el_free_sin_prueba_no(): void
    {
        $clinica = Clinic::create(['name' => 'Free', 'plan' => 'free', 'trial_ends_at' => now()->subDay()]);

        $this->assertFalse($clinica->hasFeature('treatment_plans'));
    }

    public function test_la_tienda_ya_no_lo_vende(): void
    {
        $this->assertFalse(config('addons.treatment_plans.available'));
    }

    public function test_la_pagina_dice_que_viene_incluido(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Incluido en todos los planes de pago')
            ->assertDontSee('$129');
    }
}
