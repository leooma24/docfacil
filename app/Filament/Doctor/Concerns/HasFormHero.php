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

    /**
     * «Nuevo paciente», «Nueva cita» en vez de «Crear Paciente» (11-oct-2026),
     * y botones que dicen lo que hacen: «Guardar», «Guardar y agregar otro»,
     * «Guardar cambios».
     */
    private const FEMENINOS = ['cita', 'receta', 'nota', 'consulta', 'orden', 'invitación', 'entrada', 'salida'];

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        if (! $this instanceof \Filament\Resources\Pages\CreateRecord) {
            return parent::getTitle();
        }

        $nombre = mb_strtolower(static::getResource()::getModelLabel());
        $primera = explode(' ', $nombre)[0];

        return (in_array($primera, self::FEMENINOS, true) ? 'Nueva ' : 'Nuevo ') . $nombre;
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()->label('Guardar');
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()->label('Guardar y agregar otro');
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()->label('Guardar cambios');
    }
}
