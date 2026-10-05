<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Widgets\CalendarWidget;
use App\Filament\Doctor\Widgets\PendingPayments;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\Service;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Que el sistema nunca cobre dos veces ni dé por pagado lo que no se pagó.
 *
 * Octubre 2026, revisando la consulta de punta a punta: una cita ya
 * terminada se podía volver a abrir desde el calendario y cerrarla otra vez
 * dejaba un segundo cobro; "Guardar y terminar" desde el diagnóstico daba
 * por pagado un cobro que el doctor nunca vio; "Cobros pendientes" ofrecía
 * marcar pagadas mensualidades que todavía no vencen; y un tratamiento del
 * presupuesto que el paciente ya paga en mensualidades se volvía a cobrar
 * completo, al precio del catálogo y no al del presupuesto.
 *
 * Un dentista que ve un cobro doble deja de creerle al corte del mes.
 */
class CobrosSinDobleTest extends TestCase
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
            'specialty' => 'Odontología',
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

        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    private function cita(array $datos = []): Appointment
    {
        return Appointment::create(array_merge([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'service_id' => $this->service->id,
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(2),
            'status' => 'scheduled',
        ], $datos));
    }

    private function consulta(Appointment $cita)
    {
        return Livewire::actingAs($this->user)
            ->withQueryParams(['appointment' => $cita->id])
            ->test(Consultation::class);
    }

    private function perfil(Appointment $cita): string
    {
        return route('filament.doctor.pages.perfil-paciente', ['patient' => $cita->patient_id]);
    }

    // ── Una consulta cerrada no se vuelve a cerrar ───────────────

    public function test_una_cita_terminada_no_reabre_la_consulta(): void
    {
        $cita = $this->cita(['status' => 'completed']);

        $this->consulta($cita)->assertRedirect($this->perfil($cita));

        $this->assertSame('completed', $cita->fresh()->status);
    }

    public function test_una_cita_cancelada_tampoco_abre_la_consulta(): void
    {
        $cita = $this->cita(['status' => 'cancelled']);

        $this->consulta($cita)->assertRedirect($this->perfil($cita));

        $this->assertSame('cancelled', $cita->fresh()->status);
    }

    public function test_cerrarla_dos_veces_deja_un_solo_cobro_y_un_solo_expediente(): void
    {
        $cita = $this->cita();

        $this->consulta($cita)
            ->call('goToStep', 4)
            ->call('saveAndComplete')
            ->call('saveAndComplete');

        $this->assertSame(1, Payment::where('appointment_id', $cita->id)->count());
        $this->assertSame(1, MedicalRecord::where('appointment_id', $cita->id)->count());
    }

    public function test_el_calendario_lleva_la_cita_terminada_al_perfil(): void
    {
        $cita = $this->cita(['status' => 'completed']);

        Livewire::test(CalendarWidget::class)
            ->call('onEventClick', ['id' => $cita->id])
            ->assertRedirect($this->perfil($cita));
    }

    public function test_el_calendario_lleva_la_cita_pendiente_a_la_consulta(): void
    {
        $cita = $this->cita();

        Livewire::test(CalendarWidget::class)
            ->call('onEventClick', ['id' => $cita->id])
            ->assertRedirect(route('filament.doctor.pages.consulta', ['appointment' => $cita->id]));
    }

    // ── Lo que el doctor no vio no se da por pagado ──────────────

    public function test_terminar_sin_pasar_por_el_cobro_lo_deja_por_cobrar(): void
    {
        $cita = $this->cita();

        $this->consulta($cita)
            ->call('goToStep', 2)
            ->call('saveAndComplete')
            ->assertSee('$500 por cobrar');

        $cobro = Payment::where('appointment_id', $cita->id)->sole();
        $this->assertSame('pending', $cobro->status);
        $this->assertEquals(500, (float) $cobro->amount);
        $this->assertEquals(0, (float) $cobro->receipts()->sum('amount'));
    }

    public function test_pasando_por_el_cobro_queda_pagado(): void
    {
        $cita = $this->cita();

        $this->consulta($cita)
            ->call('goToStep', 4)
            ->call('goToStep', 2)
            ->call('saveAndComplete');

        $this->assertSame('paid', Payment::where('appointment_id', $cita->id)->sole()->status);
    }

    public function test_haber_visto_el_cobro_sobrevive_a_recargar_la_pagina(): void
    {
        $cita = $this->cita();

        $this->consulta($cita)->call('goToStep', 4)->call('goToStep', 2);

        $this->consulta($cita->fresh())->call('saveAndComplete');

        $this->assertSame('paid', Payment::where('appointment_id', $cita->id)->sole()->status);
    }

    // ── Cobros pendientes: solo lo que ya toca ───────────────────

    public function test_cobros_pendientes_no_ofrece_mensualidades_que_no_vencen(): void
    {
        $plan = PaymentPlan::crear([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'description' => 'Brackets',
            'total' => 12000,
            'down_payment' => 0,
            'installments_count' => 12,
            'first_due_date' => today()->subMonth()->toDateString(),
        ]);

        $vencida = $plan->payments()->where('installment_number', 1)->sole();
        $futura = $plan->payments()->where('installment_number', 6)->sole();

        Livewire::test(PendingPayments::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$vencida])
            ->assertCanNotSeeTableRecords([$futura]);
    }

    // ── El presupuesto manda: lo financiado no se cobra otra vez ─

    private function presupuesto(float $precio, float $subtotal, float $total): TreatmentPlanItem
    {
        $plan = TreatmentPlan::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'title' => 'Tratamiento',
            'subtotal' => $subtotal,
            'discount' => $subtotal - $total,
            'total' => $total,
            'status' => 'accepted',
        ]);

        return TreatmentPlanItem::create([
            'treatment_plan_id' => $plan->id,
            'service_id' => $this->service->id,
            'description' => 'Resina',
            'quantity' => 1,
            'unit_price' => $precio,
            'subtotal' => $precio,
            'tooth_number' => '36',
        ]);
    }

    public function test_se_cobra_el_precio_del_presupuesto_y_no_el_del_catalogo(): void
    {
        $item = $this->presupuesto(400, 400, 400);
        $cita = $this->cita(['treatment_plan_item_id' => $item->id]);

        $this->assertEquals(400, (float) $this->consulta($cita)->get('payment_amount'));
    }

    public function test_el_descuento_del_presupuesto_tambien_cuenta(): void
    {
        // $800 de presupuesto con $80 de descuento: cada peso vale 90 centavos.
        $item = $this->presupuesto(400, 800, 720);
        $cita = $this->cita(['treatment_plan_item_id' => $item->id]);

        $this->assertEquals(360, (float) $this->consulta($cita)->get('payment_amount'));
    }

    public function test_el_tratamiento_que_paga_en_mensualidades_no_se_cobra_otra_vez(): void
    {
        $item = $this->presupuesto(400, 400, 400);
        PaymentPlan::crear([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'treatment_plan_id' => $item->treatment_plan_id,
            'description' => 'Tratamiento',
            'total' => 400,
            'down_payment' => 0,
            'installments_count' => 4,
            'first_due_date' => today()->toDateString(),
        ]);
        $cita = $this->cita(['treatment_plan_item_id' => $item->id]);

        $consulta = $this->consulta($cita);
        $this->assertEquals(0, (float) $consulta->get('payment_amount'));

        // Y si el doctor ajusta el procedimiento, sigue sin cobrarlo aparte.
        $consulta->set('procedures.0.quantity', 2);
        $this->assertEquals(0, (float) $consulta->get('payment_amount'));
    }
}
