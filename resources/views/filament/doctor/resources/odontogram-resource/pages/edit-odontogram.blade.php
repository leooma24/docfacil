<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
    </x-filament-panels::form>

    <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Diagrama Dental</h3>
        @livewire('odontogram-editor', ['odontogramId' => $this->record->id], key('odontogram-' . $this->record->id))
    </div>
</x-filament-panels::page>
