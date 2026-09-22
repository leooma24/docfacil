<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Onboarding;
use App\Filament\Doctor\Pages\Register;
use App\Models\Clinic;
use App\Models\User;
use App\Support\ZonaHoraria;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La zona horaria que el consultorio se pone solo.
 *
 * `sugerida()` ya sabía deducir la zona por ciudad y por estado, y eso está
 * probado en ZonaHorariaPorConsultorioTest. Lo que faltaba era el cableado:
 * el estado nunca se guardaba (la columna existía y nadie la escribía), así
 * que el respaldo por estado era código muerto y un consultorio de La Paz o
 * de Nogales nacía con la hora del centro sin que nadie se enterara. Además
 * faltaban dos zonas: los municipios fronterizos que sí usan horario de
 * verano de Estados Unidos.
 */
class ZonaHorariaQueSeDetectaSolaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        Mail::fake();
    }

    private function consultorio(array $atributos = []): Clinic
    {
        return Clinic::create(array_merge([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-' . uniqid(),
            'plan' => 'profesional',
            'plan_ends_at' => now()->addYear(),
            'onboarding_status' => 'completed',
        ], $atributos));
    }

    // ── Las dos zonas que faltaban ───────────────────────────────

    public function test_la_frontera_de_tamaulipas_tiene_su_propia_zona(): void
    {
        // Matamoros, Reynosa y Nuevo Laredo siguen el horario de verano de
        // Estados Unidos: en verano van con Cancún y en invierno con el centro.
        // Ninguna de las seis zonas anteriores podía representar eso.
        foreach (['Matamoros', 'Reynosa', 'Nuevo Laredo', 'Río Bravo'] as $ciudad) {
            $this->assertSame('America/Matamoros', ZonaHoraria::sugerida(null, $ciudad), $ciudad);
        }
    }

    public function test_ojinaga_tiene_su_propia_zona(): void
    {
        $this->assertSame('America/Ojinaga', ZonaHoraria::sugerida(null, 'Ojinaga'));
    }

    public function test_las_dos_zonas_nuevas_se_pueden_elegir_a_mano(): void
    {
        $this->assertArrayHasKey('America/Matamoros', ZonaHoraria::OPCIONES);
        $this->assertArrayHasKey('America/Ojinaga', ZonaHoraria::OPCIONES);
    }

    public function test_tamaulipas_no_es_toda_frontera(): void
    {
        // Tampico y Ciudad Victoria van con la hora del centro. Mapear el
        // estado entero a Matamoros los mandaría una hora adelante medio año.
        $this->assertNull(ZonaHoraria::sugerida('Tamaulipas', 'Tampico'));
        $this->assertNull(ZonaHoraria::sugerida('Tamaulipas', 'Ciudad Victoria'));
    }

    public function test_chihuahua_no_es_toda_ojinaga(): void
    {
        // La capital del estado va con la hora del centro todo el año.
        $this->assertNull(ZonaHoraria::sugerida('Chihuahua', 'Chihuahua'));
        $this->assertNull(ZonaHoraria::sugerida('Chihuahua', 'Delicias'));
    }

    // ── El respaldo por estado, que estaba muerto ────────────────

    public function test_la_paz_sale_del_centro_por_su_estado(): void
    {
        // La Paz hay en BCS y en el Estado de México, por eso no está en la
        // lista de ciudades y depende del estado. Sin estado guardado, un
        // consultorio de La Paz veía todo una hora corrida.
        $consultorio = $this->consultorio([
            'city' => 'La Paz',
            'state' => 'Baja California Sur',
        ]);

        $this->assertSame('America/Mazatlan', ZonaHoraria::delConsultorio($consultorio));
    }

    public function test_un_consultorio_sonorense_sin_ciudad_conocida_usa_sonora(): void
    {
        $consultorio = $this->consultorio([
            'city' => 'Puerto Peñasco',
            'state' => 'Sonora',
        ]);

        $this->assertSame('America/Hermosillo', ZonaHoraria::delConsultorio($consultorio));
    }

    public function test_la_zona_que_eligio_el_doctor_le_gana_al_estado(): void
    {
        $consultorio = $this->consultorio([
            'city' => 'La Paz',
            'state' => 'Baja California Sur',
            'timezone' => 'America/Mexico_City',
        ]);

        $this->assertSame('America/Mexico_City', ZonaHoraria::delConsultorio($consultorio->fresh()));
    }

    // ── El cableado: de la landing de ciudad al registro ─────────

    public function test_la_landing_de_ciudad_manda_ciudad_y_estado_al_registro(): void
    {
        // Antes el botón apuntaba a /doctor/register pelón: ni la ciudad
        // llegaba, así que el registro no tenía de dónde deducir la zona.
        $html = $this->get('/software-dental/tijuana')->assertOk()->getContent();

        $this->assertStringContainsString('register?city=Tijuana', $html);
        $this->assertStringContainsString('state=Baja+California', $html);
    }

    public function test_un_consultorio_que_llega_de_la_landing_nace_a_su_hora(): void
    {
        Livewire::withQueryParams(['city' => 'La Paz', 'state' => 'Baja California Sur'])
            ->test(Register::class)
            ->fillForm([
                'name' => 'Dra. Sudcaliforniana',
                'email' => 'bcs@test.com',
                'password' => 'Secreta123!',
                'passwordConfirmation' => 'Secreta123!',
                'clinic_name' => 'Consultorio La Paz',
                'terms_accepted' => true,
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'bcs@test.com')->firstOrFail();
        $consultorio = Clinic::findOrFail($user->clinic_id);

        $this->assertSame('Baja California Sur', $consultorio->state);
        $this->assertSame('America/Mazatlan', ZonaHoraria::delConsultorio($consultorio));
    }

    // ── El onboarding confirma en vez de adivinar ────────────────

    public function test_el_onboarding_guarda_la_zona_que_confirmo_el_doctor(): void
    {
        $consultorio = $this->consultorio([
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            // Sin completar: con el onboarding ya cerrado, mount() redirige
            // al dashboard y no llega a proponer la zona.
            'onboarding_status' => 'pending',
        ]);

        $doctor = User::forceCreate([
            'name' => 'Dr. Tapatío',
            'email' => 'tapatio@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $consultorio->id,
        ]);

        Livewire::actingAs($doctor)
            ->test(Onboarding::class)
            ->assertSet('clinic_timezone', 'America/Mexico_City')
            // El campo tiene que verse: un default que el doctor no ve es
            // justo el problema que esto viene a arreglar.
            ->assertSee('Zona horaria')
            ->assertSee('Tamaulipas frontera')
            ->set('clinic_timezone', 'America/Mazatlan')
            ->call('completeOnboarding');

        $this->assertSame('America/Mazatlan', $consultorio->fresh()->timezone);
    }
}
