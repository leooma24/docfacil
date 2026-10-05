<?php

namespace App\Http\Controllers;

/**
 * Comparativas vs competidores. Páginas de búsqueda con el nombre del
 * competidor en la URL; el contenido es lo que DocFácil hace hoy.
 *
 * Rutas:
 *   /vs/{competitor}                 → DocFácil vs X (1-on-1)
 *   /alternativas-a-{competitor}     → DocFácil como alternativa a X
 *
 * Data centralizada en self::COMPETITORS — un solo lugar para actualizar.
 */
class ComparisonController extends Controller
{
    /**
     * Competidores con página. Solo el nombre: la página habla de lo que
     * DocFácil hace y manda al sitio oficial del competidor para lo suyo.
     *
     * Antes había precios en dólares y reales, años en el mercado, "no tiene
     * WhatsApp", "interfaz traducida"... nada con fuente. Decisión de Omar
     * (4-oct-2026): nada de la competencia sin fuente. Eaglesoft se quitó
     * (su /vs/ da 404): sin datos con fuente no había nada que decir de él.
     */
    private const COMPETITORS = [
        'dentalink' => ['name' => 'Dentalink'],
        'doctorum'  => ['name' => 'Doctorum'],
    ];

    /**
     * /vs/{competitor} — comparativa 1-a-1 DocFácil vs X.
     */
    public function versus(string $competitor)
    {
        $key = strtolower($competitor);
        if (! isset(self::COMPETITORS[$key])) {
            abort(404);
        }

        return view('comparison.versus', [
            'competitor' => self::COMPETITORS[$key],
            'slug'       => $key,
            'all_competitors' => $this->publicCompetitors(),
        ]);
    }

    /**
     * /alternativas-a-{competitor} — DocFácil como alternativa, con ligas a
     * las otras comparativas (sin datos de los demás).
     */
    public function alternatives(string $competitor)
    {
        $key = strtolower($competitor);
        if (! isset(self::COMPETITORS[$key])) {
            abort(404);
        }

        // Otros competidores como alternativas (excluyendo el target)
        $others = collect(self::COMPETITORS)
            ->except($key)
            ->map(fn ($d, $slug) => array_merge($d, ['slug' => $slug]))
            ->values()
            ->all();

        return view('comparison.alternatives', [
            'competitor' => self::COMPETITORS[$key],
            'slug'       => $key,
            'others'     => $others,
            'all_competitors' => $this->publicCompetitors(),
        ]);
    }

    /**
     * Lista pública de competidores (slug + name) para sitemap, footer y
     * navegación cruzada entre páginas vs.
     */
    public function publicCompetitors(): array
    {
        return collect(self::COMPETITORS)
            ->map(fn ($d, $slug) => ['slug' => $slug, 'name' => $d['name']])
            ->values()
            ->all();
    }
}
