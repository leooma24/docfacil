<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\CheckInQR;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La pantalla de la sala de espera: quién está en consulta y quién sigue.
 *
 * Idea de Omar (octubre 2026). Se abre en una tele o tablet de la sala con
 * una liga firmada, como la del QR de llegada, y se actualiza sola.
 *
 * En la sala hay otras personas y esto es información de salud: se ve el
 * nombre y la inicial del apellido ("Ana R."), nunca el nombre completo ni
 * a qué viene.
 */
class PantallaDeSalaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $user;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'slug' => 'consultorio-sonrisas', 'plan' => 'basico',
            'plan_ends_at' => now()->addMonth(), 'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate(['name' => 'Ana Martínez', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
    }

    private function cita(string $nombre, string $apellido, string $hora, string $estado = 'confirmed', array $extra = [], ?Clinic $clinica = null): Appointment
    {
        $clinica ??= $this->clinica;
        $paciente = Patient::create(['clinic_id' => $clinica->id, 'first_name' => $nombre, 'last_name' => $apellido]);
        [$h, $m] = explode(':', $hora);

        return Appointment::create(array_merge([
            'clinic_id' => $clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $paciente->id,
            'starts_at' => today()->setTime((int) $h, (int) $m), 'ends_at' => today()->setTime((int) $h, (int) $m)->addMinutes(30),
            'status' => $estado,
        ], $extra));
    }

    private function pantalla(?Clinic $clinica = null)
    {
        return $this->get(URL::signedRoute('sala.pantalla', ['slug' => ($clinica ?? $this->clinica)->slug]));
    }

    // ── Lo que se ve ─────────────────────────────────────────────

    public function test_dice_quien_esta_en_consulta_con_nombre_e_inicial(): void
    {
        $this->cita('Ana Sofía', 'Ruiz Pérez', '09:45', 'in_progress');

        $this->pantalla()->assertOk()
            ->assertSee('En consulta')
            ->assertSee('Ana R.')
            ->assertDontSee('Ruiz')
            ->assertDontSee('Sofía');
    }

    public function test_dice_quien_sigue_en_orden_y_quien_ya_esta_en_la_sala(): void
    {
        $this->cita('Luis', 'Mora', '11:00');
        $this->cita('Carla', 'Vega', '10:30', 'scheduled', ['arrived_at' => now()->subMinutes(5)]);

        $this->pantalla()->assertOk()
            ->assertSeeInOrder(['Siguen', 'Carla V.', '10:30', 'Luis M.', '11:00'])
            ->assertSee('Ya llegó');
    }

    public function test_no_dice_a_que_viene_el_paciente(): void
    {
        $servicio = \App\Models\Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Extracción de tercer molar', 'price' => 900, 'duration_minutes' => 60, 'is_active' => true]);
        $this->cita('Luis', 'Mora', '11:00', 'confirmed', ['service_id' => $servicio->id]);

        $this->pantalla()->assertOk()->assertSee('Luis M.')->assertDontSee('Extracción');
    }

    public function test_no_muestra_canceladas_terminadas_de_otro_dia_ni_de_otro_consultorio(): void
    {
        $this->cita('Cancelada', 'Uno', '11:00', 'cancelled');
        $this->cita('Terminada', 'Dos', '09:00', 'completed');
        $this->cita('Mañana', 'Tres', '11:00', 'confirmed', ['starts_at' => today()->addDay()->setTime(11, 0), 'ends_at' => today()->addDay()->setTime(11, 30)]);
        $otra = Clinic::create(['name' => 'Otro', 'slug' => 'otro', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->cita('Ajeno', 'Cuatro', '11:00', 'confirmed', [], $otra);

        $this->pantalla()->assertOk()
            ->assertDontSee('Cancelada U.')
            ->assertDontSee('Terminada D.')
            ->assertDontSee('Mañana T.')
            ->assertDontSee('Ajeno C.');
    }

    public function test_el_que_ya_paso_su_hora_y_no_llego_no_estorba(): void
    {
        $this->cita('Faltó', 'Cinco', '08:00');

        $this->pantalla()->assertOk()->assertDontSee('Faltó C.');
    }

    public function test_se_actualiza_sola(): void
    {
        $this->pantalla()->assertOk()->assertSee('http-equiv="refresh"', false);
    }

    // ── Quién la puede abrir ─────────────────────────────────────

    public function test_sin_firma_no_abre(): void
    {
        $this->get('/clinica/' . $this->clinica->slug . '/sala')->assertForbidden();
    }

    public function test_el_plan_gratis_no_la_tiene(): void
    {
        $this->clinica->update(['plan' => 'free', 'plan_ends_at' => null, 'trial_ends_at' => now()->subDay()]);

        $this->pantalla()->assertNotFound();
    }

    // ── El doctor la encuentra junto al QR ───────────────────────

    public function test_la_pagina_del_qr_da_la_liga_de_la_pantalla(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->user);

        Livewire::test(CheckInQR::class)
            ->assertSee('Pantalla de la sala')
            ->assertSee(URL::signedRoute('sala.pantalla', ['slug' => $this->clinica->slug]), false);
    }
}
