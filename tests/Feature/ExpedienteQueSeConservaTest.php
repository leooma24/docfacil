<?php

namespace Tests\Feature;

use App\Exceptions\ExpedienteQueSeConserva;
use App\Filament\Doctor\Resources\PatientResource\Pages\EditPatient;
use App\Filament\Doctor\Resources\PatientResource\Pages\ListPatients;
use App\Models\Clinic;
use App\Models\ConsentForm;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El expediente no se borra junto con el paciente.
 *
 * Las llaves foráneas borran en cascada notas, recetas, consentimientos y
 * odontogramas. La NOM-004 (5.4) pide conservar el expediente al menos 5
 * años desde el último acto médico.
 */
class ExpedienteQueSeConservaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $usuario;

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

        $this->usuario = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->usuario->id,
            'clinic_id' => $this->clinica->id,
            'specialty' => 'Odontología',
        ]);

        $this->paciente = $this->paciente('Fernando', '5512345678');
    }

    private function paciente(string $nombre, string $telefono): Patient
    {
        return Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => $nombre,
            'last_name' => 'Morales',
            'phone' => $telefono,
        ]);
    }

    private function nota(Patient $paciente): MedicalRecord
    {
        return MedicalRecord::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $paciente->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => now()->toDateString(),
            'chief_complaint' => 'Dolor de muela',
            'diagnosis' => 'Caries',
        ]);
    }

    private function comoDoctor(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    public function test_un_paciente_sin_expediente_si_se_puede_borrar(): void
    {
        $this->paciente->delete();

        $this->assertModelMissing($this->paciente);
    }

    public function test_un_paciente_con_notas_no_se_borra(): void
    {
        $this->nota($this->paciente);

        $this->expectException(ExpedienteQueSeConserva::class);

        $this->paciente->delete();
    }

    public function test_despues_del_intento_el_expediente_sigue_completo(): void
    {
        $this->nota($this->paciente);

        try {
            $this->paciente->delete();
        } catch (ExpedienteQueSeConserva) {
        }

        $this->assertModelExists($this->paciente);
        $this->assertSame(1, MedicalRecord::withoutGlobalScopes()->count());
    }

    public function test_una_receta_tambien_es_expediente(): void
    {
        Prescription::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'prescription_date' => now()->toDateString(),
        ]);

        $this->expectException(ExpedienteQueSeConserva::class);

        $this->paciente->delete();
    }

    public function test_un_consentimiento_tambien_es_expediente(): void
    {
        ConsentForm::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'title' => 'Consentimiento Informado',
            'content' => '<p>Autorizo el procedimiento.</p>',
        ]);

        $this->expectException(ExpedienteQueSeConserva::class);

        $this->paciente->delete();
    }

    public function test_el_borrado_en_grupo_solo_borra_a_los_que_no_tienen_expediente(): void
    {
        $this->nota($this->paciente);
        $sinNada = $this->paciente('Lucía', '5587654321');

        $this->comoDoctor();

        Livewire::test(ListPatients::class)
            ->callTableBulkAction('delete', [$this->paciente, $sinNada]);

        $this->assertModelExists($this->paciente);
        $this->assertModelMissing($sinNada);
    }

    public function test_en_la_ficha_no_se_ofrece_borrar_si_tiene_expediente(): void
    {
        $this->nota($this->paciente);

        $this->comoDoctor();

        Livewire::test(EditPatient::class, ['record' => $this->paciente->getRouteKey()])
            ->assertActionHidden('delete')
            ->assertActionVisible('tiene_expediente');
    }

    public function test_en_la_ficha_si_se_ofrece_borrar_si_no_tiene_nada(): void
    {
        $this->comoDoctor();

        Livewire::test(EditPatient::class, ['record' => $this->paciente->getRouteKey()])
            ->assertActionVisible('delete');
    }

    public function test_un_consultorio_con_expedientes_no_se_borra(): void
    {
        $this->nota($this->paciente);

        $this->expectException(\LogicException::class);

        $this->clinica->delete();
    }
}
