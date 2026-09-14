<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La retención cuenta desde el último acto médico del paciente.
 *
 * Antes contaba por la fecha de cada nota: a un paciente que lleva años
 * viniendo se le habrían borrado sus notas viejas aunque siguiera en
 * tratamiento. La NOM-004 (5.4) cuenta los 5 años desde el ÚLTIMO acto médico.
 */
class RetencionDeExpedientesTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addYear(),
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
        ]);
    }

    private function paciente(string $telefono): Patient
    {
        return Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Paciente',
            'last_name' => $telefono,
            'phone' => $telefono,
        ]);
    }

    private function nota(Patient $paciente, \DateTimeInterface $fecha): void
    {
        MedicalRecord::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $paciente->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => $fecha->format('Y-m-d'),
            'diagnosis' => 'Revisión',
        ]);
    }

    private function notasDe(Patient $paciente): int
    {
        return MedicalRecord::withoutGlobalScopes()->where('patient_id', $paciente->id)->count();
    }

    public function test_un_paciente_que_sigue_viniendo_no_pierde_sus_notas_viejas(): void
    {
        $paciente = $this->paciente('5511111111');
        $this->nota($paciente, now()->subYears(9));
        $this->nota($paciente, now()->subYear());

        $this->artisan('app:retention-report', ['--force' => true])->assertSuccessful();

        $this->assertSame(2, $this->notasDe($paciente));
    }

    public function test_sin_force_solo_reporta(): void
    {
        $paciente = $this->paciente('5522222222');
        $this->nota($paciente, now()->subYears(8));

        $this->artisan('app:retention-report')
            ->expectsOutputToContain('Usa --force')
            ->assertSuccessful();

        $this->assertSame(1, $this->notasDe($paciente));
    }

    public function test_con_force_se_borra_solo_el_expediente_de_quien_ya_no_viene(): void
    {
        $sinVenir = $this->paciente('5533333333');
        $this->nota($sinVenir, now()->subYears(8));

        $activo = $this->paciente('5544444444');
        $this->nota($activo, now()->subYears(8));
        $this->nota($activo, now()->subYears(2));

        $this->artisan('app:retention-report', ['--force' => true])->assertSuccessful();

        $this->assertSame(0, $this->notasDe($sinVenir));
        $this->assertSame(2, $this->notasDe($activo));
    }

    public function test_una_cita_reciente_tambien_cuenta_como_actividad(): void
    {
        $paciente = $this->paciente('5555555555');
        $this->nota($paciente, now()->subYears(8));

        Appointment::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $paciente->id,
            'doctor_id' => $this->doctor->id,
            'starts_at' => now()->subMonths(3),
            'ends_at' => now()->subMonths(3)->addHour(),
            'status' => 'completed',
        ]);

        $this->artisan('app:retention-report', ['--force' => true])->assertSuccessful();

        $this->assertSame(1, $this->notasDe($paciente));
    }
}
