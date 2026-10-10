<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Resources\PatientResource\Pages\CreatePatient;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Support\Curp;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * NOM-024-SSA3-2012, 6.5: la CURP es el identificador del paciente y, con el
 * nombre y los apellidos, forma los datos mínimos (6.5.5). La Tabla 1 pide
 * además fecha y entidad de nacimiento, sexo, nacionalidad y residencia.
 *
 * El sistema no inventa la CURP (6.5.1): la valida (formato y dígito
 * verificador) y de ella toma fecha de nacimiento, sexo y estado, para no
 * capturarlos dos veces. No es obligatoria al dar de alta (no frena la cita
 * del paciente nuevo), pero el perfil avisa cuando falta.
 */
class CurpYDatosMinimosTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    // ── La CURP ─────────────────────────────────────────────────

    public function test_valida_formato_y_digito_verificador(): void
    {
        $this->assertTrue(Curp::valida('GALR850315MSLRPS05'));
        $this->assertTrue(Curp::valida('badd110313hcmlnsa6'), 'En minúsculas también');
        $this->assertFalse(Curp::valida('GALR850315MSLRPS04'), 'Dígito verificador equivocado');
        $this->assertFalse(Curp::valida('GALR851315MSLRPS05'), 'Mes 13');
        $this->assertFalse(Curp::valida('GALR850315MXXRPS05'), 'Estado que no existe');
    }

    public function test_de_la_curp_salen_nacimiento_sexo_y_estado(): void
    {
        $this->assertSame(['birth_date' => '1985-03-15', 'gender' => 'female', 'entidad_nacimiento' => 'SL'], Curp::datos('GALR850315MSLRPS05'));
        $this->assertSame('2011-03-13', Curp::datos('BADD110313HCMLNSA6')['birth_date'], 'Letra en la posición 17: nació en los 2000');
        $this->assertSame('male', Curp::datos('BADD110313HCMLNSA6')['gender']);
    }

    // ── La pantalla ─────────────────────────────────────────────

    public function test_al_dar_de_alta_con_curp_se_llenan_nacimiento_sexo_y_estado(): void
    {
        Livewire::test(CreatePatient::class)
            ->fillForm(['first_name' => 'Rosa', 'last_name' => 'García López', 'phone' => '6681234567'])
            ->set('data.curp', 'galr850315mslrps05')
            ->assertSet('data.birth_date', '1985-03-15')
            ->assertSet('data.gender', 'female')
            ->assertSet('data.entidad_nacimiento', 'SL')
            ->call('create')
            ->assertHasNoFormErrors();

        $rosa = Patient::sole();
        $this->assertSame('GALR850315MSLRPS05', $rosa->curp);
        $this->assertSame('SL', $rosa->entidad_nacimiento);
    }

    public function test_una_curp_mal_tecleada_no_se_guarda(): void
    {
        Livewire::test(CreatePatient::class)
            ->fillForm(['first_name' => 'Rosa', 'last_name' => 'García', 'phone' => '6681234567', 'curp' => 'GALR850315MSLRPS04'])
            ->call('create')
            ->assertHasFormErrors(['curp']);
    }

    public function test_la_misma_curp_no_se_repite_en_el_consultorio(): void
    {
        Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'García', 'curp' => 'GALR850315MSLRPS05']);

        Livewire::test(CreatePatient::class)
            ->fillForm(['first_name' => 'Rosa', 'last_name' => 'García', 'phone' => '6681234567', 'curp' => 'GALR850315MSLRPS05'])
            ->call('create')
            ->assertHasFormErrors(['curp']);
    }

    public function test_sin_curp_si_se_da_de_alta_y_guarda_nacionalidad_y_residencia(): void
    {
        Livewire::test(CreatePatient::class)
            ->fillForm(['first_name' => 'Beto', 'last_name' => 'Ruiz', 'phone' => '6681234567',
                'nacionalidad' => 'MEX', 'estado_residencia' => 'SL', 'municipio_residencia' => 'Ahome'])
            ->call('create')
            ->assertHasNoFormErrors();

        $beto = Patient::sole();
        $this->assertNull($beto->curp);
        $this->assertSame('Ahome', $beto->municipio_residencia);
    }

    public function test_el_perfil_avisa_cuando_falta_la_curp(): void
    {
        $sin = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Beto', 'last_name' => 'Ruiz']);
        $con = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'García', 'curp' => 'GALR850315MSLRPS05']);

        Livewire::withQueryParams(['patient' => $sin->id])->test(PatientProfile::class)->assertSee('Falta la CURP');
        Livewire::withQueryParams(['patient' => $con->id])->test(PatientProfile::class)->assertDontSee('Falta la CURP')->assertSee('GALR850315MSLRPS05');
    }
}
