<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Support\AlertasClinicas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Las alergias y antecedentes del paciente estaban guardados, pero la receta
 * no los revisaba: se podía recetar amoxicilina a un alérgico a la penicilina
 * sin un solo aviso. La alerta de anticoagulantes buscaba un campo que no
 * existe, así que nunca salía.
 *
 * Son avisos para revisar, no un dictamen: el doctor decide.
 */
class AlertasAlRecetarTest extends TestCase
{
    use RefreshDatabase;

    // ── Lo que se revisa ────────────────────────────────────────

    public function test_la_alergia_a_la_penicilina_avisa_con_la_amoxicilina(): void
    {
        $aviso = AlertasClinicas::alRecetar('Amoxicilina 500 mg', 'Penicilina', null);

        $this->assertNotNull($aviso);
        $this->assertStringContainsString('penicilina', mb_strtolower($aviso['texto']));
        $this->assertSame('alergia', $aviso['tipo']);
    }

    public function test_la_cefalosporina_es_posible_reaccion_cruzada(): void
    {
        $aviso = AlertasClinicas::alRecetar('Cefalexina', 'penicilina', null);

        $this->assertSame('cruzada', $aviso['tipo']);
    }

    public function test_alergia_a_la_aspirina_avisa_con_otros_antiinflamatorios(): void
    {
        $this->assertSame('alergia', AlertasClinicas::alRecetar('Ibuprofeno 400 mg', 'Aspirina', null)['tipo']);
        $this->assertSame('alergia', AlertasClinicas::alRecetar('Ketorolaco', 'AINES', null)['tipo']);
    }

    public function test_la_alergia_escrita_igual_que_el_medicamento_avisa(): void
    {
        $this->assertSame('alergia', AlertasClinicas::alRecetar('Clindamicina 300 mg', 'Clindamicina, mariscos', null)['tipo']);
    }

    public function test_sin_relacion_no_avisa(): void
    {
        $this->assertNull(AlertasClinicas::alRecetar('Paracetamol 500 mg', 'Penicilina', null));
        $this->assertNull(AlertasClinicas::alRecetar('Amoxicilina', null, null));
        $this->assertNull(AlertasClinicas::alRecetar('', 'Penicilina', null));
    }

    public function test_anticoagulante_en_las_notas_avisa_con_el_ibuprofeno(): void
    {
        $aviso = AlertasClinicas::alRecetar('Ibuprofeno 400 mg', null, 'Toma warfarina 5 mg diario');

        $this->assertSame('sangrado', $aviso['tipo']);
    }

    public function test_embarazo_en_las_notas_avisa_con_la_doxiciclina(): void
    {
        $this->assertSame('embarazo', AlertasClinicas::alRecetar('Doxiciclina 100 mg', null, 'Embarazada, 20 semanas')['tipo']);
    }

    public function test_lee_los_antecedentes_de_las_notas(): void
    {
        $this->assertSame(
            ['Diabetes', 'Hipertensión', 'Anticoagulantes'],
            AlertasClinicas::antecedentes('Diabético tipo 2. Hipertensa controlada. Toma acenocumarol.')
        );
        $this->assertSame([], AlertasClinicas::antecedentes(null));
    }

    // ── En la consulta ──────────────────────────────────────────

    private function consultaDe(array $paciente)
    {
        $clinica = Clinic::create(['name' => 'Consultorio Test', 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $clinica->id]);
        Doctor::create(['user_id' => $user->id, 'clinic_id' => $clinica->id, 'specialty' => 'Odontología']);
        $p = Patient::create(['clinic_id' => $clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz'] + $paciente);
        $s = Service::create(['clinic_id' => $clinica->id, 'name' => 'Consulta general', 'price' => 300, 'duration_minutes' => 30, 'is_active' => true]);
        $this->actingAs($user);

        return [Livewire::test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $p->id)
            ->set('data.walkin_service_id', (string) $s->id)
            ->call('startWalkIn'), $p];
    }

    public function test_la_receta_avisa_en_cuanto_se_escribe_el_medicamento(): void
    {
        [$consulta] = $this->consultaDe(['allergies' => 'Penicilina']);

        $consulta->set('currentStep', 3)
            ->set('medications', [['medication' => 'Amoxicilina', 'presentacion' => '', 'dosage' => '', 'via_administracion' => '', 'frequency' => '', 'duration' => '', 'instructions' => '']])
            ->assertSee('Alergia registrada');
    }

    public function test_el_aviso_de_anticoagulantes_si_sale(): void
    {
        [$consulta] = $this->consultaDe(['medical_notes' => 'Toma rivaroxabán']);

        $consulta->assertSee('Toma anticoagulantes');
    }

    public function test_sin_alergias_registradas_pide_preguntar_y_se_anotan_ahi_mismo(): void
    {
        [$consulta, $paciente] = $this->consultaDe([]);

        $consulta->assertSee('Alergias no registradas')
            ->set('alergiasNuevas', 'Penicilina')
            ->call('guardarAlergias')
            ->assertSee('Penicilina');

        $this->assertSame('Penicilina', $paciente->fresh()->allergies);
    }

    public function test_la_receta_hecha_fuera_de_la_consulta_tambien_avisa(): void
    {
        [, $paciente] = $this->consultaDe(['allergies' => 'Penicilina']);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('doctor'));

        Livewire::test(\App\Filament\Doctor\Resources\PrescriptionResource\Pages\CreatePrescription::class)
            ->fillForm(['patient_id' => $paciente->id, 'items' => [['medication' => 'Amoxicilina 500 mg']]])
            ->assertSee('Alergia registrada');
    }
}
