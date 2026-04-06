<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;
    public function test_role_is_guarded_on_mass_assignment(): void
    {
        $user = User::create([
            'name' => 'Hacker',
            'email' => 'hacker@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin', // should be ignored
        ]);

        // Role should be default (doctor), not super_admin
        $this->assertNotEquals('super_admin', $user->role);
    }

    public function test_demo_user_cannot_modify_data(): void
    {
        $clinic = Clinic::create(['name' => 'Demo Clinic', 'onboarding_status' => 'completed']);
        $demo = User::forceCreate([
            'name' => 'Demo',
            'email' => 'demo@docfacil.com',
            'password' => bcrypt('demo2026'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        // Demo user trying to create a patient should be blocked
        $response = $this->actingAs($demo)->post('/doctor/patients', [
            'first_name' => 'Test',
            'last_name' => 'Patient',
        ]);

        // Should not be a successful creation
        $this->assertDatabaseMissing('patients', ['first_name' => 'Test', 'last_name' => 'Patient']);
    }

    public function test_contact_form_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/contacto', [
                'name' => "Test {$i}",
                'email' => "rate{$i}@test.com",
                'phone' => '555000000' . $i,
            ]);
        }

        // 6th request should be rate limited
        $response->assertStatus(429);
    }

    public function test_unauthenticated_cannot_access_doctor_panel(): void
    {
        $response = $this->get('/doctor');
        $response->assertRedirect('/doctor/login');
    }

    public function test_unauthenticated_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }
}
