<?php

namespace Tests\Feature;

use App\Filament\Paciente\Resources\MyAppointmentResource;
use App\Filament\Paciente\Resources\MyPaymentResource;
use App\Filament\Paciente\Resources\MyPrescriptionResource;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Un paciente dentro del portal tiene que ver lo suyo y nada más.
 * Es información médica: que se filtre la de otro paciente del mismo
 * consultorio sería lo peor que puede pasar aquí.
 */
class PatientPortalIsolationTest extends TestCase
{
    use RefreshDatabase;

    private User $usuarioMaria;

    private Patient $maria;

    private Patient $roberto;

    protected function setUp(): void
    {
        parent::setUp();

        $clinica = Clinic::create([
            'name' => 'Consultorio Sonrisas',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed',
        ]);

        $doctorUser = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $clinica->id,
        ]);
        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'clinic_id' => $clinica->id,
            'specialty' => 'Odontología',
        ]);

        $this->usuarioMaria = User::forceCreate([
            'name' => 'María Elena García',
            'email' => 'maria@example.test',
            'password' => bcrypt('password'),
            'role' => 'patient',
            'email_verified_at' => now(),
            'clinic_id' => $clinica->id,
        ]);

        $this->maria = Patient::create([
            'clinic_id' => $clinica->id,
            'user_id' => $this->usuarioMaria->id,
            'first_name' => 'María Elena',
            'last_name' => 'García',
            'email' => 'maria@example.test',
        ]);

        // Otro paciente del MISMO consultorio.
        $this->roberto = Patient::create([
            'clinic_id' => $clinica->id,
            'first_name' => 'Roberto',
            'last_name' => 'Pérez',
        ]);

        foreach ([$this->maria, $this->roberto] as $p) {
            Appointment::create([
                'clinic_id' => $clinica->id,
                'patient_id' => $p->id,
                'doctor_id' => $doctor->id,
                'starts_at' => now()->addDay(),
                'ends_at' => now()->addDay()->addMinutes(30),
                'status' => 'scheduled',
            ]);
            Payment::create([
                'clinic_id' => $clinica->id,
                'patient_id' => $p->id,
                'amount' => 500,
                'payment_method' => 'cash',
                'status' => 'paid',
                'payment_date' => now()->toDateString(),
            ]);
            Prescription::create([
                'clinic_id' => $clinica->id,
                'patient_id' => $p->id,
                'doctor_id' => $doctor->id,
                'prescription_date' => now()->toDateString(),
            ]);
        }

        Filament::setCurrentPanel(Filament::getPanel('paciente'));
        $this->actingAs($this->usuarioMaria);
    }

    public function test_solo_ve_sus_citas(): void
    {
        Livewire::test(MyAppointmentResource\Pages\ListMyAppointments::class)
            ->assertCanSeeTableRecords(Appointment::where('patient_id', $this->maria->id)->get())
            ->assertCanNotSeeTableRecords(Appointment::where('patient_id', $this->roberto->id)->get());
    }

    public function test_solo_ve_sus_pagos(): void
    {
        Livewire::test(MyPaymentResource\Pages\ListMyPayments::class)
            ->assertCanSeeTableRecords(Payment::where('patient_id', $this->maria->id)->get())
            ->assertCanNotSeeTableRecords(Payment::where('patient_id', $this->roberto->id)->get());
    }

    public function test_solo_ve_sus_recetas(): void
    {
        Livewire::test(MyPrescriptionResource\Pages\ListMyPrescriptions::class)
            ->assertCanSeeTableRecords(Prescription::where('patient_id', $this->maria->id)->get())
            ->assertCanNotSeeTableRecords(Prescription::where('patient_id', $this->roberto->id)->get());
    }

    public function test_el_paciente_no_puede_crear_nada(): void
    {
        $this->assertFalse(MyAppointmentResource::canCreate());
        $this->assertFalse(MyPaymentResource::canCreate());
        $this->assertFalse(MyPrescriptionResource::canCreate());
    }

    /**
     * El rol 'patient' solo abre el panel del paciente. Los otros tres le
     * responden 403, no una redireccion al login: ya esta autenticado, lo
     * que no tiene es permiso.
     */
    public function test_el_paciente_no_entra_a_los_otros_paneles(): void
    {
        foreach (['/doctor', '/admin', '/ventas'] as $panel) {
            $this->get($panel)->assertForbidden();
        }

        $this->get('/paciente')->assertSuccessful();
    }
}
