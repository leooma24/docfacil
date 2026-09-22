<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\HazardousWasteResource\Pages\CreateHazardousWaste;
use App\Filament\Doctor\Resources\HazardousWasteResource\Pages\ListHazardousWastes;
use App\Models\Clinic;
use App\Models\HazardousWaste;
use App\Models\Supply;
use App\Models\SupplyMovement;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El registro de residuos peligrosos.
 *
 * Lo que más importa aquí es lo que NO pasa: registrar un residuo no puede
 * tocar el inventario. Ese material ya salió del kardex cuando se mezcló, así
 * que descontarlo otra vez desviaría el stock en silencio — y el error sería
 * invisible hasta que el conteo físico no cuadrara.
 *
 * Por eso vive en su propia tabla y no como un tipo de merma.
 */
class ResiduosPeligrososTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        // El inventario va en Pro.
        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-' . uniqid(),
            'plan' => 'profesional',
            'plan_ends_at' => now()->addYear(),
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

    private function residuo(array $atributos = []): HazardousWaste
    {
        return HazardousWaste::create(array_merge([
            'clinic_id' => $this->clinica->id,
            'material' => 'leftover_amalgam',
            'quantity' => 2.5,
            'unit' => 'gramo',
            'disposed_on' => now(),
        ], $atributos));
    }

    // ── Lo que NO pasa ───────────────────────────────────────────

    public function test_registrar_un_residuo_no_toca_el_inventario(): void
    {
        $amalgama = Supply::create([
            'clinic_id' => $this->clinica->id,
            'name' => 'Amalgama',
            'category' => 'Restaurador',
            'unit' => 'gramo',
        ]);

        $amalgama->register('in', 100);
        $this->assertSame(100.0, $amalgama->fresh()->currentStock());

        $this->residuo(['supply_id' => $amalgama->id, 'material' => 'extracted_amalgam', 'quantity' => 10]);

        // El stock no se mueve: ese material ya había salido cuando se mezcló.
        $this->assertSame(100.0, $amalgama->fresh()->currentStock());
    }

    public function test_registrar_un_residuo_no_crea_ningun_movimiento(): void
    {
        $this->residuo();

        // Ni salida ni merma: el kardex no lo ve.
        $this->assertSame(0, SupplyMovement::count());
    }

    public function test_la_tabla_de_residuos_es_aparte_de_la_de_movimientos(): void
    {
        // Si el residuo normativo estuviera en supply_movements, la suma del
        // stock tendría que saber excluirlo. Una resta que se olvida una vez
        // deja el inventario desviado para siempre.
        $this->assertNotContains('normativa', \App\Models\SupplyMovement::WASTE_REASONS);

        $columnas = \Illuminate\Support\Facades\Schema::getColumnListing('hazardous_wastes');

        $this->assertContains('material', $columnas);
        $this->assertContains('quantity', $columnas);
        $this->assertContains('manifest_number', $columnas);
    }

    // ── El registro ──────────────────────────────────────────────

    public function test_el_material_se_guarda_como_categoria(): void
    {
        $residuo = $this->residuo(['material' => 'mercury']);

        $this->assertSame('Mercurio', $residuo->fresh()->materialLabel());
    }

    public function test_los_materiales_que_la_norma_obliga_a_separar_estan(): void
    {
        $this->assertArrayHasKey('leftover_amalgam', HazardousWaste::MATERIALS);
        $this->assertArrayHasKey('extracted_amalgam', HazardousWaste::MATERIALS);
        $this->assertArrayHasKey('mercury', HazardousWaste::MATERIALS);
        $this->assertArrayHasKey('biological', HazardousWaste::MATERIALS);
    }

    public function test_detecta_cuando_falta_el_manifest_number(): void
    {
        // Sin el manifiesto del recolector, el registro no demuestra nada.
        $this->assertTrue($this->residuo()->isMissingManifest());
        $this->assertTrue($this->residuo(['manifest_number' => ''])->isMissingManifest());
        $this->assertFalse($this->residuo(['manifest_number' => 'MAN-2026-001'])->isMissingManifest());
    }

    public function test_los_residuos_quedan_aislados_por_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otro', 'slug' => 'otro-' . uniqid(), 'onboarding_status' => 'completed']);

        $this->residuo();

        HazardousWaste::create([
            'clinic_id' => $otra->id,
            'material' => 'mercury',
            'quantity' => 1,
            'unit' => 'gramo',
            'disposed_on' => now(),
        ]);

        $this->assertSame(1, HazardousWaste::count());
        $this->assertSame('leftover_amalgam', HazardousWaste::first()->material);
    }

    // ── La pantalla ──────────────────────────────────────────────

    public function test_la_pantalla_de_residuos_abre(): void
    {
        $this->get('/doctor/residuos')->assertOk();
    }

    public function test_se_puede_registrar_un_residuo_desde_la_pantalla(): void
    {
        Livewire::test(CreateHazardousWaste::class)
            ->fillForm([
                'material' => 'extracted_amalgam',
                'quantity' => 3.2,
                'unit' => 'gramo',
                'disposed_on' => now()->toDateString(),
                'container' => 'Frasco de amalgama',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $residuo = HazardousWaste::firstOrFail();

        $this->assertSame($this->clinica->id, $residuo->clinic_id);
        $this->assertSame($this->doctor->id, $residuo->user_id);
        $this->assertSame('extracted_amalgam', $residuo->material);
    }

    public function test_la_lista_muestra_los_residuos_del_consultorio(): void
    {
        $residuo = $this->residuo();

        Livewire::test(ListHazardousWastes::class)
            ->assertCanSeeTableRecords([$residuo]);
    }
}
