<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Resources\OdontogramResource\Pages\EditOdontogram;
use App\Livewire\OdontogramEditor;
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
 * Un dentista marca la caries en la cara donde está: "caries oclusal en el
 * 36", "resina MO en el 46". El odontograma solo pintaba el diente entero de
 * un color, aunque la tabla ya tenía columnas por cara que nadie llenaba.
 *
 * Caries, obturación, sellante y pendiente van por cara. Corona, extracción,
 * ausente, implante, endodoncia, puente, carilla y fractura son del diente.
 */
class OdontogramaPorCarasTest extends TestCase
{
    use RefreshDatabase;

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

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function editor()
    {
        return Livewire::test(OdontogramEditor::class, ['odontogramId' => $this->odontograma->id]);
    }

    private function guardar(array $dientes): void
    {
        $pagina = new EditOdontogram();
        $pagina->record = $this->odontograma;
        $pagina->teethData = $dientes;
        $pagina->guardarDientes();
    }

    public function test_la_caries_se_marca_en_la_cara_que_toca_el_doctor(): void
    {
        $this->editor()
            ->call('setTool', 'decay')
            ->call('applySurface', 36, 'oclusal')
            ->assertSet('teeth.36.surfaces.oclusal', 'decay')
            ->assertSet('teeth.36.surfaces.mesial', null)
            ->assertSet('teeth.36.condition', 'decay');
    }

    public function test_sano_limpia_solo_esa_cara(): void
    {
        $this->editor()
            ->call('setTool', 'filling')
            ->call('applySurface', 46, 'mesial')
            ->call('applySurface', 46, 'oclusal')
            ->call('setTool', 'healthy')
            ->call('applySurface', 46, 'mesial')
            ->assertSet('teeth.46.surfaces.mesial', null)
            ->assertSet('teeth.46.surfaces.oclusal', 'filling')
            ->assertSet('teeth.46.condition', 'filling');
    }

    public function test_la_caries_pesa_mas_que_la_resina_para_el_resumen_del_diente(): void
    {
        $this->editor()
            ->call('setTool', 'filling')
            ->call('applySurface', 16, 'oclusal')
            ->call('setTool', 'decay')
            ->call('applySurface', 16, 'distal')
            ->assertSet('teeth.16.condition', 'decay');
    }

    public function test_una_herramienta_de_diente_sobre_una_cara_marca_el_diente_entero(): void
    {
        $this->editor()
            ->call('setTool', 'extraction')
            ->call('applySurface', 38, 'oclusal')
            ->assertSet('teeth.38.condition', 'extraction')
            ->assertSet('teeth.38.surfaces.oclusal', null);
    }

    public function test_endodoncia_con_resina_conserva_las_dos_cosas(): void
    {
        $this->editor()
            ->call('setTool', 'root_canal')
            ->call('applyTool', 26)
            ->call('setTool', 'filling')
            ->call('applySurface', 26, 'oclusal')
            ->assertSet('teeth.26.condition', 'root_canal')
            ->assertSet('teeth.26.surfaces.oclusal', 'filling');
    }

    public function test_sano_sobre_el_diente_lo_deja_limpio(): void
    {
        $this->editor()
            ->call('setTool', 'decay')
            ->call('applySurface', 11, 'vestibular')
            ->call('setTool', 'healthy')
            ->call('applyTool', 11)
            ->assertSet('teeth.11.condition', 'healthy')
            ->assertSet('teeth.11.surfaces.vestibular', null);
    }

    public function test_en_modo_ver_tocar_una_cara_solo_abre_el_diente(): void
    {
        $this->editor()
            ->call('applySurface', 21, 'oclusal')
            ->assertSet('selectedTooth', 21)
            ->assertSet('teeth.21.surfaces.oclusal', null);
    }

    public function test_las_caras_se_guardan_y_regresan_al_abrirlo(): void
    {
        $this->guardar([
            36 => ['condition' => 'decay', 'notes' => '', 'surfaces' => [
                'vestibular' => null, 'lingual' => null, 'mesial' => 'filling', 'distal' => null, 'oclusal' => 'decay',
            ]],
        ]);

        $this->assertDatabaseHas('odontogram_teeth', [
            'odontogram_id' => $this->odontograma->id,
            'tooth_number' => 36,
            'condition' => 'decay',
            'center_surface' => 'decay',
            'left_surface' => 'filling',
        ]);

        $this->editor()
            ->assertSet('teeth.36.surfaces.oclusal', 'decay')
            ->assertSet('teeth.36.surfaces.mesial', 'filling');
    }

    public function test_quitar_las_caras_borra_el_registro(): void
    {
        $this->guardar([36 => ['condition' => 'decay', 'notes' => '', 'surfaces' => ['oclusal' => 'decay']]]);
        $this->guardar([36 => ['condition' => 'healthy', 'notes' => '', 'surfaces' => ['oclusal' => null]]]);

        $this->assertDatabaseMissing('odontogram_teeth', [
            'odontogram_id' => $this->odontograma->id,
            'tooth_number' => 36,
        ]);
    }

    public function test_el_editor_pinta_la_cara_con_el_color_de_la_condicion(): void
    {
        $rojo = OdontogramTooth::conditionColors()['decay'];

        $this->editor()
            ->call('setTool', 'decay')
            ->call('applySurface', 36, 'oclusal')
            ->assertSeeHtml('data-cara="36-oclusal" fill="' . $rojo . '"');
    }

    public function test_el_perfil_muestra_las_caras_marcadas(): void
    {
        OdontogramTooth::create([
            'odontogram_id' => $this->odontograma->id,
            'tooth_number' => 36,
            'condition' => 'decay',
            'center_surface' => 'decay',
        ]);
        $rojo = OdontogramTooth::conditionColors()['decay'];

        Livewire::withQueryParams(['patient' => $this->paciente->id])
            ->test(PatientProfile::class)
            ->call('setTab', 'odontogram')
            ->assertSeeHtml('data-cara="36-oclusal" fill="' . $rojo . '"');
    }

    public function test_los_dientes_de_leche_aparecen_al_elegir_denticion_temporal(): void
    {
        $this->editor()
            ->assertDontSeeHtml('Diente 55 ')
            ->call('setDenticion', 'temporal')
            ->assertSeeInOrder(['Diente 55 ', 'Diente 51 ', 'Diente 61 ', 'Diente 65 ', 'Diente 85 ', 'Diente 81 ', 'Diente 71 ', 'Diente 75 '])
            ->assertDontSeeHtml('Diente 18 ');
    }

    public function test_la_denticion_mixta_muestra_permanentes_y_temporales(): void
    {
        $this->editor()
            ->call('setDenticion', 'mixta')
            ->assertSeeInOrder(['Diente 18 ', 'Diente 28 ', 'Diente 55 ', 'Diente 65 ', 'Diente 85 ', 'Diente 75 ', 'Diente 48 ', 'Diente 38 ']);
    }

    public function test_si_ya_tiene_dientes_de_leche_marcados_abre_en_mixta(): void
    {
        OdontogramTooth::create([
            'odontogram_id' => $this->odontograma->id,
            'tooth_number' => 75,
            'condition' => 'decay',
            'center_surface' => 'decay',
        ]);

        $this->editor()
            ->assertSet('denticion', 'mixta')
            ->assertSet('teeth.75.surfaces.oclusal', 'decay');
    }

    public function test_un_molar_de_leche_es_molar(): void
    {
        $this->assertSame('molar', OdontogramTooth::tipo(75));
        $this->assertSame('molar', OdontogramTooth::tipo(54));
        $this->assertSame('canino', OdontogramTooth::tipo(83));
        $this->assertSame('premolar', OdontogramTooth::tipo(15));
    }

    public function test_el_resumen_separa_lo_que_falta_tratar_de_lo_que_ya_tiene(): void
    {
        $this->editor()
            ->call('setTool', 'decay')
            ->call('applySurface', 36, 'oclusal')
            ->call('applySurface', 16, 'distal')
            ->call('setTool', 'extraction')
            ->call('applyTool', 48)
            ->call('setTool', 'filling')
            ->call('applySurface', 26, 'oclusal')
            ->assertSeeInOrder(['Por tratar', '2 caries', '1 extracci', 'Tratamientos existentes', '1 obturaci']);
    }
}
