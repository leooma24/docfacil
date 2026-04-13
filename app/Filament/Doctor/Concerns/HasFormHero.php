<?php

namespace App\Filament\Doctor\Concerns;

/**
 * Trait para Create/Edit pages de Filament que necesitan un hero compacto arriba del form.
 * Lee la metadata del recurso (icono, gradiente, accent) y agrega título/subtítulo
 * específicos de "Crear X" o "Editar X".
 *
 * Uso en cada CreateXxx/EditXxx:
 *   use HasFormHero;
 *   protected static string $view = 'filament.doctor.resources.form-with-hero';
 *   protected function getFormHeroConfig(): array {
 *       return [
 *           'title'    => 'Nuevo paciente',
 *           'icon'     => '👤',
 *           'kicker'   => '➕ Crear registro',
 *           'subtitle' => 'Agrega los datos básicos — podrás editar todo después.',
 *           'gradient' => '#0d9488 0%, #0891b2 40%, #06b6d4 100%',
 *           'accent'   => '#0d9488',
 *       ];
 *   }
 */
trait HasFormHero
{
    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => static::$title ?? 'Formulario',
            'icon'     => '📋',
            'kicker'   => 'Formulario',
            'subtitle' => '',
            'gradient' => '#0d9488 0%, #0891b2 40%, #06b6d4 100%',
            'accent'   => '#0d9488',
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            ['formHeroConfig' => $this->getFormHeroConfig()],
        );
    }
}
