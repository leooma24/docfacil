<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ConsultationProcedure;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceSupply;
use App\Models\Supply;
use App\Models\SupplyMovement;
use App\Models\User;
use App\Support\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El descuento de insumos al cerrar la consulta.
 *
 * Es el final del camino: el sistema propone a partir de los dientes, el doctor
 * confirma, y el kardex se mueve. Lo que se prueba aquí es que no se descuente
 * dos veces y que el ajuste del doctor manda.
 */
class DescuentoDeInsumosTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Clinic $clinic;
    private Patient $patient;
    private Service $consulta;
    private Service $resina;
    private Supply $composite;
    private Supply $punta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create(['name' => 'Test Clinic', 'onboarding_status' => 'completed']);

        $this->user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinic->id,
        ]);

        $doctor = Doctor::create([
            'user_id' => $this->user->id,
            'clinic_id' => $this->clinic->id,
            'specialty' => 'General',
        ]);

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'phone' => '5551234567',
        ]);

        $this->consulta = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Consulta general',
            'price' => 500,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $this->resina = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Resina (obturación)',
            'price' => 600,
            'unit' => WorkUnit::TOOTH,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        $this->composite = Supply::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Composite A2',
            'category' => 'Restaurador',
            'unit' => 'jeringa',
        ]);

        $this->punta = Supply::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Punta de aplicación',
            'category' => 'Desechable',
            'unit' => 'pieza',
        ]);

        ServiceSupply::create([
            'clinic_id' => $this->clinic->id,
            'service_id' => $this->resina->id,
            'supply_id' => $this->composite->id,
            'quantity' => 0.5,
        ]);

        ServiceSupply::create([
            'clinic_id' => $this->clinic->id,
            'service_id' => $this->resina->id,
            'supply_id' => $this->punta->id,
            'quantity' => 1,
            'scope' => WorkUnit::VISIT,
        ]);

        // Existencia inicial, para poder ver bajar el stock.
        $this->composite->register('in', 10, ['reason' => 'Compra inicial']);
        $this->punta->register('in', 50, ['reason' => 'Compra inicial']);
    }

    private function consulta(): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->consulta->id)
            ->call('startWalkIn');
    }

    /** @return array<int, array<string, mixed>> */
    private function procedimientos(string ...$dientes): array
    {
        return collect($dientes)
            ->map(fn (string $diente) => [
                'service_id' => (string) $this->resina->id,
                'tooth_number' => $diente,
                'quantity' => 1,
            ])
            ->all();
    }

    // ── La propuesta se arma sola ────────────────────────────────

    public function test_la_propuesta_se_calcula_al_capturar_los_procedimientos(): void
    {
        $prueba = $this->consulta()->set('procedures', $this->procedimientos('16', '15'));

        $insumos = collect($prueba->get('supplies'))->keyBy('name');

        // 0.5 por diente × 2 dientes
        $this->assertSame(1.0, $insumos['Composite A2']['quantity']);
        // Una por paciente, no dos.
        $this->assertSame(1.0, $insumos['Punta de aplicación']['quantity']);
        $this->assertSame('Resina (obturación): 2 dientes', $insumos['Composite A2']['detail']);
    }

    public function test_un_servicio_sin_receta_no_propone_nada(): void
    {
        $prueba = $this->consulta()->set('procedures', [
            ['service_id' => (string) $this->consulta->id, 'tooth_number' => '', 'quantity' => 1],
        ]);

        $this->assertSame([], $prueba->get('supplies'));
    }

    public function test_el_ajuste_del_doctor_sobrevive_al_recalculo(): void
    {
        $prueba = $this->consulta()->set('procedures', $this->procedimientos('16'));

        $prueba->set('supplies.0.quantity', 2.0);
        $prueba->call('refreshProposal');

        // La cuenta no cambió, así que se respeta lo que él puso.
        $this->assertSame(2.0, (float) $prueba->get('supplies')[0]['quantity']);
    }

    public function test_la_cuenta_nueva_le_gana_al_ajuste_viejo(): void
    {
        $prueba = $this->consulta()->set('procedures', $this->procedimientos('16'));

        $prueba->set('supplies.0.quantity', 3.0);

        // Ahora captura otro diente: la cuenta cambia, y con ella la cantidad.
        $prueba->set('procedures', $this->procedimientos('16', '15'));

        $this->assertSame(1.0, $prueba->get('supplies')[0]['quantity']);
    }

    // ── El descuento ─────────────────────────────────────────────

    public function test_al_cerrar_la_consulta_los_insumos_bajan_del_inventario(): void
    {
        $this->consulta()
            ->set('procedures', $this->procedimientos('16', '15'))
            ->call('saveAndComplete');

        // 10 - 1.0 composite; 50 - 1 punta.
        $this->assertSame(9.0, $this->composite->fresh()->currentStock());
        $this->assertSame(49.0, $this->punta->fresh()->currentStock());
    }

    public function test_los_movimientos_apuntan_a_la_consulta(): void
    {
        $this->consulta()
            ->set('procedures', $this->procedimientos('16'))
            ->call('saveAndComplete');

        $salida = SupplyMovement::where('type', 'out')->firstOrFail();

        $this->assertSame(Appointment::class, $salida->reference_type);
        $this->assertNotNull($salida->reference_id);
        $this->assertStringContainsString('Consulta del', $salida->reason);
        $this->assertSame($this->clinic->id, $salida->clinic_id);
    }

    public function test_desmarcar_un_insumo_no_lo_descuenta(): void
    {
        $this->consulta()
            ->set('procedures', $this->procedimientos('16'))
            ->set('supplies.1.include', false)
            ->call('saveAndComplete');

        // El composite (índice 0) sí baja; la punta no.
        $this->assertSame(9.5, $this->composite->fresh()->currentStock());
        $this->assertSame(50.0, $this->punta->fresh()->currentStock());
        $this->assertSame(1, SupplyMovement::where('type', 'out')->count());
    }

    public function test_descontar_lo_que_el_doctor_ajusto(): void
    {
        $this->consulta()
            ->set('procedures', $this->procedimientos('16'))
            ->set('supplies.0.quantity', 0.25)
            ->call('saveAndComplete');

        $this->assertSame(9.75, $this->composite->fresh()->currentStock());
    }

    public function test_cerrar_dos_veces_no_descuenta_doble(): void
    {
        // Es la guardia que importa: si algo reintenta el cierre, el inventario
        // no se puede desviar. Se borran los movimientos de la consulta y se
        // reescriben.
        $prueba = $this->consulta()
            ->set('procedures', $this->procedimientos('16', '15'))
            ->call('saveAndComplete');

        $this->assertSame(9.0, $this->composite->fresh()->currentStock());

        $prueba->call('saveAndComplete');

        $this->assertSame(9.0, $this->composite->fresh()->currentStock());
        $this->assertSame(1, SupplyMovement::where('type', 'out')->where('supply_id', $this->composite->id)->count());
    }

    public function test_sin_procedimientos_no_se_descuenta_nada(): void
    {
        $this->consulta()->call('saveAndComplete');

        $this->assertSame(10.0, $this->composite->fresh()->currentStock());
        $this->assertSame(0, SupplyMovement::where('type', 'out')->count());
    }

    public function test_al_cerrar_quedan_las_filas_y_el_borrador_se_limpia(): void
    {
        $this->consulta()
            ->set('procedures', $this->procedimientos('16', '15', '14'))
            ->call('saveAndComplete');

        // Los procedimientos quedan como filas de verdad.
        $this->assertSame(3, ConsultationProcedure::count());

        // Y el borrador se limpia: ya no hace falta, porque el registro
        // definitivo son las filas y el expediente.
        $cita = Appointment::withoutGlobalScopes()->latest('id')->firstOrFail();

        $this->assertNull($cita->consultation_data);
        $this->assertSame('completed', $cita->status);
    }
}
