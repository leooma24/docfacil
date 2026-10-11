<x-filament-panels::page>
    {{-- Las alertas del paciente también aquí (día 10 del plan). --}}
    @if($alertas = \App\Support\AlertasClinicas::etiquetas($this->record->patient, 5))
        {!! \App\Filament\Doctor\Concerns\AvisoDeAlertas::chips($alertas) !!}
    @endif
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
    </x-filament-panels::form>

    <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Diagrama Dental</h3>
        @livewire('odontogram-editor', ['odontogramId' => $this->record->id], key('odontogram-' . $this->record->id))
    </div>
</x-filament-panels::page>
