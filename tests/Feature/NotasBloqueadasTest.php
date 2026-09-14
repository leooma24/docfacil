<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\MedicalRecordResource\Pages\EditMedicalRecord;
use App\Filament\Doctor\Resources\MedicalRecordResource\Pages\ListMedicalRecords;
use App\Filament\Doctor\Resources\PrescriptionResource\Pages\ListPrescriptions;
use App\Models\Clinic;
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
 * Lo bloqueado no se ofrece editar.
 *
 * Notas y recetas se bloquean a las 24 horas (NOM-004), pero el botón
 * "Editar" seguía en la tabla y al guardar reventaba con un error.
 */
class NotasBloqueadasTest extends TestCase
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

        $this->paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Fernando',
            'last_name' => 'Morales',
            'phone' => '5512345678',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function nota(): MedicalRecord
    {
        return MedicalRecord::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => now()->toDateString(),
            'chief_complaint' => 'Dolor de muela',
            'diagnosis' => 'Caries',
        ]);
    }

    private function receta(): Prescription
    {
        return Prescription::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'prescription_date' => now()->toDateString(),
        ]);
    }

    public function test_una_nota_de_hoy_si_se_ofrece_editar(): void
    {
        $nota = $this->nota();

        Livewire::test(ListMedicalRecords::class)->assertTableActionVisible('edit', $nota);
    }

    public function test_una_nota_de_hace_mas_de_24_horas_ya_no(): void
    {
        $this->travel(-25)->hours();
        $nota = $this->nota();
        $this->travelBack();

        Livewire::test(ListMedicalRecords::class)->assertTableActionHidden('edit', $nota);
    }

    public function test_entrar_por_la_liga_a_una_nota_bloqueada_regresa_a_la_lista(): void
    {
        $this->travel(-25)->hours();
        $nota = $this->nota();
        $this->travelBack();

        Livewire::test(EditMedicalRecord::class, ['record' => $nota->getRouteKey()])
            ->assertRedirect();
    }

    public function test_una_receta_bloqueada_tampoco_se_ofrece_editar(): void
    {
        $this->travel(-25)->hours();
        $receta = $this->receta();
        $this->travelBack();

        Livewire::test(ListPrescriptions::class)->assertTableActionHidden('edit', $receta);
    }
}
