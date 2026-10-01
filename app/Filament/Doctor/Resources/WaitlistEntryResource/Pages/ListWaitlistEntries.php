<?php

namespace App\Filament\Doctor\Resources\WaitlistEntryResource\Pages;

use App\Filament\Doctor\Resources\WaitlistEntryResource;
use App\Models\Appointment;
use App\Models\WaitlistEntry;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Livewire\Attributes\Url;

class ListWaitlistEntries extends ListRecords
{
    protected static string $resource = WaitlistEntryResource::class;

    /**
     * La cita cancelada que dejó el hueco. Con ella la lista se abre solo con
     * quienes caben ahí, y "Ofrecer" ya trae la fecha y la hora.
     */
    #[Url]
    public ?int $hueco = null;

    public function huecoCita(): ?Appointment
    {
        return $this->hueco
            ? Appointment::where('clinic_id', auth()->user()->clinic_id)->with('doctor.user')->find($this->hueco)
            : null;
    }

    public function getSubheading(): ?string
    {
        $cita = $this->huecoCita();

        return $cita
            ? 'Se liberó ' . $cita->starts_at->locale('es')->isoFormat('dddd D [de] MMMM, HH:mm') . ' a ' . $cita->ends_at->format('H:i')
                . ($cita->doctor?->user ? ' con ' . $cita->doctor->user->name : '') . '. Estos pacientes esperaban ese día.'
            : null;
    }

    public function table(Table $table): Table
    {
        return parent::table($table)->modifyQueryUsing(function ($query) {
            $cita = $this->huecoCita();
            if ($cita) {
                // Los que esperaban ese día, y a los que ya se les ofreció
                // este hueco (para poder apartárselo ahí mismo).
                $query->where(fn ($q) => $q
                    ->whereIn('id', WaitlistEntry::candidatosPara($cita, 20)->pluck('id'))
                    ->orWhere('notified_for_appointment_id', $cita->id));
            }
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ver_todos')
                ->label('Ver toda la lista')
                ->color('gray')
                ->visible(fn () => (bool) $this->hueco)
                ->url(WaitlistEntryResource::getUrl('index')),
            Actions\CreateAction::make()->label('Agregar a lista de espera'),
        ];
    }
}
