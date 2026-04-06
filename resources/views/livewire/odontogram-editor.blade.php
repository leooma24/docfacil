<div class="space-y-4">
    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-2 p-3 bg-gray-50 rounded-lg border">
        <span class="text-sm font-medium text-gray-700 self-center mr-2">Herramienta:</span>
        @foreach($conditionLabels as $key => $label)
        <button
            wire:click="setTool('{{ $key }}')"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full border transition
                {{ $activeTool === $key ? 'ring-2 ring-offset-1 ring-teal-500 border-teal-500' : 'border-gray-300 hover:border-gray-400' }}"
        >
            <span class="w-3 h-3 rounded-full inline-block" style="background-color: {{ $conditionColors[$key] }}"></span>
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Odontogram Chart --}}
    <div class="bg-white border rounded-lg p-4">
        {{-- Upper jaw --}}
        <div class="text-center text-xs text-gray-500 font-medium mb-2">SUPERIOR</div>
        <div class="flex justify-center gap-0.5 mb-1">
            {{-- Upper Right (18-11) --}}
            @foreach($upperRight as $num)
            <div
                wire:click="applyTool({{ $num }})"
                class="cursor-pointer group relative"
            >
                <div class="w-10 h-12 rounded-t-lg border-2 flex flex-col items-center justify-center transition-all hover:scale-110
                    {{ $selectedTooth === $num ? 'border-teal-500 shadow-lg' : 'border-gray-300' }}"
                    style="background-color: {{ $conditionColors[$teeth[$num]['condition']] ?? '#10b981' }}20;"
                >
                    <div class="w-5 h-5 rounded-sm" style="background-color: {{ $conditionColors[$teeth[$num]['condition']] ?? '#10b981' }};"></div>
                    <span class="text-[10px] font-bold text-gray-700 mt-0.5">{{ $num }}</span>
                </div>
            </div>
            @endforeach

            <div class="w-px bg-gray-400 mx-1 self-stretch"></div>

            {{-- Upper Left (21-28) --}}
            @foreach($upperLeft as $num)
            <div
                wire:click="applyTool({{ $num }})"
                class="cursor-pointer group relative"
            >
                <div class="w-10 h-12 rounded-t-lg border-2 flex flex-col items-center justify-center transition-all hover:scale-110
                    {{ $selectedTooth === $num ? 'border-teal-500 shadow-lg' : 'border-gray-300' }}"
                    style="background-color: {{ $conditionColors[$teeth[$num]['condition']] ?? '#10b981' }}20;"
                >
                    <div class="w-5 h-5 rounded-sm" style="background-color: {{ $conditionColors[$teeth[$num]['condition']] ?? '#10b981' }};"></div>
                    <span class="text-[10px] font-bold text-gray-700 mt-0.5">{{ $num }}</span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Divider --}}
        <div class="border-t-2 border-gray-400 my-2 mx-4"></div>

        {{-- Lower jaw --}}
        <div class="flex justify-center gap-0.5 mt-1">
            {{-- Lower Left (38-31) --}}
            @foreach($lowerLeft as $num)
            <div
                wire:click="applyTool({{ $num }})"
                class="cursor-pointer group relative"
            >
                <div class="w-10 h-12 rounded-b-lg border-2 flex flex-col items-center justify-center transition-all hover:scale-110
                    {{ $selectedTooth === $num ? 'border-teal-500 shadow-lg' : 'border-gray-300' }}"
                    style="background-color: {{ $conditionColors[$teeth[$num]['condition']] ?? '#10b981' }}20;"
                >
                    <span class="text-[10px] font-bold text-gray-700 mb-0.5">{{ $num }}</span>
                    <div class="w-5 h-5 rounded-sm" style="background-color: {{ $conditionColors[$teeth[$num]['condition']] ?? '#10b981' }};"></div>
                </div>
            </div>
            @endforeach

            <div class="w-px bg-gray-400 mx-1 self-stretch"></div>

            {{-- Lower Right (48-41) --}}
            @foreach($lowerRight as $num)
            <div
                wire:click="applyTool({{ $num }})"
                class="cursor-pointer group relative"
            >
                <div class="w-10 h-12 rounded-b-lg border-2 flex flex-col items-center justify-center transition-all hover:scale-110
                    {{ $selectedTooth === $num ? 'border-teal-500 shadow-lg' : 'border-gray-300' }}"
                    style="background-color: {{ $conditionColors[$teeth[$num]['condition']] ?? '#10b981' }}20;"
                >
                    <span class="text-[10px] font-bold text-gray-700 mb-0.5">{{ $num }}</span>
                    <div class="w-5 h-5 rounded-sm" style="background-color: {{ $conditionColors[$teeth[$num]['condition']] ?? '#10b981' }};"></div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center text-xs text-gray-500 font-medium mt-2">INFERIOR</div>
    </div>

    {{-- Selected tooth detail --}}
    @if($selectedTooth)
    <div class="bg-gray-50 border rounded-lg p-4">
        <div class="flex items-center gap-3 mb-3">
            <span class="text-lg font-bold text-gray-900">Diente #{{ $selectedTooth }}</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full"
                  style="background-color: {{ $conditionColors[$selectedCondition] }}20; color: {{ $conditionColors[$selectedCondition] }};">
                <span class="w-2 h-2 rounded-full" style="background-color: {{ $conditionColors[$selectedCondition] }}"></span>
                {{ $conditionLabels[$selectedCondition] }}
            </span>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Condición</label>
                <select wire:model.live="selectedCondition" wire:change="updateTooth"
                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    @foreach($conditionLabels as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <input type="text" wire:model.blur="toothNotes" wire:change="updateTooth"
                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    placeholder="Observaciones del diente...">
            </div>
        </div>
    </div>
    @endif

    {{-- Legend --}}
    <div class="flex flex-wrap gap-3 p-3 bg-gray-50 rounded-lg border text-xs">
        @foreach($conditionLabels as $key => $label)
        <span class="inline-flex items-center gap-1">
            <span class="w-3 h-3 rounded-sm" style="background-color: {{ $conditionColors[$key] }}"></span>
            {{ $label }}
        </span>
        @endforeach
    </div>
</div>
