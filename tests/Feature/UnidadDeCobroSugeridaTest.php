<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\ServiceResource\Pages\ListServices;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\User;
use App\Support\WorkUnit;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Señalar los servicios cuya unidad de cobro no coincide con su nombre.
 *
 * Cuando se agregó `services.unit`, los servicios que ya existían quedaron en
 * "por visita" — correcto, porque así se comportaban. Pero "Curetaje (por
 * cuadrante)" a $2,500 se sigue cobrando UNA vez aunque sean dos cuadrantes, y
 * el arreglo no llega a los consultorios que ya tenían catálogo.
 *
 * Esto los señala para que el doctor los confirme. Es el mismo patrón de
 * proponer y confirmar que va a usar la receta de insumos.
 */
class UnidadDeCobroSugeridaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-' . uniqid(),
            'onboarding_status' => 'completed',
        ]);

        $this->doctor = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);
    }

    private function servicio(string $nombre, string $unidad = WorkUnit::VISIT, float $precio = 800): Service
    {
        return Service::create([
            'clinic_id' => $this->clinica->id,
            'name' => $nombre,
            'price' => $precio,
            'unit' => $unidad,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
    }

    // ── La pista ─────────────────────────────────────────────────

    public function test_el_nombre_por_cuadrante_sugiere_cuadrante(): void
    {
        $this->assertSame(WorkUnit::QUADRANT, WorkUnit::suggestedFromName('Curetaje (por cuadrante)'));
        $this->assertSame(WorkUnit::QUADRANT, WorkUnit::suggestedFromName('Raspado por 1 cuadrante'));
    }

    public function test_el_nombre_por_pieza_o_por_diente_sugiere_diente(): void
    {
        $this->assertSame(WorkUnit::TOOTH, WorkUnit::suggestedFromName('Carillas de porcelana (por pieza)'));
        $this->assertSame(WorkUnit::TOOTH, WorkUnit::suggestedFromName('Implante por diente'));
    }

    public function test_reconoce_el_nombre_con_acentos_y_mayusculas(): void
    {
        $this->assertSame(WorkUnit::QUADRANT, WorkUnit::suggestedFromName('CURETAJE (POR CUADRANTE)'));
    }

    public function test_un_nombre_que_no_dice_nada_no_sugiere_nada(): void
    {
        // Adivinar aquí sería peor que callarse: el doctor capturaría mal sin
        // darse cuenta, que es justo lo que estamos arreglando.
        foreach ([
            'Resina (obturación)',
            'Limpieza dental',
            'Ortodoncia (mensualidad)',
            'Consulta general',
            'Radiografía panorámica',
            'Guardas dentales',
            'Corona dental porcelana',
        ] as $nombre) {
            $this->assertNull(WorkUnit::suggestedFromName($nombre), $nombre);
        }
    }

    // ── El aviso por servicio ────────────────────────────────────

    public function test_un_servicio_ya_bien_puesto_no_pide_revision(): void
    {
        $curetaje = $this->servicio('Curetaje (por cuadrante)', WorkUnit::QUADRANT, 2500);

        $this->assertFalse($curetaje->needsUnitReview());
    }

    public function test_un_servicio_con_el_nombre_delator_pide_revision(): void
    {
        $curetaje = $this->servicio('Curetaje (por cuadrante)', WorkUnit::VISIT, 2500);

        $this->assertTrue($curetaje->needsUnitReview());
        $this->assertSame(WorkUnit::QUADRANT, $curetaje->suggestedUnit());
    }

    // ── El filtro, que es lo que el doctor usa ───────────────────

    public function test_el_filtro_encuentra_los_que_hay_que_revisar(): void
    {
        $curetaje = $this->servicio('Curetaje (por cuadrante)', WorkUnit::VISIT, 2500);
        $carillas = $this->servicio('Carillas de porcelana (por pieza)', WorkUnit::VISIT, 6000);

        // Ya está bien: no debe aparecer.
        $yaCorregido = $this->servicio('Raspado (por cuadrante)', WorkUnit::QUADRANT, 2500);
        // El nombre no dice nada: no debe aparecer.
        $mudo = $this->servicio('Resina (obturación)', WorkUnit::VISIT, 600);

        Livewire::test(ListServices::class)
            ->filterTable('unit_needs_review')
            ->assertCanSeeTableRecords([$curetaje, $carillas])
            ->assertCanNotSeeTableRecords([$yaCorregido, $mudo]);
    }

    public function test_el_catalogo_sigue_mostrando_todo_sin_el_filtro(): void
    {
        $curetaje = $this->servicio('Curetaje (por cuadrante)', WorkUnit::VISIT, 2500);
        $mudo = $this->servicio('Limpieza dental', WorkUnit::VISIT, 400);

        Livewire::test(ListServices::class)
            ->assertCanSeeTableRecords([$curetaje, $mudo]);
    }
}
