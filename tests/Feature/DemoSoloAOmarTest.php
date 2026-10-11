<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * La demo solo le escribe a Omar (11-oct-2026). Los pacientes de ejemplo
 * tienen teléfonos que parecen de verdad y podrían ser de alguien; en la demo
 * todo WhatsApp (ligas y recordatorios automáticos) va al número de Omar.
 */
class DemoSoloAOmarTest extends TestCase
{
    use RefreshDatabase;

    private const OMAR = '526682493398';

    private Clinic $demo;
    private User $usuario;
    private Doctor $doctor;
    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 14:00'));
        config(['services.notifications.phone' => '6682493398']);
        $this->demo = Clinic::create(['name' => 'Clínica Demo', 'plan' => 'profesional', 'plan_ends_at' => now()->addYear(),
            'is_active' => true, 'onboarding_status' => 'completed', 'is_demo' => true, 'recordatorios_automaticos' => true]);
        $this->usuario = User::forceCreate(['name' => 'Dr. Demo', 'email' => 'otro-demo@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->demo->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->demo->id, 'specialty' => 'Odontología']);
        $this->paciente = Patient::create(['clinic_id' => $this->demo->id, 'first_name' => 'María', 'last_name' => 'García', 'phone' => '55 2111 3001']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    public function test_en_las_pantallas_de_la_demo_el_whatsapp_va_a_omar(): void
    {
        $html = $this->actingAs($this->usuario)->get(\App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $this->paciente->id]))->assertOk()->getContent();

        $this->assertStringNotContainsString('wa.me/525521113001', $html);
        $this->assertStringNotContainsString('5521113001?', $html);
        $this->assertStringContainsString('wa.me/' . self::OMAR, $html);
    }

    public function test_las_ligas_que_abren_whatsapp_tambien(): void
    {
        $plan = TreatmentPlan::create(['clinic_id' => $this->demo->id, 'patient_id' => $this->paciente->id, 'title' => 'Plan', 'status' => 'sent', 'total' => 1000]);

        $destino = $this->actingAs($this->usuario)->get(route('plan.recordar', $plan))->headers->get('Location');

        $this->assertStringContainsString('wa.me/' . self::OMAR, (string) $destino);
    }

    public function test_los_recordatorios_automaticos_de_la_demo_van_a_omar(): void
    {
        config(['services.whatsapp.token' => 'tok', 'services.whatsapp.phone_number_id' => '123', 'services.whatsapp.automaticos' => true]);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);
        Appointment::create(['clinic_id' => $this->demo->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->paciente->id,
            'starts_at' => '2026-10-15 11:00', 'ends_at' => '2026-10-15 11:30', 'status' => 'scheduled']);

        $this->artisan('docfacil:send-reminders');

        $envio = collect(Http::recorded())->map(fn ($par) => $par[0]->data())->first();
        $this->assertSame(self::OMAR, $envio['to']);
    }

    public function test_la_demo_no_le_manda_mas_de_tres_al_dia(): void
    {
        config(['services.whatsapp.token' => 'tok', 'services.whatsapp.phone_number_id' => '123', 'services.whatsapp.automaticos' => true]);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);
        foreach (range(1, 6) as $i) {
            $p = Patient::create(['clinic_id' => $this->demo->id, 'first_name' => "P{$i}", 'last_name' => 'X', 'phone' => '552111300' . $i]);
            Appointment::create(['clinic_id' => $this->demo->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $p->id,
                'starts_at' => '2026-10-15 ' . (9 + $i) . ':00', 'ends_at' => '2026-10-15 ' . (9 + $i) . ':30', 'status' => 'scheduled']);
        }

        $this->artisan('docfacil:send-reminders');

        $this->assertCount(3, Http::recorded());
    }

    public function test_un_consultorio_normal_no_se_toca(): void
    {
        $this->demo->update(['is_demo' => false]);

        $html = $this->actingAs($this->usuario)->get(\App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $this->paciente->id]))->getContent();

        // El botón de ayuda sí es de Omar; aquí importa que el del paciente quede igual.
        $this->assertStringContainsString('wa.me/525521113001', $html);
    }
}
