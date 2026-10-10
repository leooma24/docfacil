<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Resources\PaymentPlanResource\Pages\CreatePaymentPlan;
use App\Filament\Doctor\Resources\PaymentPlanResource\Pages\ViewPaymentPlan;
use App\Filament\Doctor\Resources\PaymentResource\Pages\CreatePayment;
use App\Filament\Doctor\Resources\PaymentResource\Pages\EditPayment;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Que lo que dice la caja sea lo que hay en el cajón (auditoría del
 * 12-oct-2026, grupo de dinero):
 *
 * - pasar por el paso "Cobro" de la consulta ya no lo da por pagado: se
 *   pregunta "¿Ya pagó?" y, si nadie contesta, queda por cobrar;
 * - "Se le regresó el dinero" sí lo descuenta de la caja;
 * - la asistente sin permiso de dinero no ve ingresos ni Mi plan;
 * - un plan de pagos se puede cancelar, y un enganche mayor que el total ya
 *   no tumba la pantalla;
 * - "Le deben de este periodo" no cuenta mensualidades que no vencen;
 * - "Cita asociada" solo enseña las citas del paciente.
 */
class DineroQueCuadraTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function consulta(?Appointment $cita = null)
    {
        $servicio = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Resina', 'price' => 800, 'duration_minutes' => 30, 'is_active' => true]);
        $cita ??= Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id, 'service_id' => $servicio->id,
            'starts_at' => now()->setTime(11, 0), 'ends_at' => now()->setTime(11, 30), 'status' => 'scheduled']);

        return [$cita, Livewire::withQueryParams(['appointment' => $cita->id])->test(Consultation::class)];
    }

    // ── El paso "Cobro" de la consulta ──────────────────────────

    public function test_pasar_por_el_cobro_sin_contestar_lo_deja_por_cobrar(): void
    {
        [$cita, $consulta] = $this->consulta();

        $consulta->call('goToStep', 4)
            ->assertSee('¿Ya pagó?')
            ->call('goToStep', 5)
            ->call('saveAndComplete');

        $cobro = Payment::where('appointment_id', $cita->id)->sole();
        $this->assertSame('pending', $cobro->status);
        $this->assertEquals(0, (float) $cobro->receipts()->sum('amount'));
    }

    public function test_pago_todo_queda_pagado(): void
    {
        [$cita, $consulta] = $this->consulta();

        $consulta->call('goToStep', 4)->set('ya_pago', 'todo')->call('saveAndComplete');

        $this->assertSame('paid', Payment::where('appointment_id', $cita->id)->sole()->status);
    }

    public function test_dejo_un_abono_queda_parcial_con_su_recibo(): void
    {
        [$cita, $consulta] = $this->consulta();

        $consulta->call('goToStep', 4)->set('ya_pago', 'abono')->set('abono', 300)->call('saveAndComplete');

        $cobro = Payment::where('appointment_id', $cita->id)->sole();
        $this->assertSame('partial', $cobro->status);
        $this->assertEquals(300, (float) $cobro->amount_paid);
        $this->assertEquals(300, (float) $cobro->receipts()->sum('amount'));
    }

    public function test_lo_que_contesto_sobrevive_a_recargar_la_pagina(): void
    {
        [$cita, $consulta] = $this->consulta();
        $consulta->call('goToStep', 4)->set('ya_pago', 'todo')->call('goToStep', 5);

        Livewire::withQueryParams(['appointment' => $cita->id])->test(Consultation::class)->call('saveAndComplete');

        $this->assertSame('paid', Payment::where('appointment_id', $cita->id)->sole()->status);
    }

    // ── Reembolso ───────────────────────────────────────────────

    public function test_se_le_regreso_el_dinero_lo_descuenta_de_la_caja(): void
    {
        $cobro = Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 800, 'amount_paid' => 800,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);
        $this->assertEquals(800, Payment::cobradoEntre($this->clinica->id, today(), today()));

        Livewire::test(EditPayment::class, ['record' => $cobro->getRouteKey()])
            ->set('data.status', 'refunded')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(0, Payment::cobradoEntre($this->clinica->id, today(), today()));
        $this->assertEquals(0, (float) $cobro->fresh()->amount_paid);
        $this->get('/doctor/caja')->assertOk()->assertSee('Devolución');
    }

    // ── La asistente sin permiso de dinero ──────────────────────

    public function test_la_asistente_sin_dinero_no_ve_ingresos_ni_mi_plan(): void
    {
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 4321, 'amount_paid' => 4321,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);
        $lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id, 've_dinero' => false]);
        $this->actingAs($lupita);

        // El monto de cada cobro sí lo ve (ella cobra); lo que entró en total, no.
        $this->get('/doctor/cobros')->assertOk()->assertDontSee('Este mes')->assertDontSee('Cobrado hoy');
        $this->get('/doctor/actualizar-plan')->assertForbidden();
    }

    public function test_el_aviso_de_presupuesto_aceptado_no_le_dice_el_monto_a_quien_no_ve_dinero(): void
    {
        $lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id, 've_dinero' => false]);
        $plan = \App\Models\TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'title' => 'Corona', 'status' => 'sent',
            'subtotal' => 7654, 'discount' => 0, 'total' => 7654, 'public_token' => str_repeat('a', 64)]);

        $this->get(\Illuminate\Support\Facades\URL::signedRoute('treatment-plan.accept', $plan->public_token))->assertOk();

        $this->assertStringContainsString('7,654', json_encode($this->usuario->fresh()->notifications->first()?->data));
        $this->assertStringNotContainsString('7,654', json_encode($lupita->fresh()->notifications->first()?->data));
    }

    // ── Planes de pago ──────────────────────────────────────────

    public function test_un_enganche_mayor_que_el_total_no_tumba_la_pantalla(): void
    {
        Livewire::test(CreatePaymentPlan::class)
            ->fillForm(['patient_id' => $this->ana->id, 'description' => 'Brackets', 'total' => 10000, 'down_payment' => 12000,
                'installments_count' => 10, 'first_due_date' => '2026-11-14'])
            ->call('create')
            ->assertHasFormErrors(['down_payment']);

        $this->assertSame(0, PaymentPlan::count());
    }

    public function test_un_plan_mal_hecho_se_cancela_y_quita_lo_que_no_se_ha_pagado(): void
    {
        $plan = PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'description' => 'Brackets',
            'total' => 12000, 'down_payment' => 2000, 'installments_count' => 20, 'first_due_date' => '2026-11-14'], 'cash');

        Livewire::test(ViewPaymentPlan::class, ['record' => $plan->getRouteKey()])
            ->callAction('cancelar');

        $plan->refresh();
        $this->assertSame('cancelled', $plan->status);
        $this->assertSame(1, $plan->payments()->count(), 'Solo queda el enganche que sí se pagó');
        $this->assertEquals(0, Payment::saldoPorCobrar($this->clinica->id));
    }

    // ── El corte ────────────────────────────────────────────────

    public function test_le_deben_de_este_periodo_no_cuenta_mensualidades_que_no_vencen(): void
    {
        PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'description' => 'Brackets',
            'total' => 12000, 'down_payment' => 0, 'installments_count' => 12, 'first_due_date' => '2026-10-01']);

        // De octubre solo toca la del día 1; las de noviembre en adelante no.
        $this->assertEquals(1000, Payment::porCobrarEntre($this->clinica->id, now()->startOfYear(), now()->endOfYear()));
    }

    // ── Cita asociada ───────────────────────────────────────────

    public function test_cita_asociada_solo_enseña_las_citas_del_paciente(): void
    {
        $beto = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Beto', 'last_name' => 'Salazar']);
        $deAna = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'starts_at' => '2026-10-14 09:00', 'ends_at' => '2026-10-14 09:30', 'status' => 'completed']);
        $deBeto = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $beto->id,
            'starts_at' => '2026-10-14 10:00', 'ends_at' => '2026-10-14 10:30', 'status' => 'completed']);

        $opciones = Livewire::test(CreatePayment::class)
            ->set('data.patient_id', $this->ana->id)
            ->instance()->form->getComponent('data.appointment_id')->getOptions();

        $this->assertArrayHasKey($deAna->id, $opciones);
        $this->assertArrayNotHasKey($deBeto->id, $opciones);
    }
}
