<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La primera entrada de un doctor recien registrado.
 *
 * Se rompia: VerifyClinicPlan lo mandaba a /doctor/configuracion por tener el
 * onboarding pendiente, y Filament lo regresaba a verificar su correo, que
 * caia otra vez en el middleware. 38 redirecciones y ERR_TOO_MANY_REDIRECTS.
 * La cuenta quedaba inservible desde el primer segundo.
 */
class NewDoctorFirstEntryTest extends TestCase
{
    use RefreshDatabase;

    private function doctorRecienRegistrado(bool $correoVerificado = false): User
    {
        $clinic = Clinic::create([
            'name' => 'Consultorio Nuevo',
            'plan' => 'free',
            'trial_ends_at' => now()->addDays(15),
            'onboarding_status' => 'pending',
        ]);

        $user = User::forceCreate([
            'name' => 'Dra. Ana Nueva',
            'email' => 'ana.nueva@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
            'email_verified_at' => $correoVerificado ? now() : null,
        ]);

        Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'specialty' => 'Odontología',
        ]);

        return $user;
    }

    public function test_sin_verificar_el_correo_llega_al_aviso_sin_rebotar(): void
    {
        $this->actingAs($this->doctorRecienRegistrado());

        // Sin la excepcion en VerifyClinicPlan, esto rebotaba contra
        // /doctor/configuracion hasta que el navegador cortaba.
        $this->get('/doctor/email-verification/prompt')->assertSuccessful();
    }

    public function test_con_el_correo_verificado_entra_al_onboarding(): void
    {
        $this->actingAs($this->doctorRecienRegistrado(correoVerificado: true));

        $this->get('/doctor')->assertRedirect('/doctor/configuracion');
        $this->get('/doctor/configuracion')->assertSuccessful();
    }

    public function test_la_pagina_de_planes_sigue_alcanzable_con_onboarding_pendiente(): void
    {
        $this->actingAs($this->doctorRecienRegistrado(correoVerificado: true));

        $this->get('/doctor/actualizar-plan')->assertSuccessful();
    }

    public function test_el_consultorio_nuevo_arranca_con_features_de_pro(): void
    {
        $user = $this->doctorRecienRegistrado(correoVerificado: true);

        $this->assertTrue($user->clinic->hasFeature('odontogram'));
        $this->assertTrue($user->clinic->hasFeature('consent_forms'));
        $this->assertTrue($user->clinic->hasFeature('pdf_prescriptions'));
    }
}
