<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Leaderboard — {{ now()->translatedFormat('F Y') }}</x-slot>

        @php $ranking = $this->getRanking(); @endphp

        @if(count($ranking) === 0)
            <p class="text-sm text-gray-500">Aún no hay conversiones este mes.</p>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($ranking as $rep)
                    <div class="flex items-center gap-4 py-3 {{ $rep['is_me'] ? 'bg-teal-50 dark:bg-teal-900/20 -mx-4 px-4 rounded' : '' }}">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                            {{ $rep['position'] === 1 ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $rep['position'] === 2 ? 'bg-gray-200 text-gray-700' : '' }}
                            {{ $rep['position'] === 3 ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $rep['position'] > 3 ? 'bg-gray-100 text-gray-600' : '' }}">
                            {{ $rep['position'] }}
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-sm">{{ $rep['name'] }} {{ $rep['is_me'] ? '(tú)' : '' }}</div>
                            <div class="text-xs text-gray-500">{{ $rep['closed'] }} {{ $rep['closed'] === 1 ? 'cierre' : 'cierres' }}</div>
                        </div>
                        <div class="text-sm font-semibold text-teal-600 dark:text-teal-400">
                            ${{ number_format($rep['total'], 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
