<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        background: var(--orb-bg) !important;
        font-family: 'Outfit', sans-serif !important;
    }

    .report-page, .att-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    .report-container, .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Premium Purple Gradient Hero Header */
    .report-header-premium {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #6B11F4) 100%) !important;
        border-radius: 26px !important;
        padding: 28px 34px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 14px 35px rgba(75, 0, 232, 0.18) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
        border: none !important;
    }

    .report-header-premium::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 320px !important;
        height: 320px !important;
        background: rgba(255, 255, 255, 0.09) !important;
        border-radius: 50% !important;
        filter: blur(40px) !important;
        pointer-events: none !important;
    }

    .report-header-premium .title-area h3 {
        font-size: 26px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .report-header-premium .title-area p {
        font-size: 13.5px !important;
        color: rgba(255, 255, 255, 0.88) !important;
        margin: 6px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .report-header-premium .header-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.15em !important;
        color: rgba(255, 255, 255, 0.8) !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    /* View Switcher Toggle Pill */
    .view-switcher-pill {
        display: inline-flex;
        background: rgba(255, 255, 255, 0.18);
        padding: 4px;
        border-radius: 50px;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .view-switcher-btn {
        border: none;
        background: transparent;
        color: rgba(255, 255, 255, 0.9);
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 12.5px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .view-switcher-btn:hover {
        color: #FFFFFF;
        background: rgba(255, 255, 255, 0.12);
    }

    .view-switcher-btn.active {
        background: #FFFFFF !important;
        color: var(--orb-primary, #4B00E8) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12) !important;
    }

    .report-btn-pill {
        height: 40px;
        padding: 0 18px;
        border-radius: 50px;
        font-size: 12.5px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.15);
        color: #FFFFFF !important;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .report-btn-pill:hover {
        background: #FFFFFF;
        color: var(--orb-primary, #4B00E8) !important;
        transform: translateY(-2px);
    }

    /* Summary KPI Cards matching Violations design */
    .report-kpi-grid, .audit-kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }

    .att-kpi {
        min-height: 86px;
        padding: 12px 16px;
        border-radius: 18px;
        border: 1px solid var(--orb-border);
        background: #fff;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .045);
        position: relative;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .att-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(16, 24, 40, .08);
    }

    .att-kpi:after {
        content: "";
        position: absolute;
        right: -32px;
        top: -34px;
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: var(--tone-soft);
    }

    .att-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        position: relative;
        z-index: 1;
    }

    .att-kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tone-soft);
        color: var(--tone);
        font-size: 15px;
    }

    .att-kpi-value {
        font-size: 26px;
        line-height: 1;
        font-weight: 950;
        color: var(--orb-text);
    }

    .att-kpi-label {
        margin-top: 10px;
        font-size: 10.5px;
        color: var(--orb-muted);
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .035em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        position: relative;
        z-index: 1;
    }

    .att-kpi-line {
        position: absolute;
        left: 14px;
        right: 14px;
        bottom: 6px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--tone), transparent);
    }

    .tone-success { --tone: #12B76A; --tone-soft: rgba(18, 183, 106, .12); }
    .tone-danger  { --tone: #F04438; --tone-soft: rgba(240, 68, 56, .12); }
    .tone-warning { --tone: #F79009; --tone-soft: rgba(247, 144, 9, .14); }
    .tone-orange  { --tone: #EA580C; --tone-soft: rgba(234, 88, 12, .13); }
    .tone-amber   { --tone: #D97706; --tone-soft: rgba(217, 119, 6, .13); }
    .tone-blocked { --tone: #B42318; --tone-soft: rgba(180, 35, 24, .13); }
    .tone-purple  { --tone: #7000FF; --tone-soft: rgba(112, 0, 255, .12); }
    .tone-blue    { --tone: #175CD3; --tone-soft: rgba(23, 92, 211, .12); }
    .tone-emerald { --tone: #027A48; --tone-soft: rgba(2, 122, 72, .12); }
    .tone-indigo  { --tone: #4B00E8; --tone-soft: rgba(75, 0, 232, .12); }


    /* Main Container Card */
    .att-card, .orb-table-card {
        background: #FFFFFF !important;
        border-radius: 24px !important;
        border: 1px solid var(--orb-border) !important;
        box-shadow: var(--orb-shadow) !important;
        overflow: hidden !important;
        margin-bottom: 30px !important;
    }

    /* Section Head inside Card */
    .att-section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px 16px 24px;
        border-bottom: 1px solid var(--orb-border);
        background: #FFFFFF;
    }

    .att-section-title {
        font-size: 17px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-section-title i {
        color: var(--orb-primary, #4B00E8);
    }

    /* Filter Panel below Section Head */
    .att-filter-panel {
        padding: 18px 24px;
        background: #FAFBFC;
        border-bottom: 1px solid var(--orb-border);
    }

    .att-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 14px;
        align-items: flex-end;
    }

    .att-filter-grid label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--orb-muted);
        margin-bottom: 6px;
        display: block;
    }

    .att-filter-grid .form-control {
        height: 42px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #FFFFFF;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
        width: 100%;
        transition: all 0.2s ease;
    }

    .att-filter-grid .form-control:focus {
        border-color: var(--orb-primary, #4B00E8);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
    }

    .att-filter-panel .select2-container .select2-selection--single {
        height: 42px !important;
        border-radius: 12px !important;
        border: 1px solid var(--orb-border) !important;
        padding: 6px 12px !important;
        display: flex !important;
        align-items: center !important;
        background: #FFFFFF !important;
    }

    .att-filter-panel .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 8px !important;
    }

    .att-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-search-btn {
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #6B11F4) 100%);
        color: #FFFFFF;
        border: none;
        padding: 0 20px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2);
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .att-search-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.3);
        color: #FFFFFF;
    }

    .att-reset-btn {
        height: 42px;
        width: 42px;
        border-radius: 12px;
        background: #FFFFFF;
        border: 1px solid var(--orb-border);
        color: var(--orb-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
        text-decoration: none !important;
    }

    .att-reset-btn:hover {
        background: #F8FAFC;
        color: var(--orb-primary, #4B00E8);
        border-color: #D9CCFF;
    }

    /* Table Component */
    .att-table-wrap, .orb-table-scroll {
        width: 100%;
        overflow-x: auto;
    }

    .att-table, .report-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .att-table thead th, .report-table thead th {
        background: #FAFBFC;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #475467;
        padding: 14px 18px;
        border-bottom: 1px solid var(--orb-border);
        border-top: none;
        white-space: nowrap;
        vertical-align: middle;
    }

    .att-table tbody td, .report-table tbody td {
        padding: 14px 18px;
        font-size: 13px;
        vertical-align: middle;
        border-bottom: 1px solid var(--orb-border);
        color: var(--orb-text);
        background: #FFFFFF;
        transition: background 0.15s ease;
    }

    .att-table tbody tr:hover td, .report-table tbody tr:hover td {
        background: #F9FAFB !important;
    }

    /* Table Employee Cell */
    .table-emp-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .table-emp-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #F4F2FF;
        color: var(--orb-primary, #4B00E8);
        font-weight: 800;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, 0.12);
    }

    .table-emp-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .table-emp-name {
        font-weight: 800;
        font-size: 13.5px;
        color: #101828;
    }

    .table-emp-meta {
        font-size: 11.5px;
        color: var(--orb-muted);
        font-weight: 600;
        margin-top: 1px;
    }

    /* Badges */
    .badge-premium-pill {
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        letter-spacing: 0.03em;
    }

    .badge-wfo { background: #ECFDF3; color: #027A48; border: 1px solid #A6F4C5; }
    .badge-wfh { background: #EFF8FF; color: #175CD3; border: 1px solid #B2DDFF; }

    .badge-gross-pill {
        background: #FEF7C3;
        color: #B54708;
        border: 1px solid #FEE4E2;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .work-summary-full-text p {
        margin-bottom: 4px;
    }
    .work-summary-full-text p:last-child {
        margin-bottom: 0;
    }

    .structured-task-item {
        margin-bottom: 3px;
    }

    /* Action Buttons */
    .action-btn-group {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    .btn-action-primary {
        height: 34px;
        padding: 0 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        background: #F4F2FF;
        color: var(--orb-primary, #4B00E8);
        border: 1px solid rgba(75, 0, 232, 0.15);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        text-decoration: none !important;
        cursor: pointer;
    }

    .btn-action-primary:hover {
        background: var(--orb-primary, #4B00E8);
        color: #FFFFFF !important;
        box-shadow: 0 4px 10px rgba(75, 0, 232, 0.25);
    }

    .btn-action-secondary {
        height: 34px;
        width: 34px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        background: #FFFFFF;
        color: #475467;
        border: 1px solid #E7EAF3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .btn-action-secondary:hover {
        background: #F8FAFC;
        color: var(--orb-primary, #4B00E8);
        border-color: #D9CCFF;
    }

    /* Cards Grid View */
    .emp-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 22px;
        margin-bottom: 30px;
    }

    .emp-summary-card {
        background: #FFFFFF;
        border: 1px solid #E7EAF3;
        border-radius: 22px;
        padding: 22px;
        box-shadow: 0 10px 25px rgba(16, 24, 40, .03);
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }

    .emp-summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 35px rgba(75, 0, 232, 0.09);
        border-color: rgba(75, 0, 232, 0.22);
    }

    .emp-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .emp-card-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #F4F2FF;
        color: var(--orb-primary, #4B00E8);
        font-weight: 800;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, 0.1);
    }

    .emp-card-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .emp-card-name {
        font-weight: 800;
        font-size: 16px;
        color: #101828;
        margin: 0 0 2px 0;
    }

    .emp-card-meta {
        font-size: 12px;
        color: #667085;
        font-weight: 600;
    }

    .emp-stats-bar {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        background: #F8FAFC;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid #F1F5F9;
        margin-bottom: 16px;
        text-align: center;
    }

    .emp-stat-item .stat-val {
        font-size: 15px;
        font-weight: 900;
        color: var(--orb-primary, #4B00E8);
        line-height: 1.2;
    }

    .emp-stat-item .stat-lbl {
        font-size: 10px;
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 2px;
    }

    .emp-latest-snippet {
        background: #FAFAFA;
        border: 1px solid #F1F5F9;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 18px;
        font-size: 12.5px;
        color: #334155;
    }

    .emp-latest-snippet .snippet-title {
        font-weight: 800;
        color: #0F172A;
        font-size: 11.5px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Slide-Out Timeline Drawer */
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1050;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .drawer-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .timeline-drawer {
        position: fixed;
        top: 0;
        right: 0;
        width: 600px;
        max-width: 90vw;
        height: 100vh;
        background: #FFFFFF;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
        z-index: 1060;
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }

    .timeline-drawer.active {
        transform: translateX(0);
    }

    .drawer-header {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #6B11F4) 100%);
        color: #FFFFFF;
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .drawer-header .close-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: #FFFFFF;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        transition: background 0.2s ease;
    }

    .drawer-header .close-btn:hover {
        background: rgba(255, 255, 255, 0.35);
    }

    .drawer-body {
        padding: 28px;
        overflow-y: auto;
        flex: 1;
        background: #F8FAFC;
    }

    .timeline-list {
        position: relative;
        padding-left: 24px;
    }

    .timeline-list::before {
        content: '';
        position: absolute;
        top: 10px;
        left: 7px;
        bottom: 10px;
        width: 2px;
        background: #E2E8F0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 24px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        top: 14px;
        left: -21px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--orb-primary, #4B00E8);
        border: 3px solid #FFFFFF;
        box-shadow: 0 0 0 2px var(--orb-primary, #4B00E8);
    }

    .timeline-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }

    @media (max-width: 1200px) {
        .report-kpi-grid, .audit-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .report-header-premium {
            flex-direction: column;
            align-items: flex-start;
            padding: 24px 20px !important;
        }
        .report-kpi-grid, .audit-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .att-filter-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width: 680px) {
        .report-kpi-grid, .audit-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 8px !important;
        }
        .att-kpi {
            min-height: 74px;
            padding: 10px 12px;
            border-radius: 14px;
        }
        .att-kpi-value {
            font-size: 18px !important;
        }
        .att-kpi-icon {
            width: 30px;
            height: 30px;
            font-size: 13px;
        }
        .att-kpi-label {
            font-size: 9.5px;
            margin-top: 6px;
        }
    }
</style>
