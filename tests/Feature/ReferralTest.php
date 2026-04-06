<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Referral;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_gets_referral_code_on_creation(): void
    {
        $user = User::forceCreate([
            'name' => 'Dr. Test Referral',
            'email' => 'ref-test@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
        ]);

        $this->assertNotNull($user->referral_code);
        $this->assertGreaterThan(3, strlen($user->referral_code));
    }

    public function test_referral_extends_trial_for_both(): void
    {
        $clinicA = Clinic::create([
            'name' => 'Clinic Referrer',
            'trial_ends_at' => now()->addDays(15),
        ]);
        $referrer = User::forceCreate([
            'name' => 'Dr. Referrer',
            'email' => 'referrer@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinicA->id,
            'referral_code' => 'TESTCODE',
        ]);

        $clinicB = Clinic::create([
            'name' => 'Clinic Referred',
            'trial_ends_at' => now()->addDays(15),
        ]);
        $referred = User::forceCreate([
            'name' => 'Dr. Referred',
            'email' => 'referred@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinicB->id,
        ]);

        Referral::processReferral($referred, 'TESTCODE');

        $clinicA->refresh();
        $clinicB->refresh();

        // Both should have extended trials (15 + 15 = 30 days)
        $this->assertTrue($clinicA->trial_ends_at->greaterThan(now()->addDays(25)));
        $this->assertTrue($clinicB->trial_ends_at->greaterThan(now()->addDays(25)));

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'status' => 'rewarded',
        ]);
    }

    public function test_self_referral_does_not_work(): void
    {
        $clinic = Clinic::create(['name' => 'Self Clinic', 'trial_ends_at' => now()->addDays(15)]);
        $user = User::forceCreate([
            'name' => 'Dr. Self',
            'email' => 'self@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
            'referral_code' => 'SELFCODE',
        ]);

        Referral::processReferral($user, 'SELFCODE');

        $this->assertDatabaseMissing('referrals', ['referrer_id' => $user->id]);
    }
}
