@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => 'attendances'])

@section('_content')
<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        background: var(--orb-bg) !important;
        overflow-x: hidden !important;
    }

    .att-page {
        width: 100%;
        max-width: 100%;
        min-height: calc(100vh - 80px);
        padding: 24px;
        background: var(--orb-bg);
        overflow-x: hidden;
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* HERO BANNER */
    .orb-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 26px 28px;
        color: #fff;
        box-shadow: 0 20px 45px rgba(0, 0, 0, .12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin: 0 0 24px;
    }

    .orb-hero::after {
        content: '';
        position: absolute;
        width: 230px;
        height: 230px;
        border-radius: 50%;
        right: -95px;
        bottom: -115px;
        background: rgba(255, 255, 255, .10);
    }

    .orb-hero-content, .orb-hero-actions {
        position: relative;
        z-index: 2;
    }

    .orb-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .15);
        color: rgba(255, 255, 255, .94);
        font-size: 11px;
        font-weight: 900;
        margin-bottom: 10px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .orb-hero h1 {
        font-size: 28px;
        font-weight: 950;
        margin: 0;
        letter-spacing: -.03em;
        color: #fff;
    }

    .orb-hero p {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .84);
        font-size: 14px;
    }

    .att-btn-glass,
    .orb-hero-actions .btn-glass,
    .orb-hero-actions .att-btn {
        background: rgba(255, 255, 255, 0.18) !important;
        border: 1px solid rgba(255, 255, 255, 0.45) !important;
        color: #ffffff !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border-radius: 999px !important;
        padding: 10px 22px !important;
        font-size: 13.5px !important;
        font-weight: 750 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: all 0.25s ease !important;
    }

    .att-btn-glass:hover,
    .orb-hero-actions .btn-glass:hover,
    .orb-hero-actions .att-btn:hover {
        background: rgba(255, 255, 255, 0.32) !important;
        border-color: rgba(255, 255, 255, 0.75) !important;
        color: #ffffff !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
    }

    /* STAT CARDS */
    .stat-card-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--orb-shadow);
    }

    .stat-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .stat-val {
        font-size: 24px;
        font-weight: 900;
        color: var(--orb-text);
        line-height: 1.2;
    }

    .stat-lbl {
        font-size: 12px;
        font-weight: 700;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    /* FILTER BAR */
    .filter-bar {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 14px 18px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .filter-pills {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-pill {
        padding: 8px 16px;
        border-radius: 999px;
        border: 1px solid var(--orb-border);
        background: #F9FAFB;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s ease;
        text-decoration: none !important;
    }

    .filter-pill.active, .filter-pill:hover {
        background: var(--orb-primary);
        color: #fff;
        border-color: var(--orb-primary);
    }

    .search-box {
        position: relative;
        min-width: 260px;
    }

    .search-box input {
        width: 100%;
        padding: 9px 16px 9px 38px;
        border-radius: 999px;
        border: 1px solid var(--orb-border);
        font-size: 13px;
        outline: none;
        transition: border-color .2s ease;
    }

    .search-box input:focus {
        border-color: var(--orb-primary);
    }

    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--orb-muted);
        font-size: 14px;
    }

    /* POLICY CARDS GRID */
    .policy-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 20px;
    }

    .policy-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: all .25s ease;
    }

    .policy-card:hover {
        box-shadow: var(--orb-shadow);
        border-color: rgba(75, 0, 232, .25);
        transform: translateY(-3px);
    }

    .policy-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .policy-title {
        font-size: 18px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0 0 4px;
    }

    .policy-desc {
        font-size: 13px;
        color: var(--orb-muted);
        margin: 0;
        line-height: 1.4;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .status-active {
        background: #ECFDF5;
        color: #10B981;
        border: 1px solid #A7F3D0;
    }

    .status-inactive {
        background: #FEF2F2;
        color: #EF4444;
        border: 1px solid #FECACA;
    }

    /* METRIC TILES INSIDE CARD */
    .metric-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        background: var(--orb-soft);
        padding: 14px;
        border-radius: 14px;
        margin-bottom: 16px;
    }

    .metric-item {
        display: flex;
        flex-direction: column;
    }

    .metric-val {
        font-size: 16px;
        font-weight: 900;
        color: var(--orb-primary);
    }

    .metric-lbl {
        font-size: 11px;
        font-weight: 700;
        color: var(--orb-muted);
        text-transform: uppercase;
    }

    /* BADGE PILLS */
    .badge-section {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 16px;
    }

    .feature-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        background: #F3F4F6;
        color: #374151;
    }

    .feature-pill.pill-primary {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .feature-pill.pill-success {
        background: #ECFDF5;
        color: #059669;
    }

    .feature-pill.pill-warning {
        background: #FFFBEB;
        color: #D97706;
    }

    .policy-footer {
        padding-top: 14px;
        border-top: 1px dashed var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .btn-orb-edit {
        background: var(--orb-soft);
        color: var(--orb-primary);
        font-weight: 800;
        font-size: 12px;
        border-radius: 10px;
        padding: 8px 16px;
        border: none;
        transition: all .2s ease;
    }

    .btn-orb-edit:hover {
        background: var(--orb-primary);
        color: #fff;
    }

    /* ========== HRMS PREMIUM MODAL STYLING ========== */
    .att-modal-content {
        border-radius: 24px !important;
        border: none !important;
        overflow: hidden !important;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25) !important;
        background: #fff !important;
    }

    .att-modal-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        padding: 22px 28px !important;
        color: #fff !important;
        border-bottom: none !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .att-modal-title {
        font-size: 18px !important;
        font-weight: 900 !important;
        color: #fff !important;
        margin: 0 !important;
        letter-spacing: -.02em;
    }

    .att-modal-subtitle {
        font-size: 12.5px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin-top: 3px !important;
        font-weight: 600 !important;
    }

    .att-modal-header .close {
        color: #fff !important;
        opacity: 0.85 !important;
        text-shadow: none !important;
        font-size: 24px !important;
        font-weight: 300 !important;
        outline: none !important;
        transition: all .2s ease;
        padding: 0 !important;
        margin: 0 !important;
    }

    .att-modal-header .close:hover {
        opacity: 1 !important;
        transform: scale(1.1);
    }

    .att-modal-body {
        padding: 24px 28px !important;
        background: #fff !important;
    }

    .att-modal-section {
        border: 1px solid #EEF2F6;
        background: #FCFCFD;
        border-radius: 18px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .att-modal-section-title {
        font-size: 12.5px;
        font-weight: 900;
        color: var(--orb-text);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .att-modal-section-title i {
        color: var(--orb-primary);
        font-size: 14px;
    }

    .att-modal-body label {
        font-size: 11px !important;
        font-weight: 900 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: .04em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .att-modal-body .form-control {
        height: 42px !important;
        border-radius: 12px !important;
        border: 1.5px solid #E2E8F0 !important;
        font-size: 13.5px !important;
        font-weight: 700 !important;
        color: #1E293B !important;
        background-color: #fff !important;
        transition: all .2s ease !important;
    }

    .att-modal-body .form-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1) !important;
    }

    .att-modal-footer {
        padding: 16px 28px !important;
        background: #F8FAFC !important;
        border-top: 1px solid #EEF2F6 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 12px !important;
    }

    .btn-modal-cancel {
        background: #E2E8F0 !important;
        color: #475569 !important;
        font-weight: 800 !important;
        border-radius: 999px !important;
        padding: 10px 24px !important;
        border: none !important;
        font-size: 13px !important;
        transition: all .2s ease !important;
    }

    .btn-modal-cancel:hover {
        background: #CBD5E1 !important;
        color: #1E293B !important;
    }

    .btn-modal-save {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
        font-weight: 800 !important;
        border-radius: 999px !important;
        padding: 10px 28px !important;
        border: none !important;
        font-size: 13px !important;
        box-shadow: 0 4px 14px rgba(75, 0, 232, .25) !important;
        transition: all .2s ease !important;
    }

    .btn-modal-save:hover {
        opacity: .92 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 18px rgba(75, 0, 232, .35) !important;
    }

    /* VIEW SWITCH GROUP */
    .view-switch-group {
        display: inline-flex;
        align-items: center;
        background: #F1F5F9;
        border-radius: 999px;
        padding: 3px;
        border: 1px solid var(--orb-border);
    }

    .view-toggle-btn {
        border: none;
        background: transparent;
        padding: 7px 16px;
        border-radius: 999px;
        font-size: 12.5px;
        font-weight: 750;
        color: var(--orb-muted);
        cursor: pointer;
        transition: all .2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .view-toggle-btn.active {
        background: #fff;
        color: var(--orb-primary);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* POLICY TABLE CONTAINER */
    .policy-table-container {
        background: #fff;
        border-radius: 20px;
        border: 1px solid var(--orb-border);
        box-shadow: 0 8px 30px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .policy-table-header-bar {
        padding: 16px 22px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #FAFAFD;
        flex-wrap: wrap;
        gap: 12px;
    }

    .policy-table-header-title {
        font-size: 15px;
        font-weight: 900;
        color: var(--orb-text);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .policy-table-scroll {
        overflow-x: auto;
        max-width: 100%;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    .policy-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12.5px;
        white-space: nowrap;
    }

    .policy-table thead tr.group-header th {
        background: #F8FAFC;
        color: #334155;
        font-weight: 850;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: 10px 14px;
        border-bottom: 1px solid var(--orb-border);
        border-right: 1px solid #E2E8F0;
        text-align: center;
    }

    .policy-table thead tr.group-header th.gh-early {
        background: #EFF6FF;
        color: #1D4ED8;
        border-bottom: 2px solid #93C5FD;
    }

    .policy-table thead tr.group-header th.gh-work {
        background: #FAF5FF;
        color: #6B21A8;
        border-bottom: 2px solid #D8B4FE;
    }

    .policy-table thead tr.column-header th {
        background: #FFFFFF;
        color: #64748B;
        font-weight: 750;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding: 10px 12px;
        border-bottom: 2px solid var(--orb-border);
        border-right: 1px solid #F1F5F9;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .policy-table thead tr.column-header th.th-highlight {
        background: #F0FDF4;
        color: #166534;
    }

    .policy-table thead tr.column-header th.th-early-grace {
        background: #EFF6FF;
        color: #1E40AF;
        font-weight: 850;
    }

    .policy-table thead tr.column-header th.th-early-limit {
        background: #FFFBEB;
        color: #92400E;
        font-weight: 850;
    }

    .policy-table tbody td {
        padding: 11px 12px;
        border-bottom: 1px solid #F1F5F9;
        border-right: 1px solid #F8FAFC;
        vertical-align: middle;
        text-align: center;
        color: #1E293B;
        font-size: 12.5px;
        background: #FFFFFF;
    }

    .policy-table tbody tr:hover td {
        background: #FAF8FF !important;
    }

    .policy-table tbody tr:hover td.sticky-col-id,
    .policy-table tbody tr:hover td.sticky-col-name,
    .policy-table tbody tr:hover td.sticky-col-action {
        background: #F6F2FF !important;
    }

    /* STICKY COLUMNS IN POLICY TABLE */
    .sticky-col-id {
        position: sticky;
        left: 0;
        z-index: 3;
        background: #FFFFFF;
        width: 50px;
        min-width: 50px;
        text-align: center !important;
    }

    .sticky-col-name {
        position: sticky;
        left: 50px;
        z-index: 3;
        background: #FFFFFF;
        min-width: 230px;
        max-width: 280px;
        text-align: left !important;
        box-shadow: 4px 0 8px rgba(0,0,0,0.03);
    }

    .policy-table thead th.sticky-col-id,
    .policy-table thead th.sticky-col-name {
        z-index: 5;
        background: #F8FAFC !important;
    }

    .policy-table thead tr.column-header th.sticky-col-id,
    .policy-table thead tr.column-header th.sticky-col-name {
        background: #FFFFFF !important;
    }

    .sticky-col-action {
        position: sticky;
        right: 0;
        z-index: 3;
        background: #FFFFFF;
        box-shadow: -4px 0 8px rgba(0,0,0,0.03);
        text-align: center !important;
        min-width: 100px;
    }

    .policy-table thead th.sticky-col-action {
        z-index: 5;
        background: #F8FAFC !important;
    }

    .policy-table thead tr.column-header th.sticky-col-action {
        background: #FFFFFF !important;
    }

    /* BOOLEAN PILLS */
    .badge-bool {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 750;
    }

    .badge-bool-yes {
        background: #ECFDF5;
        color: #059669;
        border: 1px solid #A7F3D0;
    }

    .badge-bool-no {
        background: #F8FAFC;
        color: #94A3B8;
        border: 1px solid #E2E8F0;
    }

    /* NUMERIC BADGES */
    .badge-num {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 800;
        font-size: 12px;
    }

    .badge-num-primary {
        background: #EEF2FF;
        color: #4F46E5;
        border: 1px solid #C7D2FE;
    }

    .badge-num-grace {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
    }

    .badge-num-amber {
        background: #FFFBEB;
        color: #D97706;
        border: 1px solid #FDE68A;
    }

    .badge-num-rose {
        background: #FFF1F2;
        color: #E11D48;
        border: 1px solid #FECDD3;
    }

    .badge-num-slate {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #E2E8F0;
    }

    .policy-name-cell {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .policy-name-title {
        font-size: 13.5px;
        font-weight: 850;
        color: var(--orb-text);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .policy-name-sub {
        font-size: 11px;
        color: var(--orb-muted);
        white-space: normal;
        max-width: 250px;
        line-height: 1.25;
    }

    /* RESPONSIVE MEDIA QUERIES FOR ALL SCREEN SIZES */
    @media(max-width: 1200px) {
        .stat-card-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .policy-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width: 768px) {
        .att-page {
            padding: 12px 10px 24px;
        }

        .orb-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 20px 18px;
            border-radius: 20px;
            gap: 14px;
        }

        .orb-hero h1 {
            font-size: 22px;
        }

        .orb-hero p {
            font-size: 13px;
        }

        .orb-hero-actions {
            width: 100%;
        }

        .orb-hero-actions .btn-glass,
        .orb-hero-actions .att-btn {
            width: 100% !important;
            padding: 11px 20px !important;
        }

        .stat-card-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .stat-card {
            padding: 14px 12px;
            border-radius: 14px;
            gap: 10px;
        }

        .stat-icon-wrapper {
            width: 42px;
            height: 42px;
            font-size: 17px;
            border-radius: 10px;
        }

        .stat-val {
            font-size: 20px;
        }

        .stat-lbl {
            font-size: 10px;
        }

        .filter-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            margin-bottom: 16px;
        }

        .filter-pills {
            width: 100%;
            overflow-x: auto;
            flex-wrap: nowrap;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
        }

        .filter-pill {
            white-space: nowrap;
            flex-shrink: 0;
            padding: 6px 14px;
            font-size: 12px;
        }

        .search-box {
            min-width: 100%;
            width: 100%;
        }

        .policy-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .policy-card {
            padding: 16px;
            border-radius: 16px;
        }

        .policy-title {
            font-size: 16px;
        }

        .metric-grid {
            padding: 10px;
            border-radius: 10px;
            gap: 8px;
        }

        .metric-val {
            font-size: 14px;
        }

        .metric-lbl {
            font-size: 10px;
        }
    }

    @media(max-width: 480px) {
        .stat-card-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="att-page">
    <div class="att-container">

        {{-- ALERT MESSAGES --}}
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-15 mb-3" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- HERO BANNER USING DB BRANDING THEME --}}
        <div class="orb-hero">
            <div class="orb-hero-content">
                <div class="orb-hero-kicker">
                    <i class="fas fa-shield-alt"></i> HR Governance & Rules
                </div>
                <h1>Attendance Policy Master</h1>
                <p>Manage DB-driven attendance policies, work minute thresholds, WFH quotas, and discipline governance rules.</p>
            </div>
            <div class="orb-hero-actions">
                @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                    <button class="att-btn att-btn-glass btn-glass" data-toggle="modal" data-target="#createPolicyModal">
                        <i class="fas fa-plus-circle mr-1"></i> Create New Policy
                    </button>
                @endif
            </div>
        </div>

        {{-- STAT CARDS --}}
        <div class="stat-card-row">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background:var(--orb-soft); color:var(--orb-primary);">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $attendancePolicies->count() }}</div>
                    <div class="stat-lbl">Total Policies</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background:#ECFDF5; color:#10B981;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $attendancePolicies->where('is_active', true)->count() }}</div>
                    <div class="stat-lbl">Active Policies</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background:#F0F9FF; color:#0284C7;">
                    <i class="fas fa-laptop-house"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $attendancePolicies->where('wfh_enabled', true)->count() }}</div>
                    <div class="stat-lbl">WFH Allowed</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background:#FFFBEB; color:#D97706;">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $attendancePolicies->where('regularization_enabled', true)->count() }}</div>
                    <div class="stat-lbl">Regularization Enabled</div>
                </div>
            </div>
        </div>

        {{-- FILTER BAR & VIEW SWITCHER --}}
        <div class="filter-bar">
            <div class="filter-pills" id="policyFilterPills">
                <button type="button" class="filter-pill active" onclick="filterPolicies('all', this)">All Policies</button>
                <button type="button" class="filter-pill" onclick="filterPolicies('full_time', this)">Full Time</button>
                <button type="button" class="filter-pill" onclick="filterPolicies('part_time', this)">Part Time</button>
                <button type="button" class="filter-pill" onclick="filterPolicies('wfh', this)">WFH Enabled</button>
            </div>

            <div class="d-flex align-items-center gap-3" style="gap: 12px; flex-wrap: wrap;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="policySearchInput" onkeyup="searchPolicies()" placeholder="Search policy name or rules...">
                </div>

                {{-- VIEW TOGGLE: TABLE VIEW VS CARDS VIEW --}}
                <div class="view-switch-group">
                    <button type="button" class="view-toggle-btn active" id="btnTableView" onclick="switchView('table')">
                        <i class="fas fa-table mr-1"></i> Table View
                    </button>
                    <button type="button" class="view-toggle-btn" id="btnCardView" onclick="switchView('cards')">
                        <i class="fas fa-th-large mr-1"></i> Card View
                    </button>
                </div>
            </div>
        </div>

        {{-- COMPREHENSIVE TABLE VIEW (ALL POLICY COLUMNS IN TABLE FORMAT) --}}
        <div class="policy-table-container" id="policyTableContainer">
            <div class="policy-table-header-bar">
                <div class="policy-table-header-title">
                    <i class="fas fa-layer-group text-primary"></i>
                    <span>All Attendance Policy Rules Matrix</span>
                    
                </div>
               
            </div>

            <div class="policy-table-scroll">
                <table class="policy-table" id="policyDataTable">
                    <thead>
                        {{-- SUPER GROUP HEADERS --}}
                        <tr class="group-header">
                            <th colspan="3" class="sticky-col-id" style="text-align: left; padding-left: 18px;">Policy Identification</th>
                            <th colspan="5" class="gh-work">Working Hours & Thresholds</th>
                            <th colspan="6" class="gh-early">Early Logout Governance (New Rule Engine)</th>
                            <th colspan="6">Late Login Governance</th>
                            <th colspan="3">Combined Violations</th>
                            <th colspan="5">Missed Punch Rules</th>
                            <th colspan="8">Punch Modes & Device Controls</th>
                            <th colspan="7">Work From Home (WFH) Rules</th>
                            <th colspan="3">Regularization Rules</th>
                            <th colspan="1" class="sticky-col-action">Action</th>
                        </tr>
                        {{-- DETAILED SUB HEADERS --}}
                        <tr class="column-header">
                            {{-- Policy Info (Sticky Left) --}}
                            <th class="sticky-col-id">#</th>
                            <th class="sticky-col-name">Policy Name & Scope</th>
                            <th>Status</th>

                            {{-- Work Hours --}}
                            <th>Req. Work Min</th>
                            <th>Half Day Min</th>
                            <th>Absent Below</th>
                            <th>Half Day Rule</th>
                            <th>Auto Absent</th>

                            {{-- Early Logout (Highlighted) --}}
                            <th>Early Out Rule</th>
                            <th class="th-early-grace">Grace Mins (Full Day)</th>
                            <th class="th-early-limit">Half Day Threshold</th>
                            <th>Monthly Limit</th>
                            <th>Violation Rule</th>
                            <th>Violation Limit</th>

                            {{-- Late Login --}}
                            <th>Late Mark</th>
                            <th>Late Limit</th>
                            <th>Violation Rule</th>
                            <th>Violation Limit</th>
                            <th>Grace Late</th>
                            <th>Late LWP After</th>

                            {{-- Combined --}}
                            <th>Combined Rule</th>
                            <th>Combined Limit</th>
                            <th>Combined Action</th>

                            {{-- Missed Punch --}}
                            <th>Allowed Missed</th>
                            <th>After Minutes</th>
                            <th>Action Type</th>
                            <th>LWP After</th>
                            <th>Auto LWP</th>

                            {{-- Punch Controls --}}
                            <th>Tracking</th>
                            <th>Punch In Req.</th>
                            <th>Punch Out Req.</th>
                            <th>Web Punch</th>
                            <th>Mobile Punch</th>
                            <th>Mobile Only</th>
                            <th>Web Disabled</th>
                            <th>Punch Block</th>

                            {{-- WFH Rules --}}
                            <th>WFH Allowed</th>
                            <th>Monthly Limit</th>
                            <th>Mgr Approval</th>
                            <th>Proof Req.</th>
                            <th>Speed Test</th>
                            <th>Net Issue LWP</th>
                            <th>Power Issue LWP</th>

                            {{-- Regularization --}}
                            <th>Regularize</th>
                            <th>Window Days</th>
                            <th>Req. Approval</th>

                            {{-- Action (Sticky Right) --}}
                            <th class="sticky-col-action">Edit</th>
                        </tr>
                    </thead>
                    <tbody id="policyTableBody">
                        @forelse($attendancePolicies as $policy)
                            @php
                                $isPartTime = str_contains(strtolower($policy->policy_name), 'part time');
                                $categoryTag = $isPartTime ? 'part_time' : 'full_time';
                                $wfhTag = $policy->wfh_enabled ? 'wfh' : '';
                            @endphp
                            <tr class="policy-tr" data-category="{{ $categoryTag }} {{ $wfhTag }}" data-name="{{ strtolower($policy->policy_name) }}">
                                {{-- Sticky ID --}}
                                <td class="sticky-col-id font-weight-bold text-muted">#{{ $policy->id }}</td>

                                {{-- Sticky Name --}}
                                <td class="sticky-col-name">
                                    <div class="policy-name-cell">
                                        <div class="policy-name-title">
                                            <span>{{ $policy->policy_name }}</span>
                                            @if($isPartTime)
                                                <span class="badge badge-light border text-primary" style="font-size: 10px;">Part Time</span>
                                            @endif
                                        </div>
                                        <div class="policy-name-sub">{{ Str::limit($policy->description ?? 'Standard policy rule definitions.', 55) }}</div>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td>
                                    <span class="status-badge {{ $policy->is_active ? 'status-active' : 'status-inactive' }}">
                                        {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                {{-- Work Hours --}}
                                <td><span class="badge-num badge-num-primary">{{ $policy->required_work_minutes ?? 480 }}m</span></td>
                                <td><span class="badge-num badge-num-slate">{{ $policy->half_day_min_minutes ?? 240 }}m</span></td>
                                <td><span class="badge-num badge-num-rose">{{ $policy->absent_below_minutes ?? 120 }}m</span></td>
                                <td>
                                    <span class="badge-bool {{ ($policy->half_day_enabled ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->half_day_enabled ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->half_day_enabled ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ $policy->auto_absent_enabled ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ $policy->auto_absent_enabled ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $policy->auto_absent_enabled ? 'ON' : 'OFF' }}
                                    </span>
                                </td>

                                {{-- Early Logout (Highlighted New Rules) --}}
                                <td>
                                    <span class="badge-bool {{ ($policy->early_out_enabled ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->early_out_enabled ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->early_out_enabled ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-num badge-num-grace" title="Punch out up to this many minutes early = Full Day, no violation">
                                        <i class="fas fa-shield-alt mr-1"></i>{{ $policy->early_out_grace_minutes ?? 60 }} mins
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-num badge-num-amber" title="Early minutes between grace and this = Early Logout. Above this = Half Day.">
                                        <i class="fas fa-sign-out-alt mr-1"></i>{{ $policy->early_out_half_day_minutes ?? 90 }} mins
                                    </span>
                                </td>
                                <td><span class="badge-num badge-num-slate">{{ $policy->early_out_limit ?? 2 }}/mo</span></td>
                                <td>
                                    <span class="badge-bool {{ ($policy->early_violation_enabled ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->early_violation_enabled ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->early_violation_enabled ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                </td>
                                <td><span class="badge-num badge-num-slate">{{ $policy->early_violation_limit ?? 3 }}</span></td>

                                {{-- Late Login --}}
                                <td>
                                    <span class="badge-bool {{ ($policy->late_mark_enabled ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->late_mark_enabled ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->late_mark_enabled ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                </td>
                                <td><span class="badge-num badge-num-slate">{{ $policy->late_mark_limit ?? 2 }}/mo</span></td>
                                <td>
                                    <span class="badge-bool {{ ($policy->late_violation_enabled ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->late_violation_enabled ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->late_violation_enabled ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                </td>
                                <td><span class="badge-num badge-num-slate">{{ $policy->late_violation_limit ?? 3 }}</span></td>
                                <td><span class="badge-num badge-num-slate">{{ $policy->grace_late_allowed ?? 0 }}m</span></td>
                                <td><span class="badge-num badge-num-rose">After {{ $policy->late_lwp_after ?? 3 }}</span></td>

                                {{-- Combined Violations --}}
                                <td>
                                    <span class="badge-bool {{ ($policy->combined_violation_enabled ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->combined_violation_enabled ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->combined_violation_enabled ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                </td>
                                <td><span class="badge-num badge-num-slate">{{ $policy->combined_violation_limit ?? 3 }}</span></td>
                                <td><span class="badge badge-light border text-capitalize font-weight-bold">{{ str_replace('_', ' ', $policy->combined_violation_action ?? 'half_day') }}</span></td>

                                {{-- Missed Punch --}}
                                <td><span class="badge-num badge-num-slate">{{ $policy->allowed_missed_punches ?? 2 }}</span></td>
                                <td><span class="badge-num badge-num-slate">{{ $policy->missed_punch_after_minutes ?? 60 }}m</span></td>
                                <td><span class="badge badge-light border text-uppercase font-weight-bold">{{ $policy->missed_punch_action ?? 'lwp' }}</span></td>
                                <td><span class="badge-num badge-num-rose">After {{ $policy->missed_punch_lwp_after ?? 3 }}</span></td>
                                <td>
                                    <span class="badge-bool {{ ($policy->auto_lwp_for_missed_punch ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->auto_lwp_for_missed_punch ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->auto_lwp_for_missed_punch ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                </td>

                                {{-- Punch & Device Controls --}}
                                <td>
                                    <span class="badge-bool {{ ($policy->attendance_tracking_enabled ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->attendance_tracking_enabled ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->attendance_tracking_enabled ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ ($policy->punch_in_required ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->punch_in_required ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->punch_in_required ?? true) ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ ($policy->punch_out_required ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->punch_out_required ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->punch_out_required ?? true) ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ ($policy->allow_web_punch ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->allow_web_punch ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->allow_web_punch ?? true) ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ ($policy->allow_mobile_punch ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->allow_mobile_punch ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->allow_mobile_punch ?? true) ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ $policy->mobile_only_punch ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ $policy->mobile_only_punch ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $policy->mobile_only_punch ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ $policy->web_punch_disabled ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ $policy->web_punch_disabled ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $policy->web_punch_disabled ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ $policy->punch_block_enabled ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ $policy->punch_block_enabled ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $policy->punch_block_enabled ? 'ON' : 'OFF' }}
                                    </span>
                                </td>

                                {{-- WFH Rules --}}
                                <td>
                                    <span class="badge-bool {{ $policy->wfh_enabled ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ $policy->wfh_enabled ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $policy->wfh_enabled ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td><span class="badge-num badge-num-primary">{{ $policy->monthly_wfh_limit ?? 2 }}/mo</span></td>
                                <td>
                                    <span class="badge-bool {{ ($policy->manager_approval_required ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->manager_approval_required ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->manager_approval_required ?? true) ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ $policy->internet_proof_required ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ $policy->internet_proof_required ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $policy->internet_proof_required ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ $policy->speed_test_required ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ $policy->speed_test_required ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $policy->speed_test_required ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ $policy->internet_issue_lwp ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ $policy->internet_issue_lwp ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $policy->internet_issue_lwp ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-bool {{ $policy->electricity_issue_lwp ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ $policy->electricity_issue_lwp ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $policy->electricity_issue_lwp ? 'Yes' : 'No' }}
                                    </span>
                                </td>

                                {{-- Regularization --}}
                                <td>
                                    <span class="badge-bool {{ ($policy->regularization_enabled ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->regularization_enabled ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->regularization_enabled ?? true) ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td><span class="badge-num badge-num-slate">{{ $policy->regularization_days ?? 7 }} days</span></td>
                                <td>
                                    <span class="badge-bool {{ ($policy->regularization_requires_approval ?? true) ? 'badge-bool-yes' : 'badge-bool-no' }}">
                                        <i class="fas {{ ($policy->regularization_requires_approval ?? true) ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ ($policy->regularization_requires_approval ?? true) ? 'Yes' : 'No' }}
                                    </span>
                                </td>

                                {{-- Sticky Action --}}
                                <td class="sticky-col-action">
                                    @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                                        <button type="button" class="btn btn-orb-edit px-3 py-1" style="font-size: 11.5px; border-radius: 999px;" data-toggle="modal" data-target="#editPolicyModal{{ $policy->id }}" data-bs-toggle="modal" data-bs-target="#editPolicyModal{{ $policy->id }}" onclick="$('#editPolicyModal{{ $policy->id }}').modal('show')">
                                            <i class="fas fa-sliders-h mr-1"></i> Edit
                                        </button>
                                    @else
                                        <span class="text-muted"><i class="fas fa-lock"></i></span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="47" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                    No Attendance Policies Found in Database
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- DYNAMIC POLICY CARDS GRID (ALTERNATIVE VIEW) --}}
        <div class="policy-grid" id="policyCardGrid" style="display: none;">
            @forelse($attendancePolicies as $policy)
                @php
                    $isPartTime = str_contains(strtolower($policy->policy_name), 'part time');
                    $categoryTag = $isPartTime ? 'part_time' : 'full_time';
                    $wfhTag = $policy->wfh_enabled ? 'wfh' : '';
                @endphp
                <div class="policy-card policy-item" data-category="{{ $categoryTag }} {{ $wfhTag }}" data-name="{{ strtolower($policy->policy_name) }}">
                    <div>
                        <div class="policy-header">
                            <div>
                                <h3 class="policy-title">{{ $policy->policy_name }}</h3>
                                <p class="policy-desc">{{ $policy->description ?? 'No policy description specified.' }}</p>
                            </div>
                            <span class="status-badge {{ $policy->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        {{-- DYNAMIC WORK MINUTE THRESHOLDS --}}
                        <div class="metric-grid">
                            <div class="metric-item">
                                <span class="metric-val">{{ $policy->required_work_minutes ?? 480 }} mins</span>
                                <span class="metric-lbl">Req. Work Min</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-val">{{ $policy->half_day_min_minutes ?? 240 }} mins</span>
                                <span class="metric-lbl">Half Day Min</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-val">{{ $policy->absent_below_minutes ?? 120 }} mins</span>
                                <span class="metric-lbl">Absent Below</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-val">{{ $policy->lunch_break_minutes ?? 60 }} mins</span>
                                <span class="metric-lbl">Lunch Break</span>
                            </div>
                        </div>

                        {{-- DYNAMIC GOVERNANCE BADGES --}}
                        <div class="badge-section">
                            <span class="feature-pill pill-primary">
                                <i class="fas fa-clock"></i> Early Out Grace: {{ $policy->early_out_grace_minutes ?? 60 }}m &bull; Limit: {{ $policy->early_out_half_day_minutes ?? 90 }}m
                            </span>
                            <span class="feature-pill {{ $policy->punch_block_enabled ? 'pill-warning' : 'pill-secondary' }}">
                                <i class="fas fa-lock"></i> Punch Block: {{ $policy->punch_block_enabled ? 'ON' : 'OFF' }}
                            </span>
                            <span class="feature-pill {{ $policy->auto_absent_enabled ? 'pill-danger' : 'pill-secondary' }}" style="{{ $policy->auto_absent_enabled ? 'background:#FEF2F2; color:#DC2626;' : '' }}">
                                <i class="fas fa-user-slash"></i> Auto Absent: {{ $policy->auto_absent_enabled ? 'ON' : 'OFF' }}
                            </span>
                            <span class="feature-pill pill-success">
                                <i class="fas fa-exclamation-circle"></i> Violation Limit: {{ $policy->combined_violation_limit ?? 3 }}
                            </span>
                            @if($policy->wfh_enabled)
                                <span class="feature-pill pill-primary">
                                    <i class="fas fa-laptop"></i> WFH Limit: {{ $policy->monthly_wfh_limit ?? 2 }}/mo
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="policy-footer">
                        <small class="text-muted font-weight-bold">
                            <i class="far fa-calendar-alt mr-1"></i> Updated {{ \Carbon\Carbon::parse($policy->updated_at)->diffForHumans() }}
                        </small>
                        @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                            <button type="button" class="btn btn-orb-edit" data-toggle="modal" data-target="#editPolicyModal{{ $policy->id }}" data-bs-toggle="modal" data-bs-target="#editPolicyModal{{ $policy->id }}" onclick="$('#editPolicyModal{{ $policy->id }}').modal('show')">
                                <i class="fas fa-sliders-h mr-1"></i> Edit Rules
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-folder-open text-muted fa-3x mb-3"></i>
                    <h5 class="text-muted font-weight-bold">No Attendance Policies Found in Database</h5>
                </div>
            @endforelse
        </div>

        {{-- CREATE NEW POLICY MODAL --}}
        @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
            <div class="modal fade" id="createPolicyModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <form method="POST" action="{{ route('attendance.policy_rules.store') }}" class="modal-content att-modal-content">
                        @csrf
                        
                        <div class="modal-header att-modal-header">
                            <div>
                                <h5 class="att-modal-title"><i class="fas fa-plus-circle mr-2"></i> Create Attendance Policy</h5>
                                <div class="att-modal-subtitle">Define new corporate attendance thresholds & governance rules</div>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body att-modal-body">
                            <div class="att-modal-section">
                                <div class="att-modal-section-title"><i class="fas fa-tag"></i> Policy Details & Scope</div>
                                <div class="row">
                                    <div class="col-md-8 form-group">
                                        <label>Policy Name <span class="text-danger">*</span></label>
                                        <input type="text" name="policy_name" class="form-control" placeholder="e.g. Standard Corporate Policy" required>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Working Hours / Day</label>
                                        <input type="number" step="0.5" name="working_hours_per_day" class="form-control" value="8">
                                    </div>
                                </div>
                            </div>

                            <div class="att-modal-section">
                                <div class="att-modal-section-title"><i class="fas fa-clock"></i> Working Hours & Thresholds</div>
                                <div class="row">
                                    <div class="col-md-3 form-group">
                                        <label>Req. Work Mins</label>
                                        <input type="number" name="required_work_minutes" class="form-control" value="480">
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label>Half Day Min Mins</label>
                                        <input type="number" name="half_day_min_minutes" class="form-control" value="270">
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label>Absent Below Mins</label>
                                        <input type="number" name="absent_below_minutes" class="form-control" value="270">
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label>Lunch Break Mins</label>
                                        <input type="number" name="lunch_break_minutes" class="form-control" value="60">
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6 form-group">
                                        <label>Early Logout Grace (Minutes) - Full Day Free Grace</label>
                                        <input type="number" name="early_out_grace_minutes" class="form-control" value="60" min="0">
                                        <small class="text-muted d-block mt-1">Punch out up to this many minutes early = Full Day, no violation.</small>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Early Logout Limit (Minutes) - Above this = Half Day</label>
                                        <input type="number" name="early_out_half_day_minutes" class="form-control" value="90" min="1">
                                        <small class="text-muted d-block mt-1">Between grace and this limit = Early Logout. Above this limit = Half Day.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="att-modal-section mb-0">
                                <div class="att-modal-section-title"><i class="fas fa-user-shield"></i> Governance, Automations & Quotas</div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label>Allowed Missed Punches</label>
                                        <input type="number" name="allowed_missed_punches" class="form-control" value="2">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Violation Limit</label>
                                        <input type="number" name="combined_violation_limit" class="form-control" value="3">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Monthly WFH Limit</label>
                                        <input type="number" name="monthly_wfh_limit" class="form-control" value="2">
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6 form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="createAutoBlockSwitch" name="punch_block_enabled" value="1" checked>
                                            <label class="custom-control-label font-weight-bold text-dark" for="createAutoBlockSwitch">Punch Block Enabled</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="createAutoAbsentSwitch" name="auto_absent_enabled" value="1" checked>
                                            <label class="custom-control-label font-weight-bold text-dark" for="createAutoAbsentSwitch">Auto Absent Enabled</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6 form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="createWfhSwitch" name="wfh_enabled" value="1" checked>
                                            <label class="custom-control-label font-weight-bold text-dark" for="createWfhSwitch">Allow Work From Home (WFH)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="createActiveSwitch" name="is_active" value="1" checked>
                                            <label class="custom-control-label font-weight-bold text-dark" for="createActiveSwitch">Policy Is Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer att-modal-footer">
                            <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-modal-save">
                                <i class="fas fa-plus-circle mr-1"></i> Create Policy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- EDIT POLICY MODALS (OUTSIDE HIDDEN CONTAINER SO MODAL CAN OPEN FROM TABLE OR CARD) --}}
        @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
            @foreach($attendancePolicies as $policy)
                <div class="modal fade" id="editPolicyModal{{ $policy->id }}" tabindex="-1" role="dialog" aria-labelledby="editPolicyModalLabel{{ $policy->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <form method="POST" action="{{ route('attendance.policy_rules.update', $policy->id) }}" class="modal-content att-modal-content">
                            @csrf
                            @method('PUT')
                            
                            {{-- MODAL HEADER --}}
                            <div class="modal-header att-modal-header">
                                <div>
                                    <h5 class="att-modal-title" id="editPolicyModalLabel{{ $policy->id }}"><i class="fas fa-shield-alt mr-2"></i> Edit Attendance Policy</h5>
                                    <div class="att-modal-subtitle">{{ $policy->policy_name }} &bull; Governance & Discipline Rules</div>
                                </div>
                                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            {{-- MODAL BODY WITH STRUCTURED HRMS SECTIONS --}}
                            <div class="modal-body att-modal-body">
                                
                                {{-- SECTION 1: POLICY IDENTIFICATION --}}
                                <div class="att-modal-section">
                                    <div class="att-modal-section-title">
                                        <i class="fas fa-tag"></i> Policy Details & Scope
                                    </div>
                                    <div class="row">
                                        <div class="col-md-7 form-group">
                                            <label>Policy Name</label>
                                            <input type="text" name="policy_name" class="form-control" value="{{ $policy->policy_name }}" required>
                                        </div>
                                        <div class="col-md-5 form-group">
                                            <label>Working Hours / Day</label>
                                            <input type="number" step="0.5" name="working_hours_per_day" class="form-control" value="{{ $policy->working_hours_per_day ?? 8 }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- SECTION 2: WORK MINUTE THRESHOLDS --}}
                                <div class="att-modal-section">
                                    <div class="att-modal-section-title">
                                        <i class="fas fa-clock"></i> Work Minute Threshold Rules
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label>Req. Work Mins</label>
                                            <input type="number" name="required_work_minutes" class="form-control" value="{{ $policy->required_work_minutes ?? 480 }}">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Half Day Min Mins</label>
                                            <input type="number" name="half_day_min_minutes" class="form-control" value="{{ $policy->half_day_min_minutes ?? 240 }}">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Absent Below Mins</label>
                                            <input type="number" name="absent_below_minutes" class="form-control" value="{{ $policy->absent_below_minutes ?? 120 }}">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Lunch Break Mins</label>
                                            <input type="number" name="lunch_break_minutes" class="form-control" value="{{ $policy->lunch_break_minutes ?? 60 }}">
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6 form-group">
                                            <label>Early Logout Grace (minutes) - free time before expected logout</label>
                                            <input type="number" name="early_out_grace_minutes" class="form-control" value="{{ $policy->early_out_grace_minutes ?? 60 }}" min="0">
                                            <small class="text-muted d-block mt-1">Punch out up to this many minutes before expected logout = Full Day, no violation.</small>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Early Logout limit (minutes) - above this = Half Day</label>
                                            <input type="number" name="early_out_half_day_minutes" class="form-control" value="{{ $policy->early_out_half_day_minutes ?? 90 }}" min="1">
                                            <small class="text-muted d-block mt-1">Between grace and this limit = Early Logout. Above this limit = Half Day.</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- SECTION 3: DISCIPLINE & GOVERNANCE --}}
                                <div class="att-modal-section mb-0">
                                    <div class="att-modal-section-title">
                                        <i class="fas fa-user-shield"></i> Discipline, Automation & Governance
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Allowed Missed Punches</label>
                                            <input type="number" name="allowed_missed_punches" class="form-control" value="{{ $policy->allowed_missed_punches ?? 2 }}">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Combined Violation Limit</label>
                                            <input type="number" name="combined_violation_limit" class="form-control" value="{{ $policy->combined_violation_limit ?? 3 }}">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Monthly WFH Limit</label>
                                            <input type="number" name="monthly_wfh_limit" class="form-control" value="{{ $policy->monthly_wfh_limit ?? 2 }}">
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6 form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="autoBlockSwitch{{ $policy->id }}" name="auto_block_enabled" value="1" {{ $policy->auto_block_enabled ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark" for="autoBlockSwitch{{ $policy->id }}">Auto Block Punch-In Enabled</label>
                                            </div>
                                            <small class="text-muted d-block mt-1">Blocks punch-in automatically when late time window expires.</small>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="autoAbsentSwitch{{ $policy->id }}" name="auto_absent_enabled" value="1" {{ $policy->auto_absent_enabled ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark" for="autoAbsentSwitch{{ $policy->id }}">Auto Absent Execution Enabled</label>
                                            </div>
                                            <small class="text-muted d-block mt-1">Marks unpunched or late un-regularized days as Absent at day end.</small>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6 form-group mb-0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="wfhEnabledSwitch{{ $policy->id }}" name="wfh_enabled" value="1" {{ $policy->wfh_enabled ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark" for="wfhEnabledSwitch{{ $policy->id }}">Allow Work From Home (WFH)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group mb-0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="isActiveSwitch{{ $policy->id }}" name="is_active" value="1" {{ $policy->is_active ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark" for="isActiveSwitch{{ $policy->id }}">Policy Is Active</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- MODAL FOOTER --}}
                            <div class="modal-footer att-modal-footer">
                                <button type="button" class="btn btn-modal-cancel" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-modal-save">
                                    <i class="fas fa-check-circle mr-1"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif

    </div>
</div>

<script>
    function switchView(view) {
        const tableView = document.getElementById('policyTableContainer');
        const cardView = document.getElementById('policyCardGrid');
        const btnTable = document.getElementById('btnTableView');
        const btnCard = document.getElementById('btnCardView');

        if (view === 'table') {
            if (tableView) tableView.style.display = 'block';
            if (cardView) cardView.style.display = 'none';
            if (btnTable) btnTable.classList.add('active');
            if (btnCard) btnCard.classList.remove('active');
            try { localStorage.setItem('orb_policy_view', 'table'); } catch(e) {}
        } else {
            if (tableView) tableView.style.display = 'none';
            if (cardView) cardView.style.display = 'grid';
            if (btnCard) btnCard.classList.add('active');
            if (btnTable) btnTable.classList.remove('active');
            try { localStorage.setItem('orb_policy_view', 'cards'); } catch(e) {}
        }
    }

    function filterPolicies(category, element) {
        document.querySelectorAll('#policyFilterPills .filter-pill').forEach(el => el.classList.remove('active'));
        if (element) element.classList.add('active');

        let visibleCount = 0;

        // Filter Table rows
        const rows = document.querySelectorAll('#policyTableBody tr.policy-tr');
        rows.forEach(row => {
            const cat = row.getAttribute('data-category') || '';
            if (category === 'all' || cat.includes(category)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Filter Cards
        const cards = document.querySelectorAll('#policyCardGrid .policy-item');
        cards.forEach(item => {
            const cat = item.getAttribute('data-category') || '';
            if (category === 'all' || cat.includes(category)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('policyTableCount');
        if (countBadge) {
            countBadge.innerText = visibleCount + ' Policies';
        }
    }

    function searchPolicies() {
        const query = (document.getElementById('policySearchInput').value || '').toLowerCase().trim();
        let visibleCount = 0;

        // Search Table rows
        const rows = document.querySelectorAll('#policyTableBody tr.policy-tr');
        rows.forEach(row => {
            const name = (row.getAttribute('data-name') || '').toLowerCase();
            const text = (row.innerText || '').toLowerCase();
            if (query === '' || name.includes(query) || text.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Search Cards
        const cards = document.querySelectorAll('#policyCardGrid .policy-item');
        cards.forEach(item => {
            const name = (item.getAttribute('data-name') || '').toLowerCase();
            const text = (item.innerText || '').toLowerCase();
            if (query === '' || name.includes(query) || text.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('policyTableCount');
        if (countBadge) {
            countBadge.innerText = visibleCount + ' Policies';
        }
    }

    // Restore view preference
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const savedView = localStorage.getItem('orb_policy_view');
            if (savedView === 'cards') {
                switchView('cards');
            } else {
                switchView('table');
            }
        } catch(e) {}
    });
</script>
@endsection
