{{-- Cierra el menu lateral en pantallas chicas.

     Filament guarda una sola bandera en localStorage ("isOpen") y la usa
     para dos cosas distintas: en escritorio decide si el menu esta ancho o
     colapsado a iconos, y en celular si esta abierto encima del contenido.
     Como no mira el tamano de pantalla, un menu abierto en la laptop llegaba
     abierto al celular y tapaba casi toda la vista.

     Aqui lo cerramos debajo del breakpoint lg (1024px), pero guardamos aparte
     como lo tenia el doctor en escritorio para devolverselo tal cual cuando
     vuelva a una pantalla grande. --}}
<script>
    (function () {
        var LG = 1024;
        var MEMORIA = 'docfacil.sidebarEscritorio';

        function menu() {
            return window.Alpine && window.Alpine.store
                ? window.Alpine.store('sidebar')
                : null;
        }

        function ajustarSegunPantalla() {
            var m = menu();
            if (!m) return;

            if (window.innerWidth < LG) {
                // Guardamos la preferencia de escritorio antes de cerrarlo,
                // una sola vez: si ya hay algo guardado, el usuario sigue en
                // pantalla chica y lo que tenga ahora no es su preferencia.
                if (m.isOpen) {
                    if (localStorage.getItem(MEMORIA) === null) {
                        localStorage.setItem(MEMORIA, '1');
                    }
                    m.isOpen = false;
                }
                return;
            }

            var guardado = localStorage.getItem(MEMORIA);
            if (guardado !== null) {
                localStorage.removeItem(MEMORIA);
                m.isOpen = guardado === '1';
            }
        }

        document.addEventListener('alpine:initialized', ajustarSegunPantalla);
        window.addEventListener('resize', ajustarSegunPantalla);

        // Por si Alpine ya arranco antes de que corriera este script.
        if (window.Alpine) ajustarSegunPantalla();
    })();
</script>

<style>
    /* ===== DOCFACIL CUSTOM THEME ===== */

    /* x-cloak: oculta elementos hasta que Alpine los procesa.
       Esto es CRÍTICO para el botón hamburguesa del topbar de Filament
       que usa x-cloak + x-show — sin esta regla, queda visible 1 frame
       antes de que Alpine lo procese, causando flash. Con esta regla,
       Alpine remueve x-cloak automáticamente en init() y el botón
       aparece correctamente. */
    [x-cloak] { display: none !important; }

    /* Garantiza que el botón hamburguesa (mobile) y close-sidebar siempre
       sean visibles cuando deben — sobre-especificidad para vencer cualquier
       conflicto con el resto del theme. */
    .fi-topbar .fi-topbar-open-sidebar-btn,
    .fi-topbar .fi-topbar-close-sidebar-btn {
        color: #0f172a !important;
        opacity: 1 !important;
    }

    /* Sidebar ALWAYS dark - both light and dark mode */

    aside.fi-sidebar,
    .fi-sidebar,
    .fi-sidebar-nav,
    aside.fi-sidebar > * {
        /* Plano, casi negro con verde: el degradado de antes se veía de otra época. */
        background: #072522 !important;
        background-color: #072522 !important;
    }

    .fi-sidebar .fi-sidebar-header {
        border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        padding: 1rem !important;
        background: transparent !important;
    }

    .fi-sidebar .fi-sidebar-header img {
        height: 2.4rem !important;
        max-height: 2.4rem !important;
    }

    /* Collapse button */
    .fi-sidebar-close-btn,
    .fi-sidebar-open-btn,
    button.fi-sidebar-close-btn,
    button.fi-sidebar-open-btn {
        color: rgba(255, 255, 255, 0.6) !important;
    }

    .fi-sidebar-close-btn:hover,
    .fi-sidebar-open-btn:hover {
        color: #ffffff !important;
    }

    /* All sidebar text and icons white */
    .fi-sidebar .fi-sidebar-item-label,
    .fi-sidebar a .fi-sidebar-item-label,
    .fi-sidebar span.fi-sidebar-item-label {
        color: rgba(255, 255, 255, 0.8) !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
    }

    .fi-sidebar .fi-sidebar-item-icon,
    .fi-sidebar svg.fi-sidebar-item-icon {
        color: rgba(255, 255, 255, 0.5) !important;
    }

    aside.fi-sidebar {
        border-right: 1px solid rgba(255, 255, 255, 0.06) !important;
        background-image: radial-gradient(120% 40% at 0% 0%, rgba(45, 212, 191, 0.10), transparent 60%) !important;
    }
    .fi-sidebar .fi-sidebar-header { border-bottom: 0 !important; }

    /* Consultorio y botón principal */
    .dfm-top { padding: 0.25rem 0.1rem 1rem; display: flex; flex-direction: column; gap: 0.75rem; }
    .dfm-clinica { display: flex; align-items: center; gap: 0.65rem; padding: 0 0.35rem; }
    .dfm-avatar { width: 2.25rem; height: 2.25rem; flex: 0 0 2.25rem; border-radius: 0.6rem; display: grid; place-items: center;
        background: linear-gradient(140deg, #2dd4bf, #0d9488); color: #042f2e; font-weight: 800; font-size: 0.8rem; letter-spacing: 0.02em; }
    .dfm-avatar-img { object-fit: cover; background: #ffffff; height: 2.25rem !important; max-height: 2.25rem !important; }
    .dfm-clinica-txt { display: flex; flex-direction: column; min-width: 0; line-height: 1.25; }
    .dfm-clinica-nombre { color: #ffffff; font-weight: 700; font-size: 0.86rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dfm-clinica-sub { color: rgba(255, 255, 255, 0.5); font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dfm-cta { display: flex; align-items: center; gap: 0.7rem; padding: 0.7rem 0.85rem; border-radius: 0.75rem;
        background: #2dd4bf; color: #042f2e !important; box-shadow: 0 8px 22px -10px rgba(45, 212, 191, 0.7), inset 0 1px 0 rgba(255,255,255,0.35);
        transition: transform .15s ease, background .15s ease; }
    .dfm-cta:hover { background: #5eead4; }
    .dfm-cta:active { transform: translateY(1px) scale(0.99); }
    .dfm-cta svg { flex: 0 0 18px; }
    .dfm-cta-txt { display: flex; flex-direction: column; min-width: 0; line-height: 1.2; }
    .dfm-cta-t { font-weight: 800; font-size: 0.88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dfm-cta-s { font-size: 0.75rem; font-weight: 600; opacity: 0.75; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Plan y prueba, abajo */
    .dfm-plan { display: flex; flex-direction: column; gap: 0.35rem; margin: 0.5rem 0.75rem 0.9rem; padding: 0.7rem 0.8rem; border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.07); }
    .dfm-plan:hover { background: rgba(255, 255, 255, 0.08); }
    .dfm-plan-t { color: #ffffff; font-weight: 700; font-size: 0.82rem; }
    .dfm-plan-prueba .dfm-plan-t { color: #fde68a; }
    .dfm-plan-s { color: rgba(255, 255, 255, 0.55); font-size: 0.75rem; }
    .dfm-barra { display: block; height: 4px; border-radius: 999px; background: rgba(255, 255, 255, 0.1); overflow: hidden; }
    .dfm-barra > span { display: block; height: 100%; border-radius: inherit; background: #fbbf24; }

    /* El nombre junto al logo, solo en el menú (el login usa el mismo logo
       sobre fondo claro). */
    .fi-sidebar .fi-sidebar-header a { display: flex; align-items: center; gap: 0.6rem; }
    .fi-sidebar .fi-sidebar-header img { height: 2.4rem !important; max-height: 2.4rem !important; }
    .fi-sidebar .fi-sidebar-header a::after {
        content: 'DocFácil';
        color: #ffffff;
        font-weight: 800;
        font-size: 1.2rem;
        letter-spacing: -0.01em;
    }
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-header a::after,
    .fi-sidebar:not(.fi-sidebar-open) .dfm-top,
    .fi-sidebar:not(.fi-sidebar-open) .dfm-plan { display: none !important; }
    @media (prefers-reduced-motion: reduce) { .dfm-cta { transition: none; } }

    /* Renglones del menú: sin caja; el fondo aparece al pasar y en el activo. */
    .fi-sidebar .fi-sidebar-nav { padding: 0.75rem 0.75rem 1.5rem !important; }
    .fi-sidebar .fi-sidebar-nav { row-gap: 0 !important; gap: 0 !important; }
    /* Filament le pone margen negativo a los lados: el activo se pegaba a la orilla. */
    .fi-sidebar .fi-sidebar-nav-groups { row-gap: 1.1rem !important; margin: 0 !important; }
    .fi-sidebar .fi-sidebar-group-items { row-gap: 2px !important; }
    .fi-sidebar .fi-sidebar-item-button {
        padding: 0.5rem 0.7rem !important;
        border-radius: 0.6rem !important;
        background: transparent !important;
        box-shadow: none !important;
        border: 0 !important;
        transition: background .15s ease, color .15s ease !important;
    }

    .fi-sidebar .fi-sidebar-item-button:hover {
        background: rgba(255, 255, 255, 0.07) !important;
    }

    .fi-sidebar .fi-sidebar-item-button:hover .fi-sidebar-item-label,
    .fi-sidebar .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
        color: #ffffff !important;
    }

    /* Solo el renglón en el que está el doctor. El grupo también lleva
       .fi-active cuando uno de sus renglones lo está; apuntarle al grupo
       pintaba como activos a todos sus renglones. */
    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-button {
        background: rgba(45, 212, 191, 0.12) !important;
        position: relative;
    }
    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-button::before {
        content: ''; position: absolute; left: -0.55rem; top: 22%; bottom: 22%; width: 3px; border-radius: 999px; background: #5eead4;
    }

    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-button .fi-sidebar-item-label {
        color: #ffffff !important;
        font-weight: 600 !important;
    }

    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-button .fi-sidebar-item-icon {
        color: #5eead4 !important;
    }

    /* Títulos de grupo */
    .fi-sidebar .fi-sidebar-group-label,
    .fi-sidebar span.fi-sidebar-group-label,
    .fi-sidebar .fi-sidebar-group > button > span {
        color: rgba(255, 255, 255, 0.62) !important;
        font-size: 0.75rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.12em !important;
        font-weight: 700 !important;
    }

    .fi-sidebar .fi-sidebar-group > button,
    .fi-sidebar .fi-sidebar-group-button {
        padding: 0.25rem 0.7rem !important;
    }

    .fi-sidebar .fi-sidebar-group-collapse-button,
    .fi-sidebar .fi-sidebar-group button svg {
        color: rgba(255, 255, 255, 0.35) !important;
    }

    /* Contadores del menú: chicos y con su color (rojo = vencido). */
    .fi-sidebar .fi-sidebar-item .fi-badge {
        background: rgba(255, 255, 255, 0.16) !important;
        color: #ffffff !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
        padding: 0.05rem 0.5rem !important;
        min-width: 1.35rem;
        justify-content: center;
        font-size: 0.75rem !important;
        box-shadow: none !important;
        border: 0 !important;
    }

    .fi-sidebar .fi-sidebar-item .fi-badge.fi-color-danger {
        background: #ef4444 !important;
    }

    /* ===== TOP BAR ===== */
    .fi-topbar {
        background: #ffffff !important;
        border-bottom: none !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
    }

    .dark .fi-topbar {
        background: #1f2937 !important;
    }

    /* ===== MAIN CONTENT ===== */
    .fi-main {
        background: #f8fafb !important;
    }

    .dark .fi-main {
        background: #111827 !important;
    }

    /* ===== CARDS ===== */
    .fi-section,
    .fi-ta-ctn {
        border-radius: 1rem !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
        overflow: hidden;
    }

    .dark .fi-section,
    .dark .fi-ta-ctn {
        border-color: #374151 !important;
    }

    /* Stats cards */
    .fi-wi-stats-overview-stat {
        border-radius: 1rem !important;
        border: 1px solid #e5e7eb !important;
        transition: all 0.2s ease !important;
    }

    .dark .fi-wi-stats-overview-stat {
        border-color: #374151 !important;
    }

    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06) !important;
        border-color: #14b8a6 !important;
    }

    /* Buttons */
    .fi-btn {
        border-radius: 0.75rem !important;
        font-weight: 600 !important;
        transition: all 0.15s ease !important;
    }

    .fi-btn:hover {
        transform: translateY(-1px) !important;
    }

    /* Table rows */
    .fi-ta-row:hover {
        background: #f0fdfa !important;
    }

    .dark .fi-ta-row:hover {
        background: rgba(20, 184, 166, 0.05) !important;
    }

    /* Form inputs */
    .fi-input, .fi-select, textarea, select {
        border-radius: 0.75rem !important;
        transition: all 0.15s ease !important;
    }

    .fi-input:focus, .fi-select:focus, textarea:focus, select:focus {
        border-color: #14b8a6 !important;
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1) !important;
    }

    /* Login page - invert white logo back to visible */
    .fi-simple-layout img {
        filter: brightness(0) saturate(100%) invert(35%) sepia(90%) saturate(500%) hue-rotate(140deg) !important;
    }

    .fi-simple-layout {
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 50%, #f0fdfa 100%) !important;
    }

    .dark .fi-simple-layout {
        background: linear-gradient(135deg, #042f2e 0%, #0c1f2e 50%, #042f2e 100%) !important;
    }

    .fi-simple-main-ctn {
        border-radius: 1.5rem !important;
        box-shadow: 0 20px 60px rgba(0,0,0,0.08) !important;
    }

    /* Page heading */
    .fi-header-heading {
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
    }

    /* Modal */
    .fi-modal-content {
        border-radius: 1rem !important;
    }

    /* FullCalendar styling */
    .fc .fc-timegrid-slot {
        height: 3rem !important;
    }

    .fc .fc-event {
        border-radius: 8px !important;
        border-left-width: 3px !important;
        padding: 2px 6px !important;
        font-size: 0.8rem !important;
    }

    .fc .fc-col-header-cell {
        padding: 0.75rem 0 !important;
        font-weight: 600 !important;
        text-transform: capitalize !important;
    }

    .fc .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        text-transform: capitalize !important;
    }

    .fc .fc-button {
        border-radius: 0.5rem !important;
        font-weight: 600 !important;
        font-size: 0.8rem !important;
        padding: 0.4rem 0.75rem !important;
    }

    .fc .fc-button-primary {
        background: #f3f4f6 !important;
        border-color: #e5e7eb !important;
        color: #374151 !important;
    }

    .fc .fc-button-primary:hover {
        background: #e5e7eb !important;
    }

    .fc .fc-button-primary.fc-button-active {
        background: #0d9488 !important;
        border-color: #0d9488 !important;
        color: white !important;
    }

    .fc .fc-today-button {
        background: #14b8a6 !important;
        border-color: #14b8a6 !important;
        color: white !important;
    }

    .fc .fc-day-today {
        background: rgba(20, 184, 166, 0.04) !important;
    }

    .fc .fc-timegrid-now-indicator-line {
        border-color: #ef4444 !important;
        border-width: 2px !important;
    }

    /* Widget cards */
    .fi-wi-chart {
        border-radius: 1rem !important;
    }

    /* ========================================
       🎨 GLASSMORPHISM MEDICAL THEME v2
       ======================================== */

    /* Body background with organic blobs */
    .fi-main,
    .fi-layout > div:not(.fi-sidebar) {
        position: relative;
    }

    .fi-main::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background:
            radial-gradient(circle at 15% 20%, rgba(13, 148, 136, 0.08) 0%, transparent 45%),
            radial-gradient(circle at 85% 75%, rgba(8, 145, 178, 0.08) 0%, transparent 45%),
            radial-gradient(circle at 50% 100%, rgba(139, 92, 246, 0.05) 0%, transparent 45%);
        pointer-events: none;
        z-index: 0;
    }

    .fi-main > * {
        position: relative;
        z-index: 1;
    }

    /* Main content padding */
    .fi-main {
        background: linear-gradient(180deg, #f8fafc 0%, #f0fdfa 100%) !important;
    }

    .dark .fi-main {
        background: linear-gradient(180deg, #0f172a 0%, #042f2e 100%) !important;
    }

    .dark .fi-main::before {
        background:
            radial-gradient(circle at 15% 20%, rgba(13, 148, 136, 0.12) 0%, transparent 45%),
            radial-gradient(circle at 85% 75%, rgba(8, 145, 178, 0.10) 0%, transparent 45%),
            radial-gradient(circle at 50% 100%, rgba(139, 92, 246, 0.08) 0%, transparent 45%);
    }

    /* Page headers - bigger and lighter */
    .fi-page-header-heading,
    .fi-header-heading,
    h1.fi-header-heading {
        font-size: 1.875rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
        background: linear-gradient(135deg, #0f172a 0%, #0d9488 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .dark .fi-page-header-heading,
    .dark .fi-header-heading {
        background: linear-gradient(135deg, #f0fdfa 0%, #5eead4 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* ====== GLASS CARDS ====== */
    .fi-section,
    .fi-wi-stats-overview-stat,
    .fi-fo-section,
    .fi-wi-widget .fi-section {
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        box-shadow:
            0 1px 3px rgba(0, 0, 0, 0.02),
            0 8px 30px rgba(13, 148, 136, 0.06),
            inset 0 1px 0 rgba(255, 255, 255, 0.8) !important;
        border-radius: 1.25rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .dark .fi-section,
    .dark .fi-wi-stats-overview-stat,
    .dark .fi-fo-section {
        background: rgba(15, 23, 42, 0.6) !important;
        border: 1px solid rgba(94, 234, 212, 0.15) !important;
        box-shadow:
            0 1px 3px rgba(0, 0, 0, 0.3),
            0 8px 30px rgba(13, 148, 136, 0.15) !important;
    }

    .fi-section:hover,
    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow:
            0 1px 3px rgba(0, 0, 0, 0.02),
            0 12px 40px rgba(13, 148, 136, 0.12),
            inset 0 1px 0 rgba(255, 255, 255, 0.8) !important;
    }

    /* ====== STATS OVERVIEW (big numbers) ====== */
    .fi-wi-stats-overview-stat-value {
        font-size: 2.5rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.03em !important;
        background: linear-gradient(135deg, #0d9488 0%, #0891b2 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1 !important;
    }

    .fi-wi-stats-overview-stat-label {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.08em !important;
        color: #64748b !important;
        margin-bottom: 0.5rem !important;
    }

    .fi-wi-stats-overview-stat-description {
        font-size: 0.8125rem !important;
        color: #64748b !important;
    }

    .fi-wi-stats-overview-stat-icon {
        background: linear-gradient(135deg, rgba(13,148,136,0.1), rgba(8,145,178,0.1)) !important;
        border-radius: 0.75rem !important;
        padding: 0.5rem !important;
        color: #0d9488 !important;
    }

    /* ====== BUTTONS - Modern gradient ====== */
    .fi-btn-color-primary,
    button.fi-btn.fi-color-primary,
    .fi-ac-btn-action.fi-color-primary {
        /* Tono 700: con el 600 el texto blanco no llegaba a 4.5:1. */
        background: linear-gradient(135deg, #0f766e 0%, #0e7490 100%) !important;
        border: none !important;
        box-shadow:
            0 4px 14px rgba(13, 148, 136, 0.35),
            inset 0 1px 0 rgba(255, 255, 255, 0.25) !important;
        font-weight: 600 !important;
        transition: all 0.2s !important;
    }

    .fi-btn-color-primary:hover,
    button.fi-btn.fi-color-primary:hover {
        transform: translateY(-1px);
        box-shadow:
            0 6px 20px rgba(13, 148, 136, 0.45),
            inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
    }

    .fi-btn {
        border-radius: 0.75rem !important;
        font-weight: 600 !important;
        transition: all 0.2s !important;
    }

    /* ====== INPUTS - Floating style ====== */
    .fi-input,
    input[type="text"].fi-input,
    input[type="email"].fi-input,
    input[type="number"].fi-input,
    input[type="password"].fi-input,
    input[type="tel"].fi-input,
    textarea.fi-input,
    select.fi-input {
        background: rgba(255, 255, 255, 0.6) !important;
        backdrop-filter: blur(10px);
        border: 1.5px solid rgba(13, 148, 136, 0.15) !important;
        border-radius: 0.75rem !important;
        transition: all 0.2s !important;
    }

    .fi-input:focus,
    input.fi-input:focus,
    textarea.fi-input:focus,
    select.fi-input:focus {
        background: white !important;
        border-color: #0d9488 !important;
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1) !important;
    }

    .dark .fi-input {
        background: rgba(15, 23, 42, 0.5) !important;
        border-color: rgba(94, 234, 212, 0.2) !important;
    }

    /* ====== TABLES - Glass rows ====== */
    .fi-ta-table {
        background: transparent !important;
    }

    .fi-ta-header-cell {
        background: rgba(240, 253, 250, 0.5) !important;
        backdrop-filter: blur(8px);
        border-bottom: 1px solid rgba(13, 148, 136, 0.15) !important;
        font-size: 0.8125rem !important;
        font-weight: 600;
        color: #0f766e !important;
    }

    .dark .fi-ta-header-cell {
        background: rgba(6, 78, 59, 0.4) !important;
        color: #5eead4 !important;
    }

    .fi-ta-row {
        transition: all 0.15s !important;
    }

    .fi-ta-row:hover {
        background: rgba(240, 253, 250, 0.5) !important;
        backdrop-filter: blur(10px);
    }

    /* ====== TOPBAR - glass ====== */
    .fi-topbar,
    .fi-topbar > div {
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border-bottom: 1px solid rgba(13, 148, 136, 0.1) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02) !important;
    }

    .dark .fi-topbar,
    .dark .fi-topbar > div {
        background: rgba(15, 23, 42, 0.7) !important;
        border-bottom: 1px solid rgba(94, 234, 212, 0.1) !important;
    }

    /* ====== SEARCH BAR in topbar ====== */
    .fi-global-search {
        background: rgba(240, 253, 250, 0.5) !important;
        border: 1px solid rgba(13, 148, 136, 0.15) !important;
        border-radius: 0.875rem !important;
        backdrop-filter: blur(8px);
    }

    /* ====== BADGES ======
       Estados ("Confirmada", "Pagado") a 13px y sin mayúsculas forzadas: a
       11px en mayúsculas costaba leerlos de un vistazo en el consultorio. */
    .fi-badge {
        border-radius: 999px !important;
        font-weight: 600 !important;
        padding: 0.25rem 0.75rem !important;
        font-size: 0.8125rem !important;
        text-transform: none;
        letter-spacing: 0;
    }

    /* ====== MODAL - glass with strong blur ====== */
    .fi-modal-window,
    [role="dialog"].fi-modal-window {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(30px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.6) !important;
        box-shadow:
            0 25px 50px -12px rgba(0, 0, 0, 0.25),
            0 0 0 1px rgba(13, 148, 136, 0.05) !important;
        border-radius: 1.5rem !important;
    }

    .fi-modal-close-overlay {
        background: rgba(15, 23, 42, 0.4) !important;
        backdrop-filter: blur(8px);
    }

    /* ====== NOTIFICATIONS ====== */
    .fi-no-notification {
        backdrop-filter: blur(20px) saturate(180%);
        background: rgba(255, 255, 255, 0.9) !important;
        border: 1px solid rgba(13, 148, 136, 0.15) !important;
        border-radius: 1rem !important;
        box-shadow: 0 12px 40px rgba(13, 148, 136, 0.15) !important;
    }

    /* ====== PAGE TRANSITIONS ====== */
    .fi-main > div {
        animation: fadeInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ====== SCROLLBAR ====== */
    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: rgba(13, 148, 136, 0.2);
        border-radius: 10px;
        transition: background 0.2s;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: rgba(13, 148, 136, 0.4);
    }

    /* ====== DASHBOARD BENTO GRID ====== */
    .fi-dashboard-page .fi-wi {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .fi-dashboard-page .fi-wi:hover {
        transform: translateY(-3px) scale(1.005);
    }

    /* ====== ICONS - Larger and more prominent ====== */
    .fi-page-header-actions .fi-btn svg {
        width: 1rem !important;
        height: 1rem !important;
    }

    /* Mobile polish */
    @media (max-width: 768px) {
        .fi-wi-stats-overview-stat-value {
            font-size: 2rem !important;
        }
        .fi-page-header-heading {
            font-size: 1.5rem !important;
        }
        .fi-section {
            border-radius: 1rem !important;
        }
    }

    /* Remove body background since .fi-main handles it */
    body {
        background: transparent !important;
    }

    /* === FIX DROPDOWN EN TABLAS (z-index + stacking context) ===
       Problema: el dropdown del ActionGroup se renderiza dentro de la
       fila de la tabla. Como cada fila tiene su propio stacking context
       implicito, el dropdown queda capado por filas siguientes (que se
       renderizan despues en DOM con z-index igual o mayor).

       Solucion: cuando un dropdown esta ABIERTO en una fila, esa fila
       entera sube de capa. Detectamos via aria-expanded="true" en el
       trigger boton, que Filament/Alpine agrega automaticamente.
    */

    /* Dropdown panel arriba de todo */
    .fi-dropdown-panel,
    .fi-dropdown-list,
    [x-ref="panel"] {
        z-index: 9999 !important;
    }

    /* Cuando una fila contiene un trigger expandido, la fila sube de capa
       para que su dropdown pueda flotar sobre las siguientes. */
    .fi-ta-row:has([aria-expanded="true"]),
    tr:has([aria-expanded="true"]) {
        position: relative !important;
        z-index: 9999 !important;
    }

    /* Celdas con dropdown abierto no deben recortar contenido */
    .fi-ta-actions-cell,
    .fi-ta-cell {
        overflow: visible !important;
    }

    /* El wrapper de la tabla mantiene scroll horizontal pero NO recorta vertical */
    .fi-ta-content {
        overflow-x: auto !important;
        overflow-y: visible !important;
    }

    /* Cuando una sección/card de Filament tiene un dropdown abierto, el
       contenedor parent NO debe recortar. Crítico cuando solo hay 1-2 filas
       y el dropdown se acerca a los límites de la card. */
    .fi-ta-ctn:has([aria-expanded="true"]),
    .fi-section-content-ctn:has([aria-expanded="true"]),
    .fi-section:has([aria-expanded="true"]) {
        overflow: visible !important;
    }

    /* Garantizar espacio mínimo abajo de la última fila para que el dropdown
       pueda abrirse hacia abajo sin recortarse. SIEMPRE (no solo cuando está
       abierto), porque Floating UI calcula el placement al abrir y necesita
       ver el espacio libre antes. */
    .fi-ta-content,
    .fi-ta-ctn {
        min-height: 480px !important;
    }

    /* Dropdown panel SIEMPRE flota arriba de cualquier fila/header */
    .fi-dropdown-panel {
        z-index: 99999 !important;
        position: absolute !important;
    }

    /* Modales por encima de todo (incluyendo dropdowns) */
    .fi-modal,
    .fi-modal-window {
        z-index: 99999 !important;
    }

    /* ====== STICKY COLUMNS EN TABLAS ANCHAS ======
       Cuando la tabla tiene scroll horizontal el doctor pierde de vista
       el nombre del paciente/prospecto y los botones de accion. Pegamos
       checkbox + nombre a la izquierda y acciones a la derecha para
       que siempre esten visibles. Solo desktop (>= 768px), en movil
       no aplica porque la tabla se renderiza como cards.
    */
    @media (min-width: 768px) {
        .fi-ta-content {
            overflow-x: auto;
        }

        /* Variables: ancho aproximado de la columna de checkbox y de la
           columna de acciones (Filament: w-1 + px-3 ~= 2.75rem para el
           checkbox; las acciones varian segun cuantos botones haya). */

        /* === IZQUIERDA: checkbox + primera columna de datos === */

        /* (A) Checkbox de seleccion → pegado a 0 */
        .fi-ta-table > thead > tr > th.fi-ta-selection-cell,
        .fi-ta-table > tbody > tr > td.fi-ta-selection-cell {
            position: sticky;
            left: 0;
            z-index: 5;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(8px);
        }
        .dark .fi-ta-table > thead > tr > th.fi-ta-selection-cell,
        .dark .fi-ta-table > tbody > tr > td.fi-ta-selection-cell {
            background: rgba(15, 23, 42, 0.97);
        }

        /* (B) Si hay checkbox: la SIGUIENTE celda (nombre) es sticky despues */
        .fi-ta-table > thead > tr > th.fi-ta-selection-cell + th,
        .fi-ta-table > tbody > tr > td.fi-ta-selection-cell + td {
            position: sticky;
            left: 2.75rem;
            z-index: 4;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(8px);
            box-shadow: 4px 0 6px -4px rgba(15, 23, 42, 0.08);
        }
        .dark .fi-ta-table > thead > tr > th.fi-ta-selection-cell + th,
        .dark .fi-ta-table > tbody > tr > td.fi-ta-selection-cell + td {
            background: rgba(15, 23, 42, 0.97);
            box-shadow: 4px 0 6px -4px rgba(0, 0, 0, 0.4);
        }

        /* (C) Si NO hay checkbox: la primera columna de datos es la del nombre */
        .fi-ta-table > thead > tr > th:first-child:not(.fi-ta-selection-cell),
        .fi-ta-table > tbody > tr > td:first-child:not(.fi-ta-selection-cell) {
            position: sticky;
            left: 0;
            z-index: 4;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(8px);
            box-shadow: 4px 0 6px -4px rgba(15, 23, 42, 0.08);
        }
        .dark .fi-ta-table > thead > tr > th:first-child:not(.fi-ta-selection-cell),
        .dark .fi-ta-table > tbody > tr > td:first-child:not(.fi-ta-selection-cell) {
            background: rgba(15, 23, 42, 0.97);
            box-shadow: 4px 0 6px -4px rgba(0, 0, 0, 0.4);
        }

        /* Headers con bg de header (mas oscuro que filas) */
        .fi-ta-table > thead > tr > th.fi-ta-selection-cell,
        .fi-ta-table > thead > tr > th.fi-ta-selection-cell + th,
        .fi-ta-table > thead > tr > th:first-child:not(.fi-ta-selection-cell) {
            background: rgba(240, 253, 250, 0.97) !important;
        }
        .dark .fi-ta-table > thead > tr > th.fi-ta-selection-cell,
        .dark .fi-ta-table > thead > tr > th.fi-ta-selection-cell + th,
        .dark .fi-ta-table > thead > tr > th:first-child:not(.fi-ta-selection-cell) {
            background: rgba(6, 78, 59, 0.7) !important;
        }

        /* Hover: matchear el highlight de la fila para no ver "huecos" */
        .fi-ta-row:hover > td.fi-ta-selection-cell,
        .fi-ta-row:hover > td.fi-ta-selection-cell + td,
        .fi-ta-row:hover > td:first-child:not(.fi-ta-selection-cell) {
            background: rgba(240, 253, 250, 0.97) !important;
        }
        .dark .fi-ta-row:hover > td.fi-ta-selection-cell,
        .dark .fi-ta-row:hover > td.fi-ta-selection-cell + td,
        .dark .fi-ta-row:hover > td:first-child:not(.fi-ta-selection-cell) {
            background: rgba(6, 78, 59, 0.85) !important;
        }

        /* === DERECHA: columna de acciones ===
           NO la hacemos sticky — los dropdowns de ActionGroup quedan
           atrapados en el stacking context de la celda sticky y se
           tapan con celdas de filas siguientes. La primera columna
           sticky ya da el contexto al scrollear (nombre del paciente
           siempre visible). El usuario puede usar la rueda/drag para
           llegar a la columna de acciones. */
    }

    /* ====== TABLET Y CELULAR: botones para el dedo (y con guantes) ======
       44px es lo mínimo para atinarle sin ver. Las acciones de cada fila
       van más separadas para no tocar "No asistió" queriendo "Llegó". */
    @media (max-width: 1024px) {
        .fi-btn, .fi-icon-btn, .fi-ac-btn-action, .fi-tabs-item { min-height: 44px; }
        .fi-icon-btn { min-width: 44px; }
        .fi-ta-actions { gap: 0.75rem !important; }
        .fi-ta-actions a.fi-link, .fi-ta-actions button.fi-link { min-height: 44px; display: inline-flex; align-items: center; }
        .fi-checkbox-input { width: 1.25rem; height: 1.25rem; }
    }

    /* ====== CELULAR: cada renglón de tabla es una tarjeta ======
       La tabla medía el doble de la pantalla: solo se veía la fecha y medio
       nombre, y el estado y los botones quedaban fuera. Aquí cada renglón se
       apila (fecha, paciente, estado, botones) y se ve completo. */
    @media (max-width: 639px) {
        .fi-ta-content { overflow-x: visible !important; }
        .fi-ta-table, .fi-ta-table tbody { display: block; width: 100%; }
        .fi-ta-table thead { display: none; }
        .fi-ta-table tr.fi-ta-row {
            display: flex; flex-wrap: wrap; align-items: center; gap: 2px 10px;
            padding: 12px 14px; border-bottom: 1px solid #e2e8f0;
        }
        .dark .fi-ta-table tr.fi-ta-row { border-color: #1f2937; }
        .fi-ta-table td.fi-ta-cell { display: block; width: auto !important; padding: 0 !important; }
        .fi-ta-table td.fi-ta-cell > * { padding: 0 !important; }
        .fi-ta-table td.fi-ta-cell .fi-ta-col-wrp, .fi-ta-table td.fi-ta-cell .w-full { width: auto !important; }
        .fi-ta-table td.fi-ta-cell .px-3 { padding-left: 0 !important; padding-right: 0 !important; }
        .fi-ta-table td.fi-ta-cell .py-4 { padding-top: 2px !important; padding-bottom: 2px !important; }
        .fi-ta-table td.fi-ta-selection-cell, .fi-ta-table td.fi-ta-cell.hidden { display: none !important; }
        .fi-ta-table td.fi-ta-cell:not(.fi-ta-selection-cell):not(.fi-ta-actions-cell) + td.fi-ta-cell:not(.fi-ta-actions-cell) { font-weight: 600; }
        /* El nombre del paciente (2a columna casi siempre) a todo lo ancho. */
        .fi-ta-table td.fi-ta-cell:not(.fi-ta-selection-cell):not(.fi-ta-actions-cell) + td.fi-ta-cell:not(.fi-ta-actions-cell) { flex-basis: 100%; order: -1; }
        .fi-ta-table td.fi-ta-actions-cell { flex-basis: 100%; position: static !important; box-shadow: none !important; background: transparent !important; }
        .fi-ta-table td.fi-ta-actions-cell .fi-ta-actions { justify-content: flex-start !important; flex-wrap: wrap; padding: 6px 0 0 !important; }
    }
</style>

<script>
    // Scroll horizontal en tablas Filament sin tener que bajar al
    // scrollbar de hasta abajo. 3 mecanismos para que no haya friccion:
    //   1. Shift + rueda del mouse → mueve la tabla horizontal.
    //   2. Click sostenido + arrastrar → drag-to-scroll (como mapas).
    //   3. La rueda del mouse normal sobre la tabla mueve horizontal
    //      cuando NO se puede scrollear vertical (table mas ancha que tall).
    (function () {
        var SELECTOR = '.fi-ta-content, .fi-ta-ctn';

        // (1) Shift+wheel
        document.addEventListener('wheel', function (e) {
            if (!e.shiftKey || e.deltaY === 0) return;
            var el = e.target.closest(SELECTOR);
            if (!el) return;
            if (el.scrollWidth <= el.clientWidth) return;
            el.scrollLeft += e.deltaY;
            e.preventDefault();
        }, { passive: false });

        // (2) Drag-to-scroll: el rep agarra la tabla con el mouse y la mueve.
        // Solo se activa si arrastra al menos 4px (asi clicks normales en
        // botones / enlaces / filas siguen funcionando bien).
        document.addEventListener('mousedown', function (e) {
            if (e.button !== 0) return;
            // No interferir con clicks en botones, links, inputs, etc.
            if (e.target.closest('button, a, input, textarea, select, label, .fi-btn, .fi-link, [role="button"]')) return;
            var el = e.target.closest(SELECTOR);
            if (!el) return;
            if (el.scrollWidth <= el.clientWidth) return;

            var startX = e.pageX;
            var startScroll = el.scrollLeft;
            var dragging = false;

            function onMove(ev) {
                var dx = ev.pageX - startX;
                if (!dragging && Math.abs(dx) < 4) return;
                dragging = true;
                el.scrollLeft = startScroll - dx;
                ev.preventDefault();
            }
            function onUp(ev) {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                if (dragging) {
                    el.style.cursor = '';
                    // Bloquear el siguiente click si efectivamente arrastramos
                    var stopClick = function (ce) { ce.stopPropagation(); ce.preventDefault(); };
                    document.addEventListener('click', stopClick, { capture: true, once: true });
                }
            }
            el.style.cursor = 'grab';
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    })();
</script>
