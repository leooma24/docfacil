<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\TreatmentPlanResource\Pages\EditTreatmentPlan;
use App\Models\Clinic;
use App\Models\ClinicAddon;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * "Armar presupuesto" deja al doctor en la pantalla del presupuesto, y ahí
 * solo había "Borrar": para mandarlo había que volver a la lista y buscarlo.
 */
class PresupuestoSeEnviaDesdeSuPantallaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_presupuesto_se_manda_por_whatsapp_desde_su_pantalla(): void
    {
        $clinic = Clinic::create(['name' => 'Consultorio Test', 'plan' => 'basico', 'plan_ends_at' => now()->addYear(), 'onboarding_status' => 'completed']);
        ClinicAddon::create(['clinic_id' => $clinic->id, 'addon_slug' => 'treatment_plans', 'status' => 'active', 'monthly_price' => 129, 'started_at' => now()]);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $clinic->id]);
        $doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $clinic->id, 'specialty' => 'Odontología']);
        $paciente = Patient::create(['clinic_id' => $clinic->id, 'first_name' => 'Carlos', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $plan = TreatmentPlan::create(['clinic_id' => $clinic->id, 'patient_id' => $paciente->id, 'doctor_id' => $doctor->id, 'title' => 'Plan', 'status' => 'draft', 'discount' => 0, 'total' => 1400]);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);

        Livewire::test(EditTreatmentPlan::class, ['record' => $plan->id])
            ->assertActionExists('pdf')
            ->callAction('send_whatsapp')
            ->assertRedirectContains('https://wa.me/526681234567');

        $this->assertSame('sent', $plan->fresh()->status);
        $this->assertNotNull($plan->fresh()->public_token);
    }
}
