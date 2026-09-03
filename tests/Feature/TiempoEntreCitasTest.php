<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\HuecosDisponibles;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El rato entre un paciente y el siguiente.
 *
 * En odontología no se pueden encadenar citas pegadas: hay que limpiar el
 * sillón, esterilizar y guardar. Antes el portal ofrecía las 11:00 y las
 * 12:00 seguidas para citas de una hora, y el doctor arrancaba tarde.
 *
 * Arranca en 0 para no cambiarle la agenda a nadie de un día para otro.
 */
class TiempoEntreCitasTest extends TestCase
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

    private function cita(string $inicio, string $fin): Appointment
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

    private function conMargen(int $minutos): void
    {
        $this->clinica->update(['minutos_entre_citas' => $minutos]);
        $this->clinica->refresh();
    }

    private function choca(string $inicio, string $fin, int $margen): bool
    {
        return Appointment::traslapes(
            $this->clinica->id,
            $this->doctor->id,
            new \DateTimeImmutable($inicio),
            new \DateTimeImmutable($fin),
            null,
            $margen,
        )->isNotEmpty();
    }

    private function aviso(string $inicio, string $fin): ?string
    {
        return Appointment::avisoDeEspacio(
            $this->clinica->id,
            $this->doctor->id,
            new \DateTimeImmutable($inicio),
            new \DateTimeImmutable($fin),
        );
    }

    /** El próximo lunes, que siempre cae en horario de atención. */
    private function proximoLunes(): CarbonImmutable
    {
        return CarbonImmutable::today()->addWeek()->startOfWeek();
    }

    // ── Qué cuenta como "pegada" ─────────────────────────────────

    public function test_sin_margen_dos_citas_seguidas_no_chocan(): void
    {
        // El default es 0: a nadie le cambia la agenda hasta que lo prenda.
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertFalse($this->choca('2026-09-10 11:00', '2026-09-10 12:00', 0));
    }

    public function test_con_margen_dos_citas_seguidas_si_chocan(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertTrue($this->choca('2026-09-10 11:00', '2026-09-10 12:00', 10));
    }

    public function test_con_margen_alcanza_si_hay_el_hueco_exacto(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertFalse($this->choca('2026-09-10 11:10', '2026-09-10 12:00', 10));
    }

    public function test_el_margen_tambien_aplica_hacia_atras(): void
    {
        // La cita nueva termina justo cuando empieza la que ya estaba.
        $this->cita('2026-09-10 11:00', '2026-09-10 12:00');

        $this->assertTrue($this->choca('2026-09-10 10:00', '2026-09-10 11:00', 15));
        $this->assertFalse($this->choca('2026-09-10 10:00', '2026-09-10 10:45', 15));
    }

    // ── El aviso que ve el doctor ────────────────────────────────

    public function test_sin_margen_configurado_no_hay_aviso(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertNull($this->aviso('2026-09-10 11:00', '2026-09-10 12:00'));
    }

    public function test_el_aviso_dice_con_quien_quedo_pegada(): void
    {
        $this->conMargen(10);
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $aviso = $this->aviso('2026-09-10 11:00', '2026-09-10 12:00');

        $this->assertNotNull($aviso);
        $this->assertStringContainsString('Fernando Morales', $aviso);
        $this->assertStringContainsString('10 minutos', $aviso);
    }

    public function test_un_traslape_de_verdad_no_se_reporta_como_pegada(): void
    {
        // Eso ya lo dice mensajeDeTraslape, y ahí sí bloquea. Repetirlo confunde.
        $this->conMargen(10);
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertNull($this->aviso('2026-09-10 10:30', '2026-09-10 11:30'));
    }

    public function test_cuando_hay_espacio_de_sobra_no_hay_aviso(): void
    {
        $this->conMargen(10);
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertNull($this->aviso('2026-09-10 14:00', '2026-09-10 15:00'));
    }

    // ── Lo que se le ofrece al paciente ──────────────────────────

    public function test_el_selector_no_ofrece_el_hueco_pegado(): void
    {
        $this->conMargen(15);

        $lunes = $this->proximoLunes();
        $this->cita(
            $lunes->setTime(10, 0)->toDateTimeString(),
            $lunes->setTime(11, 0)->toDateTimeString(),
        );

        $huecos = HuecosDisponibles::delDia($this->clinica->fresh(), $lunes, $this->doctor->id, 30);

        $this->assertNotContains('11:00', $huecos, 'Las 11:00 no dejan tiempo de limpiar.');
        $this->assertNotContains('09:30', $huecos, 'Terminaría a las 10:00, pegada a la siguiente.');
    }

    public function test_sin_margen_el_hueco_pegado_si_se_ofrece(): void
    {
        $lunes = $this->proximoLunes();
        $this->cita(
            $lunes->setTime(10, 0)->toDateTimeString(),
            $lunes->setTime(11, 0)->toDateTimeString(),
        );

        $huecos = HuecosDisponibles::delDia($this->clinica->fresh(), $lunes, $this->doctor->id, 30);

        $this->assertContains('11:00', $huecos);
    }

    // ── El portal público rechaza lo que no ofreció ──────────────

    public function test_el_portal_rechaza_una_hora_pegada(): void
    {
        $this->conMargen(15);

        $lunes = $this->proximoLunes();
        $this->cita(
            $lunes->setTime(10, 0)->toDateTimeString(),
            $lunes->setTime(11, 0)->toDateTimeString(),
        );

        $this->post("/clinica/{$this->clinica->slug}/agendar", [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5599887766',
            'doctor_id' => $this->doctor->id,
            'preferred_at' => $lunes->setTime(11, 0)->toDateTimeString(),
        ])->assertSessionHasErrors('preferred_at');

        $this->assertSame(1, Appointment::count(), 'No debe crearse la cita pegada.');
    }

    public function test_cualquiera_tampoco_asigna_un_doctor_que_quedaria_pegado(): void
    {
        $this->conMargen(15);

        $lunes = $this->proximoLunes();
        $this->cita(
            $lunes->setTime(10, 0)->toDateTimeString(),
            $lunes->setTime(11, 0)->toDateTimeString(),
        );

        $this->post("/clinica/{$this->clinica->slug}/agendar", [
            'first_name' => 'Sin',
            'last_name' => 'Preferencia',
            'phone' => '5599887766',
            'preferred_at' => $lunes->setTime(11, 0)->toDateTimeString(),
        ])->assertSessionHasErrors('preferred_at');
    }

    // ── El tope ──────────────────────────────────────────────────

    public function test_el_margen_se_topa_para_no_dejar_la_agenda_sin_huecos(): void
    {
        $this->clinica->update(['minutos_entre_citas' => 999]);

        $this->assertSame(120, $this->clinica->fresh()->minutosEntreCitas());
    }

    public function test_un_margen_negativo_se_trata_como_cero(): void
    {
        $this->clinica->minutos_entre_citas = -30;

        $this->assertSame(0, $this->clinica->minutosEntreCitas());
    }
}
