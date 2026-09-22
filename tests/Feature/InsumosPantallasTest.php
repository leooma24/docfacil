<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\ExpenseResource\Pages\ListExpenses;
use App\Filament\Doctor\Resources\SupplyResource\Pages\CreateSupply;
use App\Filament\Doctor\Resources\SupplyResource\Pages\ListSupplies;
use App\Models\Clinic;
use App\Models\Expense;
use App\Models\Supply;
use App\Models\SupplyLot;
use App\Models\SupplyMovement;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Las pantallas de insumos.
 *
 * Lo que se prueba aquí es que el doctor pueda hacer la Fase 1 sin ayuda:
 * dar de alta el catálogo y capturar el primer movimiento desde la tabla,
 * que es donde tiene el insumo enfrente y su stock a la vista.
 */
class InsumosPantallasTest extends TestCase
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

    private function insumo(array $atributos = []): Supply
    {
        return Supply::create(array_merge([
            'clinic_id' => $this->clinica->id,
            'name' => 'Guantes de nitrilo',
            'unit' => 'pieza',
            'purchase_unit' => 'caja',
            'units_per_purchase' => 50,
        ], $atributos));
    }

    public function test_el_catalogo_de_insumos_abre(): void
    {
        $this->get('/doctor/insumos')->assertOk();
    }

    public function test_el_kardex_abre(): void
    {
        $this->get('/doctor/movimientos-insumos')->assertOk();
    }

    public function test_se_puede_dar_de_alta_un_insumo_con_su_conversion(): void
    {
        Livewire::test(CreateSupply::class)
            ->fillForm([
                'name' => 'Composite A2',
                'category' => 'Restaurador',
                'unit' => 'jeringa',
                'purchase_unit' => 'caja',
                'units_per_purchase' => 4,
                'min_stock' => 2,
                'cost_per_unit' => 380,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $insumo = Supply::where('name', 'Composite A2')->firstOrFail();

        $this->assertSame($this->clinica->id, $insumo->clinic_id);
        $this->assertSame('4.000', (string) $insumo->units_per_purchase);
        // Nace en cero: el stock se llena con movimientos, no con el alta.
        $this->assertSame(0.0, $insumo->currentStock());
    }

    public function test_la_tabla_registra_una_entrada_desde_el_insumo(): void
    {
        $insumo = $this->insumo();

        Livewire::test(ListSupplies::class)
            ->callTableAction('movement', $insumo, [
                'type' => 'in',
                'quantity' => 100,
                'unit_cost' => 5,
                'reason' => 'Compra a proveedor',
            ]);

        $this->assertSame(100.0, $insumo->fresh()->currentStock());

        $movimiento = SupplyMovement::where('supply_id', $insumo->id)->firstOrFail();
        $this->assertSame('in', $movimiento->type);
        $this->assertSame('Compra a proveedor', $movimiento->reason);
        $this->assertSame($this->doctor->id, $movimiento->user_id);
    }

    public function test_la_tabla_tambien_registra_una_salida(): void
    {
        $insumo = $this->insumo();
        $insumo->register('in', 100);

        Livewire::test(ListSupplies::class)
            ->callTableAction('movement', $insumo, [
                'type' => 'out',
                'quantity' => 30,
            ]);

        $this->assertSame(70.0, $insumo->fresh()->currentStock());
    }

    public function test_la_merma_pide_motivo_desde_la_tabla(): void
    {
        // Sin motivo no se puede deducir, así que la pantalla no deja pasar.
        $insumo = $this->insumo();
        $insumo->register('in', 100);

        Livewire::test(ListSupplies::class)
            ->callTableAction('movement', $insumo, [
                'type' => 'waste',
                'quantity' => 5,
            ])
            ->assertHasTableActionErrors(['waste_reason']);

        $this->assertSame(100.0, $insumo->fresh()->currentStock());
    }

    public function test_la_merma_con_motivo_si_se_registra(): void
    {
        $insumo = $this->insumo(['cost_per_unit' => 20]);
        $insumo->register('in', 100);

        Livewire::test(ListSupplies::class)
            ->callTableAction('movement', $insumo, [
                'type' => 'waste',
                'quantity' => 5,
                'waste_reason' => 'expired',
            ]);

        $merma = SupplyMovement::where('type', 'waste')->firstOrFail();

        $this->assertSame('expired', $merma->waste_reason);
        $this->assertSame(100.0, $merma->value());
        $this->assertSame(95.0, $insumo->fresh()->currentStock());
    }

    public function test_la_entrada_puede_capturar_lote_y_caducidad(): void
    {
        $insumo = $this->insumo();

        Livewire::test(ListSupplies::class)
            ->callTableAction('movement', $insumo, [
                'type' => 'in',
                'quantity' => 50,
                'lot_number' => 'ABC-123',
                'expires_on' => now()->addDays(10)->toDateString(),
            ]);

        $lote = SupplyLot::where('supply_id', $insumo->id)->firstOrFail();

        $this->assertSame('ABC-123', $lote->lot_number);
        $this->assertSame(50.0, $insumo->fresh()->currentStock());
        // Y el insumo ya avisa de la caducidad.
        $this->assertCount(1, $insumo->lotsExpiringSoon(30));
    }

    public function test_una_entrada_sin_lote_no_crea_lote(): void
    {
        // Los guantes no caducan: capturarles lote sería trabajo sin provecho.
        $insumo = $this->insumo();

        Livewire::test(ListSupplies::class)
            ->callTableAction('movement', $insumo, [
                'type' => 'in',
                'quantity' => 50,
            ]);

        $this->assertSame(0, SupplyLot::count());
        $this->assertSame(50.0, $insumo->fresh()->currentStock());
    }

    public function test_el_kardex_no_deja_editar_ni_borrar(): void
    {
        // Un movimiento es un hecho histórico. Si se pudiera editar, el
        // inventario dejaría de poder creerse — y esa es su única razón de ser.
        $this->assertFalse(\App\Filament\Doctor\Resources\SupplyMovementResource::canCreate());

        $insumo = $this->insumo();
        $insumo->register('in', 10);

        $this->get('/doctor/movimientos-insumos')
            ->assertOk()
            ->assertDontSee('Editar');
    }

    public function test_el_catalogo_no_muestra_los_insumos_de_otro_consultorio(): void
    {
        $otra = Clinic::create([
            'name' => 'Otro Consultorio',
            'slug' => 'otro-' . uniqid(),
            'onboarding_status' => 'completed',
        ]);

        $this->insumo(['name' => 'Insumo Propio']);
        Supply::create([
            'clinic_id' => $otra->id,
            'name' => 'Insumo Ajeno',
            'unit' => 'pieza',
        ]);

        Livewire::test(ListSupplies::class)
            ->assertCanSeeTableRecords(Supply::where('clinic_id', $this->clinica->id)->get())
            ->assertCanNotSeeTableRecords(Supply::where('clinic_id', $otra->id)->get());
    }

    // ── La compra entra desde el gasto ───────────────────────────

    private function gasto(float $monto = 250): Expense
    {
        return Expense::create([
            'clinic_id' => $this->clinica->id,
            'concept' => 'Caja de guantes',
            'category' => 'Insumos',
            'amount' => $monto,
            'expense_date' => now(),
            'supplier' => 'Dental Supply',
        ]);
    }

    public function test_un_gasto_entra_al_inventario_sin_capturarlo_dos_veces(): void
    {
        $insumo = $this->insumo(['name' => 'Guantes de nitrilo', 'units_per_purchase' => 50]);
        $gasto = $this->gasto(250);

        Livewire::test(ListExpenses::class)
            ->callTableAction('supply_entry', $gasto, [
                'supply_id' => $insumo->id,
                'quantity' => 50,
            ]);

        $this->assertSame(50.0, $insumo->fresh()->currentStock());

        $movimiento = SupplyMovement::where('supply_id', $insumo->id)->firstOrFail();
        $this->assertSame('in', $movimiento->type);
        $this->assertSame(Expense::class, $movimiento->reference_type);
        $this->assertSame($gasto->id, $movimiento->reference_id);
        // Caja de 50 a $250: cada guante entra a $5.
        $this->assertEquals(5.00, (float) $movimiento->unit_cost);
        $this->assertStringContainsString('Dental Supply', $movimiento->reason);
    }

    public function test_el_mismo_gasto_no_entra_dos_veces(): void
    {
        // El doctor que se equivoca y vuelve a darle al botón no debe inflar
        // su inventario. La guardia vive en el kardex, no en la memoria de
        // quien captura.
        $insumo = $this->insumo(['units_per_purchase' => 50]);
        $gasto = $this->gasto(250);

        foreach ([1, 2] as $intento) {
            Livewire::test(ListExpenses::class)
                ->callTableAction('supply_entry', $gasto, [
                    'supply_id' => $insumo->id,
                    'quantity' => 50,
                ]);
        }

        $this->assertSame(50.0, $insumo->fresh()->currentStock());
        $this->assertSame(1, SupplyMovement::where('supply_id', $insumo->id)->count());
    }
}
