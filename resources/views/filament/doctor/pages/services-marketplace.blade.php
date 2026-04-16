<x-filament-panels::page>
    @php
        $servicesByCategory = $this->getServices();
        $purchases = $this->getMyPurchases();
    @endphp

    <div class="max-w-6xl mx-auto space-y-8">

        {{-- Hero --}}
        <div class="rounded-2xl p-6 text-white" style="background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%);">
            <div class="text-xs font-semibold tracking-wider uppercase opacity-90 mb-1">Servicios premium</div>
            <h1 class="text-2xl sm:text-3xl font-extrabold">Llévalo al siguiente nivel</h1>
            <p class="mt-2 opacity-95 max-w-2xl">Setup express, capacitación, branding profesional, WhatsApp Business API y más. Te dejamos tu consultorio funcionando como una clínica grande sin que muevas un dedo.</p>
        </div>

        {{-- Compras recientes (si las hay) --}}
        @if ($purchases->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-200 p-5 dark:bg-gray-900 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Tus compras recientes</h2>
            </div>
            <div class="space-y-2">
                @foreach ($purchases as $p)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-800 last:border-0">
                    <div>
                        <div class="font-semibold text-sm text-gray-900 dark:text-white">{{ $p->service_name_snapshot }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $p->created_at->format('d/m/Y') }} · ${{ number_format($p->amount_mxn) }} MXN</div>
                    </div>
                    @php $color = $p->statusColor(); @endphp
                    <span style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; {{ $color === 'success' ? 'background:#d1fae5; color:#065f46;' : ($color === 'warning' ? 'background:#fef3c7; color:#92400e;' : ($color === 'info' ? 'background:#dbeafe; color:#1e40af;' : ($color === 'danger' ? 'background:#fee2e2; color:#991b1b;' : 'background:#f3f4f6; color:#374151;'))) }}">
                        {{ $p->statusLabel() }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Catálogo agrupado por categoría --}}
        @foreach ($servicesByCategory as $category => $services)
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span style="width:6px;height:24px;background:#0d9488;border-radius:3px;"></span>
                {{ $this->categoryLabel($category) }}
            </h2>

            <div class="grid md:grid-cols-2 gap-5">
                @foreach ($services as $service)
                <div class="bg-white rounded-2xl border-2 transition hover:-translate-y-1 hover:shadow-xl dark:bg-gray-900 dark:border-gray-700"
                     style="{{ $service->is_featured ? 'border-color:#0d9488; box-shadow: 0 4px 12px rgba(13,148,136,0.15);' : 'border-color:#e5e7eb;' }}">
                    <div class="p-6">
                        @if ($service->is_featured)
                        <div style="display:inline-block; background:linear-gradient(135deg,#0d9488,#06b6d4); color:#fff; font-size:10px; font-weight:700; letter-spacing:1px; padding:3px 10px; border-radius:10px; margin-bottom:10px;">⭐ DESTACADO</div>
                        @endif

                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $service->name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $service->short_desc }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div style="font-size:24px; font-weight:800; color:#0d9488;">${{ number_format($service->price_mxn) }}</div>
                                <div class="text-xs text-gray-500">{{ $service->pricing_type === 'monthly' ? '/ mes' : ($service->pricing_type === 'custom_quote' ? 'a cotizar' : 'único') }}</div>
                            </div>
                        </div>

                        @if (!empty($service->bullets))
                        <ul class="space-y-1.5 mb-4 mt-3">
                            @foreach (array_slice($service->bullets, 0, 4) as $bullet)
                            @php $b = is_array($bullet) ? ($bullet['value'] ?? array_values($bullet)[0] ?? '') : $bullet; @endphp
                            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <svg style="width:16px;height:16px;color:#0d9488;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ $b }}
                            </li>
                            @endforeach
                        </ul>
                        @endif

                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            Entrega en {{ $service->sla_days }} día{{ $service->sla_days === 1 ? '' : 's' }} hábil{{ $service->sla_days === 1 ? '' : 'es' }}
                        </div>

                        <div class="flex flex-col gap-2">
                            @if ($service->pricing_type === 'custom_quote')
                            <button wire:click="purchase({{ $service->id }}, 'manual')" type="button"
                                    style="width:100%; padding:10px; border-radius:10px; font-size:14px; font-weight:700; color:#fff; background:linear-gradient(135deg,#0d9488,#06b6d4); border:none; cursor:pointer;">
                                Solicitar cotización
                            </button>
                            @else
                            <button wire:click="purchase({{ $service->id }}, 'stripe')" type="button"
                                    style="width:100%; padding:10px; border-radius:10px; font-size:14px; font-weight:700; color:#fff; background:linear-gradient(135deg,#0d9488,#06b6d4); border:none; cursor:pointer;">
                                Pagar con tarjeta
                            </button>
                            <button wire:click="purchase({{ $service->id }}, 'spei')" type="button"
                                    style="width:100%; padding:10px; border-radius:10px; font-size:14px; font-weight:700; color:#374151; background:#f3f4f6; border:none; cursor:pointer;">
                                Pagar por SPEI
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="text-center text-sm text-gray-500 dark:text-gray-400 mt-8">
            ¿Algo más que necesitas y no está acá? Escríbeme directo: <a href="https://wa.me/526682493398" target="_blank" class="text-teal-600 font-semibold hover:underline">668 249 3398</a> — Omar Lerma
        </div>
    </div>
</x-filament-panels::page>
