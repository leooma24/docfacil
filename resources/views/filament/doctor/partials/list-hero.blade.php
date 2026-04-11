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
    .lh-hero {
        position: relative;
        border-radius: 1.5rem;
        padding: 28px 32px;
        overflow: hidden;
        background: linear-gradient(135deg, {{ $gradient ?? '#0d9488 0%, #0891b2 40%, #06b6d4 100%' }});
        color: white;
        box-shadow: 0 20px 60px -15px {{ ($accent ?? '#0d9488') }}66, inset 0 1px 0 rgba(255,255,255,0.2);
        margin-bottom: 20px;
    }
    .lh-hero::before {
        content: ''; position: absolute; top: -80px; right: -60px;
        width: 280px; height: 280px;
        background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 70%);
        border-radius: 50%; pointer-events: none;
    }
    .lh-hero::after {
        content: ''; position: absolute; bottom: -100px; left: -40px;
        width: 240px; height: 240px;
        background: radial-gradient(circle, rgba(255,255,255,0.12), transparent 70%);
        border-radius: 50%; pointer-events: none;
    }
    .lh-hero-grain {
        position: absolute; inset: 0;
        background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.08) 1px, transparent 0);
        background-size: 20px 20px; pointer-events: none;
    }
    .lh-hero-content { position: relative; z-index: 1; }
    .lh-hero-top { display: flex; align-items: flex-start; gap: 18px; flex-wrap: wrap; }
    .lh-hero-icon {
        width: 64px; height: 64px; border-radius: 18px;
        background: rgba(255,255,255,0.18);
        backdrop-filter: blur(12px);
        border: 1.5px solid rgba(255,255,255,0.3);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        font-size: 32px;
    }
    .lh-hero-label {
        font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.12em;
        opacity: 0.85; font-weight: 700;
    }
    .lh-hero-title {
        font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em;
        line-height: 1.15; margin-top: 2px; color: white !important;
        -webkit-text-fill-color: white !important; background: none !important;
    }
    .lh-hero-subtitle { font-size: 0.9rem; opacity: 0.9; margin-top: 3px; max-width: 640px; }

    .lh-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-top: 22px;
    }
    @media (min-width: 640px) { .lh-stats { grid-template-columns: repeat(4, 1fr); } }

    .lh-stat {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 14px;
        padding: 14px 16px;
        transition: all 0.2s;
    }
    .lh-stat:hover { background: rgba(255,255,255,0.22); transform: translateY(-2px); }
    .lh-stat-label {
        font-size: 0.63rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.08em;
        opacity: 0.82;
    }
    .lh-stat-value {
        font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em;
        margin-top: 4px; line-height: 1; color: white;
    }

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

<div class="lh-hero">
    <div class="lh-hero-grain"></div>
    <div class="lh-hero-content">
        <div class="lh-hero-top">
            <div class="lh-hero-icon">{{ $icon ?? '📋' }}</div>
            <div style="flex:1;min-width:0;">
                <div class="lh-hero-label">{{ $emoji ?? '' }} {{ $kicker ?? 'Listado' }}</div>
                <h2 class="lh-hero-title">{{ $title ?? 'Listado' }}</h2>
                <div class="lh-hero-subtitle">{{ $subtitle ?? '' }}</div>
            </div>
        </div>

        @if(!empty($stats))
        <div class="lh-stats">
            @foreach($stats as $stat)
            <div class="lh-stat">
                <div class="lh-stat-label">{{ $stat['label'] }}</div>
                <div class="lh-stat-value">{{ $stat['value'] }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
