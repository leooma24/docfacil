<?php

namespace Tests\Feature;

use App\Filament\Doctor\Widgets\SetupChecklistWidget;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La lista de primeros pasos del doctor nuevo.
 *
 * El importador de Excel ya existía, pero la lista solo decía "Agrega tu
 * primer paciente". El dentista que ya tiene 300 en una hoja no va a empezar
 * tecleando el primero — y pasarle sus pacientes es justo lo que distingue a
 * DocFácil.
 */
class ChecklistDeArranqueTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Nuevo',
            'slug' => 'consultorio-nuevo',
            'plan' => 'free',
            'trial_ends_at' => now()->addDays(15),
            'onboarding_status' => 'completed',
        ]);

        $this->doctor = User::forceCreate([
            'name' => 'Dra. Ana Nueva',
            'email' => 'ana@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);
    }

    private function pasoDePacientes(): array
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        return collect(Livewire::test(SetupChecklistWidget::class)->instance()->getViewData()['items'])
            ->firstWhere('key', 'patient');
    }

    public function test_sin_pacientes_lo_primero_que_ofrece_es_subir_el_excel(): void
    {
        $paso = $this->pasoDePacientes();

        $this->assertSame('Importar de Excel', $paso['cta']);
        $this->assertStringEndsWith('/doctor/pacientes?action=import', $paso['url']);
    }

    public function test_capturar_a_mano_sigue_ahi_como_segunda_opcion(): void
    {
        $paso = $this->pasoDePacientes();

        $this->assertSame('Crear uno', $paso['cta2']);
        $this->assertStringEndsWith('/doctor/pacientes/create', $paso['url2']);
    }

    public function test_con_pacientes_ya_no_insiste_con_el_excel(): void
    {
        Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Fernando',
            'last_name' => 'Morales',
            'phone' => '5512345678',
        ]);

        $paso = $this->pasoDePacientes();

        $this->assertTrue($paso['done']);
        $this->assertSame('Ver pacientes', $paso['cta']);
        $this->assertNull($paso['cta2']);
    }

    public function test_la_tarjeta_ensena_las_dos_opciones(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        Livewire::test(SetupChecklistWidget::class)
            ->assertSee('Importar de Excel')
            ->assertSee('o Crear uno')
            ->assertSee('action=import', escape: false);
    }
}
