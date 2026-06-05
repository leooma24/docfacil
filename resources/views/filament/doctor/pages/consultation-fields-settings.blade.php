<x-filament-panels::page>
    <div class="space-y-4">

        {{-- Header con info de especialidad --}}
        <div class="rounded-xl p-4 text-white" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="text-xs uppercase opacity-80 font-bold tracking-wider">Tu especialidad detectada</div>
                    <div class="text-xl font-extrabold mt-1">{{ $this->specialtyLabel }}</div>
                    <div class="text-xs opacity-90 mt-1">Los defaults se eligen según esta especialidad. Puedes ajustarlos abajo.</div>
                </div>
                <button type="button" wire:click="resetToDefaults"
                    class="px-4 py-2 rounded-lg bg-white/20 hover:bg-white/30 text-sm font-bold transition">
                    Restablecer a defaults de mi especialidad
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex gap-2" aria-label="Tabs">
                <button type="button" wire:click="$set('tab', 'clinic')"
                    class="px-4 py-2 text-sm font-bold border-b-2 transition
                        {{ $tab === 'clinic' ? 'border-teal-600 text-teal-700 dark:text-teal-300' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    🏥 Configuración de la clínica
                </button>
                <button type="button" wire:click="$set('tab', 'mine')"
                    class="px-4 py-2 text-sm font-bold border-b-2 transition
                        {{ $tab === 'mine' ? 'border-teal-600 text-teal-700 dark:text-teal-300' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    👤 Mi configuración personal
                </button>
            </nav>
        </div>

        {{-- Contenido del tab --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">

            @if($tab === 'clinic')
                <div class="mb-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 text-sm">
                    <strong>Esta config afecta a TODOS los doctores de la clínica</strong> que no hayan configurado un override personal. Si solo eres tú, ajusta aquí.
                </div>
            @endif

            @if($tab === 'mine')
                <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 text-sm">
                    <strong>Override personal</strong> — solo afecta a tu pantalla de consulta. Cuando está activado "Usar configuración de la clínica" heredas lo de arriba.
                </div>

                <label class="flex items-center gap-3 mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-900 cursor-pointer">
                    <input type="checkbox" wire:model.live="inheritsClinicConfig" class="rounded text-teal-600 focus:ring-teal-500">
                    <div>
                        <div class="font-bold text-sm">Usar configuración de la clínica</div>
                        <div class="text-xs text-gray-500">Si lo prendes, heredas lo que el dueño configure para todos.</div>
                    </div>
                </label>
            @endif

            {{-- Grupos de campos --}}
            @php($disabled = ($tab === 'mine' && $inheritsClinicConfig))
            <div class="space-y-6 {{ $disabled ? 'opacity-50 pointer-events-none' : '' }}">
                @foreach($this->fieldsByGroup as $groupKey => $fields)
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
                            {{ $this->groupLabels[$groupKey] ?? $groupKey }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($fields as $field)
                                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-teal-400 transition cursor-pointer
                                    {{ ($enabled[$field['key']] ?? false) ? 'bg-teal-50 dark:bg-teal-900/20 border-teal-400' : 'bg-white dark:bg-gray-800' }}">
                                    <input type="checkbox"
                                        wire:model.live="enabled.{{ $field['key'] }}"
                                        @disabled($disabled)
                                        class="mt-0.5 rounded text-teal-600 focus:ring-teal-500">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-sm">{{ $field['label'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $field['help'] }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Botón guardar --}}
            <div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <button type="button" wire:click="save"
                    class="px-6 py-3 rounded-lg bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold text-sm shadow-lg hover:shadow-xl transition">
                    Guardar cambios
                </button>
            </div>
        </div>

    </div>
</x-filament-panels::page>
