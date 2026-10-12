<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Modulos;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lo de todos los días siempre está; lo demás se prende y se apaga
 * (11-oct-2026). Romita: «que llegue, se haga sign up y nada más, nada de
 * ruido». Omar: «poner lo que puedes usar el día a día y lo demás poder
 * apagarlo y prenderlo».
 *
 * El consultorio que ya lo usaba (modulos = null) se queda con todo prendido:
 * no se le mueve nada. El nuevo empieza con todo apagado.
 */
class ModulosQueSePrendenTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $dueno;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'free', 'trial_ends_at' => now()->addDays(10),
            'onboarding_status' => 'completed']);
        $this->dueno = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $this->dueno->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->dueno);
    }

    private function renglones(): array
    {
        return collect(Filament::getNavigation())->flatMap(fn ($g) => collect($g->getItems())->map->getLabel())->all();
    }

    private const DE_MODULO = ['Insumos', 'Movimientos', 'Residuos', 'Laboratorio', 'Consentimientos', 'Lista de espera',
        'Gastos', 'Corte del mes', 'Planes de pago', 'Llegada con QR'];

    public function test_el_consultorio_que_ya_lo_usaba_sigue_con_todo_prendido(): void
    {
        $this->assertNull($this->clinica->modulos);
        $this->assertTrue($this->clinica->tieneModulo('inventario'));

        foreach (self::DE_MODULO as $renglon) {
            $this->assertContains($renglon, $this->renglones());
        }
    }

    public function test_con_todo_apagado_el_menu_solo_trae_lo_de_todos_los_dias(): void
    {
        $this->clinica->update(['modulos' => []]);
        $this->dueno->unsetRelation('clinic');

        $menu = $this->renglones();
        foreach (self::DE_MODULO as $renglon) {
            $this->assertNotContains($renglon, $menu);
        }
        foreach (['Escritorio', 'Calendario', 'Citas', 'Pacientes', 'Expediente clínico', 'Recetas', 'Cobros', 'Presupuestos'] as $renglon) {
            $this->assertContains($renglon, $menu);
        }
        $this->assertFalse($this->clinica->fresh()->hasFeature('inventory'));
    }

    public function test_se_prende_y_se_apaga_desde_su_pantalla(): void
    {
        $this->clinica->update(['modulos' => []]);

        Livewire::test(Modulos::class)
            ->assertSee('Inventario')
            ->assertSee('Laboratorio')
            ->call('alternar', 'inventario');

        $this->assertTrue($this->clinica->fresh()->tieneModulo('inventario'));
        $this->assertTrue($this->clinica->fresh()->hasFeature('inventory'));
        $this->assertFalse($this->clinica->fresh()->tieneModulo('laboratorio'));

        Livewire::test(Modulos::class)->call('alternar', 'inventario');
        $this->assertFalse($this->clinica->fresh()->tieneModulo('inventario'));
    }

    public function test_un_modulo_que_no_existe_no_se_guarda(): void
    {
        $this->clinica->update(['modulos' => []]);

        Livewire::test(Modulos::class)->call('alternar', 'cualquier_cosa');

        $this->assertSame([], $this->clinica->fresh()->modulos);
    }

    public function test_dice_cuando_su_plan_no_lo_incluye(): void
    {
        // Básico pagado: el inventario es del Pro.
        $this->clinica->update(['modulos' => [], 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'trial_ends_at' => now()->subDay()]);

        Livewire::test(Modulos::class)->assertSee('Desde el plan Pro');
    }

    public function test_la_asistente_no_los_cambia(): void
    {
        $asistente = User::forceCreate(['name' => 'Asistente', 'email' => 'a@test.com', 'password' => bcrypt('x'), 'role' => 'staff',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->actingAs($asistente);

        $this->get(Modulos::getUrl())->assertForbidden();
    }
}
