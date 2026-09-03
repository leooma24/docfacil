<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Corte;
use App\Models\Clinic;
use App\Models\Expense;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Gastos y el corte del mes.
 *
 * DocFácil solo sabía lo que entra. El doctor que quería saber cómo le fue
 * de verdad sacaba sus cobros de aquí y les restaba a mano lo de otra hoja —
 * y por eso la hoja nunca se muere.
 */
class GastosYCorteTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $usuario;

    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed',
        ]);

        $this->usuario = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $this->paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Fernando',
            'last_name' => 'Morales',
            'phone' => '5512345678',
        ]);
    }

    private function gasto(float $monto, string $categoria = 'materiales', ?string $fecha = null): Expense
    {
        return Expense::create([
            'clinic_id' => $this->clinica->id,
            'created_by' => $this->usuario->id,
            'category' => $categoria,
            'concept' => 'Gasto de prueba',
            'amount' => $monto,
            'expense_date' => $fecha ?? now()->toDateString(),
        ]);
    }

    private function cobro(float $monto, string $estado = 'paid', float $abonado = 0, ?string $fecha = null): Payment
    {
        return Payment::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'amount' => $monto,
            'amount_paid' => $abonado,
            'status' => $estado,
            'payment_date' => $fecha ?? now()->toDateString(),
        ]);
    }

    private function corte(): array
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);

        return Livewire::test(Corte::class)->instance()->getNumeros();
    }

    // ── Lo que entra a la caja ───────────────────────────────────

    public function test_un_cobro_liquidado_cuenta_completo(): void
    {
        $this->cobro(5000);

        $this->assertSame(5000.0, Payment::cobradoEntre(
            $this->clinica->id, now()->startOfMonth(), now()->endOfMonth()
        ));
    }

    public function test_un_cobro_a_plazos_cuenta_solo_lo_abonado(): void
    {
        // Antes cada widget hacia where('status','paid')->sum('amount'), asi
        // que un paciente que dio $2,000 de $5,000 contaba como cero y el
        // doctor veia menos ingreso del que habia recibido.
        $this->cobro(5000, 'partial', abonado: 2000);

        $this->assertSame(2000.0, Payment::cobradoEntre(
            $this->clinica->id, now()->startOfMonth(), now()->endOfMonth()
        ));
    }

    public function test_un_cobro_sin_pagar_no_cuenta_como_ingreso(): void
    {
        $this->cobro(5000, 'pending');

        $this->assertSame(0.0, Payment::cobradoEntre(
            $this->clinica->id, now()->startOfMonth(), now()->endOfMonth()
        ));
    }

    public function test_lo_que_deben_se_reporta_aparte(): void
    {
        $this->cobro(5000, 'partial', abonado: 2000);
        $this->cobro(1000, 'pending');

        $this->assertSame(4000.0, Payment::porCobrarEntre(
            $this->clinica->id, now()->startOfMonth(), now()->endOfMonth()
        ));
    }

    // ── Los gastos ───────────────────────────────────────────────

    public function test_suma_los_gastos_del_periodo(): void
    {
        $this->gasto(1500);
        $this->gasto(3000, 'laboratorio');
        $this->gasto(9999, 'renta', now()->subMonths(3)->toDateString());

        $this->assertSame(4500.0, Expense::totalEntre(
            $this->clinica->id, now()->startOfMonth(), now()->endOfMonth()
        ));
    }

    public function test_el_desglose_va_de_mayor_a_menor(): void
    {
        $this->gasto(1500, 'materiales');
        $this->gasto(8000, 'renta');
        $this->gasto(3000, 'laboratorio');

        $porCategoria = Expense::porCategoria(
            $this->clinica->id, now()->startOfMonth(), now()->endOfMonth()
        );

        $this->assertSame(
            ['Renta del local', 'Laboratorio (prótesis, coronas)', 'Materiales dentales'],
            array_keys($porCategoria),
        );
        $this->assertSame(8000.0, $porCategoria['Renta del local']);
    }

    public function test_los_gastos_de_otro_consultorio_no_se_mezclan(): void
    {
        $otra = Clinic::create([
            'name' => 'Otro',
            'slug' => 'otro',
            'plan' => 'basico',
            'onboarding_status' => 'completed',
        ]);

        Expense::withoutGlobalScopes()->create([
            'clinic_id' => $otra->id,
            'category' => 'renta',
            'concept' => 'Renta del vecino',
            'amount' => 99999,
            'expense_date' => now()->toDateString(),
        ]);

        $this->gasto(1500);

        $this->assertSame(1500.0, Expense::totalEntre(
            $this->clinica->id, now()->startOfMonth(), now()->endOfMonth()
        ));
    }

    // ── El corte ─────────────────────────────────────────────────

    public function test_el_corte_resta_los_gastos_a_lo_cobrado(): void
    {
        $this->cobro(20000);
        $this->gasto(8000, 'renta');
        $this->gasto(4500, 'laboratorio');

        $n = $this->corte();

        $this->assertSame(20000.0, $n['ingresos']);
        $this->assertSame(12500.0, $n['gastos']);
        $this->assertSame(7500.0, $n['utilidad']);
    }

    public function test_el_margen_dice_cuanto_quedo_de_cada_cien(): void
    {
        $this->cobro(10000);
        $this->gasto(4000, 'renta');

        $this->assertSame(60.0, round($this->corte()['margen'], 2));
    }

    public function test_sin_ingresos_no_inventa_un_margen(): void
    {
        // Dividir entre cero daria un numero que no significa nada.
        $this->gasto(4000, 'renta');

        $this->assertNull($this->corte()['margen']);
    }

    public function test_el_corte_avisa_cuando_se_gasto_de_mas(): void
    {
        $this->cobro(5000);
        $this->gasto(12000, 'equipo');

        $this->assertSame(-7000.0, $this->corte()['utilidad']);
    }

    public function test_lo_que_deben_no_se_cuenta_como_ingreso_del_corte(): void
    {
        // Meterlo le pintaria al doctor un mes que no tuvo.
        $this->cobro(3000);
        $this->cobro(10000, 'pending');

        $n = $this->corte();

        $this->assertSame(3000.0, $n['ingresos']);
        $this->assertSame(10000.0, $n['por_cobrar']);
    }

    public function test_sin_movimientos_el_corte_se_declara_vacio(): void
    {
        $this->assertFalse($this->corte()['hay_datos']);
    }

    public function test_el_cambio_contra_el_periodo_anterior_no_se_inventa_desde_cero(): void
    {
        // Un mes que arranca de cero siempre saldria "+100%", que no dice nada.
        $this->cobro(5000);

        $this->assertNull($this->corte()['cambio_ingresos']);
    }

    // ── Quién lo puede ver ───────────────────────────────────────

    public function test_los_planes_de_pago_lo_tienen(): void
    {
        $this->assertTrue($this->clinica->hasFeature('expenses'));
        $this->assertContains('expenses', Clinic::featuresForPlan('basico'));
    }

    public function test_free_vencido_no_lo_tiene(): void
    {
        $this->clinica->update([
            'plan' => 'free',
            'plan_ends_at' => null,
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->assertFalse($this->clinica->fresh()->hasFeature('expenses'));
    }

    public function test_durante_la_prueba_de_15_dias_si_lo_tiene(): void
    {
        $this->clinica->update([
            'plan' => 'free',
            'plan_ends_at' => null,
            'trial_ends_at' => now()->addDays(15),
        ]);

        $this->assertTrue($this->clinica->fresh()->hasFeature('expenses'));
    }

    // ── Los que se repiten cada mes ──────────────────────────────

    public function test_la_renta_se_vuelve_a_capturar_al_mes_siguiente(): void
    {
        // Sin esto el doctor la captura doce veces al año y deja de hacerlo
        // al tercero — y en cuanto deja de capturar, el corte miente.
        $renta = $this->gasto(8000, 'renta', now()->subMonthNoOverflow()->toDateString());
        $renta->update(['is_recurring' => true, 'concept' => 'Renta del local']);

        $this->artisan('docfacil:gastos-recurrentes')->assertSuccessful();

        $this->assertSame(2, Expense::withoutGlobalScopes()->count());
        $this->assertSame(8000.0, Expense::totalEntre(
            $this->clinica->id, now()->startOfMonth(), now()->endOfMonth()
        ));
    }

    public function test_correrlo_dos_veces_no_duplica_la_renta(): void
    {
        $renta = $this->gasto(8000, 'renta', now()->subMonthNoOverflow()->toDateString());
        $renta->update(['is_recurring' => true, 'concept' => 'Renta del local']);

        $this->artisan('docfacil:gastos-recurrentes')->assertSuccessful();
        $this->artisan('docfacil:gastos-recurrentes')->assertSuccessful();

        $this->assertSame(2, Expense::withoutGlobalScopes()->count());
    }

    public function test_la_copia_no_se_repite_sola(): void
    {
        // Si la copia heredara la marca, al mes siguiente habria dos, luego
        // cuatro. La marca se queda solo en el original.
        $renta = $this->gasto(8000, 'renta', now()->subMonthNoOverflow()->toDateString());
        $renta->update(['is_recurring' => true, 'concept' => 'Renta del local']);

        $this->artisan('docfacil:gastos-recurrentes')->assertSuccessful();

        $copia = Expense::withoutGlobalScopes()->where('id', '!=', $renta->id)->firstOrFail();

        $this->assertFalse($copia->is_recurring);
    }

    public function test_un_gasto_de_este_mes_todavia_no_se_repite(): void
    {
        $renta = $this->gasto(8000, 'renta');
        $renta->update(['is_recurring' => true]);

        $this->artisan('docfacil:gastos-recurrentes')->assertSuccessful();

        $this->assertSame(1, Expense::withoutGlobalScopes()->count());
    }

    public function test_un_gasto_normal_no_se_repite(): void
    {
        $this->gasto(1500, 'materiales', now()->subMonthNoOverflow()->toDateString());

        $this->artisan('docfacil:gastos-recurrentes')->assertSuccessful();

        $this->assertSame(1, Expense::withoutGlobalScopes()->count());
    }
}
