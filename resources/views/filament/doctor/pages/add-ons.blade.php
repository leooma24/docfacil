<x-filament-panels::page>
    <div class="max-w-4xl mx-auto">
        <div class="mb-6 p-5 rounded-xl" style="background:linear-gradient(135deg,#f0fdfa,#ecfeff);border:1px solid #99f6e4;">
            <div class="flex items-start gap-3">
                <div class="text-3xl">✨</div>
                <div>
                    <h3 class="font-extrabold text-teal-900 text-lg">Amplía tu plan con features específicos</h3>
                    <p class="text-sm text-teal-800 mt-1 leading-relaxed">
                        Paga solo lo que usas. Activa cualquier add-on con <strong>30 días gratis</strong> como founding member. Puedes cancelarlo cuando quieras desde esta página.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            @foreach ($this->getCatalog() as $addon)
            <div @class([
                'rounded-2xl p-6 transition',
                'bg-white border-2 border-teal-400 shadow-lg shadow-teal-100' => $addon['is_active'],
                'bg-white border border-gray-200 hover:border-teal-300 hover:shadow-lg' => !$addon['is_active'],
            ])>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="text-4xl">{{ $addon['icon'] }}</div>
                    <div class="text-right">
                        @if($addon['is_active'])
                            <span class="inline-block text-xs font-bold px-2.5 py-1 rounded-full bg-teal-100 text-teal-800">
                                @if($addon['status'] === 'trial' && $addon['trial_ends_at'])
                                    🎁 Prueba hasta {{ $addon['trial_ends_at']->format('d/m/Y') }}
                                @else
                                    ✓ Activo
                                @endif
                            </span>
                        @else
                            <span class="inline-block text-xs font-bold px-2.5 py-1 rounded-full bg-teal-50 text-teal-700">
                                ${{ number_format($addon['monthly_price'], 0) }}/mes
                            </span>
                        @endif
                    </div>
                </div>

                <h4 class="font-extrabold text-gray-900 text-lg mb-2">{{ $addon['name'] }}</h4>
                <p class="text-sm text-gray-600 leading-relaxed mb-3">{{ $addon['short_description'] }}</p>
                <p class="text-xs text-gray-500 leading-relaxed mb-4">{{ $addon['long_description'] }}</p>

                @if(!empty($addon['revenue_hypothesis']))
                <div class="text-xs font-semibold text-teal-700 bg-teal-50 rounded-lg p-2.5 mb-4">
                    💰 {{ $addon['revenue_hypothesis'] }}
                </div>
                @endif

                @if($addon['is_active'])
                    <button
                        wire:click="cancelAddon('{{ $addon['slug'] }}')"
                        wire:confirm="¿Seguro que quieres cancelar {{ $addon['name'] }}? Conservarás acceso hasta el fin del periodo."
                        class="w-full px-4 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        Cancelar add-on
                    </button>
                @else
                    <button
                        wire:click="activateAddon('{{ $addon['slug'] }}')"
                        class="w-full px-4 py-2.5 text-sm font-semibold text-white rounded-lg transition"
                        style="background:linear-gradient(135deg,#14b8a6,#0d9488);">
                        @if(($addon['beta_trial_days'] ?? 0) > 0)
                            🎁 Activar — {{ $addon['beta_trial_days'] }} días gratis
                        @else
                            Activar por ${{ number_format($addon['monthly_price'], 0) }}/mes
                        @endif
                    </button>
                @endif
            </div>
            @endforeach
        </div>

        <p class="text-center text-xs text-gray-500 mt-8 italic">
            Los add-ons se cobran junto con tu plan base. Puedes cancelar cuando quieras — el cargo se detiene en el siguiente ciclo.
        </p>
    </div>
</x-filament-panels::page>
