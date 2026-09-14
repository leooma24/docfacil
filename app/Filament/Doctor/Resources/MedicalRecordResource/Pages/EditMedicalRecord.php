<?php

namespace App\Filament\Doctor\Resources\MedicalRecordResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\MedicalRecordResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMedicalRecord extends EditRecord
{
    use HasFormHero;

    protected static string $resource = MedicalRecordResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Pasadas 24 horas la nota queda bloqueada (NOM-004). Si alguien llega
        // por la liga directa, se le explica en vez de dejarlo escribir y
        // reventar al guardar.
        if ($this->record->isLocked()) {
            Notification::make()
                ->title('Esta nota ya no se puede editar')
                ->body('Pasaron 24 horas desde que se guardó. Si hay que corregir o agregar algo, registra una nota nueva.')
                ->warning()
                ->send();

            $this->redirect(MedicalRecordResource::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => ! $this->record->isLocked()),
        ];
    }

    protected function getFormHeroConfig(): array
    {
        $patient = $this->record->patient ?? null;
        $name = $patient ? trim($patient->first_name . ' ' . $patient->last_name) : 'Consulta';
        $date = $this->record->visit_date?->format('d/m/Y') ?? '';

        return [
            'title'    => 'Editar consulta',
            'icon'     => '📋',
            'kicker'   => '✏️ ' . $name . ($date ? ' · ' . $date : ''),
            'subtitle' => 'Actualiza diagnóstico, tratamiento y notas clínicas.',
            'gradient' => '#ef4444 0%, #f97316 40%, #f59e0b 100%',
            'accent'   => '#ef4444',
        ];
    }
}
