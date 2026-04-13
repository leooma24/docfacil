{{--
    Hero compacto para forms Create/Edit (sin stats, más bajo que list-hero).
    Variables:
    - $title, $icon, $kicker, $subtitle, $gradient, $accent
--}}
<style>
    .fh-hero {
        position: relative;
        border-radius: 1.25rem;
        padding: 20px 24px;
        overflow: hidden;
        background: linear-gradient(135deg, {{ $gradient ?? '#0d9488 0%, #0891b2 40%, #06b6d4 100%' }});
        color: white;
        box-shadow: 0 14px 40px -12px {{ ($accent ?? '#0d9488') }}66, inset 0 1px 0 rgba(255,255,255,0.2);
        margin-bottom: 18px;
    }
    .fh-hero::before {
        content: ''; position: absolute; top: -60px; right: -40px;
        width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 70%);
        border-radius: 50%; pointer-events: none;
    }
    .fh-hero::after {
        content: ''; position: absolute; bottom: -80px; left: -30px;
        width: 180px; height: 180px;
        background: radial-gradient(circle, rgba(255,255,255,0.12), transparent 70%);
        border-radius: 50%; pointer-events: none;
    }
    .fh-hero-grain {
        position: absolute; inset: 0;
        background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.08) 1px, transparent 0);
        background-size: 20px 20px; pointer-events: none;
    }
    .fh-hero-row {
        position: relative; z-index: 1;
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    }
    .fh-hero-icon {
        width: 54px; height: 54px; border-radius: 16px;
        background: rgba(255,255,255,0.18);
        backdrop-filter: blur(12px);
        border: 1.5px solid rgba(255,255,255,0.3);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        font-size: 28px;
    }
    .fh-hero-label {
        font-size: 0.63rem; text-transform: uppercase; letter-spacing: 0.12em;
        opacity: 0.85; font-weight: 700;
    }
    .fh-hero-title {
        font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em;
        line-height: 1.15; margin-top: 2px; color: white !important;
        -webkit-text-fill-color: white !important; background: none !important;
    }
    .fh-hero-subtitle { font-size: 0.85rem; opacity: 0.88; margin-top: 3px; max-width: 640px; }

    /* Top-border en el form de Filament para amarre visual con el hero */
    .fi-page form.fi-form > .fi-section,
    .fi-page form.fi-form .fi-section {
        position: relative;
        border-radius: 1.25rem !important;
        overflow: hidden;
        border: 1px solid rgba(229, 231, 235, 0.8) !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04), 0 1px 2px {{ ($accent ?? '#0d9488') }}14 !important;
    }
    .dark .fi-page form.fi-form > .fi-section,
    .dark .fi-page form.fi-form .fi-section {
        border-color: rgba(94, 234, 212, 0.15) !important;
    }
    .fi-page form.fi-form > .fi-section:first-of-type::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, {{ $gradient ?? '#0d9488 0%, #0891b2 40%, #06b6d4 100%' }});
        z-index: 10;
    }
</style>

<div class="fh-hero">
    <div class="fh-hero-grain"></div>
    <div class="fh-hero-row">
        <div class="fh-hero-icon">{{ $icon ?? '📋' }}</div>
        <div style="flex:1;min-width:0;">
            <div class="fh-hero-label">{{ $kicker ?? 'Formulario' }}</div>
            <h2 class="fh-hero-title">{{ $title ?? 'Formulario' }}</h2>
            <div class="fh-hero-subtitle">{{ $subtitle ?? '' }}</div>
        </div>
    </div>
</div>
