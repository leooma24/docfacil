<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Doctor\Resources\MedicalRecordResource\Pages\ListMedicalRecords;
use App\Filament\Doctor\Resources\PaymentPlanResource\Pages\ListPaymentPlans;
use App\Filament\Doctor\Resources\PaymentResource\Pages\ListPayments;
use App\Filament\Doctor\Resources\PrescriptionResource\Pages\ListPrescriptions;
use App\Filament\Doctor\Resources\TreatmentPlanResource\Pages\ListTreatmentPlans;
use App\Filament\Doctor\Widgets\TodayAppointments;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\Prescription;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Ninguna pantalla sin salida.
 *
 * Octubre 2026: la lista de Cobros no llevaba al paciente; la de Recetas y la
 * de Expedientes tampoco; en el perfil, el historial no llevaba a su receta,
 * las recetas no tenían su PDF, los cobros no se podían cobrar y las citas no
 * se podían atender. El doctor veía el dato y tenía que irse a buscar lo que
 * seguía.
 */
class PantallasConSalidaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $ana;

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
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function perfil(): string
    {
        return e(PatientProfile::getUrl(['patient' => $this->ana->id]));
    }

    private function cita(array $datos = []): Appointment
    {
        return Appointment::create(array_merge(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2), 'status' => 'confirmed'], $datos));
    }

    private function cobro(array $datos = []): Payment
    {
        return Payment::create(array_merge(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 500,
            'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => today(), 'due_date' => today()], $datos));
    }

    private function expedienteConReceta(): array
    {
        $cita = $this->cita(['status' => 'completed', 'starts_at' => now()->subDay(), 'ends_at' => now()->subDay()->addHour()]);
        $expediente = MedicalRecord::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'doctor_id' => $this->doctor->id,
            'appointment_id' => $cita->id, 'visit_date' => today()->subDay(), 'diagnosis' => 'Caries']);
        $receta = Prescription::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'doctor_id' => $this->doctor->id,
            'medical_record_id' => $expediente->id, 'prescription_date' => today()->subDay()]);

        return [$cita, $expediente, $receta];
    }

    // ── El nombre del paciente lleva a su perfil, en todas las listas ─

    public function test_la_lista_de_cobros_lleva_al_paciente(): void
    {
        $this->cobro();
        Livewire::test(ListPayments::class)->assertSeeHtml($this->perfil());
    }

    public function test_la_lista_de_recetas_lleva_al_paciente(): void
    {
        $this->expedienteConReceta();
        Livewire::test(ListPrescriptions::class)->assertSeeHtml($this->perfil());
    }

    public function test_la_lista_de_expedientes_lleva_al_paciente(): void
    {
        $this->expedienteConReceta();
        Livewire::test(ListMedicalRecords::class)->assertSeeHtml($this->perfil());
    }

    public function test_la_lista_de_citas_y_las_de_hoy_llevan_al_paciente(): void
    {
        $this->cita();
        Livewire::test(ListAppointments::class)->assertSeeHtml($this->perfil());
        Livewire::test(TodayAppointments::class)->call('loadTable')->assertSeeHtml($this->perfil());
    }

    public function test_presupuestos_y_planes_de_pago_llevan_al_paciente(): void
    {
        TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Plan', 'status' => 'draft', 'subtotal' => 0, 'discount' => 0, 'total' => 0]);
        PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'description' => 'Brackets',
            'total' => 12000, 'down_payment' => 0, 'installments_count' => 12, 'first_due_date' => today()->toDateString()]);

        Livewire::test(ListTreatmentPlans::class)->assertSeeHtml($this->perfil());
        Livewire::test(ListPaymentPlans::class)->assertSeeHtml($this->perfil());
    }

    // ── El perfil: cada pestaña lleva a lo que sigue ─────────────

    private function perfilDeAna()
    {
        return Livewire::withQueryParams(['patient' => $this->ana->id])->test(PatientProfile::class);
    }

    public function test_el_historial_lleva_a_su_receta(): void
    {
        [, , $receta] = $this->expedienteConReceta();

        $this->perfilDeAna()->set('activeTab', 'history')
            ->assertSeeHtml(e(route('prescription.pdf', $receta)));
    }

    public function test_el_historial_dice_como_quedo_el_cobro_de_esa_consulta(): void
    {
        [$cita] = $this->expedienteConReceta();
        $this->cobro(['appointment_id' => $cita->id, 'status' => 'paid']);

        $this->perfilDeAna()->set('activeTab', 'history')->assertSee('Cobro: $500 pagado');
    }

    public function test_cada_receta_trae_su_pdf(): void
    {
        [, , $receta] = $this->expedienteConReceta();

        $this->perfilDeAna()->set('activeTab', 'prescriptions')
            ->assertSee('PDF')
            ->assertSeeHtml(e(route('prescription.pdf', $receta)));
    }

    public function test_el_cobro_pendiente_se_cobra_desde_el_perfil(): void
    {
        $cobro = $this->cobro(['amount' => 700]);

        $this->perfilDeAna()->set('activeTab', 'payments')
            ->mountAction('cobrarUno', ['payment' => $cobro->id])
            ->assertActionDataSet(['monto' => 700.0])
            ->callMountedAction();

        $this->assertSame('paid', $cobro->fresh()->status);
    }

    public function test_no_se_cobra_desde_un_perfil_el_cobro_de_otro_paciente(): void
    {
        $luis = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Luis', 'last_name' => 'Mora']);
        $ajeno = $this->cobro(['patient_id' => $luis->id]);

        $this->perfilDeAna()->callAction('cobrarUno', data: ['monto' => 500, 'payment_method' => 'cash'], arguments: ['payment' => $ajeno->id]);

        $this->assertSame('pending', $ajeno->fresh()->status);
    }

    public function test_la_cita_por_atender_se_atiende_desde_el_perfil(): void
    {
        $cita = $this->cita();
        $terminada = $this->cita(['status' => 'completed', 'starts_at' => now()->subWeek(), 'ends_at' => now()->subWeek()->addHour()]);
        // La de hace días que nadie cerró no se "inicia" hoy desde aquí.
        $vieja = $this->cita(['status' => 'confirmed', 'starts_at' => now()->subDays(3), 'ends_at' => now()->subDays(3)->addHour()]);

        $this->perfilDeAna()->set('activeTab', 'appointments')
            ->assertSeeHtml(e(route('filament.doctor.pages.consulta', ['appointment' => $cita->id])))
            ->assertDontSeeHtml(e(route('filament.doctor.pages.consulta', ['appointment' => $terminada->id])))
            ->assertDontSeeHtml(e(route('filament.doctor.pages.consulta', ['appointment' => $vieja->id])));
    }
}
