<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * La ortodoncia se cobra con enganche y mensualidades. Antes solo se podía
 * capturar un cobro de $21,000 con "pagado $5,000": no había fechas, ni
 * forma de saber quién debe la de octubre.
 */
class PlanDePagosTest extends TestCase
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
        $this->paciente = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Diego', 'last_name' => 'Salazar']);
        $this->actingAs($user);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function ortodoncia(array $cambios = []): PaymentPlan
    {
        return PaymentPlan::crear(array_merge([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'description' => 'Ortodoncia',
            'total' => 21000,
            'down_payment' => 5000,
            'installments_count' => 20,
            'first_due_date' => '2026-11-01',
        ], $cambios), enganchePagadoCon: 'cash');
    }

    public function test_genera_el_enganche_y_las_mensualidades_con_su_fecha(): void
    {
        $plan = $this->ortodoncia();

        $cobros = $plan->payments()->orderBy('installment_number')->get();
        $this->assertCount(21, $cobros);
        $this->assertSame(0, $cobros[0]->installment_number);
        $this->assertEquals(5000, (float) $cobros[0]->amount);
        $this->assertEquals(800, (float) $cobros[1]->amount);
        $this->assertSame('2026-11-01', $cobros[1]->due_date->toDateString());
        $this->assertSame('2026-12-01', $cobros[2]->due_date->toDateString());
        $this->assertSame('2028-06-01', $cobros[20]->due_date->toDateString());
        $this->assertSame('Ortodoncia — mensualidad 3 de 20', $cobros[3]->notes);
        $this->assertEquals(21000, (float) $cobros->sum('amount'));
    }

    public function test_los_centavos_que_sobran_van_en_la_ultima(): void
    {
        $plan = $this->ortodoncia(['total' => 10000, 'down_payment' => 0, 'installments_count' => 3]);

        $montos = $plan->payments()->orderBy('installment_number')->pluck('amount')->map(fn ($m) => (float) $m)->all();
        $this->assertSame([3333.33, 3333.33, 3333.34], $montos);
    }

    public function test_el_enganche_pagado_hoy_entra_al_corte_de_hoy(): void
    {
        $this->ortodoncia();

        $this->assertEquals(5000, Payment::cobradoEntre($this->clinica->id, today(), today()));
    }

    public function test_por_cobrar_no_incluye_las_mensualidades_que_no_han_vencido(): void
    {
        $this->ortodoncia();
        $this->assertEquals(0, Payment::saldoPorCobrar($this->clinica->id));

        Carbon::setTestNow('2027-01-15');
        // Vencieron noviembre, diciembre y enero.
        $this->assertEquals(2400, Payment::saldoPorCobrar($this->clinica->id));
    }

    public function test_sabe_cuanto_lleva_pagado_que_sigue_y_cuantas_debe(): void
    {
        $plan = $this->ortodoncia();
        Carbon::setTestNow('2027-01-15');
        $plan->payments()->where('installment_number', 1)->first()->registrarAbono(800, 'transfer');

        $plan->refresh();
        $this->assertEquals(5800, $plan->pagado());
        $this->assertEquals(15200, $plan->saldo());
        $this->assertSame(2, $plan->vencidas()->count());
        $this->assertSame(2, $plan->siguiente()->installment_number);
    }

    public function test_al_pagar_la_ultima_el_plan_queda_liquidado(): void
    {
        $plan = $this->ortodoncia(['total' => 1600, 'down_payment' => 0, 'installments_count' => 2]);

        foreach ($plan->payments as $mensualidad) {
            $mensualidad->registrarAbono((float) $mensualidad->amount, 'cash');
        }

        $this->assertSame('completed', $plan->fresh()->status);
    }

    public function test_sin_enganche_pagado_queda_pendiente(): void
    {
        $plan = PaymentPlan::crear([
            'clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'description' => 'Ortodoncia',
            'total' => 21000, 'down_payment' => 5000, 'installments_count' => 20, 'first_due_date' => '2026-11-01',
        ]);

        $enganche = $plan->payments()->where('installment_number', 0)->first();
        $this->assertSame('pending', $enganche->status);
        $this->assertSame('2026-10-01', $enganche->due_date->toDateString());
        $this->assertEquals(5000, Payment::saldoPorCobrar($this->clinica->id));
    }

    public function test_el_enganche_no_puede_ser_mayor_al_total(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->ortodoncia(['down_payment' => 22000]);
    }

    public function test_las_mensualidades_futuras_no_saturan_cobros_ni_perfil(): void
    {
        $this->ortodoncia();
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('doctor'));

        // En cobros se ve el enganche, no las 20 mensualidades que faltan.
        \Livewire\Livewire::test(\App\Filament\Doctor\Resources\PaymentResource\Pages\ListPayments::class)
            ->assertCountTableRecords(1);

        $perfil = \Livewire\Livewire::withQueryParams(['patient' => $this->paciente->id])
            ->test(\App\Filament\Doctor\Pages\PatientProfile::class)->instance();
        $this->assertEquals(0, $perfil->getStatsProperty()['pending']);
        $this->assertCount(1, $perfil->getPaymentsProperty());

        Carbon::setTestNow('2026-12-15');
        $this->assertEquals(1600, $perfil->getStatsProperty()['pending']);
    }
}
