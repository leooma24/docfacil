<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica el aislamiento REAL de datos entre clínicas a nivel de query,
 * sin depender de que la página HTTP cargue (los tests que hacen GET a
 * paneles Filament fallan en el entorno de test por razones de sesión,
 * pero eso no dice nada sobre si los datos están aislados).
 *
 * Esto es lo que de verdad importa: que un doctor de la clínica A NUNCA
 * pueda leer datos de la clínica B, ni siquiera consultando el modelo
 * directamente.
 */
class TenantIsolationDataTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinicA;
    private Clinic $clinicB;
    private User $userA;
    private User $userB;
    private Doctor $doctorA;
    private Doctor $doctorB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinicA = Clinic::create(['name' => 'Clinica A', 'onboarding_status' => 'completed']);
        $this->clinicB = Clinic::create(['name' => 'Clinica B', 'onboarding_status' => 'completed']);

        $this->userA = User::forceCreate([
            'name' => 'Dr. A', 'email' => 'a@test.com', 'password' => bcrypt('x'),
            'role' => 'doctor', 'clinic_id' => $this->clinicA->id,
        ]);
        $this->userB = User::forceCreate([
            'name' => 'Dr. B', 'email' => 'b@test.com', 'password' => bcrypt('x'),
            'role' => 'doctor', 'clinic_id' => $this->clinicB->id,
        ]);

        $this->doctorA = Doctor::create([
            'user_id' => $this->userA->id, 'clinic_id' => $this->clinicA->id, 'specialty' => 'odontologia',
        ]);
        $this->doctorB = Doctor::create([
            'user_id' => $this->userB->id, 'clinic_id' => $this->clinicB->id, 'specialty' => 'odontologia',
        ]);
    }

    public function test_patients_are_isolated_between_clinics(): void
    {
        $this->actingAs($this->userA);
        Patient::create(['clinic_id' => $this->clinicA->id, 'first_name' => 'Ana', 'last_name' => 'A']);

        $this->actingAs($this->userB);
        Patient::create(['clinic_id' => $this->clinicB->id, 'first_name' => 'Beto', 'last_name' => 'B']);

        $this->actingAs($this->userA);
        $visibles = Patient::all();
        $this->assertCount(1, $visibles, 'Doctor A debe ver solo su paciente');
        $this->assertEquals('Ana', $visibles->first()->first_name);

        $this->actingAs($this->userB);
        $visibles = Patient::all();
        $this->assertCount(1, $visibles, 'Doctor B debe ver solo su paciente');
        $this->assertEquals('Beto', $visibles->first()->first_name);
    }

    public function test_patient_of_other_clinic_is_not_findable_by_id(): void
    {
        $this->actingAs($this->userB);
        $patientB = Patient::create(['clinic_id' => $this->clinicB->id, 'first_name' => 'Secreto', 'last_name' => 'B']);

        $this->actingAs($this->userA);
        $encontrado = Patient::find($patientB->id);

        $this->assertNull($encontrado, 'Doctor A NO debe poder cargar el paciente de la clínica B ni sabiendo su ID');
    }

    public function test_appointments_are_isolated(): void
    {
        $this->actingAs($this->userA);
        $pA = Patient::create(['clinic_id' => $this->clinicA->id, 'first_name' => 'Ana', 'last_name' => 'A']);
        Appointment::create([
            'clinic_id' => $this->clinicA->id, 'doctor_id' => $this->doctorA->id, 'patient_id' => $pA->id,
            'starts_at' => now(), 'ends_at' => now()->addMinutes(30), 'status' => 'scheduled',
        ]);

        $this->actingAs($this->userB);
        $pB = Patient::create(['clinic_id' => $this->clinicB->id, 'first_name' => 'Beto', 'last_name' => 'B']);
        Appointment::create([
            'clinic_id' => $this->clinicB->id, 'doctor_id' => $this->doctorB->id, 'patient_id' => $pB->id,
            'starts_at' => now(), 'ends_at' => now()->addMinutes(30), 'status' => 'scheduled',
        ]);

        $this->actingAs($this->userA);
        $this->assertCount(1, Appointment::all(), 'Doctor A ve solo sus citas');

        $this->actingAs($this->userB);
        $this->assertCount(1, Appointment::all(), 'Doctor B ve solo sus citas');
    }

    public function test_medical_records_are_isolated(): void
    {
        $this->actingAs($this->userA);
        $pA = Patient::create(['clinic_id' => $this->clinicA->id, 'first_name' => 'Ana', 'last_name' => 'A']);
        MedicalRecord::create([
            'clinic_id' => $this->clinicA->id, 'patient_id' => $pA->id, 'doctor_id' => $this->doctorA->id,
            'visit_date' => now()->toDateString(), 'diagnosis' => 'Diagnostico privado de A',
        ]);

        $this->actingAs($this->userB);
        $registros = MedicalRecord::all();

        $this->assertCount(0, $registros, 'Doctor B NO debe ver ningún expediente de la clínica A');
    }

    public function test_payments_are_isolated(): void
    {
        $this->actingAs($this->userA);
        $pA = Patient::create(['clinic_id' => $this->clinicA->id, 'first_name' => 'Ana', 'last_name' => 'A']);
        Payment::create([
            'clinic_id' => $this->clinicA->id, 'patient_id' => $pA->id, 'amount' => 5000,
            'payment_method' => 'cash', 'status' => 'paid', 'payment_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->userB);
        $this->assertCount(0, Payment::all(), 'Doctor B NO debe ver cobros de la clínica A');
    }

    public function test_services_are_isolated(): void
    {
        $this->actingAs($this->userA);
        Service::create(['clinic_id' => $this->clinicA->id, 'name' => 'Servicio A', 'price' => 500, 'duration_minutes' => 30, 'is_active' => true]);

        $this->actingAs($this->userB);
        $this->assertCount(0, Service::all(), 'Doctor B NO debe ver servicios de la clínica A');
    }

    public function test_new_records_get_correct_clinic_id_automatically(): void
    {
        $this->actingAs($this->userA);
        $p = Patient::create(['first_name' => 'SinClinicId', 'last_name' => 'Test']);

        $this->assertEquals(
            $this->clinicA->id,
            $p->clinic_id,
            'El trait BelongsToClinic debe autoasignar el clinic_id del usuario logueado'
        );
    }
}
