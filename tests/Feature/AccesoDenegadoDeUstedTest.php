<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El 403 que ve el doctor (auditoría del 12-oct-2026): hablaba de tú, no
 * decía que podía ser por el plan, "Volver al inicio" lo sacaba a la página
 * de ventas y le enseñaba ligas a "Panel Ventas" y "Administración".
 */
class AccesoDenegadoDeUstedTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    protected function setUp(): void
    {
        parent::setUp();
        // Básico: Consentimientos es del Pro.
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
    }

    private function usuario(string $rol): User
    {
        $u = User::forceCreate(['name' => 'Alguien', 'email' => $rol . '@test.com', 'password' => bcrypt('x'), 'role' => $rol, 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        if ($rol === 'doctor') {
            Doctor::create(['user_id' => $u->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        }

        return $u;
    }

    public function test_al_doctor_le_dice_que_puede_ser_su_plan_y_lo_regresa_a_su_consultorio(): void
    {
        $this->actingAs($this->usuario('doctor'))->get('/doctor/consentimientos')
            ->assertForbidden()
            ->assertSee('no viene en su plan')
            ->assertSee('Volver a mi consultorio')
            ->assertSee('/doctor/actualizar-plan', false)
            ->assertDontSee('Panel Ventas')
            ->assertDontSee('Administración')
            ->assertDontSee('tienes')
            ->assertDontSee('cdn.tailwindcss.com', false);
    }

    public function test_a_la_asistente_le_dice_que_se_lo_pida_al_doctor(): void
    {
        $this->actingAs($this->usuario('staff'))->get('/doctor/clinic-settings')
            ->assertForbidden()
            ->assertSee('Pídale')
            ->assertDontSee('/doctor/actualizar-plan', false);
    }

    public function test_sin_sesion_le_ofrece_entrar(): void
    {
        $this->get('/billing/spei-receipts/999999')->assertRedirect(); // pide sesión
        $html = view('errors.403', ['exception' => new \Symfony\Component\HttpKernel\Exception\HttpException(403)])->render();

        $this->assertStringContainsString('Entrar', $html);
        $this->assertStringNotContainsString('Panel Ventas', $html);
    }
}
