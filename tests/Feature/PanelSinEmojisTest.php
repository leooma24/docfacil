<?php

namespace Tests\Feature;

use App\Filament\Doctor\Widgets;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El panel del doctor sin emojis: íconos de verdad.
 *
 * Un emoji se ve distinto en cada celular y en cada Windows, y en un sistema
 * para consultorio se ve de juguete. Pendiente desde el 2-oct; en la página
 * de inicio y en los números de arriba de las listas ya se habían quitado.
 * Los signos de texto (✓ ✕ ★ ·) sí se quedan: no son emojis de color.
 */
class PanelSinEmojisTest extends TestCase
{
    use RefreshDatabase;

    private const EMOJI = '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{2604}\x{2606}-\x{26FF}\x{2700}-\x{2712}\x{2714}\x{2716}-\x{27BF}\x{23F0}-\x{23FF}\x{2B50}]/u';

    private User $user;
    private Appointment $cita;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $clinica = Clinic::create(['name' => 'Consultorio', 'slug' => 'consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $clinica->id]);
        $doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567',
            'birth_date' => '1995-11-08', 'gender' => 'female', 'blood_type' => 'B+']);
        $servicio = Service::create(['clinic_id' => $clinica->id, 'name' => 'Limpieza', 'price' => 500, 'duration_minutes' => 30, 'is_active' => true]);
        $this->cita = Appointment::create(['clinic_id' => $clinica->id, 'doctor_id' => $doctor->id, 'patient_id' => $this->ana->id, 'service_id' => $servicio->id,
            'starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2), 'status' => 'confirmed']);
        Payment::create(['clinic_id' => $clinica->id, 'patient_id' => $this->ana->id, 'amount' => 800, 'status' => 'pending',
            'payment_method' => 'cash', 'payment_date' => today()->subDays(20), 'due_date' => today()->subDays(10)]);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->user);
    }

    /** Lo que se ve: sin scripts, estilos ni comentarios. */
    private function emojisEn(string $html): array
    {
        $texto = preg_replace(['/<script\b.*?<\/script>/is', '/<style\b.*?<\/style>/is', '/<!--.*?-->/s'], '', $html);
        preg_match_all(self::EMOJI, strip_tags($texto), $m);

        return array_values(array_unique($m[0]));
    }

    public static function paginas(): array
    {
        return [
            'escritorio' => ['/doctor'],
            'calendario' => ['/doctor/calendario'],
            'add-ons' => ['/doctor/add-ons'],
            'actualizar plan' => ['/doctor/actualizar-plan'],
            'corte' => ['/doctor/corte'],
            'campos de consulta' => ['/doctor/configuracion/campos-consulta'],
            'referidos' => ['/doctor/referidos'],
            'roadmap' => ['/doctor/roadmap'],
            'pago spei' => ['/doctor/pago-spei'],
        ];
    }

    #[DataProvider('paginas')]
    public function test_la_pagina_no_trae_emojis(string $ruta): void
    {
        $respuesta = $this->get($ruta);

        $respuesta->assertOk();
        $this->assertSame([], $this->emojisEn($respuesta->getContent()), "Emojis en {$ruta}");
    }

    public function test_el_perfil_del_paciente_no_trae_emojis(): void
    {
        $respuesta = $this->get('/doctor/perfil-paciente?patient=' . $this->ana->id)->assertOk();
        $this->assertSame([], $this->emojisEn($respuesta->getContent()));
    }

    public function test_la_consulta_no_trae_emojis(): void
    {
        $respuesta = $this->get('/doctor/consulta?appointment=' . $this->cita->id)->assertOk();
        $this->assertSame([], $this->emojisEn($respuesta->getContent()));
    }

    public static function widgets(): array
    {
        return [
            'encabezado del escritorio' => [Widgets\DashboardHeroWidget::class],
            'le deben' => [Widgets\LeDebenWidget::class],
            'portal público' => [Widgets\PublicPortalShareWidget::class],
            'primeros pasos' => [Widgets\SetupChecklistWidget::class],
            'avisos' => [Widgets\AlertsWidget::class],
        ];
    }

    #[DataProvider('widgets')]
    public function test_el_widget_no_trae_emojis(string $widget): void
    {
        $this->assertSame([], $this->emojisEn(Livewire::test($widget)->html()));
    }

    public function test_el_cobro_por_whatsapp_le_habla_de_usted_sin_emojis_ni_llaves(): void
    {
        $html = Livewire::withQueryParams(['appointment' => $this->cita->id])
            ->test(\App\Filament\Doctor\Pages\Consultation::class)
            ->call('goToStep', 4)
            ->html();

        preg_match('/wa\.me\/[^"]*text=([^"&]*)/', $html, $m);
        $mensaje = urldecode(html_entity_decode($m[1] ?? ''));

        $this->assertStringContainsString('le comparto el cobro de su consulta', $mensaje);
        $this->assertStringContainsString('Total: $500.00', $mensaje);
        $this->assertStringNotContainsString('{', $mensaje);
        $this->assertDoesNotMatchRegularExpression(self::EMOJI, $mensaje);
    }

    public function test_los_add_ons_no_prometen_cifras_sin_fuente(): void
    {
        foreach (config('addons') as $addon) {
            $this->assertArrayNotHasKey('revenue_hypothesis', $addon);
        }

        $this->get('/doctor/add-ons')->assertOk()
            ->assertDontSee('$10-30k')
            ->assertDontSee('2-3x')
            ->assertDontSee('Sube 20%');
    }

    /**
     * Lo que sale solo a veces (el cumpleaños de hoy, la lista vacía) no lo
     * ve un recorrido de pantallas: se revisan las vistas mismas.
     */
    public function test_ninguna_vista_del_panel_trae_emojis(): void
    {
        $vistas = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views/filament/doctor')));

        foreach ($vistas as $vista) {
            if (! str_ends_with($vista->getFilename(), '.blade.php')) {
                continue;
            }
            $texto = preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents($vista->getPathname()));
            $this->assertDoesNotMatchRegularExpression(self::EMOJI, $texto, $vista->getPathname());
        }
    }

    public function test_los_avisos_y_botones_no_traen_emojis(): void
    {
        $archivos = [
            'app/Filament/Doctor/Widgets/CalendarWidget.php',
            'app/Filament/Doctor/Pages/Consultation.php',
            'app/Filament/Doctor/Pages/Roadmap.php',
            'app/Filament/Doctor/Resources/ConsentFormResource.php',
        ];

        foreach ($archivos as $archivo) {
            preg_match_all("/->(?:title|label|body)\\('([^']*)'/u", file_get_contents(base_path($archivo)), $m);
            foreach ($m[1] as $texto) {
                $this->assertDoesNotMatchRegularExpression(self::EMOJI, $texto, "{$archivo}: {$texto}");
            }
        }
    }
}
