<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🌐 Tu portal público de reservas
        </x-slot>
        <x-slot name="description">
            Comparte esta URL en tu Instagram, firma de WhatsApp, o pon el QR en tu recepción. Los pacientes agendan sin llamarte.
        </x-slot>

        <div class="grid md:grid-cols-[1fr_auto] gap-5 items-center">
            <div class="space-y-3">
                <div x-data="{ copied: false }" class="flex gap-2">
                    <input
                        type="text"
                        readonly
                        value="{{ $publicUrl }}"
                        id="portal-url-input"
                        class="flex-1 px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 font-mono select-all">
                    <button
                        type="button"
                        x-on:click="navigator.clipboard.writeText('{{ $publicUrl }}'); copied = true; setTimeout(() => copied = false, 2000);"
                        class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-lg transition whitespace-nowrap">
                        <span x-show="!copied">📋 Copiar</span>
                        <span x-show="copied" x-cloak>✓ Copiado</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ $whatsappShareUrl }}" target="_blank"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        Compartir WhatsApp
                    </a>
                    <a href="{{ $publicUrl }}" target="_blank"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-lg transition">
                        👁️ Ver como paciente
                    </a>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg leading-relaxed">
                    💡 <strong>Ideas para usarlo:</strong>
                    <ul class="mt-1 ml-4 list-disc space-y-0.5">
                        <li>Pega la URL en la bio de Instagram de tu consultorio</li>
                        <li>Imprime el QR y ponlo en la recepción</li>
                        <li>Agrégalo a tu firma de WhatsApp con "Agenda en: [link]"</li>
                        <li>Compártelo cuando te pregunten por WhatsApp "¿a qué hora hay?"</li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col items-center gap-2 p-3 bg-white border border-gray-200 rounded-xl">
                <img src="{{ $qrUrl }}" alt="QR portal" width="160" height="160" loading="lazy" style="display:block;">
                <a href="{{ $qrUrl }}&format=png&size=600x600" download="qr-{{ $clinic->slug }}.png"
                   class="text-xs text-teal-600 hover:text-teal-700 font-semibold">
                    ⬇️ Descargar QR alta resolución
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
