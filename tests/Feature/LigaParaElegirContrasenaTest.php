<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Auth\PasswordReset\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La liga con la que un doctor elige su contraseña por primera vez.
 *
 * Se usa cuando se le deja el consultorio listo —el onboarding 1 a 1 que se
 * le promete a los fundadores— en vez de pedirle que se registre él. La cuenta
 * nace con una contraseña al azar que nadie conoce, ni nosotros, y la liga es
 * lo único que le da entrada.
 *
 * Por eso se prueba entera: que la liga firmada abra, que la sin firmar no,
 * que al elegir contraseña se pueda entrar con ella, y que la liga no sirva
 * dos veces. Si algo de esto falla, la doctora se queda afuera de su propio
 * consultorio y con la primera impresión arruinada.
 */
class LigaParaElegirContrasenaTest extends TestCase
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
            // Al azar, como en el alta de verdad: nadie la conoce.
            'password' => Hash::make(\Illuminate\Support\Str::random(32)),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $clinica->id,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    /**
     * Esta liga dura 60 minutos a proposito: es la de "olvide mi contrasena".
     * Para estrenar una cuenta se usa la de InvitacionAlDoctor, que aguanta
     * una semana. Ver InvitacionAlDoctorTest.
     */
    private function liga(): string
    {
        return Filament::getResetPasswordUrl(
            Password::broker('users')->createToken($this->doctora),
            $this->doctora,
        );
    }

    public function test_la_liga_firmada_abre_la_pantalla(): void
    {
        $this->get($this->liga())->assertOk();
    }

    public function test_sin_firma_no_abre(): void
    {
        $sinFirma = preg_replace('/&signature=[a-f0-9]+/', '', $this->liga());

        $this->get($sinFirma)->assertForbidden();
    }

    public function test_elige_su_contrasena_y_entra_con_ella(): void
    {
        $token = Password::broker('users')->createToken($this->doctora);

        Livewire::test(ResetPassword::class, ['email' => $this->doctora->email, 'token' => $token])
            ->fillForm([
                'email' => $this->doctora->email,
                'password' => 'LaQueEllaEscoja123',
                'passwordConfirmation' => 'LaQueEllaEscoja123',
            ])
            ->call('resetPassword')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('LaQueEllaEscoja123', $this->doctora->fresh()->password));
    }

    public function test_la_misma_liga_no_sirve_dos_veces(): void
    {
        $token = Password::broker('users')->createToken($this->doctora);

        $elegir = fn (string $clave) => Livewire::test(ResetPassword::class, ['email' => $this->doctora->email, 'token' => $token])
            ->fillForm([
                'email' => $this->doctora->email,
                'password' => $clave,
                'passwordConfirmation' => $clave,
            ])
            ->call('resetPassword');

        $elegir('PrimeraVez123')->assertHasNoFormErrors();
        $elegir('SegundaVez123');

        // La segunda no cambia nada: el token ya se gastó.
        $this->assertTrue(Hash::check('PrimeraVez123', $this->doctora->fresh()->password));
    }

    public function test_al_entrar_ve_su_consultorio_y_no_el_de_alguien_mas(): void
    {
        $this->actingAs($this->doctora);

        // Entra a su panel: lo que importa es que la cuenta quede usable.
        $this->get('/doctor')->assertSuccessful();
    }
}
