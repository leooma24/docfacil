<x-filament-panels::page>
    <p style="font-size:0.9rem;margin-bottom:0.5rem;">
        <a href="{{ \App\Filament\Doctor\Pages\ConsultationFieldsSettings::getUrl() }}" style="color:#0f766e;text-decoration:underline;font-weight:700;">Elegir qué campos lleva la consulta</a>
    </p>
    <form wire:submit="save" class="max-w-3xl">
        {{ $this->form }}
        <div class="mt-6">
            <x-filament::button type="submit">Guardar cambios</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
