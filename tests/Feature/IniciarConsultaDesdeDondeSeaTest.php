<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\EditAppointment;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Actions\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Atender al paciente desde donde el doctor lo esté viendo.
 *
 * Octubre 2026: el perfil del paciente, la cita abierta y el aviso de que
 * alguien llegó sin cita no tenían cómo empezar la consulta. El doctor tenía
 * que irse a la consulta y volver a buscar al paciente. Y el recordatorio de
 * mañana estaba escondido en el menú "Acciones": dos clics por paciente.
 */
class IniciarConsultaDesdeDondeSeaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $user;
    private Doctor $doctor;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'slug' => 'consultorio-sonrisas', 'plan' => 'basico',
            'plan_ends_at' => now()->addMonth(), 'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->user);
    }

    private function cita(array $datos = []): Appointment
    {
        return Appointment::create(array_merge([
            'clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'starts_at' => now()->addMinutes(20), 'ends_at' => now()->addMinutes(50), 'status' => 'confirmed',
        ], $datos));
    }

    private function consultaDe(Patient $paciente)
    {
        return Livewire::withQueryParams(['patient' => $paciente->id])->test(Consultation::class);
    }

    // ── La consulta sabe empezar con el paciente ─────────────────

    public function test_con_cita_de_hoy_abre_esa_cita(): void
    {
        $cita = $this->cita();

        $consulta = $this->consultaDe($this->ana);

        $this->assertSame($cita->id, $consulta->get('appointment')->id);
        $this->assertSame('in_progress', $cita->fresh()->status);
    }

    public function test_sin_cita_de_hoy_empieza_una_consulta_sin_cita_con_ese_paciente(): void
    {
        $this->cita(['starts_at' => now()->addDays(3), 'ends_at' => now()->addDays(3)->addHour()]);

        $consulta = $this->consultaDe($this->ana);

        $this->assertFalse($consulta->get('isWalkIn'));
        $this->assertEquals($this->ana->id, $consulta->get('appointment')->patient_id);
        $this->assertSame(1, $consulta->get('currentStep'));
    }

    public function test_la_cita_terminada_de_hoy_no_se_reabre(): void
    {
        $terminada = $this->cita(['status' => 'completed']);

        $consulta = $this->consultaDe($this->ana);

        $this->assertNotSame($terminada->id, $consulta->get('appointment')->id);
        $this->assertSame('completed', $terminada->fresh()->status);
    }

    public function test_el_paciente_de_otro_consultorio_no_se_abre(): void
    {
        $otro = Clinic::create(['name' => 'Otro', 'onboarding_status' => 'completed']);
        $ajeno = Patient::create(['clinic_id' => $otro->id, 'first_name' => 'Pedro', 'last_name' => 'López']);

        $consulta = $this->consultaDe($ajeno);

        $this->assertTrue($consulta->get('isWalkIn'));
        $this->assertSame(0, Appointment::withoutGlobalScopes()->where('patient_id', $ajeno->id)->count());
    }

    // ── Los botones, donde el doctor ve al paciente ──────────────

    public function test_el_perfil_tiene_iniciar_consulta(): void
    {
        Livewire::withQueryParams(['patient' => $this->ana->id])->test(PatientProfile::class)
            ->assertSee('Iniciar consulta')
            ->assertSeeHtml(e(Consultation::urlParaPaciente($this->ana)));
    }

    public function test_la_cita_abierta_tiene_iniciar_consulta(): void
    {
        $cita = $this->cita();

        Livewire::test(EditAppointment::class, ['record' => $cita->id])
            ->assertActionVisible('iniciarConsulta')
            ->assertActionHasUrl('iniciarConsulta', route('filament.doctor.pages.consulta', ['appointment' => $cita->id]));
    }

    public function test_la_cita_terminada_no_ofrece_iniciar_consulta(): void
    {
        $cita = $this->cita(['status' => 'completed']);

        Livewire::test(EditAppointment::class, ['record' => $cita->id])
            ->assertActionHidden('iniciarConsulta');
    }

    public function test_el_aviso_del_que_llego_sin_cita_trae_iniciar_consulta(): void
    {
        $this->post(URL::signedRoute('checkin.store', ['slug' => $this->clinica->slug]), [
            'first_name' => 'Luis', 'last_name' => 'Mora', 'phone' => '6689999999', 'acepta_aviso' => '1',
        ]);

        $luis = Patient::where('first_name', 'Luis')->sole();
        $aviso = json_encode($this->user->fresh()->notifications()->latest()->first()->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->assertStringContainsString('Iniciar consulta', $aviso);
        $this->assertStringContainsString(Consultation::urlParaPaciente($luis), $aviso);
    }

    // ── El recordatorio, a la vista ──────────────────────────────

    public function test_whatsapp_esta_a_la_vista_en_la_lista_de_citas_y_no_en_el_menu(): void
    {
        $tabla = Livewire::test(ListAppointments::class)->instance()->getTable();

        $sueltas = collect($tabla->getActions())->filter(fn ($a) => $a instanceof Action)->map->getName();

        $this->assertContains('whatsapp', $sueltas->all());
    }
}
