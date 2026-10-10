<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La foto de la hoja vieja, en el expediente nuevo.
 *
 * Del dentista (10-oct-2026): "Los 1,500 expedientes no los pasaría, ni
 * loco… Lo que sí me ayudaría es poder tomarle foto a la hoja vieja y pegarla
 * en su expediente nuevo". También las radiografías del sensor o del
 * celular. Van al disco privado: son documentos clínicos.
 */
class ArchivosDelPacienteTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $doctor;
    private Patient $rosa;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'free', 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->rosa = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'Valenzuela']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);
    }

    private function perfil()
    {
        return Livewire::withQueryParams(['patient' => $this->rosa->id])->test(PatientProfile::class)->call('setTab', 'files');
    }

    public function test_la_foto_se_sube_desde_el_perfil_y_queda_en_su_expediente(): void
    {
        $this->perfil()
            ->set('archivoNuevo', UploadedFile::fake()->image('hoja-2019.jpg', 1200, 1600))
            ->set('notaDelArchivo', 'Expediente en papel 2019')
            ->call('subirArchivo')
            ->assertHasNoErrors()
            ->assertSee('Expediente en papel 2019');

        $archivo = PatientFile::firstOrFail();
        $this->assertSame($this->rosa->id, $archivo->patient_id);
        $this->assertSame($this->clinica->id, $archivo->clinic_id);
        $this->assertSame($this->doctor->id, $archivo->subido_por);
        Storage::disk('local')->assertExists($archivo->path);
        $this->assertStringStartsWith("patient-files/{$this->clinica->id}/{$this->rosa->id}/", $archivo->path);
    }

    public function test_tambien_acepta_pdf(): void
    {
        $this->perfil()
            ->set('archivoNuevo', UploadedFile::fake()->create('radiografia.pdf', 300, 'application/pdf'))
            ->call('subirArchivo')
            ->assertHasNoErrors();

        $this->assertSame(1, PatientFile::count());
    }

    public function test_un_tipo_que_no_es_imagen_ni_pdf_no_entra(): void
    {
        $this->perfil()
            ->set('archivoNuevo', UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'))
            ->call('subirArchivo')
            ->assertHasErrors('archivoNuevo');

        $this->assertSame(0, PatientFile::count());
    }

    public function test_se_abre_solo_desde_su_consultorio(): void
    {
        $this->perfil()->set('archivoNuevo', UploadedFile::fake()->image('hoja.jpg'))->call('subirArchivo');
        $archivo = PatientFile::firstOrFail();

        $this->get(route('paciente.archivo', $archivo))->assertOk();

        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'free', 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $ajeno = User::forceCreate(['name' => 'Dr. Otro', 'email' => 'otro@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $otra->id]);
        $this->actingAs($ajeno);

        $this->get(route('paciente.archivo', $archivo->id))->assertNotFound();
    }

    public function test_sin_sesion_no_se_abre(): void
    {
        $this->perfil()->set('archivoNuevo', UploadedFile::fake()->image('hoja.jpg'))->call('subirArchivo');
        $archivo = PatientFile::firstOrFail();
        auth()->logout();

        $this->get(route('paciente.archivo', $archivo))->assertForbidden();
    }

    public function test_la_asistente_tambien_sube(): void
    {
        $lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->actingAs($lupita);

        $this->perfil()->set('archivoNuevo', UploadedFile::fake()->image('hoja.jpg'))->call('subirArchivo')->assertHasNoErrors();

        $this->assertSame($lupita->id, PatientFile::firstOrFail()->subido_por);
    }
}
