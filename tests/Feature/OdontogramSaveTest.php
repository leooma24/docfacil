<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\OdontogramResource\Pages\EditOdontogram;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El odontograma es el diferenciador del producto para dentistas, así que
 * guardar tiene que funcionar sin sobresaltos:
 *
 *  - Antes reventaba con BadMethodCallException DESPUÉS de escribir en la
 *    base, porque llamaba notify(), la API de Filament 2.
 *  - Y al regresar un diente a "sano" no se borraba la marca vieja, así que
 *    un diente marcado por error se quedaba pintado para siempre.
 */
class OdontogramSaveTest extends TestCase
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

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    /**
     * Ejercita el guardado real de la pagina. No pasamos por callAction()
     * porque EditOdontogram usa una vista propia y el harness de Livewire
     * no la puede montar; guardarDientes() es la misma logica que corre el
     * boton "Guardar Odontograma".
     */
    private function guardar(array $dientes): void
    {
        $pagina = new EditOdontogram();
        $pagina->record = $this->odontograma;
        $pagina->teethData = $dientes;
        $pagina->guardarDientes();
    }

    public function test_guardar_marca_los_dientes_sin_reventar(): void
    {
        $this->guardar([
            23 => ['condition' => 'decay', 'notes' => null],
            24 => ['condition' => 'filling', 'notes' => 'Resina oclusal'],
        ]);

        $this->assertDatabaseHas('odontogram_teeth', [
            'odontogram_id' => $this->odontograma->id,
            'tooth_number' => 23,
            'condition' => 'decay',
        ]);
        $this->assertDatabaseHas('odontogram_teeth', [
            'odontogram_id' => $this->odontograma->id,
            'tooth_number' => 24,
            'condition' => 'filling',
            'notes' => 'Resina oclusal',
        ]);
    }

    public function test_regresar_un_diente_a_sano_borra_la_marca(): void
    {
        // El doctor marca el 23 por error...
        $this->guardar([23 => ['condition' => 'decay', 'notes' => null]]);
        $this->assertDatabaseHas('odontogram_teeth', [
            'odontogram_id' => $this->odontograma->id,
            'tooth_number' => 23,
        ]);

        // ...y lo corrige a sano.
        $this->guardar([23 => ['condition' => 'healthy', 'notes' => null]]);

        $this->assertDatabaseMissing('odontogram_teeth', [
            'odontogram_id' => $this->odontograma->id,
            'tooth_number' => 23,
        ]);
    }

    public function test_un_diente_sano_con_nota_si_se_conserva(): void
    {
        // Sano pero con observación: la nota es información clínica, se guarda.
        $this->guardar([25 => ['condition' => 'healthy', 'notes' => 'Vigilar desgaste']]);

        $this->assertDatabaseHas('odontogram_teeth', [
            'odontogram_id' => $this->odontograma->id,
            'tooth_number' => 25,
            'notes' => 'Vigilar desgaste',
        ]);
    }

    public function test_no_toca_los_dientes_de_otro_odontograma(): void
    {
        $otro = Odontogram::create([
            'clinic_id' => $this->odontograma->clinic_id,
            'patient_id' => $this->odontograma->patient_id,
            'doctor_id' => $this->odontograma->doctor_id,
            'evaluation_date' => now()->subYear()->toDateString(),
        ]);
        OdontogramTooth::create([
            'odontogram_id' => $otro->id,
            'tooth_number' => 23,
            'condition' => 'decay',
        ]);

        $this->guardar([23 => ['condition' => 'healthy', 'notes' => null]]);

        $this->assertDatabaseHas('odontogram_teeth', [
            'odontogram_id' => $otro->id,
            'tooth_number' => 23,
            'condition' => 'decay',
        ]);
    }
}
