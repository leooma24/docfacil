<x-filament-panels::page>
    <style>
        /* ===== CALENDAR HERO ===== */
        .cal-hero {
            position: relative;
            border-radius: 1.5rem;
            padding: 28px 32px;
            overflow: hidden;
            background: linear-gradient(135deg, #3b82f6 0%, #0891b2 40%, #7c3aed 100%);
            color: white;
            box-shadow: 0 20px 60px -15px rgba(59, 130, 246, 0.4), inset 0 1px 0 rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }
        .cal-hero::before {
            content: '';
            position: absolute; top: -80px; right: -60px;
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .cal-hero::after {
            content: '';
            position: absolute; bottom: -100px; left: -40px;
            width: 240px; height: 240px;
            background: radial-gradient(circle, rgba(139,92,246,0.3), transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .cal-hero-grain {
            position: absolute; inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.08) 1px, transparent 0);
            background-size: 20px 20px; pointer-events: none;
        }
        .cal-hero-content { position: relative; z-index: 1; }

        .cal-hero-top {
            display: flex; flex-direction: column; gap: 16px;
        }
        @media (min-width: 768px) {
            .cal-hero-top {
                flex-direction: row; align-items: center; justify-content: space-between;
            }
        }

        .cal-hero-title-block { display: flex; align-items: center; gap: 16px; }
        .cal-hero-icon {
            width: 64px; height: 64px; border-radius: 18px;
            background: rgba(255,255,255,0.18); backdrop-filter: blur(12px);
            border: 1.5px solid rgba(255,255,255,0.3);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .cal-hero-icon svg { width: 32px; height: 32px; color: white; }
        .cal-hero-label {
            font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.12em;
            opacity: 0.8; font-weight: 700;
        }
        .cal-hero-title {
            font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em;
            line-height: 1.15; margin-top: 2px;
            color: white !important;
            -webkit-text-fill-color: white !important;
            background: none !important;
        }
        .cal-hero-subtitle { font-size: 0.85rem; opacity: 0.85; margin-top: 2px; }

        /* ===== STATS GRID ===== */
        .cal-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 22px;
        }
        @media (min-width: 640px) { .cal-stats { grid-template-columns: repeat(4, 1fr); } }

        .cal-stat {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 14px;
            padding: 16px 18px;
            transition: all 0.2s;
        }
        .cal-stat:hover {
            background: rgba(255,255,255,0.22);
            transform: translateY(-2px);
        }
        .cal-stat-label {
            font-size: 0.63rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            opacity: 0.78; display: flex; align-items: center; gap: 4px;
        }
        .cal-stat-value {
            font-size: 2rem; font-weight: 800; letter-spacing: -0.02em;
            margin-top: 4px; line-height: 1; color: white;
        }

        /* ===== CALENDAR WIDGET CONTAINER ===== */
        .cal-container {
            background: white;
            border-radius: 1.25rem;
            padding: 20px;
            border: 1px solid rgba(229, 231, 235, 0.8);
            box-shadow: 0 4px 16px rgba(0,0,0,0.04), 0 1px 2px rgba(59,130,246,0.05);
            position: relative;
            overflow: hidden;
        }
        .dark .cal-container {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(94, 234, 212, 0.15);
        }
        .cal-container::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #3b82f6, #0891b2, #7c3aed);
        }

        /* ===== FULLCALENDAR OVERRIDES ===== */
        .cal-container .fc {
            font-family: 'Inter', sans-serif !important;
        }
        .cal-container .fc-toolbar-title {
            font-size: 1.35rem !important;
            font-weight: 800 !important;
            letter-spacing: -0.02em !important;
            color: #0f172a !important;
            text-transform: capitalize !important;
        }
        .dark .cal-container .fc-toolbar-title { color: #f0fdfa !important; }

        /* Toolbar layout */
        .cal-container .fc-header-toolbar {
            flex-wrap: wrap !important;
            gap: 12px !important;
            margin-bottom: 18px !important;
            padding: 4px !important;
        }
        .cal-container .fc-toolbar-chunk {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        /* All buttons base */
        .cal-container .fc-button,
        .cal-container .fc-button-primary,
        .cal-container button.fc-button {
            background: linear-gradient(135deg, #3b82f6, #0891b2) !important;
            background-color: #3b82f6 !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 9px 16px !important;
            font-weight: 700 !important;
            font-size: 0.78rem !important;
            text-transform: capitalize !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25) !important;
            transition: all 0.2s !important;
            color: white !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1) !important;
            margin: 0 4px !important;
        }
        .cal-container .fc-button:focus,
        .cal-container .fc-button:active {
            outline: none !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4), 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }
        .cal-container .fc-button:hover:not(:disabled) {
            transform: translateY(-1px);
            color: white !important;
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35) !important;
        }

        /* Active view button (e.g. Semana selected) */
        .cal-container .fc-button-active,
        .cal-container .fc-button-primary.fc-button-active,
        .cal-container button.fc-button-active {
            background: linear-gradient(135deg, #0d9488, #0891b2) !important;
            background-color: #0d9488 !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.4), inset 0 1px 0 rgba(255,255,255,0.2) !important;
        }

        /* Today button (orange) */
        .cal-container .fc-today-button {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            background-color: #f59e0b !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35) !important;
        }
        .cal-container .fc-today-button:disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
            color: white !important;
        }

        /* Prev/next arrows */
        .cal-container .fc-prev-button,
        .cal-container .fc-next-button {
            padding: 9px 12px !important;
            min-width: 38px !important;
        }
        .cal-container .fc-icon {
            color: white !important;
        }

        /* Button groups (rounded pill) */
        .cal-container .fc-button-group {
            display: inline-flex !important;
            gap: 6px !important;
            background: transparent !important;
        }
        .cal-container .fc-button-group .fc-button {
            border-radius: 10px !important;
            margin: 0 !important;
        }

        /* Fix text visibility in all states */
        .cal-container .fc-button span,
        .cal-container .fc-button .fc-icon,
        .cal-container button.fc-button * {
            color: white !important;
        }

        .cal-container .fc-col-header-cell {
            background: linear-gradient(180deg, rgba(59,130,246,0.05), rgba(59,130,246,0.02)) !important;
            border-color: rgba(59, 130, 246, 0.1) !important;
            padding: 10px 0 !important;
        }
        .cal-container .fc-col-header-cell-cushion {
            font-size: 0.72rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.08em !important;
            color: #0f766e !important;
            padding: 4px 0 !important;
        }

        .cal-container .fc-timegrid-slot-label-cushion,
        .cal-container .fc-timegrid-slot-label {
            font-size: 0.7rem !important;
            font-weight: 600 !important;
            color: #94a3b8 !important;
        }

        .cal-container .fc-day-today {
            background: rgba(59, 130, 246, 0.04) !important;
        }
        .cal-container .fc-day-today .fc-col-header-cell-cushion {
            color: #3b82f6 !important;
        }

        .cal-container .fc-event {
            border-radius: 10px !important;
            border: none !important;
            padding: 4px 8px !important;
            font-weight: 600 !important;
            font-size: 0.72rem !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
            overflow: hidden !important;
        }
        .cal-container .fc-event:hover {
            transform: translateY(-1px) scale(1.02);
            box-shadow: 0 4px 14px rgba(0,0,0,0.15) !important;
            z-index: 5 !important;
        }
        .cal-container .fc-event-main {
            padding: 2px 0 !important;
        }

        .cal-container .fc-timegrid-now-indicator-line {
            border-color: #ef4444 !important;
            border-width: 2px !important;
        }
        .cal-container .fc-timegrid-now-indicator-arrow {
            border-color: #ef4444 !important;
        }

        .cal-container .fc-scrollgrid {
            border-radius: 12px !important;
            overflow: hidden !important;
            border-color: rgba(229, 231, 235, 0.8) !important;
        }

        .cal-container .fc-timegrid-slot,
        .cal-container .fc-timegrid-cols,
        .cal-container .fc-daygrid-day-frame {
            border-color: rgba(229, 231, 235, 0.6) !important;
        }

        /* Legend below calendar */
        .cal-legend {
            display: flex; flex-wrap: wrap; gap: 10px;
            margin-top: 16px; padding: 12px 16px;
            background: rgba(240, 253, 250, 0.5);
            border: 1px solid rgba(13, 148, 136, 0.15);
            border-radius: 12px;
        }
        .cal-legend-item {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.72rem; font-weight: 600; color: #475569;
        }
        .cal-legend-dot {
            width: 12px; height: 12px; border-radius: 4px; flex-shrink: 0;
        }
    </style>

    {{-- HERO --}}
    <div class="cal-hero">
        <div class="cal-hero-grain"></div>
        <div class="cal-hero-content">
            <div class="cal-hero-top">
                <div class="cal-hero-title-block">
                    <div class="cal-hero-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="cal-hero-label">📅 Tu agenda</div>
                        <h2 class="cal-hero-title">Calendario de Citas</h2>
                        <div class="cal-hero-subtitle">Arrastra para mover citas · Click para ver detalles · Redimensiona para ajustar duración</div>
                    </div>
                </div>
            </div>

            {{-- STATS --}}
            <div class="cal-stats">
                <div class="cal-stat">
                    <div class="cal-stat-label">📌 Hoy</div>
                    <div class="cal-stat-value">{{ $this->stats['today'] }}</div>
                </div>
                <div class="cal-stat">
                    <div class="cal-stat-label">📊 Esta semana</div>
                    <div class="cal-stat-value">{{ $this->stats['week'] }}</div>
                </div>
                <div class="cal-stat">
                    <div class="cal-stat-label">⚠️ Sin confirmar</div>
                    <div class="cal-stat-value">{{ $this->stats['pending_confirmation'] }}</div>
                </div>
                <div class="cal-stat">
                    <div class="cal-stat-label">✅ Atendidas</div>
                    <div class="cal-stat-value">{{ $this->stats['completed_this_week'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- CALENDAR CONTAINER --}}
    <div class="cal-container">
        @livewire(\App\Filament\Doctor\Widgets\CalendarWidget::class)

        {{-- Legend --}}
        <div class="cal-legend">
            <div class="cal-legend-item">
                <div class="cal-legend-dot" style="background:#fef3c7;border:2px solid #f59e0b;"></div>
                Programada
            </div>
            <div class="cal-legend-item">
                <div class="cal-legend-dot" style="background:#dbeafe;border:2px solid #3b82f6;"></div>
                Confirmada
            </div>
            <div class="cal-legend-item">
                <div class="cal-legend-dot" style="background:#ede9fe;border:2px solid #8b5cf6;"></div>
                En consulta
            </div>
            <div class="cal-legend-item">
                <div class="cal-legend-dot" style="background:#d1fae5;border:2px solid #10b981;"></div>
                Completada
            </div>
            <div class="cal-legend-item">
                <div class="cal-legend-dot" style="background:#fde2e2;border:2px solid #f87171;"></div>
                Cancelada
            </div>
            <div class="cal-legend-item">
                <div class="cal-legend-dot" style="background:#e5e7eb;border:2px solid #9ca3af;"></div>
                No asistió
            </div>
        </div>
    </div>

    {{--
        Después de arrastrar/redimensionar una cita, forzamos al FullCalendar
        a refetch sus eventos desde la BD. Si no hacemos esto, el visual se
        queda "regresando" o desincronizado de la BD.

        El widget despacha 'docfacil-calendar-refresh' después de guardar.
        Aquí escuchamos ese evento y llamamos refetchEvents() en la instancia
        de FullCalendar que vive dentro de .filament-fullcalendar.
    --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('docfacil-calendar-refresh', () => {
                // El package usa un evento window-level para refresh
                window.dispatchEvent(new CustomEvent('filament-fullcalendar--refresh'));
            });
        });
    </script>
</x-filament-panels::page>
