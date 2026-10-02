@php
    use App\Models\OdontogramTooth;
    $inspeccionando = $activeTool === \App\Livewire\OdontogramEditor::INSPECCIONAR;
    $deCara = array_merge(['healthy'], OdontogramTooth::DE_CARA);
    $deDiente = OdontogramTooth::DE_DIENTE;
    $nombresCara = OdontogramTooth::caraLabels();
    $nombres = OdontogramTooth::nombresParaResumen();
    $resumen = OdontogramTooth::resumenDeBoca($teeth);
    $plural = fn (string $c, int $n) => $n . ' ' . ($nombres[$c][$n === 1 ? 0 : 1] ?? $c);

    // Ícono chico de cada condición, el mismo símbolo que en el diente.
    $icono = function (string $c) use ($conditionColors) {
        $col = $conditionColors[$c] ?? '#94a3b8';
        $cara = '<circle cx="9" cy="9" r="7" fill="#fff" stroke="#94a3b8"/><path d="M4.05,4.05 A7,7 0 0 1 13.95,4.05 L11.12,6.88 A3,3 0 0 0 6.88,6.88 Z" fill="' . $col . '"/><circle cx="9" cy="9" r="3" fill="' . $col . '"/>';
        $muela = '<path d="M3,4 L15,4 C16,8 16,11 15,13 C13,16 10,15 9,13 C8,15 5,16 3,13 C2,11 2,8 3,4 Z" fill="%F" stroke="%S" stroke-width="%W"/>';
        $svg = match ($c) {
            'healthy' => '<circle cx="9" cy="9" r="7" fill="#fff" stroke="' . $col . '" stroke-width="1.6"/><path d="M5.5,9.2 L8,11.5 L12.5,6.5" fill="none" stroke="' . $col . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
            'decay', 'filling', 'sealant', 'pending' => $cara,
            'extraction' => '<path d="M4,4 L14,14 M14,4 L4,14" stroke="#dc2626" stroke-width="2.4" stroke-linecap="round"/>',
            'missing' => strtr($muela, ['%F' => '#f1f5f9', '%S' => '#cbd5e1', '%W' => '1']) . '<path d="M2,9 L16,9" stroke="#64748b" stroke-width="2" stroke-linecap="round"/>',
            'implant' => '<path d="M6,16 L7,2 L11,2 L12,16 Z" fill="#cbd5e1" stroke="#64748b"/><path d="M6.3,12 L11.7,12 M6.6,8.5 L11.4,8.5 M6.9,5 L11.1,5" stroke="#64748b"/>',
            'crown' => strtr($muela, ['%F' => $col . '66', '%S' => $col, '%W' => '2']),
            'veneer' => strtr($muela, ['%F' => $col . '44', '%S' => $col, '%W' => '1.4']),
            'bridge' => '<path d="M1,9 L17,9" stroke="' . $col . '" stroke-width="4"/>',
            'root_canal' => '<path d="M6,2 L6,16 M12,2 L12,16" stroke="' . $col . '" stroke-width="2.2" stroke-linecap="round"/>',
            'fracture' => '<path d="M2,7 L6,12 L9,6 L13,12 L16,8" fill="none" stroke="' . $col . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
            default => '<rect x="3" y="3" width="12" height="12" rx="3" fill="' . $col . '"/>',
        };

        return '<svg width="18" height="18" viewBox="0 0 18 18" style="flex:0 0 18px">' . $svg . '</svg>';
    };
    $chip = fn (string $color, bool $activo) => 'display:inline-flex;align-items:center;gap:7px;padding:6px 12px 6px 8px;border-radius:10px;font-size:12.5px;font-weight:600;cursor:pointer;border:1.5px solid '
        . ($activo ? $color : '#e5e7eb') . ';background:' . ($activo ? $color . '14' : '#fff') . ';color:' . ($activo ? '#111827' : '#4b5563') . ';'
        . ($activo ? 'box-shadow:0 0 0 3px ' . $color . '22;' : '');
    $tarjeta = 'background:#fff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 1px 2px rgba(15,23,42,.04);';
    $titulo = 'font-size:12px;font-weight:700;letter-spacing:.09em;color:#64748b;text-transform:uppercase;';
@endphp
<div style="display:flex;flex-direction:column;gap:14px;font-family:Inter,system-ui,sans-serif;">

    {{-- Resumen y dentición --}}
    <div style="{{ $tarjeta }}padding:14px 16px;display:flex;flex-wrap:wrap;gap:14px 28px;align-items:center;justify-content:space-between;">
        <div style="display:flex;flex-wrap:wrap;gap:12px 28px;">
            <div>
                <div style="{{ $titulo }}color:#b91c1c;">Por tratar</div>
                <div style="font-size:13.5px;color:#111827;margin-top:3px;">
                    @forelse($resumen['por_tratar'] as $c => $n)
                        <span style="display:inline-flex;align-items:center;gap:5px;margin-right:12px;">{!! $icono($c) !!}{{ $plural($c, $n) }}</span>
                    @empty
                        <span style="color:#6b7280;">Nada marcado</span>
                    @endforelse
                </div>
            </div>
            <div>
                <div style="{{ $titulo }}color:#1d4ed8;">Tratamientos existentes</div>
                <div style="font-size:13.5px;color:#111827;margin-top:3px;">
                    @forelse($resumen['existentes'] as $c => $n)
                        <span style="display:inline-flex;align-items:center;gap:5px;margin-right:12px;">{!! $icono($c) !!}{{ $plural($c, $n) }}</span>
                    @empty
                        <span style="color:#6b7280;">Ninguno</span>
                    @endforelse
                    @if($resumen['ausentes'])
                        <span style="display:inline-flex;align-items:center;gap:5px;">{!! $icono('missing') !!}{{ $plural('missing', $resumen['ausentes']) }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div style="display:inline-flex;background:#f1f5f9;border-radius:10px;padding:3px;">
            @foreach(['permanente' => 'Permanente', 'mixta' => 'Mixta', 'temporal' => 'Temporal'] as $clave => $nombre)
                <button type="button" wire:click="setDenticion('{{ $clave }}')"
                    style="padding:6px 12px;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer;border:0;{{ $denticion === $clave ? 'background:#fff;color:#0f766e;box-shadow:0 1px 3px rgba(15,23,42,.12);' : 'background:transparent;color:#64748b;' }}">{{ $nombre }}</button>
            @endforeach
        </div>
    </div>

    {{-- Herramientas --}}
    <div style="{{ $tarjeta }}padding:14px 16px;">
        <div style="display:flex;flex-wrap:wrap;gap:16px 28px;align-items:flex-start;">
            <div>
                <div style="{{ $titulo }}margin-bottom:7px;">Modo</div>
                {{-- Por defecto tocar un diente solo lo abre, no lo cambia. --}}
                <button wire:click="setTool('{{ \App\Livewire\OdontogramEditor::INSPECCIONAR }}')" type="button" title="Solo ver el diente, sin cambiarlo" style="{{ $chip('#475569', $inspeccionando) }}">
                    <svg width="18" height="18" fill="none" stroke="#475569" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Ver
                </button>
            </div>
            <div>
                <div style="{{ $titulo }}margin-bottom:7px;">Por cara</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($deCara as $key)
                        <button wire:click="setTool('{{ $key }}')" type="button" style="{{ $chip($conditionColors[$key], $activeTool === $key) }}">{!! $icono($key) !!}{{ $conditionLabels[$key] }}</button>
                    @endforeach
                </div>
            </div>
            <div>
                <div style="{{ $titulo }}margin-bottom:7px;">Todo el diente</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($deDiente as $key)
                        <button wire:click="setTool('{{ $key }}')" type="button" style="{{ $chip($key === 'extraction' ? '#dc2626' : $conditionColors[$key], $activeTool === $key) }}">{!! $icono($key) !!}{{ $conditionLabels[$key] }}</button>
                    @endforeach
                </div>
            </div>
        </div>
        <div style="margin-top:10px;font-size:12.5px;color:#64748b;display:flex;align-items:center;gap:6px;">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="#94a3b8"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 12H9V9h2v5zm0-7H9V5h2v2z"/></svg>
            @if($inspeccionando)
                Toca un diente para ver su detalle, o elige una herramienta.
            @elseif(in_array($activeTool, $deCara))
                Toca la <strong style="color:#334155;">cara</strong> del círculo para marcar {{ mb_strtolower($conditionLabels[$activeTool]) }}.
            @else
                Toca el <strong style="color:#334155;">diente</strong> para marcar {{ mb_strtolower($conditionLabels[$activeTool]) }}.
            @endif
        </div>
    </div>

    {{-- Arcadas --}}
    <div style="{{ $tarjeta }}padding:16px 10px 12px;background:linear-gradient(180deg,#fbfdff 0%,#ffffff 100%);">
        <x-odontograma.arcadas :dientes="$teeth" :denticion="$denticion" :interactivo="true" :seleccionado="$selectedTooth" />
    </div>

    {{-- Diente seleccionado --}}
    @if($selectedTooth)
        @php $sel = $teeth[$selectedTooth] ?? []; $marcadas = array_filter($sel['surfaces'] ?? []); @endphp
        <div style="{{ $tarjeta }}border-color:#99f6e4;padding:16px;display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start;">
            <div style="width:84px;flex:0 0 84px;">
                <x-odontograma.diente :numero="$selectedTooth" :condicion="$sel['condition'] ?? 'healthy'" :caras="$sel['surfaces'] ?? []" />
            </div>
            <div style="flex:1 1 280px;min-width:0;">
                <div style="font-size:12px;color:#64748b;">Diente {{ $selectedTooth }} · {{ ucfirst(OdontogramTooth::tipo($selectedTooth)) }}{{ $selectedTooth >= 51 ? ' temporal' : '' }}</div>
                <div style="display:flex;align-items:center;gap:7px;font-weight:700;font-size:16px;color:#111827;margin:2px 0 8px;">
                    {!! $icono($selectedCondition) !!}{{ $conditionLabels[$selectedCondition] ?? 'Sano' }}
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">
                    @foreach($nombresCara as $cara => $nombre)
                        @php $c = $sel['surfaces'][$cara] ?? null; @endphp
                        <span style="font-size:12px;padding:4px 9px;border-radius:999px;border:1px solid {{ $c ? $conditionColors[$c] : '#e5e7eb' }};background:{{ $c ? $conditionColors[$c] . '14' : '#f8fafc' }};color:{{ $c ? '#111827' : '#94a3b8' }};">
                            {{ $nombre }}{{ $c ? ': ' . $conditionLabels[$c] : '' }}
                        </span>
                    @endforeach
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
        </div>
    @endif

    {{-- Leyenda --}}
    <div style="{{ $tarjeta }}padding:12px 16px;">
        <div style="{{ $titulo }}margin-bottom:8px;">Leyenda</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px 18px;font-size:12.5px;color:#4b5563;">
            @foreach($conditionLabels as $key => $label)
                <span style="display:inline-flex;align-items:center;gap:6px;">{!! $icono($key) !!}{{ $label }}</span>
            @endforeach
        </div>
        <div style="font-size:12px;color:#64748b;margin-top:8px;">Círculo de cinco caras: el centro es oclusal/incisal, el lado hacia la línea media es mesial, y vestibular mira hacia afuera de la boca. Los números en amarillo son dientes temporales.</div>
    </div>
</div>
