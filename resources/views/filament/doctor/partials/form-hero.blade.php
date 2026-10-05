{{--
    Hero compacto para forms Create/Edit (sin stats, más bajo que list-hero).
    Variables:
    - $title, $icon, $kicker, $subtitle, $gradient, $accent
--}}
<style>
    /* Solo la indicación del formulario, en una franja clara. Antes era una
       tarjeta de color con el título repetido (ya está arriba como
       encabezado) que empujaba el formulario hacia abajo. */
    .fh-hero {
        border-radius: 12px;
        padding: 12px 16px;
        background: #f0fdfa;
        border: 1px solid #ccfbf1;
        color: #134e4a;
        font-size: 0.9375rem;
        line-height: 1.45;
        margin-bottom: 16px;
    }
    .dark .fh-hero { background: rgba(20, 184, 166, 0.08); border-color: rgba(94, 234, 212, 0.2); color: #ccfbf1; }

    /* Top-border en el form de Filament para amarre visual con el hero */
    .fi-page form.fi-form > .fi-section,
    .fi-page form.fi-form .fi-section {
        position: relative;
        border-radius: 16px !important;
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

@if(filled($subtitle ?? ''))
<div class="fh-hero">{{ $subtitle }}</div>
@endif
