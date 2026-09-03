<?php

namespace Tests\Feature;

use App\Exceptions\LimiteDePacientesAlcanzado;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El tope de pacientes de cada plan.
 *
 * La landing lleva tiempo diciendo "15 pacientes" en Free y "200" en Básico,
 * pero nada lo impedía: un consultorio gratis podía capturar mil, y con el
 * importador podía subirlos de un jalón.
 *
 * El candado vive en el modelo Patient a propósito. Hay siete caminos que
 * crean pacientes y parcharlos uno por uno deja huecos — este es el único
 * lugar por el que pasan todos.
 */
class LimiteDePacientesTest extends TestCase
{
    use RefreshDatabase;

    private function clinica(string $plan = 'free', array $extra = []): Clinic
    {
        return Clinic::create(array_merge([
            'name' => 'Consultorio Test',
            'slug' => 'c-' . uniqid(),
            'plan' => $plan,
            'plan_ends_at' => $plan === 'free' ? null : now()->addMonth(),
            // Sin prueba vigente: durante la prueba no hay tope, a propósito.
            'trial_ends_at' => now()->subDay(),
            'onboarding_status' => 'completed',
        ], $extra));
    }

    private function llenar(Clinic $clinica, int $cuantos): void
    {
        for ($i = 0; $i < $cuantos; $i++) {
            Patient::withoutGlobalScopes()->create([
                'clinic_id' => $clinica->id,
                'first_name' => 'Paciente',
                'last_name' => (string) $i,
                'phone' => '55' . str_pad((string) $i, 8, '0', STR_PAD_LEFT),
            ]);
        }
    }

    // ── El tope de cada plan ─────────────────────────────────────

    public function test_free_llega_a_quince(): void
    {
        $this->assertSame(15, $this->clinica('free')->limitePacientes());
    }

    public function test_basico_llega_a_doscientos(): void
    {
        $this->assertSame(200, $this->clinica('basico')->limitePacientes());
    }

    public function test_pro_y_clinica_no_tienen_tope(): void
    {
        // Ojo: el valor es null y eso significa "sin tope", no "sin definir".
        $this->assertNull($this->clinica('profesional')->limitePacientes());
        $this->assertNull($this->clinica('clinica')->limitePacientes());
    }

    public function test_durante_la_prueba_de_15_dias_no_hay_tope(): void
    {
        // Ponerle tope a la prueba sería al revés de lo que queremos: que
        // alcance a meter sus pacientes y vea el sistema lleno antes de pagar.
        $nuevo = $this->clinica('free', ['trial_ends_at' => now()->addDays(15)]);

        $this->assertNull($nuevo->limitePacientes());
    }

    public function test_un_plan_de_pago_vencido_cae_al_tope_de_free(): void
    {
        $vencido = $this->clinica('profesional', ['plan_ends_at' => now()->subDay()]);

        $this->assertSame(15, $vencido->limitePacientes());
    }

    // ── La cuenta ────────────────────────────────────────────────

    public function test_dice_cuantos_le_quedan(): void
    {
        $clinica = $this->clinica('free');
        $this->llenar($clinica, 10);

        $this->assertSame(5, $clinica->pacientesRestantes());
        $this->assertTrue($clinica->puedeAgregarPacientes());
    }

    public function test_al_llegar_al_tope_ya_no_puede(): void
    {
        $clinica = $this->clinica('free');
        $this->llenar($clinica, 15);

        $this->assertSame(0, $clinica->pacientesRestantes());
        $this->assertFalse($clinica->puedeAgregarPacientes());
    }

    public function test_el_que_ya_venia_pasado_no_pierde_pacientes(): void
    {
        // Un consultorio que capturó 40 durante la prueba y no pagó conserva
        // los 40: son expedientes clínicos suyos. Nada más ya no agrega.
        $clinica = $this->clinica('free', ['trial_ends_at' => now()->addDay()]);
        $this->llenar($clinica, 40);

        $clinica->update(['trial_ends_at' => now()->subDay()]);
        $clinica->refresh();

        $this->assertSame(40, Patient::withoutGlobalScopes()->where('clinic_id', $clinica->id)->count());
        $this->assertSame(0, $clinica->pacientesRestantes(), 'No puede quedar en negativo.');
        $this->assertFalse($clinica->puedeAgregarPacientes());
    }

    // ── El candado del modelo ────────────────────────────────────

    public function test_el_paciente_dieciseis_no_se_crea(): void
    {
        $clinica = $this->clinica('free');
        $this->llenar($clinica, 15);

        $this->expectException(LimiteDePacientesAlcanzado::class);

        Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinica->id,
            'first_name' => 'Uno',
            'last_name' => 'DeMás',
            'phone' => '5599887766',
        ]);
    }

    public function test_el_mensaje_dice_el_plan_y_el_numero(): void
    {
        $clinica = $this->clinica('free');
        $mensaje = $clinica->mensajeDeTopeDePacientes();

        $this->assertStringContainsString('15', $mensaje);
        $this->assertStringContainsString('Free', $mensaje);
        // Que no crea que va a perder los que ya tiene.
        $this->assertStringContainsString('se quedan', $mensaje);
    }

    public function test_editar_un_paciente_existente_sigue_funcionando(): void
    {
        // El tope es para agregar, no para trabajar con los que ya tiene.
        $clinica = $this->clinica('free');
        $this->llenar($clinica, 15);

        $paciente = Patient::withoutGlobalScopes()->where('clinic_id', $clinica->id)->first();
        $paciente->update(['first_name' => 'Corregido']);

        $this->assertSame('Corregido', $paciente->fresh()->first_name);
    }

    public function test_un_consultorio_sin_tope_agrega_sin_problema(): void
    {
        $clinica = $this->clinica('profesional');
        $this->llenar($clinica, 30);

        $this->assertNull($clinica->pacientesRestantes());
        $this->assertTrue($clinica->puedeAgregarPacientes());
    }

    public function test_el_tope_de_un_consultorio_no_afecta_a_otro(): void
    {
        $lleno = $this->clinica('free');
        $this->llenar($lleno, 15);

        $otro = $this->clinica('free');

        $this->assertTrue($otro->puedeAgregarPacientes());
    }

    // ── Los caminos donde el que se topa es el paciente ──────────

    public function test_el_check_in_con_qr_no_truena_le_manda_con_recepcion(): void
    {
        $clinica = $this->clinica('basico');
        $this->llenar($clinica, 200);

        $this->post("/clinica/{$clinica->slug}/check-in", [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5599887766',
        ])->assertSessionHasErrors('first_name');

        $this->assertSame(200, Patient::withoutGlobalScopes()->where('clinic_id', $clinica->id)->count());
    }

    public function test_un_consultorio_topado_no_registra_pacientes_por_la_agenda_publica(): void
    {
        // Matiz: la agenda pública es de Pro para arriba, y Pro no tiene tope,
        // así que en la práctica esta puerta no se topa. Y si el plan vence,
        // la página se apaga antes por falta de feature. El guardia del
        // controlador está de todos modos, por si mañana public_booking baja
        // a Básico, que sí tiene tope de 200.
        //
        // Lo que esta prueba fija es el resultado que importa: por ahí no
        // entra un paciente de más, gane el guardia que gane.
        $clinica = $this->clinica('profesional');

        $usuario = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doc@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinica->id,
        ]);
        Doctor::create(['user_id' => $usuario->id, 'clinic_id' => $clinica->id, 'specialty' => 'Odontología']);

        $this->llenar($clinica, 15);
        $clinica->update(['plan' => 'free', 'plan_ends_at' => null]);

        $this->post("/clinica/{$clinica->slug}/agendar", [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5599887766',
            'preferred_at' => now()->addWeek()->setTime(11, 0)->toDateTimeString(),
        ]);

        $this->assertSame(
            15,
            Patient::withoutGlobalScopes()->where('clinic_id', $clinica->id)->count(),
            'No debe entrar un paciente de más por la agenda pública.'
        );
    }

    public function test_el_guardia_de_la_agenda_publica_reconoce_el_tope(): void
    {
        $clinica = $this->clinica('basico');
        $this->llenar($clinica, 200);

        $this->assertFalse($clinica->puedeAgregarPacientes());
    }
}
