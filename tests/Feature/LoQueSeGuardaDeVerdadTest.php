<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\ClinicSettings;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Supply;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Dos huecos entre "la pantalla lo pide" y "la base lo guarda".
 *
 * El primero: Ajustes enseñaba los tres campos de anestesia, decía
 * "Configuración guardada" y no escribía ninguno. La vigilancia de dosis —la
 * única pieza de seguridad del paciente en la consulta— no se podía encender
 * por ningún lado, y el doctor no tenía forma de enterarse.
 *
 * El segundo: register() validaba el tipo y la cantidad y después dejaba que
 * los datos de la llamada los pisaran.
 */
class LoQueSeGuardaDeVerdadTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Sonrisas',
            'slug' => 'consultorio-sonrisas',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addYear(),
            'onboarding_status' => 'completed',
        ]);

        $this->doctor = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        Doctor::create([
            'user_id' => $this->doctor->id,
            'clinic_id' => $this->clinica->id,
            'specialty' => 'Odontología',
        ]);
    }

    // ── La anestesia ─────────────────────────────────────────────

    public function test_la_configuracion_de_anestesia_si_se_guarda(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        Livewire::test(ClinicSettings::class)
            ->set('data.anesthetic_max_mg_kg', 7)
            ->set('data.anesthetic_mg_ml', 20)
            ->set('data.anesthetic_ml_per_cartridge', 1.8)
            ->call('save')
            ->assertHasNoErrors();

        $clinica = $this->clinica->fresh();

        $this->assertEquals(7, (float) $clinica->anesthetic_max_mg_kg);
        $this->assertEquals(20, (float) $clinica->anesthetic_mg_ml);
        $this->assertEquals(1.8, (float) $clinica->anesthetic_ml_per_cartridge);
    }

    public function test_al_volver_a_entrar_los_valores_siguen_ahi(): void
    {
        $this->clinica->update([
            'anesthetic_max_mg_kg' => 4.4,
            'anesthetic_mg_ml' => 20,
            'anesthetic_ml_per_cartridge' => 1.8,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        Livewire::test(ClinicSettings::class)
            ->assertSet('data.anesthetic_max_mg_kg', fn ($valor) => (float) $valor === 4.4)
            ->assertSet('data.anesthetic_mg_ml', fn ($valor) => (float) $valor === 20.0);
    }

    public function test_dejarlos_vacios_los_apaga_en_vez_de_guardar_cero(): void
    {
        $this->clinica->update(['anesthetic_max_mg_kg' => 7]);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        Livewire::test(ClinicSettings::class)
            ->set('data.anesthetic_max_mg_kg', '')
            ->call('save')
            ->assertHasNoErrors();

        // Null y no 0: con 0 el sistema compararía contra un máximo de cero y
        // avisaría de sobredosis en cada paciente.
        $this->assertNull($this->clinica->fresh()->anesthetic_max_mg_kg);
    }

    // ── La puerta del kardex ─────────────────────────────────────

    public function test_la_llamada_no_puede_pisar_el_tipo_ni_la_cantidad(): void
    {
        $this->actingAs($this->doctor);

        $insumo = Supply::create([
            'clinic_id' => $this->clinica->id,
            'name' => 'Guantes de nitrilo M',
            'unit' => 'pieza',
        ]);

        $insumo->register('in', 100);

        // Una salida que intenta colarse como entrada, con cantidad negativa.
        $movimiento = $insumo->register('out', 5, [
            'type' => 'in',
            'quantity' => -5,
            'clinic_id' => 999,
        ]);

        $this->assertSame('out', $movimiento->type);
        $this->assertEquals(5, (float) $movimiento->quantity);
        $this->assertSame($this->clinica->id, $movimiento->clinic_id);
        $this->assertEquals(95, (float) $insumo->fresh()->currentStock());
    }
}
