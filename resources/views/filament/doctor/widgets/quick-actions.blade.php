<x-filament-widgets::widget>
    <style>
        .qa-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        @media (min-width: 640px) { .qa-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 1024px) { .qa-grid { grid-template-columns: repeat(5, 1fr); } }
        .qa-card { position: relative; display: flex; align-items: center; gap: 12px; padding: 18px 16px; border-radius: 1.25rem; text-decoration: none; overflow: hidden; transition: all 0.3s cubic-bezier(0.4,0,0.2,1); backdrop-filter: blur(14px) saturate(180%); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 4px 12px rgba(0,0,0,0.04), inset 0 1px 0 rgba(255,255,255,0.9); }
        .qa-card:hover { transform: translateY(-4px) scale(1.01); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        .qa-card::before { content: ''; position: absolute; inset: 0; opacity: 0.7; z-index: 0; }
        .qa-card > * { position: relative; z-index: 1; }
        .qa-card-teal::before { background: linear-gradient(135deg, #ccfbf1 0%, #f0fdfa 100%); }
        .qa-card-blue::before { background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%); }
        .qa-card-emerald::before { background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 100%); }
        .qa-card-purple::before { background: linear-gradient(135deg, #ede9fe 0%, #faf5ff 100%); }
        .qa-card-amber::before { background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%); }

        .qa-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 6px 16px rgba(0,0,0,0.12), inset 0 1px 0 rgba(255,255,255,0.3); }
        .qa-icon svg { width: 22px; height: 22px; color: white; }
        .qa-icon-teal { background: linear-gradient(135deg, #0d9488, #14b8a6); }
        .qa-icon-blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .qa-icon-emerald { background: linear-gradient(135deg, #059669, #10b981); }
        .qa-icon-purple { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
        .qa-icon-amber { background: linear-gradient(135deg, #d97706, #f59e0b); }

        .qa-text-teal { color: #134e4a; }
        .qa-text-blue { color: #1e3a8a; }
        .qa-text-emerald { color: #064e3b; }
        .qa-text-purple { color: #4c1d95; }
        .qa-text-amber { color: #78350f; }
    </style>

    <div class="qa-grid">
        <a href="{{ route('filament.doctor.resources.citas.create') }}" class="qa-card qa-card-teal">
            <div class="qa-icon qa-icon-teal">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <span style="font-size:0.82rem;font-weight:800;letter-spacing:-0.01em;" class="qa-text-teal">Nueva cita</span>
        </a>
        <a href="{{ route('filament.doctor.resources.pacientes.create') }}" class="qa-card qa-card-blue">
            <div class="qa-icon qa-icon-blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <span style="font-size:0.82rem;font-weight:800;letter-spacing:-0.01em;" class="qa-text-blue">Nuevo paciente</span>
        </a>
        <a href="{{ route('filament.doctor.resources.cobros.create') }}" class="qa-card qa-card-emerald">
            <div class="qa-icon qa-icon-emerald">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
            </div>
            <span style="font-size:0.82rem;font-weight:800;letter-spacing:-0.01em;" class="qa-text-emerald">Cobro</span>
        </a>
        <a href="{{ route('filament.doctor.resources.recetas.create') }}" class="qa-card qa-card-purple">
            <div class="qa-icon qa-icon-purple">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span style="font-size:0.82rem;font-weight:800;letter-spacing:-0.01em;" class="qa-text-purple">Receta</span>
        </a>
        <a href="{{ route('filament.doctor.pages.consulta') }}" class="qa-card qa-card-amber" style="grid-column: span 2;">
            <div class="qa-icon qa-icon-amber">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span style="font-size:0.82rem;font-weight:800;letter-spacing:-0.01em;" class="qa-text-amber">Consulta rápida</span>
        </a>
    </div>

    <style>
        @media (min-width: 640px) {
            .qa-grid .qa-card:last-child { grid-column: auto !important; }
        }
    </style>
</x-filament-widgets::widget>
