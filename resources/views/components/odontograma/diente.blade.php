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
    $esSuperior = OdontogramTooth::esSuperior($numero);
    $temporal = $cuadrante >= 5;
    // Mesial mira a la línea media: a la derecha en la mitad izquierda del
    // dibujo (cuadrantes 1, 4, 5 y 8), a la izquierda en la otra mitad.
    $mesialDerecha = in_array($cuadrante, [1, 4, 5, 8]);
    $tipo = OdontogramTooth::tipo($numero);

    // Silueta con las raíces hacia arriba (y 0–44) y la corona abajo (40–78).
    // La arcada inferior se dibuja volteada.
    $raices = match (true) {
        $tipo === 'molar' && $esSuperior => 'M9,44 C9,30 11,13 15,8 C19,13 21,30 22,44 Z M22,44 C23,32 25,17 28,14 C31,17 33,32 34,44 Z M34,44 C35,30 37,13 41,8 C45,13 47,30 47,44 Z',
        $tipo === 'molar' => 'M10,44 C10,28 13,11 18,6 C23,11 25,28 26,44 Z M30,44 C31,28 33,11 38,6 C43,11 46,28 46,44 Z',
        $tipo === 'premolar' && in_array($numero, [14, 24]) => 'M16,44 C16,30 18,13 22,9 C25,13 27,30 28,44 Z M28,44 C29,30 31,13 34,9 C38,13 40,30 40,44 Z',
        $tipo === 'premolar' => 'M17,44 C18,26 22,9 28,6 C34,9 38,26 39,44 Z',
        $tipo === 'canino' => 'M19,44 C20,22 24,2 28,0 C32,2 36,22 37,44 Z',
        default => 'M19,44 C20,24 23,7 28,4 C33,7 36,24 37,44 Z',
    };
    $corona = [
        'incisivo' => 'M17,40 L39,40 L41,66 C41,74 35,78 28,78 C21,78 15,74 15,66 Z',
        'canino'   => 'M16,40 L40,40 L41,60 C40,68 34,74 28,79 C22,74 16,68 15,60 Z',
        'premolar' => 'M12,40 L44,40 C46,52 46,62 44,68 C41,75 35,74 33,70 C31,75 25,75 23,70 C21,74 15,75 12,68 C10,62 10,52 12,40 Z',
        'molar'    => 'M6,40 L50,40 C53,52 53,63 50,69 C47,76 41,75 38,71 C35,76 31,77 28,72 C25,77 21,76 18,71 C15,75 9,76 6,69 C3,63 3,52 6,40 Z',
    ][$tipo];
    $cuspides = [
        'incisivo' => null,
        'canino'   => 'M22,62 C25,66 31,66 34,62',
        'premolar' => 'M18,60 C23,64 33,64 38,60',
        'molar'    => 'M13,59 C18,63 22,61 28,65 C34,61 38,63 43,59 M28,48 L28,58',
    ][$tipo];
    $conductos = match (true) {
        $tipo === 'molar' && $esSuperior => 'M15,42 L15,14 M28,42 L28,18 M41,42 L41,14',
        $tipo === 'molar' => 'M18,42 L18,12 M38,42 L38,12',
        default => 'M28,42 L28,8',
    };
    $brillo = $tipo === 'molar' ? 'M12,45 C16,44 22,44 26,45' : 'M20,45 C22,44 25,44 27,45';

    // Cara de cinco partes: círculo en (28,100). "arriba" mira a las raíces,
    // que en la arcada superior es vestibular; al voltear la inferior,
    // vestibular queda abajo, como en la boca.
    $segmentos = [
        'arriba'    => 'M18.1,90.1 A14,14 0 0 1 37.9,90.1 L32.24,95.76 A6,6 0 0 0 23.76,95.76 Z',
        'derecha'   => 'M37.9,90.1 A14,14 0 0 1 37.9,109.9 L32.24,104.24 A6,6 0 0 0 32.24,95.76 Z',
        'abajo'     => 'M37.9,109.9 A14,14 0 0 1 18.1,109.9 L23.76,104.24 A6,6 0 0 0 32.24,104.24 Z',
        'izquierda' => 'M18.1,109.9 A14,14 0 0 1 18.1,90.1 L23.76,95.76 A6,6 0 0 0 23.76,104.24 Z',
    ];
    $dondeVa = [
        'vestibular' => $segmentos['arriba'],
        'lingual'    => $segmentos['abajo'],
        'mesial'     => $mesialDerecha ? $segmentos['derecha'] : $segmentos['izquierda'],
        'distal'     => $mesialDerecha ? $segmentos['izquierda'] : $segmentos['derecha'],
    ];
    $rellenoCara = fn (?string $c) => $c ? ($colores[$c] ?? '#ffffff') : '#ffffff';

    $tinteCorona = (in_array($condicion, ['crown', 'veneer']) || (! $hayCaras && in_array($condicion, OdontogramTooth::DE_CARA)))
        ? $color : null;
    $ausente = $condicion === 'missing';
    $escala = $temporal ? 'translate(28,44) scale(0.84) translate(-28,-44)' : '';
    $yNum = $esSuperior ? 3 : 122;

    $titulo = 'Diente ' . $numero . ' — ' . ($etiquetas[$condicion] ?? 'Sano') . ($notas ? ' · ' . $notas : '');
    $clic = fn (string $metodo, ?string $cara = null) => $interactivo
        ? 'wire:click="' . $metodo . '(' . $numero . ($cara ? ", '" . $cara . "'" : '') . ')"'
        : '';
@endphp
<div {{ $attributes->only('wire:key') }} title="{{ $titulo }}" class="odo-diente{{ $interactivo ? ' odo-interactivo' : '' }}{{ $seleccionado ? ' odo-sel' : '' }}" style="min-width:0;">
    <svg viewBox="0 0 56 140" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;height:auto;overflow:visible">
        <defs>
            <linearGradient id="odo-esmalte" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#ece5d5"/>
            </linearGradient>
            <linearGradient id="odo-raiz" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#e2d2ad"/><stop offset="1" stop-color="#f3ebd8"/>
            </linearGradient>
        </defs>

        <rect class="odo-fondo" x="1" y="1" width="54" height="138" rx="10"
            fill="{{ $seleccionado ? '#f0fdfa' : 'transparent' }}" stroke="{{ $seleccionado ? '#14b8a6' : 'transparent' }}" stroke-width="1.5"/>

        {{-- Número en etiqueta, del lado de afuera de la boca --}}
        <rect x="11" y="{{ $yNum }}" width="34" height="17" rx="8.5" fill="{{ $seleccionado ? '#0d9488' : ($temporal ? '#fef3c7' : '#f1f5f9') }}"/>
        <text x="28" y="{{ $yNum + 12.5 }}" text-anchor="middle" font-family="Inter, system-ui, sans-serif" font-size="12" font-weight="700" fill="{{ $seleccionado ? '#ffffff' : ($temporal ? '#92400e' : '#334155') }}">{{ $numero }}</text>

        <g transform="{{ $esSuperior ? 'translate(0,20)' : 'translate(0,120) scale(1,-1)' }}">
            {{-- Silueta. Tocarla aplica la herramienta al diente entero. --}}
            <g class="odo-silueta" {!! $clic('applyTool') !!} transform="{{ $escala }}" opacity="{{ $ausente ? '0.14' : '1' }}">
                <rect x="2" y="0" width="52" height="80" fill="transparent"/>
                @if($condicion === 'implant')
                    <path d="M21,44 L23,6 L33,6 L35,44 Z" fill="#cbd5e1" stroke="#64748b" stroke-width="1.2" stroke-linejoin="round"/>
                    <path d="M21.5,36 L34.5,36 M21.8,29 L34.2,29 M22.2,22 L33.8,22 M22.6,15 L33.4,15" stroke="#64748b" stroke-width="1.4"/>
                @else
                    <path d="{{ $raices }}" fill="url(#odo-raiz)" stroke="#c4b087" stroke-width="1.1" stroke-linejoin="round"/>
                @endif
                <path d="{{ $corona }}" fill="{{ $tinteCorona ? $tinteCorona . '55' : 'url(#odo-esmalte)' }}"
                    stroke="{{ $tinteCorona ?: '#a8a090' }}" stroke-width="{{ $condicion === 'crown' ? '2.8' : '1.3' }}" stroke-linejoin="round"/>
                @if($cuspides)
                    <path d="{{ $cuspides }}" fill="none" stroke="#cfc6b4" stroke-width="1.1" stroke-linecap="round"/>
                @endif
                <path d="{{ $brillo }}" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" opacity=".8"/>
                @if($condicion === 'root_canal')
                    <path d="{{ $conductos }}" stroke="{{ $color }}" stroke-width="2.6" stroke-linecap="round"/>
                @endif
                @if($condicion === 'fracture')
                    <path d="M9,52 L19,62 L27,52 L36,63 L47,55" fill="none" stroke="{{ $color }}" stroke-width="2.8" stroke-linejoin="round" stroke-linecap="round"/>
                @endif
            </g>
            @if($ausente)
                <path d="M10,40 L46,40" stroke="#64748b" stroke-width="2.6" stroke-linecap="round"/>
            @endif
            @if($condicion === 'extraction')
                <path d="M10,6 L46,76 M46,6 L10,76" stroke="#dc2626" stroke-width="3.6" stroke-linecap="round"/>
            @endif
            @if($condicion === 'bridge')
                <path d="M-3,47 L59,47" stroke="{{ $color }}" stroke-width="5" stroke-linecap="square"/>
            @endif

            {{-- Cara de cinco partes --}}
            <g class="odo-caras">
                @foreach($dondeVa as $cara => $d)
                    <path class="odo-cara" data-cara="{{ $numero }}-{{ $cara }}" fill="{{ $rellenoCara($caras[$cara]) }}" d="{{ $d }}" stroke="#94a3b8" stroke-width="1" {!! $clic('applySurface', $cara) !!}><title>{{ $numero }} · {{ $nombresCara[$cara] }}{{ $caras[$cara] ? ': ' . ($etiquetas[$caras[$cara]] ?? '') : '' }}</title></path>
                @endforeach
                <circle class="odo-cara" data-cara="{{ $numero }}-oclusal" fill="{{ $rellenoCara($caras['oclusal']) }}" cx="28" cy="100" r="6" stroke="#94a3b8" stroke-width="1" {!! $clic('applySurface', 'oclusal') !!}><title>{{ $numero }} · {{ $nombresCara['oclusal'] }}{{ $caras['oclusal'] ? ': ' . ($etiquetas[$caras['oclusal']] ?? '') : '' }}</title></circle>
            </g>
        </g>
    </svg>
</div>
