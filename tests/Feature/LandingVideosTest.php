<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los 3 videos de venta en la página, en orden y livianos: casi todo el que
 * llega viene de WhatsApp en su celular, así que no se baja nada hasta que
 * le da play.
 */
class LandingVideosTest extends TestCase
{
    use RefreshDatabase;

    private const VIDEOS = ['v1-consulta', 'v2-presupuesto', 'v3-ortodoncia'];

    public function test_los_tres_videos_en_orden_sin_descargarse_solos(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, '<video'));
        $this->assertSame(3, substr_count($html, 'preload="none"'));
        $this->assertSame(3, substr_count($html, 'playsinline'));

        $posiciones = array_map(fn ($v) => strpos($html, "videos/{$v}.mp4"), self::VIDEOS);
        $this->assertNotContains(false, $posiciones);
        $this->assertSame($posiciones, array_values(collect($posiciones)->sort()->all()));
    }

    public function test_los_archivos_existen_y_son_livianos(): void
    {
        foreach (self::VIDEOS as $v) {
            $mp4 = public_path("videos/{$v}.mp4");
            $poster = public_path("videos/{$v}-poster.jpg");
            $this->assertFileExists($mp4);
            $this->assertFileExists($poster);
            $this->assertLessThan(3 * 1024 * 1024, filesize($mp4), "{$v}.mp4 pesa demasiado para el celular");
            $this->assertLessThan(150 * 1024, filesize($poster));
        }
    }
}
