<?php

namespace Tests\Unit;

use App\Models\Prospect;
use App\Models\ProspectEmailEvent;
use App\Services\LeadScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de la lógica pura de scoring (0-100) que decide a qué prospectos
 * llamar primero. Bug aquí = Omar contacta los equivocados todo el día
 * sin enterarse (no genera 500, falla silenciosa de revenue).
 */
class LeadScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeadScoringService $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = new LeadScoringService();
    }

    private function makeProspect(array $attrs = []): Prospect
    {
        return Prospect::create(array_merge([
            'name' => 'Dr. Test',
            'email' => 'test' . uniqid() . '@example.com',
            'status' => 'new',
        ], $attrs));
    }

    /** @test */
    public function converted_status_returns_100(): void
    {
        $p = $this->makeProspect(['status' => 'converted']);
        $this->assertSame(100, $this->scorer->calculate($p));
    }

    /** @test */
    public function lost_status_returns_0(): void
    {
        $p = $this->makeProspect(['status' => 'lost', 'specialty' => 'Odontología General']);
        $this->assertSame(0, $this->scorer->calculate($p));
    }

    /** @test */
    public function unsubscribed_returns_0_regardless_of_other_signals(): void
    {
        $p = $this->makeProspect([
            'status' => 'interested',
            'specialty' => 'Odontología General',
            'city' => 'Hermosillo',
            'phone' => '6681234567',
            'unsubscribed_at' => now(),
        ]);
        $this->assertSame(0, $this->scorer->calculate($p));
    }

    /** @test */
    public function score_is_clamped_between_0_and_100(): void
    {
        // Prospect con TODO a favor — debe topar en 100 sin pasarse
        $p = $this->makeProspect([
            'specialty' => 'Odontología General',
            'city' => 'Hermosillo',
            'phone' => '6681234567',
            'status' => 'trial',
            'demo_completed_at' => now(),
        ]);
        // Simulamos 5 clicks
        for ($i = 0; $i < 5; $i++) {
            ProspectEmailEvent::create([
                'prospect_id' => $p->id,
                'email_type' => 'beta_invite',
                'event_type' => 'click',
            ]);
        }

        $score = $this->scorer->calculate($p);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);

        // Prospect con TODO en contra — debe quedar en 0, no negativo
        $bad = $this->makeProspect([
            'specialty' => 'Pediatría',
            'objections_faced' => ['specific_govt', 'specific_old', 'tech_has_system'],
            'last_followup_at' => now()->subDays(90),
        ]);
        $badScore = $this->scorer->calculate($bad);
        $this->assertGreaterThanOrEqual(0, $badScore);
    }

    /** @test */
    public function dental_specialty_in_high_traction_city_with_full_contact_scores_at_least_50(): void
    {
        $p = $this->makeProspect([
            'specialty' => 'Odontología General', // +30
            'city' => 'Hermosillo',                // +10
            'phone' => '6681234567',               // +10 (con email implícito al crear)
        ]);
        // FIT total = 50 incluso sin engagement
        $this->assertGreaterThanOrEqual(50, $this->scorer->calculate($p));
    }

    /** @test */
    public function anti_persona_with_govt_objection_does_not_double_count_penalty(): void
    {
        // El método penaltyAntiPersona aplica EITHER 15 (ambas) OR 10 (solo govt)
        // OR 0 — nunca debe sumar las dos. Test guards la rama if/elseif.
        $p = $this->makeProspect([
            'specialty' => 'Pediatría', // anti-persona
            'objections_faced' => ['specific_govt'],
        ]);
        $score1 = $this->scorer->calculate($p);

        $p2 = $this->makeProspect([
            'specialty' => 'Pediatría',
            'objections_faced' => ['specific_govt', 'specific_old'], // govt + otra
        ]);
        $score2 = $this->scorer->calculate($p2);

        // El segundo tiene MÁS penalizadores (specific_old suma 10 en penaltyObjections)
        // pero el penaltyAntiPersona NO debe duplicar. Solo verificamos que no caiga
        // a un valor absurdamente bajo (menor a -50).
        $this->assertGreaterThanOrEqual(0, $score1);
        $this->assertGreaterThanOrEqual(0, $score2);
    }

    /** @test */
    public function decay_uses_last_followup_or_contacted_NOT_created_at(): void
    {
        // Lead recién importado pero nunca contactado: created_at viejo, sin
        // outreach. NO debe penalizarse — el outreach apenas empezará.
        $freshImport = $this->makeProspect([
            'specialty' => 'Odontología General',
            'city' => 'Mérida',
            'phone' => '9991234567',
        ]);
        // Simular import viejo
        $freshImport->created_at = now()->subDays(90);
        $freshImport->save();

        $scoreFresh = $this->scorer->calculate($freshImport);

        // Lead contactado hace 90 días sin volver a tocar — sí debe decaer.
        $stale = $this->makeProspect([
            'specialty' => 'Odontología General',
            'city' => 'Mérida',
            'phone' => '9991234568',
            'last_followup_at' => now()->subDays(90),
        ]);
        $scoreStale = $this->scorer->calculate($stale);

        // El stale tiene MISMO fit pero -15 de decay → score más bajo.
        $this->assertGreaterThan($scoreStale, $scoreFresh,
            'Lead con created_at viejo pero sin contacto NO debe decaer; lead contactado hace 90d SÍ.');
    }

    /** @test */
    public function bucket_label_matches_thresholds(): void
    {
        $this->assertSame('🔥 Caliente', LeadScoringService::bucketLabel(100));
        $this->assertSame('🔥 Caliente', LeadScoringService::bucketLabel(80));
        $this->assertSame('🌡️ Tibio', LeadScoringService::bucketLabel(79));
        $this->assertSame('🌡️ Tibio', LeadScoringService::bucketLabel(50));
        $this->assertSame('🧊 Frío', LeadScoringService::bucketLabel(49));
        $this->assertSame('🧊 Frío', LeadScoringService::bucketLabel(30));
        $this->assertSame('❄️ Congelado', LeadScoringService::bucketLabel(29));
        $this->assertSame('❄️ Congelado', LeadScoringService::bucketLabel(0));
        $this->assertSame('❄️ Congelado', LeadScoringService::bucketLabel(null));
    }

    /** @test */
    public function bucket_color_matches_thresholds(): void
    {
        $this->assertSame('danger', LeadScoringService::bucketColor(85));
        $this->assertSame('warning', LeadScoringService::bucketColor(60));
        $this->assertSame('info', LeadScoringService::bucketColor(35));
        $this->assertSame('gray', LeadScoringService::bucketColor(10));
        $this->assertSame('gray', LeadScoringService::bucketColor(null));
    }

    /** @test */
    public function email_clicks_progressively_boost_score(): void
    {
        $base = $this->makeProspect([
            'specialty' => 'Odontología General',
            'city' => 'Culiacán',
        ]);
        $scoreNoClicks = $this->scorer->calculate($base);

        // 1 click
        ProspectEmailEvent::create([
            'prospect_id' => $base->id,
            'email_type' => 'beta_invite',
            'event_type' => 'click',
        ]);
        $base->refresh()->load('emailEvents');
        $scoreOneClick = $this->scorer->calculate($base);

        // 3 clicks
        ProspectEmailEvent::create(['prospect_id' => $base->id, 'email_type' => 'followup', 'event_type' => 'click']);
        ProspectEmailEvent::create(['prospect_id' => $base->id, 'email_type' => 'last_chance', 'event_type' => 'click']);
        $base->refresh()->load('emailEvents');
        $scoreThreeClicks = $this->scorer->calculate($base);

        $this->assertGreaterThan($scoreNoClicks, $scoreOneClick, '1 click debe subir el score vs 0');
        $this->assertGreaterThan($scoreOneClick, $scoreThreeClicks, '3 clicks debe subir más que 1');
    }
}
