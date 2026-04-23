<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🔔 Adeudos vencidos
        </x-slot>
        <x-slot name="description">
            Total pendiente: <strong>${{ number_format($total_overdue, 2) }}</strong> — click "Cobrar" para abrir WhatsApp.
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
                @if($p['wa_url'])
                <a href="{{ $p['wa_url'] }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition shadow-sm whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    Cobrar
                </a>
                @else
                <span class="text-xs text-gray-400 italic whitespace-nowrap">Sin tel</span>
                @endif
            </div>
            @endforeach
        </div>

        <div class="mt-3 text-right">
            <a href="{{ url('/doctor/cobros?tableFilters[overdue][isActive]=true') }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">
                Ver todos los adeudos →
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
