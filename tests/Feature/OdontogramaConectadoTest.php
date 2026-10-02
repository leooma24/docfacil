<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Resources\OdontogramResource\Pages\CreateOdontogram;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Support\OdontogramaClinico;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El odontograma no es una hoja suelta: lo que el doctor hace en la consulta
 * lo actualiza, lo que falta tratar se vuelve presupuesto, y cada visita deja
 * su versión para ver cómo cambió la boca.
 */
class OdontogramaConectadoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Clinic $clinic;
    private Doctor $doctor;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create(['name' => 'Consultorio Test', 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinic->id,
        ]);
        $this->doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $this->clinic->id, 'specialty' => 'Odontología']);
        $this->patient = Patient::create(['clinic_id' => $this->clinic->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz']);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->user);
    }

    private function servicio(string $nombre, float $precio = 600): Service
    {
        return Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => $nombre,
            'price' => $precio,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
    }

    private function odontograma(array $dientes, string $fecha = '2026-09-01'): Odontogram
    {
        $o = Odontogram::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'evaluation_date' => $fecha,
        ]);
        foreach ($dientes as $num => $datos) {
            OdontogramTooth::create(['odontogram_id' => $o->id, 'tooth_number' => $num] + $datos);
        }

        return $o;
    }

    private function consultaCon(array $procedimientos): void
    {
        $general = $this->servicio('Consulta general', 300);

        Livewire::test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $general->id)
            ->call('startWalkIn')
            ->set('procedures', $procedimientos)
            ->call('saveAndComplete');
    }

    // ── Qué servicio pinta qué ──────────────────────────────────

    public function test_cada_servicio_dental_sabe_que_le_hace_al_diente(): void
    {
        $this->assertSame('filling', OdontogramaClinico::condicionDeServicio('Resina (obturación)'));
        $this->assertSame('missing', OdontogramaClinico::condicionDeServicio('Extracción simple'));
        $this->assertSame('missing', OdontogramaClinico::condicionDeServicio('Extracción de tercer molar'));
        $this->assertSame('root_canal', OdontogramaClinico::condicionDeServicio('Endodoncia'));
        $this->assertSame('crown', OdontogramaClinico::condicionDeServicio('Corona dental zirconia'));
        $this->assertSame('veneer', OdontogramaClinico::condicionDeServicio('Carillas de porcelana (por pieza)'));
        $this->assertNull(OdontogramaClinico::condicionDeServicio('Limpieza dental'));
        $this->assertNull(OdontogramaClinico::condicionDeServicio('Blanqueamiento dental'));
    }

    public function test_lee_varios_dientes_de_un_mismo_renglon(): void
    {
        $this->assertSame([16, 17], OdontogramaClinico::dientes('16, 17'));
        $this->assertSame([36], OdontogramaClinico::dientes('36'));
        $this->assertSame([], OdontogramaClinico::dientes('99 y 10'));
        $this->assertSame([75], OdontogramaClinico::dientes('75'));
    }

    // ── La consulta actualiza el odontograma ────────────────────

    public function test_la_resina_de_la_consulta_cura_la_caries_del_odontograma(): void
    {
        $this->odontograma([36 => ['condition' => 'decay', 'center_surface' => 'decay', 'left_surface' => 'decay']]);
        $resina = $this->servicio('Resina (obturación)');

        $this->consultaCon([['service_id' => (string) $resina->id, 'tooth_number' => '36', 'quantity' => 1]]);

        $hoy = Odontogram::where('patient_id', $this->patient->id)->whereDate('evaluation_date', today())->first();
        $this->assertNotNull($hoy, 'la visita deja su propio odontograma');
        $diente = $hoy->teeth()->where('tooth_number', 36)->first();
        $this->assertSame('filling', $diente->condition);
        $this->assertSame('filling', $diente->center_surface);
        $this->assertSame('filling', $diente->left_surface);
    }

    public function test_la_visita_copia_lo_que_ya_traia_el_paciente(): void
    {
        $this->odontograma([18 => ['condition' => 'missing'], 26 => ['condition' => 'crown']]);
        $endo = $this->servicio('Endodoncia', 3500);

        $this->consultaCon([['service_id' => (string) $endo->id, 'tooth_number' => '46', 'quantity' => 1]]);

        $hoy = Odontogram::where('patient_id', $this->patient->id)->whereDate('evaluation_date', today())->first();
        $this->assertSame('missing', $hoy->teeth()->where('tooth_number', 18)->value('condition'));
        $this->assertSame('crown', $hoy->teeth()->where('tooth_number', 26)->value('condition'));
        $this->assertSame('root_canal', $hoy->teeth()->where('tooth_number', 46)->value('condition'));
        // El de la visita anterior se queda como estaba: es historia.
        $this->assertSame(2, Odontogram::where('patient_id', $this->patient->id)->count());
        $this->assertSame(0, OdontogramTooth::where('tooth_number', 46)->where('condition', 'root_canal')
            ->whereHas('odontogram', fn ($q) => $q->whereDate('evaluation_date', '2026-09-01'))->count());
    }

    public function test_la_extraccion_deja_el_diente_ausente(): void
    {
        $this->odontograma([48 => ['condition' => 'extraction']]);
        $ext = $this->servicio('Extracción de tercer molar', 2500);

        $this->consultaCon([['service_id' => (string) $ext->id, 'tooth_number' => '48', 'quantity' => 1]]);

        $hoy = Odontogram::where('patient_id', $this->patient->id)->whereDate('evaluation_date', today())->first();
        $this->assertSame('missing', $hoy->teeth()->where('tooth_number', 48)->value('condition'));
    }

    public function test_una_consulta_sin_procedimientos_dentales_no_crea_odontograma(): void
    {
        $limpieza = $this->servicio('Limpieza dental', 500);

        $this->consultaCon([['service_id' => (string) $limpieza->id, 'tooth_number' => '', 'quantity' => 1]]);

        $this->assertSame(0, Odontogram::where('patient_id', $this->patient->id)->count());
    }

    public function test_dos_procedimientos_el_mismo_dia_usan_el_mismo_odontograma(): void
    {
        $resina = $this->servicio('Resina (obturación)');

        $this->consultaCon([
            ['service_id' => (string) $resina->id, 'tooth_number' => '16', 'quantity' => 1],
            ['service_id' => (string) $resina->id, 'tooth_number' => '17', 'quantity' => 1],
        ]);

        $this->assertSame(1, Odontogram::where('patient_id', $this->patient->id)->count());
        $hoy = Odontogram::where('patient_id', $this->patient->id)->first();
        $this->assertSame(['16', '17'], $hoy->teeth->pluck('tooth_number')->map(fn ($n) => (string) $n)->all());
    }

    // ── Un odontograma nuevo arranca de lo que ya se sabe ───────

    public function test_un_odontograma_nuevo_arranca_con_lo_del_anterior(): void
    {
        $this->odontograma([18 => ['condition' => 'missing'], 36 => ['condition' => 'decay', 'center_surface' => 'decay']]);

        // CreateOdontogram usa una vista propia que el harness de Livewire no
        // monta (ver OdontogramSaveTest); se prueba el paso que corre al crear.
        $pagina = new CreateOdontogram();
        $pagina->record = Odontogram::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'evaluation_date' => '2026-10-01',
        ]);
        $pagina->arrancarDeLoAnterior();

        $nuevo = Odontogram::whereDate('evaluation_date', '2026-10-01')->first();
        $this->assertSame('missing', $nuevo->teeth()->where('tooth_number', 18)->value('condition'));
        $this->assertSame('decay', $nuevo->teeth()->where('tooth_number', 36)->value('center_surface'));
    }

    // ── Historial ───────────────────────────────────────────────

    public function test_dice_que_cambio_desde_la_visita_anterior(): void
    {
        $antes = $this->odontograma([36 => ['condition' => 'decay', 'center_surface' => 'decay'], 48 => ['condition' => 'extraction']], '2026-09-01');
        $ahora = $this->odontograma([36 => ['condition' => 'filling', 'center_surface' => 'filling'], 48 => ['condition' => 'missing'], 11 => ['condition' => 'crown']], '2026-10-01');

        $cambios = OdontogramaClinico::cambios($antes, $ahora);

        $this->assertContains('36: Caries → Obturación', $cambios);
        $this->assertContains('48: Extracción → Ausente', $cambios);
        $this->assertContains('11: Sano → Corona', $cambios);
        $this->assertCount(3, $cambios);
    }

    // ── Presupuesto desde lo que falta tratar ───────────────────

    public function test_lo_por_tratar_se_vuelve_presupuesto_con_precios_del_catalogo(): void
    {
        $this->servicio('Resina (obturación)', 600);
        $this->servicio('Extracción simple', 800);
        $this->servicio('Extracción de tercer molar', 2500);
        $o = $this->odontograma([
            36 => ['condition' => 'decay', 'center_surface' => 'decay', 'left_surface' => 'decay'],
            14 => ['condition' => 'extraction'],
            48 => ['condition' => 'extraction'],
            26 => ['condition' => 'filling', 'center_surface' => 'filling'],
        ]);

        $plan = OdontogramaClinico::presupuestoDesde($o);

        $this->assertSame('draft', $plan->status);
        $this->assertSame($this->patient->id, $plan->patient_id);
        $items = $plan->items()->get();
        $this->assertCount(3, $items, 'la obturación que ya tiene no se cobra');
        $this->assertEquals(600 + 800 + 2500, (float) $plan->fresh()->total);
        $this->assertSame('48', (string) $items->firstWhere('unit_price', 2500)->tooth_number, 'el tercer molar va con su precio');
        $this->assertStringContainsString('oclusal', mb_strtolower($items->firstWhere('tooth_number', '36')->description));
    }

    public function test_si_no_hay_servicio_en_el_catalogo_la_linea_queda_en_cero_para_que_la_llene(): void
    {
        $o = $this->odontograma([21 => ['condition' => 'fracture']]);

        $plan = OdontogramaClinico::presupuestoDesde($o);

        $item = $plan->items()->first();
        $this->assertSame('21', (string) $item->tooth_number);
        $this->assertEquals(0, (float) $item->unit_price);
        $this->assertStringContainsString('Fractura', $item->description);
    }

    // ── La cita ya trae lo que se va a hacer ────────────────────

    private function citaDe(Service $servicio): \App\Models\Appointment
    {
        return \App\Models\Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'service_id' => $servicio->id,
            'starts_at' => now(),
            'ends_at' => now()->addHour(),
            'status' => 'confirmed',
        ]);
    }

    private function iniciar(\App\Models\Appointment $cita)
    {
        return Livewire::withQueryParams(['appointment' => $cita->id])->test(Consultation::class);
    }

    public function test_la_cita_de_extraccion_abre_la_consulta_con_el_diente_indicado(): void
    {
        $ext = $this->servicio('Extracción de tercer molar', 2500);
        $this->odontograma([48 => ['condition' => 'extraction'], 14 => ['condition' => 'extraction']]);

        $this->iniciar($this->citaDe($ext))
            ->assertSet('procedures', [
                ['service_id' => (string) $ext->id, 'tooth_number' => '48', 'quantity' => 1],
            ]);
    }

    public function test_la_cita_de_resina_propone_cada_diente_con_caries(): void
    {
        $resina = $this->servicio('Resina (obturación)', 600);
        $this->odontograma([
            16 => ['condition' => 'decay', 'right_surface' => 'decay'],
            36 => ['condition' => 'decay', 'center_surface' => 'decay'],
            26 => ['condition' => 'filling', 'center_surface' => 'filling'],
        ]);

        $this->iniciar($this->citaDe($resina))
            ->assertSet('procedures', [
                ['service_id' => (string) $resina->id, 'tooth_number' => '16', 'quantity' => 1],
                ['service_id' => (string) $resina->id, 'tooth_number' => '36', 'quantity' => 1],
            ])
            ->assertSet('payment_amount', '1200');
    }

    public function test_si_el_odontograma_no_dice_que_diente_el_renglon_queda_para_llenarlo(): void
    {
        $endo = $this->servicio('Endodoncia', 3500);

        $this->iniciar($this->citaDe($endo))
            ->assertSet('procedures', [
                ['service_id' => (string) $endo->id, 'tooth_number' => '', 'quantity' => 1],
            ]);
    }

    public function test_una_cita_de_consulta_general_no_propone_procedimientos(): void
    {
        $general = $this->servicio('Consulta general', 300);

        $this->iniciar($this->citaDe($general))->assertSet('procedures', []);
    }

    // ── En pantalla ─────────────────────────────────────────────

    private function conPresupuestos(): void
    {
        $this->clinic->update(['plan' => 'basico', 'plan_ends_at' => now()->addYear()]);
        \App\Models\ClinicAddon::create([
            'clinic_id' => $this->clinic->id,
            'addon_slug' => 'treatment_plans',
            'status' => 'active',
            'monthly_price' => 129,
            'started_at' => now(),
        ]);
    }

    private function paginaDeEdicion(Odontogram $o): \App\Filament\Doctor\Resources\OdontogramResource\Pages\EditOdontogram
    {
        $pagina = new \App\Filament\Doctor\Resources\OdontogramResource\Pages\EditOdontogram();
        $pagina->record = $o;
        $pagina->teethData = [];

        return $pagina;
    }

    public function test_desde_el_odontograma_se_arma_el_presupuesto_si_tiene_el_add_on(): void
    {
        $this->conPresupuestos();
        $this->servicio('Resina (obturación)', 600);
        $o = $this->odontograma([36 => ['condition' => 'decay', 'center_surface' => 'decay']]);

        $plan = $this->paginaDeEdicion($o)->armarPresupuesto();

        $this->assertNotNull($plan);
        $this->assertEquals(600, (float) $plan->fresh()->total);
    }

    public function test_en_el_free_sin_prueba_no_arma_presupuesto(): void
    {
        // Desde el 2-oct-2026 los presupuestos vienen en todos los planes de
        // pago; solo el Free (ya sin prueba) se queda sin ellos.
        $this->clinic->update(['plan' => 'free', 'plan_ends_at' => null, 'trial_ends_at' => now()->subDay()]);
        $o = $this->odontograma([36 => ['condition' => 'decay', 'center_surface' => 'decay']]);

        $this->assertNull($this->paginaDeEdicion($o)->armarPresupuesto());
        $this->assertSame(0, \App\Models\TreatmentPlan::count());
    }

    public function test_el_presupuesto_toma_lo_que_el_doctor_marco_aunque_no_haya_guardado(): void
    {
        $this->conPresupuestos();
        $this->servicio('Extracción simple', 800);
        $o = $this->odontograma([]);
        $pagina = $this->paginaDeEdicion($o);
        $pagina->teethData = [14 => ['condition' => 'extraction', 'notes' => '', 'surfaces' => []]];

        $plan = $pagina->armarPresupuesto();

        $this->assertEquals(800, (float) $plan->fresh()->total);
    }

    public function test_el_odontograma_se_imprime_con_los_datos_del_paciente(): void
    {
        $o = $this->odontograma([36 => ['condition' => 'decay', 'center_surface' => 'decay']]);

        $this->get(route('odontograma.imprimir', $o))
            ->assertOk()
            ->assertSee('Ana Ruiz')
            ->assertSee('Consultorio Test')
            ->assertSee('data-cara="36-oclusal"', false)
            ->assertSee('Por tratar');
    }

    public function test_no_se_imprime_el_odontograma_de_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'onboarding_status' => 'completed']);
        $ajeno = Odontogram::create([
            'clinic_id' => $otra->id,
            'patient_id' => Patient::create(['clinic_id' => $otra->id, 'first_name' => 'X', 'last_name' => 'Y'])->id,
            'doctor_id' => $this->doctor->id,
            'evaluation_date' => '2026-09-01',
        ]);

        $this->get(route('odontograma.imprimir', $ajeno->id))->assertNotFound();
    }

    public function test_en_la_consulta_se_agrega_lo_por_tratar_con_un_clic(): void
    {
        $resina = $this->servicio('Resina (obturación)', 600);
        $general = $this->servicio('Consulta general', 300);
        $this->odontograma([36 => ['condition' => 'decay', 'center_surface' => 'decay']]);

        Livewire::test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $general->id)
            ->call('startWalkIn')
            ->set('currentStep', 4)
            ->assertSee('Del odontograma')
            ->call('agregarDesdeOdontograma', 36, 'decay')
            ->assertSet('procedures', [
                ['service_id' => (string) $resina->id, 'tooth_number' => '36', 'quantity' => 1],
            ])
            ->assertSet('payment_amount', '600');
    }

    public function test_el_perfil_dice_que_cambio_desde_la_visita_anterior(): void
    {
        $this->odontograma([36 => ['condition' => 'decay', 'center_surface' => 'decay']], '2026-09-01');
        $this->odontograma([36 => ['condition' => 'filling', 'center_surface' => 'filling']], '2026-10-01');

        Livewire::withQueryParams(['patient' => $this->patient->id])
            ->test(\App\Filament\Doctor\Pages\PatientProfile::class)
            ->call('setTab', 'odontogram')
            ->assertSee('Cambios desde el 01/09/2026')
            ->assertSee('36: Caries');
    }
}
