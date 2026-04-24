<x-filament-panels::page>
    <form wire:submit="save" class="max-w-3xl">
        {{ $this->form }}
        <div class="mt-6">
            <x-filament::button type="submit">Guardar cambios</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
