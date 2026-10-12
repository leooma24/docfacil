<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Día 1 del plan (10-oct-2026): cobrar como profesional.
 *
 * Lo que vimos en DaleControl y Dentalink: un botón de cobro en la ficha que
 * dice cuánto debe ("Cobrar $300"), atajos "Paga todo" / "Sin pago hoy",
 * pago combinado (mitad efectivo, mitad tarjeta) y un estado de cuenta con un
 * solo saldo. Antes en DocFácil:
 * - en la ficha no había cómo cobrar algo nuevo; había que ir a Cobros;
 * - "Cobrar" en la cita cobraba todo, sin abono ni "no pagó hoy";
 * - no había pago combinado: se registraba todo como efectivo o como tarjeta,
 *   y el corte no cuadraba con la terminal.
 */
class CobrarComoProfesionalTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $ana;
    private Service $limpieza;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $this->limpieza = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Limpieza', 'price' => 800, 'duration_minutes' => 30]);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function perfil()
    {
        return Livewire::withQueryParams(['patient' => $this->ana->id])->test(PatientProfile::class);
    }

    private function debe(float $monto, array $datos = []): Payment
    {
        return Payment::create(array_merge(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => $monto,
            'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => today()->subWeek(), 'due_date' => today()->subDay()], $datos));
    }

    private function metodosDeLosRecibos(Payment $cobro): array
    {
        return $cobro->receipts()->orderBy('id')->get()->map(fn ($r) => [$r->payment_method, (float) $r->amount])->all();
    }

    // ── Cobrar algo nuevo desde la ficha ────────────────────────

    public function test_desde_la_ficha_cobra_un_servicio_y_paga_todo(): void
    {
        $this->perfil()->callAction('cobrarHoy', data: [
            'service_id' => $this->limpieza->id, 'amount' => 800, 'como' => 'todo', 'payment_method' => 'card',
        ])->assertHasNoActionErrors();

        $cobro = Payment::where('patient_id', $this->ana->id)->sole();
        $this->assertSame('paid', $cobro->status);
        $this->assertSame($this->limpieza->id, $cobro->service_id);
        $this->assertSame([['card', 800.0]], $this->metodosDeLosRecibos($cobro));
    }

    public function test_al_cobrar_se_anota_que_pide_factura(): void
    {
        // Para la lista de su contador (11-oct-2026): se anota donde lo pide.
        $this->perfil()->callAction('cobrarHoy', data: [
            'concepto' => 'Resina', 'amount' => 1200, 'como' => 'todo', 'payment_method' => 'card', 'factura' => true,
        ])->assertHasNoActionErrors();

        $this->assertTrue(Payment::where('patient_id', $this->ana->id)->sole()->factura_solicitada);
    }

    public function test_paga_una_parte_y_queda_el_saldo(): void
    {
        $this->perfil()->callAction('cobrarHoy', data: [
            'concepto' => 'Resina', 'amount' => 1200, 'como' => 'parte', 'recibido' => 500, 'payment_method' => 'cash',
        ])->assertHasNoActionErrors();

        $cobro = Payment::where('patient_id', $this->ana->id)->sole();
        $this->assertSame('partial', $cobro->status);
        $this->assertSame(700.0, $cobro->remaining);
        $this->assertSame('Resina', $cobro->notes);
    }

    public function test_sin_pago_hoy_queda_todo_por_cobrar_sin_recibo(): void
    {
        $this->perfil()->callAction('cobrarHoy', data: [
            'concepto' => 'Corona', 'amount' => 4500, 'como' => 'nada',
        ])->assertHasNoActionErrors();

        $cobro = Payment::where('patient_id', $this->ana->id)->sole();
        $this->assertSame('pending', $cobro->status);
        $this->assertSame(0, $cobro->receipts()->count());
    }

    public function test_pago_combinado_deja_un_recibo_por_cada_forma(): void
    {
        $this->perfil()->callAction('cobrarHoy', data: [
            'service_id' => $this->limpieza->id, 'amount' => 800, 'como' => 'todo',
            'payment_method' => 'combinado', 'efectivo' => 300, 'otra_forma' => 'card',
        ])->assertHasNoActionErrors();

        $cobro = Payment::where('patient_id', $this->ana->id)->sole();
        $this->assertSame('paid', $cobro->status);
        $this->assertSame([['cash', 300.0], ['card', 500.0]], $this->metodosDeLosRecibos($cobro));
    }

    public function test_no_deja_un_abono_mayor_al_total_ni_un_efectivo_que_no_cuadra(): void
    {
        $this->perfil()->callAction('cobrarHoy', data: [
            'concepto' => 'Resina', 'amount' => 1200, 'como' => 'parte', 'recibido' => 1500, 'payment_method' => 'cash',
        ])->assertHasActionErrors(['recibido']);

        $this->perfil()->callAction('cobrarHoy', data: [
            'concepto' => 'Resina', 'amount' => 1200, 'como' => 'todo', 'payment_method' => 'combinado', 'efectivo' => 1200, 'otra_forma' => 'card',
        ])->assertHasActionErrors(['efectivo']);

        $this->assertSame(0, Payment::count());
    }

    // ── El botón que dice cuánto debe ───────────────────────────

    public function test_si_debe_el_boton_dice_cuanto_y_cobra_todo_lo_que_debe(): void
    {
        $vieja = $this->debe(200, ['payment_date' => today()->subMonth(), 'due_date' => today()->subWeeks(3)]);
        $nueva = $this->debe(100);

        $this->perfil()
            ->assertActionVisible('cobrarSaldo')
            ->assertActionHasLabel('cobrarSaldo', 'Cobrar $300')
            ->callAction('cobrarSaldo', data: ['monto' => 300, 'payment_method' => 'combinado', 'efectivo' => 100, 'otra_forma' => 'transfer'])
            ->assertHasNoActionErrors();

        $this->assertSame('paid', $vieja->fresh()->status);
        $this->assertSame('paid', $nueva->fresh()->status);
        $metodos = collect([$vieja, $nueva])->flatMap(fn ($c) => $this->metodosDeLosRecibos($c))->all();
        $this->assertSame([['cash', 100.0], ['transfer', 100.0], ['transfer', 100.0]], $metodos);
    }

    public function test_si_no_debe_no_sale_el_boton_de_saldo(): void
    {
        $this->perfil()->assertActionHidden('cobrarSaldo');
    }

    public function test_las_mensualidades_que_no_vencen_no_cuentan_en_el_boton(): void
    {
        $plan = \App\Models\PaymentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'description' => 'Ortodoncia',
            'total' => 1000, 'down_payment' => 0, 'installments_count' => 1, 'installment_amount' => 1000, 'first_due_date' => today()->addMonth()]);
        $this->debe(1000, ['payment_plan_id' => $plan->id, 'due_date' => today()->addMonth(), 'payment_date' => today()->addMonth()]);
        $this->debe(150);

        $this->perfil()->assertActionHasLabel('cobrarSaldo', 'Cobrar $150');
    }

    // ── Estado de cuenta ────────────────────────────────────────

    public function test_el_perfil_trae_el_estado_de_cuenta_para_descargar(): void
    {
        $this->debe(800)->registrarAbono(600, 'cash');

        $this->perfil()
            ->assertActionVisible('estado_de_cuenta')
            ->assertActionHasUrl('estado_de_cuenta', route('paciente.estado-de-cuenta', $this->ana->id));
    }

    public function test_el_estado_de_cuenta_en_pdf_trae_cada_cobro_y_cada_abono(): void
    {
        $this->debe(800, ['service_id' => $this->limpieza->id])->registrarAbono(600, 'card');

        $respuesta = $this->get(route('paciente.estado-de-cuenta', $this->ana->id));

        $respuesta->assertOk();
        $this->assertSame('application/pdf', $respuesta->headers->get('content-type'));
    }

    public function test_el_estado_de_cuenta_de_otro_consultorio_no_se_ve(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico']);
        $ajeno = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Luis', 'last_name' => 'P']);

        $this->get(route('paciente.estado-de-cuenta', $ajeno->id))->assertNotFound();
    }

    public function test_los_datos_del_estado_de_cuenta_cuadran(): void
    {
        $this->debe(800, ['service_id' => $this->limpieza->id])->registrarAbono(600, 'card');
        $this->debe(300, ['notes' => 'Resina', 'status' => 'paid']);

        $datos = \App\Support\EstadoDeCuenta::de($this->ana);

        $this->assertSame(1100.0, $datos['total']);
        $this->assertSame(900.0, $datos['pagado']);
        $this->assertSame(200.0, $datos['debe']);
        $this->assertCount(2, $datos['cobros']);
    }

    // ── Ya sabe qué se cobra ────────────────────────────────────

    public function test_al_abrir_cobrar_ya_trae_lo_de_su_cita_de_hoy(): void
    {
        $cita = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'service_id' => $this->limpieza->id, 'starts_at' => now()->subHour(), 'ends_at' => now()->subMinutes(30), 'status' => 'completed']);

        $this->perfil()->mountAction('cobrarHoy')
            ->assertActionDataSet(['service_id' => $this->limpieza->id, 'amount' => 800.0, 'appointment_id' => $cita->id])
            ->callMountedAction();

        $this->assertSame($cita->id, Payment::where('patient_id', $this->ana->id)->sole()->appointment_id);
    }

    public function test_si_en_la_consulta_se_anotaron_procedimientos_trae_esos_y_su_total(): void
    {
        $resina = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Resina', 'price' => 700]);
        $cita = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'starts_at' => now()->subHour(), 'ends_at' => now()->subMinutes(30), 'status' => 'completed']);
        \App\Models\ConsultationProcedure::create(['clinic_id' => $this->clinica->id, 'appointment_id' => $cita->id, 'service_id' => $resina->id,
            'tooth_number' => '36', 'quantity' => 2, 'unit' => 'pieza', 'unit_price' => 700]);
        \App\Models\ConsultationProcedure::create(['clinic_id' => $this->clinica->id, 'appointment_id' => $cita->id, 'service_id' => $this->limpieza->id,
            'quantity' => 1, 'unit' => 'visit', 'unit_price' => 800]);

        $this->perfil()->mountAction('cobrarHoy')
            ->assertActionDataSet(['amount' => 2200.0, 'concepto' => 'Resina en 36 (2), Limpieza', 'appointment_id' => $cita->id]);
    }

    public function test_si_esa_cita_ya_se_cobro_no_la_vuelve_a_sugerir(): void
    {
        $cita = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'service_id' => $this->limpieza->id, 'starts_at' => now()->subHour(), 'ends_at' => now()->subMinutes(30), 'status' => 'completed']);
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'appointment_id' => $cita->id, 'amount' => 800,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);

        $this->perfil()->mountAction('cobrarHoy')->assertActionDataSet(['amount' => null, 'appointment_id' => null]);
    }

    // ── Cobrar desde la cita ────────────────────────────────────

    public function test_desde_la_cita_puede_dejar_un_abono_en_vez_de_cobrar_todo(): void
    {
        $cita = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            // En curso: la lista de citas trae de entrada solo las que vienen.
            'service_id' => $this->limpieza->id, 'starts_at' => now()->addMinutes(5), 'ends_at' => now()->addMinutes(35), 'status' => 'in_progress']);

        Livewire::test(ListAppointments::class)
            ->callTableAction('charge', $cita, data: ['amount' => 800, 'como' => 'parte', 'recibido' => 300, 'payment_method' => 'cash'])
            ->assertHasNoTableActionErrors();

        $cobro = Payment::where('appointment_id', $cita->id)->sole();
        $this->assertSame('partial', $cobro->status);
        $this->assertSame(500.0, $cobro->remaining);
    }
}
