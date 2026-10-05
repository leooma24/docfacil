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
    if ($perm) { $filas[] = ['arcada' => 'sup', 'der' => [18, 17, 16, 15, 14, 13, 12, 11], 'izq' => [21, 22, 23, 24, 25, 26, 27, 28]]; }
    if ($temp) { $filas[] = ['arcada' => 'sup', 'der' => [null, null, null, 55, 54, 53, 52, 51], 'izq' => [61, 62, 63, 64, 65, null, null, null]]; }
    $filas[] = 'separador';
    if ($temp) { $filas[] = ['arcada' => 'inf', 'der' => [null, null, null, 85, 84, 83, 82, 81], 'izq' => [71, 72, 73, 74, 75, null, null, null]]; }
    if ($perm) { $filas[] = ['arcada' => 'inf', 'der' => [48, 47, 46, 45, 44, 43, 42, 41], 'izq' => [31, 32, 33, 34, 35, 36, 37, 38]]; }
    // En el celular, el nombre del lado va sobre la primera fila de cada arcada.
    $vistas = [];
    foreach ($filas as $k => $fila) {
        if (is_array($fila)) {
            $filas[$k]['etiquetas'] = ! isset($vistas[$fila['arcada']]);
            $vistas[$fila['arcada']] = true;
        }
    }
    $etiqueta = 'font-size:12px;font-weight:700;letter-spacing:.1em;color:#64748b;';
@endphp
<style>
    .odo-interactivo .odo-cara { cursor: pointer; transition: filter .12s; }
    .odo-interactivo .odo-cara:hover { filter: brightness(.86); }
    .odo-interactivo .odo-silueta { cursor: pointer; }
    .odo-interactivo:not(.odo-sel):hover .odo-fondo { fill: #f8fafc; stroke: #e2e8f0; }
    .odo-escoge { display: none; }
    .odo-escoge button { flex: 1; padding: 10px 12px; min-height: 44px; border-radius: 10px; font-size: 14px; font-weight: 700; border: 1px solid #cbd5e1; background: #fff; color: #475569; }
    .odo-ver-sup .odo-escoge [data-arcada="sup"], .odo-ver-inf .odo-escoge [data-arcada="inf"] { background: #0f766e; border-color: #0f766e; color: #fff; }
    .odo-lado { display: none; }
    /* En el celular, una arcada a la vez y cada lado del paciente en su
       renglón de 8 dientes: casi el doble de grandes para el dedo. Solo en
       pantalla: al imprimir siempre van las dos arcadas juntas. */
    @media screen and (max-width: 760px) {
        .odo-escoge { display: flex; gap: 8px; margin-bottom: 10px; }
        .odo-cabeza, .odo-separador, .odo-pie, .odo-linea { display: none !important; }
        .odo-ver-sup .odo-inf, .odo-ver-inf .odo-sup { display: none !important; }
        .odo-fila { grid-template-columns: 1fr !important; gap: 6px !important; margin-bottom: 6px; }
        .odo-lado { display: block; font-size: 12px; font-weight: 700; letter-spacing: .08em; color: #64748b; margin: 2px 2px 4px; }
    }
</style>
<div wire:key="arcadas-{{ $denticion }}" class="odo-arcadas odo-ver-sup" x-data="{ arcada: 'sup' }" :class="{ 'odo-ver-sup': arcada === 'sup', 'odo-ver-inf': arcada === 'inf' }">
    <div class="odo-escoge" role="group" aria-label="Arcada que se ve">
        <button type="button" data-arcada="sup" x-on:click="arcada = 'sup'">
            Superior
        </button>
        <button type="button" data-arcada="inf" x-on:click="arcada = 'inf'">
            Inferior
        </button>
    </div>
    <div style="max-width:980px;margin:0 auto;">
        <div class="odo-cabeza" style="display:flex;justify-content:space-between;align-items:center;padding:0 6px 6px;{{ $etiqueta }}">
            <span>DERECHA DEL PACIENTE</span><span style="color:#64748b;">ARCADA SUPERIOR</span><span>IZQUIERDA DEL PACIENTE</span>
        </div>
        @foreach($filas as $fila)
            @if($fila === 'separador')
                <div class="odo-separador" style="display:flex;align-items:center;gap:10px;margin:4px 6px;">
                    <div style="flex:1;border-top:1.5px dashed #cbd5e1;"></div>
                    <span style="{{ $etiqueta }}">PLANO OCLUSAL</span>
                    <div style="flex:1;border-top:1.5px dashed #cbd5e1;"></div>
                </div>
                @continue
            @endif
            <div class="odo-fila {{ $fila['arcada'] === 'sup' ? 'odo-sup' : 'odo-inf' }}" style="display:grid;grid-template-columns:1fr 3px 1fr;gap:8px;align-items:stretch;">
                @foreach([$fila['der'], 'linea', $fila['izq']] as $mitad)
                    @if($mitad === 'linea')
                        <div class="odo-linea" style="background:linear-gradient(#e2e8f0,#94a3b8,#e2e8f0);border-radius:2px;"></div>
                        @continue
                    @endif
                    <div>
                        @if($fila['etiquetas'])
                            <div class="odo-lado">{{ $loop->first ? 'DERECHA DEL PACIENTE' : 'IZQUIERDA DEL PACIENTE' }}</div>
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
                    </div>
                @endforeach
            </div>
        @endforeach
        <div class="odo-pie" style="text-align:center;padding-top:6px;{{ $etiqueta }}color:#64748b;">ARCADA INFERIOR</div>
    </div>
</div>
