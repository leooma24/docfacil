<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\DoctorInvitation;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;
    public function test_invitation_page_loads_with_valid_token(): void
    {
        $clinic = Clinic::create(['name' => 'Invite Clinic']);
        $inviter = User::forceCreate([
            'name' => 'Inviter',
            'email' => 'inviter@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        $invitation = DoctorInvitation::create([
            'clinic_id' => $clinic->id,
            'invited_by' => $inviter->id,
            'email' => 'invited@test.com',
            'name' => 'Dr. Invited',
        ]);

        $response = $this->get("/invitation/{$invitation->token}");
        $response->assertStatus(200);
        $response->assertSee('Dr. Invited');
    }

    public function test_expired_invitation_returns_410(): void
    {
        $clinic = Clinic::create(['name' => 'Expired Clinic']);
        $inviter = User::forceCreate([
            'name' => 'Inviter2',
            'email' => 'inviter2@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        $invitation = DoctorInvitation::create([
            'clinic_id' => $clinic->id,
            'invited_by' => $inviter->id,
            'email' => 'expired@test.com',
            'name' => 'Dr. Expired',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->get("/invitation/{$invitation->token}");
        $response->assertStatus(410);
    }

    public function test_invalid_token_returns_404(): void
    {
        $response = $this->get('/invitation/invalid-token-12345');
        $response->assertStatus(404);
    }
}
