<?php

namespace Tests\Feature;

use App\Http\Controllers\BriefPdfController;
use App\Http\Controllers\BrochureController;
use App\Http\Controllers\ProposalPdfController;
use App\Models\BlogPost;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Prospect;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Los materiales de venta dicen lo que el sistema hace.
 *
 * Revisión del 4-oct-2026: el brief, el folleto, la propuesta, las páginas
 * por ciudad, las comparativas, la calculadora y el blog prometían cosas que
 * no existen (link de pago, recetas por WhatsApp, multi-sucursal, comisiones
 * entre doctores, correos automáticos, notas SOAP, recordatorio a las 2
 * horas, WhatsApp automático) y cifras inventadas ($15,000 al mes, 30% → 8%,
 * 40% menos, 8 horas a la semana). Decisiones de Omar: solo dentistas, Free
 * sí funciona después de la prueba, y nada de la competencia sin fuente.
 *
 * "Respaldo automático" sí se vale: el respaldo diario corre solo.
 */
class MaterialesDeVentaHonestosTest extends TestCase
{
    use RefreshDatabase;

    private const PROHIBIDO = [
        // Cifras sin fuente
        '$15,000', '$11,500', '$14,400', '$29K', '$29,000', '30% a 8%', '30% → 8%', '40%', '30%', '85%', '95%', '25%',
        '8 horas', '8 hrs', '10 hrs', '2 horas al día', '2+ horas', '$20K', '$200K', '30-200', '30–200', '$6-15', '15-25 citas',
        'Triplica', 'Triplicar', '2-3x', 'en 10 segundos', '$7,000', '$6,301', 'cobra por ti',
        // Lo que no existe
        'SOAP', 'link de pago', 'links de pago', 'link de cobro', 'links de cobro', 'Multi-sede', 'Multisede', 'sucursal',
        'comisiones', 'Correo automático', 'Pagos en línea', 'intermitente', 'recetas vencidas', 'sin límite',
        'Plantilla personalizada', 'WhatsApp Business', '2h antes', '2 horas antes', 'Soporte prioritario 24/7',
        'WhatsApp automático', 'mensaje automático', 'recordatorios automáticos', 'recordatorio automático',
        'confirmación automática', 'seguimientos post-consulta', 'firma consentimiento en tablet',
        'se envían al paciente por WhatsApp', 'lo recibe por WhatsApp', 'las recibe por WhatsApp',
        'Compartible con el paciente', 'paga sin salir',
        // Datos viejos
        '14 días', 'testimonios', 'hasta 30 pacientes',
        // Solo dentistas
        'médicos generales', 'médicos y dentistas', 'dentistas y médicos', 'consultorios médicos',
        // Competencia sin fuente y superlativos
        'Dentrix', 'Eaglesoft', 'DentalIntel', 'iPraxis', 'única plataforma', 'el único', 'No soportado',
        'zona horaria distinta', 'R$', 'USD',
    ];

    private function sinTrampas(string $html, string $donde): void
    {
        $texto = html_entity_decode(strip_tags(preg_replace(['/<script\b.*?<\/script>/is', '/<style\b.*?<\/style>/is'], '', $html)));

        foreach (self::PROHIBIDO as $frase) {
            $this->assertStringNotContainsStringIgnoringCase($frase, $texto, "{$donde} dice \"{$frase}\"");
        }
    }

    public static function paginasPublicas(): array
    {
        return [
            'brief' => ['/brief'],
            'folleto' => ['/brochure'],
            'ciudad' => ['/software-dental/culiacan'],
            'vs dentalink' => ['/vs/dentalink'],
            'vs doctorum' => ['/vs/doctorum'],
            'alternativas a dentalink' => ['/alternativas-a-dentalink'],
            'calculadora' => ['/herramientas/calculadora-consultorio'],
            'blog' => ['/blog'],
        ];
    }

    #[DataProvider('paginasPublicas')]
    public function test_la_pagina_publica_no_promete_de_mas(string $ruta): void
    {
        $this->sinTrampas($this->get($ruta)->assertOk()->getContent(), $ruta);
    }

    public function test_no_hay_comparativa_sin_fuente_contra_eaglesoft(): void
    {
        // Sin datos con fuente no hay nada que decir de Eaglesoft, y su
        // nombre no puede salir en la página: la comparativa ya no existe.
        $this->get('/vs/eaglesoft')->assertNotFound();
        $this->get('/alternativas-a-eaglesoft')->assertNotFound();
    }

    public function test_el_brief_en_pdf_no_promete_de_mas(): void
    {
        $datos = (new \ReflectionMethod(BriefPdfController::class, 'viewData'))->invoke(new BriefPdfController(), 'pdf');
        $this->sinTrampas(view('pdf.brief', $datos)->render(), 'brief.pdf');
    }

    public function test_el_folleto_en_pdf_no_promete_de_mas(): void
    {
        $datos = (new \ReflectionMethod(BrochureController::class, 'viewData'))->invoke(new BrochureController(), 'pdf');
        $this->sinTrampas(view('pdf.brochure', $datos)->render(), 'brochure.pdf');
    }

    public function test_la_propuesta_no_promete_de_mas(): void
    {
        $prospecto = Prospect::create(['name' => 'Dra. Prueba', 'phone' => '6681234567', 'specialty' => 'Odontología', 'source' => 'prospecting', 'status' => 'new']);

        $this->sinTrampas(view('pdf.proposal', ProposalPdfController::datos($prospecto, 'Omar'))->render(), 'propuesta');
    }

    public function test_cada_articulo_del_blog_no_promete_de_mas(): void
    {
        $articulos = BlogPost::query()->pluck('slug');
        $this->assertNotEmpty($articulos, 'El blog debería tener artículos');

        foreach ($articulos as $slug) {
            $this->sinTrampas($this->get('/blog/' . $slug)->assertOk()->getContent(), "/blog/{$slug}");
        }
    }

    public function test_la_pagina_de_planes_del_panel_no_promete_de_mas(): void
    {
        $clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'free', 'trial_ends_at' => now()->addDays(5), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $clinica->id]);
        Doctor::create(['user_id' => $user->id, 'clinic_id' => $clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));

        $this->sinTrampas($this->actingAs($user)->get('/doctor/actualizar-plan')->assertOk()->getContent(), 'actualizar-plan');
    }

    public function test_todos_los_materiales_listan_los_planes_de_la_misma_fuente(): void
    {
        $basico = \App\Support\LoQueTraeCadaPlan::plan('basico');

        $this->assertContains('Odontograma FDI interactivo', $basico['features']);
        $this->assertContains('Presupuestos que el paciente acepta en línea', $basico['features']);
        $this->assertStringContainsString('15 pacientes', \App\Support\LoQueTraeCadaPlan::plan('free')['limits']);
    }
}
