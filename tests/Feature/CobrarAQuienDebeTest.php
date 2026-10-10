<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Resources\PaymentResource\Pages\EditPayment;
use App\Filament\Doctor\Widgets\LeDebenWidget;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cobrarle a quien debe, desde donde se ve que debe, con el monto ya puesto.
 *
 * Octubre 2026: el "Cobrar" de los adeudos vencidos del escritorio solo abría
 * WhatsApp; el "Cobrar" del perfil llevaba a un formulario sin botón de
 * abono; y desde la lista eran Acciones → Pagar abono → escribir el monto a
 * mano. Ahora es Cobrar → Guardar, con lo que debe ya escrito.
 */
class CobrarAQuienDebeTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function adeudo(float $monto, int $diasVencido, float $abonado = 0): Payment
    {
        return Payment::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->ana->id,
            'amount' => $monto,
            'amount_paid' => $abonado,
            'status' => $abonado > 0 ? 'partial' : 'pending',
            'payment_method' => 'cash',
            'payment_date' => today()->subDays($diasVencido + 5),
            'due_date' => today()->subDays($diasVencido),
        ]);
    }

    // ── El modelo: un pago que cubre varios adeudos ──────────────

    public function test_un_pago_se_reparte_del_adeudo_mas_viejo_al_mas_nuevo(): void
    {
        $viejo = $this->adeudo(1000, 30);
        $nuevo = $this->adeudo(1000, 5);

        Payment::abonarEnOrden(collect([$nuevo, $viejo]), 1500, 'transfer');

        $this->assertSame('paid', $viejo->fresh()->status);
        $this->assertSame('partial', $nuevo->fresh()->status);
        $this->assertEquals(500, (float) $nuevo->fresh()->remaining);
        $this->assertSame('transfer', $viejo->fresh()->receipts()->latest('id')->first()->payment_method);
    }

    public function test_no_se_cobra_mas_de_lo_que_debe(): void
    {
        $adeudo = $this->adeudo(1000, 10);

        $this->expectException(\InvalidArgumentException::class);
        Payment::abonarEnOrden(collect([$adeudo]), 1200, 'cash');
    }

    // ── Escritorio: adeudos vencidos ─────────────────────────────

    public function test_el_cobrar_de_adeudos_vencidos_registra_el_pago_con_el_monto_ya_puesto(): void
    {
        $adeudo = $this->adeudo(800, 10, 300);

        Livewire::test(LeDebenWidget::class)
            ->mountAction('cobrar', ['patient' => $this->ana->id])
            ->assertActionDataSet(['monto' => 500.0])
            ->callMountedAction();

        $this->assertSame('paid', $adeudo->fresh()->status);
    }

    public function test_adeudos_vencidos_sigue_ofreciendo_el_recordatorio_por_whatsapp(): void
    {
        $this->adeudo(800, 10);

        Livewire::test(LeDebenWidget::class)
            ->assertSee('Recordarle')
            ->assertSee('wa.me/526681234567', false);
    }

    // ── Perfil: "Lo que sigue" ───────────────────────────────────

    public function test_el_perfil_cobra_todo_lo_vencido_con_el_total_ya_puesto(): void
    {
        $uno = $this->adeudo(1000, 30);
        $dos = $this->adeudo(500, 5);

        Livewire::withQueryParams(['patient' => $this->ana->id])->test(PatientProfile::class)
            ->mountAction('cobrarVencido')
            ->assertActionDataSet(['monto' => 1500.0])
            ->setActionData(['payment_method' => 'card'])
            ->callMountedAction();

        $this->assertSame('paid', $uno->fresh()->status);
        $this->assertSame('paid', $dos->fresh()->status);
    }

    public function test_el_perfil_acepta_un_pago_parcial(): void
    {
        $uno = $this->adeudo(1000, 30);
        $dos = $this->adeudo(500, 5);

        Livewire::withQueryParams(['patient' => $this->ana->id])->test(PatientProfile::class)
            ->callAction('cobrarVencido', data: ['monto' => 1200, 'payment_method' => 'cash']);

        $this->assertSame('paid', $uno->fresh()->status);
        $this->assertEquals(300, (float) $dos->fresh()->remaining);
    }

    // ── El cobro abierto ─────────────────────────────────────────

    public function test_el_cobro_abierto_tiene_registrar_abono(): void
    {
        $adeudo = $this->adeudo(900, 3);

        Livewire::test(EditPayment::class, ['record' => $adeudo->id])
            ->mountAction('registrarAbono')
            ->assertActionDataSet(['monto' => 900.0])
            ->callMountedAction()
            // El formulario de abajo se pone al día: si luego le dan Guardar,
            // no regresa el saldo de antes.
            ->assertFormSet(['amount_paid' => 900.0, 'status' => 'paid'])
            ->call('save');

        $this->assertSame('paid', $adeudo->fresh()->status);
        $this->assertEquals(900, (float) $adeudo->fresh()->receipts()->sum('amount'));
    }

    public function test_el_cobro_pagado_no_ofrece_abono(): void
    {
        $pagado = Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 500,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);

        Livewire::test(EditPayment::class, ['record' => $pagado->id])
            ->assertActionHidden('registrarAbono');
    }
}
