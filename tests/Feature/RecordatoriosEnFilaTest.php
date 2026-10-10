<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\RecordatoriosDeManana;
use App\Filament\Doctor\Widgets\AlertsWidget;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Support\RecordatorioDeCita;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Los recordatorios de mañana, en fila.
 *
 * En la entrevista del 10-oct-2026 lo primero que pidió la asistente fue no
 * mandar los recordatorios uno por uno: hoy son de 20 a 30 minutos cada
 * tarde, buscando cita por cita. Esta pantalla pone la fila: uno por
 * paciente, en orden de hora, y al mandar uno (el botón abre su WhatsApp con
 * el mensaje ya escrito y lo deja marcado) sale el siguiente.
 */
class RecordatoriosEnFilaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(16, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function cita(string $nombre, int $hora, string $estado = 'scheduled', int $enDias = 1, ?string $telefono = '6681234567'): Appointment
    {
        $paciente = Patient::firstOrCreate(
            ['clinic_id' => $this->clinica->id, 'first_name' => $nombre, 'last_name' => 'Prueba'],
            ['phone' => $telefono],
        );

        return Appointment::create([
            'clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $paciente->id,
            'starts_at' => now()->addDays($enDias)->setTime($hora, 0), 'ends_at' => now()->addDays($enDias)->setTime($hora, 30),
            'status' => $estado,
        ]);
    }

    public function test_la_fila_es_uno_por_paciente_de_mananaa_en_orden_de_hora(): void
    {
        $this->cita('Camila', 11);
        $primera = $this->cita('Ana', 9);
        $this->cita('Ana', 16);           // misma paciente: un solo mensaje
        $this->cita('Beto', 10, 'confirmed');   // ya dijo que viene
        $this->cita('Carla', 12, 'cancelled');
        $this->cita('Dani', 9, 'scheduled', 2); // pasado mañana
        $this->cita('Hoy', 17, 'scheduled', 0);

        $fila = RecordatorioDeCita::pendientesDeManana($this->clinica->id);

        $this->assertSame(['Ana', 'Camila'], $fila->map(fn ($c) => $c->patient->first_name)->all());
        $this->assertSame($primera->id, $fila->first()->id);
    }

    public function test_la_pantalla_pone_el_boton_del_primero_y_avanza_al_mandarlo(): void
    {
        $ana = $this->cita('Ana', 9);
        $camila = $this->cita('Camila', 11);

        Livewire::test(RecordatoriosDeManana::class)
            ->assertSee('Van 0 de 2')
            ->assertSee('Ana')
            ->assertSee(route('cita.recordar', $ana), false);

        // El botón es la liga que ya existe: abre WhatsApp y deja la cita marcada.
        $this->get(route('cita.recordar', $ana))->assertRedirect();

        Livewire::test(RecordatoriosDeManana::class)
            ->assertSee('Van 1 de 2')
            ->assertSee(route('cita.recordar', $camila), false);
    }

    public function test_cuando_no_queda_nadie_lo_dice(): void
    {
        $ana = $this->cita('Ana', 9);
        $this->get(route('cita.recordar', $ana))->assertRedirect();

        Livewire::test(RecordatoriosDeManana::class)
            ->assertSee('Van 1 de 1')
            ->assertSee('todos los de mañana tienen su recordatorio');
    }

    public function test_sin_telefono_sale_aparte_para_llamarle(): void
    {
        $this->cita('Ana', 9);
        $this->cita('Sin Tel', 10, 'scheduled', 1, null);

        Livewire::test(RecordatoriosDeManana::class)
            ->assertSee('Van 0 de 1')
            ->assertSee('Sin teléfono')
            ->assertSee('Sin Tel');
    }

    public function test_el_aviso_del_escritorio_lleva_a_la_fila(): void
    {
        $this->cita('Ana', 9);

        $alertas = (new AlertsWidget())->getAlerts();
        $aviso = collect($alertas)->first(fn ($a) => str_contains($a['title'], 'sin recordatorio'));

        $this->assertNotNull($aviso);
        $this->assertSame(RecordatoriosDeManana::getUrl(), $aviso['url']);
    }

    public function test_la_asistente_entra(): void
    {
        $asistente = User::forceCreate(['name' => 'Lupita', 'email' => 'lupita@test.com', 'password' => bcrypt('x'), 'role' => 'staff', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->actingAs($asistente);

        $this->get(RecordatoriosDeManana::getUrl())->assertOk()->assertSee('Recordatorios de mañana');
    }

    public function test_en_free_despues_de_la_prueba_no_entra(): void
    {
        $libre = Clinic::create(['name' => 'Consultorio Free', 'plan' => 'free', 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $dr = User::forceCreate(['name' => 'Dra. Free', 'email' => 'free@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $libre->id]);
        $this->actingAs($dr);

        $this->get(RecordatoriosDeManana::getUrl())->assertForbidden();
    }
}
