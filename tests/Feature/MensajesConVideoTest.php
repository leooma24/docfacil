<?php

namespace Tests\Feature;

use App\Filament\Sales\Pages\ColaDelDia;
use App\Filament\Sales\Resources\ProspectResource;
use App\Models\Prospect;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El primer mensaje corto y el segundo con video.
 *
 * Octubre 2026: casi nadie contestaba. El primer mensaje era un bloque de
 * texto (quién soy, el programa de fundador, la pregunta) y el segundo
 * repetía la misma pregunta. Ahora el primero es una sola pregunta, y el
 * segundo trae algo nuevo: el video que le toca según lo que hace.
 *
 * WhatsApp no deja adjuntar desde una liga, así que el CRM dice qué video
 * adjuntar y deja la liga para bajarlo al celular.
 */
class MensajesConVideoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $vendedor = User::forceCreate([
            'name' => 'Omar',
            'email' => 'ventas@test.com',
            'password' => bcrypt('password'),
            'role' => 'sales',
            'email_verified_at' => now(),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('ventas'));
        $this->actingAs($vendedor);
    }

    private function prospecto(array $datos = []): Prospect
    {
        static $n = 0;
        $n++;

        return Prospect::create(array_merge([
            'name' => "Dr. Carlos Pérez {$n}",
            'phone' => '66812345' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'city' => 'Los Mochis',
            'source' => 'prospecting',
            'status' => 'new',
            'has_whatsapp' => true,
            'notes' => '{"verificado_wa":true}',
            'contact_day' => 0,
        ], $datos));
    }

    private function mensaje(Prospect $p): string
    {
        return urldecode(ProspectResource::buildContextualWhatsappUrl($p));
    }

    // ── Primer mensaje: corto ────────────────────────────────────

    public function test_el_primer_mensaje_ya_no_trae_el_programa_de_fundador(): void
    {
        $msg = $this->mensaje($this->prospecto());

        $this->assertStringContainsString('Omar Lerma', $msg);
        $this->assertStringNotContainsString('primeros', $msg);
        $this->assertStringNotContainsString('busco', $msg);
    }

    public function test_el_primer_mensaje_conserva_la_salida(): void
    {
        $this->assertStringContainsString('no lo molesto más', $this->mensaje($this->prospecto()));
    }

    public function test_el_primer_mensaje_conserva_la_pregunta_de_su_especialidad(): void
    {
        $msg = $this->mensaje($this->prospecto(['specialty' => 'Ortodoncia']));

        $this->assertStringContainsString('ortodoncia', $msg);
        $this->assertStringContainsString('control', $msg);
    }

    public function test_al_consultorio_se_le_aclara_que_no_es_cita_y_se_le_habla_en_plural(): void
    {
        $msg = $this->mensaje($this->prospecto(['name' => 'Consultorio Dental Sonrisa']));

        $this->assertStringContainsString('no es para una cita', $msg);
        $this->assertStringContainsString('no los molesto más', $msg);
    }

    public function test_de_aqui_solo_se_le_dice_al_de_los_mochis(): void
    {
        $deAqui = $this->mensaje($this->prospecto(['city' => 'Los Mochis']));
        $deFuera = $this->mensaje($this->prospecto(['city' => 'Cuernavaca']));

        $this->assertStringContainsString('de aquí de Los Mochis', $deAqui);
        $this->assertStringNotContainsString('de aquí', $deFuera);
        $this->assertStringContainsString('ingeniero de Los Mochis', $deFuera);
    }

    // ── Segundo mensaje: el video ────────────────────────────────

    public function test_el_segundo_mensaje_trae_el_video_y_no_repite_la_pregunta(): void
    {
        $msg = $this->mensaje($this->prospecto(['contact_day' => 1]));

        $this->assertStringContainsString('video', $msg);
        $this->assertStringNotContainsString('¿cómo le hace hoy', $msg);
        $this->assertStringContainsString('no le vuelvo a escribir', $msg);
    }

    public function test_al_ortodoncista_le_toca_el_video_de_las_mensualidades(): void
    {
        $p = $this->prospecto(['contact_day' => 1, 'specialty' => 'Ortodoncia']);

        $this->assertSame('v3-ortodoncia.mp4', basename(ProspectResource::videoDelSeguimiento($p)['url']));
        $this->assertStringContainsString('brackets', $this->mensaje($p));
    }

    /**
     * El primer mensaje pregunta cómo recuerda las citas: el segundo enseña
     * eso. Y dice lo que de verdad pasa: se abre su WhatsApp y él da enviar.
     */
    public function test_a_los_demas_les_toca_el_video_de_los_recordatorios(): void
    {
        $p = $this->prospecto(['contact_day' => 1]);
        $msg = $this->mensaje($p);

        $this->assertSame('v4-recordatorios.mp4', basename(ProspectResource::videoDelSeguimiento($p)['url']));
        $this->assertStringContainsString('recordatorios', $msg);
        $this->assertStringContainsString('enviar', $msg);
        $this->assertStringNotContainsString('automátic', $msg);
    }

    public function test_al_consultorio_el_video_se_le_manda_para_el_doctor(): void
    {
        $msg = $this->mensaje($this->prospecto(['contact_day' => 1, 'name' => 'Clínica Dental Norte']));

        $this->assertStringContainsString('el doctor o la doctora', $msg);
        $this->assertStringContainsString('Si les hace sentido', $msg);
    }

    public function test_la_pregunta_empieza_con_mayuscula_despues_del_signo(): void
    {
        // Las dos preguntas generales se alternan por id par o impar.
        $dos = $this->mensaje($this->prospecto()) . $this->mensaje($this->prospecto());

        $this->assertStringContainsString('¿Cómo le hace hoy', $dos);
        $this->assertStringContainsString('¿Qué hace hoy', $dos);
    }

    public function test_los_otros_mensajes_no_piden_video(): void
    {
        $this->assertNull(ProspectResource::videoDelSeguimiento($this->prospecto(['contact_day' => 0])));
        $this->assertNull(ProspectResource::videoDelSeguimiento($this->prospecto(['contact_day' => 3])));
    }

    public function test_cada_video_de_la_lista_existe(): void
    {
        foreach (['Ortodoncia', null] as $especialidad) {
            $video = ProspectResource::videoDelSeguimiento($this->prospecto(['contact_day' => 1, 'specialty' => $especialidad]));
            $this->assertFileExists(public_path('videos/' . basename($video['url'])));
        }
    }

    // ── La cola dice qué adjuntar ────────────────────────────────

    public function test_la_cola_dice_que_video_adjuntar_en_el_seguimiento(): void
    {
        $this->prospecto([
            'contact_day' => 1,
            'specialty' => 'Ortodoncia',
            'last_contact_method' => 'whatsapp',
            'next_contact_at' => now()->subHour(),
            'assigned_to_sales_rep_id' => auth()->id(),
            'status' => 'contacted',
        ]);

        Livewire::test(ColaDelDia::class)
            ->assertSee('Adjunte el video')
            ->assertSee('Mensualidades de brackets')
            ->assertSee('videos/v3-ortodoncia.mp4');
    }

    // ── Solo dentistas, y solo lo que existe ─────────────────────

    public function test_el_crm_le_habla_a_consultorios_dentales_aunque_el_prospecto_no_diga_dental(): void
    {
        $msg = $this->mensaje($this->prospecto(['name' => 'Dr. Luis Mora', 'specialty' => 'Medicina general']));

        $this->assertStringContainsString('consultorios dentales', $msg);
        $this->assertStringNotContainsString('médicos', $msg);
    }

    public function test_el_mensaje_de_despues_no_promete_expediente_con_firma(): void
    {
        $msg = $this->mensaje($this->prospecto(['contact_day' => 14]));

        $this->assertStringNotContainsString('expediente con firma', $msg);
        $this->assertStringContainsString('consentimientos', $msg);
    }

}
