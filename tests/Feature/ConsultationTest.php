<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConsultationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Clinic $clinic;
    private Doctor $doctor;
    private Patient $patient;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create(['name' => 'Test Clinic', 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->doctor = Doctor::create([
            'user_id' => $this->user->id,
            'clinic_id' => $this->clinic->id,
            'specialty' => 'General',
        ]);
        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'phone' => '5551234567',
        ]);
        $this->service = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Consulta General',
            'price' => 500,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
    }

    private function createAppointment(array $overrides = []): Appointment
    {
        return Appointment::create(array_merge([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'service_id' => $this->service->id,
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(30),
            'status' => 'scheduled',
        ], $overrides));
    }

    private function testWithAppointment(Appointment $appointment)
    {
        return Livewire::actingAs($this->user)
            ->withQueryParams(['appointment' => $appointment->id])
            ->test(Consultation::class);
    }

    // ── Walk-in mode ─────────────────────────────────────────────

    public function test_page_loads_in_walkin_mode_without_appointment(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/doctor/consulta');
        $response->assertStatus(200);
    }

    public function test_walkin_mode_sets_step_zero_and_flag(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->assertSet('isWalkIn', true)
            ->assertSet('currentStep', 0);
    }

    public function test_walkin_data_initializes_with_null_keys(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->assertSet('data.walkin_patient_id', null)
            ->assertSet('data.walkin_service_id', null);
    }

    // ── Appointment mode ─────────────────────────────────────────

    public function test_page_loads_with_existing_appointment(): void
    {
        $appointment = $this->createAppointment();

        $this->actingAs($this->user);

        $response = $this->get('/doctor/consulta?appointment=' . $appointment->id);
        $response->assertStatus(200);
    }

    public function test_appointment_mode_sets_step_one(): void
    {
        $appointment = $this->createAppointment(['status' => 'scheduled']);

        $this->testWithAppointment($appointment)
            ->assertSet('isWalkIn', false)
            ->assertSet('currentStep', 1);
    }

    public function test_appointment_marks_status_in_progress(): void
    {
        $appointment = $this->createAppointment(['status' => 'scheduled']);

        $this->testWithAppointment($appointment);

        $this->assertEquals('in_progress', $appointment->fresh()->status);
    }

    public function test_appointment_prefills_payment_from_service(): void
    {
        $appointment = $this->createAppointment();

        $this->testWithAppointment($appointment)
            ->assertSet('payment_service_id', (string) $this->service->id)
            ->assertSet('payment_amount', (string) $this->service->price);
    }

    public function test_confirmed_appointment_also_marked_in_progress(): void
    {
        $appointment = $this->createAppointment(['status' => 'confirmed']);

        $this->testWithAppointment($appointment);

        $this->assertEquals('in_progress', $appointment->fresh()->status);
    }

    // ── startWalkIn ──────────────────────────────────────────────

    public function test_start_walkin_creates_appointment(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->service->id)
            ->call('startWalkIn')
            ->assertSet('isWalkIn', false)
            ->assertSet('currentStep', 1);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->id,
            'service_id' => $this->service->id,
            'status' => 'in_progress',
            'clinic_id' => $this->clinic->id,
        ]);
    }

    public function test_start_walkin_without_patient_does_nothing(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->call('startWalkIn')
            ->assertSet('isWalkIn', true)
            ->assertSet('currentStep', 0);

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_start_walkin_without_service_still_works(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->call('startWalkIn')
            ->assertSet('currentStep', 1);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->id,
            'service_id' => null,
            'status' => 'in_progress',
        ]);
    }

    // ── createQuickPatient ───────────────────────────────────────

    public function test_create_quick_patient_creates_and_starts_walkin(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('new_first_name', 'María')
            ->set('new_last_name', 'López')
            ->set('new_phone', '5559999999')
            ->call('createQuickPatient')
            ->assertSet('isWalkIn', false)
            ->assertSet('currentStep', 1)
            ->assertSet('showNewPatientForm', false);

        $this->assertDatabaseHas('patients', [
            'first_name' => 'María',
            'last_name' => 'López',
            'phone' => '5559999999',
            'clinic_id' => $this->clinic->id,
        ]);

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_create_quick_patient_requires_name(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('new_first_name', '')
            ->set('new_last_name', '')
            ->call('createQuickPatient')
            ->assertSet('isWalkIn', true);

        $this->assertDatabaseCount('patients', 1); // only the setUp patient
    }

    // ── Step navigation ──────────────────────────────────────────

    public function test_next_step_increments(): void
    {
        $appointment = $this->createAppointment();

        $this->testWithAppointment($appointment)
            ->call('nextStep')
            ->assertSet('currentStep', 2)
            ->call('nextStep')
            ->assertSet('currentStep', 3);
    }

    public function test_prev_step_decrements(): void
    {
        $appointment = $this->createAppointment();

        $this->testWithAppointment($appointment)
            ->set('currentStep', 3)
            ->call('prevStep')
            ->assertSet('currentStep', 2)
            ->call('prevStep')
            ->assertSet('currentStep', 1);
    }

    public function test_step_cannot_go_below_one(): void
    {
        $appointment = $this->createAppointment();

        $this->testWithAppointment($appointment)
            ->call('prevStep')
            ->assertSet('currentStep', 1);
    }

    public function test_step_cannot_go_above_five(): void
    {
        $appointment = $this->createAppointment();

        $this->testWithAppointment($appointment)
            ->set('currentStep', 5)
            ->call('nextStep')
            ->assertSet('currentStep', 5);
    }

    public function test_go_to_step_jumps_directly(): void
    {
        $appointment = $this->createAppointment();

        $this->testWithAppointment($appointment)
            ->call('goToStep', 4)
            ->assertSet('currentStep', 4);
    }

    // ── saveAndComplete ──────────────────────────────────────────

    public function test_save_and_complete_creates_medical_record(): void
    {
        $appointment = $this->createAppointment();

        $this->actingAs($this->user);
        $response = $this->get('/doctor/consulta?appointment=' . $appointment->id);
        $response->assertStatus(200);

        // Verify the appointment was loaded and marked in progress
        $this->assertEquals('in_progress', $appointment->fresh()->status);

        // Now test saveAndComplete via Livewire with a fresh walk-in approach
        // Create appointment through walk-in so it stays in component state
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->service->id)
            ->call('startWalkIn')
            ->set('blood_pressure', '120/80')
            ->set('heart_rate', '72')
            ->set('temperature', '36.5')
            ->set('weight', '70')
            ->set('chief_complaint', 'Dolor de cabeza')
            ->set('diagnosis', 'Migraña')
            ->set('treatment', 'Ibuprofeno 400mg')
            ->set('medical_notes', 'Paciente estable')
            ->call('saveAndComplete')
            ->assertSet('completed', true)
            ->assertSet('currentStep', 6);

        $this->assertDatabaseHas('medical_records', [
            'patient_id' => $this->patient->id,
            'chief_complaint' => 'Dolor de cabeza',
            'diagnosis' => 'Migraña',
            'treatment' => 'Ibuprofeno 400mg',
            'notes' => 'Paciente estable',
        ]);
    }

    public function test_save_and_complete_creates_prescription_with_medications(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->service->id)
            ->call('startWalkIn')
            ->set('diagnosis', 'Infección')
            ->set('medications', [
                [
                    'medication' => 'Amoxicilina',
                    'dosage' => '500mg',
                    'frequency' => 'Cada 8 horas',
                    'duration' => '7 días',
                    'instructions' => 'Tomar con alimentos',
                ],
                [
                    'medication' => 'Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => 'Cada 6 horas',
                    'duration' => '3 días',
                    'instructions' => 'Si hay fiebre',
                ],
            ])
            ->set('prescription_notes', 'Regresar si no mejora')
            ->call('saveAndComplete');

        $this->assertDatabaseCount('prescriptions', 1);
        $this->assertDatabaseCount('prescription_items', 2);

        $this->assertDatabaseHas('prescription_items', [
            'medication' => 'Amoxicilina',
            'dosage' => '500mg',
            'frequency' => 'Cada 8 horas',
        ]);

        $this->assertDatabaseHas('prescription_items', [
            'medication' => 'Paracetamol',
            'dosage' => '500mg',
        ]);
    }

    public function test_save_and_complete_skips_prescription_without_medications(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->call('startWalkIn')
            ->set('diagnosis', 'Revisión')
            ->call('saveAndComplete');

        $this->assertDatabaseCount('prescriptions', 0);
        $this->assertDatabaseCount('prescription_items', 0);
    }

    public function test_save_and_complete_creates_payment(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->service->id)
            ->call('startWalkIn')
            ->set('payment_service_id', (string) $this->service->id)
            ->set('payment_amount', '500')
            ->set('payment_method', 'card')
            ->call('saveAndComplete');

        $this->assertDatabaseHas('payments', [
            'patient_id' => $this->patient->id,
            'amount' => 500,
            'payment_method' => 'card',
            'status' => 'paid',
        ]);
    }

    public function test_save_and_complete_skips_payment_when_zero(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->call('startWalkIn')
            ->set('payment_amount', '0')
            ->call('saveAndComplete');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_save_and_complete_creates_next_appointment(): void
    {
        $nextDate = now()->addDays(7)->format('Y-m-d H:i:s');

        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->service->id)
            ->call('startWalkIn')
            ->set('next_appointment_date', $nextDate)
            ->set('next_appointment_service_id', (string) $this->service->id)
            ->call('saveAndComplete');

        // Walk-in appointment + next appointment
        $this->assertDatabaseCount('appointments', 2);
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->id,
            'service_id' => $this->service->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_save_and_complete_full_flow(): void
    {
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->service->id)
            ->call('startWalkIn')
            // Vitals
            ->set('blood_pressure', '130/85')
            ->set('heart_rate', '80')
            ->set('temperature', '37.2')
            ->set('weight', '75')
            // Diagnosis
            ->set('chief_complaint', 'Fiebre y tos')
            ->set('diagnosis', 'Gripa')
            ->set('treatment', 'Reposo e hidratación')
            // Prescription
            ->set('medications', [
                ['medication' => 'Antigripal', 'dosage' => '1 sobre', 'frequency' => 'Cada 8h', 'duration' => '5 días', 'instructions' => ''],
            ])
            // Payment
            ->set('payment_amount', '500')
            ->set('payment_method', 'cash')
            // Next appointment
            ->set('next_appointment_date', now()->addDays(14)->format('Y-m-d H:i:s'))
            ->call('saveAndComplete')
            ->assertSet('completed', true)
            ->assertSet('currentStep', 6);

        $this->assertDatabaseCount('medical_records', 1);
        $this->assertDatabaseCount('prescriptions', 1);
        $this->assertDatabaseCount('prescription_items', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('appointments', 2); // walk-in + next
    }

    // ── Payment service update ───────────────────────────────────

    public function test_updating_payment_service_updates_amount(): void
    {
        $otherService = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Limpieza',
            'price' => 800,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->service->id)
            ->call('startWalkIn')
            ->assertSet('payment_amount', (string) $this->service->price)
            ->set('payment_service_id', (string) $otherService->id)
            ->assertSet('payment_amount', (string) $otherService->fresh()->price);
    }

    // ── History toggle ───────────────────────────────────────────

    public function test_toggle_history(): void
    {
        $appointment = $this->createAppointment();

        $this->testWithAppointment($appointment)
            ->assertSet('showHistory', false)
            ->call('toggleHistory')
            ->assertSet('showHistory', true)
            ->call('toggleHistory')
            ->assertSet('showHistory', false);
    }

    // ── Multi-tenancy isolation ──────────────────────────────────

    public function test_cannot_access_other_clinic_appointment(): void
    {
        $otherClinic = Clinic::create(['name' => 'Other Clinic', 'onboarding_status' => 'completed']);
        $otherUser = User::forceCreate([
            'name' => 'Dr. Other',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $otherClinic->id,
        ]);
        $otherDoctor = Doctor::create([
            'user_id' => $otherUser->id,
            'clinic_id' => $otherClinic->id,
            'specialty' => 'General',
        ]);
        $otherPatient = Patient::create([
            'clinic_id' => $otherClinic->id,
            'first_name' => 'Otro',
            'last_name' => 'Paciente',
        ]);

        $otherAppointment = Appointment::create([
            'clinic_id' => $otherClinic->id,
            'doctor_id' => $otherDoctor->id,
            'patient_id' => $otherPatient->id,
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(30),
            'status' => 'scheduled',
        ]);

        // Our doctor tries to access the other clinic's appointment
        Livewire::actingAs($this->user)
            ->withQueryParams(['appointment' => $otherAppointment->id])
            ->test(Consultation::class)
            ->assertSet('appointment', null)
            ->assertSet('isWalkIn', true);
    }

    // ── E2E: flujo completo crear paciente + servicio + consulta ──

    public function test_e2e_create_patient_inline_and_complete_consultation(): void
    {
        // Doctor abre consulta sin paciente ni servicio existente
        // Crea paciente inline via createQuickPatient, luego completa toda la consulta
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->assertSet('isWalkIn', true)
            ->assertSet('currentStep', 0)
            // Crea paciente nuevo inline
            ->set('new_first_name', 'Nuevo')
            ->set('new_last_name', 'Paciente')
            ->set('new_phone', '5550001111')
            ->set('new_email', 'nuevo@test.com')
            ->call('createQuickPatient')
            ->assertSet('isWalkIn', false)
            ->assertSet('currentStep', 1);

        // Verificar que se creó el paciente y la cita
        $this->assertDatabaseHas('patients', [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5550001111',
            'email' => 'nuevo@test.com',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->assertDatabaseCount('appointments', 1);
        $this->assertDatabaseHas('appointments', [
            'status' => 'in_progress',
            'clinic_id' => $this->clinic->id,
        ]);
    }

    public function test_e2e_select_patient_and_service_then_complete(): void
    {
        // Flujo completo: seleccionar paciente existente + servicio, walkin, consulta, pago
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->assertSet('isWalkIn', true)
            // Selecciona paciente y servicio existentes
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->service->id)
            ->call('startWalkIn')
            ->assertSet('currentStep', 1)
            // Step 1: Signos vitales
            ->set('blood_pressure', '120/80')
            ->set('heart_rate', '72')
            ->set('temperature', '36.5')
            ->set('weight', '68')
            ->call('nextStep')
            ->assertSet('currentStep', 2)
            // Step 2: Diagnóstico
            ->set('chief_complaint', 'Dolor de garganta')
            ->set('diagnosis', 'Faringitis')
            ->set('treatment', 'Antibiótico + antiinflamatorio')
            ->set('medical_notes', 'Sin alergias conocidas')
            ->call('nextStep')
            ->assertSet('currentStep', 3)
            // Step 3: Receta
            ->set('medications', [
                ['medication' => 'Azitromicina', 'dosage' => '500mg', 'frequency' => 'Cada 24h', 'duration' => '3 días', 'instructions' => 'En ayunas'],
                ['medication' => 'Ibuprofeno', 'dosage' => '400mg', 'frequency' => 'Cada 8h', 'duration' => '5 días', 'instructions' => 'Con alimentos'],
            ])
            ->set('prescription_notes', 'Regresar si persiste la fiebre')
            ->call('nextStep')
            ->assertSet('currentStep', 4)
            // Step 4: Cobro
            ->assertSet('payment_amount', (string) $this->service->price)
            ->set('payment_method', 'card')
            ->call('nextStep')
            ->assertSet('currentStep', 5)
            // Step 5: Siguiente cita
            ->set('next_appointment_date', now()->addDays(7)->format('Y-m-d H:i:s'))
            ->set('next_appointment_service_id', (string) $this->service->id)
            // Guardar y completar
            ->call('saveAndComplete')
            ->assertSet('completed', true)
            ->assertSet('currentStep', 6);

        // Verificar todo el flujo en BD
        $this->assertDatabaseCount('appointments', 2); // walkin + próxima
        $this->assertDatabaseHas('appointments', ['status' => 'completed']);
        $this->assertDatabaseHas('appointments', ['status' => 'scheduled']);

        $this->assertDatabaseCount('medical_records', 1);
        $this->assertDatabaseHas('medical_records', [
            'chief_complaint' => 'Dolor de garganta',
            'diagnosis' => 'Faringitis',
        ]);

        $record = MedicalRecord::first();
        $vitals = $record->vital_signs;
        $this->assertEquals('120/80', $vitals['blood_pressure']);
        $this->assertEquals('72', $vitals['heart_rate']);

        $this->assertDatabaseCount('prescriptions', 1);
        $this->assertDatabaseCount('prescription_items', 2);
        $this->assertDatabaseHas('prescription_items', ['medication' => 'Azitromicina']);
        $this->assertDatabaseHas('prescription_items', ['medication' => 'Ibuprofeno']);

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'card',
            'status' => 'paid',
        ]);
    }

    public function test_e2e_walkin_with_service_prefills_payment(): void
    {
        // Verifica que al seleccionar servicio en walk-in, el pago se pre-llena
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->service->id)
            ->call('startWalkIn')
            ->assertSet('payment_service_id', (string) $this->service->id)
            ->assertSet('payment_amount', (string) $this->service->price);
    }

    public function test_e2e_walkin_without_service_has_empty_payment(): void
    {
        // Walk-in sin servicio: pago vacío
        Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->call('startWalkIn')
            ->assertSet('payment_service_id', null)
            ->assertSet('payment_amount', '');
    }

    public function test_patient_search_only_returns_own_clinic(): void
    {
        $otherClinic = Clinic::create(['name' => 'Other Clinic', 'onboarding_status' => 'completed']);
        Patient::create([
            'clinic_id' => $otherClinic->id,
            'first_name' => 'Invisible',
            'last_name' => 'Patient',
        ]);

        $this->actingAs($this->user);
        $patients = (new Consultation)->patientsList;

        $this->assertCount(1, $patients);
        $this->assertStringContainsString('Juan', array_values($patients)[0]);
    }
}
