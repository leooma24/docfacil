<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\AppointmentResource;
use App\Filament\Doctor\Resources\MedicalRecordResource;
use App\Filament\Doctor\Resources\PatientResource;
use App\Filament\Doctor\Resources\PaymentResource;
use App\Filament\Doctor\Resources\PrescriptionResource;
use App\Filament\Doctor\Resources\ServiceResource;
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
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DoctorResourcesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Clinic $clinic;
    private Doctor $doctor;

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

        // Set the current Filament panel to "doctor"
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->user);
    }

    // ══════════════════════════════════════════════════════════════
    //  PatientResource
    // ══════════════════════════════════════════════════════════════

    public function test_patient_list_page_loads(): void
    {
        $this->get(PatientResource::getUrl('index'))->assertSuccessful();
    }

    public function test_patient_create_page_loads(): void
    {
        $this->get(PatientResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_patient(): void
    {
        Livewire::test(PatientResource\Pages\CreatePatient::class)
            ->fillForm([
                'first_name' => 'María',
                'last_name' => 'García',
                'email' => 'maria@test.com',
                'phone' => '5551234567',
                'gender' => 'female',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('patients', [
            'first_name' => 'María',
            'last_name' => 'García',
            'clinic_id' => $this->clinic->id,
        ]);
    }

    public function test_patient_requires_name(): void
    {
        Livewire::test(PatientResource\Pages\CreatePatient::class)
            ->fillForm([
                'first_name' => '',
                'last_name' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['first_name' => 'required', 'last_name' => 'required']);
    }

    public function test_can_edit_patient(): void
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);

        Livewire::test(PatientResource\Pages\EditPatient::class, ['record' => $patient->getRouteKey()])
            ->fillForm([
                'first_name' => 'Juan Carlos',
                'phone' => '5559999999',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('Juan Carlos', $patient->fresh()->first_name);
        $this->assertEquals('5559999999', $patient->fresh()->phone);
    }

    public function test_patient_list_shows_patients(): void
    {
        Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Ana',
            'last_name' => 'López',
        ]);

        Livewire::test(PatientResource\Pages\ListPatients::class)
            ->assertCanSeeTableRecords(Patient::where('clinic_id', $this->clinic->id)->get());
    }

    public function test_patient_list_does_not_show_other_clinic(): void
    {
        $otherClinic = Clinic::create(['name' => 'Other', 'onboarding_status' => 'completed']);
        $otherPatient = Patient::create([
            'clinic_id' => $otherClinic->id,
            'first_name' => 'Invisible',
            'last_name' => 'Patient',
        ]);

        Livewire::test(PatientResource\Pages\ListPatients::class)
            ->assertCanNotSeeTableRecords(collect([$otherPatient]));
    }

    // ══════════════════════════════════════════════════════════════
    //  ServiceResource
    // ══════════════════════════════════════════════════════════════

    public function test_service_list_page_loads(): void
    {
        $this->get(ServiceResource::getUrl('index'))->assertSuccessful();
    }

    public function test_service_create_page_loads(): void
    {
        $this->get(ServiceResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_service(): void
    {
        Livewire::test(ServiceResource\Pages\CreateService::class)
            ->fillForm([
                'name' => 'Limpieza Dental',
                'price' => 800,
                'duration_minutes' => 45,
                'category' => 'Preventivo',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('services', [
            'name' => 'Limpieza Dental',
            'clinic_id' => $this->clinic->id,
        ]);
    }

    public function test_service_requires_name_and_price(): void
    {
        Livewire::test(ServiceResource\Pages\CreateService::class)
            ->fillForm([
                'name' => '',
                'price' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'price' => 'required']);
    }

    public function test_can_edit_service(): void
    {
        $service = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Consulta',
            'price' => 500,
            'is_active' => true,
        ]);

        Livewire::test(ServiceResource\Pages\EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm([
                'price' => 600,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(600, $service->fresh()->price);
    }

    public function test_service_list_does_not_show_other_clinic(): void
    {
        $otherClinic = Clinic::create(['name' => 'Other', 'onboarding_status' => 'completed']);
        $otherService = Service::create([
            'clinic_id' => $otherClinic->id,
            'name' => 'Secret Service',
            'price' => 100,
            'is_active' => true,
        ]);

        Livewire::test(ServiceResource\Pages\ListServices::class)
            ->assertCanNotSeeTableRecords(collect([$otherService]));
    }

    // ══════════════════════════════════════════════════════════════
    //  AppointmentResource
    // ══════════════════════════════════════════════════════════════

    public function test_appointment_list_page_loads(): void
    {
        $this->get(AppointmentResource::getUrl('index'))->assertSuccessful();
    }

    public function test_appointment_create_page_loads(): void
    {
        $this->get(AppointmentResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_appointment(): void
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);
        $service = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Consulta',
            'price' => 500,
            'is_active' => true,
        ]);

        Livewire::test(AppointmentResource\Pages\CreateAppointment::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'doctor_id' => $this->doctor->id,
                'service_id' => $service->id,
                'starts_at' => now()->addDay()->setHour(10)->setMinute(0),
                'ends_at' => now()->addDay()->setHour(10)->setMinute(30),
                'status' => 'scheduled',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'scheduled',
            'clinic_id' => $this->clinic->id,
        ]);
    }

    public function test_appointment_requires_patient_and_dates(): void
    {
        Livewire::test(AppointmentResource\Pages\CreateAppointment::class)
            ->fillForm([
                'patient_id' => null,
                'doctor_id' => null,
                'starts_at' => null,
                'ends_at' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['patient_id', 'doctor_id', 'starts_at', 'ends_at']);
    }

    public function test_can_edit_appointment(): void
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $patient->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(30),
            'status' => 'scheduled',
        ]);

        Livewire::test(AppointmentResource\Pages\EditAppointment::class, ['record' => $appointment->getRouteKey()])
            ->fillForm([
                'status' => 'confirmed',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('confirmed', $appointment->fresh()->status);
    }

    public function test_appointment_list_does_not_show_other_clinic(): void
    {
        $otherClinic = Clinic::create(['name' => 'Other', 'onboarding_status' => 'completed']);
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
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(30),
            'status' => 'scheduled',
        ]);

        Livewire::test(AppointmentResource\Pages\ListAppointments::class)
            ->assertCanNotSeeTableRecords(collect([$otherAppointment]));
    }

    // ══════════════════════════════════════════════════════════════
    //  MedicalRecordResource
    // ══════════════════════════════════════════════════════════════

    public function test_medical_record_list_page_loads(): void
    {
        $this->get(MedicalRecordResource::getUrl('index'))->assertSuccessful();
    }

    public function test_medical_record_create_page_loads(): void
    {
        $this->get(MedicalRecordResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_medical_record(): void
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);

        Livewire::test(MedicalRecordResource\Pages\CreateMedicalRecord::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'doctor_id' => $this->doctor->id,
                'visit_date' => now()->toDateString(),
                'chief_complaint' => 'Dolor de muelas',
                'diagnosis' => 'Caries',
                'treatment' => 'Obturación',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('medical_records', [
            'patient_id' => $patient->id,
            'diagnosis' => 'Caries',
            'clinic_id' => $this->clinic->id,
        ]);
    }

    public function test_medical_record_requires_patient_and_doctor(): void
    {
        Livewire::test(MedicalRecordResource\Pages\CreateMedicalRecord::class)
            ->fillForm([
                'patient_id' => null,
                'doctor_id' => null,
                'visit_date' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['patient_id', 'doctor_id', 'visit_date']);
    }

    public function test_can_edit_medical_record(): void
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);
        $record = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => now()->toDateString(),
            'diagnosis' => 'Gripa',
        ]);

        Livewire::test(MedicalRecordResource\Pages\EditMedicalRecord::class, ['record' => $record->getRouteKey()])
            ->fillForm([
                'diagnosis' => 'Gripa severa',
                'treatment' => 'Antibiótico',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('Gripa severa', $record->fresh()->diagnosis);
    }

    public function test_medical_record_list_does_not_show_other_clinic(): void
    {
        $otherClinic = Clinic::create(['name' => 'Other', 'onboarding_status' => 'completed']);
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
        $otherRecord = MedicalRecord::create([
            'clinic_id' => $otherClinic->id,
            'patient_id' => $otherPatient->id,
            'doctor_id' => $otherDoctor->id,
            'visit_date' => now()->toDateString(),
        ]);

        Livewire::test(MedicalRecordResource\Pages\ListMedicalRecords::class)
            ->assertCanNotSeeTableRecords(collect([$otherRecord]));
    }

    // ══════════════════════════════════════════════════════════════
    //  PrescriptionResource
    // ══════════════════════════════════════════════════════════════

    public function test_prescription_list_page_loads(): void
    {
        $this->get(PrescriptionResource::getUrl('index'))->assertSuccessful();
    }

    public function test_prescription_create_page_loads(): void
    {
        $this->get(PrescriptionResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_prescription(): void
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);

        Livewire::test(PrescriptionResource\Pages\CreatePrescription::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'doctor_id' => $this->doctor->id,
                'prescription_date' => now()->toDateString(),
                'diagnosis' => 'Infección',
                'items' => [
                    [
                        'medication' => 'Amoxicilina',
                        'dosage' => '500mg',
                        'frequency' => 'Cada 8 horas',
                        'duration' => '7 días',
                        'instructions' => 'Con alimentos',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $patient->id,
            'diagnosis' => 'Infección',
            'clinic_id' => $this->clinic->id,
        ]);

        $this->assertDatabaseHas('prescription_items', [
            'medication' => 'Amoxicilina',
            'dosage' => '500mg',
        ]);
    }

    public function test_prescription_requires_patient_and_doctor(): void
    {
        Livewire::test(PrescriptionResource\Pages\CreatePrescription::class)
            ->fillForm([
                'patient_id' => null,
                'doctor_id' => null,
                'prescription_date' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['patient_id', 'doctor_id', 'prescription_date']);
    }

    public function test_can_edit_prescription(): void
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);
        $prescription = Prescription::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Gripa',
        ]);

        Livewire::test(PrescriptionResource\Pages\EditPrescription::class, ['record' => $prescription->getRouteKey()])
            ->fillForm([
                'diagnosis' => 'Gripa severa',
                'notes' => 'Reposo 3 días',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('Gripa severa', $prescription->fresh()->diagnosis);
    }

    public function test_prescription_list_does_not_show_other_clinic(): void
    {
        $otherClinic = Clinic::create(['name' => 'Other', 'onboarding_status' => 'completed']);
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
        $otherPrescription = Prescription::create([
            'clinic_id' => $otherClinic->id,
            'patient_id' => $otherPatient->id,
            'doctor_id' => $otherDoctor->id,
            'prescription_date' => now()->toDateString(),
        ]);

        Livewire::test(PrescriptionResource\Pages\ListPrescriptions::class)
            ->assertCanNotSeeTableRecords(collect([$otherPrescription]));
    }

    // ══════════════════════════════════════════════════════════════
    //  PaymentResource
    // ══════════════════════════════════════════════════════════════

    public function test_payment_list_page_loads(): void
    {
        $this->get(PaymentResource::getUrl('index'))->assertSuccessful();
    }

    public function test_payment_create_page_loads(): void
    {
        $this->get(PaymentResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_payment(): void
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);
        $service = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Consulta',
            'price' => 500,
            'is_active' => true,
        ]);

        Livewire::test(PaymentResource\Pages\CreatePayment::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'service_id' => $service->id,
                'amount' => 500,
                'payment_method' => 'cash',
                'status' => 'paid',
                'payment_date' => now()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('payments', [
            'patient_id' => $patient->id,
            'amount' => 500,
            'payment_method' => 'cash',
            'status' => 'paid',
            'clinic_id' => $this->clinic->id,
        ]);
    }

    public function test_payment_requires_patient_amount_date(): void
    {
        Livewire::test(PaymentResource\Pages\CreatePayment::class)
            ->fillForm([
                'patient_id' => null,
                'amount' => '',
                'payment_date' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['patient_id', 'amount', 'payment_date']);
    }

    public function test_can_edit_payment(): void
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);
        $payment = Payment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $patient->id,
            'amount' => 500,
            'payment_method' => 'cash',
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        Livewire::test(PaymentResource\Pages\EditPayment::class, ['record' => $payment->getRouteKey()])
            ->fillForm([
                'payment_method' => 'card',
                'notes' => 'Pagó con Visa',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('card', $payment->fresh()->payment_method);
    }

    public function test_payment_list_does_not_show_other_clinic(): void
    {
        $otherClinic = Clinic::create(['name' => 'Other', 'onboarding_status' => 'completed']);
        $otherPatient = Patient::create([
            'clinic_id' => $otherClinic->id,
            'first_name' => 'Otro',
            'last_name' => 'Paciente',
        ]);
        $otherPayment = Payment::create([
            'clinic_id' => $otherClinic->id,
            'patient_id' => $otherPatient->id,
            'amount' => 999,
            'payment_method' => 'cash',
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        Livewire::test(PaymentResource\Pages\ListPayments::class)
            ->assertCanNotSeeTableRecords(collect([$otherPayment]));
    }
}
