<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\TraerMisDatos;
use App\Filament\Doctor\Resources\PatientResource\Pages\CreatePatient;
use App\Filament\Doctor\Resources\PatientResource\Pages\ListPatients;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Día 3 del plan (10-oct-2026): cambiarse a DocFácil sin perder nada.
 *
 * Quien ya tiene sistema (o su libreta en Excel) no se cambia si pierde sus
 * citas de las próximas semanas o lo que le deben. Dentalink y DaleControl
 * importan pacientes; ninguno de los dos documenta que traiga citas futuras
 * ni saldos. Aquí se traen las tres cosas desde una sola pantalla, «Traer mis
 * datos», y al dar de alta un paciente se avisa si ya existe.
 */
class CambiarseADocFacilTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        // Sin viajar en el tiempo: la subida de archivos de Livewire se
        // invalida si el reloj se adelanta. Las fechas se arman desde hoy.
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier Soto', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function subir(string $accion, string $csv, array $mapa)
    {
        return Livewire::test(TraerMisDatos::class)
            ->callAction($accion, data: [
                'file' => UploadedFile::fake()->createWithContent('datos.csv', $csv),
                'columnMap' => $mapa,
            ])
            ->assertHasNoActionErrors();
    }

    // ── La pantalla ─────────────────────────────────────────────

    public function test_la_pantalla_trae_los_tres_pasos(): void
    {
        Livewire::test(TraerMisDatos::class)
            ->assertSee('Sus pacientes')->assertSee('Sus citas que vienen')->assertSee('Lo que le deben')
            ->assertActionVisible('importarPacientes')
            ->assertActionVisible('importarCitas')
            ->assertActionVisible('importarSaldos');
    }

    // ── Pacientes con CURP ──────────────────────────────────────

    public function test_la_curp_del_archivo_llena_nacimiento_y_sexo(): void
    {
        $this->subir('importarPacientes', "Nombre,Teléfono,CURP\nRosa García López,6681234567,galr850315mslrps05\nLuis Pérez,6689999999,NOESCURP\n",
            ['first_name' => 'Nombre', 'phone' => 'Teléfono', 'curp' => 'CURP']);

        $rosa = Patient::where('phone', '6681234567')->sole();
        $this->assertSame('GALR850315MSLRPS05', $rosa->curp);
        $this->assertSame('1985-03-15', $rosa->birth_date->toDateString());
        $this->assertSame('female', $rosa->gender);
        $this->assertNull(Patient::where('phone', '6689999999')->sole()->curp, 'Una CURP que no es válida no se guarda');
    }

    public function test_no_duplica_al_paciente_que_ya_tiene_esa_curp(): void
    {
        Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'García', 'curp' => 'GALR850315MSLRPS05']);

        $this->subir('importarPacientes', "Nombre,CURP\nRosa García López,GALR850315MSLRPS05\n", ['first_name' => 'Nombre', 'curp' => 'CURP']);

        $this->assertSame(1, Patient::count());
    }

    // ── Citas que vienen ────────────────────────────────────────

    public function test_importa_las_citas_que_vienen_y_reconoce_al_paciente_por_telefono(): void
    {
        $ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $limpieza = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Limpieza', 'price' => 600, 'duration_minutes' => 45]);

        $dia = now()->addDays(5);
        $this->subir('importarCitas', "Paciente,Tel,Fecha,Hora,Motivo\nAna Ruiz,(668) 123-4567,{$dia->format('d/m/Y')},4:30 pm,limpieza\n",
            ['paciente' => 'Paciente', 'telefono' => 'Tel', 'fecha' => 'Fecha', 'hora' => 'Hora', 'motivo' => 'Motivo']);

        $cita = Appointment::sole();
        $this->assertSame($ana->id, $cita->patient_id);
        $this->assertSame($dia->format('Y-m-d') . ' 16:30', $cita->starts_at->format('Y-m-d H:i'));
        $this->assertSame($dia->format('Y-m-d') . ' 17:15', $cita->ends_at->format('Y-m-d H:i'), 'Dura lo que dura el servicio');
        $this->assertSame($limpieza->id, $cita->service_id);
        $this->assertSame($this->doctor->id, $cita->doctor_id);
        $this->assertSame('scheduled', $cita->status);
    }

    public function test_si_el_paciente_no_existe_lo_crea(): void
    {
        $this->subir('importarCitas', "Paciente,Tel,Fecha,Hora,Duración,Motivo\nJuan Carlos Pérez López,6687654321," . now()->addDays(10)->format('Y-m-d') . ",10:00,60,Revisión de brackets\n",
            ['paciente' => 'Paciente', 'telefono' => 'Tel', 'fecha' => 'Fecha', 'hora' => 'Hora', 'duracion' => 'Duración', 'motivo' => 'Motivo']);

        $juan = Patient::where('phone', '6687654321')->sole();
        $this->assertSame('Juan Carlos', $juan->first_name);
        $this->assertSame('Pérez López', $juan->last_name);
        $cita = Appointment::sole();
        $this->assertSame(60, (int) $cita->starts_at->diffInMinutes($cita->ends_at));
        $this->assertStringContainsString('Revisión de brackets', (string) $cita->notes);
    }

    public function test_las_citas_que_ya_pasaron_no_se_traen_y_subir_dos_veces_no_duplica(): void
    {
        $paso = now()->subDays(5)->format('d/m/Y');
        $viene = now()->addDays(5);
        $csv = "Paciente,Tel,Fecha,Hora\nAna Ruiz,6681234567,{$paso},10:00\nAna Ruiz,6681234567,{$viene->format('d/m/Y')},10:00\n";
        $mapa = ['paciente' => 'Paciente', 'telefono' => 'Tel', 'fecha' => 'Fecha', 'hora' => 'Hora'];

        $this->subir('importarCitas', $csv, $mapa);
        $this->subir('importarCitas', $csv, $mapa);

        $this->assertSame(1, Appointment::count());
        $this->assertSame($viene->toDateString(), Appointment::sole()->starts_at->toDateString());
    }

    public function test_reconoce_al_doctor_por_su_nombre(): void
    {
        $otra = User::forceCreate(['name' => 'Dra. Paola Méndez', 'email' => 'p@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $paola = Doctor::create(['user_id' => $otra->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Ortodoncia']);

        $this->subir('importarCitas', "Paciente,Fecha,Hora,Doctor\nAna Ruiz," . now()->addDays(5)->format('d/m/Y') . ",10:00,paola\n",
            ['paciente' => 'Paciente', 'fecha' => 'Fecha', 'hora' => 'Hora', 'doctor' => 'Doctor']);

        $this->assertSame($paola->id, Appointment::sole()->doctor_id);
    }

    // ── Lo que le deben ─────────────────────────────────────────

    public function test_importa_los_saldos_como_cobros_pendientes(): void
    {
        $ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);

        $this->subir('importarSaldos', "Paciente,Tel,Debe,Concepto\nAna Ruiz,6681234567,\"$1,250.50\",Corona 36\nPedro Gómez,,800,\n",
            ['paciente' => 'Paciente', 'telefono' => 'Tel', 'monto' => 'Debe', 'concepto' => 'Concepto']);

        $deAna = Payment::where('patient_id', $ana->id)->sole();
        $this->assertSame('pending', $deAna->status);
        $this->assertSame(1250.5, $deAna->remaining);
        $this->assertSame('Corona 36', $deAna->notes);
        $pedro = Patient::where('first_name', 'Pedro')->sole();
        $this->assertSame('Saldo anterior', Payment::where('patient_id', $pedro->id)->sole()->notes);
        $this->assertSame(2050.5, Payment::saldoPorCobrar($this->clinica->id));
    }

    public function test_subir_los_saldos_dos_veces_no_los_duplica(): void
    {
        $csv = "Paciente,Debe\nAna Ruiz,500\n";
        $this->subir('importarSaldos', $csv, ['paciente' => 'Paciente', 'monto' => 'Debe']);
        $this->subir('importarSaldos', $csv, ['paciente' => 'Paciente', 'monto' => 'Debe']);

        $this->assertSame(1, Payment::count());
    }

    // ── ¿Es esta persona? ───────────────────────────────────────

    public function test_al_dar_de_alta_un_telefono_repetido_pregunta_si_es_la_misma_persona(): void
    {
        $ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);

        Livewire::test(CreatePatient::class)
            ->fillForm(['first_name' => 'Anita', 'phone' => '668 123 4567'])
            ->assertSee('¿Es esta persona?')
            ->assertSee('Ana Ruiz');
    }

    public function test_al_dar_de_alta_un_nombre_repetido_tambien_pregunta(): void
    {
        Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);

        Livewire::test(CreatePatient::class)
            ->fillForm(['first_name' => 'ana', 'last_name' => 'ruiz'])
            ->assertSee('¿Es esta persona?');
    }

    public function test_un_paciente_de_otro_consultorio_no_sale(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico']);
        Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);

        Livewire::test(CreatePatient::class)
            ->fillForm(['first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567'])
            ->assertDontSee('¿Es esta persona?');
    }

    // ── Al terminar la configuración inicial ────────────────────

    public function test_al_terminar_la_configuracion_puede_ir_directo_a_traer_sus_datos(): void
    {
        $this->clinica->update(['onboarding_status' => 'pending']);

        Livewire::test(\App\Filament\Doctor\Pages\Onboarding::class)
            ->set('step', 5)
            ->assertSee('Traer mis pacientes')
            ->call('completeOnboarding', true)
            ->assertRedirect(TraerMisDatos::getUrl());

        $this->assertSame('completed', $this->clinica->fresh()->onboarding_status);
    }
}
