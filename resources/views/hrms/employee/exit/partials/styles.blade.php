<style>
    :root {
        --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
        --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
        --orb-primary-hover: {{ $branding['primary_color'] ?? '#3C00B8' }};
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    /* Custom exit-table-card specific styles */
    .exit-table-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .eo-card-header-premium {
        padding: 22px 28px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .eo-card-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .eo-header-icon-circle {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .eo-card-title-premium {
        font-size: 18px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
    }

    .eo-card-subtitle-premium {
        font-size: 12px;
        font-weight: 600;
        color: var(--orb-muted);
        margin: 4px 0 0 0;
    }

    /* Filters Layout - Fully Responsive Grid */
    .eo-filter-inside {
        padding: 18px 24px;
        border-bottom: 1px solid var(--orb-border);
        background: #FCFCFD;
    }

    .exit-filter-grid {
        display: grid;
        grid-template-columns: minmax(130px, 1.15fr) minmax(180px, 1.45fr) minmax(110px, 1fr) minmax(110px, 1fr) minmax(110px, 1fr) minmax(110px, 1fr) auto;
        gap: 12px;
        align-items: end;
    }

    .exit-filter-grid .eo-field {
        margin-bottom: 0 !important;
        width: 100%;
    }

    .exit-filter-grid .eo-control {
        height: 38px !important;
        border-radius: 10px !important;
        font-size: 12px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
        font-weight: 700;
        padding: 8px 12px;
        outline: none;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        width: 100% !important;
        transition: all 0.2s ease;
    }

    .exit-filter-grid .eo-control:focus {
        border-color: var(--orb-primary) !important;
        background: #fff !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
    }

    .exit-filter-grid label {
        display: block;
        margin: 0 0 6px;
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    /* Premium Search & Reset Buttons */
    .eo-filter-btns {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-search-primary {
        height: 38px !important;
        min-height: 38px !important;
        border-radius: 10px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        padding: 0 18px !important;
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #fff !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        white-space: nowrap !important;
    }

    .btn-search-primary:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2) !important;
        transform: translateY(-1px) !important;
        color: #fff !important;
    }

    .btn-search-primary:active {
        transform: translateY(0) !important;
    }

    .btn-reset-secondary {
        height: 38px !important;
        min-height: 38px !important;
        border-radius: 10px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        padding: 0 16px !important;
        background: #F1F5F9 !important;
        color: #475569 !important;
        border: 1px solid #E2E8F0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        white-space: nowrap !important;
    }

    .btn-reset-secondary:hover {
        background: #E2E8F0 !important;
        color: #1E293B !important;
        border-color: #CBD5E1 !important;
        transform: translateY(-1px) !important;
    }

    /* Responsive Breakpoints for Filters */
    @media(max-width: 1300px) {
        .exit-filter-grid {
            grid-template-columns: repeat(4, 1fr);
        }
        .eo-filter-btns {
            grid-column: span 2;
            justify-content: flex-start;
        }
    }

    @media(max-width: 991px) {
        .exit-filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .eo-filter-btns {
            grid-column: span 2;
            justify-content: flex-start;
            margin-top: 4px;
        }
    }

    @media(max-width: 576px) {
        .exit-filter-grid {
            grid-template-columns: 1fr;
        }
        .eo-filter-btns {
            grid-column: span 1;
            width: 100%;
        }
        .eo-filter-btns .btn-search-primary,
        .eo-filter-btns .btn-reset-secondary {
            flex: 1;
        }
    }

    /* Table Scroll & Datatable */

    /* Scroll Behavior & Table */
    .exit-table-scroll {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .exit-table-scroll table {
        min-width: 1200px;
        margin-bottom: 0;
    }

    .exit-dt-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 22px;
        border-top: 1px solid #E7EAF3;
        flex-wrap: wrap;
        gap: 12px;
        background: #fff;
    }

    /* DataTable Overrides & Premium Styling */
    .dataTables_wrapper {
        position: relative;
        width: 100%;
    }

    .dataTables_paginate {
        display: flex;
        align-items: center;
    }

    .dataTables_info {
        font-size: 13px;
        color: var(--orb-muted);
        font-weight: 600;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 6px 12px !important;
        margin-left: 4px !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 8px !important;
        background: #fff !important;
        color: #344054 !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        cursor: pointer !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
        display: inline-block !important;
        transition: 0.15s ease;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #F8FAFC !important;
        color: var(--orb-text) !important;
        border-color: #D0D5DD !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--orb-primary) !important;
        color: #fff !important;
        border-color: var(--orb-primary) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        background: #F9FAFB !important;
        color: #D0D5DD !important;
        border-color: #EAECF0 !important;
        cursor: not-allowed !important;
    }

    /* Table Column Sizing and Formatting */
    .eo-table th {
        background: #F8FAFC !important;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .45px;
        border-bottom: 1px solid var(--orb-border);
        white-space: nowrap;
        padding: 14px 18px;
    }

    .eo-table td {
        vertical-align: middle;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 650;
        border-bottom: 1px solid #F1F3F8;
        padding: 14px 18px;
    }

    .eo-table tbody tr:hover {
        background: #FCFAFF;
    }

    .eo-emp-cell {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .eo-emp-cell .eo-name {
        font-weight: 950;
        color: var(--orb-text);
        font-size: 13.5px;
    }

    .eo-emp-cell .eo-code-under {
        display: inline-flex;
        width: max-content;
        padding: 3px 7px;
        border-radius: 6px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .eo-emp-cell .eo-muted-text {
        font-size: 11.5px;
        color: var(--orb-muted);
    }

    .eo-pill {
        display: inline-flex !important;
        align-items: center !important;
        border-radius: 999px !important;
        white-space: nowrap !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        font-size: 10px !important;
        padding: 4px 9px !important;
        gap: 5px !important;
    }

    .eo-pill-success {
        background: #DCFCE7 !important;
        color: #15803D !important;
    }

    .eo-pill-info {
        background: #E0F2FE !important;
        color: #0369A1 !important;
    }

    .eo-pill-danger {
        background: #FEE2E2 !important;
        color: #B91C1C !important;
    }

    .eo-pill-warning {
        background: #FEF3C7 !important;
        color: #B45309 !important;
    }

    .eo-action-btn-premium {
        height: 34px;
        border-radius: 8px;
        padding: 0 12px;
        font-size: 11.5px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        border: 1px solid transparent;
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        transition: 0.15s ease;
        white-space: nowrap;
    }

    .eo-action-btn-premium:hover {
        background: var(--orb-primary) !important;
        color: #fff !important;
    }

    .eo-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 6px !important;
        flex-wrap: nowrap !important;
        white-space: nowrap !important;
    }

    .eo-icon-btn {
        width: 34px !important;
        height: 34px !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #F8FAFC !important;
        border: 1px solid var(--orb-border) !important;
        color: var(--orb-muted) !important;
        transition: 0.15s ease !important;
    }

    .eo-icon-btn:hover {
        background: var(--orb-primary) !important;
        color: #fff !important;
        border-color: var(--orb-primary) !important;
    }

    /* Modal Responsive & Premium Styles */
    .modal-dialog {
        max-width: 900px !important;
        width: 95% !important;
        margin: 1.5rem auto !important;
    }

    .modal-content {
        border: none !important;
        border-radius: 20px !important;
        overflow: hidden !important;
        box-shadow: 0 20px 50px rgba(16, 24, 40, 0.18) !important;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        padding: 16px 22px !important;
        border: none !important;
        color: #fff !important;
    }

    .modal-header .modal-title {
        font-size: 16px !important;
        font-weight: 900 !important;
        color: #fff !important;
        margin: 0 !important;
        line-height: 1.3 !important;
    }

    .modal-header .modal-subtitle {
        font-size: 11.5px !important;
        color: rgba(255, 255, 255, 0.9) !important;
        margin: 3px 0 0 0 !important;
        font-weight: 500 !important;
        line-height: 1.35 !important;
    }

    .modal-header .close {
        color: #fff !important;
        opacity: 0.9 !important;
        text-shadow: none !important;
        background: rgba(255, 255, 255, 0.2) !important;
        width: 28px !important;
        height: 28px !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: none !important;
        outline: none !important;
        transition: all 0.2s ease;
        font-size: 18px !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .modal-header .close:hover {
        opacity: 1 !important;
        background: rgba(255, 255, 255, 0.35) !important;
    }

    .modal-body {
        padding: 18px 22px !important;
        background: #fff !important;
        max-height: calc(85vh - 75px);
        overflow-y: auto;
    }

    /* Custom scrollbar for modal body */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    .modal-body::-webkit-scrollbar-track {
        background: #F1F5F9;
        border-radius: 8px;
    }
    .modal-body::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 8px;
    }
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #94A3B8;
    }

    .modal-footer {
        padding: 14px 22px !important;
        border-top: 1px solid var(--orb-border) !important;
        background: #F8FAFC !important;
        display: flex !important;
        justify-content: flex-end !important;
        gap: 10px !important;
    }

    /* Modal Form / Button Pill Actions */
    .btn-orb,
    .btn-primary {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #fff !important;
        border-radius: 50px !important;
        font-weight: 800 !important;
        padding: 8px 20px !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
    }

    .btn-orb:hover,
    .btn-primary:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2) !important;
        transform: translateY(-1px) !important;
        color: #fff !important;
    }

    .btn-soft,
    .btn-secondary {
        background: #EAECEF !important;
        color: #4A5568 !important;
        border-radius: 50px !important;
        font-weight: 800 !important;
        padding: 8px 20px !important;
        border: none !important;
    }

    .btn-soft:hover,
    .btn-secondary:hover {
        background: #DFE2E6 !important;
        color: #2D3748 !important;
        transform: translateY(-1px) !important;
    }

    .eo-label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        margin-bottom: 5px !important;
        display: block !important;
    }

    .required {
        color: #D92D20 !important;
        font-weight: 950 !important;
    }

    /* Action Modal Cards */
    .eo-action-card {
        border: 1px solid #EEF1F6;
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        margin-bottom: 14px;
    }

    .eo-action-card:last-child {
        margin-bottom: 0;
    }

    .eo-action-card-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: #F8FAFC;
        border-bottom: 1px solid #EEF1F6;
    }

    .eo-action-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F4F2FF;
        color: var(--orb-primary);
        flex: 0 0 auto;
        font-size: 13px;
    }

    .eo-action-title {
        font-size: 13px;
        font-weight: 950;
        color: var(--orb-text);
        line-height: 1.2;
    }

    .eo-action-sub {
        font-size: 11px;
        font-weight: 750;
        color: var(--orb-muted);
        margin-top: 2px;
        line-height: 1.35;
    }

    .eo-action-body {
        padding: 14px 16px;
        background: #fff;
    }

    /* Mobile & Tablet Specific Modal Overrides */
    @media(max-width: 768px) {
        .modal-dialog {
            margin: 0.5rem auto !important;
            width: 95% !important;
        }
        .modal-content {
            border-radius: 16px !important;
        }
        .modal-header {
            padding: 14px 16px !important;
        }
        .modal-body {
            padding: 14px !important;
            max-height: calc(90vh - 70px);
        }
        .modal-footer {
            padding: 12px 16px !important;
        }
    }

    @media(max-width: 576px) {
        .modal-dialog {
            margin: 6px auto !important;
            width: 96% !important;
            max-width: 96% !important;
        }
        .modal-content {
            border-radius: 14px !important;
        }
        .modal-header {
            padding: 12px 14px !important;
        }
        .modal-header .modal-title {
            font-size: 14.5px !important;
        }
        .modal-header .modal-subtitle {
            font-size: 10.5px !important;
        }
        .modal-body {
            padding: 12px 10px !important;
            max-height: calc(92vh - 60px);
        }
        .eo-overview-pills {
            width: 100%;
            justify-content: flex-start;
            gap: 6px 10px !important;
        }
        .eo-action-flex-row {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
        }
        .eo-action-submit-btn,
        .eo-complete-btn-wrap .btn,
        .eo-complete-btn-wrap {
            width: 100% !important;
        }
        .eo-dept-head {
            background: #F8FAFC !important;
            border-bottom: 1px solid #EEF1F6 !important;
            transition: background 0.15s ease;
        }
        .eo-dept-head:hover {
            background: #F1F5F9 !important;
        }
        .eo-dept-head-left {
            min-width: 0;
        }
        .eo-dept-head-right {
            flex-shrink: 0;
        }
        .eo-action-card-head {
            padding: 10px 12px;
            gap: 10px;
        }
        .eo-action-body {
            padding: 12px;
        }
    }
</style>
