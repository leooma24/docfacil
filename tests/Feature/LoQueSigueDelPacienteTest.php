<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\TreatmentPlan;
use App\Models\User;
use App\Support\LoQueSigue;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El perfil del paciente mostraba todo lo que pasó (citas, recetas, pagos),
 * pero para saber qué tocaba ahora había que abrir cada pestaña y sumar de
 * cabeza. "Lo que sigue" lo junta arriba, cada cosa con su liga.
 */
class LoQueSigueDelPacienteTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $user;
    private Doctor $doctor;
    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio Test', 'plan' => 'free', 'trial_ends_at' => now()->addDays(10), 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->paciente = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Carlos', 'last_name' => 'Hernández', 'phone' => '6681234567', 'allergies' => 'Penicilina']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    private function tipos(): array
    {
        return array_column(LoQueSigue::para($this->paciente), 'tipo');
    }

    private function item(string $tipo): ?array
    {
        return collect(LoQueSigue::para($this->paciente))->firstWhere('tipo', $tipo);
    }

    private function presupuesto(string $status): TreatmentPlan
    {
        $plan = TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Plan', 'status' => $status, 'discount' => 0, 'sent_at' => now()->subDays(4)]);
        $plan->items()->create(['description' => 'Resina — diente 36', 'tooth_number' => '36', 'quantity' => 1, 'unit_price' => 900, 'sort_order' => 0]);
        $plan->items()->create(['description' => 'Extracción — diente 38', 'tooth_number' => '38', 'quantity' => 1, 'unit_price' => 1500, 'sort_order' => 1]);
        $plan->recalculateTotal();

        return $plan;
    }

    public function test_sin_nada_pendiente_solo_ofrece_agendar(): void
    {
        $this->assertSame(['sin_cita'], $this->tipos());
    }

    public function test_la_proxima_cita_con_su_hora_y_liga(): void
    {
        $cita = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->paciente->id,
            'starts_at' => now()->addDays(3)->setTime(10, 0), 'ends_at' => now()->addDays(3)->setTime(10, 30), 'status' => 'confirmed']);

        $item = $this->item('cita');
        $this->assertNotNull($item);
        $this->assertStringContainsString('10:00', $item['titulo']);
        $this->assertStringContainsString('/citas/' . $cita->id, $item['url']);
        $this->assertNull($this->item('sin_cita'));
    }

    public function test_tratamientos_aceptados_por_agendar(): void
    {
        $plan = $this->presupuesto('accepted');
        $plan->items()->first()->update(['completed_at' => now()]);

        $item = $this->item('por_agendar');
        $this->assertNotNull($item);
        $this->assertStringContainsString('1 tratamiento', $item['titulo']);
        $this->assertStringContainsString('diente 38', $item['detalle']);
        $this->assertStringContainsString('/presupuestos/' . $plan->id, $item['url']);
    }

    public function test_presupuesto_enviado_sin_respuesta(): void
    {
        $plan = $this->presupuesto('sent');

        $item = $this->item('presupuesto');
        $this->assertNotNull($item);
        $this->assertStringContainsString('2,400', $item['titulo']);
        $this->assertStringContainsString('/presupuestos/' . $plan->id, $item['url']);
    }

    public function test_saldo_vencido_primero(): void
    {
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'amount' => 2500, 'amount_paid' => 1000,
            'status' => 'partial', 'payment_date' => now()->subDays(20), 'due_date' => now()->subDays(10)]);

        $this->assertSame('saldo', $this->tipos()[0]);
        $this->assertStringContainsString('1,500', $this->item('saldo')['titulo']);
    }

    public function test_la_siguiente_mensualidad_del_plan(): void
    {
        $plan = PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'doctor_id' => $this->doctor->id,
            'description' => 'Brackets', 'total' => 24000, 'down_payment' => 4000, 'installments_count' => 20, 'first_due_date' => now()->addDays(15)], 'cash');

        $item = $this->item('mensualidad');
        $this->assertNotNull($item);
        $this->assertStringContainsString('1,000', $item['titulo']);
        $this->assertStringContainsString('/planes-de-pago/' . $plan->id, $item['url']);
    }

    public function test_alergias_sin_registrar(): void
    {
        $this->paciente->update(['allergies' => null]);

        $this->assertNotNull($this->item('alergias'));
    }

    public function test_el_perfil_lo_muestra(): void
    {
        $this->presupuesto('sent');
        $this->actingAs($this->user);

        Livewire::withQueryParams(['patient' => $this->paciente->id])
            ->test(PatientProfile::class)
            ->assertSee('Lo que sigue')
            ->assertSee('Presupuesto sin respuesta');
    }
}
