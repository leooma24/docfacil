<?php

namespace App\Filament\Doctor\Resources\OdontogramResource\Pages;

use App\Filament\Doctor\Resources\OdontogramResource;
use App\Models\OdontogramTooth;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;

class EditOdontogram extends EditRecord
{
    protected static string $resource = OdontogramResource::class;

    protected static string $view = 'filament.doctor.resources.odontogram-resource.pages.edit-odontogram';

    public array $teethData = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Load existing teeth data
        $this->teethData = $this->record->teeth->mapWithKeys(fn (OdontogramTooth $t) => [$t->tooth_number => [
            'condition' => $t->condition,
            'notes' => $t->notes,
            'surfaces' => $t->caras(),
        ]])->toArray();
    }

    #[On('teeth-updated')]
    public function onTeethUpdated(array $teeth): void
    {
        $this->teethData = $teeth;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save_odontogram')
                ->label('Guardar Odontograma')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->action(function () {
                    $this->save();
                    $this->guardarDientes();

                    // notify() era la API de Filament 2; en la 3 no existe y
                    // el guardado reventaba con BadMethodCallException DESPUES
                    // de escribir en la base: los datos quedaban guardados
                    // pero el doctor veia un error.
                    Notification::make()
                        ->title('Odontograma guardado')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('armar_presupuesto')
                ->label('Armar presupuesto')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('gray')
                ->action(function () {
                    $plan = $this->armarPresupuesto();

                    if (! $plan) {
                        Notification::make()
                            ->title('Presupuestos es un add-on')
                            ->body('Actívelo en Add-ons para convertir lo que falta tratar en un presupuesto con sus precios.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $this->redirect(\App\Filament\Doctor\Resources\TreatmentPlanResource::getUrl('edit', ['record' => $plan], panel: 'doctor'));
                }),
            Actions\Action::make('imprimir')
                ->label('Imprimir')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('odontograma.imprimir', $this->record))
                ->openUrlInNewTab(),
            // Borrar va aparte, en el menú de más acciones: pegado a
            // "Guardar" era fácil tocarlo sin querer.
            Actions\ActionGroup::make([
                Actions\DeleteAction::make(),
            ])->tooltip('Más acciones'),
        ];
    }

    /**
     * Baja $teethData a la tabla odontogram_teeth.
     *
     * Un diente sano y sin nota no guarda registro: es el estado por defecto.
     * Pero si antes tenia una marca hay que borrarla, o el diente que el
     * doctor corrigio se queda pintado para siempre.
     */
    public function guardarDientes(): void
    {
        foreach ($this->teethData as $toothNumber => $data) {
            $condition = is_array($data) ? ($data['condition'] ?? 'healthy') : $data;
            $notes = is_array($data) ? ($data['notes'] ?? null) : null;
            $caras = array_merge(OdontogramTooth::carasVacias(), is_array($data) ? ($data['surfaces'] ?? []) : []);

            $columnas = [];
            foreach (OdontogramTooth::CARAS as $cara => $columna) {
                $columnas[$columna] = $caras[$cara] ?: null;
            }

            if ($condition !== 'healthy' || $notes || array_filter($caras)) {
                OdontogramTooth::updateOrCreate(
                    [
                        'odontogram_id' => $this->record->id,
                        'tooth_number' => $toothNumber,
                    ],
                    [
                        'condition' => $condition,
                        'notes' => $notes,
                    ] + $columnas
                );

                continue;
            }

            OdontogramTooth::where('odontogram_id', $this->record->id)
                ->where('tooth_number', $toothNumber)
                ->delete();
        }
    }

    /**
     * Guarda lo que el doctor marcó (aunque no haya dado "Guardar") y arma
     * el presupuesto con lo que falta tratar. Sin el add-on, no hace nada.
     */
    public function armarPresupuesto(): ?\App\Models\TreatmentPlan
    {
        if (! auth()->user()?->clinic?->hasFeature('treatment_plans')) {
            return null;
        }

        $this->guardarDientes();

        return \App\Support\OdontogramaClinico::presupuestoDesde($this->record->fresh());
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public function getContentTabLabel(): ?string
    {
        return 'Datos';
    }

    protected function afterGetFormActions(): array
    {
        return [];
    }
}
