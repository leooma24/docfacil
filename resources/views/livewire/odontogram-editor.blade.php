@php
    use App\Models\OdontogramTooth;
    $inspeccionando = $activeTool === \App\Livewire\OdontogramEditor::INSPECCIONAR;
    $deCara = array_merge(['healthy'], OdontogramTooth::DE_CARA);
    $deDiente = OdontogramTooth::DE_DIENTE;
    $nombresCara = OdontogramTooth::caraLabels();
    $chip = function (string $key, string $label, string $color, bool $activo) {
        $base = 'display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:600;border:1.5px solid ' . $color . ';cursor:pointer;transition:transform .1s;';
        return $base . ($activo ? 'background:' . $color . ';color:#fff;box-shadow:0 2px 6px ' . $color . '55;' : 'background:#fff;color:#374151;');
    };
    $muestra = fn (string $color) => 'display:inline-block;width:10px;height:10px;border-radius:3px;background:' . $color . ';';
@endphp
<div style="display:flex;flex-direction:column;gap:16px;">
    {{-- Herramientas --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px 16px;">
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:10px;">
            {{-- Modo por defecto: tocar un diente solo lo abre, no lo cambia. --}}
            <button wire:click="setTool('{{ \App\Livewire\OdontogramEditor::INSPECCIONAR }}')" type="button"
                title="Solo ver el diente, sin cambiarlo" style="{{ $chip('inspect', 'Ver', '#475569', $inspeccionando) }}">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Ver
            </button>
            <span style="font-size:12px;color:#6b7280;">
                @if($inspeccionando)
                    Elige una herramienta y toca la cara o el diente.
                @elseif(in_array($activeTool, $deCara))
                    Toca la <strong>cara</strong> del diente (el cuadro de abajo) para marcarla.
                @else
                    Toca el <strong>diente</strong> para marcarlo completo.
                @endif
            </span>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:14px 24px;">
            <div>
                <div style="font-size:10px;font-weight:700;letter-spacing:.08em;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Por cara</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($deCara as $key)
                        <button wire:click="setTool('{{ $key }}')" type="button" style="{{ $chip($key, $conditionLabels[$key], $conditionColors[$key], $activeTool === $key) }}">
                            <span style="{{ $muestra($activeTool === $key ? '#ffffff' : $conditionColors[$key]) }}"></span>{{ $conditionLabels[$key] }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div>
                <div style="font-size:10px;font-weight:700;letter-spacing:.08em;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Todo el diente</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($deDiente as $key)
                        <button wire:click="setTool('{{ $key }}')" type="button" style="{{ $chip($key, $conditionLabels[$key], $conditionColors[$key], $activeTool === $key) }}">
                            <span style="{{ $muestra($activeTool === $key ? '#ffffff' : $conditionColors[$key]) }}"></span>{{ $conditionLabels[$key] }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Arcadas, vistas de frente: el lado derecho del paciente a la izquierda --}}
    <div style="background:linear-gradient(#f8fafc,#fff);border:1px solid #e5e7eb;border-radius:14px;padding:14px 8px;">
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <div style="min-width:700px;display:flex;flex-direction:column;align-items:center;">
                <div style="display:flex;justify-content:space-between;width:690px;font-size:10px;font-weight:700;letter-spacing:.08em;color:#9ca3af;margin-bottom:4px;">
                    <span>DERECHA DEL PACIENTE</span><span>SUPERIOR</span><span>IZQUIERDA DEL PACIENTE</span>
                </div>
                @foreach([[$upperRight, $upperLeft], [$lowerRight, $lowerLeft]] as $fila => [$mitadDerecha, $mitadIzquierda])
                    @if($fila === 1)
                        <div style="width:690px;border-top:1.5px dashed #d1d5db;margin:6px 0;"></div>
                    @endif
                    <div style="display:flex;align-items:stretch;gap:2px;">
                        @foreach($mitadDerecha as $num)
                            <x-odontograma.diente :numero="$num" :condicion="$teeth[$num]['condition'] ?? 'healthy'" :caras="$teeth[$num]['surfaces'] ?? []" :notas="$teeth[$num]['notes'] ?? null" :interactivo="true" :seleccionado="$selectedTooth === $num" />
                        @endforeach
                        <div style="width:2px;background:#cbd5e1;margin:0 6px;border-radius:2px;"></div>
                        @foreach($mitadIzquierda as $num)
                            <x-odontograma.diente :numero="$num" :condicion="$teeth[$num]['condition'] ?? 'healthy'" :caras="$teeth[$num]['surfaces'] ?? []" :notas="$teeth[$num]['notes'] ?? null" :interactivo="true" :seleccionado="$selectedTooth === $num" />
                        @endforeach
                    </div>
                @endforeach
                <div style="font-size:10px;font-weight:700;letter-spacing:.08em;color:#9ca3af;margin-top:4px;">INFERIOR</div>
            </div>
        </div>
    </div>

    {{-- Diente seleccionado --}}
    @if($selectedTooth)
        @php $sel = $teeth[$selectedTooth] ?? []; $marcadas = array_filter($sel['surfaces'] ?? []); @endphp
        <div style="background:#fff;border:2px solid #99f6e4;border-radius:14px;padding:16px;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="width:42px;height:42px;border-radius:12px;background:#f0fdfa;border:1px solid #99f6e4;display:flex;align-items:center;justify-content:center;font-weight:800;color:#0f766e;font-size:16px;">{{ $selectedTooth }}</div>
                <div>
                    <div style="font-size:12px;color:#6b7280;">Diente {{ $selectedTooth }} · {{ ucfirst(\App\Models\OdontogramTooth::tipo($selectedTooth)) }}</div>
                    <div style="display:inline-flex;align-items:center;gap:6px;font-weight:600;font-size:14px;color:{{ $conditionColors[$selectedCondition] ?? '#374151' }};">
                        <span style="{{ $muestra($conditionColors[$selectedCondition] ?? '#94a3b8') }}"></span>{{ $conditionLabels[$selectedCondition] ?? 'Sano' }}
                    </div>
                    @if($marcadas)
                        <div style="font-size:12px;color:#374151;margin-top:2px;">
                            @foreach($marcadas as $cara => $cond)
                                <span style="margin-right:10px;">{{ $nombresCara[$cara] }}: <strong style="color:{{ $conditionColors[$cond] }}">{{ $conditionLabels[$cond] }}</strong></span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#4b5563;margin-bottom:6px;">Condición del diente</label>
                    <select wire:model.live="selectedCondition" wire:change="updateTooth"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500" style="width:100%;">
                        @foreach($conditionLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#4b5563;margin-bottom:6px;">Notas</label>
                    <input type="text" wire:model.blur="toothNotes" wire:change="updateTooth"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500" style="width:100%;"
                        placeholder="Observaciones del diente...">
                </div>
            </div>
        </div>
    @endif

    {{-- Leyenda --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:12px 16px;">
        <div style="font-size:10px;font-weight:700;letter-spacing:.08em;color:#6b7280;text-transform:uppercase;margin-bottom:8px;">Leyenda</div>
        <div style="display:flex;flex-wrap:wrap;gap:6px 16px;font-size:12px;color:#4b5563;">
            @foreach($conditionLabels as $key => $label)
                <span style="display:inline-flex;align-items:center;gap:6px;"><span style="{{ $muestra($conditionColors[$key]) }}"></span>{{ $label }}</span>
            @endforeach
        </div>
        <div style="font-size:11px;color:#9ca3af;margin-top:8px;">Cuadro de cinco caras: arriba vestibular en la arcada superior (abajo en la inferior), centro oclusal/incisal, y mesial del lado de la línea media.</div>
    </div>
</div>
