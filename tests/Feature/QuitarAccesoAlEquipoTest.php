<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\DoctorInvitationResource\Pages\ListDoctorInvitations;
use App\Mail\DoctorInvitationMail;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorInvitation;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Su equipo: a la asistente que se fue se le quita el acceso (auditoría del
 * 12-oct-2026). Antes "Su equipo" listaba invitaciones y borrar una no
 * desactivaba a nadie: la recepcionista que renunció seguía entrando a los
 * expedientes. Y "Reenviar" rompía la liga sin mandar nada.
 */
class QuitarAccesoAlEquipoTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $doctor;
    private User $lupita;
    private DoctorInvitation $invitacion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $this->doctor->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'lupita@test.com', 'password' => bcrypt('x'), 'role' => 'staff', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->invitacion = DoctorInvitation::create(['clinic_id' => $this->clinica->id, 'invited_by' => $this->doctor->id, 'name' => 'Lupita', 'email' => 'lupita@test.com',
            'role' => 'staff', 'token' => str_repeat('t', 64), 'status' => 'accepted', 'expires_at' => now()->addDays(7)]);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    public function test_el_doctor_le_quita_el_acceso_y_ya_no_entra(): void
    {
        $this->actingAs($this->doctor);
        Livewire::test(ListDoctorInvitations::class)
            ->assertTableActionVisible('quitar_acceso', $this->invitacion)
            ->callTableAction('quitar_acceso', $this->invitacion);

        $this->assertFalse($this->lupita->fresh()->canAccessPanel(Filament::getPanel('doctor')));
        $this->actingAs($this->lupita->fresh())->get('/doctor')->assertForbidden();
    }

    public function test_se_le_puede_devolver(): void
    {
        $this->actingAs($this->doctor);
        Livewire::test(ListDoctorInvitations::class)->callTableAction('quitar_acceso', $this->invitacion);
        Livewire::test(ListDoctorInvitations::class)
            ->assertSee('Sin acceso')
            ->callTableAction('devolver_acceso', $this->invitacion);

        $this->assertTrue($this->lupita->fresh()->canAccessPanel(Filament::getPanel('doctor')));
    }

    public function test_reenviar_manda_la_liga_nueva(): void
    {
        Mail::fake();
        $this->invitacion->update(['status' => 'pending']);
        $this->actingAs($this->doctor);

        Livewire::test(ListDoctorInvitations::class)->callTableAction('resend', $this->invitacion);

        Mail::assertSent(DoctorInvitationMail::class, fn ($m) => $m->hasTo('lupita@test.com'));
        $this->assertNotSame(str_repeat('t', 64), $this->invitacion->fresh()->token);
    }
}
