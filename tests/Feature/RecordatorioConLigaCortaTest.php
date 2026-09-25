<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\ShortUrl;
use App\Models\User;
use App\Support\RecordatorioDeCita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El recordatorio que le llega al paciente.
 *
 * Traía dos ligas firmadas de 250 caracteres cada una, una para confirmar y
 * otra para cancelar. En el celular eso se ve como un muro de letras y
 * números: da desconfianza, se corta feo, y el paciente no sabe cuál tocar.
 *
 * Ahora va una sola liga corta que abre una página con los dos botones. El
 * mensaje pasa de unos 500 caracteres a menos de 200, y el paciente ve lo que
 * va a hacer antes de hacerlo.
 */
class RecordatorioConLigaCortaTest extends TestCase
{
    use RefreshDatabase;

    private Appointment $cita;

    protected function setUp(): void
    {
        parent::setUp();

        $clinica = Clinic::create([
            'name' => 'Consultorio Sonrisas',
            'slug' => 'consultorio-sonrisas',
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
            'clinic_id' => $clinica->id,
        ]);

        $doctor = Doctor::create([
            'user_id' => $usuario->id,
            'clinic_id' => $clinica->id,
            'specialty' => 'Odontología',
        ]);

        $paciente = Patient::create([
            'clinic_id' => $clinica->id,
            'first_name' => 'José Manuel',
            'last_name' => 'Ruiz',
            'phone' => '6681234567',
        ]);

        $this->cita = Appointment::create([
            'clinic_id' => $clinica->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $paciente->id,
            'starts_at' => now()->addDay()->setTime(9, 0),
            'ends_at' => now()->addDay()->setTime(9, 30),
            'status' => 'scheduled',
        ]);
    }

    // ── El mensaje ───────────────────────────────────────────────

    public function test_el_mensaje_lleva_una_sola_liga_y_es_corta(): void
    {
        $mensaje = RecordatorioDeCita::mensaje($this->cita);

        $this->assertSame(1, substr_count($mensaje, 'http'), 'Debe ir una sola liga, no una para confirmar y otra para cancelar.');
        $this->assertLessThan(320, strlen($mensaje), 'El mensaje sigue siendo un muro de texto.');
        $this->assertStringNotContainsString('signature=', $mensaje);
    }

    public function test_el_mensaje_dice_de_quien_es_y_a_que_hora(): void
    {
        $mensaje = RecordatorioDeCita::mensaje($this->cita);

        $this->assertStringContainsString('José Manuel', $mensaje);
        $this->assertStringContainsString('09:00', $mensaje);
        $this->assertStringContainsString('Consultorio Sonrisas', $mensaje);
    }

    public function test_la_liga_corta_lleva_a_la_pagina_de_la_cita(): void
    {
        RecordatorioDeCita::mensaje($this->cita);

        $corta = ShortUrl::firstOrFail();

        $this->get(route('shortlink', ['code' => $corta->code]))
            ->assertRedirect($corta->target_url);
    }

    // ── Abrir no es confirmar ────────────────────────────────────

    public function test_abrir_la_liga_no_confirma_la_cita_sola(): void
    {
        // Antes, entrar sin acción confirmaba: el paciente abría a ver de qué
        // se trataba y su cita quedaba confirmada sin que él decidiera.
        $this->get(RecordatorioDeCita::ligaDirecta($this->cita))->assertOk();

        $this->assertSame('scheduled', $this->cita->fresh()->status);
    }

    public function test_la_pagina_ensena_los_dos_botones(): void
    {
        $this->get(RecordatorioDeCita::ligaDirecta($this->cita))
            ->assertOk()
            ->assertSee('Confirmar', false)
            ->assertSee('Cancelar', false);
    }

    public function test_el_boton_de_confirmar_si_confirma(): void
    {
        $this->get(RecordatorioDeCita::ligaDirecta($this->cita, 'confirm'));

        $this->assertSame('confirmed', $this->cita->fresh()->status);
        $this->assertNotNull($this->cita->fresh()->confirmed_at);
    }

    public function test_el_boton_de_cancelar_si_cancela(): void
    {
        $this->get(RecordatorioDeCita::ligaDirecta($this->cita, 'cancel'));

        $this->assertSame('cancelled', $this->cita->fresh()->status);
    }

    public function test_sin_firma_no_se_puede_confirmar_una_cita_ajena(): void
    {
        $this->get(url("/c/{$this->cita->id}?action=confirm"))->assertForbidden();

        $this->assertSame('scheduled', $this->cita->fresh()->status);
    }

    // ── El choque de rutas ───────────────────────────────────────

    public function test_una_cita_con_id_largo_sigue_abriendo(): void
    {
        // /c/{codigo} de la liga corta y /c/{cita} competían por la misma
        // dirección: en cuanto las citas llegaran a seis dígitos, el
        // recordatorio iba a mandar al paciente a un 404.
        $this->cita->forceFill(['id' => 250525])->save();

        $this->get(RecordatorioDeCita::ligaDirecta($this->cita->fresh()))->assertOk();
    }
}
