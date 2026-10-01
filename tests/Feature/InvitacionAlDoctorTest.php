<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\InvitacionAlDoctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * La liga con la que un doctor estrena su cuenta.
 *
 * Cuando se le deja el consultorio armado —el onboarding 1 a 1 que se le
 * promete a los fundadores— hay que darle una entrada. Se estaba usando la de
 * "olvidé mi contraseña", que dura 60 minutos por diseño: sirve para quien la
 * acaba de pedir, no para una doctora que dice "lo checo saliendo de consulta".
 *
 * Eso no es hipotético. La primera fundadora abrió su liga cinco horas después
 * de que se generó, se sentó a hacerlo, y el sistema la rebotó dos veces sin
 * decirle por qué. La bitácora del servidor tiene los dos intentos.
 *
 * Esta liga dura una semana y es de un solo uso. Es la misma idea que la
 * invitación del paciente, que ya existe y funciona. El reset de contraseña se
 * queda en 60 minutos, como debe ser: son cosas distintas.
 */
class InvitacionAlDoctorTest extends TestCase
{
    use RefreshDatabase;

    private User $doctora;

    protected function setUp(): void
    {
        parent::setUp();

        $clinica = Clinic::create([
            'name' => 'Consultorio Dra. Prueba',
            'slug' => 'consultorio-dra-prueba',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addMonths(6),
            'onboarding_status' => 'completed',
        ]);

        $this->doctora = User::forceCreate([
            'name' => 'Dra. Prueba',
            'email' => 'doctora@ejemplo.com',
            // Al azar, como en el alta de verdad: nadie la conoce, ni nosotros.
            'password' => Hash::make(Str::random(32)),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $clinica->id,
        ]);
    }

    // ── Que aguante hasta que la abra ────────────────────────────

    public function test_la_liga_sirve_horas_despues(): void
    {
        // Las cinco horas que tardó la Dra. Ramírez en abrirla.
        $liga = InvitacionAlDoctor::liga($this->doctora);

        $this->travel(5)->hours();

        $this->get($liga)->assertOk();
    }

    public function test_la_liga_sirve_dias_despues(): void
    {
        $liga = InvitacionAlDoctor::liga($this->doctora);

        $this->travel(6)->days();

        $this->get($liga)->assertOk();
    }

    public function test_pasada_la_semana_ya_no_sirve(): void
    {
        // No es para siempre: si nunca la usó, se cierra.
        $liga = InvitacionAlDoctor::liga($this->doctora);

        $this->travel(InvitacionAlDoctor::DIAS_VIGENCIA + 1)->days();

        $this->get($liga)->assertForbidden();
    }

    // ── Que solo entre quien debe ────────────────────────────────

    public function test_sin_firma_no_entra(): void
    {
        $this->get("/doctor/estrenar/{$this->doctora->id}")->assertForbidden();
    }

    public function test_con_la_firma_manoseada_no_entra(): void
    {
        $liga = InvitacionAlDoctor::liga($this->doctora);

        $this->get(substr($liga, 0, -4) . 'aaaa')->assertForbidden();
    }

    // ── La pantalla ──────────────────────────────────────────────

    public function test_la_pantalla_la_saluda_y_nombra_su_consultorio(): void
    {
        $this->get(InvitacionAlDoctor::liga($this->doctora))
            ->assertOk()
            ->assertSee('Dra. Prueba')
            ->assertSee('Consultorio Dra. Prueba');
    }

    // ── Elegir la contraseña ─────────────────────────────────────

    public function test_elige_su_contrasena_y_queda_dentro(): void
    {
        $this->post(InvitacionAlDoctor::liga($this->doctora), [
            'password' => 'LaQueEllaEscoja123',
            'password_confirmation' => 'LaQueEllaEscoja123',
        ])->assertRedirect('/doctor');

        $this->assertTrue(Hash::check('LaQueEllaEscoja123', $this->doctora->fresh()->password));
        $this->assertAuthenticatedAs($this->doctora->fresh());
    }

    public function test_una_contrasena_corta_no_pasa(): void
    {
        $this->post(InvitacionAlDoctor::liga($this->doctora), [
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_si_no_la_confirma_igual_no_pasa(): void
    {
        $this->post(InvitacionAlDoctor::liga($this->doctora), [
            'password' => 'UnaBuenaClave123',
            'password_confirmation' => 'OtraDistinta123',
        ])->assertSessionHasErrors('password');
    }

    // ── De un solo uso ───────────────────────────────────────────

    public function test_la_liga_ya_usada_no_sirve_otra_vez(): void
    {
        // Si el mensaje se reenvía o alguien más lo ve, no le abre la cuenta
        // de la doctora a un tercero.
        $liga = InvitacionAlDoctor::liga($this->doctora);

        $this->post($liga, [
            'password' => 'LaPrimera123',
            'password_confirmation' => 'LaPrimera123',
        ]);

        auth()->logout();

        $this->get($liga)->assertForbidden();

        $this->post($liga, [
            'password' => 'LaSegunda123',
            'password_confirmation' => 'LaSegunda123',
        ])->assertForbidden();

        // La que ella eligió sigue siendo la buena.
        $this->assertTrue(Hash::check('LaPrimera123', $this->doctora->fresh()->password));
    }

    // ── El mensaje que se le manda ───────────────────────────────

    public function test_el_mensaje_trae_la_liga_y_dice_cuanto_dura(): void
    {
        $mensaje = InvitacionAlDoctor::mensajeWhatsApp($this->doctora);

        $this->assertStringContainsString('/doctor/estrenar/', $mensaje);
        $this->assertStringContainsString('Consultorio Dra. Prueba', $mensaje);
        $this->assertStringContainsString((string) InvitacionAlDoctor::DIAS_VIGENCIA, $mensaje);
    }
}
