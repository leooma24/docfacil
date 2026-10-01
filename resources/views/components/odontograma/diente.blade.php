@props([
    'numero',
    'condicion' => 'healthy',
    'caras' => [],
    'notas' => null,
    'interactivo' => false,
    'seleccionado' => false,
])
@php
    use App\Models\OdontogramTooth;

    $colores = OdontogramTooth::conditionColors();
    $etiquetas = OdontogramTooth::conditionLabels();
    $nombresCara = OdontogramTooth::caraLabels();
    $condicion = $condicion ?: 'healthy';
    if ($condicion === 'sano') { $condicion = 'healthy'; }
    $caras = array_merge(OdontogramTooth::carasVacias(), $caras ?? []);
    $hayCaras = (bool) array_filter($caras);
    $color = $colores[$condicion] ?? '#94a3b8';

    $cuadrante = intdiv($numero, 10);
    $esSuperior = in_array($cuadrante, [1, 2]);
    // Mesial es la cara que mira a la línea media: a la derecha en los
    // cuadrantes 1 y 4 (la mitad izquierda del dibujo), a la izquierda en 2 y 3.
    $mesialDerecha = in_array($cuadrante, [1, 4]);
    $tipo = OdontogramTooth::tipo($numero);

    // Dibujo con las raíces hacia arriba; la arcada inferior se voltea.
    $raices = [
        'incisivo' => 'M15,27 L18,3 Q20,0 22,3 L25,27 Z',
        'canino'   => 'M15,27 L18,1 Q20,-1 22,1 L25,27 Z',
        'premolar' => 'M14,27 L18,4 Q20,2 22,4 L26,27 Z',
        'molar'    => 'M7,27 L9,6 Q11,4 13,6 L16,27 Z M16,27 L19,10 Q20,8 21,10 L24,27 Z M24,27 L27,6 Q29,4 31,6 L33,27 Z',
    ][$tipo];
    $corona = [
        'incisivo' => 'M13,26 L27,26 L29,44 Q20,50 11,44 Z',
        'canino'   => 'M12,26 L28,26 L28,40 L20,49 L12,40 Z',
        'premolar' => 'M10,26 L30,26 L31,42 Q25,49 20,45 Q15,49 9,42 Z',
        'molar'    => 'M6,26 L34,26 L35,41 Q30,49 25,44 Q20,49 15,44 Q10,49 5,41 Z',
    ][$tipo];
    $conductos = $tipo === 'molar' ? 'M11,25 L11,8 M20,25 L20,12 M29,25 L29,8' : 'M20,25 L20,5';

    $polCaras = [
        'arriba'    => '4,52 36,52 26,62 14,62',
        'abajo'     => '14,74 26,74 36,84 4,84',
        'izquierda' => '4,52 14,62 14,74 4,84',
        'derecha'   => '36,52 36,84 26,74 26,62',
    ];
    $dondeVa = [
        'vestibular' => $polCaras['arriba'],
        'lingual'    => $polCaras['abajo'],
        'mesial'     => $mesialDerecha ? $polCaras['derecha'] : $polCaras['izquierda'],
        'distal'     => $mesialDerecha ? $polCaras['izquierda'] : $polCaras['derecha'],
    ];

    $tinteCorona = (in_array($condicion, ['crown', 'veneer']) || (! $hayCaras && in_array($condicion, OdontogramTooth::DE_CARA)))
        ? $color : null;
    $ausente = $condicion === 'missing';

    $titulo = 'Diente ' . $numero . ' — ' . ($etiquetas[$condicion] ?? 'Sano') . ($notas ? ' · ' . $notas : '');
    $clic = fn (string $metodo, ?string $cara = null) => $interactivo
        ? 'wire:click="' . $metodo . '(' . $numero . ($cara ? ", '" . $cara . "'" : '') . ')" style="cursor:pointer"'
        : '';
@endphp
<div title="{{ $titulo }}" style="display:inline-block;width:40px;flex:0 0 40px;">
    <svg viewBox="0 0 40 104" width="40" height="104" xmlns="http://www.w3.org/2000/svg" style="display:block;overflow:visible">
        @if($seleccionado)
            <rect x="0.5" y="0.5" width="39" height="103" rx="7" fill="#ccfbf1" stroke="#14b8a6" stroke-width="1.5"/>
        @endif
        <text x="20" y="{{ $esSuperior ? 11 : 101 }}" text-anchor="middle" font-family="Inter, system-ui, sans-serif" font-size="10" font-weight="700" fill="{{ $seleccionado ? '#0f766e' : '#334155' }}">{{ $numero }}</text>

        <g transform="{{ $esSuperior ? 'translate(0,15)' : 'translate(0,89) scale(1,-1)' }}">
            {{-- Silueta: raíz y corona. Tocarla aplica la herramienta al diente. --}}
            <g {!! $clic('applyTool') !!} opacity="{{ $ausente ? '0.18' : '1' }}">
                @if($condicion === 'implant')
                    <rect x="16" y="4" width="8" height="22" rx="2" fill="{{ $color }}33" stroke="{{ $color }}" stroke-width="1.4"/>
                    <path d="M16,9 L24,9 M16,13 L24,13 M16,17 L24,17 M16,21 L24,21" stroke="{{ $color }}" stroke-width="1"/>
                @else
                    <path d="{{ $raices }}" fill="#f8fafc" stroke="#cbd5e1" stroke-width="1.1" stroke-linejoin="round"/>
                @endif
                <path d="{{ $corona }}" fill="{{ $tinteCorona ? $tinteCorona . '40' : '#ffffff' }}" stroke="{{ $tinteCorona ?: '#94a3b8' }}" stroke-width="{{ $condicion === 'crown' ? '2.6' : '1.3' }}" stroke-linejoin="round"/>
                @if($condicion === 'root_canal')
                    <path d="{{ $conductos }}" stroke="{{ $color }}" stroke-width="2.2" stroke-linecap="round"/>
                @endif
                @if($condicion === 'fracture')
                    <path d="M8,31 L15,38 L21,31 L28,39 L33,34" fill="none" stroke="{{ $color }}" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round"/>
                @endif
                <rect x="4" y="2" width="32" height="48" fill="transparent"/>
            </g>
            @if($ausente)
                <path d="M6,24 L34,24" stroke="{{ $color }}" stroke-width="2.4" stroke-linecap="round"/>
            @endif
            @if($condicion === 'extraction')
                <path d="M7,5 L33,47 M33,5 L7,47" stroke="{{ $color }}" stroke-width="3" stroke-linecap="round"/>
            @endif
            @if($condicion === 'bridge')
                <path d="M-1,29 L41,29" stroke="{{ $color }}" stroke-width="4"/>
            @endif

            {{-- Diagrama de cinco caras. --}}
            @foreach($dondeVa as $cara => $puntos)
                <polygon points="{{ $puntos }}" data-cara="{{ $numero }}-{{ $cara }}" fill="{{ $caras[$cara] ? ($colores[$caras[$cara]] ?? '#ffffff') : '#ffffff' }}" stroke="#94a3b8" stroke-width="1" {!! $clic('applySurface', $cara) !!}><title>{{ $numero }} · {{ $nombresCara[$cara] }}{{ $caras[$cara] ? ': ' . ($etiquetas[$caras[$cara]] ?? '') : '' }}</title></polygon>
            @endforeach
            <rect x="14" y="62" width="12" height="12" data-cara="{{ $numero }}-oclusal" fill="{{ $caras['oclusal'] ? ($colores[$caras['oclusal']] ?? '#ffffff') : '#ffffff' }}" stroke="#94a3b8" stroke-width="1" {!! $clic('applySurface', 'oclusal') !!}><title>{{ $numero }} · {{ $nombresCara['oclusal'] }}{{ $caras['oclusal'] ? ': ' . ($etiquetas[$caras['oclusal']] ?? '') : '' }}</title></rect>
        </g>
    </svg>
</div>
