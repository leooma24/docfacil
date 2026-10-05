<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\TreatmentPlanResource;
use App\Filament\Doctor\Resources\TreatmentPlanResource\Pages\EditTreatmentPlan;
use App\Filament\Doctor\Resources\TreatmentPlanResource\Pages\ListTreatmentPlans;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\TreatmentPlan;
use App\Models\User;
use App\Support\LoQueSigue;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El paciente acepta el presupuesto en línea y el doctor se entera; agendarlo
 * es abrir el aviso y escoger la hora.
 *
 * Octubre 2026: aceptar solo dejaba una línea en el log. El doctor se
 * enteraba si revisaba las alertas, y de ahí eran la lista, el presupuesto,
 * el "Agendar" de cada tratamiento y la fecha: unos seis clics.
 */
class PresupuestoAceptadoSeAgendaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $user;
    private Doctor $doctor;
    private Patient $ana;
    private TreatmentPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $resina = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Resina', 'price' => 600, 'duration_minutes' => 40, 'is_active' => true]);
        $limpieza = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Limpieza', 'price' => 500, 'duration_minutes' => 30, 'is_active' => true]);
        $this->plan = TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Plan de Ana', 'status' => 'sent', 'subtotal' => 1100, 'discount' => 0, 'total' => 1100, 'public_token' => str_repeat('a', 64)]);
        $this->plan->items()->create(['service_id' => $limpieza->id, 'description' => 'Limpieza', 'quantity' => 1, 'unit_price' => 500, 'sort_order' => 1]);
        $this->plan->items()->create(['service_id' => $resina->id, 'description' => 'Resina en el 36', 'tooth_number' => '36', 'quantity' => 1, 'unit_price' => 600, 'sort_order' => 0]);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    private function aceptar(): void
    {
        $this->get(URL::signedRoute('treatment-plan.accept', ['token' => $this->plan->public_token]))->assertOk();
    }

    // ── Al aceptar, el doctor se entera ──────────────────────────

    public function test_al_aceptar_el_doctor_recibe_el_aviso_con_agendar(): void
    {
        $this->aceptar();

        $aviso = $this->user->fresh()->notifications()->latest()->first();
        $this->assertNotNull($aviso);
        $datos = json_encode($aviso->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->assertStringContainsString('Ana Ruiz aceptó su presupuesto', $datos);
        $this->assertStringContainsString('Agendar', $datos);
        $this->assertStringContainsString(TreatmentPlanResource::urlParaAgendar($this->plan), $datos);
    }

    public function test_la_liga_de_agendar_abre_ya_la_ventana_de_la_fecha(): void
    {
        $this->assertStringContainsString('action=agendarSiguiente', TreatmentPlanResource::urlParaAgendar($this->plan));
    }

    // ── Agendar el siguiente tratamiento, uno tras otro ──────────

    public function test_agenda_el_primer_tratamiento_en_orden_y_queda_ligado(): void
    {
        $this->plan->update(['status' => 'accepted']);
        $this->actingAs($this->user);

        Livewire::test(EditTreatmentPlan::class, ['record' => $this->plan->id])
            ->callAction('agendarSiguiente', data: ['starts_at' => today()->addDay()->setTime(11, 0)->toDateTimeString()]);

        $cita = Appointment::sole();
        $this->assertSame('Resina en el 36', $cita->treatmentPlanItem->description);
        $this->assertSame('11:00', $cita->starts_at->format('H:i'));
        $this->assertSame('11:40', $cita->ends_at->format('H:i'));
    }

    public function test_despues_ofrece_el_siguiente_y_al_final_ya_no(): void
    {
        $this->plan->update(['status' => 'accepted']);
        $this->actingAs($this->user);

        $pagina = Livewire::test(EditTreatmentPlan::class, ['record' => $this->plan->id])
            ->callAction('agendarSiguiente', data: ['starts_at' => today()->addDay()->setTime(11, 0)->toDateTimeString()])
            ->callAction('agendarSiguiente', data: ['starts_at' => today()->addDays(8)->setTime(11, 0)->toDateTimeString()]);

        $this->assertSame(2, Appointment::count());
        $this->assertSame(['Resina en el 36', 'Limpieza'], Appointment::orderBy('starts_at')->get()->map->treatmentPlanItem->pluck('description')->all());
        $pagina->assertActionHidden('agendarSiguiente');
    }

    public function test_no_agenda_encima_de_otra_cita(): void
    {
        $this->plan->update(['status' => 'accepted']);
        $this->actingAs($this->user);
        Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'starts_at' => today()->addDay()->setTime(11, 0), 'ends_at' => today()->addDay()->setTime(12, 0), 'status' => 'scheduled']);

        Livewire::test(EditTreatmentPlan::class, ['record' => $this->plan->id])
            ->callAction('agendarSiguiente', data: ['starts_at' => today()->addDay()->setTime(11, 15)->toDateTimeString()]);

        $this->assertSame(1, Appointment::count());
    }

    public function test_el_presupuesto_que_no_se_ha_aceptado_no_se_agenda(): void
    {
        $this->actingAs($this->user);

        Livewire::test(EditTreatmentPlan::class, ['record' => $this->plan->id])
            ->assertActionHidden('agendarSiguiente');
    }

    // ── Desde la lista y desde el perfil ─────────────────────────

    public function test_la_lista_de_presupuestos_ofrece_agendar_el_aceptado(): void
    {
        $this->plan->update(['status' => 'accepted']);
        $this->actingAs($this->user);

        Livewire::test(ListTreatmentPlans::class)
            ->assertTableActionVisible('agendar', $this->plan)
            ->assertTableActionHasUrl('agendar', TreatmentPlanResource::urlParaAgendar($this->plan), $this->plan);
    }

    public function test_lo_que_sigue_del_perfil_lleva_directo_a_agendar(): void
    {
        $this->plan->update(['status' => 'accepted']);
        $this->actingAs($this->user);

        $porAgendar = collect(LoQueSigue::para($this->ana))->firstWhere('tipo', 'por_agendar');

        $this->assertSame(TreatmentPlanResource::urlParaAgendar($this->plan), $porAgendar['url']);
    }
}
