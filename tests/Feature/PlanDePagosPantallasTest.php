<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\PaymentPlanResource\Pages\CreatePaymentPlan;
use App\Filament\Doctor\Resources\PaymentPlanResource\Pages\ListPaymentPlans;
use App\Filament\Doctor\Resources\PaymentPlanResource\Pages\ViewPaymentPlan;
use App\Filament\Doctor\Resources\PaymentPlanResource\RelationManagers\MensualidadesRelationManager;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class PlanDePagosPantallasTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Patient $paciente;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-01 12:00:00');
        $this->clinica = Clinic::create(['name' => 'Consultorio Test', 'plan' => 'profesional', 'plan_ends_at' => now()->addYear(), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Ortodoncia']);
        $this->paciente = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Diego', 'last_name' => 'Salazar', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_el_doctor_arma_el_plan_y_ve_como_queda_antes_de_guardar(): void
    {
        Livewire::test(CreatePaymentPlan::class)
            ->fillForm([
                'patient_id' => $this->paciente->id,
                'description' => 'Ortodoncia',
                'total' => 21000,
                'down_payment' => 5000,
                'installments_count' => 20,
                'first_due_date' => '2026-11-01',
                'enganche_pagado' => true,
                'payment_method' => 'cash',
            ])
            ->assertSee('20 mensualidades de $800.00')
            ->call('create')
            ->assertHasNoFormErrors();

        $plan = PaymentPlan::first();
        $this->assertSame(21, $plan->payments()->count());
        $this->assertEquals(5000, $plan->pagado());
    }

    public function test_un_presupuesto_aceptado_se_vuelve_plan_de_pagos(): void
    {
        $presupuesto = TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Ortodoncia con brackets metálicos', 'status' => 'accepted', 'discount' => 0, 'total' => 24000]);

        Livewire::withQueryParams(['presupuesto' => $presupuesto->id])
            ->test(CreatePaymentPlan::class)
            ->assertFormSet([
                'patient_id' => $this->paciente->id,
                'description' => 'Ortodoncia con brackets metálicos',
                'total' => 24000,
                'treatment_plan_id' => $presupuesto->id,
            ]);
    }

    public function test_desde_el_plan_se_registra_el_pago_de_una_mensualidad(): void
    {
        $plan = PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'description' => 'Ortodoncia',
            'total' => 21000, 'down_payment' => 5000, 'installments_count' => 20, 'first_due_date' => '2026-11-01'], 'cash');
        $noviembre = $plan->payments()->where('installment_number', 1)->first();

        Livewire::test(MensualidadesRelationManager::class, ['ownerRecord' => $plan, 'pageClass' => ViewPaymentPlan::class])
            ->assertCanSeeTableRecords([$noviembre])
            ->callTableAction('pagar', $noviembre, data: ['monto' => 800, 'payment_method' => 'transfer']);

        $this->assertSame('paid', $noviembre->fresh()->status);
        $this->assertEquals(5800, $plan->fresh()->pagado());
    }

    public function test_la_lista_dice_que_sigue_y_cuantas_debe(): void
    {
        PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'description' => 'Ortodoncia',
            'total' => 21000, 'down_payment' => 5000, 'installments_count' => 20, 'first_due_date' => '2026-08-01'], 'cash');

        Livewire::test(ListPaymentPlans::class)
            ->assertSee('Planes de pago')
            ->assertDontSee('Planes De Pago')
            ->assertSee('Diego Salazar')
            ->assertSee('2 vencidas')
            ->assertSee('$800.00');
    }

    // ── Conectado con lo demás ──────────────────────────────────

    private function planDeDiego(string $primera = '2026-09-01'): PaymentPlan
    {
        return PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'description' => 'Ortodoncia',
            'total' => 21000, 'down_payment' => 5000, 'installments_count' => 20, 'first_due_date' => $primera], 'cash');
    }

    public function test_el_presupuesto_aceptado_ofrece_hacer_el_plan_de_pagos(): void
    {
        $this->clinica->update(['plan' => 'free', 'trial_ends_at' => now()->addDays(5)]);
        $presupuesto = TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Ortodoncia', 'status' => 'accepted', 'discount' => 0, 'total' => 24000]);

        Livewire::test(\App\Filament\Doctor\Resources\TreatmentPlanResource\Pages\EditTreatmentPlan::class, ['record' => $presupuesto->id])
            ->assertActionVisible('plan_de_pagos')
            ->assertActionHasUrl('plan_de_pagos', PaymentPlanResourceUrl::crearDesde($presupuesto));
    }

    public function test_en_la_consulta_se_cobra_la_mensualidad_que_debe(): void
    {
        $plan = $this->planDeDiego();
        $septiembre = $plan->payments()->where('installment_number', 1)->first();
        $servicio = \App\Models\Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Ajuste de brackets', 'price' => 0, 'duration_minutes' => 20, 'is_active' => true]);

        Livewire::test(\App\Filament\Doctor\Pages\Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->paciente->id)
            ->set('data.walkin_service_id', (string) $servicio->id)
            ->call('startWalkIn')
            ->set('currentStep', 4)
            ->assertSee('Mensualidades por cobrar')
            ->assertSee('Ortodoncia — mensualidad 1 de 20')
            ->call('cobrarMensualidad', $septiembre->id, 'card')
            // Ya cobrada, sale de la lista en ese momento.
            ->assertDontSee('Ortodoncia — mensualidad 1 de 20');

        $this->assertSame('paid', $septiembre->fresh()->status);
        $this->assertSame('card', $septiembre->receipts()->latest('id')->value('payment_method'));
    }

    public function test_el_perfil_muestra_el_plan_con_lo_que_debe(): void
    {
        $this->planDeDiego();

        Livewire::withQueryParams(['patient' => $this->paciente->id])
            ->test(\App\Filament\Doctor\Pages\PatientProfile::class)
            ->call('setTab', 'payments')
            ->assertSee('Planes de pago')
            ->assertSee('Ortodoncia')
            ->assertSee('1 vencida');
    }

    public function test_la_cita_de_la_mensualidad_no_se_cobra_dos_veces(): void
    {
        $mensualidad = \App\Models\Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Ortodoncia (mensualidad)', 'price' => 800, 'duration_minutes' => 30, 'is_active' => true]);
        PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'service_id' => $mensualidad->id, 'description' => 'Ortodoncia',
            'total' => 21000, 'down_payment' => 5000, 'installments_count' => 20, 'first_due_date' => '2026-10-01'], 'cash');

        Livewire::test(\App\Filament\Doctor\Pages\Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->paciente->id)
            ->set('data.walkin_service_id', (string) $mensualidad->id)
            ->call('startWalkIn')
            ->assertSet('payment_amount', '0')
            ->set('currentStep', 4)
            ->assertSee('Ortodoncia — mensualidad 1 de 20');
    }
}

/** La liga para crear el plan desde un presupuesto, para no repetirla en la prueba. */
class PaymentPlanResourceUrl
{
    public static function crearDesde(TreatmentPlan $p): string
    {
        return \App\Filament\Doctor\Resources\PaymentPlanResource::getUrl('create', ['presupuesto' => $p->id], panel: 'doctor');
    }
}
