<style>
    /* ===== DOCFACIL CUSTOM THEME ===== */

    /* Sidebar - dark teal gradient for maximum contrast */
    .fi-sidebar {
        background: linear-gradient(180deg, #042f2e 0%, #064e3b 40%, #065f46 100%) !important;
    }

    .fi-sidebar .fi-sidebar-header {
        border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        padding: 1rem !important;
    }

    /* Logo in sidebar bigger */
    .fi-sidebar .fi-sidebar-header img {
        height: 2.5rem !important;
    }

    /* Sidebar collapse button */
    .fi-sidebar-close-btn,
    .fi-sidebar-open-btn {
        color: rgba(255, 255, 255, 0.6) !important;
    }

    .fi-sidebar-close-btn:hover,
    .fi-sidebar-open-btn:hover {
        color: #ffffff !important;
    }

    /* Sidebar nav items */
    .fi-sidebar-item-label {
        color: rgba(255, 255, 255, 0.8) !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
    }

    .fi-sidebar-item-icon {
        color: rgba(255, 255, 255, 0.5) !important;
    }

    .fi-sidebar-item:hover {
        background: rgba(255, 255, 255, 0.06) !important;
        border-radius: 0.5rem;
    }

    .fi-sidebar-item:hover .fi-sidebar-item-label,
    .fi-sidebar-item:hover .fi-sidebar-item-icon {
        color: #ffffff !important;
    }

    /* Active item - subtle, blends with sidebar */
    .fi-sidebar-item.fi-active {
        background: rgba(255, 255, 255, 0.1) !important;
        border-radius: 0.5rem;
        border-left: 3px solid #5eead4 !important;
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-label {
        color: #ffffff !important;
        font-weight: 600 !important;
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
        color: #5eead4 !important;
    }

    /* Group labels */
    .fi-sidebar-group-label {
        color: rgba(255, 255, 255, 0.35) !important;
        font-size: 0.65rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        font-weight: 700 !important;
    }

    /* Group collapse button */
    .fi-sidebar-group-collapse-button {
        color: rgba(255, 255, 255, 0.4) !important;
    }

    /* Top bar - clean */
    .fi-topbar {
        background: #ffffff !important;
        border-bottom: none !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
    }

    /* Main content area */
    .fi-main {
        background: #f8fafb !important;
    }

    /* Cards - modern rounded */
    .fi-section,
    .fi-ta-ctn {
        border-radius: 1rem !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
        overflow: hidden;
    }

    /* Stats cards - interactive */
    .fi-wi-stats-overview-stat {
        border-radius: 1rem !important;
        border: 1px solid #e5e7eb !important;
        transition: all 0.2s ease !important;
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

    /* Login page */
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

    /* Modal */
    .fi-modal-content {
        border-radius: 1rem !important;
    }

    /* Widget cards */
    .fi-wi-chart {
        border-radius: 1rem !important;
    }

    /* Dark mode */
    .dark .fi-sidebar {
        background: linear-gradient(180deg, #022c22 0%, #042f2e 40%, #064e3b 100%) !important;
    }

    .dark .fi-main {
        background: #111827 !important;
    }

    .dark .fi-ta-row:hover {
        background: rgba(20, 184, 166, 0.05) !important;
    }
</style>
