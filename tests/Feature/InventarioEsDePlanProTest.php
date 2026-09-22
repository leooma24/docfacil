<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\HazardousWasteResource;
use App\Filament\Doctor\Resources\SupplyMovementResource;
use App\Filament\Doctor\Resources\SupplyResource;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El inventario de insumos va en Pro.
 *
 * Es lo que necesita la clínica con varios doctores —donde el material se va
 * sin que nadie sepa quién lo sacó— y es la razón para subir de Básico. Se
 * cierran las dos puertas: la navegación y la URL directa, porque esconder el
 * menú no cierra nada.
 */
class InventarioEsDePlanProTest extends TestCase
{
    use RefreshDatabase;

    private function doctorDePlan(string $plan): User
    {
        $clinica = Clinic::create([
            'name' => 'Consultorio ' . $plan,
            'slug' => 'consultorio-' . $plan,
            'plan' => $plan,
            'plan_ends_at' => $plan === 'free' ? null : now()->addYear(),
            'trial_ends_at' => now()->subDay(),
            'onboarding_status' => 'completed',
        ]);

        $usuario = User::forceCreate([
            'name' => 'Dr. ' . $plan,
            'email' => $plan . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $clinica->id,
        ]);

        Doctor::create([
            'user_id' => $usuario->id,
            'clinic_id' => $clinica->id,
            'specialty' => 'Odontología',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($usuario);

        return $usuario;
    }

    public function test_el_plan_pro_si_lo_trae(): void
    {
        $this->doctorDePlan('profesional');

        $this->assertTrue(SupplyResource::canAccess());
        $this->assertTrue(SupplyMovementResource::canAccess());
        $this->assertTrue(HazardousWasteResource::canAccess());
        $this->assertTrue(SupplyResource::shouldRegisterNavigation());
    }

    public function test_el_plan_clinica_tambien(): void
    {
        $this->doctorDePlan('clinica');

        $this->assertTrue(SupplyResource::canAccess());
    }

    public function test_basico_no_lo_trae(): void
    {
        $this->doctorDePlan('basico');

        $this->assertFalse(SupplyResource::canAccess());
        $this->assertFalse(SupplyMovementResource::canAccess());
        $this->assertFalse(HazardousWasteResource::canAccess());
        $this->assertFalse(SupplyResource::shouldRegisterNavigation());
    }

    public function test_free_tampoco(): void
    {
        $this->doctorDePlan('free');

        $this->assertFalse(SupplyResource::canAccess());
    }

    public function test_sin_el_plan_la_url_directa_tampoco_entra(): void
    {
        $this->doctorDePlan('basico');

        $this->get(SupplyResource::getUrl('index'))->assertForbidden();
        $this->get(HazardousWasteResource::getUrl('index'))->assertForbidden();
    }

    public function test_esta_en_la_lista_de_funciones_de_pro_y_no_en_la_de_basico(): void
    {
        $this->assertContains('inventory', Clinic::featuresForPlan('profesional'));
        $this->assertContains('inventory', Clinic::featuresForPlan('clinica'));
        $this->assertNotContains('inventory', Clinic::featuresForPlan('basico'));
        $this->assertNotContains('inventory', Clinic::featuresForPlan('free'));
    }
}
