<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Doctor\Resources\PatientResource\Pages\EditPatient;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Support\AlertasClinicas;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lo importante del paciente, a la vista y sin cuestionario.
 *
 * En la entrevista del 10-oct-2026 el dentista contó dos sustos: un hipertenso
 * que había dejado su medicina y se descompensó con la anestesia con
 * epinefrina, y una receta de amoxicilina casi para una alérgica a la
 * penicilina. La historia clínica se llena una vez y queda en el archivero, y
 * "la señora que hace tres años no tomaba nada hoy toma clopidogrel".
 *
 * Lo que pidió: que al abrir al paciente salga en rojo lo importante, que
 * se marque con casillas (no 50 preguntas) y que de vez en cuando le pregunte
 * si sigue igual. Antes solo se leía del texto libre de las notas y solo se
 * veía dentro de la consulta.
 */
class AlertasClinicasEnRojoTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function paciente(array $datos = []): Patient
    {
        return Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'Valenzuela', 'phone' => '6681234567'] + $datos);
    }

    private function consultaDe(Patient $paciente)
    {
        $s = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Consulta general', 'price' => 300, 'duration_minutes' => 30, 'is_active' => true]);

        return Livewire::test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $paciente->id)
            ->set('data.walkin_service_id', (string) $s->id)
            ->call('startWalkIn');
    }

    // ── Las casillas y las notas dicen lo mismo ─────────────────

    public function test_junta_las_casillas_con_lo_que_dicen_las_notas(): void
    {
        $p = $this->paciente(['riesgos' => ['anticoagulado'], 'medical_notes' => 'Diabética tipo 2']);

        $a = AlertasClinicas::delPaciente($p);

        $this->assertEqualsCanonicalizing(['Anticoagulantes', 'Diabetes'], $a['riesgos']);
    }

    public function test_una_casilla_sin_notas_tambien_avisa_al_recetar(): void
    {
        $p = $this->paciente(['riesgos' => ['anticoagulado']]);

        $aviso = AlertasClinicas::alRecetar('Ibuprofeno 400 mg', $p->allergies, $p->notasParaAlertas());

        $this->assertSame('sangrado', $aviso['tipo']);
    }

    public function test_el_embarazo_marcado_avisa_con_la_doxiciclina(): void
    {
        $p = $this->paciente(['riesgos' => ['embarazo']]);

        $this->assertSame('embarazo', AlertasClinicas::alRecetar('Doxiciclina 100 mg', null, $p->notasParaAlertas())['tipo']);
    }

    // ── Se marcan con casillas ──────────────────────────────────

    public function test_el_formulario_del_paciente_guarda_las_casillas(): void
    {
        $p = $this->paciente();

        Livewire::test(EditPatient::class, ['record' => $p->id])
            ->fillForm(['riesgos' => ['diabetes', 'anticoagulado']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEqualsCanonicalizing(['diabetes', 'anticoagulado'], $p->fresh()->riesgos);
        $this->assertNotNull($p->fresh()->riesgos_revisados_at, 'Cambiar algo cuenta como revisarlo.');
    }

    // ── Se ven al abrir al paciente ─────────────────────────────

    public function test_el_perfil_las_muestra_en_rojo(): void
    {
        $p = $this->paciente(['allergies' => 'Penicilina', 'riesgos' => ['hipertension', 'anticoagulado']]);

        Livewire::withQueryParams(['patient' => $p->id])->test(PatientProfile::class)
            ->assertSee('Penicilina')
            ->assertSee('Hipertensión')
            ->assertSee('Anticoagulantes');
    }

    public function test_la_consulta_las_muestra_desde_el_primer_paso(): void
    {
        $p = $this->paciente(['riesgos' => ['diabetes']]);

        $this->consultaDe($p)->assertSee('Diabetes');
    }

    public function test_la_lista_de_citas_marca_al_paciente_con_alertas(): void
    {
        $p = $this->paciente(['allergies' => 'Penicilina', 'riesgos' => ['embarazo']]);
        $limpio = $this->paciente(['first_name' => 'Beto', 'allergies' => 'Ninguna conocida']);
        foreach ([$p, $limpio] as $i => $pac) {
            Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $pac->id,
                'starts_at' => now()->addDay()->setTime(9 + $i, 0), 'ends_at' => now()->addDay()->setTime(9 + $i, 30), 'status' => 'scheduled']);
        }

        Livewire::test(ListAppointments::class)
            ->assertSee('Alergia: Penicilina')
            ->assertSee('Embarazo');
    }

    // ── ¿Sigue igual? ──────────────────────────────────────────

    public function test_pregunta_si_sigue_igual_cuando_lo_ultimo_es_de_hace_mas_de_seis_meses(): void
    {
        $p = $this->paciente(['allergies' => 'Penicilina', 'riesgos' => ['hipertension']]);
        $p->forceFill(['riesgos_revisados_at' => now()->subMonths(8)])->saveQuietly();

        $this->consultaDe($p)->assertSee('¿Sigue igual?');
    }

    public function test_no_pregunta_si_se_revisó_hace_poco(): void
    {
        $p = $this->paciente(['allergies' => 'Penicilina', 'riesgos' => ['hipertension']]);
        $p->forceFill(['riesgos_revisados_at' => now()->subMonth()])->saveQuietly();

        $this->consultaDe($p)->assertDontSee('¿Sigue igual?');
    }

    public function test_el_boton_deja_anotado_que_se_reviso(): void
    {
        $p = $this->paciente(['riesgos' => ['hipertension']]);
        $p->forceFill(['riesgos_revisados_at' => now()->subYear()])->saveQuietly();

        $this->consultaDe($p)->call('sigueIgual')->assertDontSee('¿Sigue igual?');

        $this->assertTrue($p->fresh()->riesgos_revisados_at->isSameMinute(now()));
    }

    public function test_se_marca_o_se_quita_una_casilla_desde_la_consulta(): void
    {
        $p = $this->paciente();

        $this->consultaDe($p)->call('alternarRiesgo', 'anticoagulado')->assertSee('Anticoagulantes');
        $this->assertSame(['anticoagulado'], $p->fresh()->riesgos);

        $this->consultaDe($p->fresh())->call('alternarRiesgo', 'anticoagulado');
        $this->assertSame([], $p->fresh()->riesgos);
    }

    public function test_una_clave_que_no_existe_no_se_guarda(): void
    {
        $p = $this->paciente();

        $this->consultaDe($p)->call('alternarRiesgo', 'cualquier-cosa');

        $this->assertEmpty($p->fresh()->riesgos);
    }
}
