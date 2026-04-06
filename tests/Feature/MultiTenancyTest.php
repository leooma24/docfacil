<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;
    private function createDoctorWithClinic(string $clinicName, string $email): array
    {
        $clinic = Clinic::create(['name' => $clinicName, 'onboarding_status' => 'completed']);
        $user = User::forceCreate([
            'name' => "Dr. {$clinicName}",
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'specialty' => 'General',
        ]);

        return [$user, $clinic, $doctor];
    }

    public function test_doctor_cannot_see_other_clinic_patients(): void
    {
        [$userA, $clinicA] = $this->createDoctorWithClinic('Clinic A', 'a@test.com');
        [$userB, $clinicB] = $this->createDoctorWithClinic('Clinic B', 'b@test.com');

        Patient::create(['clinic_id' => $clinicA->id, 'first_name' => 'Patient', 'last_name' => 'A']);
        Patient::create(['clinic_id' => $clinicB->id, 'first_name' => 'Patient', 'last_name' => 'B']);

        // Doctor A page loads
        $response = $this->actingAs($userA)->get('/doctor/patients');
        $response->assertStatus(200);

        // Verify via query scoping that doctor A only sees their patients
        $this->actingAs($userA);
        $query = \App\Filament\Doctor\Resources\PatientResource::getEloquentQuery();
        $this->assertEquals(1, $query->count());
        $this->assertEquals('Patient', $query->first()->first_name);
    }

    public function test_doctor_cannot_see_other_clinic_services(): void
    {
        [$userA, $clinicA] = $this->createDoctorWithClinic('Clinic C', 'c@test.com');
        [$userB, $clinicB] = $this->createDoctorWithClinic('Clinic D', 'd@test.com');

        Service::create(['clinic_id' => $clinicA->id, 'name' => 'ServiceA', 'price' => 100]);
        Service::create(['clinic_id' => $clinicB->id, 'name' => 'ServiceB', 'price' => 200]);

        $this->actingAs($userA);
        $query = \App\Filament\Doctor\Resources\ServiceResource::getEloquentQuery();
        $this->assertEquals(1, $query->count());
        $this->assertEquals('ServiceA', $query->first()->name);
    }

    public function test_doctor_cannot_access_other_clinic_patient_by_id(): void
    {
        [$userA, $clinicA] = $this->createDoctorWithClinic('Clinic E', 'e@test.com');
        [$userB, $clinicB] = $this->createDoctorWithClinic('Clinic F', 'f@test.com');

        $patientB = Patient::create(['clinic_id' => $clinicB->id, 'first_name' => 'Secret', 'last_name' => 'Patient']);

        $response = $this->actingAs($userA)->get("/doctor/patients/{$patientB->id}/edit");
        $response->assertStatus(404);
    }
}
