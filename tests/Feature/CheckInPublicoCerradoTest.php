<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * El check-in de la sala de espera.
 *
 * Era la única puerta por la que alguien sin cuenta escribía en el expediente
 * de un paciente: bastaba con saber el nombre del consultorio (el slug sale de
 * ahí) y un teléfono. La pantalla contestaba "¡Hola de nuevo!" si ese teléfono
 * ya era paciente, así que también servía para averiguar quién se atiende en
 * qué consultorio, y dejaba registrada una aceptación del aviso de privacidad
 * a nombre de esa persona.
 *
 * Ahora la liga va firmada desde el QR que imprime el consultorio.
 */
class CheckInPublicoCerradoTest extends TestCase
{
    use RefreshDatabase;

    private function consultorio(string $plan = 'basico'): Clinic
    {
        return Clinic::create([
            'name' => 'Consultorio Sonrisas',
            'slug' => 'consultorio-sonrisas',
            'plan' => $plan,
            'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(),
            'is_active' => true,
            'onboarding_status' => 'completed',
        ]);
    }

    public function test_sin_la_firma_del_qr_no_se_entra(): void
    {
        $clinica = $this->consultorio();

        $this->get("/clinica/{$clinica->slug}/check-in")->assertForbidden();

        $this->post("/clinica/{$clinica->slug}/check-in", [
            'first_name' => 'Quien',
            'last_name' => 'Sea',
            'phone' => '6681234567',
            'acepta_aviso' => '1',
        ])->assertForbidden();

        $this->assertSame(0, Patient::withoutGlobalScopes()->count());
    }

    public function test_con_la_firma_del_qr_el_paciente_se_registra(): void
    {
        $clinica = $this->consultorio();

        $this->post(URL::signedRoute('checkin.store', ['slug' => $clinica->slug]), [
            'first_name' => 'Ana',
            'last_name' => 'Ruiz',
            'phone' => '6681234567',
            'acepta_aviso' => '1',
        ])->assertOk();

        $this->assertDatabaseHas('patients', [
            'clinic_id' => $clinica->id,
            'first_name' => 'Ana',
        ]);
    }

    public function test_no_le_escribe_en_las_notas_medicas_a_un_paciente_que_ya_existe(): void
    {
        $clinica = $this->consultorio();

        $paciente = Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinica->id,
            'first_name' => 'Luis',
            'last_name' => 'Mora',
            'phone' => '6689998877',
            'medical_notes' => 'Alergia a la penicilina',
        ]);

        $this->post(URL::signedRoute('checkin.store', ['slug' => $clinica->slug]), [
            'first_name' => 'Luis',
            'last_name' => 'Mora',
            'phone' => '6689998877',
            'reason_for_visit' => 'texto que no debe acabar en el expediente',
            'acepta_aviso' => '1',
        ])->assertOk();

        $this->assertSame('Alergia a la penicilina', $paciente->fresh()->medical_notes);
    }

    public function test_la_pantalla_no_dice_si_el_telefono_ya_era_paciente(): void
    {
        $clinica = $this->consultorio();

        Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinica->id,
            'first_name' => 'Luis',
            'last_name' => 'Mora',
            'phone' => '6689998877',
        ]);

        $conocido = $this->post(URL::signedRoute('checkin.store', ['slug' => $clinica->slug]), [
            'first_name' => 'Luis',
            'last_name' => 'Mora',
            'phone' => '6689998877',
            'acepta_aviso' => '1',
        ]);

        $nuevo = $this->post(URL::signedRoute('checkin.store', ['slug' => $clinica->slug]), [
            'first_name' => 'Otra',
            'last_name' => 'Persona',
            'phone' => '6681112233',
            'acepta_aviso' => '1',
        ]);

        $conocido->assertDontSee('Hola de nuevo');
        $nuevo->assertDontSee('Hola de nuevo');
        $conocido->assertSee('Check-in completado');
        $nuevo->assertSee('Check-in completado');
    }

    public function test_un_consultorio_sin_el_plan_no_tiene_check_in(): void
    {
        $clinica = $this->consultorio('free');

        $this->get(URL::signedRoute('checkin.show', ['slug' => $clinica->slug]))
            ->assertNotFound();
    }
}
