<?php

namespace App\Filament\Doctor\Concerns;

/**
 * Trait para listados de Filament que necesitan un hero visual.
 * Cada ListXxx.php sobreescribe getHeroConfig() para proveer los valores específicos.
 * La vista compartida filament.doctor.resources.list-with-hero lee estos datos y
 * renderiza el partial list-hero antes de la tabla de Filament.
 */
trait HasListHero
{
    /**
     * Sobreescribir en cada ListXxx para personalizar el hero.
     *
     * Claves esperadas:
     *   - title:    string — Título grande
     *   - icon:     string — Emoji grande dentro del box glassmorphism
     *   - kicker:   string — Texto pequeño arriba del título (tagline)
     *   - subtitle: string — Descripción bajo el título
     *   - gradient: string — CSS linear-gradient stops (ej. '#0d9488 0%, #0891b2 40%, #06b6d4 100%')
     *   - accent:   string — Color sólido para sombras/border-top (ej. '#0d9488')
     *   - stats:    array  — [['label' => '...', 'value' => '...'], ...]
     */
    public function getHeroConfig(): array
    {
        return [
            'title' => static::$title ?? 'Listado',
            'icon' => '📋',
            'kicker' => 'Listado',
            'subtitle' => '',
            'gradient' => '#0d9488 0%, #0891b2 40%, #06b6d4 100%',
            'accent' => '#0d9488',
            'stats' => [],
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            ['heroConfig' => $this->getHeroConfig()],
        );
    }
}
