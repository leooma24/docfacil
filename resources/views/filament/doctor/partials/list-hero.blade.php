{{--
    Hero glassmorphism reutilizable para listados.
    Variables esperadas:
    - $title (string)
    - $emoji (string) — emoji + label corto
    - $subtitle (string)
    - $gradient (string) — ej: '#0d9488 0%, #0891b2 40%, #06b6d4 100%'
    - $accent (string) — color sólido para la sombra/borde superior (ej: #0d9488)
    - $stats (array) — [['label' => '...', 'value' => '...'], ...]
--}}
<style>
    /* Franja compacta de números arriba de la tabla. Antes era una tarjeta
       de 230px con el título repetido (ya está arriba como encabezado de la
       página): en una laptop de 1366x768 la primera cita aparecía a media
       pantalla, y en el celular no se veía ninguna sin bajar. */
    .lh-hero {
        position: relative;
        border-radius: 16px;
        padding: 14px 16px;
        overflow: hidden;
        /* Un solo color en todas las pantallas (el de cada módulo queda en la
           rayita de la tabla): con amarillos y verdes claros el texto blanco
           no se leía. */
        background: linear-gradient(135deg, #0f766e 0%, #0e7490 100%);
        color: white;
        box-shadow: 0 10px 30px -15px rgba(15, 118, 110, 0.5);
        margin-bottom: 16px;
    }
    .lh-hero-content { position: relative; z-index: 1; }

    .lh-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    @media (min-width: 640px) { .lh-stats { grid-template-columns: repeat({{ max(1, min(4, count($stats ?? []))) }}, minmax(0, 1fr)); } }

    .lh-stat {
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.22);
        border-radius: 12px;
        padding: 10px 14px;
        min-width: 0;
    }
    .lh-stat-label { font-size: 0.8125rem; font-weight: 600; line-height: 1.25; }
    .lh-stat-value { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; margin-top: 2px; line-height: 1.1; color: white; overflow-wrap: anywhere; }

    /* Wrap la tabla de Filament en un container con topo-borde con el accent */
    .fi-page > .fi-section,
    .fi-page > section.fi-ta-ctn,
    .fi-page .fi-resource-list-records-page > .fi-ta {
        position: relative;
        border-radius: 1.25rem !important;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04), 0 1px 2px {{ ($accent ?? '#0d9488') }}14 !important;
        border: 1px solid rgba(229, 231, 235, 0.8) !important;
    }
    .dark .fi-page > .fi-section,
    .dark .fi-page > section.fi-ta-ctn,
    .dark .fi-page .fi-resource-list-records-page > .fi-ta {
        border-color: rgba(94, 234, 212, 0.15) !important;
    }
    .fi-page > .fi-section::before,
    .fi-page > section.fi-ta-ctn::before,
    .fi-page .fi-resource-list-records-page > .fi-ta::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, {{ $gradient ?? '#0d9488 0%, #0891b2 40%, #06b6d4 100%' }});
        z-index: 10;
    }
</style>

@if(!empty($stats))
<div class="lh-hero">
    <div class="lh-hero-content">
        <div class="lh-stats">
            @foreach($stats as $stat)
            <div class="lh-stat">
                {{-- Sin emoji: se ve distinto en cada celular y no dice nada que no diga el texto. --}}
                <div class="lh-stat-label">{{ trim(preg_replace('/[\x{1F000}-\x{1FAFF}\x{2300}-\x{23FF}\x{2B00}-\x{2BFF}\x{2600}-\x{27BF}\x{FE0F}\x{200D}]/u', '', $stat['label'])) }}</div>
                <div class="lh-stat-value">{{ $stat['value'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
