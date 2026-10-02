@props([
    'dientes' => [],          // [numero => ['condition' => ..., 'surfaces' => [...], 'notes' => ...]]
    'denticion' => 'permanente',
    'interactivo' => false,
    'seleccionado' => null,
])
@php
    // Vista de frente: el lado derecho del paciente queda a la izquierda.
    // Cada mitad tiene 8 lugares; los temporales ocupan los 5 junto a la
    // línea media, debajo de sus permanentes (55 bajo 15, 51 bajo 11).
    $filas = [];
    $perm = $denticion !== 'temporal';
    $temp = $denticion !== 'permanente';
    if ($perm) { $filas[] = ['der' => [18, 17, 16, 15, 14, 13, 12, 11], 'izq' => [21, 22, 23, 24, 25, 26, 27, 28]]; }
    if ($temp) { $filas[] = ['der' => [null, null, null, 55, 54, 53, 52, 51], 'izq' => [61, 62, 63, 64, 65, null, null, null]]; }
    $filas[] = 'separador';
    if ($temp) { $filas[] = ['der' => [null, null, null, 85, 84, 83, 82, 81], 'izq' => [71, 72, 73, 74, 75, null, null, null]]; }
    if ($perm) { $filas[] = ['der' => [48, 47, 46, 45, 44, 43, 42, 41], 'izq' => [31, 32, 33, 34, 35, 36, 37, 38]]; }
    $etiqueta = 'font-size:12px;font-weight:700;letter-spacing:.1em;color:#64748b;';
@endphp
<style>
    .odo-interactivo .odo-cara { cursor: pointer; transition: filter .12s; }
    .odo-interactivo .odo-cara:hover { filter: brightness(.86); }
    .odo-interactivo .odo-silueta { cursor: pointer; }
    .odo-interactivo:not(.odo-sel):hover .odo-fondo { fill: #f8fafc; stroke: #e2e8f0; }
    .odo-desliza { display: none; }
    @media (max-width: 760px) { .odo-desliza { display: flex; } }
</style>
<div class="odo-desliza" style="align-items:center;justify-content:center;gap:6px;font-size:12px;color:#64748b;padding:0 0 8px;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 8l-4 4 4 4M17 8l4 4-4 4M3 12h18"/></svg>
    Desliza para ver toda la boca
</div>
<div wire:key="arcadas-{{ $denticion }}" style="overflow-x:auto;-webkit-overflow-scrolling:touch;padding-bottom:4px;">
    <div style="min-width:720px;max-width:980px;margin:0 auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0 6px 6px;{{ $etiqueta }}">
            <span>DERECHA DEL PACIENTE</span><span style="color:#64748b;">ARCADA SUPERIOR</span><span>IZQUIERDA DEL PACIENTE</span>
        </div>
        @foreach($filas as $fila)
            @if($fila === 'separador')
                <div style="display:flex;align-items:center;gap:10px;margin:4px 6px;">
                    <div style="flex:1;border-top:1.5px dashed #cbd5e1;"></div>
                    <span style="{{ $etiqueta }}">PLANO OCLUSAL</span>
                    <div style="flex:1;border-top:1.5px dashed #cbd5e1;"></div>
                </div>
                @continue
            @endif
            <div style="display:grid;grid-template-columns:1fr 3px 1fr;gap:8px;align-items:stretch;">
                @foreach([$fila['der'], 'linea', $fila['izq']] as $mitad)
                    @if($mitad === 'linea')
                        <div style="background:linear-gradient(#e2e8f0,#94a3b8,#e2e8f0);border-radius:2px;"></div>
                        @continue
                    @endif
                    <div style="display:grid;grid-template-columns:repeat(8,minmax(0,1fr));gap:3px;">
                        @foreach($mitad as $num)
                            @if($num === null)
                                <div wire:key="hueco-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}"></div>
                            @else
                                @php $d = $dientes[$num] ?? []; @endphp
                                <x-odontograma.diente wire:key="diente-{{ $num }}" :numero="$num" :condicion="$d['condition'] ?? 'healthy'" :caras="$d['surfaces'] ?? []" :notas="$d['notes'] ?? null" :interactivo="$interactivo" :seleccionado="$seleccionado === $num" />
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
        <div style="text-align:center;padding-top:6px;{{ $etiqueta }}color:#64748b;">ARCADA INFERIOR</div>
    </div>
</div>
