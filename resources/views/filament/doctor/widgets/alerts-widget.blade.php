<x-filament-widgets::widget>
    <x-filament::section heading="Alertas y avisos">
        <div class="space-y-3">
            @foreach($this->getAlerts() as $alert)
            <div class="flex items-start gap-3 p-3 rounded-lg
                {{ $alert['type'] === 'danger' ? 'bg-danger-50 dark:bg-danger-950' : '' }}
                {{ $alert['type'] === 'warning' ? 'bg-warning-50 dark:bg-warning-950' : '' }}
                {{ $alert['type'] === 'info' ? 'bg-info-50 dark:bg-info-950' : '' }}
                {{ $alert['type'] === 'success' ? 'bg-success-50 dark:bg-success-950' : '' }}
            ">
                <x-filament::icon
                    :icon="$alert['icon']"
                    class="w-5 h-5 mt-0.5 flex-shrink-0
                        {{ $alert['type'] === 'danger' ? 'text-danger-500' : '' }}
                        {{ $alert['type'] === 'warning' ? 'text-warning-500' : '' }}
                        {{ $alert['type'] === 'info' ? 'text-info-500' : '' }}
                        {{ $alert['type'] === 'success' ? 'text-success-500' : '' }}
                    "
                />
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $alert['title'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $alert['desc'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
