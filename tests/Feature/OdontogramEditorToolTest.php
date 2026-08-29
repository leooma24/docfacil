<?php

namespace Tests\Feature;

use App\Livewire\OdontogramEditor;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La barra de herramientas mostraba "Sano" igual que Caries u Obturación,
 * pero al tocar un diente con esa herramienta no pasaba nada: 'healthy' era
 * también el modo por defecto y applyTool() lo trataba como inspección. Un
 * dentista que marcaba un diente por error no tenía cómo corregirlo.
 */
class OdontogramEditorToolTest extends TestCase
{
    use RefreshDatabase;

    private Odontogram $odontograma;

    protected function setUp(): void
    {
        parent::setUp();

        $clinic = Clinic::create(['name' => 'Consultorio Test', 'onboarding_status' => 'completed']);
        $user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $clinic->id,
        ]);
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'specialty' => 'Odontología',
        ]);
        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Ana',
            'last_name' => 'Ruiz',
        ]);

        $this->odontograma = Odontogram::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'evaluation_date' => now()->toDateString(),
        ]);

        OdontogramTooth::create([
            'odontogram_id' => $this->odontograma->id,
            'tooth_number' => 23,
            'condition' => 'decay',
        ]);

        $this->actingAs($user);
    }

    private function editor()
    {
        return Livewire::test(OdontogramEditor::class, ['odontogramId' => $this->odontograma->id]);
    }

    public function test_al_entrar_tocar_un_diente_no_lo_cambia(): void
    {
        $this->editor()
            ->assertSet('activeTool', OdontogramEditor::INSPECCIONAR)
            ->call('applyTool', 23)
            ->assertSet('teeth.23.condition', 'decay')      // sigue con caries
            ->assertSet('selectedTooth', 23);               // pero ya está abierto
    }

    public function test_la_herramienta_sano_despinta_el_diente(): void
    {
        $this->editor()
            ->call('setTool', 'healthy')
            ->call('applyTool', 23)
            ->assertSet('teeth.23.condition', 'healthy');
    }

    public function test_las_demas_herramientas_siguen_marcando(): void
    {
        $this->editor()
            ->call('setTool', 'crown')
            ->call('applyTool', 25)
            ->assertSet('teeth.25.condition', 'crown');
    }

    public function test_volver_a_ver_deja_de_modificar(): void
    {
        $this->editor()
            ->call('setTool', 'decay')
            ->call('applyTool', 25)
            ->assertSet('teeth.25.condition', 'decay')
            ->call('setTool', OdontogramEditor::INSPECCIONAR)
            ->call('applyTool', 26)
            ->assertSet('teeth.26.condition', 'healthy');   // no lo marcó
    }
}
