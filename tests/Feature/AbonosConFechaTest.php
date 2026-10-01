<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\PaymentResource\Pages\ListPayments;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cada abono es un pago con su fecha y su forma de pago.
 *
 * Antes un abono solo subía "pagado" en el cobro original, así que el
 * dinero que entraba hoy se contaba el día del cobro: el corte del día no
 * cuadraba con el efectivo de la caja, y no quedaba cuándo ni cómo pagó
 * el paciente.
 */
class AbonosConFechaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $doctor;
    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-01 12:00:00');

        $this->clinica = Clinic::create(['name' => 'Consultorio Test', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate([
            'name' => 'Dr. Test', 'email' => 'doctor@test.com', 'password' => bcrypt('password'),
            'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id,
        ]);
        $this->paciente = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Diego', 'last_name' => 'Salazar']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function cobro(float $monto, string $estado = 'pending', float $abonado = 0, string $fecha = '2026-09-28'): Payment
    {
        return Payment::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'amount' => $monto,
            'amount_paid' => $abonado,
            'status' => $estado,
            'payment_method' => 'cash',
            'payment_date' => $fecha,
        ]);
    }

    private function cobradoEl(string $dia): float
    {
        return Payment::cobradoEntre($this->clinica->id, Carbon::parse($dia), Carbon::parse($dia));
    }

    public function test_el_abono_de_hoy_cuenta_hoy_aunque_el_cobro_sea_de_otro_dia(): void
    {
        $cobro = $this->cobro(21000, 'partial', abonado: 5000);

        $cobro->registrarAbono(800, 'transfer');

        $this->assertEquals(800, $this->cobradoEl('2026-10-01'));
        $this->assertEquals(5000, $this->cobradoEl('2026-09-28'));
    }

    public function test_cada_abono_queda_con_su_fecha_y_forma_de_pago(): void
    {
        $cobro = $this->cobro(21000, 'partial', abonado: 5000);

        $cobro->registrarAbono(800, 'transfer', 'Mensualidad de octubre');

        $abonos = $cobro->receipts()->orderBy('paid_at')->get();
        $this->assertCount(2, $abonos);
        $this->assertSame('2026-09-28', $abonos[0]->paid_at->toDateString());
        $this->assertEquals(5000, (float) $abonos[0]->amount);
        $this->assertSame('2026-10-01', $abonos[1]->paid_at->toDateString());
        $this->assertSame('transfer', $abonos[1]->payment_method);
        $this->assertSame('Mensualidad de octubre', $abonos[1]->notes);
        $this->assertEquals(5800, (float) $cobro->fresh()->amount_paid);
        $this->assertSame('partial', $cobro->fresh()->status);
    }

    public function test_el_ultimo_abono_lo_liquida(): void
    {
        $cobro = $this->cobro(1500, 'partial', abonado: 1000);

        $cobro->registrarAbono(500, 'cash');

        $this->assertSame('paid', $cobro->fresh()->status);
        $this->assertEquals(0, $cobro->fresh()->remaining);
    }

    public function test_no_se_abona_mas_de_lo_que_se_debe(): void
    {
        $cobro = $this->cobro(1500, 'partial', abonado: 1000);

        $this->expectException(\InvalidArgumentException::class);
        $cobro->registrarAbono(600, 'cash');
    }

    public function test_un_cobro_pagado_al_momento_cuenta_el_dia_del_cobro(): void
    {
        $this->cobro(600, 'paid', fecha: '2026-09-30');

        $this->assertEquals(600, $this->cobradoEl('2026-09-30'));
        $this->assertEquals(0, $this->cobradoEl('2026-10-01'));
    }

    public function test_marcarlo_pagado_a_mano_registra_lo_que_faltaba_hoy(): void
    {
        $cobro = $this->cobro(1500, 'partial', abonado: 1000);

        $cobro->update(['status' => 'paid', 'amount_paid' => 1500]);

        $this->assertEquals(500, $this->cobradoEl('2026-10-01'));
        $this->assertEquals(1500, (float) $cobro->receipts()->sum('amount'));
    }

    public function test_los_cobros_de_antes_se_pasan_a_abonos_sin_perder_nada(): void
    {
        // Como quedaron en producción: sin un solo recibo.
        $pagado = $this->cobro(600, 'paid', fecha: '2026-09-10');
        $parcial = $this->cobro(2500, 'partial', abonado: 1000, fecha: '2026-09-12');
        PaymentReceipt::query()->delete();

        PaymentReceipt::respaldarCobrosSinRecibos();
        PaymentReceipt::respaldarCobrosSinRecibos(); // dos veces no duplica

        $this->assertEquals(600, (float) $pagado->receipts()->sum('amount'));
        $this->assertEquals(1000, (float) $parcial->receipts()->sum('amount'));
        $this->assertEquals(600, $this->cobradoEl('2026-09-10'));
        $this->assertEquals(1000, $this->cobradoEl('2026-09-12'));
    }

    public function test_el_boton_de_abono_registra_la_forma_de_pago(): void
    {
        $cobro = $this->cobro(21000, 'partial', abonado: 5000);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        Livewire::test(ListPayments::class)
            ->callTableAction('pay_installment', $cobro, data: ['installment' => 800, 'payment_method' => 'card']);

        $this->assertSame('card', $cobro->receipts()->latest('id')->value('payment_method'));
        $this->assertEquals(800, $this->cobradoEl('2026-10-01'));
    }
}
