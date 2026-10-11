<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\AgendaDelDia;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Doctor\Resources\ConsentFormResource\Pages\CreateConsentForm;
use App\Filament\Doctor\Resources\OdontogramResource\Pages\EditOdontogram;
use App\Filament\Doctor\Resources\TreatmentPlanResource\Pages\CreateTreatmentPlan;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Día 10 del plan (10-oct-2026): lo que falta para que se sienta
 * profesional.
 * - Las alertas médicas en todas las pantallas del paciente: también en el
 *   odontograma, los consentimientos, los presupuestos y el día por doctor.
 * - La agenda marca a quien le faltan datos (teléfono, fecha de nacimiento).
 * - La historia clínica en PDF lleva folio y número de hoja.
 */
class CierreDelPlanTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-12 08:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz',
            'allergies' => 'Penicilina']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function cita(): Appointment
    {
        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'starts_at' => '2026-10-12 10:00', 'ends_at' => '2026-10-12 10:30', 'status' => 'scheduled']);
    }

    // ── Datos que faltan ────────────────────────────────────────

    public function test_sabe_que_datos_le_faltan_al_paciente(): void
    {
        $this->assertSame(['teléfono', 'fecha de nacimiento'], $this->ana->datosQueFaltan());

        $this->ana->update(['phone' => '6681234567', 'birth_date' => '1990-05-01']);
        $this->assertSame([], $this->ana->fresh()->datosQueFaltan());
    }

    public function test_la_lista_de_citas_marca_los_datos_que_faltan(): void
    {
        $this->cita();

        Livewire::test(ListAppointments::class)->assertSee('Faltan datos');
    }

    public function test_el_dia_por_doctor_marca_alertas_y_datos_que_faltan(): void
    {
        $this->cita();

        Livewire::test(AgendaDelDia::class)
            ->assertSee('Alergia: Penicilina')
            ->assertSee('Faltan datos');
    }

    // ── Alertas en todas las pantallas del paciente ─────────────

    public function test_al_hacer_un_consentimiento_sale_la_alerta(): void
    {
        Livewire::test(CreateConsentForm::class)
            ->fillForm(['patient_id' => $this->ana->id])
            ->assertSee('Alergia: Penicilina');
    }

    public function test_al_hacer_un_presupuesto_sale_la_alerta(): void
    {
        Livewire::test(CreateTreatmentPlan::class)
            ->fillForm(['patient_id' => $this->ana->id])
            ->assertSee('Alergia: Penicilina');
    }

    public function test_en_el_odontograma_sale_la_alerta(): void
    {
        $odontograma = Odontogram::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'doctor_id' => $this->doctor->id,
            'evaluation_date' => '2026-10-12']);

        Livewire::test(EditOdontogram::class, ['record' => $odontograma->getRouteKey()])
            ->assertSee('Alergia: Penicilina');
    }

    // ── Historia clínica foliada ────────────────────────────────

    public function test_la_historia_clinica_lleva_folio(): void
    {
        $html = view('pdf.expediente', ['patient' => $this->ana->load(['clinic', 'medicalRecords', 'prescriptions', 'consentForms', 'odontograms'])])->render();

        $this->assertStringContainsString('Folio EXP-' . str_pad((string) $this->ana->id, 6, '0', STR_PAD_LEFT), $html);
        $this->assertStringContainsString('counter(page)', $html, 'Número de hoja en cada página');
    }
}
