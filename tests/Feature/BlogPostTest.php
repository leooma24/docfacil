<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El blog pasó de un arreglo de PHP en el controlador a una tabla con editor
 * en el panel. La parte delicada es la forma del cuerpo: el editor entrega
 * los bloques envueltos en 'data', y con listas y tablas escritas como texto,
 * mientras que la vista los lee planos. Si esa traducción se rompe, el
 * artículo publicado se corrompe en silencio al guardarlo.
 */
class BlogPostTest extends TestCase
{
    use RefreshDatabase;

    private function crear(array $atributos = []): BlogPost
    {
        return BlogPost::create(array_merge([
            'title' => 'Artículo de prueba',
            'description' => 'Una descripción de prueba.',
            'content' => [['type' => 'p', 'text' => 'Hola.']],
            'published_at' => now()->subDay(),
        ], $atributos));
    }

    // ── La forma del cuerpo ──────────────────────────────────────

    public function test_desenvuelve_los_bloques_que_entrega_el_editor(): void
    {
        $post = $this->crear(['content' => [
            ['type' => 'p', 'data' => ['text' => 'Un párrafo.']],
            ['type' => 'h2', 'data' => ['text' => 'Un subtítulo.']],
        ]]);

        $this->assertSame([
            ['type' => 'p', 'text' => 'Un párrafo.'],
            ['type' => 'h2', 'text' => 'Un subtítulo.'],
        ], $post->fresh()->content);
    }

    public function test_la_lista_escrita_como_texto_se_guarda_como_viñetas(): void
    {
        $post = $this->crear(['content' => [
            ['type' => 'ul', 'data' => ['lista' => "Primera\nSegunda\n\nTercera"]],
        ]]);

        $this->assertSame(
            [['type' => 'ul', 'items' => ['Primera', 'Segunda', 'Tercera']]],
            $post->fresh()->content,
            'Los renglones vacíos deben descartarse.'
        );
    }

    public function test_la_tabla_escrita_con_pipes_se_parte_en_encabezado_y_filas(): void
    {
        $post = $this->crear(['content' => [
            ['type' => 'table', 'data' => [
                'tabla' => "Nivel | Costo\nBásico | \$250,000\nPro | \$700,000",
                'caption' => 'Pesos mexicanos.',
            ]],
        ]]);

        $this->assertSame([[
            'type' => 'table',
            'head' => ['Nivel', 'Costo'],
            'rows' => [['Básico', '$250,000'], ['Pro', '$700,000']],
            'caption' => 'Pesos mexicanos.',
        ]], $post->fresh()->content);
    }

    public function test_guardar_dos_veces_no_deforma_el_contenido(): void
    {
        // El caso que rompía: abrir un artículo publicado, no tocar nada y
        // guardar. Tiene que quedar idéntico.
        $post = $this->crear(['content' => [
            ['type' => 'p', 'text' => 'Sin cambios.'],
            ['type' => 'ul', 'items' => ['Uno', 'Dos']],
            ['type' => 'table', 'head' => ['A', 'B'], 'rows' => [['1', '2']], 'caption' => null],
        ]]);

        $original = $post->fresh()->content;

        $post->touch();
        $post->save();

        $this->assertSame($original, $post->fresh()->content);
    }

    // ── Publicación ──────────────────────────────────────────────

    public function test_un_borrador_no_sale_en_el_sitio(): void
    {
        // Contamos sobre este articulo y no sobre el total, porque la
        // migracion que trajo los 6 articulos originales tambien corre aqui.
        $borrador = $this->crear(['title' => 'Borrador', 'published_at' => null]);

        $this->assertFalse($borrador->estaPublicado());
        $this->assertFalse(BlogPost::publicados()->where('id', $borrador->id)->exists());
        $this->get('/blog')->assertSuccessful()->assertDontSee('Borrador');
        $this->get("/blog/{$borrador->slug}")->assertNotFound();
    }

    public function test_un_articulo_programado_no_sale_antes_de_su_fecha(): void
    {
        $post = $this->crear(['title' => 'Programado', 'published_at' => now()->addWeek()]);

        $this->assertFalse($post->estaPublicado());
        $this->assertFalse(BlogPost::publicados()->where('id', $post->id)->exists());
        $this->get("/blog/{$post->slug}")->assertNotFound();
    }

    public function test_un_articulo_publicado_si_se_ve(): void
    {
        $post = $this->crear(['title' => 'Publicado y visible']);

        $this->get("/blog/{$post->slug}")
            ->assertSuccessful()
            ->assertSee('Publicado y visible');
    }

    // ── Detalles que ahorran trabajo al escribir ─────────────────

    public function test_el_slug_sale_solo_del_titulo(): void
    {
        $post = $this->crear(['title' => 'Cuánto cuesta abrir un consultorio']);

        $this->assertSame('cuanto-cuesta-abrir-un-consultorio', $post->slug);
    }

    public function test_dos_titulos_iguales_no_chocan_de_slug(): void
    {
        $a = $this->crear(['title' => 'Mismo título']);
        $b = $this->crear(['title' => 'Mismo título']);

        $this->assertSame('mismo-titulo', $a->slug);
        $this->assertSame('mismo-titulo-2', $b->slug);
    }

    public function test_el_tiempo_de_lectura_se_calcula_si_se_deja_vacio(): void
    {
        $post = $this->crear(['content' => [
            ['type' => 'p', 'text' => str_repeat('palabra ', 600)],
        ]]);

        $this->assertSame('3 min', $post->read_time);
    }

    public function test_el_sitemap_recoge_los_publicados_y_no_los_borradores(): void
    {
        $publicado = $this->crear(['title' => 'Sale en el sitemap']);
        $borrador = $this->crear(['title' => 'No sale', 'published_at' => null]);

        $xml = $this->get('/sitemap.xml')->assertSuccessful()->getContent();

        $this->assertStringContainsString("/blog/{$publicado->slug}", $xml);
        $this->assertStringNotContainsString("/blog/{$borrador->slug}", $xml);
    }

    // ── El editor del panel ──────────────────────────────────────

    /**
     * Escribir un artículo desde el panel, que es el punto de todo esto:
     * publicar sin editar código ni desplegar.
     */
    public function test_se_puede_publicar_un_articulo_desde_el_panel(): void
    {
        $admin = \App\Models\User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        $this->actingAs($admin);

        \Livewire\Livewire::test(\App\Filament\Resources\BlogPostResource\Pages\CreateBlogPost::class)
            ->fillForm([
                'title' => 'Escrito desde el panel',
                'slug' => 'escrito-desde-el-panel',
                'description' => 'Un artículo capturado en el editor, sin tocar código.',
                'category' => 'Finanzas',
                'published_at' => now()->subMinute(),
                'content' => [
                    ['type' => 'p', 'data' => ['text' => 'Primer párrafo del artículo.']],
                    ['type' => 'ul', 'data' => ['lista' => "Una cosa\nOtra cosa"]],
                    ['type' => 'table', 'data' => [
                        'tabla' => "Concepto | Costo\nRenta | \$12,000",
                        'caption' => 'Al mes.',
                    ]],
                    ['type' => 'cta', 'data' => ['text' => 'Pruébalo gratis.']],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = BlogPost::where('slug', 'escrito-desde-el-panel')->firstOrFail();

        // Guardado en la forma plana que lee la vista del blog.
        $this->assertSame([
            ['type' => 'p', 'text' => 'Primer párrafo del artículo.'],
            ['type' => 'ul', 'items' => ['Una cosa', 'Otra cosa']],
            ['type' => 'table', 'head' => ['Concepto', 'Costo'], 'rows' => [['Renta', '$12,000']], 'caption' => 'Al mes.'],
            ['type' => 'cta', 'text' => 'Pruébalo gratis.'],
        ], $post->content);

        // Y ya se ve publicado en el sitio, con su tabla y su lista.
        $this->get('/blog/escrito-desde-el-panel')
            ->assertSuccessful()
            ->assertSee('Primer párrafo del artículo.')
            ->assertSee('Una cosa')
            ->assertSee('Renta')
            ->assertSee('Al mes.');
    }
}
