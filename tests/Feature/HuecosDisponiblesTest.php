<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicClosure;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Services\HuecosDisponibles;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los horarios que se le ofrecen al paciente en el portal público.
 *
 * Antes escribía una fecha y hora a mano y se la rechazábamos si estaba
 * ocupada o fuera de horario. Cada rebote de esos es una oportunidad de que
 * se vaya, así que ahora solo le enseñamos lo que sí puede elegir.
 */
class HuecosDisponiblesTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private Doctor $doctor;

    /** Un martes cualquiera, bien lejos, para que nunca sea "hoy". */
    private const MARTES = '2026-12-08';

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-12-01 08:00'));

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
            'clinic_id' => $this->clinica->id,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $usuario->id,
            'clinic_id' => $this->clinica->id,
            'specialty' => 'Odontología',
        ]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function huecos(string $fecha = self::MARTES, int $duracion = 30): array
    {
        return HuecosDisponibles::delDia(
            $this->clinica->fresh(),
            CarbonImmutable::parse($fecha),
            $this->doctor->id,
            $duracion,
        );
    }

    private function ocupar(string $inicio, string $fin, string $estado = 'scheduled'): Appointment
    {
        $paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Ocupa',
            'last_name' => 'Horario',
        ]);

        return Appointment::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $paciente->id,
            'doctor_id' => $this->doctor->id,
            'starts_at' => $inicio,
            'ends_at' => $fin,
            'status' => $estado,
        ]);
    }

    // ── Lo básico ────────────────────────────────────────────────

    public function test_un_dia_normal_ofrece_huecos_dentro_del_horario(): void
    {
        $huecos = $this->huecos();

        $this->assertContains('09:00', $huecos);
        $this->assertContains('18:30', $huecos);   // cierra a las 19:00
        $this->assertNotContains('08:30', $huecos);
        $this->assertNotContains('19:00', $huecos);
    }

    public function test_el_domingo_no_ofrece_nada(): void
    {
        $this->assertSame([], $this->huecos('2026-12-06'));
    }

    public function test_un_dia_pasado_no_ofrece_nada(): void
    {
        $this->assertSame([], $this->huecos('2026-11-30'));
    }

    // ── Lo que quita huecos ──────────────────────────────────────

    public function test_una_cita_existente_quita_su_hueco(): void
    {
        $this->ocupar(self::MARTES . ' 10:00', self::MARTES . ' 10:30');

        $huecos = $this->huecos();

        $this->assertNotContains('10:00', $huecos);
        $this->assertContains('10:30', $huecos);
    }

    public function test_una_cita_larga_quita_todos_los_huecos_que_pisa(): void
    {
        $this->ocupar(self::MARTES . ' 10:00', self::MARTES . ' 12:00');

        $huecos = $this->huecos();

        foreach (['10:00', '10:30', '11:00', '11:30'] as $tomado) {
            $this->assertNotContains($tomado, $huecos);
        }
        $this->assertContains('12:00', $huecos);
    }

    public function test_una_cita_cancelada_devuelve_su_hueco(): void
    {
        $this->ocupar(self::MARTES . ' 10:00', self::MARTES . ' 10:30', 'cancelled');

        $this->assertContains('10:00', $this->huecos());
    }

    public function test_la_hora_de_comida_no_se_ofrece(): void
    {
        $this->clinica->update(['working_hours' => [
            'martes' => ['abre' => '09:00', 'cierra' => '19:00', 'descanso_de' => '14:00', 'descanso_a' => '16:00'],
        ]]);

        $huecos = $this->huecos();

        $this->assertNotContains('14:00', $huecos);
        $this->assertNotContains('15:30', $huecos);
        $this->assertContains('13:00', $huecos);
        $this->assertContains('16:00', $huecos);
    }

    public function test_en_un_dia_cerrado_no_hay_huecos(): void
    {
        ClinicClosure::create([
            'clinic_id' => $this->clinica->id,
            'starts_on' => self::MARTES,
            'ends_on' => self::MARTES,
            'reason' => 'Congreso',
        ]);

        $this->assertSame([], $this->huecos());
    }

    // ── La duración importa ──────────────────────────────────────

    public function test_una_cita_larga_tiene_menos_huecos(): void
    {
        $cortos = $this->huecos(duracion: 30);
        $largos = $this->huecos(duracion: 90);

        $this->assertLessThan(count($cortos), count($largos));

        // Cerrando a las 19:00, una cita de 90 min no puede empezar a las 18:00.
        $this->assertNotContains('18:00', $largos);
        $this->assertContains('17:30', $largos);
    }

    public function test_una_cita_larga_no_cabe_antes_de_la_comida(): void
    {
        $this->clinica->update(['working_hours' => [
            'martes' => ['abre' => '09:00', 'cierra' => '19:00', 'descanso_de' => '14:00', 'descanso_a' => '16:00'],
        ]]);

        $huecos = $this->huecos(duracion: 90);

        // A las 13:00 terminaría a las 14:30, ya comiendo.
        $this->assertNotContains('13:00', $huecos);
        $this->assertContains('12:30', $huecos);
    }

    // ── El endpoint que consume el portal ────────────────────────

    public function test_el_endpoint_devuelve_los_dias_con_sus_horas(): void
    {
        $respuesta = $this->getJson("/clinica/{$this->clinica->slug}/horarios-libres")
            ->assertSuccessful()
            ->json();

        $this->assertNotEmpty($respuesta['dias']);
        $this->assertArrayHasKey('fecha', $respuesta['dias'][0]);
        $this->assertArrayHasKey('nombre', $respuesta['dias'][0]);
        $this->assertNotEmpty($respuesta['dias'][0]['horas']);
    }

    public function test_el_endpoint_respeta_la_duracion_del_servicio(): void
    {
        $largo = Service::create([
            'clinic_id' => $this->clinica->id,
            'name' => 'Endodoncia',
            'price' => 3500,
            'duration_minutes' => 120,
        ]);

        $conServicio = $this->getJson("/clinica/{$this->clinica->slug}/horarios-libres?service_id={$largo->id}")
            ->assertSuccessful()
            ->json('dias.0.horas');

        $sinServicio = $this->getJson("/clinica/{$this->clinica->slug}/horarios-libres")
            ->assertSuccessful()
            ->json('dias.0.horas');

        $this->assertLessThan(count($sinServicio), count($conServicio));
    }

    public function test_el_endpoint_no_se_abre_sin_el_plan(): void
    {
        $this->clinica->update(['plan' => 'free', 'plan_ends_at' => null, 'trial_ends_at' => now()->subDay()]);

        $this->getJson("/clinica/{$this->clinica->slug}/horarios-libres")->assertForbidden();
    }

    public function test_el_nombre_del_dia_lleva_mayuscula_solo_al_inicio(): void
    {
        // "Lunes 31 de agosto", no "Lunes 31 De Agosto".
        $this->assertSame('Martes 8 de diciembre', HuecosDisponibles::nombreDelDia(self::MARTES));
    }
}
