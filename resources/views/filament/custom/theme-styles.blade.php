<style>
    /* ===== DOCFACIL CUSTOM THEME ===== */
    /* Sidebar ALWAYS dark - both light and dark mode */

    aside.fi-sidebar,
    .fi-sidebar,
    .fi-sidebar-nav,
    aside.fi-sidebar > * {
        background: linear-gradient(180deg, #042f2e 0%, #064e3b 40%, #065f46 100%) !important;
        background-color: #042f2e !important;
    }

    .fi-sidebar .fi-sidebar-header {
        border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        padding: 1rem !important;
        background: transparent !important;
    }

    .fi-sidebar .fi-sidebar-header img {
        height: 2.5rem !important;
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

    /* Hover */
    .fi-sidebar .fi-sidebar-item:hover,
    .fi-sidebar li:hover > a,
    .fi-sidebar a.fi-sidebar-item-button:hover {
        background: rgba(255, 255, 255, 0.06) !important;
        border-radius: 0.5rem;
    }

    .fi-sidebar .fi-sidebar-item:hover .fi-sidebar-item-label,
    .fi-sidebar .fi-sidebar-item:hover .fi-sidebar-item-icon {
        color: #ffffff !important;
    }

    /* Active item */
    .fi-sidebar .fi-sidebar-item.fi-active,
    .fi-sidebar .fi-sidebar-item.fi-active > a,
    .fi-sidebar .fi-active .fi-sidebar-item-button {
        background: rgba(255, 255, 255, 0.1) !important;
        border-radius: 0.5rem;
        border-left: 3px solid #5eead4 !important;
    }

    .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-label,
    .fi-sidebar .fi-active .fi-sidebar-item-label {
        color: #ffffff !important;
        font-weight: 600 !important;
    }

    .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-icon,
    .fi-sidebar .fi-active .fi-sidebar-item-icon {
        color: #5eead4 !important;
    }

    /* Group labels */
    .fi-sidebar .fi-sidebar-group-label,
    .fi-sidebar span.fi-sidebar-group-label,
    .fi-sidebar .fi-sidebar-group > button > span {
        color: rgba(255, 255, 255, 0.35) !important;
        font-size: 0.65rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        font-weight: 700 !important;
    }

    .fi-sidebar .fi-sidebar-group-collapse-button,
    .fi-sidebar .fi-sidebar-group button svg {
        color: rgba(255, 255, 255, 0.4) !important;
    }

    /* Sidebar badge (notification counts etc) */
    .fi-sidebar .fi-badge {
        background: rgba(255, 255, 255, 0.15) !important;
        color: #ffffff !important;
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

    /* Badges */
    .fi-badge {
        border-radius: 9999px !important;
        font-weight: 600 !important;
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

    /* Widget cards */
    .fi-wi-chart {
        border-radius: 1rem !important;
    }
</style>
