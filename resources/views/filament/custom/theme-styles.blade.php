<style>
    /* ===== DOCFACIL CUSTOM THEME ===== */

    /* Sidebar gradient background */
    .fi-sidebar {
        background: linear-gradient(180deg, #0f766e 0%, #0d9488 30%, #14b8a6 100%) !important;
    }

    .fi-sidebar .fi-sidebar-header {
        border-bottom: 1px solid rgba(255,255,255,0.15) !important;
    }

    /* Sidebar nav items - white text */
    .fi-sidebar .fi-sidebar-nav-groups {
        --c-50: 240 249 255;
        --c-100: 224 242 254;
        --c-200: 186 230 253;
        --c-300: 125 211 252;
        --c-400: 56 189 248;
        --c-500: 14 165 233;
        --c-600: 2 132 199;
        --c-700: 3 105 161;
        --c-800: 7 89 133;
        --c-900: 12 74 110;
    }

    .fi-sidebar-item-label {
        color: rgba(255, 255, 255, 0.85) !important;
        font-weight: 500 !important;
    }

    .fi-sidebar-item-icon {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .fi-sidebar-item:hover .fi-sidebar-item-label,
    .fi-sidebar-item:hover .fi-sidebar-item-icon {
        color: #ffffff !important;
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
        color: #ffffff !important;
    }

    .fi-sidebar-item.fi-active {
        background: rgba(255, 255, 255, 0.15) !important;
        border-radius: 0.5rem;
    }

    .fi-sidebar-group-label {
        color: rgba(255, 255, 255, 0.5) !important;
        font-size: 0.65rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
    }

    /* Top bar - clean white with subtle shadow */
    .fi-topbar {
        background: #ffffff !important;
        border-bottom: none !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06) !important;
    }

    /* Main content background - subtle warm gray */
    .fi-main {
        background: #f8fafb !important;
    }

    /* Cards and sections - rounded with subtle shadows */
    .fi-section,
    .fi-ta-ctn {
        border-radius: 1rem !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important;
        overflow: hidden;
    }

    /* Stats widget cards */
    .fi-wi-stats-overview-stat {
        border-radius: 1rem !important;
        border: 1px solid #e5e7eb !important;
        transition: all 0.2s ease !important;
    }

    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important;
    }

    /* Buttons - more rounded and vibrant */
    .fi-btn {
        border-radius: 0.75rem !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
    }

    .fi-btn:hover {
        transform: translateY(-1px) !important;
    }

    /* Table rows - hover effect */
    .fi-ta-row:hover {
        background: #f0fdfa !important;
    }

    /* Badges - more rounded */
    .fi-badge {
        border-radius: 9999px !important;
        font-weight: 600 !important;
    }

    /* Form inputs - softer borders */
    .fi-input, .fi-select, textarea, select {
        border-radius: 0.75rem !important;
        border-color: #d1d5db !important;
        transition: all 0.15s ease !important;
    }

    .fi-input:focus, .fi-select:focus, textarea:focus, select:focus {
        border-color: #14b8a6 !important;
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1) !important;
    }

    /* Login page customization */
    .fi-simple-layout {
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 50%, #f0fdfa 100%) !important;
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

    /* Navigation collapse button */
    .fi-sidebar-close-btn,
    .fi-sidebar-open-btn {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    /* Modal styling */
    .fi-modal-content {
        border-radius: 1rem !important;
    }

    /* Notification styling */
    .fi-no-notification {
        border-radius: 1rem !important;
    }

    /* Widget cards */
    .fi-wi-chart {
        border-radius: 1rem !important;
    }

    /* Breadcrumbs */
    .fi-breadcrumbs {
        font-size: 0.8rem !important;
    }

    /* Smooth transitions for everything */
    * {
        transition-duration: 0.15s;
    }

    /* Dark mode adjustments */
    .dark .fi-sidebar {
        background: linear-gradient(180deg, #064e45 0%, #0d5e54 30%, #0f766e 100%) !important;
    }

    .dark .fi-main {
        background: #111827 !important;
    }

    .dark .fi-ta-row:hover {
        background: rgba(20, 184, 166, 0.05) !important;
    }
</style>
