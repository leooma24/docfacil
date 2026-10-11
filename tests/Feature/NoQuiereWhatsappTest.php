<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\PatientResource\Pages\EditPatient;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El paciente que no quiere WhatsApp (10-oct-2026). WhatsApp exige respetar a
 * quien pide que no le escriban; si no, baja la calidad del número de
 * DocFácil y con él los recordatorios de todos los consultorios.
 * - El consultorio lo marca en el perfil del paciente.
 * - Si el paciente contesta "ya no me manden" (o "baja"), se marca solo.
 * - Marcado, no le sale ningún recordatorio automático.
 */
class NoQuiereWhatsappTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 14:00'));
        config(['services.whatsapp.token' => 'tok', 'services.whatsapp.phone_number_id' => '123',
            'services.whatsapp.app_secret' => 'secreto', 'services.whatsapp.automaticos' => true]);
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed', 'recordatorios_automaticos' => true]);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '668 123 4567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    private function cita(Patient $p): Appointment
    {
        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $p->id,
            'starts_at' => '2026-10-15 11:00', 'ends_at' => '2026-10-15 11:30', 'status' => 'scheduled']);
    }

    private function mensajesAMeta(): array
    {
        return collect(Http::recorded())->filter(fn ($par) => str_contains($par[0]->url(), '/messages'))->map(fn ($par) => $par[0]->data())->values()->all();
    }

    private function escribe(string $texto)
    {
        $cuerpo = json_encode(['entry' => [['changes' => [['value' => ['messages' => [[
            'from' => '526681234567', 'id' => 'wamid.t', 'type' => 'text', 'text' => ['body' => $texto],
        ]]]]]]]]);

        return $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => 'sha256=' . hash_hmac('sha256', $cuerpo, 'secreto'),
        ], $cuerpo);
    }

    public function test_el_consultorio_lo_marca_en_el_paciente(): void
    {
        $this->actingAs($this->usuario);

        Livewire::test(EditPatient::class, ['record' => $this->ana->getRouteKey()])
            ->assertSee('No quiere recordatorios por WhatsApp')
            ->set('data.no_quiere_whatsapp', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue((bool) $this->ana->fresh()->no_quiere_whatsapp);
    }

    public function test_marcado_no_le_sale_el_recordatorio_automatico(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);
        $this->ana->update(['no_quiere_whatsapp' => true]);
        $this->cita($this->ana);

        $this->artisan('docfacil:send-reminders');

        $this->assertSame([], $this->mensajesAMeta());
    }

    public function test_si_la_mama_no_quiere_tampoco_le_llega_el_del_nino(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);
        $this->ana->update(['no_quiere_whatsapp' => true]);
        $mateo = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Mateo', 'last_name' => 'Ruiz', 'responsable_id' => $this->ana->id]);
        $this->cita($mateo);

        $this->artisan('docfacil:send-reminders');

        $this->assertSame([], $this->mensajesAMeta());
    }

    public function test_si_contesta_ya_no_me_manden_se_marca_solo_y_se_le_confirma(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.2']]])]);
        $otraClinica = Clinic::create(['name' => 'Otra', 'plan' => 'basico']);
        $enOtra = Patient::withoutGlobalScopes()->create(['clinic_id' => $otraClinica->id, 'first_name' => 'Ana', 'last_name' => 'R', 'phone' => '+52 (668) 123-4567']);
        $otroNumero = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Luis', 'last_name' => 'P', 'phone' => '6689999999']);

        $this->escribe('Ya no me manden mensajes por favor')->assertOk();

        $this->assertTrue((bool) $this->ana->fresh()->no_quiere_whatsapp);
        $this->assertNotNull($this->ana->fresh()->no_quiere_whatsapp_at);
        $this->assertTrue((bool) Patient::withoutGlobalScopes()->find($enOtra->id)->no_quiere_whatsapp, 'Lo pidió al número de DocFácil: vale para todos sus consultorios');
        $this->assertFalse((bool) $otroNumero->fresh()->no_quiere_whatsapp);
        $this->assertStringContainsString('ya no le', mb_strtolower($this->mensajesAMeta()[0]['text']['body']));
    }

    public function test_baja_tambien_cuenta(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.2']]])]);

        $this->escribe('BAJA')->assertOk();

        $this->assertTrue((bool) $this->ana->fresh()->no_quiere_whatsapp);
    }

    public function test_ya_no_puedo_ir_no_es_darse_de_baja(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.2']]])]);

        $this->escribe('Ya no puedo ir mañana, ¿me la cambia?')->assertOk();

        $this->assertFalse((bool) $this->ana->fresh()->no_quiere_whatsapp);
    }

    public function test_el_perfil_lo_avisa(): void
    {
        $this->ana->update(['no_quiere_whatsapp' => true]);
        $this->actingAs($this->usuario);

        $this->get(\App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $this->ana->id], panel: 'doctor'))
            ->assertOk()->assertSee('No quiere WhatsApp');
    }
}
