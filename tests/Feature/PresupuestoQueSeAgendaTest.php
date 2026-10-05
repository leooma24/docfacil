<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Resources\TreatmentPlanResource\Pages\EditTreatmentPlan;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El paciente aceptó el presupuesto y luego cada cita se capturaba a mano,
 * sin ligarla al tratamiento: nadie sabía qué faltaba agendar ni qué ya se
 * hizo. Ahora cada tratamiento se agenda desde el presupuesto, la consulta
 * trae su diente y servicio, y al cerrarla queda hecho.
 */
class PresupuestoQueSeAgendaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $paciente;
    private Service $resina;
    private TreatmentPlan $plan;
    private TreatmentPlanItem $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio Test', 'plan' => 'free', 'trial_ends_at' => now()->addDays(10), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->paciente = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Carlos', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $this->resina = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Resina (obturación)', 'price' => 600, 'duration_minutes' => 40, 'is_active' => true]);
        $this->plan = TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Plan', 'status' => 'accepted', 'discount' => 0, 'total' => 600]);
        $this->item = $this->plan->items()->create(['service_id' => $this->resina->id, 'description' => 'Resina — diente 46 (oclusal)', 'tooth_number' => '46', 'quantity' => 1, 'unit_price' => 600, 'sort_order' => 0]);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    public function test_cada_tratamiento_aceptado_se_agenda_desde_el_presupuesto(): void
    {
        Livewire::test(EditTreatmentPlan::class, ['record' => $this->plan->id])
            ->callFormComponentAction('items', 'agendar', ['starts_at' => '2026-10-20 10:00'], ['item' => 'record-' . $this->item->id]);

        $cita = Appointment::where('treatment_plan_item_id', $this->item->id)->first();
        $this->assertNotNull($cita);
        $this->assertSame($this->resina->id, $cita->service_id);
        $this->assertSame($this->paciente->id, $cita->patient_id);
        $this->assertSame('2026-10-20 10:40', $cita->ends_at->format('Y-m-d H:i'), 'la duración sale del servicio');
    }

    public function test_el_presupuesto_dice_que_esta_agendado_y_que_esta_hecho(): void
    {
        $this->assertSame('Por agendar', $this->item->estado());

        Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->paciente->id,
            'service_id' => $this->resina->id, 'treatment_plan_item_id' => $this->item->id,
            'starts_at' => now()->addDays(3)->setTime(10, 0), 'ends_at' => now()->addDays(3)->setTime(10, 40), 'status' => 'scheduled']);
        $this->assertStringStartsWith('Agendado', $this->item->fresh()->estado());

        $this->item->update(['completed_at' => now()]);
        $this->assertStringStartsWith('Hecho', $this->item->fresh()->estado());
    }

    public function test_la_cita_del_presupuesto_abre_la_consulta_con_su_diente_y_al_cerrarla_queda_hecho(): void
    {
        $cita = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->paciente->id,
            'service_id' => $this->resina->id, 'treatment_plan_item_id' => $this->item->id,
            'starts_at' => now()->addMinutes(10), 'ends_at' => now()->addMinutes(50), 'status' => 'confirmed']);

        Livewire::withQueryParams(['appointment' => $cita->id])->test(Consultation::class)
            ->assertSet('procedures', [['service_id' => (string) $this->resina->id, 'tooth_number' => '46', 'quantity' => 1,
                'precio' => 600.0, 'precio_de' => (string) $this->resina->id]])
            ->call('saveAndComplete');

        $this->assertNotNull($this->item->fresh()->completed_at);
    }

    public function test_no_se_agenda_un_presupuesto_que_no_se_ha_aceptado(): void
    {
        $this->plan->update(['status' => 'sent']);

        Livewire::test(EditTreatmentPlan::class, ['record' => $this->plan->id])
            ->assertFormComponentActionHidden('items', 'agendar', ['item' => 'record-' . $this->item->id]);
    }
}
