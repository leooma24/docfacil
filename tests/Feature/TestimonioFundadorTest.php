<?php

namespace Tests\Feature;

use App\Filament\Doctor\Widgets\TestimonioFundadorWidget;
use App\Mail\TestimonioDeFundadorMail;
use App\Models\Clinic;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Livewire\Features\SupportTesting\Testable;
use Tests\TestCase;

/**
 * La frase del fundador a los 30 días.
 *
 * A los anuncios les faltan testimonios, e inventarlos está prohibido. Cada
 * fundador puede dejar uno real — con permiso por escrito, y sin que se le
 * pregunte a cada rato.
 */
class TestimonioFundadorTest extends TestCase
{
    use RefreshDatabase;

    private const FRASE = 'Antes llevaba la agenda en una libreta; ahora veo mi día completo desde el celular.';

    private Clinic $clinica;

    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config(['founders.avisar_a' => ['omar@test.com']]);

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Fundador',
            'slug' => 'consultorio-fundador',
            'city' => 'Culiacán',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addMonths(6),
            'is_beta' => true,
            'is_founder' => true,
            'founder_price' => 499,
            'beta_starts_at' => now()->subDays(31),
            'beta_ends_at' => now()->addMonths(5),
            'onboarding_status' => 'completed',
        ]);

        $this->doctor = User::forceCreate([
            'name' => 'Dra. Laura Méndez',
            'email' => 'laura@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);
    }

    private function widget(): Testable
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        return Livewire::test(TestimonioFundadorWidget::class);
    }

    // ── Cuándo se le pide ────────────────────────────────────────

    public function test_a_los_30_dias_se_le_pide_su_frase(): void
    {
        $this->assertTrue($this->clinica->tocaPedirTestimonio());
    }

    public function test_antes_de_los_30_dias_todavia_no(): void
    {
        // Todavía no lo ha usado lo suficiente para decir algo que valga.
        $this->clinica->update(['beta_starts_at' => now()->subDays(20)]);

        $this->assertFalse($this->clinica->fresh()->tocaPedirTestimonio());
    }

    public function test_a_quien_no_es_fundador_no_se_le_pide(): void
    {
        $this->clinica->update(['is_founder' => false]);

        $this->assertFalse($this->clinica->fresh()->tocaPedirTestimonio());
    }

    public function test_si_ya_hay_frase_no_se_le_vuelve_a_pedir(): void
    {
        // Pudo haberla capturado Omar de un WhatsApp.
        $this->clinica->update(['case_study_testimonial' => 'Muy buen sistema, se lo recomiendo a mis colegas.']);

        $this->assertFalse($this->clinica->fresh()->tocaPedirTestimonio());
    }

    public function test_solo_se_le_muestra_al_doctor(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));

        $this->actingAs($this->doctor);
        $this->assertTrue(TestimonioFundadorWidget::canView());

        $recepcion = User::forceCreate([
            'name' => 'Recepción',
            'email' => 'recepcion@test.com',
            'password' => bcrypt('password'),
            'role' => 'receptionist',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $this->actingAs($recepcion);
        $this->assertFalse(TestimonioFundadorWidget::canView());
    }

    // ── Lo que deja ──────────────────────────────────────────────

    public function test_la_firma_viene_armada_con_su_nombre_consultorio_y_ciudad(): void
    {
        $this->widget()->assertSet('firma', 'Dra. Laura Méndez · Consultorio Fundador · Culiacán');
    }

    public function test_el_permiso_nunca_viene_marcado(): void
    {
        // Un permiso que el doctor no tocó no es permiso.
        $this->widget()->assertSet('permiso', false);
    }

    public function test_con_permiso_se_guarda_la_frase_y_cuando_lo_dio(): void
    {
        $this->widget()
            ->set('frase', self::FRASE)
            ->set('permiso', true)
            ->call('enviar')
            ->assertHasNoErrors()
            ->assertSet('enviado', true);

        $clinica = $this->clinica->fresh();

        $this->assertSame(self::FRASE, $clinica->case_study_testimonial);
        $this->assertSame('Dra. Laura Méndez · Consultorio Fundador · Culiacán', $clinica->testimonio_firma);
        $this->assertNotNull($clinica->testimonio_permiso_at);
        $this->assertFalse($clinica->tocaPedirTestimonio());
    }

    public function test_sin_marcar_la_casilla_la_frase_se_guarda_pero_sin_permiso(): void
    {
        $this->widget()
            ->set('frase', self::FRASE)
            ->call('enviar')
            ->assertHasNoErrors();

        $clinica = $this->clinica->fresh();

        $this->assertSame(self::FRASE, $clinica->case_study_testimonial);
        $this->assertNull($clinica->testimonio_permiso_at);
    }

    public function test_a_omar_le_llega_la_frase_y_el_asunto_dice_si_tiene_permiso(): void
    {
        $this->widget()->set('frase', self::FRASE)->set('permiso', true)->call('enviar');

        Mail::assertSent(TestimonioDeFundadorMail::class, fn (TestimonioDeFundadorMail $correo) => $correo->hasTo('omar@test.com')
            && str_contains($correo->envelope()->subject, 'CON permiso'));
    }

    public function test_sin_permiso_el_asunto_lo_advierte(): void
    {
        // Para que no se use por error en un anuncio.
        $this->widget()->set('frase', self::FRASE)->call('enviar');

        Mail::assertSent(TestimonioDeFundadorMail::class, fn (TestimonioDeFundadorMail $correo) => str_contains($correo->envelope()->subject, 'SIN permiso'));
    }

    public function test_dos_palabras_no_son_una_frase(): void
    {
        $this->widget()
            ->set('frase', 'Muy bueno')
            ->call('enviar')
            ->assertHasErrors(['frase' => 'min']);

        $this->assertNull($this->clinica->fresh()->case_study_testimonial);
        Mail::assertNothingSent();
    }

    public function test_el_correo_a_omar_trae_la_frase_y_el_permiso(): void
    {
        $this->clinica->update([
            'case_study_testimonial' => self::FRASE,
            'testimonio_firma' => 'Dra. Laura Méndez',
            'testimonio_permiso_at' => now(),
        ]);

        $html = (new TestimonioDeFundadorMail($this->clinica->fresh()))->render();

        $this->assertStringContainsString('ahora veo mi día completo', $html);
        $this->assertStringContainsString('Dio permiso de publicarla', $html);
    }

    public function test_el_admin_ve_cuando_una_frase_no_tiene_permiso(): void
    {
        $this->clinica->update(['case_study_testimonial' => self::FRASE]);

        $admin = User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/clinics/' . $this->clinica->id . '/edit')
            ->assertOk()
            ->assertSee('NO dio permiso de publicarla');
    }

    // ── Que no se le pregunte a cada rato ────────────────────────

    public function test_ahora_no_se_le_vuelve_a_preguntar_en_dos_semanas(): void
    {
        $this->widget()->call('despues')->assertSet('oculto', true);

        $this->assertFalse($this->clinica->fresh()->tocaPedirTestimonio());

        $this->travel(15)->days();

        $this->assertTrue($this->clinica->fresh()->tocaPedirTestimonio());
    }

    public function test_no_volver_a_preguntar_es_para_siempre(): void
    {
        $this->widget()->call('noPreguntar')->assertSet('oculto', true);

        $this->travel(1)->years();

        $this->assertFalse($this->clinica->fresh()->tocaPedirTestimonio());
    }
}
