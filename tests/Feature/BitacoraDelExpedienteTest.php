<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Models\Clinic;
use App\Models\ConsentForm;
use App\Models\Doctor;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * NOM-024-SSA3-2012 (3.42, 5.8 y 6.6.1): trazabilidad, quién vio o cambió
 * el expediente y cuándo. Antes la bitácora solo cubría notas, pacientes y
 * citas, y nadie quedaba registrado al abrir un expediente. Ahora se anota
 * quién lo abre y quién crea o cambia recetas, consentimientos y
 * odontogramas, y el doctor lo ve en la pestaña "Bitácora" del paciente.
 */
class BitacoraDelExpedienteTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;
    private Patient $rosa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->rosa = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'García']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    public function test_abrir_el_expediente_queda_anotado_una_vez(): void
    {
        Livewire::withQueryParams(['patient' => $this->rosa->id])->test(PatientProfile::class)
            ->call('setTab', 'history'); // moverse dentro del perfil no cuenta otra vez

        $vistas = Activity::where('subject_type', Patient::class)->where('subject_id', $this->rosa->id)->where('event', 'viewed')->get();
        $this->assertCount(1, $vistas);
        $this->assertSame($this->usuario->id, $vistas->first()->causer_id);
    }

    public function test_recetas_consentimientos_y_odontogramas_dejan_rastro(): void
    {
        Prescription::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'doctor_id' => $this->doctor->id,
            'prescription_date' => today(), 'diagnosis' => 'Pulpitis']);
        ConsentForm::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Extracción', 'content' => 'Texto', 'status' => 'pending']);
        Odontogram::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'doctor_id' => $this->doctor->id, 'evaluation_date' => today()]);

        foreach ([Prescription::class, ConsentForm::class, Odontogram::class] as $modelo) {
            $this->assertTrue(Activity::where('subject_type', $modelo)->where('event', 'created')->exists(), class_basename($modelo) . ' no dejó rastro');
        }
    }

    public function test_la_pestana_bitacora_dice_quien_y_que(): void
    {
        Prescription::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'doctor_id' => $this->doctor->id,
            'prescription_date' => today(), 'diagnosis' => 'Pulpitis']);

        Livewire::withQueryParams(['patient' => $this->rosa->id])->test(PatientProfile::class)
            ->assertSee('Bitácora')
            ->call('setTab', 'bitacora')
            ->assertSee('Dr. Javier')
            ->assertSee('Abrió el expediente')
            ->assertSee('Creó una receta');
    }

    public function test_no_mezcla_la_bitacora_de_otro_paciente(): void
    {
        $beto = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Beto', 'last_name' => 'Ruiz']);
        Prescription::create(['clinic_id' => $this->clinica->id, 'patient_id' => $beto->id, 'doctor_id' => $this->doctor->id,
            'prescription_date' => today(), 'diagnosis' => 'Otra cosa']);

        $this->assertTrue($this->rosa->bitacora()->every(fn ($a) => ! ($a->subject_type === Prescription::class)));
    }

    public function test_la_asistente_no_ve_la_bitacora(): void
    {
        $lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->actingAs($lupita);

        Livewire::withQueryParams(['patient' => $this->rosa->id])->test(PatientProfile::class)->assertDontSee('Bitácora');
    }
}
