<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    @php $h = $heroConfig ?? []; @endphp

    @include('filament.doctor.partials.list-hero', [
        'title'    => $h['title']    ?? 'Listado',
        'icon'     => $h['icon']     ?? '📋',
        'kicker'   => $h['kicker']   ?? 'Listado',
        'subtitle' => $h['subtitle'] ?? '',
        'gradient' => $h['gradient'] ?? '#0d9488 0%, #0891b2 40%, #06b6d4 100%',
        'accent'   => $h['accent']   ?? '#0d9488',
        'stats'    => $h['stats']    ?? [],
    ])

    <div class="flex flex-col gap-y-6">
        <x-filament-panels::resources.tabs />

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

        {{ $this->table }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>
