<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\AgendaDelDia;
use App\Filament\Doctor\Pages\ClinicSettings;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\CreateAppointment;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicClosure;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Services\HuecosDisponibles;
use App\Support\FestivosDeMexico;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Día 6 del plan (10-oct-2026): agenda más rápida.
 *
 * Lo que vimos en DaleControl: «Buscar espacio» (el siguiente hueco de N
 * minutos con ese doctor), al dar la cita solo los huecos reales, una columna
 * por doctor, vacaciones de un doctor sin cerrar el consultorio, y festivos de
 * México propuestos que no cierran nada solos.
 */
class AgendaMasRapidaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $javier;
    private Doctor $paola;
    private Patient $ana;
    private Service $limpieza;

    protected function setUp(): void
    {
        parent::setUp();
        // Lunes 12 de octubre de 2026, 8 de la mañana.
        $this->travelTo(\Carbon\Carbon::parse('2026-10-12 08:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $uno = User::forceCreate(['name' => 'Dr. Javier Soto', 'email' => 'j@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $dos = User::forceCreate(['name' => 'Dra. Paola Méndez', 'email' => 'p@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->javier = Doctor::create(['user_id' => $uno->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->paola = Doctor::create(['user_id' => $dos->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Ortodoncia']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $this->limpieza = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Limpieza', 'price' => 600, 'duration_minutes' => 60]);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($uno);
    }

    private function cita(Doctor $doctor, string $inicio, int $minutos = 60, string $estado = 'scheduled'): Appointment
    {
        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $doctor->id, 'patient_id' => $this->ana->id,
            'starts_at' => $inicio, 'ends_at' => \Carbon\Carbon::parse($inicio)->addMinutes($minutos), 'status' => $estado]);
    }

    // ── Buscar espacio ──────────────────────────────────────────

    public function test_buscar_espacio_pone_el_primer_hueco_libre_de_ese_doctor(): void
    {
        $this->cita($this->javier, '2026-10-12 09:00');

        Livewire::test(CreateAppointment::class)
            ->fillForm(['doctor_id' => $this->javier->id, 'service_id' => $this->limpieza->id])
            ->callFormComponentAction('starts_at', 'buscarEspacio')
            ->assertFormSet(['starts_at' => '2026-10-12 10:00:00', 'ends_at' => '2026-10-12 11:00:00']);
    }

    public function test_al_dar_la_cita_ofrece_solo_los_huecos_libres_de_ese_dia(): void
    {
        $this->cita($this->javier, '2026-10-12 09:00');

        $opciones = Livewire::test(CreateAppointment::class)
            ->fillForm(['doctor_id' => $this->javier->id, 'service_id' => $this->limpieza->id, 'starts_at' => '2026-10-12 12:00:00'])
            ->instance()->form->getComponent('data.hueco')->getOptions();

        $this->assertArrayNotHasKey('09:00', $opciones);
        $this->assertArrayHasKey('10:00', $opciones);
    }

    public function test_tocar_un_hueco_pone_esa_hora(): void
    {
        Livewire::test(CreateAppointment::class)
            ->fillForm(['doctor_id' => $this->javier->id, 'service_id' => $this->limpieza->id, 'starts_at' => '2026-10-13 12:00:00'])
            ->set('data.hueco', '16:30')
            ->assertFormSet(['starts_at' => '2026-10-13 16:30:00', 'ends_at' => '2026-10-13 17:30:00']);
    }

    // ── Una columna por doctor ──────────────────────────────────

    public function test_la_agenda_del_dia_trae_una_columna_por_doctor_con_sus_citas_y_sus_huecos(): void
    {
        $this->cita($this->javier, '2026-10-12 10:00');
        $this->cita($this->paola, '2026-10-12 11:00');

        Livewire::test(AgendaDelDia::class)
            ->assertSeeInOrder(['Dr. Javier Soto', 'Dra. Paola Méndez'])
            ->assertSee('10:00')
            ->assertSee('Ana Ruiz')
            ->assertSee('Libre');
    }

    public function test_se_mueve_al_dia_siguiente(): void
    {
        $this->cita($this->paola, '2026-10-13 11:00');

        Livewire::test(AgendaDelDia::class)
            ->call('otroDia', 1)
            ->assertSet('dia', '2026-10-13')
            ->assertSee('11:00');
    }

    // ── Vacaciones de un doctor ─────────────────────────────────

    public function test_las_vacaciones_de_un_doctor_no_cierran_a_los_demas(): void
    {
        ClinicClosure::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->paola->id, 'starts_on' => '2026-10-13', 'ends_on' => '2026-10-16', 'reason' => 'Congreso']);
        $martes = CarbonImmutable::parse('2026-10-13');

        $this->assertSame([], HuecosDisponibles::delDia($this->clinica, $martes, $this->paola->id, 30));
        $this->assertNotSame([], HuecosDisponibles::delDia($this->clinica, $martes, $this->javier->id, 30));
        $this->assertNull($this->clinica->cierraEse($martes), 'El consultorio sigue abierto');
    }

    public function test_si_piden_cualquier_doctor_no_le_toca_al_que_esta_de_vacaciones(): void
    {
        ClinicClosure::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->javier->id, 'starts_on' => '2026-10-13', 'ends_on' => '2026-10-13']);

        $doctor = Appointment::doctorLibreEn($this->clinica->id, \Carbon\Carbon::parse('2026-10-13 10:00'), \Carbon\Carbon::parse('2026-10-13 10:30'));

        $this->assertSame($this->paola->id, $doctor);
    }

    public function test_en_configuracion_se_elige_de_quien_son_los_dias_cerrados(): void
    {
        Livewire::test(ClinicSettings::class)
            ->set('data.cierres', [['starts_on' => '2026-12-21', 'ends_on' => '2026-12-31', 'reason' => 'Vacaciones', 'doctor_id' => $this->paola->id]])
            ->call('save');

        $cierre = ClinicClosure::sole();
        $this->assertSame($this->paola->id, $cierre->doctor_id);
    }

    // ── Festivos de México ──────────────────────────────────────

    public function test_los_festivos_oficiales_y_los_de_costumbre_de_2026(): void
    {
        $festivos = collect(FestivosDeMexico::de(2026))->keyBy('fecha');

        $this->assertTrue($festivos['2026-01-01']['oficial']);
        $this->assertTrue($festivos['2026-02-02']['oficial'], 'Primer lunes de febrero');
        $this->assertTrue($festivos['2026-03-16']['oficial'], 'Tercer lunes de marzo');
        $this->assertTrue($festivos['2026-11-16']['oficial'], 'Tercer lunes de noviembre');
        $this->assertFalse($festivos['2026-04-02']['oficial'], 'Jueves Santo: de costumbre');
        $this->assertFalse($festivos['2026-04-03']['oficial'], 'Viernes Santo');
        $this->assertFalse($festivos['2026-11-02']['oficial'], 'Día de Muertos');
        $this->assertFalse($festivos['2026-12-12']['oficial']);
    }

    public function test_proponer_festivos_cierra_solo_los_que_se_eligen(): void
    {
        // Vienen marcados los oficiales; el doctor deja solo dos.
        Livewire::test(ClinicSettings::class)
            ->mountAction('proponerFestivos')
            ->set('mountedActionsData.0.festivos', ['2026-11-16', '2026-12-25'])
            ->callMountedAction();

        $this->assertSame(['2026-11-16', '2026-12-25'], ClinicClosure::orderBy('starts_on')->get()->map(fn ($c) => $c->starts_on->toDateString())->all());
        $this->assertNull(ClinicClosure::first()->doctor_id);
    }
}
