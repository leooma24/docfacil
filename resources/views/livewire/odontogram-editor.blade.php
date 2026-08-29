<div class="space-y-4">
    {{-- Toolbar de condiciones --}}
    <div class="bg-white border border-gray-200 rounded-xl p-3 md:p-4">
        <div class="text-[10px] md:text-xs font-bold tracking-wider text-gray-500 uppercase mb-2.5">Selecciona herramienta</div>
        <div class="flex flex-wrap gap-2">
            {{-- Modo por defecto: tocar un diente solo lo abre, no lo cambia.
                 Va explicito en la barra para que se vea que es un modo mas,
                 y para que "Sano" quede libre de despintar de verdad. --}}
            @php $inspeccionando = $activeTool === \App\Livewire\OdontogramEditor::INSPECCIONAR; @endphp
            <button
                wire:click="setTool('{{ \App\Livewire\OdontogramEditor::INSPECCIONAR }}')"
                type="button"
                title="Solo ver el diente, sin cambiarlo"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full border-2 transition
                    {{ $inspeccionando ? 'shadow-sm scale-105' : 'bg-white hover:scale-105' }}"
                style="border-color: #64748b;
                    {{ $inspeccionando ? 'background-color: #64748b20; color: #64748b;' : 'color: #4b5563;' }}"
            >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Ver
            </button>
            @foreach($conditionLabels as $key => $label)
            <button
                wire:click="setTool('{{ $key }}')"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full border-2 transition
                    {{ $activeTool === $key ? 'shadow-sm scale-105' : 'bg-white hover:scale-105' }}"
                style="
                    border-color: {{ $conditionColors[$key] }};
                    {{ $activeTool === $key ? 'background-color: ' . $conditionColors[$key] . '20; color: ' . $conditionColors[$key] . ';' : 'color: #4b5563;' }}"
            >
                <span class="w-2.5 h-2.5 rounded-sm inline-block" style="background-color: {{ $conditionColors[$key] }}"></span>
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Arcada dental --}}
    <div class="bg-gradient-to-b from-gray-50 to-white border border-gray-200 rounded-xl p-3 md:p-5">
        {{-- ARCADA SUPERIOR --}}
        <div class="text-[10px] md:text-xs font-bold tracking-wider text-gray-400 text-center mb-2">SUPERIOR</div>
        <div class="flex justify-center gap-0.5 md:gap-1 mb-1">
            {{-- Cuadrante sup. derecho (paciente) — visualmente a la izquierda del que ve el doctor --}}
            @foreach($upperRight as $num)
            @php
                $cond = $teeth[$num]['condition'] ?? 'sano';
                $color = $conditionColors[$cond] ?? '#cbd5e1';
                $isSelected = $selectedTooth === $num;
            @endphp
            <button type="button" wire:click="applyTool({{ $num }})"
                class="group relative cursor-pointer focus:outline-none"
                title="Diente {{ $num }} — {{ $conditionLabels[$cond] ?? 'Sano' }}{{ !empty($teeth[$num]['notes']) ? ' · ' . $teeth[$num]['notes'] : '' }}">
                <div class="w-7 h-9 md:w-9 md:h-12 rounded-t-xl border-2 flex flex-col items-center justify-end pb-1 transition hover:scale-110
                    {{ $isSelected ? 'ring-2 ring-offset-1 ring-teal-500 shadow-md' : '' }}"
                    style="background-color: {{ $color }}25; border-color: {{ $color }};">
                    <div class="w-3 h-3 md:w-4 md:h-4 rounded-sm" style="background-color: {{ $color }};"></div>
                    <span class="text-[8px] md:text-[10px] font-bold text-gray-700 mt-0.5">{{ $num }}</span>
                </div>
            </button>
            @endforeach

            {{-- Línea media --}}
            <div class="w-px bg-gray-300 mx-1 self-stretch"></div>

            @foreach($upperLeft as $num)
            @php
                $cond = $teeth[$num]['condition'] ?? 'sano';
                $color = $conditionColors[$cond] ?? '#cbd5e1';
                $isSelected = $selectedTooth === $num;
            @endphp
            <button type="button" wire:click="applyTool({{ $num }})"
                class="group relative cursor-pointer focus:outline-none"
                title="Diente {{ $num }} — {{ $conditionLabels[$cond] ?? 'Sano' }}{{ !empty($teeth[$num]['notes']) ? ' · ' . $teeth[$num]['notes'] : '' }}">
                <div class="w-7 h-9 md:w-9 md:h-12 rounded-t-xl border-2 flex flex-col items-center justify-end pb-1 transition hover:scale-110
                    {{ $isSelected ? 'ring-2 ring-offset-1 ring-teal-500 shadow-md' : '' }}"
                    style="background-color: {{ $color }}25; border-color: {{ $color }};">
                    <div class="w-3 h-3 md:w-4 md:h-4 rounded-sm" style="background-color: {{ $color }};"></div>
                    <span class="text-[8px] md:text-[10px] font-bold text-gray-700 mt-0.5">{{ $num }}</span>
                </div>
            </button>
            @endforeach
        </div>

        {{-- Separador entre arcadas --}}
        <div class="border-t-2 border-dashed border-gray-300 my-3 mx-4"></div>

        {{-- ARCADA INFERIOR --}}
        <div class="flex justify-center gap-0.5 md:gap-1 mt-1">
            @foreach($lowerLeft as $num)
            @php
                $cond = $teeth[$num]['condition'] ?? 'sano';
                $color = $conditionColors[$cond] ?? '#cbd5e1';
                $isSelected = $selectedTooth === $num;
            @endphp
            <button type="button" wire:click="applyTool({{ $num }})"
                class="group relative cursor-pointer focus:outline-none"
                title="Diente {{ $num }} — {{ $conditionLabels[$cond] ?? 'Sano' }}{{ !empty($teeth[$num]['notes']) ? ' · ' . $teeth[$num]['notes'] : '' }}">
                <div class="w-7 h-9 md:w-9 md:h-12 rounded-b-xl border-2 flex flex-col items-center justify-start pt-1 transition hover:scale-110
                    {{ $isSelected ? 'ring-2 ring-offset-1 ring-teal-500 shadow-md' : '' }}"
                    style="background-color: {{ $color }}25; border-color: {{ $color }};">
                    <span class="text-[8px] md:text-[10px] font-bold text-gray-700 mb-0.5">{{ $num }}</span>
                    <div class="w-3 h-3 md:w-4 md:h-4 rounded-sm" style="background-color: {{ $color }};"></div>
                </div>
            </button>
            @endforeach

            <div class="w-px bg-gray-300 mx-1 self-stretch"></div>

            @foreach($lowerRight as $num)
            @php
                $cond = $teeth[$num]['condition'] ?? 'sano';
                $color = $conditionColors[$cond] ?? '#cbd5e1';
                $isSelected = $selectedTooth === $num;
            @endphp
            <button type="button" wire:click="applyTool({{ $num }})"
                class="group relative cursor-pointer focus:outline-none"
                title="Diente {{ $num }} — {{ $conditionLabels[$cond] ?? 'Sano' }}{{ !empty($teeth[$num]['notes']) ? ' · ' . $teeth[$num]['notes'] : '' }}">
                <div class="w-7 h-9 md:w-9 md:h-12 rounded-b-xl border-2 flex flex-col items-center justify-start pt-1 transition hover:scale-110
                    {{ $isSelected ? 'ring-2 ring-offset-1 ring-teal-500 shadow-md' : '' }}"
                    style="background-color: {{ $color }}25; border-color: {{ $color }};">
                    <span class="text-[8px] md:text-[10px] font-bold text-gray-700 mb-0.5">{{ $num }}</span>
                    <div class="w-3 h-3 md:w-4 md:h-4 rounded-sm" style="background-color: {{ $color }};"></div>
                </div>
            </button>
            @endforeach
        </div>
        <div class="text-[10px] md:text-xs font-bold tracking-wider text-gray-400 text-center mt-2">INFERIOR</div>
    </div>

    {{-- Detalle del diente seleccionado --}}
    @if($selectedTooth)
    <div class="bg-white border-2 border-teal-200 rounded-xl p-4 md:p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-center">
                <span class="text-base font-extrabold text-teal-700">{{ $selectedTooth }}</span>
            </div>
            <div>
                <div class="text-xs text-gray-500">Diente</div>
                <div class="inline-flex items-center gap-1.5 mt-0.5">
                    <span class="w-2.5 h-2.5 rounded-sm" style="background-color: {{ $conditionColors[$selectedCondition] }}"></span>
                    <span class="text-sm font-semibold" style="color: {{ $conditionColors[$selectedCondition] }};">
                        {{ $conditionLabels[$selectedCondition] }}
                    </span>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Cambiar condición</label>
                <select wire:model.live="selectedCondition" wire:change="updateTooth"
                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    @foreach($conditionLabels as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Notas</label>
                <input type="text" wire:model.blur="toothNotes" wire:change="updateTooth"
                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    placeholder="Observaciones del diente...">
            </div>
        </div>
    </div>
    @endif

    {{-- Leyenda completa de todas las condiciones disponibles --}}
    <div class="bg-white border border-gray-200 rounded-xl p-3">
        <div class="text-[10px] md:text-xs font-bold tracking-wider text-gray-500 uppercase mb-2">Leyenda</div>
        <div class="flex flex-wrap gap-x-3 gap-y-1.5 text-[11px] md:text-xs">
            @foreach($conditionLabels as $key => $label)
            <span class="inline-flex items-center gap-1.5 text-gray-600">
                <span class="w-2.5 h-2.5 rounded-sm" style="background-color: {{ $conditionColors[$key] }}"></span>
                {{ $label }}
            </span>
            @endforeach
        </div>
    </div>
</div>
