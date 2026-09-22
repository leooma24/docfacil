<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ConsultationProcedure;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceSupply;
use App\Models\Supply;
use App\Models\User;
use App\Support\SupplyProposal;
use App\Support\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La propuesta de insumos de una consulta.
 *
 * Las dos trampas que se prueban aquí son las que harían que el inventario
 * mienta: contar la contigüidad por renglón en vez de por servicio, y contar
 * los insumos de visita una vez por servicio en vez de una vez por consulta.
 */
class SupplyProposalTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Appointment $cita;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-' . uniqid(),
            'onboarding_status' => 'completed',
        ]);

        $user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $this->clinica->id,
            'specialty' => 'General',
        ]);

        $paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'phone' => '5551234567',
        ]);

        $this->cita = Appointment::create([
            'clinic_id' => $this->clinica->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $paciente->id,
            'starts_at' => now(),
            'ends_at' => now()->addHour(),
            'status' => 'in_progress',
        ]);

        $this->actingAs($user);
    }

    private function servicio(string $nombre, string $unidad): Service
    {
        return Service::create([
            'clinic_id' => $this->clinica->id,
            'name' => $nombre,
            'price' => 1000,
            'unit' => $unidad,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
    }

    private function insumo(string $nombre, ?string $categoria = null): Supply
    {
        return Supply::create([
            'clinic_id' => $this->clinica->id,
            'name' => $nombre,
            'category' => $categoria,
            'unit' => 'pieza',
        ]);
    }

    private function linea(Service $servicio, Supply $insumo, float $cantidad, ?string $scope = null, float $merma = 1, bool $opcional = false): ServiceSupply
    {
        return ServiceSupply::create([
            'clinic_id' => $this->clinica->id,
            'service_id' => $servicio->id,
            'supply_id' => $insumo->id,
            'quantity' => $cantidad,
            'scope' => $scope,
            'waste_factor' => $merma,
            'is_optional' => $opcional,
        ]);
    }

    private function procedimiento(Service $servicio, ?string $diente = null, int $cantidad = 1): ConsultationProcedure
    {
        return ConsultationProcedure::create([
            'clinic_id' => $this->clinica->id,
            'appointment_id' => $this->cita->id,
            'service_id' => $servicio->id,
            'tooth_number' => $diente,
            'quantity' => $cantidad,
            'unit' => $servicio->unit,
            'unit_price' => $servicio->price,
        ]);
    }

    private function propuesta(): array
    {
        return collect(SupplyProposal::for($this->cita->procedures()->with('service')->get()))
            ->keyBy(fn (array $linea) => $linea['supply']->name)
            ->all();
    }

    // ── Lo básico ────────────────────────────────────────────────

    public function test_sin_procedimientos_no_hay_propuesta(): void
    {
        $this->assertSame([], SupplyProposal::for(collect()));
    }

    public function test_un_servicio_sin_receta_no_aporta_nada(): void
    {
        $limpieza = $this->servicio('Limpieza', WorkUnit::VISIT);
        $this->procedimiento($limpieza, '16');

        $this->assertSame([], $this->propuesta());
    }

    public function test_el_composite_escala_con_los_dientes(): void
    {
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $this->linea($resina, $this->insumo('Composite', 'Restaurador'), 0.5);

        foreach (['16', '15', '14'] as $diente) {
            $this->procedimiento($resina, $diente);
        }

        $this->assertSame(1.5, $this->propuesta()['Composite']['quantity']);
        $this->assertSame('Resina: 3 dientes', $this->propuesta()['Composite']['detail']);
    }

    public function test_la_punta_es_una_aunque_sean_siete_dientes(): void
    {
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $this->linea($resina, $this->insumo('Punta', 'Desechable'), 1, WorkUnit::VISIT);

        foreach (['16', '15', '14', '13', '12', '11', '21'] as $diente) {
            $this->procedimiento($resina, $diente);
        }

        $this->assertSame(1.0, $this->propuesta()['Punta']['quantity']);
        // Y va palomeada: los guantes, las puntas y el babero siempre se usan.
        $this->assertFalse($this->propuesta()['Punta']['optional']);
    }

    public function test_la_merma_se_aplica_a_la_cantidad(): void
    {
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $this->linea($resina, $this->insumo('Composite'), 1, null, 1.15);

        $this->procedimiento($resina, '16');
        $this->procedimiento($resina, '15');

        // 2 dientes × 1 × 1.15
        $this->assertSame(2.3, $this->propuesta()['Composite']['quantity']);
    }

    // ── Trampa 1: la contigüidad cruza los renglones ─────────────

    public function test_la_contiguidad_cruza_los_renglones(): void
    {
        // Resina en el 16 y resina en el 15 son DOS procedimientos, pero los
        // dientes están pegados: es UNA zona de anestesia. Calculados renglón
        // por renglón darían dos, y el inventario se desviaría en cada consulta
        // con más de un diente.
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $anestesia = $this->insumo('Anestésico', 'Anestesia');
        $this->linea($resina, $anestesia, 1, WorkUnit::CONTIGUOUS_ZONE);

        $this->procedimiento($resina, '16');
        $this->procedimiento($resina, '15');

        $this->assertSame(1.0, $this->propuesta()['Anestésico']['quantity']);
        $this->assertSame('Resina: 1 zona contigua', $this->propuesta()['Anestésico']['detail']);
    }

    public function test_los_dientes_separados_siguen_siendo_dos_zonas(): void
    {
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $this->linea($resina, $this->insumo('Anestésico', 'Anestesia'), 1, WorkUnit::CONTIGUOUS_ZONE);

        $this->procedimiento($resina, '16');
        $this->procedimiento($resina, '14');

        $this->assertSame(2.0, $this->propuesta()['Anestésico']['quantity']);
    }

    public function test_la_regla_por_arcada_tambien_aplica_en_la_propuesta(): void
    {
        $curetaje = $this->servicio('Curetaje', WorkUnit::QUADRANT);
        $this->linea($curetaje, $this->insumo('Anestésico', 'Anestesia'), 1, WorkUnit::CONTIGUOUS_ZONE);

        // Abajo y separados: un solo bloqueo por cuadrante.
        $this->procedimiento($curetaje, '44');
        $this->procedimiento($curetaje, '47');

        $this->assertSame(1.0, $this->propuesta()['Anestésico']['quantity']);
    }

    // ── Trampa 2: los insumos de visita ──────────────────────────

    public function test_los_guantes_no_se_cuentan_dos_veces_entre_dos_servicios(): void
    {
        // La consulta pasa UNA vez. Si la resina pide 2 guantes por visita y el
        // curetaje también, la consulta gastó 2, no 4.
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $curetaje = $this->servicio('Curetaje', WorkUnit::QUADRANT);

        $guantes = $this->insumo('Guantes', 'Protección');
        $this->linea($resina, $guantes, 2, WorkUnit::VISIT);
        $this->linea($curetaje, $guantes, 2, WorkUnit::VISIT);

        $this->procedimiento($resina, '16');
        $this->procedimiento($curetaje, '46');

        $this->assertSame(2.0, $this->propuesta()['Guantes']['quantity']);
    }

    public function test_entre_dos_servicios_gana_la_cantidad_mayor_de_visita(): void
    {
        // Una cirugía pide más guantes que una resina. Si se hacen las dos en
        // la misma consulta, se usan los de la cirugía — no la suma.
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $cirugia = $this->servicio('Cirugía', WorkUnit::VISIT);

        $guantes = $this->insumo('Guantes', 'Protección');
        $this->linea($resina, $guantes, 2, WorkUnit::VISIT);
        $this->linea($cirugia, $guantes, 6, WorkUnit::VISIT);

        $this->procedimiento($resina, '16');
        $this->procedimiento($cirugia, '46');

        $this->assertSame(6.0, $this->propuesta()['Guantes']['quantity']);
    }

    // ── Sumar entre servicios, cuando sí se debe ─────────────────

    public function test_dos_servicios_suman_sus_insumos_por_diente(): void
    {
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $endodoncia = $this->servicio('Endodoncia', WorkUnit::TOOTH);

        $composite = $this->insumo('Composite', 'Restaurador');
        $this->linea($resina, $composite, 0.5);
        $this->linea($endodoncia, $composite, 1);

        $this->procedimiento($resina, '16');
        $this->procedimiento($endodoncia, '26');

        // 0.5 × 1 diente + 1 × 1 diente
        $this->assertSame(1.5, $this->propuesta()['Composite']['quantity']);
    }

    public function test_el_detalle_dice_de_donde_salio_cada_cantidad(): void
    {
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $this->linea($resina, $this->insumo('Composite'), 0.5);

        $this->procedimiento($resina, '16');
        $this->procedimiento($resina, '26');

        // Dos cuadrantes tocados, pero el alcance es por diente.
        $this->assertSame('Resina: 2 dientes', $this->propuesta()['Composite']['detail']);
    }

    public function test_una_linea_opcional_queda_marcada_como_opcional(): void
    {
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $this->linea($resina, $this->insumo('Banda de matriz'), 1, null, 1, true);
        $this->linea($resina, $this->insumo('Composite'), 0.5);

        $this->procedimiento($resina, '16');

        $propuesta = $this->propuesta();

        $this->assertTrue($propuesta['Banda de matriz']['optional']);
        $this->assertFalse($propuesta['Composite']['optional']);
    }

    public function test_los_insumos_sin_receta_no_aparecen(): void
    {
        // Un insumo del catálogo que no está en ninguna receta no se propone:
        // no hay de dónde saber que se usó.
        $this->insumo('Sutura', 'Instrumental');

        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $this->linea($resina, $this->insumo('Composite'), 0.5);
        $this->procedimiento($resina, '16');

        $this->assertCount(1, $this->propuesta());
        $this->assertArrayNotHasKey('Sutura', $this->propuesta());
    }

    public function test_el_mismo_diente_en_dos_renglones_no_cuenta_doble(): void
    {
        // El doctor pudo capturar el 16 dos veces. Un diente se trabaja una vez.
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $this->linea($resina, $this->insumo('Composite'), 0.5);

        $this->procedimiento($resina, '16');
        $this->procedimiento($resina, '16');

        $this->assertSame(0.5, $this->propuesta()['Composite']['quantity']);
    }
}
