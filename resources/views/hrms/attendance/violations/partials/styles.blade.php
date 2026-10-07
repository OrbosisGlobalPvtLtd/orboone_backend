<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
    :root {
        --orb-bg: #F8FAFC;
        --orb-card: #FFFFFF;
        --orb-border: #E2E8F0;
        --orb-text: #0F172A;
        --orb-muted: #64748B;
        --orb-soft: #F1EDFF;
        --orb-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08);
    }

    body {
        overflow-x: hidden !important;
        background: var(--orb-bg) !important;
    }

    .att-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 20px 24px 48px;
        box-sizing: border-box;
        width: 100%;
    }

    .att-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
    }

    /* Hero Header */
    .att-hero {
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%);
        border-radius: 24px !important;
        padding: 28px 32px;
        margin-bottom: 22px;
        box-shadow: 0 20px 45px rgba(75, 0, 232, 0.18);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        color: #fff !important;
        position: relative;
        overflow: hidden;
    }

    .att-hero * {
        color: #fff !important;
    }

    .att-hero:before {
        content: "";
        position: absolute;
        right: -80px;
        top: -110px;
        width: 340px;
        height: 340px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        pointer-events: none;
    }

    .att-kicker {
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        opacity: 0.95;
        margin-bottom: 8px;
        display: inline-flex;
        gap: 8px;
        align-items: center;
        background: rgba(255, 255, 255, 0.15);
        padding: 4px 12px;
        border-radius: 999px;
    }

    .att-title {
        font-size: 26px;
        font-weight: 900;
        margin: 0;
        line-height: 1.25;
        letter-spacing: -0.02em;
    }

    .att-subtitle {
        font-size: 13.5px;
        opacity: 0.92;
        margin-top: 6px;
        max-width: 820px;
        line-height: 1.5;
    }

    /* Summary KPI Cards */
    .audit-kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
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

    /* Filter Panel & Card */
    .att-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px !important;
        box-shadow: var(--orb-shadow);
        overflow: hidden !important;
        margin-bottom: 24px;
    }

    .att-section-head {
        padding: 20px 24px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .att-section-title {
        font-size: 18px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .att-section-title i {
        color: var(--orb-primary, #4B00E8);
    }

    .att-filter-panel {
        padding: 16px 24px 18px;
        border-bottom: 1px solid var(--orb-border);
        background: #FAFBFC;
    }

    .att-filter-grid {
        display: flex;
        align-items: flex-end;
        gap: 12px 14px;
        flex-wrap: wrap;
    }

    .att-filter-grid > div {
        flex: 1 1 170px;
        min-width: 150px;
    }

    .att-filter-grid > div.att-filter-actions-col {
        flex: 0 0 auto;
        min-width: auto;
    }

    .att-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .att-search-btn {
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%);
        border: none;
        color: #fff;
        font-weight: 800;
        font-size: 13px;
        padding: 0 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.2);
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .att-search-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(75, 0, 232, 0.3);
        color: #fff;
    }

    .att-reset-btn {
        height: 40px;
        width: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #fff;
        border: 1px solid var(--orb-border);
        color: var(--orb-muted);
        font-weight: 700;
        transition: all 0.15s ease;
    }

    .att-reset-btn:hover {
        background: var(--orb-soft);
        color: var(--orb-primary, #4B00E8);
        border-color: rgba(75, 0, 232, 0.2);
    }

    .att-filter-grid label {
        font-size: 11px;
        font-weight: 800;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
        display: block;
    }

    .att-filter-grid .form-control,
    .att-filter-grid .custom-select {
        height: 40px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        font-size: 13px;
        font-weight: 600;
        padding: 0 14px;
        box-shadow: none !important;
        background: #fff;
        width: 100%;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .att-filter-grid .form-control:focus,
    .att-filter-grid .custom-select:focus {
        border-color: var(--orb-primary, #4B00E8);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1) !important;
    }

    .att-filter-grid .select2-container .select2-selection--single {
        height: 40px !important;
        border-radius: 12px !important;
        border: 1px solid var(--orb-border) !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 8px !important;
        background: #fff !important;
    }

    .att-filter-grid .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        padding-left: 4px !important;
    }

    .att-filter-grid .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        right: 8px !important;
    }

    /* Badges & Color Scheme */
    .orb-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .orb-badge-orange { background: #FFEDD5; color: #C2410C; }
    .orb-badge-blue   { background: #DBEAFE; color: #1D4ED8; }
    .orb-badge-red    { background: #FEE2E2; color: #B91C1C; }
    .orb-badge-yellow { background: #FEF3C7; color: #B45309; }
    .orb-badge-darkred { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
    .orb-badge-green  { background: #D1FAE5; color: #047857; }
    .orb-badge-purple { background: #F3E8FF; color: #6D28D9; }
    .orb-badge-secondary { background: #F1F5F9; color: #475569; }

    /* Counter pill */
    .counter-pill {
        background: #F1F5F9;
        border: 1px solid #CBD5E1;
        color: #0F172A;
        font-weight: 900;
        font-size: 12px;
        padding: 3px 9px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* DATATABLE WRAPPER */
    .dataTables_wrapper .dataTables_filter {
        display: none !important;
    }

    /* Table layout */
    .att-table-wrap {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        background: #fff;
    }

    .att-table {
        width: 100% !important;
        min-width: 1250px !important;
        border-collapse: collapse !important;
        margin: 0 !important;
    }

    .att-table thead th {
        background: #F8FAFC !important;
        color: var(--orb-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 14px 18px !important;
        border-top: 0 !important;
        border-bottom: 1.5px solid var(--orb-border) !important;
        white-space: nowrap !important;
    }

    .att-table tbody td {
        padding: 13px 18px !important;
        vertical-align: middle !important;
        font-size: 13px !important;
        border-bottom: 1px solid #F1F5F9 !important;
        white-space: nowrap !important;
    }

    .att-table tbody tr:hover {
        background-color: #FAFBFF !important;
    }

    .emp-name-btn {
        background: none;
        border: none;
        padding: 0;
        color: var(--orb-text);
        font-weight: 800;
        cursor: pointer;
        text-align: left;
        font-size: 13.5px;
        text-decoration: none !important;
        transition: color 0.15s ease;
    }

    .emp-name-btn:hover {
        color: var(--orb-primary, #4B00E8);
    }

    .orb-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: var(--orb-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: none;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .orb-action-btn:hover {
        background: var(--orb-soft);
        color: var(--orb-primary, #4B00E8);
        border-color: rgba(75, 0, 232, 0.2);
    }

    /* Drawer / Offcanvas Styles */
    .audit-drawer-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(4px);
        z-index: 1050;
        display: none;
    }

    .audit-drawer {
        position: fixed;
        top: 0;
        right: -550px;
        width: 520px;
        max-width: 90vw;
        height: 100vh;
        background: #fff;
        box-shadow: -10px 0 40px rgba(0, 0, 0, 0.15);
        z-index: 1051;
        transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }

    .audit-drawer.open {
        right: 0;
    }

    .audit-drawer-head {
        padding: 18px 24px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%);
        color: #fff !important;
    }

    .audit-drawer-head * {
        color: #fff !important;
    }

    .audit-drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    .timeline-card {
        position: relative;
        margin-bottom: 18px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 13px 15px;
    }

    .timeline-card:before {
        content: "";
        position: absolute;
        left: -21px;
        top: 16px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--orb-primary, #4B00E8);
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px var(--orb-primary, #4B00E8);
    }

    /* Responsive Media Queries */
    @media(max-width: 1300px) {
        .audit-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .att-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) auto;
        }
    }

    @media(max-width: 992px) {
        .att-page {
            padding: 16px 12px 36px;
        }
        .att-hero {
            padding: 22px 20px;
            border-radius: 18px !important;
            flex-direction: column;
            align-items: flex-start;
        }
        .att-title {
            font-size: 22px;
        }
        .audit-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .att-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .att-filter-actions {
            width: 100%;
        }
        .att-search-btn {
            flex: 1;
        }
    }

    @media(max-width: 680px) {
        .audit-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .att-kpi {
            min-height: 74px;
            padding: 10px 12px;
            border-radius: 14px;
        }
        .att-kpi-value {
            font-size: 20px;
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
        .att-filter-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .att-filter-panel {
            padding: 14px 16px;
        }
    }
</style>
