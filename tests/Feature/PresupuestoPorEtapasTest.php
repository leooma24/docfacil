<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\TreatmentPlanResource\Pages\CreateTreatmentPlan;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Día 4 del plan (10-oct-2026): presupuesto por etapas.
 *
 * Lo que vimos en Dentalink (secciones, varios dientes, tratamiento
 * alternativo, tres niveles de detalle al mandarlo, firma) y en DaleControl
 * (presupuesto por etapas firmado desde el celular). Antes en DocFácil el
 * presupuesto era una lista plana, el descuento solo en pesos, un diente por
 * renglón (10 caracteres) y el paciente aceptaba con un botón, sin firma.
 */
class PresupuestoPorEtapasTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $ana;

    /** Una firma de 1x1 px: lo que manda el lienzo del celular. */
    private const FIRMA = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function plan(array $datos = []): TreatmentPlan
    {
        $plan = TreatmentPlan::create(array_merge(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Rehabilitación', 'status' => 'sent', 'public_token' => str_repeat('b', 64)], $datos));
        $plan->items()->create(['description' => 'Extracción', 'tooth_number' => '48', 'quantity' => 1, 'unit_price' => 1500, 'etapa' => '1. Urgencia', 'sort_order' => 0]);
        $plan->items()->create(['description' => 'Resinas', 'tooth_number' => '11, 12, 21, 22', 'quantity' => 4, 'unit_price' => 800, 'etapa' => '2. Rehabilitación', 'sort_order' => 1]);
        $plan->items()->create(['description' => 'Endodoncia', 'tooth_number' => '36', 'quantity' => 1, 'unit_price' => 4000, 'etapa' => '1. Urgencia', 'sort_order' => 2]);
        $plan->items()->create(['description' => 'Carillas de porcelana', 'quantity' => 4, 'unit_price' => 5000, 'etapa' => '2. Rehabilitación', 'opcional' => true, 'sort_order' => 3]);
        $plan->recalculateTotal();

        return $plan->fresh();
    }

    // ── El total ────────────────────────────────────────────────

    public function test_lo_opcional_no_suma_y_el_descuento_puede_ser_en_porcentaje(): void
    {
        $plan = $this->plan(['descuento_porcentaje' => 10]);

        $this->assertSame(8700.0, (float) $plan->subtotal, '1,500 + 3,200 + 4,000; las carillas son opcionales');
        $this->assertSame(870.0, (float) $plan->discount);
        $this->assertSame(7830.0, (float) $plan->total);
    }

    public function test_agrupa_por_etapa_en_el_orden_en_que_aparecen(): void
    {
        $etapas = $this->plan()->porEtapas();

        $this->assertSame(['1. Urgencia', '2. Rehabilitación'], $etapas->keys()->all());
        $this->assertSame(['Extracción', 'Endodoncia'], $etapas['1. Urgencia']->pluck('description')->all());
        $this->assertSame(5500.0, $etapas['1. Urgencia']->where('opcional', false)->sum('subtotal'));
    }

    // ── El formulario ───────────────────────────────────────────

    public function test_desde_el_formulario_se_arma_por_etapas_con_varios_dientes_y_opcional(): void
    {
        $undo = Repeater::fake();

        Livewire::test(CreateTreatmentPlan::class)
            ->fillForm([
                'patient_id' => $this->ana->id,
                'doctor_id' => $this->doctor->id,
                'title' => 'Plan de Ana',
                'status' => 'draft',
                'descuento_porcentaje' => 5,
                'detalle_al_enviar' => 'solo_total',
                'items' => [
                    ['etapa' => 'Urgencia', 'description' => 'Resinas', 'tooth_number' => '11, 12, 21, 22', 'quantity' => 4, 'unit_price' => 800, 'opcional' => false],
                    ['etapa' => 'Estética', 'description' => 'Blanqueamiento', 'tooth_number' => null, 'quantity' => 1, 'unit_price' => 3000, 'opcional' => true],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();
        $undo();

        $plan = TreatmentPlan::where('title', 'Plan de Ana')->sole();
        $this->assertSame(3200.0, (float) $plan->subtotal);
        $this->assertSame(160.0, (float) $plan->discount);
        $this->assertSame('solo_total', $plan->detalle_al_enviar);
        $this->assertSame('11, 12, 21, 22', $plan->items->firstWhere('description', 'Resinas')->tooth_number);
        $this->assertTrue((bool) $plan->items->firstWhere('description', 'Blanqueamiento')->opcional);
    }

    // ── Lo que ve el paciente ───────────────────────────────────

    public function test_el_paciente_lo_ve_por_etapas_y_lo_opcional_aparte(): void
    {
        $this->plan();

        $this->get(route('treatment-plan.public', str_repeat('b', 64)))
            ->assertOk()
            ->assertSeeInOrder(['1. Urgencia', 'Extracción', 'Endodoncia', '2. Rehabilitación', 'Resinas'])
            ->assertSee('Opcional')
            ->assertSee('Carillas de porcelana')
            ->assertSee('11, 12, 21, 22');
    }

    public function test_con_solo_total_no_se_ven_los_precios_de_cada_tratamiento(): void
    {
        $this->plan(['detalle_al_enviar' => 'solo_total']);

        $this->get(route('treatment-plan.public', str_repeat('b', 64)))
            ->assertOk()
            ->assertDontSee('$4,000.00')
            ->assertSee('$8,700.00');
    }

    public function test_sin_precios_no_se_ve_ningun_monto(): void
    {
        $this->plan(['detalle_al_enviar' => 'sin_precios']);

        $this->get(route('treatment-plan.public', str_repeat('b', 64)))
            ->assertOk()
            ->assertSee('Endodoncia')
            ->assertDontSee('$4,000.00')
            ->assertDontSee('$8,700.00');
    }

    // ── La firma ────────────────────────────────────────────────

    public function test_para_aceptar_tiene_que_firmar(): void
    {
        $plan = $this->plan();

        $this->post(URL::signedRoute('treatment-plan.accept', ['token' => $plan->public_token]), ['firmado_por' => 'Ana Ruiz'])
            ->assertSessionHasErrors('firma');

        $this->assertSame('sent', $plan->fresh()->status);
    }

    public function test_al_firmar_queda_aceptado_con_su_firma_y_su_nombre(): void
    {
        $plan = $this->plan();

        $this->post(URL::signedRoute('treatment-plan.accept', ['token' => $plan->public_token]), ['firma' => self::FIRMA, 'firmado_por' => 'Ana Ruiz'])
            ->assertOk();

        $plan->refresh();
        $this->assertSame('accepted', $plan->status);
        $this->assertSame(self::FIRMA, $plan->firma_paciente);
        $this->assertSame('Ana Ruiz', $plan->firmado_por);
    }

    public function test_una_firma_que_no_es_imagen_no_se_acepta(): void
    {
        $plan = $this->plan();

        $this->post(URL::signedRoute('treatment-plan.accept', ['token' => $plan->public_token]), ['firma' => '<script>alert(1)</script>', 'firmado_por' => 'Ana'])
            ->assertSessionHasErrors('firma');
    }

    public function test_el_pdf_lleva_la_firma(): void
    {
        $plan = $this->plan();
        $plan->update(['status' => 'accepted', 'accepted_at' => now(), 'firma_paciente' => self::FIRMA, 'firmado_por' => 'Ana Ruiz']);

        $html = view('pdf.treatment-plan', ['plan' => $plan->fresh()->load(['patient', 'doctor.user', 'clinic', 'items.service'])])->render();

        $this->assertStringContainsString(self::FIRMA, $html);
        $this->assertStringContainsString('Ana Ruiz', $html);
        $this->assertStringContainsString('1. Urgencia', $html);
    }
}
