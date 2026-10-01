<?php

namespace Tests\Feature;

use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El presupuesto es lo que convierte el odontograma en dinero para el
 * consultorio, así que en la prueba de 15 días se ve funcionando aunque
 * después sea un add-on.
 */
class PruebaIncluyePresupuestosTest extends TestCase
{
    use RefreshDatabase;

    public function test_en_la_prueba_se_pueden_armar_presupuestos(): void
    {
        $clinica = Clinic::create(['name' => 'Nuevo', 'plan' => 'free', 'trial_ends_at' => now()->addDays(10)]);

        $this->assertTrue($clinica->hasFeature('treatment_plans'));
    }

    public function test_al_vencer_la_prueba_vuelve_a_ser_add_on(): void
    {
        $clinica = Clinic::create(['name' => 'Nuevo', 'plan' => 'free', 'trial_ends_at' => now()->subDay()]);

        $this->assertFalse($clinica->hasFeature('treatment_plans'));
    }

    public function test_un_plan_pagado_sin_el_add_on_no_lo_trae(): void
    {
        $clinica = Clinic::create(['name' => 'Pro', 'plan' => 'profesional', 'plan_ends_at' => now()->addYear()]);

        $this->assertFalse($clinica->hasFeature('treatment_plans'));
    }
}
