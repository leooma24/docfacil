<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\CajaDelDia;
use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Resources\PaymentResource\Pages\EditPayment;
use App\Models\Clinic;
use App\Models\CorteDeCaja;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\User;
use App\Support\CajaDelDia as Caja;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Día 2 del plan (10-oct-2026): saldo a favor y corte con conteo.
 *
 * - Anticipo: el paciente deja dinero antes del tratamiento. Entra a la caja
 *   el día que lo deja y queda a su favor; en el siguiente cobro se descuenta
 *   solo, sin volver a contarse como ingreso.
 * - Cierre de caja: lo que debe haber en efectivo, lo que se contó y la
 *   diferencia, con quién cerró y a qué hora (como el corte de DaleControl).
 * - Un cobro que ya tiene dinero no se borra: el dinero desaparecía de la
 *   caja del día sin dejar rastro. Se regresa el dinero, y eso sí queda.
 */
class SaldoAFavorYCorteTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $doctor;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $this->doctor->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);
    }

    private function perfil()
    {
        return Livewire::withQueryParams(['patient' => $this->ana->id])->test(PatientProfile::class);
    }

    private function anticipo(float $monto, array $mas = [])
    {
        return $this->perfil()->callAction('cobrarHoy', data: array_merge(['tipo' => 'anticipo', 'amount' => $monto, 'payment_method' => 'cash'], $mas));
    }

    private function debe(float $monto): Payment
    {
        return Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => $monto,
            'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => today()->subWeek(), 'due_date' => today()->subDay()]);
    }

    // ── Anticipo ────────────────────────────────────────────────

    public function test_el_anticipo_entra_hoy_a_la_caja_y_queda_a_su_favor(): void
    {
        $this->anticipo(1000)->assertHasNoActionErrors();

        $this->assertSame(1000.0, $this->ana->fresh()->saldoAFavor());
        $this->assertSame(1000.0, Caja::de($this->clinica->id, today())['porMetodo']['cash']);
        $this->assertSame(1000.0, Payment::cobradoEntre($this->clinica->id, today(), today()));
    }

    public function test_si_debe_algo_el_anticipo_primero_paga_lo_que_debe(): void
    {
        $deuda = $this->debe(300);

        $this->anticipo(1000)->assertHasNoActionErrors();

        $this->assertSame('paid', $deuda->fresh()->status);
        $this->assertSame(700.0, $this->ana->fresh()->saldoAFavor());
    }

    public function test_el_siguiente_cobro_se_descuenta_solo_y_no_se_cuenta_dos_veces(): void
    {
        $this->anticipo(1000);

        $this->perfil()->callAction('cobrarHoy', data: ['tipo' => 'cobro', 'concepto' => 'Resina', 'amount' => 800, 'como' => 'todo', 'payment_method' => 'cash'])
            ->assertHasNoActionErrors();

        $cobro = Payment::where('notes', 'Resina')->sole();
        $this->assertSame('paid', $cobro->status);
        $this->assertSame([PaymentReceipt::SALDO_A_FAVOR], $cobro->receipts->pluck('payment_method')->all());
        $this->assertSame(200.0, $this->ana->fresh()->saldoAFavor());
        $this->assertSame(1000.0, Payment::cobradoEntre($this->clinica->id, today(), today()), 'Entraron $1,000, no $1,800');
        $this->assertSame(1000.0, Caja::de($this->clinica->id, today())['total']);
    }

    public function test_si_el_saldo_no_alcanza_paga_el_resto_como_diga(): void
    {
        $this->anticipo(1000);

        $this->perfil()->callAction('cobrarHoy', data: ['tipo' => 'cobro', 'concepto' => 'Corona', 'amount' => 1500, 'como' => 'todo', 'payment_method' => 'card'])
            ->assertHasNoActionErrors();

        $cobro = Payment::where('notes', 'Corona')->sole();
        $this->assertSame('paid', $cobro->status);
        $this->assertSame(0.0, $this->ana->fresh()->saldoAFavor());
        $this->assertSame(['cash' => 1000.0, 'card' => 500.0], array_filter(Caja::de($this->clinica->id, today())['porMetodo']));
    }

    public function test_cobrar_lo_que_debe_usa_primero_el_saldo_a_favor(): void
    {
        $this->anticipo(200);
        $deuda = $this->debe(500);

        $this->perfil()
            ->assertActionHasLabel('cobrarSaldo', 'Cobrar $300')
            ->callAction('cobrarSaldo', data: ['monto' => 300, 'payment_method' => 'cash'])
            ->assertHasNoActionErrors();

        $this->assertSame('paid', $deuda->fresh()->status);
        $this->assertSame(0.0, $this->ana->fresh()->saldoAFavor());
    }

    public function test_el_perfil_y_el_estado_de_cuenta_muestran_el_saldo_a_favor(): void
    {
        $this->anticipo(1000);

        $this->perfil()->assertSee('A favor $1,000');
        $estado = \App\Support\EstadoDeCuenta::de($this->ana);
        $this->assertSame(1000.0, $estado['aFavor']);
        $this->assertCount(0, $estado['cobros'], 'El anticipo no es un cobro de un tratamiento');
    }

    // ── Cierre de caja ──────────────────────────────────────────

    public function test_cerrar_la_caja_guarda_lo_esperado_lo_contado_y_la_diferencia(): void
    {
        $this->anticipo(1000);
        $this->anticipo(300, ['payment_method' => 'card']);

        Livewire::test(CajaDelDia::class)
            ->assertSee('Debe haber en efectivo')
            ->set('contado', 950)
            ->set('notasDelCierre', 'Se dio cambio de más')
            ->call('cerrarCaja')
            ->assertHasNoErrors()
            ->assertSee('Faltan $50');

        $corte = CorteDeCaja::sole();
        $this->assertSame(1000.0, (float) $corte->esperado);
        $this->assertSame(950.0, (float) $corte->contado);
        $this->assertSame(-50.0, (float) $corte->diferencia);
        $this->assertSame($this->doctor->id, $corte->user_id);
        $this->assertSame(today()->toDateString(), $corte->fecha->toDateString());
    }

    public function test_si_cuadra_lo_dice(): void
    {
        $this->anticipo(1000);

        Livewire::test(CajaDelDia::class)->set('contado', 1000)->call('cerrarCaja')->assertSee('Cuadra');
    }

    public function test_la_asistente_sin_permiso_de_dinero_tambien_cierra_la_caja(): void
    {
        $lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->actingAs($lupita);

        Livewire::test(CajaDelDia::class)->set('contado', 0)->call('cerrarCaja')->assertHasNoErrors();

        $this->assertSame($lupita->id, CorteDeCaja::sole()->user_id);
    }

    public function test_si_entra_dinero_despues_de_cerrar_lo_avisa(): void
    {
        Livewire::test(CajaDelDia::class)->set('contado', 0)->call('cerrarCaja');
        $this->travel(1)->hours();
        $this->anticipo(400);

        Livewire::test(CajaDelDia::class)->assertSee('Entró dinero después del cierre');
    }

    // ── Un cobro con dinero no se borra ─────────────────────────

    public function test_un_cobro_que_ya_tiene_pagos_no_se_puede_borrar(): void
    {
        $pagado = Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 500,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);
        $sinPagos = $this->debe(300);

        Livewire::test(EditPayment::class, ['record' => $pagado->getRouteKey()])->assertActionHidden('delete');
        Livewire::test(EditPayment::class, ['record' => $sinPagos->getRouteKey()])->assertActionVisible('delete');
    }
}
