<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El paciente que mueve su cita tres veces.
 *
 * Cada movimiento ya quedaba en la bitácora de actividad, pero ahí nadie lo
 * mira: para el doctor era invisible que el paciente de las 4 ya había
 * cambiado de horario tres veces. Y el que reagenda tres veces casi nunca
 * llega a la cuarta.
 */
class ReagendarSeguidoTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private Doctor $doctor;

    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed',
        ]);

        $usuario = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $usuario->id,
            'clinic_id' => $this->clinica->id,
            'specialty' => 'Odontología',
        ]);

        $this->paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Fernando',
            'last_name' => 'Morales',
            'phone' => '5512345678',
        ]);
    }

    private function cita(string $inicio = '2026-09-10 10:00', string $fin = '2026-09-10 11:00'): Appointment
    {
        return Appointment::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'starts_at' => $inicio,
            'ends_at' => $fin,
            'status' => 'scheduled',
        ]);
    }

    // ── La cuenta ────────────────────────────────────────────────

    public function test_una_cita_nueva_arranca_en_cero(): void
    {
        $this->assertSame(0, (int) $this->cita()->veces_reagendada);
    }

    public function test_cambiar_la_hora_cuenta_como_movimiento(): void
    {
        $cita = $this->cita();

        $cita->update(['starts_at' => '2026-09-10 12:00', 'ends_at' => '2026-09-10 13:00']);

        $this->assertSame(1, (int) $cita->fresh()->veces_reagendada);
    }

    public function test_moverla_tres_veces_cuenta_tres(): void
    {
        $cita = $this->cita();

        $cita->update(['starts_at' => '2026-09-10 12:00']);
        $cita->update(['starts_at' => '2026-09-11 12:00']);
        $cita->update(['starts_at' => '2026-09-12 12:00']);

        $this->assertSame(3, (int) $cita->fresh()->veces_reagendada);
    }

    public function test_cambiar_otra_cosa_no_cuenta(): void
    {
        // Confirmarla, ponerle notas o cambiarle el estado no es reagendar.
        $cita = $this->cita();

        $cita->update(['status' => 'confirmed', 'notes' => 'Llega 10 min antes']);
        $cita->update(['confirmed_at' => now()]);

        $this->assertSame(0, (int) $cita->fresh()->veces_reagendada);
    }

    public function test_guardar_la_misma_hora_no_cuenta(): void
    {
        $cita = $this->cita();

        $cita->update(['starts_at' => '2026-09-10 10:00']);

        $this->assertSame(0, (int) $cita->fresh()->veces_reagendada);
    }

    // ── Cuándo preocuparse ───────────────────────────────────────

    public function test_con_dos_movimientos_todavia_no_preocupa(): void
    {
        $cita = $this->cita();
        $cita->update(['starts_at' => '2026-09-10 12:00']);
        $cita->update(['starts_at' => '2026-09-11 12:00']);

        $this->assertFalse($cita->fresh()->seHaMovidoDeMas());
    }

    public function test_con_tres_movimientos_ya_preocupa(): void
    {
        $cita = $this->cita();
        $cita->update(['starts_at' => '2026-09-10 12:00']);
        $cita->update(['starts_at' => '2026-09-11 12:00']);
        $cita->update(['starts_at' => '2026-09-12 12:00']);

        $this->assertTrue($cita->fresh()->seHaMovidoDeMas());
    }

    // ── A nivel paciente ─────────────────────────────────────────

    public function test_el_paciente_suma_los_movimientos_de_todas_sus_citas(): void
    {
        $una = $this->cita('2026-09-10 10:00', '2026-09-10 11:00');
        $una->update(['starts_at' => '2026-09-10 12:00']);
        $una->update(['starts_at' => '2026-09-10 13:00']);

        $otra = $this->cita('2026-09-20 10:00', '2026-09-20 11:00');
        $otra->update(['starts_at' => '2026-09-20 12:00']);

        $this->assertSame(3, $this->paciente->fresh()->vecesQueHaMovidoCitas());
        $this->assertTrue($this->paciente->fresh()->reagendaDeMas());
    }

    public function test_lo_viejo_ya_no_cuenta_en_su_contra(): void
    {
        // Un paciente que movió sus citas hace dos años no es el mismo caso
        // que uno que las movió el mes pasado.
        $vieja = $this->cita('2023-01-10 10:00', '2023-01-10 11:00');
        $vieja->update(['starts_at' => '2023-01-11 10:00']);
        $vieja->update(['starts_at' => '2023-01-12 10:00']);
        $vieja->update(['starts_at' => '2023-01-13 10:00']);

        $this->assertSame(0, $this->paciente->fresh()->vecesQueHaMovidoCitas());
        $this->assertFalse($this->paciente->fresh()->reagendaDeMas());
    }

    public function test_un_paciente_sin_citas_movidas_no_reagenda_de_mas(): void
    {
        $this->cita();

        $this->assertSame(0, $this->paciente->fresh()->vecesQueHaMovidoCitas());
        $this->assertFalse($this->paciente->fresh()->reagendaDeMas());
    }
}
