<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Livewire\OdontogramEditor;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El dentista ve la boca de frente: arriba 18…11 | 21…28 y abajo
 * 48…41 | 31…38, con el lado derecho del paciente a su izquierda. El
 * editor pintaba abajo 31…38 | 48…41 y el perfil 38…31 | 41…48: el 36
 * quedaba debajo del 16. Un dentista lo nota al primer vistazo.
 */
class OdontogramaEnOrdenTest extends TestCase
{
    use RefreshDatabase;

    private const EN_ORDEN = [
        'Diente 18 ', 'Diente 11 ', 'Diente 21 ', 'Diente 28 ',
        'Diente 48 ', 'Diente 41 ', 'Diente 31 ', 'Diente 38 ',
    ];

    private Odontogram $odontograma;
    private Patient $paciente;

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
        $doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $clinic->id, 'specialty' => 'Odontología']);
        $this->paciente = Patient::create(['clinic_id' => $clinic->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz']);
        $this->odontograma = Odontogram::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $doctor->id,
            'evaluation_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);
    }

    public function test_el_editor_pone_la_arcada_inferior_como_la_ve_el_dentista(): void
    {
        Livewire::test(OdontogramEditor::class, ['odontogramId' => $this->odontograma->id])
            ->assertSeeInOrder(self::EN_ORDEN);
    }

    public function test_el_perfil_del_paciente_pone_la_arcada_inferior_como_la_ve_el_dentista(): void
    {
        Livewire::withQueryParams(['patient' => $this->paciente->id])
            ->test(PatientProfile::class)
            ->call('setTab', 'odontogram')
            ->assertSeeInOrder(self::EN_ORDEN);
    }
}
