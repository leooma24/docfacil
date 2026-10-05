<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * En producción los artículos ya viven en blog_posts con el texto viejo
 * (WhatsApp automático, 40%, 95%, Dentrix...). Corregir el arreglo del
 * controlador no los toca: la migración tiene que hacerlo, y sin pisar lo
 * que Omar ya haya reescrito a mano desde el panel.
 */
class BlogSinPromesasFalsasMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRACION = 'database/migrations/2026_10_04_120000_blog_sin_promesas_falsas.php';

    private function correrMigracion(): void
    {
        (require base_path(self::MIGRACION))->up();
    }

    /** Deja el artículo como está hoy en producción. */
    private function ponerTextoViejo(string $slug, array $campos): BlogPost
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        $post->update($campos);

        return $post;
    }

    public function test_corrige_el_articulo_que_conserva_las_promesas_falsas(): void
    {
        $post = $this->ponerTextoViejo('como-reducir-inasistencias-consultorio', [
            'title' => 'Cómo reducir inasistencias en tu consultorio un 40%',
            'content' => [
                ['type' => 'p', 'text' => 'Si eres médico o dentista en México, probablemente pierdes entre 3 y 5 citas a la semana.'],
                ['type' => 'p', 'text' => 'El 95% de los mexicanos revisan WhatsApp. Un mensaje automático reduce inasistencias un 30% desde el primer mes.'],
                ['type' => 'cta', 'text' => 'Pruébalo 15 días gratis.'],
            ],
        ]);

        $this->correrMigracion();

        $post->refresh();
        $texto = $post->title . ' ' . json_encode($post->content, JSON_UNESCAPED_UNICODE);

        $this->assertSame('Cómo reducir inasistencias en su consultorio', $post->title);
        foreach (['40%', '95%', '30%', 'mensaje automático', 'médico'] as $frase) {
            $this->assertStringNotContainsString($frase, $texto);
        }
        $this->assertStringContainsString('a 1 clic desde su WhatsApp', $texto);
    }

    public function test_no_pisa_el_articulo_que_el_admin_ya_reescribio(): void
    {
        $editado = [
            ['type' => 'p', 'text' => 'Texto que Omar escribió en el panel.'],
            ['type' => 'cta', 'text' => 'Pruébelo gratis.'],
        ];
        $post = $this->ponerTextoViejo('odontograma-digital-beneficios-dentistas', [
            'title' => 'Mi título editado',
            'content' => $editado,
        ]);

        $this->correrMigracion();

        $post->refresh();
        $this->assertSame('Mi título editado', $post->title);
        $this->assertSame($editado, $post->content);
    }

    public function test_si_falta_el_articulo_no_lo_crea(): void
    {
        BlogPost::where('slug', 'recetas-electronicas-mexico-guia-completa')->delete();

        $this->correrMigracion();

        $this->assertFalse(BlogPost::where('slug', 'recetas-electronicas-mexico-guia-completa')->exists());
    }

    public function test_en_una_instalacion_nueva_no_cambia_nada(): void
    {
        $antes = BlogPost::orderBy('slug')->get(['slug', 'title', 'description', 'content'])->toArray();

        $this->correrMigracion();

        $this->assertSame($antes, BlogPost::orderBy('slug')->get(['slug', 'title', 'description', 'content'])->toArray());
    }
}
