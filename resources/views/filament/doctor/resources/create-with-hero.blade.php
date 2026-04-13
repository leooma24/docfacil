<x-filament-panels::page
    @class([
        'fi-resource-create-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    @php $h = $formHeroConfig ?? []; @endphp

    @include('filament.doctor.partials.form-hero', [
        'title'    => $h['title']    ?? 'Nuevo registro',
        'icon'     => $h['icon']     ?? '📋',
        'kicker'   => $h['kicker']   ?? '➕ Crear',
        'subtitle' => $h['subtitle'] ?? '',
        'gradient' => $h['gradient'] ?? '#0d9488 0%, #0891b2 40%, #06b6d4 100%',
        'accent'   => $h['accent']   ?? '#0d9488',
    ])

    <x-filament-panels::form
        id="form"
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        wire:submit="create"
    >
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    <x-filament-panels::page.unsaved-data-changes-alert />
</x-filament-panels::page>
