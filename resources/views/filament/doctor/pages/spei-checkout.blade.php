<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Hero --}}
        <div class="rounded-2xl p-6 text-white" style="background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%);">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold tracking-wider uppercase opacity-90 mb-1">Pago por transferencia SPEI</div>
                    <h1 class="text-2xl font-extrabold">Plan {{ ucfirst($this->plan === 'profesional' ? 'Pro' : $this->plan) }} · {{ $this->cycle === 'annual' ? 'Anual' : 'Mensual' }}</h1>
                    <p class="mt-1 opacity-95">Total a transferir: <strong>${{ number_format($this->amount, 2) }} MXN</strong></p>
                </div>
                <a href="{{ route('filament.doctor.pages.actualizar-plan') }}" class="text-white/90 hover:text-white text-sm underline">← Regresar</a>
            </div>
        </div>

        {{-- Pasos --}}
        <div class="grid md:grid-cols-2 gap-6">
            {{-- Datos bancarios --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4 dark:bg-gray-900 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm font-bold">1</span>
                    Transfiere a esta cuenta
                </h2>

                @php $spei = config('services.spei'); @endphp

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-gray-500 dark:text-gray-400">Banco</span>
                        <strong class="text-gray-900 dark:text-white">{{ $spei['banco'] }}</strong>
                    </div>
                    <div class="flex items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-gray-500 dark:text-gray-400">Titular</span>
                        <strong class="text-gray-900 dark:text-white">{{ $spei['titular'] }}</strong>
                    </div>
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-2">
                        <div class="text-gray-500 dark:text-gray-400 mb-1">CLABE</div>
                        <div class="flex items-center gap-2">
                            <strong class="text-gray-900 dark:text-white font-mono text-base tracking-wider">{{ $spei['clabe'] }}</strong>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $spei['clabe'] }}'); this.innerText='✓ Copiado'; setTimeout(()=>this.innerText='Copiar',2000)" class="text-xs text-teal-600 hover:text-teal-700 font-semibold border border-teal-200 px-2 py-1 rounded">Copiar</button>
                        </div>
                    </div>
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-2">
                        <div class="text-gray-500 dark:text-gray-400 mb-1">Monto</div>
                        <div class="text-2xl font-extrabold text-teal-600">${{ number_format($this->amount, 2) }} MXN</div>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 dark:bg-amber-900/20 dark:border-amber-800">
                        <div class="text-xs text-amber-900 font-semibold mb-1 dark:text-amber-200">Concepto / Referencia (importante)</div>
                        <div class="flex items-center gap-2">
                            <strong class="text-amber-900 dark:text-amber-100 font-mono">{{ $this->referenceCode }}</strong>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $this->referenceCode }}'); this.innerText='✓ Copiado'; setTimeout(()=>this.innerText='Copiar',2000)" class="text-xs text-amber-700 hover:text-amber-900 font-semibold border border-amber-300 px-2 py-1 rounded">Copiar</button>
                        </div>
                        <p class="text-xs text-amber-800 mt-1 dark:text-amber-200/80">Pon este código como <strong>concepto</strong> o <strong>referencia</strong> de tu transferencia. Así lo cruzamos contigo.</p>
                    </div>
                </div>
            </div>

            {{-- Subir comprobante --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4 dark:bg-gray-900 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm font-bold">2</span>
                    Sube el comprobante
                </h2>

                <form wire:submit="submit" class="space-y-4">
                    {{ $this->form }}

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-teal-200 transition">
                        Enviar comprobante
                    </button>

                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center">Lo revisaremos en 1-24 horas hábiles. Recibirás un correo y WhatsApp con la resolución.</p>
                </form>
            </div>
        </div>

        {{-- Recomendación de anual si eligió mensual --}}
        @if ($this->cycle === 'monthly')
        <div class="rounded-2xl p-5 border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 flex items-start gap-4 dark:border-amber-800 dark:from-amber-900/20 dark:to-orange-900/20">
            <div class="text-3xl">💡</div>
            <div class="flex-1">
                <div class="font-bold text-amber-900 dark:text-amber-200">¿Te conviene el plan anual con SPEI?</div>
                <p class="text-sm text-amber-800 dark:text-amber-200/80 mt-1">Con SPEI mensual tendrás que subir comprobante cada mes. Con anual pagas 1 vez, te ahorras 2 meses y te olvidas del trámite.</p>
                <a href="{{ route('filament.doctor.pages.pago-spei', ['plan' => $this->plan, 'cycle' => 'annual']) }}" class="inline-block mt-2 text-sm font-bold text-amber-900 dark:text-amber-200 underline">Cambiar a anual y ahorrar 2 meses →</a>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
