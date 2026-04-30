<?php

namespace Tests\Feature;

use App\Mail\LeadHeatedUpMail;
use App\Models\Prospect;
use App\Models\ProspectEmailEvent;
use App\Services\LeadScoringService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

/**
 * Tests del flujo end-to-end de alerta "lead caliente". Bug aquí = spam
 * a Omar cada cron run, O peor: lead caliente sin alertar y se enfría.
 *
 * Por defecto desactivamos WhatsApp (token vacío) para aislar el flow de
 * email. Tests específicos lo re-habilitan via mock.
 */
class LeadHotAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config([
            'services.notifications.emails' => 'omar@test.local',
            'services.notifications.phone' => '',           // sin phone = no intenta WA
            'services.whatsapp.token' => '',                 // sin token = no intenta WA
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Prospect calibrado para cruzar a HOT (≥80) cuando se le agreguen
     * clicks o demo. Sin clicks queda en 75 (TIBIO), con clicks llega a HOT.
     */
    private function makeHighFitProspect(array $extra = []): Prospect
    {
        return Prospect::create(array_merge([
            'name' => 'Dr. Lead Caliente',
            'email' => 'caliente_' . uniqid('', true) . '@test.local',
            'specialty' => 'Odontología General', // +30
            'city' => 'Hermosillo',                 // +10
            'phone' => '6681111111',                // contact = +10
            'status' => 'interested',               // +15
            'demo_scheduled_at' => now(),           // +10
        ], $extra));
    }

    private function addClicks(Prospect $p, int $n): void
    {
        for ($i = 0; $i < $n; $i++) {
            ProspectEmailEvent::create([
                'prospect_id' => $p->id,
                'email_type' => 'beta_invite',
                'event_type' => 'click',
            ]);
        }
    }

    /** @test */
    public function crossing_hot_threshold_first_time_dispatches_alert_and_sets_hot_alerted_at(): void
    {
        $p = $this->makeHighFitProspect();
        $this->addClicks($p, 3);

        $result = app(LeadScoringService::class)->updateAndNotify($p->fresh());

        $this->assertGreaterThanOrEqual(80, $result['new'], 'Score debe haber cruzado a HOT (sanity check)');
        $this->assertTrue($result['alerted']);
        $this->assertNotNull($p->fresh()->hot_alerted_at);
        Mail::assertSent(LeadHeatedUpMail::class, 1);
    }

    /** @test */
    public function staying_hot_does_NOT_redispatch_alert(): void
    {
        $p = $this->makeHighFitProspect();
        $this->addClicks($p, 3);

        // Primera llamada: alerta
        app(LeadScoringService::class)->updateAndNotify($p->fresh());
        Mail::assertSent(LeadHeatedUpMail::class, 1);

        // Segunda llamada: sigue caliente, NO debe re-alertar
        $result = app(LeadScoringService::class)->updateAndNotify($p->fresh());
        $this->assertFalse($result['alerted'], 'Una vez alertado no se re-alerta hasta que enfríe');
        Mail::assertSent(LeadHeatedUpMail::class, 1); // sigue siendo 1
    }

    /** @test */
    public function cooling_below_warm_resets_hot_alerted_at_so_recalentamiento_alerta_otra_vez(): void
    {
        $p = $this->makeHighFitProspect();
        $this->addClicks($p, 3);

        // Primera alerta
        app(LeadScoringService::class)->updateAndNotify($p->fresh());
        $this->assertNotNull($p->fresh()->hot_alerted_at);

        // El lead enfría: lo marcamos como lost (override → score = 0, debajo de WARM=50)
        $p->update(['status' => 'lost']);
        app(LeadScoringService::class)->updateAndNotify($p->fresh());
        $this->assertNull($p->fresh()->hot_alerted_at, 'Enfriar debajo de WARM debe resetear hot_alerted_at');

        // Lo "revivimos" — vuelve a interested → nueva alerta
        $p->update(['status' => 'interested']);
        $result = app(LeadScoringService::class)->updateAndNotify($p->fresh());

        $this->assertTrue($result['alerted'], 'Después de enfriar y volver a calentar, debe alertar de nuevo');
        Mail::assertSent(LeadHeatedUpMail::class, 2);
    }

    /** @test */
    public function cooling_to_just_below_hot_but_above_warm_does_NOT_reset(): void
    {
        $p = $this->makeHighFitProspect();
        $this->addClicks($p, 3);

        app(LeadScoringService::class)->updateAndNotify($p->fresh());
        $this->assertNotNull($p->fresh()->hot_alerted_at);

        // Quitamos demo (-10) y status pasa a contacted (-10 vs interested).
        // Sigue siendo TIBIO (≥50), NO debe resetear hot_alerted_at.
        $p->update(['demo_scheduled_at' => null, 'status' => 'contacted']);
        app(LeadScoringService::class)->updateAndNotify($p->fresh());

        $this->assertNotNull($p->fresh()->hot_alerted_at,
            'Si solo enfría a TIBIO (no <WARM), hot_alerted_at NO debe resetearse');
    }

    /** @test */
    public function alert_only_marks_hot_alerted_at_if_at_least_one_channel_succeeded(): void
    {
        // Re-habilitar WhatsApp pero hacer que falle
        config([
            'services.notifications.phone' => '6681234567',
            'services.whatsapp.token' => 'fake-token',
        ]);
        $waMock = Mockery::mock(WhatsAppService::class);
        $waMock->shouldReceive('sendMessage')->andReturn(false); // WA falla
        $this->app->instance(WhatsAppService::class, $waMock);

        // Email también falla — Mail::shouldReceive sobreescribe el fake
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $p = $this->makeHighFitProspect();
        $this->addClicks($p, 3);

        $result = app(LeadScoringService::class)->updateAndNotify($p->fresh());

        $this->assertGreaterThanOrEqual(80, $result['new'], 'Score sí debe actualizarse aunque alertas fallen');
        $this->assertFalse($result['alerted']);
        $this->assertNull($p->fresh()->hot_alerted_at,
            'Si TODOS los canales fallan, hot_alerted_at queda null para reintento');
    }

    /** @test */
    public function alert_marks_hot_alerted_at_if_only_email_succeeds(): void
    {
        // WhatsApp habilitado pero falla. Email funciona via Mail::fake() del setUp.
        config([
            'services.notifications.phone' => '6681234567',
            'services.whatsapp.token' => 'fake-token',
        ]);
        $waMock = Mockery::mock(WhatsAppService::class);
        $waMock->shouldReceive('sendMessage')->andReturn(false);
        $this->app->instance(WhatsAppService::class, $waMock);

        $p = $this->makeHighFitProspect();
        $this->addClicks($p, 3);

        $result = app(LeadScoringService::class)->updateAndNotify($p->fresh());

        $this->assertTrue($result['alerted'], 'Si email salió, alertado=true aunque WA falle');
        $this->assertNotNull($p->fresh()->hot_alerted_at);
        Mail::assertSent(LeadHeatedUpMail::class, 1);
    }

    /** @test */
    public function low_score_lead_does_not_trigger_alert(): void
    {
        // Anti-persona, sin engagement → score bajo
        $p = Prospect::create([
            'name' => 'Dr. Frío',
            'email' => 'frio_' . uniqid('', true) . '@test.local',
            'specialty' => 'Pediatría',
            'status' => 'new',
        ]);

        $result = app(LeadScoringService::class)->updateAndNotify($p);

        $this->assertLessThan(80, $result['new']);
        $this->assertFalse($result['alerted']);
        $this->assertNull($p->fresh()->hot_alerted_at);
        Mail::assertNothingSent();
    }
}
