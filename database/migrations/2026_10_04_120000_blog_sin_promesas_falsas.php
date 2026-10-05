<?php

use App\Http\Controllers\BlogController;
use App\Models\BlogPost;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * Corrige los artículos del blog que ya están en producción.
 *
 * Revisión del 4-oct-2026: el blog prometía WhatsApp automático, recetas por
 * WhatsApp y un odontograma "compartible", traía cifras sin fuente (40%, 95%,
 * 30%, 85%, 25%, $25,000+), nombraba a la competencia, hablaba a "médicos" y
 * decía 14 días de prueba. El texto corregido vive en
 * BlogController::articulosHeredados(); esta migración lo copia a las filas.
 *
 * Cómo decide:
 *  - Si el artículo no existe (lo borraron en el panel), no lo crea.
 *  - Solo lo reescribe si su título, descripción o cuerpo todavía dicen
 *    alguna de las frases falsas de abajo. Si ya no las dicen, alguien lo
 *    reescribió a mano en el panel y se respeta tal cual (queda en el log).
 *  - Cuando lo reescribe cambia título, descripción y cuerpo completos
 *    (también pasa el artículo de tú a de usted). Lo que había antes queda
 *    en el log, por si alguna edición hecha a mano se quiere recuperar.
 *  - No toca slug, imagen, fecha, categoría ni tiempo de lectura.
 *
 * down() no hace nada: regresar al texto falso no es algo que se quiera, y
 * el contenido anterior quedó en el log.
 */
return new class extends Migration
{
    /** Frases que solo traía el texto viejo, por artículo. */
    private const FRASES_VIEJAS = [
        'cuanto-cuesta-abrir-consultorio-dental-mexico' => [
            'entre 10,800 y 18,000',
            'manda los recordatorios por WhatsApp',
        ],
        'como-reducir-inasistencias-consultorio' => [
            '40%', '95%', '30%', '85%', '$7,000', 'mensaje automático', 'confirmación automática',
            '2 horas antes', 'médico o dentista',
        ],
        'software-consultorio-medico-mexico-guia' => [
            'iPraxis', 'Dentrix', 'Eaglesoft', '$25,000', 'entre $100 y $500', 'consultorio médico',
        ],
        'expediente-clinico-digital-nom-004' => [
            'Si eres médico o dentista',
            'y el paciente firma sus consentimientos en pantalla',
        ],
        'recetas-electronicas-mexico-guia-completa' => [
            'en 10 segundos', 'por WhatsApp', 'para médicos',
        ],
        'odontograma-digital-beneficios-dentistas' => [
            '14 días', 'compartible con el paciente', '25%', '10 segundos vs 2 minutos',
        ],
    ];

    public function up(): void
    {
        if (! method_exists(BlogController::class, 'articulosHeredados')) {
            return;
        }

        $corregidos = BlogController::articulosHeredados();

        foreach (self::FRASES_VIEJAS as $slug => $frases) {
            $post = BlogPost::where('slug', $slug)->first();
            $nuevo = $corregidos[$slug] ?? null;

            if (! $post || ! $nuevo) {
                continue;
            }

            $texto = $this->textoDe($post);
            $quedan = array_values(array_filter(
                $frases,
                fn (string $frase) => mb_stripos($texto, $frase) !== false
            ));

            if ($quedan === []) {
                Log::info("Blog sin promesas falsas: '{$slug}' ya no trae el texto viejo, se deja como está.");

                continue;
            }

            Log::info("Blog sin promesas falsas: se reescribe '{$slug}' (decía: " . implode(' | ', $quedan) . ').', [
                'antes' => [
                    'title' => $post->title,
                    'description' => $post->description,
                    'content' => $post->content,
                ],
            ]);

            $post->update([
                'title' => $nuevo['title'],
                'description' => $nuevo['description'],
                'content' => $nuevo['content'],
            ]);
        }
    }

    public function down(): void
    {
        // A propósito vacío: ver el comentario de arriba.
    }

    /** Todo el texto visible del artículo, en una sola cadena. */
    private function textoDe(BlogPost $post): string
    {
        $partes = [$post->title, $post->description];

        $contenido = $post->content ?? [];
        array_walk_recursive($contenido, function ($valor) use (&$partes) {
            if (is_string($valor)) {
                $partes[] = $valor;
            }
        });

        return implode(' ', $partes);
    }
};
