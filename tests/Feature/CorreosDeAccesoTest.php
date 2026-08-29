<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\ResetPassword;
use Filament\Notifications\Auth\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los correos de verificacion y de contraseña son los primeros que recibe
 * una cuenta nueva. Laravel manda su plantilla por defecto: en ingles
 * ("Hello! Please click the button below...") y sin colores ni marca. En un
 * producto en español eso se ve como phishing.
 */
class CorreosDeAccesoTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $clinic = Clinic::create(['name' => 'Consultorio Test', 'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate([
            'name' => 'Dra. Ana Ruiz',
            'email' => 'ana@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    private function verificacion(): string
    {
        $aviso = new VerifyEmail();
        $aviso->url = 'https://docfacil.tu-app.co/verificar/abc123';

        return $aviso->toMail($this->doctor)->render();
    }

    public function test_el_correo_de_verificacion_va_en_espanol(): void
    {
        $html = $this->verificacion();

        $this->assertStringContainsString('Confirmar mi correo', $html);
        $this->assertStringNotContainsString('Please click the button below', $html);
        $this->assertStringNotContainsString('Regards', $html);
    }

    public function test_el_correo_de_verificacion_trae_la_marca_y_la_liga(): void
    {
        $html = $this->verificacion();

        $this->assertStringContainsString('DocFácil', $html);
        $this->assertStringContainsString('#14b8a6', $html);          // el teal de la marca
        $this->assertStringContainsString('https://docfacil.tu-app.co/verificar/abc123', $html);
    }

    public function test_saluda_por_su_nombre_respetando_el_titulo(): void
    {
        // "Dra. Ana Ruiz" guardado → "Hola Dra. Ana", sin duplicar el titulo
        // ni asumir que es hombre.
        $this->assertStringContainsString('Hola Dra. Ana', $this->verificacion());
    }

    public function test_menciona_los_15_dias_de_prueba(): void
    {
        $this->assertStringContainsString('15 días', $this->verificacion());
    }

    public function test_el_asunto_es_claro_y_en_espanol(): void
    {
        $aviso = new VerifyEmail();
        $aviso->url = 'https://docfacil.tu-app.co/verificar/abc123';

        $this->assertSame(
            'Confirma tu correo para entrar a DocFácil',
            $aviso->toMail($this->doctor)->subject
        );
    }

    public function test_el_correo_de_contrasena_tambien_va_en_espanol(): void
    {
        $mail = (new ResetPassword('token-de-prueba'))->toMail($this->doctor);
        $html = $mail->render();

        $this->assertSame('Restablece tu contraseña de DocFácil', $mail->subject);
        $this->assertStringContainsString('Elegir nueva contraseña', $html);
        $this->assertStringNotContainsString('Reset Password', $html);
        $this->assertStringContainsString('DocFácil', $html);
    }

    /**
     * La liga tiene que llevar a SU panel. Se armaba con
     * Filament::getCurrentPanel(), que desde la cola ya no sabe de donde vino
     * el usuario y cae al panel por defecto: a un doctor le llegaba una liga
     * a /admin, donde ni siquiera puede entrar.
     */
    public static function rolesYPaneles(): array
    {
        return [
            'doctor'  => ['doctor', '/doctor/password-reset/reset'],
            'staff'   => ['staff', '/doctor/password-reset/reset'],
            'paciente' => ['patient', '/paciente/password-reset/reset'],
            'ventas'  => ['sales', '/ventas/password-reset/reset'],
            'admin'   => ['super_admin', '/admin/password-reset/reset'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('rolesYPaneles')]
    public function test_la_liga_de_contrasena_lleva_a_su_panel(string $rol, string $ruta): void
    {
        $usuario = User::forceCreate([
            'name' => 'Persona Prueba',
            'email' => "{$rol}@test.com",
            'password' => bcrypt('password'),
            'role' => $rol,
        ]);

        $html = (new ResetPassword('token-de-prueba'))->toMail($usuario)->render();

        $this->assertStringContainsString($ruta, $html);
    }

    public function test_la_liga_es_correcta_aunque_no_haya_panel_activo(): void
    {
        // Asi corre en la cola: sin peticion HTTP detras.
        Filament::setCurrentPanel(null);

        $html = (new ResetPassword('token-de-prueba'))->toMail($this->doctor)->render();

        $this->assertStringContainsString('/doctor/password-reset/reset', $html);
        $this->assertStringNotContainsString('/admin/password-reset', $html);
    }
}
