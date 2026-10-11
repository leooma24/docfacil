<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\ClinicSettings;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Support\PlantillasDeWhatsapp;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Recordatorios que salen solos por la API oficial de WhatsApp (12-oct-2026).
 *
 * Romita, al revisar la demo: "no resuelve su premisa de automatizar". El
 * código existía pero se apagó el 14-sep (mandaba texto libre, a todos los
 * consultorios, sin que el doctor lo pidiera). Ahora:
 * - plantillas aprobadas por Meta, con botones "Confirmo" / "Necesito cambiar";
 * - solo a consultorios que lo prenden en Configuración, y solo si el
 *   interruptor general del servidor (WHATSAPP_AUTOMATICOS) está prendido;
 * - a quien paga (la mamá si es un niño), sin repetir a quien ya se le mandó
 *   a mano, con tope diario por consultorio;
 * - "Confirmo" marca la cita confirmada; "Necesito cambiar" avisa al consultorio.
 */
class RecordatoriosAutomaticosTest extends TestCase
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
        config(['services.whatsapp.token' => 'tok', 'services.whatsapp.phone_number_id' => '123', 'services.whatsapp.business_account_id' => '999',
            'services.whatsapp.app_secret' => 'secreto', 'services.whatsapp.automaticos' => true]);
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed', 'recordatorios_automaticos' => true]);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    private function cita(string $inicio, ?Patient $p = null, array $extra = []): Appointment
    {
        return Appointment::create(array_merge(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => ($p ?? $this->ana)->id,
            'starts_at' => $inicio, 'ends_at' => \Carbon\Carbon::parse($inicio)->addMinutes(30), 'status' => 'scheduled'], $extra));
    }

    private function enviosAMeta(): array
    {
        return collect(Http::recorded())->filter(fn ($par) => str_contains($par[0]->url(), '/messages'))->map(fn ($par) => $par[0]->data())->values()->all();
    }

    // ── Las plantillas ──────────────────────────────────────────

    public function test_las_plantillas_son_de_utilidad_en_espanol_con_botones(): void
    {
        $p = PlantillasDeWhatsapp::paraMeta('docfacil_recordatorio_cita');

        $this->assertSame('UTILITY', $p['category']);
        $this->assertSame('es_MX', $p['language']);
        $botones = collect($p['components'])->firstWhere('type', 'BUTTONS')['buttons'];
        $this->assertSame(['Confirmo', 'Necesito cambiar'], array_column($botones, 'text'));
        $this->assertNotEmpty(collect($p['components'])->firstWhere('type', 'BODY')['example']);
    }

    public function test_el_comando_las_manda_a_revision_de_meta(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['id' => '1', 'status' => 'PENDING'])]);

        $this->artisan('docfacil:whatsapp-plantillas', ['--enviar' => true])->assertSuccessful();

        Http::assertSent(fn ($r) => str_contains($r->url(), '/999/message_templates') && $r['name'] === 'docfacil_recordatorio_cita');
    }

    // ── El interruptor ──────────────────────────────────────────

    public function test_el_doctor_lo_prende_en_configuracion(): void
    {
        $this->clinica->update(['recordatorios_automaticos' => false]);
        $this->actingAs($this->usuario);

        Livewire::test(ClinicSettings::class)
            ->assertSee('Recordatorios automáticos por WhatsApp')
            ->set('data.recordatorios_automaticos', true)
            ->call('save');

        $this->assertTrue((bool) $this->clinica->fresh()->recordatorios_automaticos);
    }

    public function test_si_el_servidor_no_lo_tiene_prendido_ni_se_ofrece(): void
    {
        config(['services.whatsapp.automaticos' => false]);
        $this->actingAs($this->usuario);

        Livewire::test(ClinicSettings::class)->assertDontSee('Recordatorios automáticos por WhatsApp');
    }

    // ── El envío ────────────────────────────────────────────────

    public function test_manda_la_plantilla_un_dia_antes_y_la_marca(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);
        $cita = $this->cita('2026-10-15 11:00');

        $this->artisan('docfacil:send-reminders')->assertSuccessful();

        $envios = $this->enviosAMeta();
        $this->assertCount(1, $envios);
        $this->assertSame('526681234567', $envios[0]['to']);
        $this->assertSame('docfacil_recordatorio_cita', $envios[0]['template']['name']);
        $this->assertNotNull($cita->fresh()->reminder_24h_sent_at);
        $this->assertTrue($cita->fresh()->reminder_sent);
    }

    public function test_al_nino_le_llega_a_su_mama(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);
        $mateo = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Mateo', 'last_name' => 'Ruiz', 'responsable_id' => $this->ana->id]);
        $this->cita('2026-10-15 11:00', $mateo);

        $this->artisan('docfacil:send-reminders');

        $this->assertSame('526681234567', $this->enviosAMeta()[0]['to']);
    }

    public function test_no_manda_si_el_consultorio_no_lo_prendio_o_el_servidor_esta_apagado(): void
    {
        Http::fake();
        $this->cita('2026-10-15 11:00');

        $this->clinica->update(['recordatorios_automaticos' => false]);
        $this->artisan('docfacil:send-reminders');
        $this->clinica->update(['recordatorios_automaticos' => true]);
        config(['services.whatsapp.automaticos' => false]);
        $this->artisan('docfacil:send-reminders');

        $this->assertSame([], $this->enviosAMeta());
    }

    public function test_no_repite_a_quien_ya_se_le_mando_a_mano(): void
    {
        Http::fake();
        $this->cita('2026-10-15 11:00', null, ['reminder_sent' => true, 'reminder_sent_at' => now()]);

        $this->artisan('docfacil:send-reminders');

        $this->assertSame([], $this->enviosAMeta());
    }

    public function test_respeta_el_tope_diario_del_consultorio(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);
        $this->clinica->update(['recordatorios_por_dia' => 2]);
        foreach (range(1, 4) as $i) {
            $p = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => "P{$i}", 'last_name' => 'X', 'phone' => '668123450' . $i]);
            $this->cita('2026-10-15 ' . (9 + $i) . ':00', $p);
        }

        $this->artisan('docfacil:send-reminders');

        $this->assertCount(2, $this->enviosAMeta());
    }

    // ── Los botones ─────────────────────────────────────────────

    private function tocar(Appointment $cita, string $accion, ?string $firma = null)
    {
        $payload = PlantillasDeWhatsapp::payload($cita, $accion);
        if ($firma !== null) {
            $payload = preg_replace('/:[^:]+$/', ':' . $firma, $payload);
        }
        $cuerpo = json_encode(['entry' => [['changes' => [['value' => ['messages' => [[
            'from' => '526681234567', 'id' => 'wamid.r', 'type' => 'button',
            'button' => ['text' => $accion === 'confirmar' ? 'Confirmo' : 'Necesito cambiar', 'payload' => $payload],
        ]]]]]]]]);

        return $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => 'sha256=' . hash_hmac('sha256', $cuerpo, 'secreto'),
        ], $cuerpo);
    }

    public function test_confirmo_confirma_la_cita_y_le_contesta(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.2']]])]);
        $cita = $this->cita('2026-10-15 11:00');

        $this->tocar($cita, 'confirmar')->assertOk();

        $this->assertSame('confirmed', $cita->fresh()->status);
        $this->assertNotNull($cita->fresh()->confirmed_at);
        $this->assertCount(1, $this->enviosAMeta(), 'Le contesta que quedó confirmada');
    }

    public function test_necesito_cambiar_le_avisa_al_consultorio(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.2']]])]);
        $cita = $this->cita('2026-10-15 11:00');

        $this->tocar($cita, 'cambiar')->assertOk();

        $this->assertSame('scheduled', $cita->fresh()->status);
        $this->assertStringContainsString('quiere cambiar', json_encode($this->usuario->fresh()->notifications->first()?->data, JSON_UNESCAPED_UNICODE));
    }

    public function test_un_boton_con_la_firma_alterada_no_hace_nada(): void
    {
        Http::fake();
        $cita = $this->cita('2026-10-15 11:00');

        $this->tocar($cita, 'confirmar', 'falsa')->assertOk();

        $this->assertSame('scheduled', $cita->fresh()->status);
    }

    // ── El aviso de privacidad ──────────────────────────────────

    public function test_el_aviso_de_privacidad_dice_que_pasa_por_meta_cuando_esta_prendido(): void
    {
        $this->get(\App\Support\AvisoDePrivacidad::urlDelConsultorio($this->clinica))->assertOk()->assertSee('Meta');

        $this->clinica->update(['recordatorios_automaticos' => false]);
        $this->get(\App\Support\AvisoDePrivacidad::urlDelConsultorio($this->clinica))->assertOk()->assertDontSee('Meta Platforms');
    }
}
