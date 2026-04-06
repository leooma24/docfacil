<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlanLimitsTest extends TestCase
{
    use RefreshDatabase;
    private function createDoctor(array $clinicOverrides = []): User
    {
        $clinic = Clinic::create(array_merge([
            'name' => 'Test Clinic',
            'plan' => 'free',
            'trial_ends_at' => now()->addDays(15),
            'onboarding_status' => 'completed',
        ], $clinicOverrides));

        $user = User::forceCreate([
            'name' => 'Doctor',
            'email' => 'plan-test-' . rand(1000, 9999) . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        Doctor::create(['user_id' => $user->id, 'clinic_id' => $clinic->id]);

        return $user;
    }

    public function test_expired_trial_redirects_to_upgrade(): void
    {
        $user = $this->createDoctor([
            'trial_ends_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get('/doctor/patients/create');
        $response->assertRedirect(route('filament.doctor.pages.upgrade'));
    }

    public function test_active_trial_allows_access(): void
    {
        $user = $this->createDoctor([
            'trial_ends_at' => now()->addDays(10),
        ]);

        $response = $this->actingAs($user)->get('/doctor/patients');
        $response->assertStatus(200);
    }

    public function test_expired_beta_redirects_to_upgrade(): void
    {
        $user = $this->createDoctor([
            'plan' => 'profesional',
            'is_beta' => true,
            'beta_ends_at' => now()->subDay(),
            'trial_ends_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get('/doctor/patients/create');
        $response->assertRedirect(route('filament.doctor.pages.upgrade'));
    }

    public function test_upgrade_page_loads(): void
    {
        $user = $this->createDoctor();

        $response = $this->actingAs($user)->get('/doctor/upgrade');
        $response->assertStatus(200);
        $response->assertSee('Actualizar Plan');
    }

    public function test_new_doctor_redirects_to_onboarding(): void
    {
        $user = $this->createDoctor([
            'onboarding_status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/doctor');
        $response->assertRedirect(route('filament.doctor.pages.onboarding'));
    }
}
