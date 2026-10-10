<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Resources\MedicalRecordResource\Pages\ListMedicalRecords;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * NOM-024-SSA3-2012 (6.3.4 y 5.9): los registros son inalterables. Una nota
 * de consulta se bloquea a las 24 horas; si hay que corregirla, se agrega una
 * corrección (addendum) ligada a la original, sin tocarla. Antes la pantalla
 * solo decía "registra una nota nueva", sin liga entre las dos.
 */
class CorreccionDeNotaBloqueadaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $rosa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->rosa = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'García']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function nota(string $cuando): MedicalRecord
    {
        $this->travelTo(\Carbon\Carbon::parse($cuando));
        $nota = MedicalRecord::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'doctor_id' => $this->doctor->id,
            'visit_date' => substr($cuando, 0, 10), 'diagnosis' => 'Caries en 36', 'treatment' => 'Resina en 36']);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));

        return $nota;
    }

    public function test_solo_la_nota_bloqueada_ofrece_corregir(): void
    {
        $vieja = $this->nota('2026-10-10 12:00');
        $deHoy = $this->nota('2026-10-14 09:00');

        Livewire::test(ListMedicalRecords::class)
            ->assertTableActionVisible('corregir', $vieja)
            ->assertTableActionHidden('corregir', $deHoy);
    }

    public function test_la_correccion_queda_ligada_y_la_original_no_cambia(): void
    {
        $vieja = $this->nota('2026-10-10 12:00');

        Livewire::test(ListMedicalRecords::class)
            ->callTableAction('corregir', $vieja, ['correccion' => 'El diente era el 46, no el 36.'])
            ->assertHasNoTableActionErrors();

        $correccion = MedicalRecord::where('corrige_a_id', $vieja->id)->sole();
        $this->assertSame($this->rosa->id, $correccion->patient_id);
        $this->assertStringContainsString('El diente era el 46', $correccion->notes);
        $this->assertSame('Caries en 36', $vieja->fresh()->diagnosis, 'La original no se toca');
    }

    public function test_el_historial_las_muestra_juntas(): void
    {
        $vieja = $this->nota('2026-10-10 12:00');
        MedicalRecord::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'doctor_id' => $this->doctor->id,
            'visit_date' => '2026-10-14', 'notes' => 'El diente era el 46.', 'corrige_a_id' => $vieja->id]);

        Livewire::withQueryParams(['patient' => $this->rosa->id])->test(PatientProfile::class)
            ->call('setTab', 'history')
            ->assertSee('Corrección a la nota del 10/10/2026')
            ->assertSee('Tiene una corrección');
    }

    public function test_al_entrar_a_una_nota_bloqueada_dice_como_corregirla(): void
    {
        $vieja = $this->nota('2026-10-10 12:00');

        $this->get(\App\Filament\Doctor\Resources\MedicalRecordResource::getUrl('edit', ['record' => $vieja]))
            ->assertRedirect();
        // El aviso es de usted y lleva a "Agregar corrección".
        $this->assertStringContainsString('Agregar corrección', json_encode(session('filament.notifications'), JSON_UNESCAPED_UNICODE));
    }
}
