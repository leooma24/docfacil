<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Login;
use App\Models\Clinic;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

/**
 * La autenticación de dos pasos se pide al entrar.
 *
 * La pantalla de Seguridad dejaba activarla y prometía pedir un código al
 * iniciar sesión, pero el login nunca lo pedía: con la contraseña bastaba.
 */
class DosPasosAlEntrarTest extends TestCase
{
    use RefreshDatabase;

    private string $secreto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->secreto = (new Google2FA())->generateSecretKey();

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    private function doctor(bool $conDosPasos): User
    {
        $clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test-' . ($conDosPasos ? 'si' : 'no'),
            'plan' => 'profesional',
            'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed',
        ]);

        $usuario = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('contrasena-segura'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $clinica->id,
        ]);

        if ($conDosPasos) {
            $usuario->forceFill([
                'two_factor_secret' => $this->secreto,
                'two_factor_enabled' => true,
                'two_factor_confirmed_at' => now(),
            ])->save();
        }

        return $usuario;
    }

    private function conContrasena(): Testable
    {
        return Livewire::test(Login::class)
            ->fillForm(['email' => 'doctor@test.com', 'password' => 'contrasena-segura'])
            ->call('authenticate');
    }

    private function codigoActual(): string
    {
        return (new Google2FA())->getCurrentOtp($this->secreto);
    }

    public function test_sin_dos_pasos_entra_con_la_contrasena(): void
    {
        $usuario = $this->doctor(conDosPasos: false);

        $this->conContrasena()->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_con_dos_pasos_la_contrasena_sola_no_abre_la_sesion(): void
    {
        $this->doctor(conDosPasos: true);

        $this->conContrasena()->assertSet('pidiendoCodigo', true);

        $this->assertGuest();
    }

    public function test_con_el_codigo_correcto_entra(): void
    {
        $usuario = $this->doctor(conDosPasos: true);

        $this->conContrasena()
            ->fillForm(['code' => $this->codigoActual()])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_con_un_codigo_equivocado_no_entra(): void
    {
        $this->doctor(conDosPasos: true);

        $equivocado = str_pad((string) (((int) $this->codigoActual() + 500000) % 1000000), 6, '0', STR_PAD_LEFT);

        $this->conContrasena()
            ->fillForm(['code' => $equivocado])
            ->call('authenticate')
            ->assertHasFormErrors(['code']);

        $this->assertGuest();
    }

    public function test_con_la_contrasena_equivocada_ni_siquiera_pide_el_codigo(): void
    {
        $this->doctor(conDosPasos: true);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'doctor@test.com', 'password' => 'otra-cosa'])
            ->call('authenticate')
            ->assertHasFormErrors(['email'])
            ->assertSet('pidiendoCodigo', false);

        $this->assertGuest();
    }

    public function test_sin_haber_puesto_la_contrasena_un_codigo_no_sirve_de_nada(): void
    {
        // Brincarse el primer paso desde el navegador no debe abrir nada:
        // quién puso bien la contraseña vive en la sesión, no en la página.
        $this->doctor(conDosPasos: true);

        Livewire::test(Login::class)
            ->set('pidiendoCodigo', true)
            ->fillForm(['code' => $this->codigoActual()])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }
}
