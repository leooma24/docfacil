<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\TreatmentPlanResource;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * El presupuesto lo acepta el paciente tocando un botón, no abrir la liga
 * (auditoría del 12-oct-2026): la vista previa de WhatsApp o un antivirus
 * abren las ligas solos, y el doctor recibía "aceptó su presupuesto" sin que
 * nadie lo aceptara. Y "Por ahora no" podía deshacer uno ya aceptado.
 */
class PresupuestoSeAceptaConUnToqueTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;
    private TreatmentPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $clinica->id]);
        Doctor::create(['user_id' => $this->doctor->id, 'clinic_id' => $clinica->id, 'specialty' => 'Odontología']);
        $ana = Patient::create(['clinic_id' => $clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $this->plan = TreatmentPlan::create(['clinic_id' => $clinica->id, 'patient_id' => $ana->id, 'title' => 'Corona', 'status' => 'sent',
            'subtotal' => 5000, 'discount' => 0, 'total' => 5000, 'public_token' => str_repeat('b', 64)]);
    }

    private function liga(string $que): string
    {
        return URL::signedRoute("treatment-plan.{$que}", ['token' => $this->plan->public_token]);
    }

    public function test_abrir_la_liga_de_aceptar_no_acepta_nada(): void
    {
        $this->get($this->liga('accept'))->assertRedirect(route('treatment-plan.public', ['token' => $this->plan->public_token]));

        $this->assertSame('sent', $this->plan->fresh()->status);
        $this->assertSame(0, $this->doctor->notifications()->count());
    }

    public function test_la_pagina_tiene_botones_que_mandan_el_formulario(): void
    {
        $this->get(route('treatment-plan.public', ['token' => $this->plan->public_token]))->assertOk()
            ->assertSee('method="POST"', false)
            ->assertSee('Aceptar plan')
            ->assertDontSee('quieres');
    }

    public function test_tocar_aceptar_lo_acepta_y_avisa(): void
    {
        $this->post($this->liga('accept'))->assertOk();

        $this->assertSame('accepted', $this->plan->fresh()->status);
        $this->assertSame(1, $this->doctor->notifications()->count());
    }

    public function test_por_ahora_no_avisa_al_consultorio(): void
    {
        $this->post($this->liga('reject'))->assertOk();

        $this->assertSame('rejected', $this->plan->fresh()->status);
        $this->assertStringContainsString('por ahora no', json_encode($this->doctor->notifications()->first()?->data, JSON_UNESCAPED_UNICODE));
    }

    public function test_por_ahora_no_ya_no_deshace_uno_aceptado(): void
    {
        $this->plan->update(['status' => 'accepted', 'accepted_at' => now()]);

        $this->post($this->liga('reject'))->assertNotFound();

        $this->assertSame('accepted', $this->plan->fresh()->status);
    }

    public function test_el_whatsapp_manda_solo_la_liga_para_verlo(): void
    {
        $this->actingAs($this->doctor);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('doctor'));

        $destino = urldecode(TreatmentPlanResource::enviarPorWhatsapp($this->plan)->getTargetUrl());

        $this->assertStringContainsString(route('treatment-plan.public', ['token' => $this->plan->public_token]), $destino);
        $this->assertStringNotContainsString('/aceptar', $destino);
    }
}
