<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\OdontogramResource;
use App\Filament\Doctor\Resources\OdontogramResource\Pages\EditOdontogram;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El odontograma que ya pasó se conserva (auditoría del 12-oct-2026): antes
 * uno de hace 3 años se podía editar, cambiarle el paciente o borrarlo sin
 * rastro, y las marcas se perdían si el doctor salía sin dar "Guardar".
 *
 * El más reciente es el que se trabaja (ahí también escribe la consulta);
 * los anteriores son el historial.
 */
class OdontogramaQueSeConservaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function odontograma(string $creado): Odontogram
    {
        $this->travelTo(\Carbon\Carbon::parse($creado));
        $o = Odontogram::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'doctor_id' => $this->doctor->id, 'evaluation_date' => $creado]);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));

        return $o;
    }

    public function test_uno_anterior_no_se_edita_y_lleva_al_mas_reciente(): void
    {
        $viejo = $this->odontograma('2023-05-02 10:00');
        $nuevo = $this->odontograma('2026-10-01 10:00');

        Livewire::test(EditOdontogram::class, ['record' => $viejo->getRouteKey()])
            ->assertRedirect(OdontogramResource::getUrl('edit', ['record' => $nuevo]));
    }

    public function test_despues_de_24_horas_no_se_borra(): void
    {
        $o = $this->odontograma('2026-10-01 10:00');

        Livewire::test(EditOdontogram::class, ['record' => $o->getRouteKey()])->assertActionHidden('delete');
    }

    public function test_el_de_hoy_si_se_puede_borrar_si_fue_un_error(): void
    {
        $o = $this->odontograma('2026-10-14 09:00');

        Livewire::test(EditOdontogram::class, ['record' => $o->getRouteKey()])->assertActionVisible('delete');
    }

    public function test_cada_marca_se_guarda_al_momento(): void
    {
        $o = $this->odontograma('2026-10-14 09:00');

        Livewire::test(EditOdontogram::class, ['record' => $o->getRouteKey()])
            ->dispatch('teeth-updated', teeth: [36 => ['condition' => 'decay', 'notes' => null, 'surfaces' => []]]);

        $this->assertSame('decay', OdontogramTooth::where('odontogram_id', $o->id)->where('tooth_number', 36)->value('condition'));
    }

    public function test_al_editar_no_se_cambia_el_paciente(): void
    {
        $o = $this->odontograma('2026-10-14 09:00');

        Livewire::test(EditOdontogram::class, ['record' => $o->getRouteKey()])->assertFormFieldIsDisabled('patient_id');
    }
}
