<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Register;
use App\Mail\WelcomeOnboardingMail;
use App\Models\Clinic;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Registrarse y usarlo (11-oct-2026). Romita: «necesitamos un software que
 * llegue y se haga sign up y nada más, nada de ruido» (KISS).
 *
 * Antes eran 7 pantallas y 14 clics a la primera cita: el correo sin
 * verificar no dejaba entrar, un asistente de 5 pasos era obligatorio y
 * llegaban dos correos. Ahora: se registra y cae en su escritorio, con sus
 * servicios ya puestos y lo de todos los días en el menú.
 */
class RegistroSencilloTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        Mail::fake();
        Notification::fake();
    }

    private function registrarse(string $contrasena = 'sonrisas8'): User
    {
        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Dra. Laura Méndez',
                'email' => 'laura@test.com',
                'password' => $contrasena,
                'clinic_name' => 'Consultorio Méndez',
                'terms_accepted' => true,
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        return User::where('email', 'laura@test.com')->firstOrFail();
    }

    public function test_se_registra_con_cuatro_datos_y_una_contrasena_de_8(): void
    {
        $usuario = $this->registrarse();

        $this->assertNotNull($usuario->clinic_id);
    }

    public function test_despues_puede_volver_a_entrar_con_su_contrasena(): void
    {
        $usuario = $this->registrarse('sonrisas8');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('sonrisas8', $usuario->password));
    }

    public function test_entra_directo_a_su_escritorio_sin_verificar_ni_asistente(): void
    {
        $usuario = $this->registrarse();
        $this->assertNull($usuario->email_verified_at);

        $this->actingAs($usuario)->get('/doctor')
            ->assertOk()
            ->assertSee('Confirme su correo');
        $this->actingAs($usuario)->get('/doctor/citas/create')->assertOk();
    }

    public function test_su_consultorio_ya_trae_servicios_y_los_modulos_apagados(): void
    {
        $clinica = Clinic::find($this->registrarse()->clinic_id);

        $this->assertSame('completed', $clinica->onboarding_status);
        $this->assertGreaterThan(0, $clinica->services()->count());
        $this->assertSame([], $clinica->modulos);
    }

    public function test_el_dentista_recien_registrado_ve_su_odontograma(): void
    {
        $this->actingAs($this->registrarse())->get('/doctor')
            ->assertOk()
            ->assertSee('Odontograma');
    }

    public function test_le_llega_un_solo_correo(): void
    {
        $usuario = $this->registrarse();

        Mail::assertNotSent(WelcomeOnboardingMail::class);
        Notification::assertSentTo($usuario, \Filament\Notifications\Auth\VerifyEmail::class);
    }

    public function test_el_escritorio_del_que_va_empezando_no_trae_ruido(): void
    {
        $this->actingAs($this->registrarse());

        // Los bloques del escritorio cargan aparte (lazy): se revisan uno por uno.
        Livewire::test(\App\Filament\Doctor\Widgets\DashboardHeroWidget::class)->assertSee('Agendar mi primera cita');
        foreach ([\App\Filament\Doctor\Widgets\SuMesWidget::class, \App\Filament\Doctor\Widgets\InviteDoctorsWidget::class,
            \App\Filament\Doctor\Widgets\PublicPortalShareWidget::class, \App\Filament\Doctor\Widgets\AlertsWidget::class,
            \App\Filament\Doctor\Widgets\TodayAppointments::class, \App\Filament\Doctor\Widgets\QuickActions::class] as $bloque) {
            $this->assertFalse($bloque::canView(), class_basename($bloque) . ' no debería salir todavía');
        }
    }

    public function test_con_su_primera_cita_ya_sale_todo(): void
    {
        $usuario = $this->registrarse();
        $this->actingAs($usuario);
        $paciente = \App\Models\Patient::create(['clinic_id' => $usuario->clinic_id, 'first_name' => 'Ana', 'last_name' => 'Ruiz']);
        \App\Models\Appointment::create(['clinic_id' => $usuario->clinic_id, 'doctor_id' => $usuario->doctor->id, 'patient_id' => $paciente->id,
            'starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addMinutes(30), 'status' => 'scheduled']);

        $this->assertTrue(\App\Filament\Doctor\Widgets\TodayAppointments::canView());
        $this->assertTrue(\App\Filament\Doctor\Widgets\SuMesWidget::canView());
    }
}
