<?php

namespace App\Filament\Doctor\Resources\OdontogramResource\Pages;

use App\Filament\Doctor\Resources\OdontogramResource;
use App\Models\OdontogramTooth;
use Filament\Actions;
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
        $this->teethData = $this->record->teeth->pluck('condition', 'tooth_number')->toArray();
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
                ->color('success')
                ->action(function () {
                    // Save form data
                    $this->save();

                    // Save teeth
                    foreach ($this->teethData as $toothNumber => $data) {
                        $condition = is_array($data) ? ($data['condition'] ?? 'healthy') : $data;
                        $notes = is_array($data) ? ($data['notes'] ?? null) : null;

                        if ($condition !== 'healthy' || $notes) {
                            OdontogramTooth::updateOrCreate(
                                [
                                    'odontogram_id' => $this->record->id,
                                    'tooth_number' => $toothNumber,
                                ],
                                [
                                    'condition' => $condition,
                                    'notes' => $notes,
                                ]
                            );
                        }
                    }

                    $this->notify('success', 'Odontograma guardado correctamente.');
                }),
            Actions\DeleteAction::make(),
        ];
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
