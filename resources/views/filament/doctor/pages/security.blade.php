<x-filament-panels::page>
    @if(auth()->user()->hasTwoFactorEnabled())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold">Autenticación de dos factores activa</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Habilitada el {{ auth()->user()->two_factor_confirmed_at->translatedFormat('d/m/Y H:i') }}.
                        Tu cuenta está protegida con un código que cambia cada 30 segundos.
                    </p>
                    <button
                        wire:click="disable2FA"
                        wire:confirm="¿Seguro que quieres deshabilitar 2FA? Tu cuenta quedará menos protegida."
                        class="mt-4 px-4 py-2 text-sm font-semibold text-red-700 bg-red-50 rounded-lg hover:bg-red-100 border border-red-200"
                    >
                        Deshabilitar 2FA
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-6 space-y-6">
            <div>
                <h3 class="text-lg font-bold">Habilitar autenticación de dos factores</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Protege tu cuenta con un código que cambia cada 30 segundos. Recomendado por LFPDPPP para datos sensibles de salud.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-sm mb-2">Paso 1: Escanea el código QR</h4>
                    <p class="text-xs text-gray-500 mb-3">
                        Usa Google Authenticator, Authy, 1Password o cualquier app TOTP.
                    </p>
                    <div class="bg-white p-4 rounded-lg border inline-block">
                        {!! $qrCodeSvg !!}
                    </div>
                    <div class="mt-3 text-xs">
                        <p class="text-gray-500">¿No puedes escanear? Introduce manualmente:</p>
                        <code class="block mt-1 p-2 bg-gray-100 dark:bg-gray-700 rounded font-mono text-xs break-all">{{ $secret }}</code>
                    </div>
                </div>

                <div>
                    <h4 class="font-semibold text-sm mb-2">Paso 2: Verifica el código</h4>
                    <p class="text-xs text-gray-500 mb-3">
                        Introduce el código de 6 dígitos que muestra tu app para confirmar.
                    </p>
                    <input
                        type="text"
                        wire:model="verificationCode"
                        maxlength="6"
                        placeholder="000000"
                        class="w-full px-4 py-3 text-center text-2xl font-mono tracking-widest rounded-lg border-gray-300 focus:border-teal-500 focus:ring-teal-500 dark:bg-gray-700 dark:border-gray-600"
                    >
                    <button
                        wire:click="enable2FA"
                        class="mt-3 w-full px-4 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700"
                    >
                        Habilitar 2FA
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
