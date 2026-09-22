<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Pages\Onboarding;
use App\Models\Clinic;
use App\Models\ConsultationProcedure;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Support\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Los procedimientos realizados en la consulta.
 *
 * Es el prerrequisito del motor de insumos, y por sí solo arregla un bug: hasta
 * antes, "Curetaje (por cuadrante)" a $800 se cobraba UNA vez aunque el doctor
 * hiciera dos cuadrantes, porque el precio no decía de qué era.
 */
class ProcedimientosDeConsultaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Clinic $clinic;
    private Doctor $doctor;
    private Patient $patient;
    private Service $service;

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
        $this->doctor = Doctor::create([
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
        $this->service = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Consulta General',
            'price' => 500,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
    }

    private function curetaje(): Service
    {
        return Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Curetaje (por cuadrante)',
            'price' => 800,
            'unit' => WorkUnit::QUADRANT,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);
    }

    /** Una consulta abierta por walk-in, que es como la montan los tests. */
    private function consulta(): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::actingAs($this->user)
            ->test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $this->service->id)
            ->call('startWalkIn');
    }

    // ── El vocabulario ───────────────────────────────────────────

    public function test_una_zona_nerviosa_no_se_ofrece_como_unidad_de_cobro(): void
    {
        // La anestesia se gasta por zona contigua, pero un servicio no se cobra
        // así. Ofrecerla en el catálogo solo invitaría a elegirla mal.
        $this->assertArrayNotHasKey(WorkUnit::CONTIGUOUS_ZONE, WorkUnit::billingLabels());
        $this->assertCount(3, WorkUnit::billingLabels());
    }

    public function test_una_unidad_inventada_no_es_valida(): void
    {
        $this->assertFalse(WorkUnit::isValid('por_diente_y_media'));
        $this->assertTrue(WorkUnit::isValid(WorkUnit::TOOTH));
    }

    public function test_un_servicio_sin_unidad_definida_se_cobra_por_visita(): void
    {
        // El default importa: los servicios que ya existen se comportaban así
        // (una línea, un cobro) y no deben cambiar de significado.
        $this->assertSame(WorkUnit::VISIT, $this->service->fresh()->unit);
        $this->assertFalse($this->service->fresh()->isBilledPerUnit());
    }

    // ── El bug que esto arregla ──────────────────────────────────

    public function test_un_curetaje_de_dos_cuadrantes_se_cobra_dos_veces(): void
    {
        $curetaje = $this->curetaje();

        $this->consulta()
            ->set('procedures', [
                ['service_id' => (string) $curetaje->id, 'tooth_number' => '', 'quantity' => 2],
            ])
            ->assertSet('payment_amount', '1600');
    }

    public function test_el_cobro_sigue_al_total_de_los_procedimientos(): void
    {
        $curetaje = $this->curetaje();

        $this->consulta()
            ->set('procedures', [
                ['service_id' => (string) $curetaje->id, 'tooth_number' => '16', 'quantity' => 2],
                ['service_id' => (string) $this->service->id, 'tooth_number' => '26', 'quantity' => 1],
            ])
            // 800 × 2 + 500 × 1
            ->assertSet('payment_amount', '2100');
    }

    public function test_quitar_un_procedimiento_vuelve_a_calcular_el_cobro(): void
    {
        $curetaje = $this->curetaje();

        $this->consulta()
            ->set('procedures', [
                ['service_id' => (string) $curetaje->id, 'tooth_number' => '', 'quantity' => 2],
            ])
            ->call('removeProcedure', 0)
            ->set('procedures', [
                ['service_id' => (string) $this->service->id, 'tooth_number' => '', 'quantity' => 1],
            ])
            ->assertSet('payment_amount', '500');
    }

    public function test_agregar_procedimiento_abre_un_renglon_vacio(): void
    {
        $this->consulta()
            ->call('addProcedure')
            ->assertSet('procedures', [
                ['service_id' => '', 'tooth_number' => '', 'quantity' => 1],
            ]);
    }

    public function test_la_cantidad_se_pregunta_segun_la_unidad_del_servicio(): void
    {
        $curetaje = $this->curetaje();

        $prueba = $this->consulta();

        $this->assertSame('¿Cuántos cuadrantes?', $prueba->instance()->questionFor((string) $curetaje->id));
        $this->assertSame('¿Cuántas visitas?', $prueba->instance()->questionFor((string) $this->service->id));
    }

    // ── Al cerrar, se vuelven filas ──────────────────────────────

    public function test_al_cerrar_la_consulta_los_procedimientos_se_guardan(): void
    {
        $curetaje = $this->curetaje();

        $this->consulta()
            ->set('procedures', [
                ['service_id' => (string) $curetaje->id, 'tooth_number' => '16', 'quantity' => 2],
            ])
            ->call('saveAndComplete');

        $guardados = ConsultationProcedure::where('service_id', $curetaje->id)->get();

        $this->assertCount(1, $guardados);
        $this->assertSame('16', $guardados->first()->tooth_number);
        $this->assertSame(2, $guardados->first()->quantity);
        $this->assertSame(1600.0, $guardados->first()->total());
    }

    public function test_el_precio_y_la_unidad_quedan_congelados(): void
    {
        $curetaje = $this->curetaje();

        $this->consulta()
            ->set('procedures', [
                ['service_id' => (string) $curetaje->id, 'tooth_number' => '36', 'quantity' => 1],
            ])
            ->call('saveAndComplete');

        // El doctor sube el precio después. Lo que se cobró ayer no se mueve.
        $curetaje->update(['price' => 3500, 'unit' => WorkUnit::VISIT]);

        $guardado = ConsultationProcedure::where('service_id', $curetaje->id)->firstOrFail();

        $this->assertSame('800.00', (string) $guardado->unit_price);
        $this->assertSame(WorkUnit::QUADRANT, $guardado->unit);
        $this->assertSame(800.0, $guardado->total());
    }

    public function test_cerrar_dos_veces_no_duplica_los_procedimientos(): void
    {
        $curetaje = $this->curetaje();

        $prueba = $this->consulta()
            ->set('procedures', [
                ['service_id' => (string) $curetaje->id, 'tooth_number' => '16', 'quantity' => 3],
            ])
            ->call('saveAndComplete');

        $this->assertSame(1, ConsultationProcedure::count());

        // Si algo reintenta el cierre, se reescribe en vez de acumular.
        $prueba->call('saveAndComplete');

        $this->assertSame(1, ConsultationProcedure::count());
        $this->assertSame(3, ConsultationProcedure::first()->quantity);
    }

    public function test_un_renglon_sin_servicio_no_se_guarda(): void
    {
        $this->consulta()
            ->set('procedures', [
                ['service_id' => '', 'tooth_number' => '16', 'quantity' => 1],
            ])
            ->call('saveAndComplete');

        $this->assertSame(0, ConsultationProcedure::count());
    }

    public function test_los_procedimientos_quedan_aislados_por_consultorio(): void
    {
        $curetaje = $this->curetaje();

        $this->consulta()
            ->set('procedures', [
                ['service_id' => (string) $curetaje->id, 'tooth_number' => '16', 'quantity' => 1],
            ])
            ->call('saveAndComplete');

        $otra = Clinic::create(['name' => 'Otro', 'slug' => 'otro-' . uniqid(), 'onboarding_status' => 'completed']);

        $ajeno = Service::create([
            'clinic_id' => $otra->id,
            'name' => 'Ajeno',
            'price' => 100,
            'is_active' => true,
        ]);

        ConsultationProcedure::create([
            'clinic_id' => $otra->id,
            'appointment_id' => \App\Models\Appointment::create([
                'clinic_id' => $otra->id,
                'doctor_id' => $this->doctor->id,
                'patient_id' => $this->patient->id,
                'service_id' => $ajeno->id,
                'starts_at' => now(),
                'ends_at' => now()->addMinutes(30),
                'status' => 'scheduled',
            ])->id,
            'service_id' => $ajeno->id,
            'quantity' => 1,
            'unit' => WorkUnit::VISIT,
            'unit_price' => 100,
        ]);

        $this->actingAs($this->user);

        $this->assertSame(1, ConsultationProcedure::count());
        $this->assertSame($curetaje->id, ConsultationProcedure::first()->service_id);
    }

    // ── El onboarding ya trae el curetaje bien ───────────────────

    public function test_el_curetaje_sugerido_ya_viene_por_cuadrante(): void
    {
        $sugerencias = (new \ReflectionClass(Onboarding::class))->getConstant('SERVICE_SUGGESTIONS');
        $curetaje = collect($sugerencias['periodoncia'])->firstWhere('name', 'Curetaje (por cuadrante)');

        $this->assertSame(WorkUnit::QUADRANT, $curetaje['unit']);
    }

    public function test_el_onboarding_crea_el_servicio_con_su_unidad(): void
    {
        // En curso: con el onboarding ya cerrado, mount() redirige al
        // dashboard y el wizard no llega a construir nada.
        $this->clinic->update(['onboarding_status' => 'pending']);

        Livewire::actingAs($this->user)
            ->test(Onboarding::class)
            ->set('clinic_name', 'Consultorio Test')
            ->set('quick_services', [
                ['name' => 'Curetaje (por cuadrante)', 'price' => 2500, 'duration' => 45, 'unit' => WorkUnit::QUADRANT],
                ['name' => 'Limpieza dental', 'price' => 400, 'duration' => 30],
            ])
            ->call('completeOnboarding');

        $this->assertSame(WorkUnit::QUADRANT, Service::where('name', 'Curetaje (por cuadrante)')->firstOrFail()->unit);
        // Sin unidad en la sugerencia, cae al default y no revienta.
        $this->assertSame(WorkUnit::VISIT, Service::where('name', 'Limpieza dental')->firstOrFail()->unit);
    }
}
