<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-icono nombre="bell-alert" /> Adeudos vencidos
        </x-slot>
        <x-slot name="description">
            Total pendiente: <strong>${{ number_format($total_overdue, 2) }}</strong> — "Cobrar" registra el pago; "Recordarle" abre WhatsApp.
        </x-slot>

        <div class="space-y-2">
            @foreach ($payments as $p)
            <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/40">
                <div class="min-w-0 flex-1">
                    <div class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $p['name'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-red-600 dark:text-red-400">${{ $p['remaining'] }}</span>
                        · vence {{ $p['due_date'] }}
                        @if($p['days_overdue'] > 0)
                        · <span class="text-red-700 dark:text-red-300 font-semibold">{{ $p['days_overdue'] }}d atrasado</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    {{ ($this->cobrarAction)(['payment' => $p['id']]) }}
                    @if($p['wa_url'])
                    <a href="{{ $p['wa_url'] }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-teal-700 bg-white border border-teal-200 hover:bg-teal-50 rounded-lg transition whitespace-nowrap">
                        Recordarle
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-3 text-right">
            <a href="{{ url('/doctor/cobros?tableFilters[overdue][isActive]=true') }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">
                Ver todos los adeudos →
            </a>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
