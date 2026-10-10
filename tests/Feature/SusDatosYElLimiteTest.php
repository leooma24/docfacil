<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\MisDatos;
use App\Filament\Doctor\Widgets\AlertsWidget;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\Payment;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Llevarse sus datos, y saber el límite de pacientes antes de toparse con él.
 *
 * De la prueba con 20 doctores (12-oct-2026): ninguno pagaría sin saber que
 * puede llevarse sus datos ("tuve un software que me dejó sin mis
 * expedientes"), y el que tiene 800 pacientes se enteraba del tope de 200 del
 * Básico ya adentro.
 */
class SusDatosYElLimiteTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $doctor;
    private Doctor $ficha;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'slug' => 'sonrisas', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->ficha = Doctor::create(['user_id' => $this->doctor->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);
    }

    /** Baja el ZIP y lo abre: nombre de archivo => contenido. */
    private function bajar(): array
    {
        $r = $this->get(route('datos.exportar'));
        $r->assertOk();
        $this->assertStringContainsString('.zip', (string) $r->headers->get('content-disposition'));

        $ruta = tempnam(sys_get_temp_dir(), 'zip');
        file_put_contents($ruta, $r->streamedContent() ?: file_get_contents($r->getFile()->getPathname()));
        $zip = new \ZipArchive();
        $zip->open($ruta);
        $archivos = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nombre = $zip->getNameIndex($i);
            $archivos[$nombre] = $zip->getFromIndex($i);
        }
        $zip->close();

        return $archivos;
    }

    // ── Llevarse sus datos ──────────────────────────────────────

    public function test_el_doctor_baja_todos_sus_datos_en_un_zip(): void
    {
        $rosa = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'Valenzuela', 'phone' => '6681234567', 'allergies' => 'Penicilina']);
        Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->ficha->id, 'patient_id' => $rosa->id,
            'starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addMinutes(30), 'status' => 'scheduled']);
        $cobro = Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $rosa->id, 'amount' => 4500, 'amount_paid' => 0,
            'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => today()]);
        $cobro->registrarAbono(1500, 'cash');
        TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $rosa->id, 'title' => 'Corona 36', 'status' => 'sent',
            'subtotal' => 5000, 'discount' => 0, 'total' => 5000, 'public_token' => 'secreto123']);
        Storage::disk('local')->put("patient-files/{$this->clinica->id}/{$rosa->id}/hoja.jpg", 'imagen');
        PatientFile::create(['clinic_id' => $this->clinica->id, 'patient_id' => $rosa->id, 'path' => "patient-files/{$this->clinica->id}/{$rosa->id}/hoja.jpg",
            'nombre' => 'hoja-2019.jpg', 'mime' => 'image/jpeg', 'size' => 6]);

        $zip = $this->bajar();

        foreach (['LEEME.txt', 'pacientes.csv', 'citas.csv', 'cobros.csv', 'abonos.csv', 'presupuestos.csv', 'recetas.csv', 'notas_clinicas.csv', 'gastos.csv', 'laboratorio.csv'] as $f) {
            $this->assertArrayHasKey($f, $zip, "Falta {$f}");
        }
        $this->assertStringContainsString('Valenzuela', $zip['pacientes.csv']);
        $this->assertStringContainsString('Penicilina', $zip['pacientes.csv']);
        $this->assertStringContainsString('1500', $zip['abonos.csv']);
        $this->assertStringContainsString('Corona 36', $zip['presupuestos.csv']);
        $this->assertStringNotContainsString('secreto123', $zip['presupuestos.csv'], 'Las ligas privadas no salen.');
        $this->assertSame('imagen', $zip["archivos/{$rosa->id}-Rosa-Valenzuela/hoja-2019.jpg"] ?? null, 'Las fotos del expediente van incluidas.');
    }

    public function test_no_trae_nada_de_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'Secreta']);

        $zip = $this->bajar();

        $this->assertStringNotContainsString('Secreta', implode("\n", $zip));
    }

    public function test_queda_anotado_quien_bajo_los_datos(): void
    {
        $this->bajar();

        $this->assertDatabaseHas('activity_log', ['causer_id' => $this->doctor->id, 'description' => 'Bajó todos los datos del consultorio']);
    }

    public function test_la_asistente_no_puede_bajar_todo(): void
    {
        $lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id, 've_dinero' => true]);
        $this->actingAs($lupita);

        $this->get(route('datos.exportar'))->assertForbidden();
        $this->get(MisDatos::getUrl())->assertForbidden();
    }

    public function test_la_pantalla_dice_que_pasa_si_deja_de_pagar(): void
    {
        $this->get(MisDatos::getUrl())->assertOk()
            ->assertSee('Si deja de pagar')
            ->assertSee('no se borra')
            ->assertSee(route('datos.exportar'), false);
    }

    // ── El límite de pacientes, antes de toparse ────────────────

    public function test_el_registro_dice_los_limites_de_cada_plan(): void
    {
        auth()->logout();

        $this->get('/doctor/register')->assertOk()
            ->assertSee('15 días con todo')
            ->assertSee('hasta 15 pacientes')
            ->assertSee('hasta 200 pacientes')
            ->assertSee('sin límite de pacientes');
    }

    public function test_en_la_prueba_con_mas_de_200_le_avisa_antes_de_que_termine(): void
    {
        $this->clinica->update(['plan' => 'free', 'plan_ends_at' => null, 'trial_ends_at' => now()->addDays(5)]);
        for ($i = 0; $i < 205; $i++) {
            Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => "P{$i}", 'last_name' => 'X']);
        }

        $avisos = collect((new AlertsWidget())->getAlerts())->pluck('title')->implode(' | ');

        $this->assertStringContainsString('205 pacientes', $avisos);
        $this->assertStringContainsString('200', $avisos);
    }

    public function test_en_el_basico_cerca_del_tope_le_avisa(): void
    {
        for ($i = 0; $i < 185; $i++) {
            Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => "P{$i}", 'last_name' => 'X']);
        }

        $avisos = collect((new AlertsWidget())->getAlerts())->pluck('title')->implode(' | ');

        $this->assertStringContainsString('185 de 200 pacientes', $avisos);
    }

    public function test_lejos_del_tope_no_molesta(): void
    {
        for ($i = 0; $i < 20; $i++) {
            Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => "P{$i}", 'last_name' => 'X']);
        }

        $avisos = collect((new AlertsWidget())->getAlerts())->pluck('title')->implode(' | ');

        $this->assertStringNotContainsString('de 200 pacientes', $avisos);
    }
}
