<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_doctor_login_page_loads(): void
    {
        $response = $this->get('/doctor/login');
        $response->assertStatus(200);
    }

    public function test_admin_login_page_loads(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_paciente_login_page_loads(): void
    {
        $response = $this->get('/paciente/login');
        $response->assertStatus(200);
    }

    public function test_admin_registration_is_disabled(): void
    {
        $response = $this->get('/admin/register');
        $response->assertStatus(404);
    }

    public function test_paciente_registration_is_disabled(): void
    {
        $response = $this->get('/paciente/register');
        $response->assertStatus(404);
    }

    public function test_doctor_registration_page_loads(): void
    {
        $response = $this->get('/doctor/register');
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_admin_panel(): void
    {
        $admin = User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_doctor_cannot_access_admin_panel(): void
    {
        $clinic = Clinic::create(['name' => 'Test Clinic', 'onboarding_status' => 'completed']);
        $user = User::forceCreate([
            'name' => 'Doctor',
            'email' => 'doc@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_doctor_can_access_doctor_panel(): void
    {
        $clinic = Clinic::create(['name' => 'Test Clinic', 'onboarding_status' => 'completed']);
        $user = User::forceCreate([
            'name' => 'Doctor',
            'email' => 'doc2@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        $response = $this->actingAs($user)->get('/doctor');
        $response->assertStatus(200);
    }
}
