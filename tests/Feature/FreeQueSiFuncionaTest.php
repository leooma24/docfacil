<?php

namespace Tests\Feature;

use App\Exceptions\LimiteDeCitasAlcanzado;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El plan Free sí funciona después de la prueba.
 *
 * La página promete "Free para siempre (1 doctor, 15 pacientes)", pero al
 * vencer los 15 días el sistema mandaba a "actualizar plan" en cualquier
 * alta o edición: en la práctica, Free era de solo lectura. Decisión de Omar
 * (4-oct-2026): lo prometido se cumple, con sus límites.
 *
 * Y los límites no se cumplían: el de 10 citas al mes y el de 1 doctor
 * buscaban rutas en inglés ("appointments", "doctor-invitations") que no
 * existen, porque las del panel están en español.
 */
class FreeQueSiFuncionaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'free', 'trial_ends_at' => now()->subDay(),
            'is_active' => true, 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function citas(int $cuantas): void
    {
        for ($i = 0; $i < $cuantas; $i++) {
            Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
                'starts_at' => now()->startOfMonth()->addDays($i)->setTime(10, 0), 'ends_at' => now()->startOfMonth()->addDays($i)->setTime(10, 30),
                'status' => 'completed']);
        }
    }

    // ── Después de la prueba, Free sigue funcionando ─────────────

    public function test_free_despues_de_la_prueba_puede_agregar_pacientes_y_citas(): void
    {
        $this->get('/doctor/pacientes/create')->assertOk();
        $this->get('/doctor/citas/create')->assertOk();
        $this->get('/doctor/pacientes/' . $this->ana->id . '/edit')->assertOk();
    }

    public function test_el_beta_vencido_si_se_sigue_mandando_a_actualizar(): void
    {
        $this->clinica->update(['plan' => 'basico', 'is_beta' => true, 'beta_ends_at' => now()->subDay(), 'plan_ends_at' => now()->addMonth()]);

        $this->get('/doctor/pacientes/create')->assertRedirect(route('filament.doctor.pages.actualizar-plan'));
    }

    // ── Sus límites sí se cumplen ────────────────────────────────

    public function test_free_tiene_diez_citas_al_mes(): void
    {
        $this->assertSame(10, $this->clinica->limiteDeCitasDelMes());

        $this->citas(9);
        $this->assertTrue($this->clinica->puedeAgendar());

        $this->citas(1);
        $this->assertFalse($this->clinica->puedeAgendar());
        $this->get('/doctor/citas/create')->assertRedirect(route('filament.doctor.pages.actualizar-plan'));
    }

    public function test_la_cita_once_no_se_crea_por_ningun_camino(): void
    {
        $this->citas(10);

        $this->expectException(LimiteDeCitasAlcanzado::class);
        $this->citas(1);
    }

    public function test_durante_la_prueba_y_en_planes_de_pago_no_hay_tope_de_citas(): void
    {
        $this->clinica->update(['trial_ends_at' => now()->addDays(5)]);
        $this->assertNull($this->clinica->fresh()->limiteDeCitasDelMes());

        $this->clinica->forceFill(['plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'trial_ends_at' => now()->subDay()])->save();
        $this->assertNull($this->clinica->fresh()->limiteDeCitasDelMes());

        // Puesto a mano, sin fecha: no se le corta la agenda.
        $this->clinica->forceFill(['plan' => 'profesional', 'plan_ends_at' => null])->save();
        $this->assertNull($this->clinica->fresh()->limiteDeCitasDelMes());

        // El que ya venció sí cae al tope de Free.
        $this->clinica->forceFill(['plan' => 'basico', 'plan_ends_at' => now()->subDay()])->save();
        $this->assertSame(10, $this->clinica->fresh()->limiteDeCitasDelMes());
    }

    public function test_las_citas_del_mes_pasado_no_cuentan(): void
    {
        for ($i = 0; $i < 10; $i++) {
            Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
                'starts_at' => now()->subMonthNoOverflow()->startOfMonth()->addDays($i)->setTime(10, 0),
                'ends_at' => now()->subMonthNoOverflow()->startOfMonth()->addDays($i)->setTime(10, 30), 'status' => 'completed']);
        }

        $this->assertTrue($this->clinica->puedeAgendar());
    }

    public function test_al_llegar_al_tope_el_formulario_avisa_en_vez_de_tronar(): void
    {
        $this->citas(10);

        \Livewire\Livewire::test(\App\Filament\Doctor\Resources\AppointmentResource\Pages\CreateAppointment::class)
            ->fillForm(['patient_id' => $this->ana->id, 'doctor_id' => $this->doctor->id,
                'starts_at' => now()->addDay()->setTime(10, 0)->toDateTimeString(), 'ends_at' => now()->addDay()->setTime(10, 30)->toDateTimeString(), 'status' => 'scheduled'])
            ->call('create')
            ->assertNotified('Llegaste al tope de tu plan');

        $this->assertSame(10, Appointment::count());
    }

    public function test_la_consulta_sin_cita_tambien_avisa(): void
    {
        $this->citas(10);

        \Livewire\Livewire::withQueryParams(['patient' => $this->ana->id])
            ->test(\App\Filament\Doctor\Pages\Consultation::class)
            ->assertNotified('Llegaste al tope de tu plan');

        $this->assertSame(10, Appointment::count());
    }

    public function test_free_tiene_un_doctor(): void
    {
        $this->get('/doctor/invitar-doctores/create')->assertRedirect(route('filament.doctor.pages.actualizar-plan'));
    }
}
