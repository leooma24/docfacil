<x-filament-panels::page>
    @php
        $clinic = $this->getClinic();
        $expired = $this->isExpired();
        $founder = $this->isFounder();
        $cycle = $this->billingCycle;
        $plans = $this->getPlans();
    @endphp

    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Banner expirado --}}
        @if ($expired)
        <div class="rounded-2xl p-5 border border-amber-300 bg-gradient-to-r from-amber-50 to-amber-100 flex items-center gap-4 dark:border-amber-700 dark:from-amber-900/20 dark:to-amber-800/20">
            <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="font-bold text-amber-900 dark:text-amber-200">Tu {{ $clinic->is_beta ? 'período beta' : 'prueba gratuita' }} ha terminado</div>
                <div class="text-sm text-amber-800 dark:text-amber-300">Tus datos están seguros. Activa un plan para seguir usando todas las funciones.</div>
            </div>
        </div>
        @endif

        {{-- Info plan actual --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center justify-between dark:bg-gray-900 dark:border-gray-700">
            <div>
                <div class="text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 uppercase">Tu plan actual</div>
                <div class="text-2xl font-extrabold text-gray-900 dark:text-white capitalize">{{ $clinic->plan === 'profesional' ? 'Pro' : $clinic->plan }}</div>
                @if ($clinic->plan_ends_at)
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ $clinic->plan_ends_at->isPast() ? 'Venció el' : 'Vence el' }} {{ $clinic->plan_ends_at->format('d/m/Y') }}
                </div>
                @elseif ($clinic->trial_ends_at)
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Trial {{ $clinic->trial_ends_at->isPast() ? 'venció el' : 'vence el' }} {{ $clinic->trial_ends_at->format('d/m/Y') }}
                </div>
                @endif
            </div>
            <div class="text-right space-x-2">
                @if ($clinic->is_beta)
                <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold dark:bg-amber-900/30 dark:text-amber-200">BETA</span>
                @endif
                @if ($founder)
                <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold dark:bg-emerald-900/30 dark:text-emerald-200">FUNDADOR</span>
                @endif
            </div>
        </div>

        {{-- Toggle mensual/anual --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border border-amber-200 rounded-2xl p-4 dark:from-amber-900/10 dark:via-orange-900/10 dark:to-amber-900/10 dark:border-amber-800">
            <div class="flex items-center gap-3">
                <div class="text-3xl">💡</div>
                <div>
                    <div class="font-bold text-amber-900 dark:text-amber-200">Paga anual y ahorra 2 meses</div>
                    <div class="text-sm text-amber-800 dark:text-amber-300/80">El plan anual cuesta solo 10 meses (16.7% descuento).</div>
                </div>
            </div>
            <div class="inline-flex bg-white rounded-xl p-1 border border-amber-200 shadow-sm dark:bg-gray-900">
                <button type="button" wire:click="setCycle('monthly')" class="px-4 py-2 rounded-lg text-sm font-bold transition {{ $cycle === 'monthly' ? 'bg-teal-600 text-white shadow' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300' }}">
                    Mensual
                </button>
                <button type="button" wire:click="setCycle('annual')" class="px-4 py-2 rounded-lg text-sm font-bold transition {{ $cycle === 'annual' ? 'bg-teal-600 text-white shadow' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300' }}">
                    Anual · 2 meses gratis
                </button>
            </div>
        </div>

        {{-- Planes --}}
        <div class="grid md:grid-cols-3 gap-5" x-data>
            @foreach ($plans as $plan)
            @php
                $price = $cycle === 'annual' ? $plan['annual'] : $plan['monthly'];
                $subtitle = $cycle === 'annual' ? '/año' : '/mes';
                $isPopular = !empty($plan['popular']);
                $visible = array_slice($plan['features'], 0, 4);
                $hidden = array_slice($plan['features'], 4);
            @endphp
            <div x-data="{ expanded: false }" class="relative flex flex-col bg-white rounded-2xl p-6 border-2 transition {{ $isPopular ? 'border-teal-500 shadow-xl scale-[1.02]' : 'border-gray-200 hover:border-teal-300' }} dark:bg-gray-900 dark:border-gray-700">
                @if ($isPopular)
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-teal-600 to-cyan-600 text-white text-xs font-bold px-4 py-1 rounded-full shadow-lg">★ RECOMENDADO</span>
                @endif

                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan['name'] }}</h3>
                <div class="mt-3 mb-1">
                    <span class="text-4xl font-extrabold {{ $isPopular ? 'text-teal-600' : 'text-gray-900 dark:text-white' }}">${{ number_format($price) }}</span>
                    <span class="text-gray-500 text-sm">{{ $subtitle }} MXN</span>
                </div>
                @if ($cycle === 'annual')
                <div class="mb-4 inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 border border-emerald-200 rounded text-xs font-semibold text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300">
                    Equivale a ${{ number_format($plan['annual'] / 12) }}/mes
                </div>
                @endif
                <p class="text-xs text-gray-500 mb-4 min-h-[32px] dark:text-gray-400">{{ $plan['ideal'] }}</p>

                <ul class="space-y-2 mb-4 text-sm">
                    @foreach ($visible as $feat)
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold mt-0.5">✓</span> <span class="text-gray-700 dark:text-gray-300">{{ $feat }}</span></li>
                    @endforeach
                    @if (count($hidden) > 0)
                    <template x-if="expanded">
                        <div class="space-y-2">
                            @foreach ($hidden as $feat)
                            <li class="flex items-start gap-2"><span class="text-teal-500 font-bold mt-0.5">✓</span> <span class="text-gray-700 dark:text-gray-300">{{ $feat }}</span></li>
                            @endforeach
                        </div>
                    </template>
                    @endif
                </ul>

                <div class="mt-auto space-y-2">
                    @if (count($hidden) > 0)
                    <button type="button" @click="expanded = !expanded" class="w-full text-xs font-semibold text-teal-600 hover:text-teal-700 flex items-center justify-center gap-1">
                        <span x-show="!expanded">Ver {{ count($hidden) }} features más</span>
                        <span x-show="expanded" x-cloak>Ver menos</span>
                        <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    @endif

                    {{-- CTAs (styles inline para evitar que Tailwind v4 no compile gradient en prod) --}}
                    <button wire:click="checkout('{{ $plan['key'] }}', 'stripe')" type="button"
                            style="width:100%; padding:10px; border-radius:12px; font-size:14px; font-weight:700; color:#fff; background:linear-gradient(135deg,#0d9488,#06b6d4); display:flex; align-items:center; justify-content:center; gap:8px; border:none; cursor:pointer;"
                            onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 19h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Pagar con tarjeta
                    </button>
                    <button wire:click="checkout('{{ $plan['key'] }}', 'spei')" type="button"
                            style="width:100%; padding:10px; border-radius:12px; font-size:14px; font-weight:700; color:#374151; background:#f3f4f6; display:flex; align-items:center; justify-content:center; gap:8px; border:none; cursor:pointer;"
                            onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                        Pagar por SPEI (transferencia)
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Métodos aceptados --}}
        <div class="text-center text-sm text-gray-500 dark:text-gray-400">
            Aceptamos <strong>tarjeta de crédito/débito</strong> (Visa, Mastercard, AmEx) vía Stripe y <strong>transferencia SPEI</strong> con aprobación manual (1-24 hrs).
            <br>¿Dudas? WhatsApp al <a href="https://wa.me/526682493398" target="_blank" class="text-teal-600 font-semibold hover:underline">668 249 3398</a>
        </div>
    </div>
</x-filament-panels::page>
