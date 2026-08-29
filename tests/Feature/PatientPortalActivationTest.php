<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientPortalInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * El paciente recibe por WhatsApp una liga firmada, elige su contraseña y
 * queda dentro del portal. Antes no existía forma de que un paciente tuviera
 * cuenta: la pantalla de login no la podía pasar nadie.
 */
class PatientPortalActivationTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Sonrisas',
            'plan' => 'profesional',
            // hasFeature() apaga los features de plan pagado cuando el plan
            // vencio, asi que la vigencia tiene que estar al corriente.
            'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed',
        ]);

        $this->paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'María Elena',
            'last_name' => 'García López',
            'email' => 'maria@example.test',
            'phone' => '6681234567',
        ]);
    }

    // ── El camino feliz ──────────────────────────────────────────

    public function test_el_paciente_activa_su_acceso_y_entra_al_portal(): void
    {
        $this->get(PatientPortalInvite::link($this->paciente))
            ->assertSuccessful()
            ->assertSee('María Elena', false);

        $this->post(PatientPortalInvite::link($this->paciente), [
            'password' => 'micontrasena1',
            'password_confirmation' => 'micontrasena1',
        ])->assertRedirect('/paciente');

        $usuario = User::where('email', 'maria@example.test')->firstOrFail();

        $this->assertSame('patient', $usuario->role);
        $this->assertSame($this->clinica->id, $usuario->clinic_id);
        $this->assertTrue(Hash::check('micontrasena1', $usuario->password));
        $this->assertSame($usuario->id, $this->paciente->fresh()->user_id);
        $this->assertAuthenticatedAs($usuario);
    }

    // ── Lo que tiene que rebotar ─────────────────────────────────

    public function test_sin_firma_no_se_entra(): void
    {
        $this->get("/paciente/activar/{$this->paciente->id}")->assertForbidden();
    }

    public function test_una_liga_vencida_no_sirve(): void
    {
        $link = PatientPortalInvite::link($this->paciente);

        $this->travel(PatientPortalInvite::DIAS_VIGENCIA + 1)->days();

        $this->get($link)->assertForbidden();
    }

    public function test_no_se_puede_activar_dos_veces(): void
    {
        $link = PatientPortalInvite::link($this->paciente);

        $this->post($link, [
            'password' => 'micontrasena1',
            'password_confirmation' => 'micontrasena1',
        ])->assertRedirect('/paciente');

        auth()->logout();

        // Segundo intento: lo mandamos al login, no le creamos otra cuenta.
        $this->get($link)->assertRedirect('/paciente/login');
        $this->assertSame(1, User::where('email', 'maria@example.test')->count());
    }

    public function test_si_el_plan_no_incluye_el_portal_no_activa(): void
    {
        $this->clinica->update(['plan' => 'free']);

        $this->get(PatientPortalInvite::link($this->paciente))->assertForbidden();
    }

    public function test_un_correo_ya_ocupado_no_deja_activar(): void
    {
        User::forceCreate([
            'name' => 'Dr. Alguien',
            'email' => 'maria@example.test',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
        ]);

        $this->get(PatientPortalInvite::link($this->paciente))->assertStatus(409);
    }

    public function test_la_contrasena_debe_confirmarse(): void
    {
        $this->post(PatientPortalInvite::link($this->paciente), [
            'password' => 'micontrasena1',
            'password_confirmation' => 'otracosa123',
        ])->assertSessionHasErrors('password');

        $this->assertNull($this->paciente->fresh()->user_id);
    }

    public function test_la_contrasena_corta_se_rechaza(): void
    {
        $this->post(PatientPortalInvite::link($this->paciente), [
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ])->assertSessionHasErrors('password');

        $this->assertNull($this->paciente->fresh()->user_id);
    }

    // ── La liga de WhatsApp ──────────────────────────────────────

    public function test_el_mensaje_de_whatsapp_lleva_la_liga_y_el_consultorio(): void
    {
        $mensaje = PatientPortalInvite::mensajeWhatsApp($this->paciente);

        $this->assertStringContainsString('María Elena', $mensaje);
        $this->assertStringContainsString('Consultorio Sonrisas', $mensaje);
        $this->assertStringContainsString('/paciente/activar/', $mensaje);
    }

    public function test_el_telefono_de_10_digitos_se_manda_con_lada_de_mexico(): void
    {
        $this->assertStringStartsWith(
            'https://wa.me/526681234567?text=',
            PatientPortalInvite::urlWhatsApp($this->paciente)
        );
    }
}
