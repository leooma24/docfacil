<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Un artículo del blog público.
 *
 * No usa BelongsToClinic: es contenido de marketing del sitio, no de un
 * consultorio, así que no se filtra por clinic_id.
 */
class BlogPost extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'description',
        'category',
        'cover_image',
        'read_time',
        'content',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /** Categorías que se ofrecen en el editor. */
    public const CATEGORIAS = [
        'Gestión' => 'Gestión',
        'Finanzas' => 'Finanzas',
        'Normatividad' => 'Normatividad',
        'Tecnología' => 'Tecnología',
        'Odontología' => 'Odontología',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if (empty($post->slug)) {
                $post->slug = static::slugDisponible($post->title);
            }

            // Si el autor no puso el tiempo de lectura, lo calculamos:
            // ~200 palabras por minuto, mínimo 1.
            if (empty($post->read_time)) {
                $post->read_time = max(1, (int) round($post->contarPalabras() / 200)) . ' min';
            }
        });
    }

    /**
     * Normaliza el cuerpo al guardarlo.
     *
     * El editor de Filament entrega los bloques como ['type' => x, 'data' => [...]],
     * y con las listas y tablas escritas como texto. La vista del blog los lee
     * planos (['type' => 'p', 'text' => ...]), asi que la traduccion vive aqui:
     * el modelo es el dueño de la forma de sus datos, y de paso queda igual
     * venga del editor, de un seeder o de una migracion.
     */
    public function setContentAttribute($valor): void
    {
        $bloques = collect(is_string($valor) ? json_decode($valor, true) : $valor)
            ->map(function ($bloque) {
                $bloque = (array) $bloque;
                $tipo = $bloque['type'] ?? 'p';
                $datos = array_key_exists('data', $bloque)
                    ? (array) $bloque['data']
                    : array_diff_key($bloque, ['type' => null]);

                if ($tipo === 'ul' && array_key_exists('lista', $datos)) {
                    $datos = ['items' => self::renglones($datos['lista'])];
                }

                if ($tipo === 'table' && array_key_exists('tabla', $datos)) {
                    $filas = collect(self::renglones($datos['tabla']))
                        ->map(fn (string $linea) => array_map('trim', explode('|', $linea)))
                        ->values();

                    $datos = [
                        'head' => $filas->first() ?? [],
                        'rows' => $filas->slice(1)->values()->all(),
                        'caption' => $datos['caption'] ?? null,
                    ];
                }

                return array_merge(['type' => $tipo], $datos);
            })
            ->values()
            ->all();

        $this->attributes['content'] = json_encode($bloques, JSON_UNESCAPED_UNICODE);
    }

    /** Parte un textarea en renglones, sin vacios. */
    private static function renglones(?string $texto): array
    {
        return collect(preg_split('/\R/', (string) $texto))
            ->map(fn (string $linea) => trim($linea))
            ->filter()
            ->values()
            ->all();
    }

    /** Slug único a partir del título, agregando sufijo si ya existe. */
    private static function slugDisponible(string $titulo): string
    {
        $base = Str::slug($titulo) ?: 'articulo';
        $slug = $base;
        $n = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$n}";
            $n++;
        }

        return $slug;
    }

    /** Solo los que ya salieron: publicados y con fecha que no sea futura. */
    public function scopePublicados(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }

    public function estaPublicado(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * Palabras del cuerpo, para el tiempo de lectura.
     */
    public function contarPalabras(): int
    {
        $texto = '';

        foreach ($this->content ?? [] as $bloque) {
            $texto .= ' ' . ($bloque['text'] ?? '');
            $texto .= ' ' . implode(' ', $bloque['items'] ?? []);

            foreach ($bloque['rows'] ?? [] as $fila) {
                $texto .= ' ' . implode(' ', (array) $fila);
            }
        }

        return str_word_count(strip_tags($texto));
    }

    /**
     * Forma que espera la vista del blog, que nació leyendo un arreglo.
     */
    public function paraLaVista(): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->cover_image,
            'date' => $this->published_at?->toDateString(),
            'read_time' => $this->read_time,
            'category' => $this->category,
            'content' => $this->content ?? [],
        ];
    }
}
