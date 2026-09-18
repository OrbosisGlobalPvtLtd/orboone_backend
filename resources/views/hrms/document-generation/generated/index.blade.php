    @extends('layouts.panel', ['active' => 'document_generation'])

    @section('page_title', 'Generated Documents')

    @section('_head')
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
            background: var(--orb-bg);
            padding: 20px 16px 40px;
        }

        .att-container {
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
        }

        /* PREMIUM HERO HEADER */
        .att-hero {
            background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
            border-radius: 24px;
            padding: 28px 30px;
            margin-bottom: 18px;
            box-shadow: 0 18px 45px rgba(75, 0, 232, .20);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .att-hero::before {
            content: "";
            position: absolute;
            right: -60px;
            top: -90px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .att-hero-content {
            position: relative;
            z-index: 2;
            min-width: 0;
        }

        .att-kicker {
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            opacity: .95;
            margin-bottom: 8px;
            display: flex;
            gap: 8px;
            align-items: center;
            color: #E0E7FF;
        }

        .att-title {
            font-size: clamp(22px, 3.5vw, 30px);
            font-weight: 900;
            margin: 0;
            line-height: 1.18;
            color: #fff;
            letter-spacing: -0.02em;
        }

        .att-subtitle {
            font-size: clamp(12.5px, 1.5vw, 14px);
            font-weight: 500;
            margin-top: 8px;
            opacity: .92;
            max-width: 800px;
            color: #F3E8FF;
            line-height: 1.4;
        }

        .att-hero-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .btn-action {
            border: 0;
            border-radius: 12px;
            padding: 10px 18px;
            font-weight: 700;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none !important;
            white-space: nowrap;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .btn-action-primary,
        .btn-create-doc {
            background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25);
            border: none;
            border-radius: 10px;
        }

        .btn-action-primary:hover,
        .btn-create-doc:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(75, 0, 232, 0.35);
            color: #fff !important;
        }

        .btn-action-outline {
            background: rgba(255, 255, 255, 0.14);
            color: #fff !important;
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(8px);
        }

        .btn-action-outline:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-1px);
        }

        /* TOP STATISTICS GRID */
        .document-metric-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .dm-kpi {
            min-height: 94px;
            padding: 16px 18px 14px;
            border-radius: 18px;
            border: 1px solid var(--orb-border);
            background: #fff;
            box-shadow: 0 4px 16px rgba(16, 24, 40, .04);
            position: relative;
            overflow: hidden;
            transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .dm-kpi:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(16, 24, 40, .08);
            border-color: #CBD5E1;
        }

        .dm-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            position: relative;
            z-index: 1;
        }

        .dm-kpi-value {
            font-size: clamp(22px, 2.8vw, 26px);
            line-height: 1;
            font-weight: 950;
            color: var(--orb-text);
            letter-spacing: -0.02em;
        }

        .dm-kpi-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--tone-soft) !important;
            color: var(--tone) !important;
            font-size: 15px;
            flex-shrink: 0;
        }

        .dm-kpi-label {
            margin-top: 12px;
            font-size: 11px;
            color: var(--orb-muted);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
            z-index: 1;
        }

        .dm-kpi-line {
            position: absolute;
            left: 16px;
            right: 16px;
            bottom: 0;
            height: 3px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--tone), transparent);
        }

        .tone-purple {
            --tone: #7A5AF8;
            --tone-soft: rgba(122, 90, 248, .13);
        }

        .tone-warning {
            --tone: #F79009;
            --tone-soft: rgba(247, 144, 9, .14);
        }

        .tone-success {
            --tone: #12B76A;
            --tone-soft: rgba(18, 183, 106, .12);
        }

        .tone-danger {
            --tone: #F04438;
            --tone-soft: rgba(240, 68, 56, .12);
        }

        .tone-info {
            --tone: #0EA5E9;
            --tone-soft: rgba(14, 165, 233, .13);
        }

        .tone-orange {
            --tone: #EA580C;
            --tone-soft: rgba(234, 88, 12, .13);
        }

        /* CARD STRUCTURE */
        .att-card {
            background: #fff;
            border: 1px solid var(--orb-border);
            border-radius: 20px;
            box-shadow: var(--orb-shadow);
            overflow: hidden;
        }

        .att-section-head {
            padding: 18px 24px;
            border-bottom: 1px solid var(--orb-border);
            background: linear-gradient(180deg, #FFFFFF 0%, #FAFAFD 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .att-section-title {
            font-size: clamp(16px, 2vw, 18px);
            font-weight: 800;
            color: var(--orb-text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .att-section-title i {
            color: var(--orb-primary);
        }

        .att-section-sub {
            font-size: 12.5px;
            color: var(--orb-muted);
            font-weight: 500;
            margin-top: 3px;
        }

        .att-head-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .att-head-btn {
            height: 38px;
            border-radius: 10px;
            border: 1px solid #CBD5E1;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            padding: 0 14px;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none !important;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .att-head-btn:hover {
            background: #F8FAFC;
            color: var(--orb-text);
            border-color: #94A3B8;
        }

        .att-total-pill {
            border: 1px solid rgba(79, 70, 229, 0.2);
            background: rgba(79, 70, 229, 0.06);
            color: var(--orb-primary);
            border-radius: 10px;
            padding: 0 14px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12.5px;
            font-weight: 800;
            white-space: nowrap;
        }

        /* FILTER SECTION */
        .att-filter-panel {
            padding: 18px 24px;
            background: #FAFAFD;
            border-bottom: 1px solid var(--orb-border);
        }

        .doc-filter-grid {
            display: grid;
            grid-template-columns: minmax(180px, 1.4fr) minmax(160px, 1.4fr) minmax(160px, 1.4fr) minmax(130px, 1.1fr) minmax(240px, 2fr) minmax(130px, 1.1fr);
            gap: 12px;
            align-items: flex-end;
        }

        .att-filter-group {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .att-filter-group label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748B;
            margin-bottom: 6px;
            letter-spacing: 0.05em;
        }

        .att-filter-group .form-control,
        .att-filter-group .form-select {
            height: 40px;
            border-radius: 10px;
            border: 1px solid #CBD5E1;
            font-size: 13px;
            font-weight: 500;
            color: var(--orb-text);
            padding: 0 12px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
            background-color: #fff;
            transition: all 0.2s ease;
            width: 100%;
            min-width: 0;
        }

        .att-filter-group .form-control:focus,
        .att-filter-group .form-select:focus {
            border-color: var(--orb-primary);
            box-shadow: 0 0 0 3.5px rgba(79, 70, 229, 0.12) !important;
            background-color: #fff;
        }

        .btn-filter-submit {
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
            border: none;
            color: #fff !important;
            font-weight: 700;
            padding: 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            font-size: 13px;
            flex: 1;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            white-space: nowrap;
            box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25);
        }

        .btn-filter-submit:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(75, 0, 232, 0.35);
            color: #fff !important;
        }

        .btn-filter-reset-icon {
            height: 40px;
            width: 40px;
            border-radius: 10px;
            border: 1px solid #CBD5E1;
            background: #fff;
            color: #64748B;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .btn-filter-reset-icon:hover {
            background: #F1F5F9;
            color: var(--orb-text);
        }

        /* TABLE LAYOUT */
        .att-table-wrap {
            padding: 0;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .att-table {
            width: 100% !important;
            min-width: 820px;
            border-collapse: separate !important;
            border-spacing: 0;
            margin: 0 !important;
        }

        .att-table thead th {
            background: #f8fafc !important;
            color: #475569 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            padding: 14px 18px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            letter-spacing: 0.05em;
            vertical-align: middle !important;
            white-space: nowrap;
        }

        .att-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .att-table tbody tr:hover td {
            background: #f8fafc !important;
        }

        .att-table tbody td {
            background: #fff;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 14px 18px !important;
            vertical-align: middle !important;
            color: var(--orb-text);
        }

        /* TYPOGRAPHY */
        .doc-name {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--orb-text);
            text-decoration: none !important;
            display: inline-block;
        }

        .doc-name:hover {
            color: var(--orb-primary);
        }

        .doc-num {
            font-size: 11px;
            color: var(--orb-muted);
            margin-top: 2px;
            font-family: monospace;
        }

        .doc-ver {
            display: inline-flex;
            align-items: center;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 10px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 4px;
            margin-top: 4px;
        }

        /* RECIPIENT CARD */
        .recipient-block {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .recipient-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4f46e5;
            font-weight: 700;
            font-size: 12.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e0e7ff;
        }

        .recipient-name {
            font-weight: 700;
            font-size: 13px;
            color: var(--orb-text);
            white-space: nowrap;
        }

        .recipient-meta {
            font-size: 11px;
            color: var(--orb-muted);
            margin-top: 2px;
            white-space: nowrap;
        }

        /* PREMIUM BADGES */
        .badge-doc-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-employee-doc {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }

        .badge-manual-doc {
            background: #fef8ec;
            color: #b45309;
            border: 1px solid #fef3c7;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-generated {
            background: #e0f2fe;
            color: #0369a1;
        }

        .status-sent {
            background: #dcfce7;
            color: #166534;
        }

        .status-viewed {
            background: #faf5ff;
            color: #6b21a8;
        }

        .status-downloaded {
            background: #e0f2fe;
            color: #0369a1;
        }

        .status-expired {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-draft {
            background: #f1f5f9;
            color: #475569;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ROW DATE */
        .date-main {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--orb-text);
            white-space: nowrap;
        }

        .date-sub {
            font-size: 11px;
            color: var(--orb-muted);
            margin-top: 2px;
            white-space: nowrap;
        }

        /* ACTIONS & BUTTONS */
        .btn-action-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            transition: all 0.2s ease;
            font-size: 12.5px;
            flex-shrink: 0;
        }

        .btn-action-icon:hover {
            background: #f1f5f9;
            color: var(--orb-primary);
            border-color: #cbd5e1;
        }

        .btn-action-icon-eye:hover {
            background: #e0f2fe;
            color: #0284c7;
            border-color: #bae6fd;
        }

        .btn-action-icon-download:hover {
            background: #e2fbf0;
            color: #059669;
            border-color: #a7f3d0;
        }

        .btn-action-icon-envelope:hover {
            background: #fff7ed;
            color: #ea580c;
            border-color: #ffedd5;
        }

        .btn-more-menu {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .btn-more-menu:hover {
            background: #f1f5f9;
            color: var(--orb-text);
        }

        .dropdown-menu.att-action-menu {
            border: 1px solid var(--orb-border);
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            padding: 6px;
            min-width: 200px;
        }

        .att-action-menu .dropdown-item {
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #334155;
            border: none;
        }

        .att-action-menu .dropdown-item:hover {
            background: #f1f5f9;
            color: var(--orb-primary);
        }

        .att-action-menu .dropdown-item.text-danger:hover {
            background: #fef2f2;
            color: #dc2626 !important;
        }

        /* EMPTY STATE */
        .empty-state-container {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-state-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
            border: 4px solid #fff;
            box-shadow: 0 0 0 4px #f1f5f9;
        }

        .empty-state-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--orb-text);
            margin-bottom: 6px;
        }

        .empty-state-text {
            font-size: 13.5px;
            color: var(--orb-muted);
            max-width: 360px;
            margin: 0 auto 20px;
        }

        /* AUDIT TIMELINE */
        .timeline {
            position: relative;
            padding-left: 8px;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 16px;
            top: 32px;
            bottom: -16px;
            width: 2px;
            background: #E2E8F0;
        }

        .no-caret::after {
            display: none !important;
        }

        /* ==========================================================================
           RESPONSIVE BREAKPOINTS & MOBILE OPTIMIZATIONS
           ========================================================================== */

        @media(max-width: 1400px) {
            .document-metric-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .doc-filter-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media(max-width: 991.98px) {
            .att-page {
                padding: 16px 12px 36px;
            }

            .att-hero {
                flex-direction: column;
                align-items: flex-start;
                padding: 22px 20px;
                border-radius: 18px;
                gap: 16px;
            }

            .att-hero-actions {
                width: 100%;
            }

            .btn-action {
                flex: 1 1 auto;
            }

            .document-metric-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
            }

            .doc-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .att-section-head {
                padding: 16px 20px;
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
            }

            .att-head-badges {
                width: 100%;
                justify-content: flex-start;
            }

            .att-filter-panel {
                padding: 16px 20px;
            }
        }

        @media(max-width: 767.98px) {
            .att-page {
                padding: 12px 8px 30px;
            }

            .att-hero {
                padding: 18px 16px;
                border-radius: 16px;
                margin-bottom: 14px;
            }

            .att-hero-actions {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                width: 100%;
            }

            .btn-action {
                flex: 1 1 calc(50% - 6px);
                padding: 9px 12px;
                font-size: 12px;
            }

            .document-metric-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
                margin-bottom: 14px;
            }

            .att-metric {
                padding: 12px 14px;
                border-radius: 14px;
                min-height: auto;
            }

            .att-metric-value {
                font-size: 20px;
            }

            .att-metric-icon {
                width: 32px;
                height: 32px;
                font-size: 13px;
                border-radius: 10px;
            }

            .att-metric-bottom {
                margin-top: 8px;
            }

            .att-metric-label {
                font-size: 10.5px;
                letter-spacing: 0.02em;
            }

            .att-metric-subtext {
                font-size: 10px;
            }

            .att-card {
                border-radius: 16px;
            }

            .att-section-head {
                padding: 14px 16px;
                gap: 12px;
            }

            .att-head-badges {
                display: flex;
                gap: 6px;
                flex-wrap: wrap;
                width: 100%;
            }

            .att-head-btn,
            .att-total-pill {
                flex: 1 1 calc(50% - 4px);
                height: 36px;
                font-size: 12px;
                padding: 0 10px;
                margin: 0 !important;
            }

            .att-filter-panel {
                padding: 14px 16px 18px;
            }

            .doc-filter-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .att-filter-group .form-control,
            .att-filter-group .form-select {
                height: 38px;
                font-size: 12.5px;
            }

            .att-table thead th {
                padding: 10px 14px !important;
                font-size: 10.5px !important;
            }

            .att-table tbody td {
                padding: 12px 14px !important;
                font-size: 12.5px;
            }

            .card-footer {
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                text-align: center;
                gap: 10px !important;
                padding: 12px 16px !important;
            }
        }

        @media(max-width: 480px) {
            .att-page {
                padding: 8px 6px 24px;
            }

            .att-hero {
                padding: 14px 12px;
                border-radius: 14px;
            }

            .att-kicker {
                font-size: 10.5px;
            }

            .att-title {
                font-size: 20px;
            }

            .att-subtitle {
                font-size: 12px;
                margin-top: 4px;
            }

            .btn-action {
                flex: 1 1 100%;
                padding: 9px 12px;
                font-size: 12px;
            }

            .att-head-btn,
            .att-total-pill {
                flex: 1 1 100%;
            }

            .modal-dialog {
                margin: 10px auto;
                max-width: calc(100% - 16px);
            }

            .modal-header {
                padding: 16px 18px !important;
            }

            .modal-body {
                padding: 16px 18px !important;
            }

            .modal-footer {
                padding: 12px 18px 18px !important;
            }
        }

        @media(max-width: 340px) {
            .document-metric-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @endsection

    @section('_content')
    <div class="att-page">
        <div class="att-container">

            <!-- HEADER SECTION -->
            <div class="att-hero">
                <div class="att-hero-content">
                    <div class="att-kicker">
                        <i class="fas fa-file-invoice"></i> HRMS • Document Generation
                    </div>
                    <h3 class="att-title">Generated Documents</h3>
                    <div class="att-subtitle">
                        Manage, track, preview, download and email generated HR documents.
                    </div>
                </div>
                <div class="att-hero-actions">
                    {{-- <button type="button" onclick="exportTableToCSV('generated_documents_export.csv')" class="btn-action btn-action-outline">
                        <i class="fas fa-file-export"></i> Export Documents
                    </button> --}}
                    <button type="button" onclick="window.location.reload();" class="btn-action btn-action-outline">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    {{-- @if(Route::has('hrms.document-generation.generated.create'))
                    <a href="{{ route('hrms.document-generation.generated.create') }}" class="btn-action btn-action-primary">
                        <i class="fas fa-plus-circle"></i> Create Document
                    </a>
                    @endif --}}
                </div>
            </div>

            <!-- TOP STATISTICS CARDS (DOCUMENT VERIFICATION KPI DESIGN) -->
            <div class="document-metric-grid">
                <!-- Total Documents -->
                <div class="dm-kpi tone-purple">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-value">{{ $totalDocuments ?? 0 }}</div>
                        <div class="dm-kpi-icon"><i class="fas fa-file-alt"></i></div>
                    </div>
                    <div class="dm-kpi-label">Total Documents</div>
                    <div class="dm-kpi-line"></div>
                </div>

                <!-- Generated Today -->
                <div class="dm-kpi tone-success">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-value">{{ $generatedToday ?? 0 }}</div>
                        <div class="dm-kpi-icon"><i class="fas fa-calendar-day"></i></div>
                    </div>
                    <div class="dm-kpi-label">Generated Today</div>
                    <div class="dm-kpi-line"></div>
                </div>

                <!-- Employee Documents -->
                <div class="dm-kpi tone-info">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-value">{{ $employeeDocuments ?? 0 }}</div>
                        <div class="dm-kpi-icon"><i class="fas fa-user-tie"></i></div>
                    </div>
                    <div class="dm-kpi-label">Employee Documents</div>
                    <div class="dm-kpi-line"></div>
                </div>

                <!-- Manual Documents -->
                <div class="dm-kpi tone-warning">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-value">{{ $manualDocuments ?? 0 }}</div>
                        <div class="dm-kpi-icon"><i class="fas fa-file-signature"></i></div>
                    </div>
                    <div class="dm-kpi-label">Manual Documents</div>
                    <div class="dm-kpi-line"></div>
                </div>

                <!-- Emailed Documents -->
                <div class="dm-kpi tone-orange">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-value">{{ $emailedDocuments ?? 0 }}</div>
                        <div class="dm-kpi-icon"><i class="fas fa-paper-plane"></i></div>
                    </div>
                    <div class="dm-kpi-label">Emailed Documents</div>
                    <div class="dm-kpi-line"></div>
                </div>
            </div>

            <!-- TABLE SECTION CARD -->
            <div class="att-card document-generated-table-card">
                <div class="att-section-head">
                    <div>
                        <h3 class="att-section-title">
                            <i class="fas fa-file-invoice"></i> Generated Documents List
                        </h3>
                        <p class="att-section-sub">Filters are attached with this table. Select criteria and click Search.</p>
                    </div>
                    <div class="att-head-badges">
                        {{-- <a href="{{ route('hrms.document-generation.generated.index') }}" class="att-head-btn">
                            <i class="fas fa-undo"></i> Reset Filters
                        </a> --}}
                        {{-- <button type="button" onclick="exportTableToCSV('documents_filter_results.csv')" class="att-head-btn">
                            <i class="fas fa-file-csv"></i> Export Results
                        </button>
                        <span class="att-total-pill">Total: {{ $documents->total() }}</span> --}}
                        @if(Route::has('hrms.document-generation.generated.create'))
                    <a href="{{ route('hrms.document-generation.generated.create') }}" class="btn-action btn-action-primary">
                        <i class="fas fa-plus-circle"></i> Create Document
                    </a>
                    @endif
                    </div>
                </div>

                <!-- FILTER SECTION -->
                <div class="att-filter-panel">
                    <form id="filterForm" method="GET" action="{{ route('hrms.document-generation.generated.index') }}">
                        <div class="doc-filter-grid">
                            <!-- Search Documents -->
                            <div class="att-filter-group">
                                <label>Search Documents</label>
                                <input type="text" name="search" id="filterSearch" class="form-control" placeholder="Search number, employee..." value="{{ request('search') }}">
                            </div>

                            <!-- Employee Filter -->
                            <div class="att-filter-group">
                                <label>Employee</label>
                                <select name="employee_id" id="filterEmployee" class="form-select select2-searchable">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->display_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Document Type Filter -->
                            <div class="att-filter-group">
                                <label>Document Type</label>
                                <select name="document_type" id="filterDocType" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="offer_letter" {{ request('document_type') == 'offer_letter' ? 'selected' : '' }}>Offer Letter</option>
                                    <option value="appointment_letter" {{ request('document_type') == 'appointment_letter' ? 'selected' : '' }}>Appointment Letter</option>
                                    <option value="internship_offer_letter" {{ request('document_type') == 'internship_offer_letter' ? 'selected' : '' }}>Internship Offer Letter</option>
                                    <option value="discontinuing_letter" {{ request('document_type') == 'discontinuing_letter' ? 'selected' : '' }}>Discontinuing Letter</option>
                                    <option value="experience_letter" {{ request('document_type') == 'experience_letter' ? 'selected' : '' }}>Experience Letter</option>
                                    <option value="relieving_letter" {{ request('document_type') == 'relieving_letter' ? 'selected' : '' }}>Relieving Letter</option>
                                    <option value="internship_certificate" {{ request('document_type') == 'internship_certificate' ? 'selected' : '' }}>Internship Certificate</option>
                                    <option value="salary_certificate" {{ request('document_type') == 'salary_certificate' ? 'selected' : '' }}>Salary Certificate</option>
                                    <option value="warning_letter" {{ request('document_type') == 'warning_letter' ? 'selected' : '' }}>Warning Letter</option>
                                    <option value="appreciation_letter" {{ request('document_type') == 'appreciation_letter' ? 'selected' : '' }}>Appreciation Letter</option>
                                    <option value="nda_agreement" {{ request('document_type') == 'nda_agreement' ? 'selected' : '' }}>NDA / Agreement</option>
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="att-filter-group">
                                <label>Status</label>
                                <select name="status" id="filterStatus" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="generated" {{ request('status') == 'generated' ? 'selected' : '' }}>Generated</option>
                                    <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <!-- Date Range Filters -->
                            <div class="att-filter-group">
                                <label>Date Range</label>
                                <div class="d-flex align-items-center" style="gap: 6px;">
                                    <input type="date" name="start_date" id="filterStartDate" class="form-control" value="{{ request('start_date') }}" style="font-size: 11.5px; padding: 0 6px; flex: 1; min-width: 0;">
                                    <span class="text-muted small font-weight-bold" style="flex-shrink: 0;">to</span>
                                    <input type="date" name="end_date" id="filterEndDate" class="form-control" value="{{ request('end_date') }}" style="font-size: 11.5px; padding: 0 6px; flex: 1; min-width: 0;">
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="att-filter-group">
                                <label class="d-none d-md-block">&nbsp;</label>
                                <div class="d-flex align-items-center" style="gap: 6px;">
                                    <button type="submit" class="btn-filter-submit shadow-sm">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <a href="{{ route('hrms.document-generation.generated.index') }}" class="btn-filter-reset-icon" title="Reset Filters">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TABLE -->
                <div class="att-table-wrap table-responsive">
                    <table class="table att-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">S.No</th>
                                <th>Document</th>
                                <th>Recipient</th>
                                <th>Document Type</th>
                                <th>Status</th>
                                <th>Generated Date</th>
                                <th class="text-right" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $doc)
                            @php
                            $pdfExists = false;
                            if ($doc->generated_pdf_path) {
                            $pdfExists = \Illuminate\Support\Facades\Storage::disk('private')->exists($doc->generated_pdf_path);
                            } elseif ($doc->pdf_path) {
                            $pdfExists = \Illuminate\Support\Facades\Storage::disk('private')->exists($doc->pdf_path);
                            }
                            $isHtml = ($doc->template_type ?? ($doc->template->template_type ?? 'html')) !== 'docx';
                            $version = intval($doc->template_version ?? 1);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <!-- Document Column -->
                                <td>
                                    <a href="{{ route('hrms.document-generation.generated.show', $doc->id) }}" class="doc-name">
                                        {{ $doc->template->name ?? ucwords(str_replace('_', ' ', $doc->document_type)) }}
                                    </a>
                                    <div class="doc-num">{{ $doc->document_number }}</div>
                                    @if($version > 1)
                                    <div class="doc-ver">Version {{ $version }}</div>
                                    @endif
                                </td>

                                <!-- Recipient Column -->
                                <td>
                                    @if($doc->employee)
                                    @php
                                    $initials = collect(explode(' ', $doc->employee->display_name))
                                    ->map(fn($n) => mb_substr($n, 0, 1))
                                    ->take(2)
                                    ->join('');
                                    @endphp
                                    <div class="recipient-block">
                                        <div class="recipient-avatar">{{ $initials }}</div>
                                        <div>
                                            <div class="recipient-name">{{ $doc->employee->display_name }}</div>
                                            <div class="recipient-meta">{{ $doc->employee->employee_code }} • {{ $doc->employee->designation->name ?? 'Staff' }}</div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="recipient-block">
                                        <div class="recipient-avatar" style="background: #fff7ed; color: #ea580c; box-shadow: 0 0 0 2px #ffedd5;"><i class="fas fa-user"></i></div>
                                        <div>
                                            <div class="recipient-name">{{ $doc->candidate_name ?: 'Candidate' }}</div>
                                            <div class="recipient-meta"><span class="badge badge-manual-doc" style="font-size: 9px; padding: 2px 6px;">Manual Document</span></div>
                                        </div>
                                    </div>
                                    @endif
                                </td>

                                <!-- Document Type Column -->
                                <td>
                                    @if($doc->employee)
                                    <span class="badge-doc-type badge-employee-doc">
                                        <i class="fas fa-id-card"></i> Employee Document
                                    </span>
                                    @else
                                    <span class="badge-doc-type badge-manual-doc">
                                        <i class="fas fa-file-signature"></i> Manual Document
                                    </span>
                                    @endif
                                </td>

                                <!-- Status Column -->
                                <td>
                                    @if($doc->status == 'sent')
                                    <span class="badge-status status-sent"><i class="fas fa-paper-plane mr-1"></i> Sent</span>
                                    @elseif($doc->status == 'reviewed')
                                    <span class="badge-status status-viewed"><i class="fas fa-check-double mr-1"></i> Reviewed</span>
                                    @elseif($doc->status == 'generated')
                                    <span class="badge-status status-generated"><i class="fas fa-file-alt mr-1"></i> Generated</span>
                                    @elseif($doc->status == 'cancelled')
                                    <span class="badge-status status-cancelled"><i class="fas fa-ban mr-1"></i> Cancelled</span>
                                    @elseif($doc->status == 'draft')
                                    <span class="badge-status status-draft"><i class="fas fa-edit mr-1"></i> Draft</span>
                                    @else
                                    <span class="badge-status status-draft"><i class="fas fa-circle mr-1"></i> {{ ucfirst($doc->status) }}</span>
                                    @endif
                                </td>

                                <!-- Generated Date Column -->
                                <td>
                                    <div class="date-main">{{ $doc->created_at->format('d M Y') }}</div>
                                    <div class="date-sub">{{ $doc->created_at->format('h:i A') }}</div>
                                </td>

                                <!-- Actions Column -->
                                <td class="text-right">
                                    @php
                                    $hasPreview = auth()->user() && auth()->user()->hasPermission('document_generation.preview');
                                    $hasDownload = auth()->user() && auth()->user()->hasPermission('document_generation.download');
                                    $hasEmail = auth()->user() && auth()->user()->hasPermission('document_generation.email');
                                    $hasView = auth()->user() && auth()->user()->hasPermission('document_generation.view');
                                    $hasGenerate = auth()->user() && auth()->user()->hasPermission('document_generation.generate');
                                    $hasDelete = auth()->user() && auth()->user()->hasPermission('document_generation.delete');

                                    $anyDropdownAction = ($hasPreview && $pdfExists) ||
                                    ($hasEmail && $pdfExists) ||
                                    $hasView ||
                                    ($hasGenerate && $isHtml) ||
                                    $hasDelete;
                                    @endphp
                                    <div class="d-flex align-items-center justify-content-end" style="gap: 6px;">
                                        <!-- Preview Button -->
                                        @if($hasPreview)
                                        @if($pdfExists)
                                        <a href="{{ route('hrms.document-generation.generated.stream', $doc->id) }}" target="_blank" class="btn-action-icon btn-action-icon-eye" title="Preview Document">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @else
                                        <button class="btn-action-icon" disabled title="PDF Missing" style="opacity: 0.4; cursor: not-allowed;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @endif
                                        @endif

                                        <!-- Download Button -->
                                        @if($hasDownload)
                                        @if($pdfExists)
                                        <a href="{{ route('hrms.document-generation.generated.download', $doc->id) }}" class="btn-action-icon btn-action-icon-download" title="Download Document">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @else
                                        <button class="btn-action-icon" disabled title="PDF Missing" style="opacity: 0.4; cursor: not-allowed;">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        @endif
                                        @endif

                                        <!-- Email Button -->
                                        @if($hasEmail)
                                        @if($pdfExists)
                                        <button type="button" onclick="openEmailModal({{ $doc->id }});" class="btn-action-icon btn-action-icon-envelope" title="Email Document">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        @else
                                        <button class="btn-action-icon" disabled title="PDF Missing" style="opacity: 0.4; cursor: not-allowed;">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        @endif
                                        @endif

                                        <!-- More Dropdown -->
                                        <div class="dropdown">
                                            <button type="button" class="btn-more-menu dropdown-toggle no-caret" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="More Actions">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right document-action-menu att-action-menu">
                                                @if($anyDropdownAction)
                                                @if($hasPreview)
                                                @if($pdfExists)
                                                <!-- <a class="dropdown-item" href="#" onclick="previewPdf('{{ route('hrms.document-generation.generated.stream', $doc->id) }}'); return false;">
                                                    <i class="fas fa-window-restore text-primary" style="width: 16px;"></i> Modal Preview
                                                </a> -->
                                                @endif
                                                @endif

                                                @if($hasView)
                                                <a class="dropdown-item" href="#" onclick="viewAuditLogs({{ $doc->id }}, {{ json_encode($doc->logs->map(function($log) {
                                                        return [
                                                            'action' => ucwords(str_replace('_', ' ', $log->action)),
                                                            'remarks' => $log->remarks ?? 'No remarks provided',
                                                            'actor' => $log->actor->name ?? 'System',
                                                            'date' => $log->created_at->format('d M Y, h:i A')
                                                        ];
                                                    })) }}); return false;">
                                                    <i class="fas fa-history text-info" style="width: 16px;"></i> Audit Log / History
                                                </a>
                                                @endif

                                                @if($hasGenerate && $isHtml)
                                                <form action="{{ route('hrms.document-generation.generated.regenerate', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Regenerate PDF? This will overwrite the current version.');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success border-0 bg-transparent text-left w-100">
                                                        <i class="fas fa-sync-alt" style="width: 16px;"></i> Regenerate PDF
                                                    </button>
                                                </form>
                                                @endif

                                                @if($hasDelete)
                                                <div class="dropdown-divider"></div>
                                                @if($doc->status != 'cancelled')
                                                <form action="{{ route('hrms.document-generation.generated.cancel', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this document?');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-warning border-0 bg-transparent text-left w-100">
                                                        <i class="fas fa-ban" style="width: 16px;"></i> Cancel Document
                                                    </button>
                                                </form>
                                                @endif

                                                <form action="{{ route('hrms.document-generation.generated.delete', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this document? This will soft-delete it.');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-danger border-0 bg-transparent text-left w-100">
                                                        <i class="fas fa-trash-alt" style="width: 16px;"></i> Delete Document
                                                    </button>
                                                </form>
                                                @endif
                                                @else
                                                <a class="dropdown-item disabled text-muted" href="#" onclick="return false;">No actions available</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-0">
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-folder-open"></i>
                                        </div>
                                        <div class="empty-state-title">No Documents Found</div>
                                        <div class="empty-state-text">Generate your first document to get started.</div>
                                        @if(Route::has('hrms.document-generation.generated.create'))
                                        <a href="{{ route('hrms.document-generation.generated.create') }}" class="btn btn-primary rounded-pill px-4" style="background: var(--orb-primary); border: none;">
                                            <i class="fas fa-plus-circle mr-1"></i> Generate Document
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                @if($documents->hasPages())
                <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-muted small">
                        Showing {{ $documents->firstItem() }}–{{ $documents->lastItem() }} of {{ $documents->total() }} documents
                    </div>
                    <div>
                        {{ $documents->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- EMAIL MODAL -->
    <div class="modal fade" id="emailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header d-flex flex-column align-items-start position-relative" style="background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); padding: 24px 30px; border: none;">
                    <h4 class="modal-title fw-bold text-white m-0" style="font-size: 20px;"><i class="fas fa-envelope me-2"></i> Send Email</h4>
                    <p class="text-white-50 m-0 mt-1" style="font-size: 13px; opacity: 0.85;">Send this document as a PDF attachment to the recipient.</p>
                    <button type="button" class="close text-white position-absolute" data-dismiss="modal" aria-label="Close" style="font-size: 28px; right: 24px; top: 20px; opacity: 0.8; background: none; border: none;">&times;</button>
                </div>
                <form id="emailForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Recipient Email</label>
                            <input type="email" name="email_to" class="form-control" style="height: 44px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 13.5px; font-weight: 600;" required placeholder="employee@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Subject</label>
                            <input type="text" name="email_subject" class="form-control" style="height: 44px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 13.5px; font-weight: 600;" required value="HR Document from {{ branding_name() }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Message Body</label>
                            <textarea name="email_body" class="form-control" rows="4" style="border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 13.5px; font-weight: 600;" required>Please find your attached HR document.</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" style="font-weight: 700; font-size: 13px; height: 42px; border: 1.5px solid #cbd5e1; background: #fff;" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 text-white" style="background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border: none; font-weight: 700; font-size: 13px; height: 42px; display: inline-flex; align-items: center; gap: 8px;">Send Email <i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- PREVIEW MODAL -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header d-flex flex-column align-items-start position-relative" style="background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); padding: 24px 30px; border: none;">
                    <h4 class="modal-title fw-bold text-white m-0" style="font-size: 20px;"><i class="fas fa-eye me-2"></i> Document Preview</h4>
                    <p class="text-white-50 m-0 mt-1" style="font-size: 13px; opacity: 0.85;">Preview the generated A4 blueprint.</p>
                    <button type="button" class="close text-white position-absolute" data-dismiss="modal" aria-label="Close" style="font-size: 28px; right: 24px; top: 20px; opacity: 0.8; background: none; border: none;">&times;</button>
                </div>
                <div class="modal-body p-0" style="height: 70vh;">
                    <iframe id="previewIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- AUDIT LOG MODAL -->
    <div class="modal fade" id="auditLogModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header d-flex flex-column align-items-start position-relative" style="background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); padding: 24px 30px; border: none;">
                    <h4 class="modal-title fw-bold text-white m-0" style="font-size: 20px;"><i class="fas fa-history me-2"></i> Document History</h4>
                    <p class="text-white-50 m-0 mt-1" style="font-size: 13px; opacity: 0.85;">View history of operations for this document.</p>
                    <button type="button" class="close text-white position-absolute" data-dismiss="modal" aria-label="Close" style="font-size: 28px; right: 24px; top: 20px; opacity: 0.8; background: none; border: none;">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div id="auditLogTimeline" class="timeline">
                        <!-- Dynamic timeline items -->
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-end">
                    <button type="button" class="btn btn-primary rounded-pill px-4 text-white" style="background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); border: none; font-weight: 700; font-size: 13px; height: 42px; display: inline-flex; align-items: center; justify-content: center;" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script>
        function openEmailModal(id) {
            let url = '{{ route("hrms.document-generation.generated.email", ":id") }}';
            url = url.replace(':id', id);
            document.getElementById('emailForm').action = url;
            $('#emailModal').modal('show');
        }

        function previewPdf(url) {
            document.getElementById('previewIframe').src = url;
            $('#previewModal').modal('show');
        }

        function viewAuditLogs(id, logs) {
            const container = document.getElementById('auditLogTimeline');
            container.innerHTML = '';
            if (!logs || logs.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-4">No audit logs found for this document.</div>';
            } else {
                logs.forEach(log => {
                    const item = document.createElement('div');
                    item.className = 'timeline-item d-flex gap-3 mb-3';
                    item.innerHTML = `
                        <div class="timeline-badge rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0; background: #e0f2fe; color: #0369a1;">
                            <i class="fas fa-check-circle" style="font-size: 14px;"></i>
                        </div>
                        <div class="timeline-content" style="flex-grow: 1; padding-left: 8px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 13.5px; font-weight: 800;">${log.action}</h6>
                                <span class="text-muted small" style="font-size: 11px;">${log.date}</span>
                            </div>
                            <p class="text-muted mb-1 small" style="font-size: 12px; margin-top: 2px;">${log.remarks}</p>
                            <small class="text-secondary font-weight-bold" style="font-size: 11px;">Actor: ${log.actor}</small>
                        </div>
                    `;
                    container.appendChild(item);
                });
            }
            $('#auditLogModal').modal('show');
        }

        // Client-side CSV export
        function exportTableToCSV(filename) {
            var csv = [];
            var rows = document.querySelectorAll("table.att-table tr");

            for (var i = 0; i < rows.length; i++) {
                var row = [],
                    cols = rows[i].querySelectorAll("td, th");

                // If the row doesn't have columns or is an empty state, skip
                if (cols.length <= 1) continue;

                for (var j = 0; j < cols.length - 1; j++) { // exclude actions column
                    var text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/"/g, '""').trim();
                    row.push('"' + text + '"');
                }

                csv.push(row.join(","));
            }

            var csvFile = new Blob([csv.join("\n")], {
                type: "text/csv"
            });
            var downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('filterSearch');

            // If search value exists, restore cursor focus at the end of the text input
            if (searchInput.value) {
                searchInput.focus();
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
            }
        });
    </script>
    @endpush