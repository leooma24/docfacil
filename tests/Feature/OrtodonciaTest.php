<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Ortodoncia;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\Service;
use App\Models\User;
use App\Support\TableroDeOrtodoncia;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Día 7 del plan (10-oct-2026): ortodoncia.
 *
 * Dentalink lo tiene solo en su plan más caro: quién va atrasado en sus
 * controles y quién no tiene su siguiente cita. Aquí además, a quién se le
 * venció la mensualidad, y al cobrar el control del mes se cobra la
 * mensualidad de su plan en vez de crear otro cobro.
 */
class OrtodonciaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $daniela;
    private Service $control;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dra. Paola', 'email' => 'p@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Ortodoncia']);
        $this->daniela = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Daniela', 'last_name' => 'Cruz', 'phone' => '6681234567']);
        $this->control = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Control de ortodoncia', 'price' => 800, 'duration_minutes' => 30, 'category' => 'Ortodoncia']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function plan(): PaymentPlan
    {
        return PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->daniela->id, 'service_id' => $this->control->id,
            'description' => 'Ortodoncia (brackets)', 'total' => 9600, 'down_payment' => 0, 'installments_count' => 12,
            'first_due_date' => '2026-06-14']);
    }

    private function cita(string $cuando, string $estado): Appointment
    {
        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->daniela->id,
            'service_id' => $this->control->id, 'starts_at' => $cuando, 'ends_at' => \Carbon\Carbon::parse($cuando)->addMinutes(30), 'status' => $estado]);
    }

    private function filaDeDaniela(): ?array
    {
        return TableroDeOrtodoncia::de($this->clinica->id)->firstWhere('paciente.id', $this->daniela->id);
    }

    // ── Quién está en tratamiento ───────────────────────────────

    public function test_sale_quien_tiene_plan_de_ortodoncia(): void
    {
        $this->plan();

        $this->assertNotNull($this->filaDeDaniela());
    }

    public function test_sale_quien_tuvo_un_control_reciente_aunque_no_tenga_plan(): void
    {
        $this->cita('2026-09-20 10:00', 'completed');

        $this->assertNotNull($this->filaDeDaniela());
    }

    public function test_quien_no_es_de_ortodoncia_no_sale(): void
    {
        $limpieza = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Limpieza', 'price' => 600, 'category' => 'Preventivo']);
        Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->daniela->id,
            'service_id' => $limpieza->id, 'starts_at' => '2026-10-01 10:00', 'ends_at' => '2026-10-01 10:30', 'status' => 'completed']);

        $this->assertNull($this->filaDeDaniela());
    }

    // ── Lo que hay que ver ──────────────────────────────────────

    public function test_control_atrasado_y_sin_proxima_cita(): void
    {
        $this->plan();
        $this->cita('2026-08-20 10:00', 'completed');

        $fila = $this->filaDeDaniela();
        $this->assertTrue($fila['atrasado']);
        $this->assertNull($fila['proximo']);
        $this->assertSame('2026-08-20', $fila['ultimo']->toDateString());
    }

    public function test_con_su_siguiente_cita_no_va_atrasado(): void
    {
        $this->plan();
        $this->cita('2026-08-20 10:00', 'completed');
        $this->cita('2026-10-20 10:00', 'scheduled');

        $fila = $this->filaDeDaniela();
        $this->assertFalse($fila['atrasado']);
        $this->assertSame('2026-10-20', $fila['proximo']->toDateString());
    }

    public function test_mensualidades_vencidas(): void
    {
        $this->plan();

        // Vencen 14-jun, 14-jul, 14-ago, 14-sep y 14-oct (hoy no cuenta como vencida).
        $this->assertSame(3200.0, $this->filaDeDaniela()['vencido']);
    }

    // ── La pantalla ─────────────────────────────────────────────

    public function test_el_tablero_lo_muestra_y_filtra_los_atrasados(): void
    {
        $this->plan();
        $this->cita('2026-08-20 10:00', 'completed');
        $luis = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Luis', 'last_name' => 'Mora']);
        Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $luis->id,
            'service_id' => $this->control->id, 'starts_at' => '2026-10-01 10:00', 'ends_at' => '2026-10-01 10:30', 'status' => 'completed']);
        Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $luis->id,
            'service_id' => $this->control->id, 'starts_at' => '2026-11-01 10:00', 'ends_at' => '2026-11-01 10:30', 'status' => 'scheduled']);

        Livewire::test(Ortodoncia::class)
            ->assertSee('Daniela Cruz')->assertSee('Luis Mora')
            ->assertSee('Control atrasado')
            ->assertSee('Mensualidad vencida')
            ->set('filtro', 'atrasados')
            ->assertSee('Daniela Cruz')->assertDontSee('Luis Mora');
    }

    public function test_el_menu_lo_muestra_solo_si_el_consultorio_hace_ortodoncia(): void
    {
        $this->assertTrue(Ortodoncia::shouldRegisterNavigation());

        $this->control->update(['category' => 'General']);
        $this->assertFalse(Ortodoncia::shouldRegisterNavigation());
    }

    // ── La mensualidad ligada al control ────────────────────────

    public function test_cobrar_el_control_cobra_la_mensualidad_del_plan(): void
    {
        $plan = $this->plan();
        $cita = $this->cita('2026-10-14 10:30', 'in_progress');

        Livewire::test(ListAppointments::class)
            ->mountTableAction('charge', $cita)
            ->assertTableActionDataSet(['amount' => 4000.0])
            ->setTableActionData(['amount' => 4000, 'como' => 'parte', 'recibido' => 800, 'payment_method' => 'cash'])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertSame(0, Payment::where('appointment_id', $cita->id)->count(), 'No crea otro cobro');
        $this->assertSame(800.0, $plan->fresh()->pagado());
        $this->assertSame('paid', $plan->payments()->orderBy('due_date')->first()->status, 'Se abona la más vieja primero');
    }
}
