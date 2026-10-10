<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\CajaDelDia;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Support\CajaDelDia as Caja;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La caja del día, la factura pedida y el recibo.
 *
 * Del dentista (10-oct-2026): "Lupita cuadra la caja contra la libreta y casi
 * nunca cuadra exacto, porque alguien pagó con transferencia y no lo anotó, o
 * alguien pidió factura y quedó pendiente". Y de otro lado: "el paciente dice
 * 'yo ya le había dado 2,000' y hay que hojear la libreta para atrás".
 *
 * La factura la hace su contador (no hay CFDI en DocFácil): aquí solo queda
 * anotado quién la pidió y si ya se le mandó.
 */
class CajaDelDiaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $doctor;
    private Patient $rosa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(18, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'free', 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $this->doctor->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->rosa = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'Valenzuela', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);
    }

    private function cobro(float $monto, string $metodo = 'cash', ?Patient $paciente = null, string $estado = 'paid', float $pagado = 0, array $extra = []): Payment
    {
        return Payment::create(array_merge([
            'clinic_id' => $this->clinica->id, 'patient_id' => ($paciente ?? $this->rosa)->id,
            'amount' => $monto, 'amount_paid' => $estado === 'paid' ? $monto : $pagado, 'status' => $estado,
            'payment_method' => $metodo, 'payment_date' => today(),
        ], $extra));
    }

    // ── La caja ─────────────────────────────────────────────────

    public function test_junta_lo_que_entro_hoy_por_forma_de_pago(): void
    {
        $this->cobro(500, 'cash');
        $this->cobro(300, 'cash');
        $this->cobro(1200, 'card');
        $this->cobro(800, 'transfer');

        $caja = Caja::de($this->clinica->id, today());

        $this->assertSame(500.0 + 300.0, $caja['porMetodo']['cash']);
        $this->assertSame(1200.0, $caja['porMetodo']['card']);
        $this->assertSame(800.0, $caja['porMetodo']['transfer']);
        $this->assertSame(2800.0, $caja['total']);
        $this->assertCount(4, $caja['movimientos']);
    }

    public function test_un_abono_de_hoy_entra_hoy_aunque_el_cobro_sea_de_antes(): void
    {
        $cobro = $this->cobro(4500, 'cash', null, 'partial', 1000, ['payment_date' => today()->subDays(20)]);
        $cobro->registrarAbono(500, 'transfer');

        $hoy = Caja::de($this->clinica->id, today());

        $this->assertSame(500.0, $hoy['total']);
        $this->assertSame(500.0, $hoy['porMetodo']['transfer']);
        $this->assertSame(0.0, $hoy['porMetodo']['cash'], 'Los $1,000 del abono de hace 20 días no son de hoy.');
    }

    public function test_otro_dia_otro_consultorio_no_se_mezclan(): void
    {
        $this->cobro(500, 'cash');
        $ayer = $this->cobro(900, 'cash');
        $ayer->receipts()->update(['paid_at' => now()->subDay()]);

        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'free', 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $pOtra = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'X']);
        Payment::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'patient_id' => $pOtra->id, 'amount' => 7000, 'amount_paid' => 7000,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);

        $this->assertSame(500.0, Caja::de($this->clinica->id, today())['total']);
        $this->assertSame(900.0, Caja::de($this->clinica->id, today()->subDay())['total']);
    }

    // ── Factura: quién la pidió y si ya se mandó ────────────────

    public function test_la_factura_pedida_queda_pendiente_hasta_que_se_manda(): void
    {
        $cobro = $this->cobro(1500, 'transfer');

        Livewire::test(CajaDelDia::class)
            ->assertDontSee('Facturas por mandar')
            ->call('pidioFactura', $cobro->id)
            ->assertSee('Facturas por mandar')
            ->assertSee('Rosa Valenzuela')
            ->call('facturaEnviada', $cobro->id)
            ->assertDontSee('Facturas por mandar');

        $cobro->refresh();
        $this->assertTrue($cobro->factura_solicitada);
        $this->assertTrue($cobro->factura_enviada_at->isSameMinute(now()));
    }

    public function test_la_factura_pendiente_se_ve_aunque_el_cobro_sea_de_otro_dia(): void
    {
        $viejo = $this->cobro(900, 'cash', null, 'paid', 0, ['payment_date' => today()->subDays(12), 'factura_solicitada' => true]);

        Livewire::test(CajaDelDia::class)->assertSee('Facturas por mandar')->assertSee('Rosa Valenzuela');
    }

    public function test_no_toca_cobros_de_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'free', 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $pOtra = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'X']);
        $ajeno = Payment::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'patient_id' => $pOtra->id, 'amount' => 100, 'amount_paid' => 100,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);

        Livewire::test(CajaDelDia::class)->call('pidioFactura', $ajeno->id);

        $this->assertFalse((bool) Payment::withoutGlobalScopes()->find($ajeno->id)->factura_solicitada);
    }

    // ── La pantalla y la asistente ──────────────────────────────

    public function test_la_pantalla_dice_cuanto_entro_por_cada_forma(): void
    {
        $this->cobro(500, 'cash');
        $this->cobro(1200, 'card');

        Livewire::test(CajaDelDia::class)
            ->assertSee('Efectivo')->assertSee('$500')
            ->assertSee('Tarjeta')->assertSee('$1,200')
            ->assertSee('Total del día')->assertSee('$1,700');
    }

    public function test_la_asistente_la_ve_aunque_no_vea_el_corte_del_mes(): void
    {
        $lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id, 've_dinero' => false]);
        $this->actingAs($lupita);

        $this->get(CajaDelDia::getUrl())->assertOk();
        $this->get('/doctor/corte')->assertForbidden();
    }

    // ── El recibo ───────────────────────────────────────────────

    public function test_el_recibo_trae_lo_pagado_y_el_saldo(): void
    {
        $cobro = $this->cobro(4500, 'cash', null, 'partial', 2000);
        $cobro->registrarAbono(500, 'transfer');

        $r = $this->get(route('cobro.recibo', $cobro));

        $r->assertOk();
        $this->assertSame('application/pdf', $r->headers->get('Content-Type'));
    }

    public function test_el_recibo_de_otro_consultorio_no_se_abre(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'free', 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $pOtra = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'X']);
        $ajeno = Payment::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'patient_id' => $pOtra->id, 'amount' => 100, 'amount_paid' => 100,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);

        $this->get(route('cobro.recibo', $ajeno->id))->assertNotFound();
    }

    public function test_el_contenido_del_recibo_dice_lo_que_el_paciente_pregunta(): void
    {
        $cobro = $this->cobro(4500, 'cash', null, 'partial', 2000);
        $cobro->registrarAbono(500, 'transfer');

        $texto = view('pdf.recibo', Caja::datosDelRecibo($cobro->fresh()))->render();

        $this->assertStringContainsString('Rosa Valenzuela', $texto);
        $this->assertStringContainsString('$4,500', $texto);   // lo que cuesta
        $this->assertStringContainsString('$2,500', $texto);   // lo que lleva pagado
        $this->assertStringContainsString('$2,000', $texto);   // lo que falta
        $this->assertStringContainsString('Consultorio Sonrisas', $texto);
        $this->assertStringNotContainsStringIgnoringCase('factura fiscal', $texto, 'Es un recibo, no una factura (CFDI).');
    }
}
