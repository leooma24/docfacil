<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceSupply;
use App\Models\Supply;
use App\Models\User;
use App\Support\AnesthesiaDose;
use App\Support\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La seguridad de la anestesia: la alergia del paciente y la dosis por peso.
 *
 * Lo que se prueba con más cuidado es lo que NO se afirma. Una app clínica que
 * dice "está bien" sin tener con qué saberlo es más peligrosa que una que se
 * calla, así que sin configuración y sin peso el sistema no compara nada — y
 * eso es distinto de decir que no se pasa.
 */
class SeguridadDeLaAnestesiaTest extends TestCase
{
    use RefreshDatabase;

    private function clinica(array $atributos = []): Clinic
    {
        return Clinic::create(array_merge([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-' . uniqid(),
            'onboarding_status' => 'completed',
        ], $atributos));
    }

    // ── El cálculo de la dosis ───────────────────────────────────

    public function test_sin_configuracion_no_compara_nada(): void
    {
        $clinica = $this->clinica();

        $this->assertFalse(AnesthesiaDose::isConfigured($clinica));
        $this->assertNull(AnesthesiaDose::maxCartridges($clinica, 70));

        $estado = AnesthesiaDose::status($clinica, 70, 5);

        // null y no false: "no se pasa" y "no hay con qué compararlo" no pueden
        // verse igual.
        $this->assertNull($estado['exceeds']);
        $this->assertStringContainsString('Sin límite configurado', $estado['message']);
    }

    public function test_sin_peso_tampoco_compara(): void
    {
        $clinica = $this->clinica(['anesthetic_max_mg_kg' => 7, 'anesthetic_mg_ml' => 20]);

        $this->assertNull(AnesthesiaDose::maxCartridges($clinica, null));
        $this->assertNull(AnesthesiaDose::maxCartridges($clinica, 0));

        $estado = AnesthesiaDose::status($clinica, null, 3);

        $this->assertNull($estado['exceeds']);
        $this->assertStringContainsString('Falta el peso', $estado['message']);
    }

    public function test_los_miligramos_por_cartucho_salen_de_la_concentracion(): void
    {
        // Lidocaína al 2% = 20 mg/ml, en cartuchos de 1.8 ml → 36 mg.
        $clinica = $this->clinica(['anesthetic_mg_ml' => 20, 'anesthetic_ml_per_cartridge' => 1.8]);

        $this->assertSame(36.0, AnesthesiaDose::mgPerCartridge($clinica));
    }

    public function test_una_concentracion_sin_capturar_no_deja_calcular(): void
    {
        $clinica = $this->clinica(['anesthetic_max_mg_kg' => 7]);

        $this->assertNull(AnesthesiaDose::mgPerCartridge($clinica));
        $this->assertFalse(AnesthesiaDose::isConfigured($clinica));
    }

    public function test_calcula_el_maximo_para_el_peso(): void
    {
        $clinica = $this->clinica([
            'anesthetic_max_mg_kg' => 7,
            'anesthetic_mg_ml' => 20,
            'anesthetic_ml_per_cartridge' => 1.8,
        ]);

        // 7 mg/kg × 70 kg = 490 mg; 490 / 36 = 13.6 cartuchos.
        $this->assertSame(13.6, AnesthesiaDose::maxCartridges($clinica, 70));

        // Un niño de 20 kg: 140 mg → 3.9 cartuchos.
        $this->assertSame(3.9, AnesthesiaDose::maxCartridges($clinica, 20));
    }

    public function test_avisa_cuando_se_pasa_del_maximo(): void
    {
        $clinica = $this->clinica([
            'anesthetic_max_mg_kg' => 7,
            'anesthetic_mg_ml' => 20,
            'anesthetic_ml_per_cartridge' => 1.8,
        ]);

        $estado = AnesthesiaDose::status($clinica, 20, 5);

        $this->assertTrue($estado['exceeds']);
        $this->assertStringContainsString('Se pasa del máximo', $estado['message']);
    }

    public function test_no_avisa_cuando_esta_dentro(): void
    {
        $clinica = $this->clinica([
            'anesthetic_max_mg_kg' => 7,
            'anesthetic_mg_ml' => 20,
            'anesthetic_ml_per_cartridge' => 1.8,
        ]);

        $estado = AnesthesiaDose::status($clinica, 70, 2);

        $this->assertFalse($estado['exceeds']);
        $this->assertStringContainsString('Dentro del máximo', $estado['message']);
    }

    // ── En la consulta ───────────────────────────────────────────

    private function consultaConAnestesia(?string $alergias, array $clinicaAtributos = []): \Livewire\Features\SupportTesting\Testable
    {
        $clinica = $this->clinica($clinicaAtributos);

        $user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doc' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $clinica->id,
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $clinica->id,
            'specialty' => 'General',
        ]);

        $paciente = Patient::create([
            'clinic_id' => $clinica->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'phone' => '5551234567',
            'allergies' => $alergias,
        ]);

        $consulta = Service::create([
            'clinic_id' => $clinica->id,
            'name' => 'Consulta general',
            'price' => 500,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $resina = Service::create([
            'clinic_id' => $clinica->id,
            'name' => 'Resina',
            'price' => 600,
            'unit' => WorkUnit::TOOTH,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        $anestesia = Supply::create([
            'clinic_id' => $clinica->id,
            'name' => 'Anestésico lidocaína 2%',
            'category' => 'Anestesia',
            'unit' => 'cartucho',
        ]);

        ServiceSupply::create([
            'clinic_id' => $clinica->id,
            'service_id' => $resina->id,
            'supply_id' => $anestesia->id,
            'quantity' => 1,
            'scope' => WorkUnit::CONTIGUOUS_ZONE,
        ]);

        return Livewire::actingAs($user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $paciente->id)
            ->set('data.walkin_service_id', (string) $consulta->id)
            ->call('startWalkIn')
            ->set('procedures', [
                ['service_id' => (string) $resina->id, 'tooth_number' => '16', 'quantity' => 1],
            ]);
    }

    public function test_la_alerta_de_alergia_sale_cuando_hay_anestesia(): void
    {
        $prueba = $this->consultaConAnestesia('Penicilina y lidocaína');

        $this->assertNotNull($prueba->instance()->allergyAlert);
        $this->assertStringContainsString('Revísalas antes de aplicar', $prueba->instance()->allergyAlert);
        $this->assertSame('Penicilina y lidocaína', $prueba->instance()->patientAllergies);
    }

    public function test_sin_alergias_no_hay_alerta(): void
    {
        $prueba = $this->consultaConAnestesia(null);

        $this->assertNull($prueba->instance()->allergyAlert);
    }

    public function test_sin_anestesico_en_la_propuesta_no_hay_alerta_de_alergia(): void
    {
        // El paciente tiene alergias, pero no se le va a poner anestésico: la
        // alerta no tiene nada que juntar.
        $prueba = $this->consultaConAnestesia('Penicilina')
            ->set('supplies.0.include', false);

        $this->assertSame(0.0, $prueba->instance()->proposedAnesthesia);
        $this->assertNull($prueba->instance()->allergyAlert);
    }

    public function test_la_consulta_cuenta_los_cartuchos_de_la_propuesta(): void
    {
        $prueba = $this->consultaConAnestesia(null);

        // Un diente arriba: una zona contigua, un cartucho.
        $this->assertSame(1.0, $prueba->instance()->proposedAnesthesia);
    }

    public function test_sin_limite_configurado_la_consulta_lo_dice(): void
    {
        $prueba = $this->consultaConAnestesia(null)->set('weight', '70');

        $estado = $prueba->instance()->doseStatus;

        $this->assertNotNull($estado);
        $this->assertFalse($estado['configured']);
        $this->assertNull($estado['exceeds']);
    }

    public function test_con_limite_configurado_la_consulta_avisa(): void
    {
        $prueba = $this->consultaConAnestesia(null, [
            'anesthetic_max_mg_kg' => 7,
            'anesthetic_mg_ml' => 20,
            'anesthetic_ml_per_cartridge' => 1.8,
        ])->set('weight', '20');

        $estado = $prueba->instance()->doseStatus;

        $this->assertTrue($estado['configured']);
        $this->assertSame(20.0, $estado['weight']);
        $this->assertFalse($estado['exceeds']);
        $this->assertSame(3.9, $estado['maximum']);
    }
}
