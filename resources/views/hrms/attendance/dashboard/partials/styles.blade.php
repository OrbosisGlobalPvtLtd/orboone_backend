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

    .att-page {
        min-height: calc(100vh - 90px);
        padding: 18px 16px 36px;
        background: var(--orb-bg);
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Modern OrboOne Hero Banner */
    .att-hero {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        border-radius: 24px;
        padding: 24px 28px;
        margin-bottom: 20px;
        box-shadow: 0 16px 38px rgba(75, 0, 232, .18);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .att-hero:before {
        content: "";
        position: absolute;
        right: -70px;
        top: -100px;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .12);
        pointer-events: none;
    }

    .att-hero:after {
        content: "";
        position: absolute;
        right: 180px;
        bottom: -90px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .06);
        pointer-events: none;
    }

    .att-hero-content {
        position: relative;
        z-index: 2;
        min-width: 0;
    }

    .att-hero-kicker {
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .18);
        color: #fff;
        margin-bottom: 8px;
        backdrop-filter: blur(4px);
    }

    .att-hero-title {
        font-size: 28px;
        font-weight: 950;
        margin: 0;
        line-height: 1.2;
        color: #fff;
        letter-spacing: -.02em;
    }

    .att-hero-subtitle {
        font-size: 13.5px;
        font-weight: 550;
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .92);
        max-width: 820px;
        line-height: 1.5;
    }

    .att-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
        flex-shrink: 0;
    }

    .att-btn {
        border: 0;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 850;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none !important;
        white-space: nowrap;
        transition: all .2s ease;
        cursor: pointer;
    }

    .att-btn-glass,
    .att-hero-actions .att-btn {
        background: rgba(255, 255, 255, 0.18) !important;
        border: 1px solid rgba(255, 255, 255, 0.38) !important;
        color: #ffffff !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border-radius: 999px !important;
        padding: 9px 20px !important;
        font-size: 13.5px !important;
        font-weight: 750 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        text-decoration: none !important;
        white-space: nowrap !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        transition: all 0.2s ease !important;
    }

    .att-btn-glass:hover,
    .att-hero-actions .att-btn:hover {
        background: rgba(255, 255, 255, 0.32) !important;
        border-color: rgba(255, 255, 255, 0.65) !important;
        color: #ffffff !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
    }

    .att-btn-glass i,
    .att-hero-actions .att-btn i {
        color: #ffffff !important;
    }


    /* KPI Summary Cards Grid */
    .att-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(195px, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }

    @media (min-width: 1650px) {
        .att-kpi-grid {
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }
    }

    @media (min-width: 1200px) and (max-width: 1649px) {
        .att-kpi-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }

    @media (min-width: 860px) and (max-width: 1199px) {
        .att-kpi-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (min-width: 576px) and (max-width: 859px) {
        .att-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .att-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
    }

    .att-kpi {
        min-height: 94px;
        padding: 13px 15px 12px;
        border-radius: 18px;
        border: 1px solid #EAEFF5;
        background: #fff;
        box-shadow: 0 6px 18px rgba(16, 24, 40, .035);
        position: relative;
        overflow: hidden;
        transition: all .2s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .att-kpi:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(16, 24, 40, .08);
        border-color: rgba(var(--tone-rgb, 75, 0, 232), 0.4);
    }

    .att-kpi:after {
        content: "";
        position: absolute;
        right: -20px;
        top: -24px;
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: var(--tone-soft);
        pointer-events: none;
        opacity: 0.75;
        transition: transform .3s ease;
    }

    .att-kpi:hover:after {
        transform: scale(1.18);
    }

    .att-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
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
        font-size: 14px;
        flex-shrink: 0;
    }

    .att-kpi-value {
        font-size: 26px;
        line-height: 1;
        font-weight: 950;
        color: var(--orb-text);
        letter-spacing: -.02em;
    }

    .att-kpi-label {
        margin-top: 10px;
        font-size: 11px;
        color: #475467;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .04em;
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
        bottom: 0px;
        height: 3px;
        border-radius: 6px 6px 0 0;
        background: linear-gradient(90deg, var(--tone), transparent);
        opacity: 0.9;
    }

    .tone-success {
        --tone: #10B981;
        --tone-soft: rgba(16, 185, 129, .13);
        --tone-rgb: 16, 185, 129;
    }

    .tone-danger {
        --tone: #EF4444;
        --tone-soft: rgba(239, 68, 68, .13);
        --tone-rgb: 239, 68, 68;
    }

    .tone-warning {
        --tone: #F59E0B;
        --tone-soft: rgba(245, 158, 11, .14);
        --tone-rgb: 245, 158, 11;
    }

    .tone-orange {
        --tone: #EA580C;
        --tone-soft: rgba(234, 88, 12, .13);
        --tone-rgb: 234, 88, 12;
    }

    .tone-amber {
        --tone: #D97706;
        --tone-soft: rgba(217, 119, 6, .13);
        --tone-rgb: 217, 119, 6;
    }

    .tone-blocked {
        --tone: #E11D48;
        --tone-soft: rgba(225, 29, 72, .13);
        --tone-rgb: 225, 29, 72;
    }

    .tone-purple {
        --tone: #8B5CF6;
        --tone-soft: rgba(139, 92, 246, .13);
        --tone-rgb: 139, 92, 246;
    }

    .tone-blue {
        --tone: #2563EB;
        --tone-soft: rgba(37, 99, 235, .13);
        --tone-rgb: 37, 99, 235;
    }

    .tone-info {
        --tone: #0284C7;
        --tone-soft: rgba(2, 132, 199, .13);
        --tone-rgb: 2, 132, 199;
    }

    .tone-indigo {
        --tone: #6366F1;
        --tone-soft: rgba(99, 102, 241, .13);
        --tone-rgb: 99, 102, 241;
    }

    .tone-teal {
        --tone: #0D9488;
        --tone-soft: rgba(13, 148, 136, .13);
        --tone-rgb: 13, 148, 136;
    }

    /* Table Cards & Containers */
    .orb-table-card,
    .att-card {
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 24px;
        background: #fff;
        border: 1px solid var(--orb-border);
        box-shadow: var(--orb-shadow);
    }

    .orb-table-toolbar,
    .att-section-head {
        padding: 16px 20px;
        background: linear-gradient(180deg, #fff, #FAFBFF);
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
    }

    .att-section-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 16px;
        font-weight: 950;
        display: flex;
        gap: 9px;
        align-items: center;
    }

    .att-section-title i {
        color: var(--orb-primary);
    }

    .att-section-subtitle {
        font-size: 12.5px;
        color: var(--orb-muted);
        font-weight: 550;
        margin-top: 3px;
    }

    .att-search {
        width: 250px;
        height: 38px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        padding: 7px 13px;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        transition: all .2s ease;
        background: #fff;
    }

    .att-search:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 .15rem rgba(75, 0, 232, .12);
    }

    .orb-filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .orb-btn-light {
        background: #fff;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
        border-radius: 12px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none !important;
        transition: all .2s ease;
    }

    .orb-btn-light:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, .2);
    }

    /* General Table Typography & Borders */
    .att-table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0;
        margin-bottom: 0 !important;
    }

    .att-table thead th {
        background: #F8FAFC;
        color: #475467;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 13px 18px !important;
        border-top: none !important;
        border-bottom: 1px solid #EAECF0 !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .att-table td {
        background: #fff;
        border-bottom: 1px solid #EEF2F6 !important;
        padding: 13px 18px !important;
        vertical-align: middle !important;
    }

    .att-table tbody tr:hover td {
        background: #FCFAFF;
    }

    /* Specific Table Widths */
    #attendanceDataTable {
        min-width: 1120px;
    }

    #unmarkedAttendanceTable,
    #blockedAttendanceTable {
        min-width: 100% !important;
        width: 100% !important;
        table-layout: auto !important;
    }

    /* Employee Cell */
    .att-emp {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .att-avatar,
    .leave-avatar {
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        max-width: 38px !important;
        border-radius: 12px !important;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)) !important;
        color: #ffffff !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 900 !important;
        font-size: 14px !important;
        flex-shrink: 0 !important;
        overflow: hidden !important;
        text-transform: uppercase !important;
        box-shadow: 0 2px 8px rgba(75, 0, 232, 0.12) !important;
        line-height: 1 !important;
        position: relative !important;
    }

    .att-avatar img,
    .leave-avatar img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
        border-radius: inherit !important;
    }

    .att-avatar span,
    .leave-avatar span {
        width: 100%;
        height: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff !important;
        font-weight: 900 !important;
        font-size: 14px !important;
        text-transform: uppercase !important;
        line-height: 1;
    }

    .att-emp-name {
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .att-emp-code,
    .att-small {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 750;
    }

    .att-time {
        font-size: 12px;
        font-weight: 850;
        color: #344054;
        white-space: nowrap;
    }

    .att-strong {
        font-weight: 900;
        color: var(--orb-text);
    }

    .att-badge,
    .mode-badge,
    .flag-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        white-space: nowrap;
        font-weight: 950;
        text-transform: uppercase;
    }

    .att-badge {
        padding: 6px 10px;
        font-size: 10.5px;
    }

    .mode-badge {
        padding: 6px 10px;
        font-size: 10px;
    }

    .flag-badge {
        padding: 4px 8px;
        font-size: 9px;
        margin: 2px 3px 2px 0;
    }

    .badge-present {
        background: #DCFCE7;
        color: #166534;
    }

    .badge-absent {
        background: #FEE2E2;
        color: #991B1B;
    }

    .badge-half_day {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-lwp {
        background: #FEE2E2;
        color: #B42318;
    }

    .badge-leave {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .badge-week_off {
        background: #F1F5F9;
        color: #475569;
    }

    .badge-holiday {
        background: #EDE9FE;
        color: #5B21B6;
    }

    .badge-punch_blocked {
        background: #FFE4E6;
        color: #BE123C;
    }

    .badge-missed_punch {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .badge-default {
        background: #F1F5F9;
        color: #475569;
    }

    .mode-wfo {
        background: #EEF2FF;
        color: #3730A3;
    }

    .mode-wfh {
        background: #ECFEFF;
        color: #155E75;
    }

    .mode-default {
        background: #F1F5F9;
        color: #475569;
    }

    .flag-late {
        background: #FFF7ED;
        color: #C2410C;
    }

    .flag-early {
        background: #FEF2F2;
        color: #B42318;
    }

    .flag-block {
        background: #FFE4E6;
        color: #BE123C;
    }

    .flag-clear {
        background: #F1F5F9;
        color: #475569;
    }

    .flag-missed {
        background: #FEF3C7;
        color: #92400E;
    }

    .flag-unlock {
        background: #EDE9FE;
        color: #5B21B6;
    }

    .action-dot {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #475467;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
    }

    .action-dot:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .dropdown-menu.att-action-menu {
        border: 1px solid var(--orb-border);
        border-radius: 15px;
        box-shadow: 0 18px 45px rgba(16, 24, 40, .14);
        padding: 7px;
        min-width: 178px;
    }

    .att-action-menu .dropdown-item {
        border-radius: 11px;
        padding: 8px 10px;
        font-size: 13px;
        font-weight: 800;
        color: #344054;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .att-action-menu .dropdown-item:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .att-block-card {
        border-color: #FED7AA;
        background: radial-gradient(circle at top right, rgba(249, 115, 22, .08), transparent 24%), #fff;
    }

    .att-block-card .att-section-head {
        background: linear-gradient(135deg, #FFF7ED, #fff);
        border-bottom: 1px solid #FFEDD5;
    }

    .att-block-summary {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .att-mini-stat {
        padding: 6px 12px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #FED7AA;
        font-size: 12px;
        font-weight: 900;
        color: #9A3412;
        display: inline-flex;
        align-items: center;
    }

    /* Exact vendor.pagination.orbo Pagination Styles */
    .orb-pagination-wrapper {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
        width: 100% !important;
        padding: 12px 18px 14px !important;
        background: #FFFFFF !important;
        border-top: 1px solid #EEF2F6 !important;
        box-sizing: border-box !important;
    }

    .orb-pagination-info,
    .dataTables_info {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-muted, #6B7280) !important;
        white-space: nowrap !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .orb-pagination-nav,
    .dataTables_paginate {
        display: inline-flex !important;
        align-items: center !important;
    }

    .dataTables_paginate ul.pagination {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        flex-wrap: wrap !important;
    }

    .dataTables_paginate ul.pagination .page-item {
        margin: 0 !important;
        border: none !important;
        background: transparent !important;
    }

    .dataTables_paginate ul.pagination .page-item .page-link,
    .dataTables_paginate .paginate_button {
        height: 34px !important;
        min-width: 34px !important;
        padding: 0 12px !important;
        border-radius: 9px !important;
        border: 1px solid transparent !important;
        background: transparent !important;
        color: var(--orb-primary, #4B00E8) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.18s ease !important;
        box-shadow: none !important;
        text-decoration: none !important;
        outline: none !important;
    }

    .dataTables_paginate ul.pagination .page-item:not(.active):not(.disabled) .page-link:hover,
    .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: var(--orb-soft, #F3EDFF) !important;
        color: var(--orb-primary, #4B00E8) !important;
        border-color: transparent !important;
    }

    .dataTables_paginate ul.pagination .page-item.active .page-link,
    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)) !important;
        color: #ffffff !important;
        border: none !important;
        cursor: default !important;
    }

    .dataTables_paginate ul.pagination .page-item.disabled .page-link,
    .dataTables_paginate .paginate_button.disabled,
    .dataTables_paginate .paginate_button.disabled:hover {
        background: transparent !important;
        color: #94A3B8 !important;
        border-color: transparent !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        opacity: 0.6 !important;
    }

    @media (max-width: 991px) {
        .att-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 22px 20px;
            gap: 16px;
        }

        .att-hero-subtitle {
            max-width: 100%;
        }

        .orb-table-toolbar {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 14px;
        }

        .orb-filter-group {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
    }

    @media (max-width: 768px) {
        .att-page {
            padding: 12px 10px 28px;
        }

        .att-hero {
            padding: 18px 16px;
            border-radius: 18px;
            gap: 14px;
        }

        .att-hero-title {
            font-size: 22px;
        }

        .att-hero-subtitle {
            font-size: 12.5px;
            line-height: 1.45;
        }

        .att-hero-actions {
            width: 100%;
        }

        .att-hero-actions .att-btn {
            width: 100%;
            justify-content: center;
        }

        .orb-table-toolbar {
            padding: 14px 14px !important;
        }

        .att-search {
            width: 100% !important;
        }

        .orb-filter-group {
            width: 100%;
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px;
        }

        .orb-filter-group a.orb-btn-light {
            width: 100%;
            justify-content: center;
        }

        .att-block-summary {
            width: 100%;
            flex-direction: column;
            align-items: stretch !important;
        }

        .att-mini-stat {
            justify-content: center;
        }

        .orb-pagination-wrapper {
            flex-direction: column;
            align-items: center !important;
            text-align: center;
            gap: 10px !important;
            padding: 10px 12px !important;
        }

        .orb-pagination-nav {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .att-kpi {
            min-height: 86px;
            padding: 10px 12px;
            border-radius: 14px;
        }

        .att-kpi-icon {
            width: 30px;
            height: 30px;
            font-size: 12px;
            border-radius: 9px;
        }

        .att-kpi-value {
            font-size: 22px;
        }

        .att-kpi-label {
            font-size: 10px;
            margin-top: 6px;
        }
    /* Compact Select2 for Table Length Entries */
    .select2-container--per-page.select2-container--default .select2-selection--single {
        min-height: 38px !important;
        height: 38px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        border-radius: 10px !important;
        padding: 0 6px !important;
        width: 75px !important;
        background-color: #fff !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--per-page.select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 13px !important;
        font-weight: 750 !important;
        color: var(--orb-text, #101828) !important;
        padding-left: 4px !important;
    }
    .select2-container--per-page.select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 6px !important;
    }
    .select2-dropdown-per-page {
        min-width: 75px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        border-radius: 10px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        z-index: 99999 !important;
    }
    .select2-dropdown-per-page .select2-results__option {
        padding: 6px 10px !important;
        text-align: center !important;
    }
    .select2-dropdown-per-page .select2-results__option--highlighted[aria-selected] {
        background-color: var(--orb-primary, #4B00E8) !important;
        color: #ffffff !important;
    }

</style>
