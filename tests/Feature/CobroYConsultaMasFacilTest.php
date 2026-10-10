<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Doctor\Resources\PaymentResource\Pages\CreatePayment;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ConsultationProcedure;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lo que sigue de la prueba con 20 doctores (12-oct-2026):
 *
 * 1. El cobro se entiende: "¿Cómo quedó?" en vez de total contra abonado y
 *    estado; lo que ya existe (planes de pago, consentimientos, todo lo del
 *    plan) se encuentra desde donde el doctor lo busca.
 * 2. De la entrevista: al abrir la cita, lo último que se le hizo en cada
 *    diente y lo que falta; y la nota en 30 segundos con botones.
 */
class CobroYConsultaMasFacilTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;
    private Patient $rosa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        config(['services.ai.enabled' => false]);
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->rosa = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'Valenzuela', 'phone' => '6681234567', 'allergies' => 'Ninguna']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function cita(\Carbon\Carbon $cuando, string $estado = 'scheduled', ?Patient $p = null): Appointment
    {
        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => ($p ?? $this->rosa)->id,
            'starts_at' => $cuando, 'ends_at' => $cuando->copy()->addMinutes(60), 'status' => $estado]);
    }

    private function servicio(string $nombre, float $precio = 1000): Service
    {
        return Service::create(['clinic_id' => $this->clinica->id, 'name' => $nombre, 'price' => $precio, 'duration_minutes' => 60, 'is_active' => true]);
    }

    // ── 1. El cobro ─────────────────────────────────────────────

    public function test_no_ha_pagado_queda_pendiente_sin_abono_ni_forma_de_pago(): void
    {
        Livewire::test(CreatePayment::class)
            ->assertSee('¿Cómo quedó?')
            ->set('data.patient_id', $this->rosa->id)
            ->set('data.amount', 800)
            ->set('data.status', 'pending')
            ->assertSee('Fecha del tratamiento')
            ->set('data.payment_method', null)
            ->call('create')
            ->assertHasNoFormErrors();

        $cobro = Payment::sole();
        $this->assertSame('pending', $cobro->status);
        $this->assertEquals(0, $cobro->amount_paid);
        $this->assertSame(0, $cobro->receipts()->count());
    }

    public function test_dejo_un_abono_guarda_lo_que_dejo_y_su_recibo(): void
    {
        Livewire::test(CreatePayment::class)
            ->set('data.patient_id', $this->rosa->id)
            ->set('data.amount', 800)
            ->set('data.status', 'partial')
            ->set('data.amount_paid', 300)
            ->call('create')
            ->assertHasNoFormErrors();

        $cobro = Payment::sole();
        $this->assertSame('partial', $cobro->status);
        $this->assertEquals(300, $cobro->amount_paid);
        $this->assertEquals(300, $cobro->receipts()->sum('amount'));
    }

    public function test_un_abono_sin_monto_no_se_guarda(): void
    {
        Livewire::test(CreatePayment::class)
            ->set('data.patient_id', $this->rosa->id)
            ->set('data.amount', 800)
            ->set('data.status', 'partial')
            ->set('data.amount_paid', null)
            ->call('create')
            ->assertHasFormErrors(['amount_paid']);

        $this->assertSame(0, Payment::count());
    }

    public function test_un_abono_por_todo_queda_pagado(): void
    {
        Livewire::test(CreatePayment::class)
            ->set('data.patient_id', $this->rosa->id)
            ->set('data.amount', 800)
            ->set('data.status', 'partial')
            ->set('data.amount_paid', 800)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('paid', Payment::sole()->status);
    }

    public function test_pago_todo_deja_lo_pagado_igual_al_total(): void
    {
        Livewire::test(CreatePayment::class)
            ->set('data.patient_id', $this->rosa->id)
            ->set('data.amount', 800)
            ->set('data.status', 'paid')
            ->assertSee('Fecha en que pagó')
            ->call('create')
            ->assertHasNoFormErrors();

        $cobro = Payment::sole();
        $this->assertEquals(800, $cobro->amount_paid);
        $this->assertEquals(800, $cobro->receipts()->sum('amount'));
    }

    public function test_el_cobro_le_dice_donde_van_las_mensualidades(): void
    {
        $this->get('/doctor/cobros/create')->assertOk()
            ->assertSee('Planes de pago')
            ->assertSee('/doctor/planes-de-pago/create', false);
    }

    // ── 1. Lo que ya existe, a la mano ──────────────────────────

    public function test_desde_el_paciente_se_hace_su_plan_de_pagos_y_su_consentimiento(): void
    {
        Livewire::withQueryParams(['patient' => $this->rosa->id])->test(PatientProfile::class)
            ->assertSee('Plan de pagos')
            ->assertSee('/doctor/planes-de-pago/create?patient=' . $this->rosa->id, false)
            ->assertSee('Consentimiento')
            ->assertSee('/doctor/consentimientos/create?patient=' . $this->rosa->id, false);
    }

    public function test_el_consentimiento_llega_con_el_paciente_puesto(): void
    {
        $this->get('/doctor/consentimientos/create?patient=' . $this->rosa->id)->assertOk();

        Livewire::withQueryParams(['patient' => $this->rosa->id])
            ->test(\App\Filament\Doctor\Resources\ConsentFormResource\Pages\CreateConsentForm::class)
            ->assertSet('data.patient_id', $this->rosa->id);
    }

    public function test_en_el_basico_no_ofrece_consentimiento_porque_no_lo_trae(): void
    {
        $this->clinica->update(['plan' => 'basico']);

        Livewire::withQueryParams(['patient' => $this->rosa->id])->test(PatientProfile::class)
            ->assertSee('Plan de pagos')
            ->assertDontSee('/doctor/consentimientos/create', false);
    }

    public function test_mi_plan_ensena_todo_lo_que_trae_cada_plan_sin_esconderlo(): void
    {
        $html = $this->get('/doctor/actualizar-plan')->assertOk()->getContent();

        $this->assertStringNotContainsString('funciones más', $html);
        $this->assertStringNotContainsString('<template x-if="expanded">', $html);
    }

    // ── 2. Lo último del diente ─────────────────────────────────

    public function test_al_abrir_la_cita_dice_lo_ultimo_del_diente_y_lo_que_falta(): void
    {
        $endo = $this->servicio('Endodoncia', 3500);
        $antes = $this->cita(\Carbon\Carbon::parse('2026-10-03 11:00'), 'completed');
        ConsultationProcedure::create(['clinic_id' => $this->clinica->id, 'appointment_id' => $antes->id, 'service_id' => $endo->id,
            'tooth_number' => '36', 'quantity' => 1, 'unit' => 'pieza', 'unit_price' => 3500]);
        MedicalRecord::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'doctor_id' => $this->doctor->id,
            'appointment_id' => $antes->id, 'visit_date' => '2026-10-03', 'treatment' => '3 conductos, LT 21 mm, obturación con gutapercha']);
        $plan = TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'title' => 'Rehabilitación', 'status' => 'accepted',
            'subtotal' => 5000, 'discount' => 0, 'total' => 5000]);
        $plan->items()->create(['description' => 'Corona de zirconia', 'quantity' => 1, 'unit_price' => 5000, 'subtotal' => 5000, 'tooth_number' => '36']);

        $hoy = $this->cita(now()->setTime(11, 0));

        Livewire::withQueryParams(['appointment' => $hoy->id])->test(Consultation::class)
            ->assertSee('Lo último en cada diente')
            ->assertSee('Endodoncia')
            ->assertSee('3 conductos, LT 21 mm')
            ->assertSee('3 oct')
            ->assertSee('Falta: Corona de zirconia');
    }

    public function test_no_mezcla_dientes_de_otro_paciente(): void
    {
        $otro = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Beto', 'last_name' => 'Ruiz']);
        $antes = $this->cita(\Carbon\Carbon::parse('2026-10-03 11:00'), 'completed', $otro);
        ConsultationProcedure::create(['clinic_id' => $this->clinica->id, 'appointment_id' => $antes->id, 'service_id' => $this->servicio('Extracción')->id,
            'tooth_number' => '48', 'quantity' => 1, 'unit' => 'pieza', 'unit_price' => 900]);

        $hoy = $this->cita(now()->setTime(11, 0));

        Livewire::withQueryParams(['appointment' => $hoy->id])->test(Consultation::class)
            ->assertDontSee('Lo último en cada diente')
            ->assertDontSee('Extracción');
    }

    // ── 2. La nota en 30 segundos ───────────────────────────────

    public function test_los_botones_escriben_la_nota_sin_repetir(): void
    {
        $hoy = $this->cita(now()->setTime(11, 0));

        Livewire::withQueryParams(['appointment' => $hoy->id])->test(Consultation::class)
            ->call('goToStep', 2)
            ->assertSee('Sin dolor')
            ->call('agregarFrase', 'Sin dolor')
            ->call('agregarFrase', 'Higiene buena')
            ->call('agregarFrase', 'Sin dolor')
            ->assertSet('treatment', 'Sin dolor. Higiene buena.');
    }

    public function test_igual_que_la_vez_pasada_copia_lo_que_se_hizo(): void
    {
        $antes = $this->cita(\Carbon\Carbon::parse('2026-09-16 11:00'), 'completed');
        MedicalRecord::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'doctor_id' => $this->doctor->id,
            'appointment_id' => $antes->id, 'visit_date' => '2026-09-16', 'treatment' => 'Ajuste de arco 0.016 NiTi, cambio de ligas']);
        $hoy = $this->cita(now()->setTime(11, 0));

        Livewire::withQueryParams(['appointment' => $hoy->id])->test(Consultation::class)
            ->call('goToStep', 2)
            ->assertSee('Igual que la vez pasada')
            ->call('igualQueLaVezPasada')
            ->assertSet('treatment', 'Ajuste de arco 0.016 NiTi, cambio de ligas');
    }

    public function test_la_visita_rapida_arma_la_nota_con_botones(): void
    {
        $antes = $this->cita(\Carbon\Carbon::parse('2026-09-16 11:00'), 'completed');
        MedicalRecord::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'doctor_id' => $this->doctor->id,
            'appointment_id' => $antes->id, 'visit_date' => '2026-09-16', 'treatment' => 'Ajuste de arco 0.016 NiTi']);
        $hoy = $this->cita(now()->setTime(11, 0));

        Livewire::test(ListAppointments::class)
            ->mountTableAction('quick_visit', $hoy)
            ->assertSee('Igual que la vez pasada')
            ->setTableActionData(['frases' => ['previa', 'Sin dolor'], 'note' => 'Viene en 4 semanas', 'next_appointment_date' => null])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $nota = MedicalRecord::where('appointment_id', $hoy->id)->sole();
        $this->assertStringContainsString('Ajuste de arco 0.016 NiTi', $nota->notes);
        $this->assertStringContainsString('Sin dolor', $nota->notes);
        $this->assertStringContainsString('Viene en 4 semanas', $nota->notes);
    }
}
