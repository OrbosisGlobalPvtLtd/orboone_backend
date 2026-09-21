@extends('layouts.panel', [
    'accesses' => $accesses ?? [],
    'active' => $active ?? 'attendance'
])

@section('_head')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<style>
    .orb-page {
        width: 100%;
        max-width: 100%;
        min-height: calc(100vh - 90px);
        padding: 24px !important;
        background: var(--bg, #F6F7FB);
        box-sizing: border-box;
    }

    /* HERO */
    .orb-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252));
        border-radius: 26px;
        padding: 24px 28px;
        color: #fff;
        box-shadow: 0 20px 45px rgba(75, 0, 232, .22);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin: 0 0 18px;
        width: 100%;
        box-sizing: border-box;
    }

    .orb-hero-content {
        min-width: 0;
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
        font-size: 13px;
        line-height: 1.6;
        max-width: 780px;
    }

    /* BUTTONS */
    .orb-btn {
        border-radius: 14px;
        min-height: 40px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s ease;
        cursor: pointer;
        text-decoration: none !important;
        border: 1px solid transparent;
        line-height: 1;
        white-space: nowrap;
    }

    .orb-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .orb-btn-gradient {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252));
        color: #fff;
        box-shadow: 0 14px 30px rgba(75, 0, 232, .18);
    }

    .orb-btn-light {
        background: #fff;
        color: #344054;
        border-color: #D0D5DD;
    }

    .orb-btn-light:hover {
        background: #F8FAFC;
        color: var(--orb-primary, #4B00E8);
        border-color: rgba(75, 0, 232, .25);
    }

    .orb-btn-reset {
        min-height: 38px;
        height: 38px;
        padding: 0 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: #fff;
        border: 1px solid #D0D5DD;
        color: #475467;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
        transition: all 0.2s ease;
    }

    .orb-btn-reset:hover {
        background: #F8FAFC;
        color: #1D2939;
        border-color: #98A2B3;
        transform: translateY(-1px);
    }

    .orb-btn-search {
        min-height: 38px;
        height: 38px;
        padding: 0 18px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: none;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252));
        color: #fff;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2);
        transition: all 0.2s ease;
    }

    .orb-btn-search:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.3);
        color: #fff;
    }

    .orb-action-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid #E4E7EC;
        background: #fff;
        color: #475467;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
        cursor: pointer;
    }

    .orb-action-btn:hover {
        background: #F4F2FF;
        color: var(--orb-primary, #4B00E8);
        border-color: rgba(75, 0, 232, .25);
    }

    /* CARD */
    .orb-card {
        background: #fff;
        border: 1px solid #E7EAF3;
        border-radius: 22px;
        box-shadow: 0 14px 35px rgba(16, 24, 40, .07);
        margin-bottom: 18px;
        overflow: hidden;
    }

    .orb-card-body {
        padding: 18px;
    }

    /* SUMMARY GRID */
    .orb-summary-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .orb-summary-card {
        position: relative;
        overflow: hidden;
        background: #fff;
        border: 1px solid #E7EAF3;
        border-radius: 18px;
        padding: 14px 16px;
        box-shadow: 0 14px 35px rgba(16, 24, 40, .07);
        min-height: 88px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .orb-summary-card::after {
        content: '';
        position: absolute;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        right: -20px;
        bottom: -20px;
        background: rgba(75, 0, 232, .05);
    }

    .orb-summary-label {
        font-size: 11px;
        text-transform: uppercase;
        color: #667085;
        font-weight: 800;
        margin-bottom: 6px;
        letter-spacing: .04em;
    }

    .orb-summary-value {
        font-size: 24px;
        font-weight: 950;
        color: #101828;
        line-height: 1;
    }

    /* TABLE CARD */
    .orb-table-card .orb-card-body {
        padding: 0;
        overflow: hidden;
    }

    .orb-table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 18px 12px;
        border-bottom: 1px solid #EEF2F6;
        background: #fff;
    }

    .orb-table-head-left {
        min-width: 0;
    }

    .orb-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #F4F2FF;
        color: var(--orb-primary, #4B00E8);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        border: 1px solid rgba(75, 0, 232, .10);
    }

    .orb-table-head-right {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex: 0 0 auto;
    }

    .orb-table-title {
        margin: 0;
        font-size: 16px;
        font-weight: 950;
        color: #101828;
        letter-spacing: -.02em;
    }

    .orb-table-subtitle {
        margin: 3px 0 0;
        font-size: 12px;
        color: #667085;
        font-weight: 600;
    }

    /* FILTER SECTION */
    .orb-filter {
        margin: 0;
        border-bottom: 1px solid #EEF2F6;
        background: #FCFCFD;
        padding: 16px 18px;
    }

    .orb-filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 12px;
    }

    .orb-filter-item {
        flex: 1 1 150px;
        min-width: 140px;
    }

    .orb-filter-actions {
        flex: 0 0 auto;
        min-width: auto;
        display: inline-flex;
        align-items: flex-end;
        gap: 8px;
        margin-bottom: 0;
    }

    .orb-form-group {
        margin-bottom: 0;
    }

    .orb-form-label {
        font-size: 11px;
        font-weight: 800;
        color: #475467;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: .03em;
        display: block;
    }

    .orb-filter-form .form-control,
    .orb-filter-form .custom-select,
    .orb-filter-form select {
        border-radius: 12px;
        border: 1px solid #D0D5DD;
        min-height: 38px;
        height: 38px;
        font-size: 12px;
        font-weight: 600;
        color: #101828;
        padding: 0 12px;
        background-color: #fff;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        width: 100%;
    }

    .orb-filter-form select.form-control,
    .orb-filter-form .custom-select {
        padding-right: 32px;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 11px 11px;
        appearance: none;
        -webkit-appearance: none;
    }

    .orb-filter-form .form-control:focus,
    .orb-filter-form .custom-select:focus,
    .orb-filter-form select:focus {
        border-color: var(--orb-primary, #4B00E8);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .10) !important;
        outline: none;
    }

    /* Select2 in Filter */
    .orb-filter-form .select2-container .select2-selection--single {
        height: 38px !important;
        border-radius: 12px !important;
        border: 1px solid #D0D5DD !important;
        background-color: #fff !important;
        display: flex;
        align-items: center;
        padding: 0 6px !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04) !important;
    }

    .orb-filter-form .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        color: #101828 !important;
        padding-left: 6px !important;
    }

    .orb-filter-form .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }

    /* DATATABLES TOOLBAR & EXPORTS */
    .dataTables_wrapper .dataTables_filter {
        display: none !important;
    }

    .crud-dt-toolbar {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        flex-wrap: nowrap !important;
        margin: 0 !important;
        width: 100%;
        padding: 10px 18px !important;
        background: #fff !important;
        border-bottom: 1px solid #EEF2F6 !important;
        box-sizing: border-box !important;
    }

    .crud-dt-left {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        white-space: nowrap !important;
    }

    .crud-dt-right {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
        flex: 0 0 auto !important;
    }

    .dataTables_wrapper .dt-buttons {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
        margin: 0 !important;
    }

    .crud-export-btn,
    .dataTables_wrapper .dt-buttons .btn,
    .dataTables_wrapper .dt-buttons .dt-button {
        width: auto !important;
        height: 32px !important;
        min-height: 32px !important;
        padding: 0 10px !important;
        border-radius: 9px !important;
        border: 1px solid #D0D5DD !important;
        background: #fff !important;
        color: #344054 !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        line-height: 30px !important;
        box-shadow: none !important;
        margin: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        white-space: nowrap !important;
        transition: all .2s ease !important;
        cursor: pointer !important;
    }

    .crud-export-btn:hover,
    .dataTables_wrapper .dt-buttons .btn:hover,
    .dataTables_wrapper .dt-buttons .dt-button:hover {
        background: #F4F2FF !important;
        color: var(--orb-primary, #4B00E8) !important;
        border-color: rgba(75, 0, 232, .25) !important;
    }

    /* TABLE SCROLL & STYLING */
    .crud-table-responsive,
    .orb-table-wrap {
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        background: #fff !important;
    }

    .orb-table {
        width: 100% !important;
        min-width: 1200px;
        margin: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .orb-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #F8FAFC;
        border-top: 0 !important;
        border-bottom: 1px solid #E7EAF3 !important;
        color: #475467;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
        padding: 13px 12px;
    }

    .orb-table tbody td {
        vertical-align: middle !important;
        white-space: nowrap;
        padding: 12px 12px !important;
        border-top: 1px solid #F2F4F7 !important;
        font-size: 12.5px;
        font-weight: 600;
        color: #101828;
    }

    .orb-table tbody tr:hover {
        background: #FAFAFF;
    }

    /* BADGES */
    .orb-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .orb-badge-primary {
        background: #F4F2FF;
        color: var(--orb-primary, #4B00E8);
        border: 1px solid rgba(75, 0, 232, 0.15);
    }

    .orb-badge-success {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .orb-badge-warning {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .orb-badge-danger {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .orb-badge-info {
        background: #EFF8FF;
        color: #175CD3;
        border: 1px solid #B2DDFF;
    }

    /* MODAL */
    .orb-modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
    }

    .orb-modal-content .modal-header {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252));
        border: 0;
        color: #fff;
        padding: 18px 24px;
    }

    .orb-modal-content .modal-title {
        font-weight: 900;
        font-size: 17px;
        color: #fff;
    }

    .orb-modal-content .modal-header .close {
        color: #fff;
        opacity: 0.85;
        text-shadow: none;
    }

    .orb-modal-content .modal-header .close:hover {
        opacity: 1;
    }

    .orb-modal-content .modal-body {
        background: #F8FAFC;
        padding: 20px;
        max-height: calc(100vh - 180px);
        overflow-y: auto;
    }

    .orb-modal-content .modal-footer {
        background: #fff;
        border-top: 1px solid #EEF2F6;
        padding: 14px 22px;
    }

    .orb-modal-content .form-control,
    .orb-modal-content .custom-select,
    .orb-modal-content select {
        border-radius: 12px;
        border: 1px solid #D0D5DD;
        min-height: 40px;
        height: 40px;
        font-size: 13px;
        font-weight: 600;
        color: #101828;
        padding: 0 12px;
    }

    .orb-detail-section {
        background: #fff;
        border: 1px solid #E7EAF3;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 14px;
        box-shadow: 0 2px 6px rgba(16, 24, 40, .03);
    }

    .orb-detail-title {
        font-size: 12px;
        font-weight: 900;
        color: var(--orb-primary, #4B00E8);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .orb-detail-emp-banner {
        background: #fff;
        border: 1px solid #E7EAF3;
        border-radius: 16px;
        padding: 14px 16px;
        box-shadow: 0 2px 6px rgba(16, 24, 40, .03);
    }

    .orb-emp-avatar-box {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #F4F2FF;
        color: var(--orb-primary, #4B00E8);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .orb-date-range-card {
        background: #F4F2FF;
        border: 1px solid rgba(75, 0, 232, 0.12);
        border-radius: 12px;
        padding: 10px 12px;
    }

    .orb-stat-pill {
        background: #F8FAFC;
        border: 1px solid #E7EAF3;
        border-radius: 12px;
        padding: 10px 12px;
        text-align: center;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .orb-stat-pill-label {
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 800;
        color: #667085;
        letter-spacing: .03em;
        margin-bottom: 2px;
    }

    .orb-stat-pill-val {
        font-size: 14px;
        font-weight: 900;
        line-height: 1.2;
    }

    .orb-info-tile {
        background: #F8FAFC;
        border: 1px solid #EEF2F6;
        border-radius: 12px;
        padding: 10px 12px;
        height: 100%;
    }

    .orb-info-tile-label {
        font-size: 10.5px;
        text-transform: uppercase;
        font-weight: 800;
        color: #667085;
        letter-spacing: .03em;
        display: block;
        margin-bottom: 3px;
    }

    .orb-info-tile-val {
        font-size: 13px;
        font-weight: 700;
        color: #101828;
    }

    .orb-timeline-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .orb-timeline-card {
        background: #F8FAFC;
        border: 1px solid #EEF2F6;
        border-radius: 12px;
        padding: 10px 12px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .orb-tl-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .orb-tl-content {
        min-width: 0;
        flex-grow: 1;
    }

    .orb-tl-label {
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 800;
        color: #667085;
        display: block;
        margin-bottom: 2px;
    }

    .orb-tl-val {
        font-size: 12px;
        font-weight: 700;
        color: #101828;
        display: block;
        word-break: break-word;
    }

    @media(max-width: 768px) {
        .orb-timeline-grid {
            grid-template-columns: 1fr;
        }
        .orb-modal-content .modal-body {
            padding: 14px;
        }
        .orb-modal-content .modal-header {
            padding: 14px 18px;
        }
        .orb-modal-content .modal-footer {
            padding: 12px 16px;
        }
        .orb-modal-content .modal-footer .orb-btn {
            width: 100%;
        }
    }

    @media(max-width: 1200px) {
        .orb-page {
            padding: 18px 20px 28px !important;
        }
        .orb-summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
    }

    @media(max-width: 991px) {
        .orb-page {
            padding: 16px 16px 24px !important;
        }
        .orb-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 18px 20px;
            border-radius: 20px;
        }
        .orb-summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .orb-table-header {
            gap: 12px;
        }
    }

    @media(max-width: 768px) {
        .orb-page {
            padding: 14px 12px 22px !important;
        }

        .orb-hero {
            padding: 16px;
            border-radius: 18px;
        }

        .orb-hero h1 {
            font-size: 20px;
        }

        .orb-hero p {
            font-size: 12px;
        }

        .orb-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .orb-summary-card {
            padding: 12px 14px;
            min-height: 78px;
        }

        .orb-summary-value {
            font-size: 20px;
        }

        .orb-summary-label {
            font-size: 10px;
        }

        .orb-table-header {
            flex-direction: column;
            align-items: stretch;
            padding: 14px;
            gap: 12px;
        }

        .orb-table-head-right {
            width: 100%;
        }

        .orb-table-head-right .orb-btn {
            width: 100%;
        }

        .orb-filter {
            padding: 14px 14px;
        }

        .orb-filter-form {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .orb-filter-item {
            width: 100% !important;
            min-width: 100% !important;
            flex: 1 1 100% !important;
        }

        .orb-filter-actions {
            width: 100% !important;
            min-width: 100% !important;
            display: flex !important;
            gap: 8px !important;
            margin-top: 6px !important;
        }

        .orb-filter-actions .orb-btn {
            flex: 1 1 50% !important;
            width: 50% !important;
            height: 38px !important;
            min-height: 38px !important;
            justify-content: center !important;
        }

        .crud-dt-toolbar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
            padding: 12px 14px !important;
        }

        .crud-dt-left {
            width: 100% !important;
            justify-content: space-between !important;
        }

        .crud-dt-right {
            width: 100% !important;
            justify-content: flex-start !important;
            margin: 0 !important;
        }

        .dataTables_wrapper .dt-buttons {
            width: 100% !important;
            display: flex !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 4px !important;
        }

        .crud-export-btn,
        .dataTables_wrapper .dt-buttons .btn,
        .dataTables_wrapper .dt-buttons .dt-button {
            flex: 1 1 calc(25% - 4px) !important;
            min-width: 60px !important;
            padding: 0 6px !important;
            font-size: 11px !important;
            height: 32px !important;
            justify-content: center !important;
        }
    }

    @media(max-width: 576px) {
        .orb-page {
            padding: 12px 10px 20px !important;
        }

        .orb-hero {
            padding: 14px 16px;
            border-radius: 16px;
        }

        .orb-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .orb-summary-card {
            padding: 10px 12px;
            min-height: 72px;
            border-radius: 14px;
        }

        .orb-summary-value {
            font-size: 18px;
        }

        .orb-summary-label {
            font-size: 9.5px;
            margin-bottom: 4px;
        }

        .orb-table-title {
            font-size: 15px;
        }

        .orb-btn {
            padding: 0 12px;
            font-size: 12px;
        }

        .crud-export-btn,
        .dataTables_wrapper .dt-buttons .btn,
        .dataTables_wrapper .dt-buttons .dt-button {
            flex: 1 1 calc(50% - 4px) !important;
        }
    }

    @media(max-width: 480px) {
        .orb-page {
            padding: 10px 8px 18px !important;
        }

        .orb-hero {
            padding: 14px;
            border-radius: 16px;
        }

        .orb-hero h1 {
            font-size: 18px;
        }

        .orb-hero p {
            font-size: 11.5px;
        }
    }
</style>
@endsection

@section('_content')
<div class="orb-page">
    @include('hrms.attendance.wfh.partials.header')

    @include('hrms.attendance.wfh.partials.alerts')

    @include('hrms.attendance.wfh.partials.summary')

    <div class="orb-card orb-table-card">
        <div class="orb-card-body">
            @include('hrms.attendance.wfh.partials.filters')

            @include('hrms.attendance.wfh.partials.table')
        </div>
    </div>

    @include('hrms.attendance.wfh.partials.modals.assign')
    @include('hrms.attendance.wfh.partials.modals.details')
    @include('hrms.attendance.wfh.partials.modals.approve')
    @include('hrms.attendance.wfh.partials.modals.reject')
    @include('hrms.attendance.wfh.partials.modals.mark-lwp')
</div>
@endsection

@section('_script')
<!-- DataTables and Export Buttons JS Libraries -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<script>
    (function() {
        document.querySelectorAll('.js-view-details').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var row = JSON.parse(this.getAttribute('data-row') || '{}');
                var fmt = function(v) { return (v !== null && v !== undefined && String(v).trim() !== '') ? String(v) : '-'; };
                
                document.getElementById('d_employee').textContent = fmt(row.employee_display_name);
                document.getElementById('d_employee_code').textContent = fmt(row.employee_code);
                document.getElementById('d_from_date').textContent = fmt(row.from_date_formatted || row.request_date);
                document.getElementById('d_to_date').textContent = fmt(row.to_date_formatted || row.from_date_formatted || row.request_date);
                document.getElementById('d_total_days').textContent = fmt(row.total_days || 1) + ' Days';
                document.getElementById('d_working_days').textContent = fmt(row.working_days || 1) + ' Days';
                document.getElementById('d_weekoff_days').textContent = fmt(row.weekoff_days || 0) + ' Days';
                document.getElementById('d_holiday_days').textContent = fmt(row.holiday_days || 0) + ' Days';
                
                var formatLabel = function(str) {
                    if (!str) return '-';
                    return str.replaceAll('_', ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                };

                document.getElementById('d_type').textContent = formatLabel(row.request_type);
                document.getElementById('d_reason_cat').textContent = formatLabel(row.reason_category);
                document.getElementById('d_reason').textContent = fmt(row.reason);

                // Quota Impact Badge
                var quotaEl = document.getElementById('d_quota');
                if (quotaEl) {
                    quotaEl.innerHTML = row.counts_in_monthly_quota 
                        ? '<span class="orb-badge orb-badge-warning"><i class="fas fa-exclamation-circle mr-1"></i> Counts in Quota</span>'
                        : '<span class="orb-badge orb-badge-primary"><i class="fas fa-check-circle mr-1"></i> Non-Quota</span>';
                }

                // Payroll Impact Badge
                var payrollEl = document.getElementById('d_payroll');
                if (payrollEl) {
                    var pImpact = (row.payroll_impact || 'none').toLowerCase();
                    payrollEl.innerHTML = pImpact === 'lwp'
                        ? '<span class="orb-badge orb-badge-danger"><i class="fas fa-times-circle mr-1"></i> LWP (Loss of Pay)</span>'
                        : '<span class="orb-badge orb-badge-success"><i class="fas fa-check-circle mr-1"></i> Paid (None)</span>';
                }

                // Status Badge in Banner
                var stBadgeEl = document.getElementById('d_status_badge');
                if (stBadgeEl) {
                    var st = (row.status || 'pending').toLowerCase();
                    if (st === 'approved') {
                        stBadgeEl.innerHTML = '<span class="orb-badge orb-badge-success font-weight-bold" style="font-size: 12px;"><i class="fas fa-check-circle mr-1"></i> Approved</span>';
                    } else if (st === 'rejected' || st === 'cancelled') {
                        stBadgeEl.innerHTML = '<span class="orb-badge orb-badge-danger font-weight-bold" style="font-size: 12px;"><i class="fas fa-times-circle mr-1"></i> ' + (st.charAt(0).toUpperCase() + st.slice(1)) + '</span>';
                    } else if (st === 'manager_approved') {
                        stBadgeEl.innerHTML = '<span class="orb-badge orb-badge-info font-weight-bold" style="font-size: 12px;"><i class="fas fa-user-check mr-1"></i> Pending HR</span>';
                    } else {
                        stBadgeEl.innerHTML = '<span class="orb-badge orb-badge-warning font-weight-bold" style="font-size: 12px;"><i class="fas fa-clock mr-1"></i> Pending</span>';
                    }
                }

                // Optional Boxes (LWP, Assigned By, Remarks)
                var lwpBox = document.getElementById('d_lwp_box');
                if (lwpBox) {
                    if (row.lwp_reason) {
                        document.getElementById('d_lwp_reason').textContent = row.lwp_reason;
                        lwpBox.style.display = 'block';
                    } else {
                        lwpBox.style.display = 'none';
                    }
                }

                var assignedBox = document.getElementById('d_assigned_by_box');
                if (assignedBox) {
                    if (row.assigned_by_label) {
                        document.getElementById('d_assigned_by').textContent = row.assigned_by_label;
                        assignedBox.style.display = 'block';
                    } else {
                        assignedBox.style.display = 'none';
                    }
                }

                var remarksBox = document.getElementById('d_remarks_box');
                if (remarksBox) {
                    if (row.remarks) {
                        document.getElementById('d_remarks').textContent = row.remarks;
                        remarksBox.style.display = 'block';
                    } else {
                        remarksBox.style.display = 'none';
                    }
                }

                // Audit History
                document.getElementById('d_applied_at').textContent = fmt(row.created_at);
                document.getElementById('d_mgr_at').textContent = fmt(row.manager_approved_at);
                document.getElementById('d_hr_at').textContent = fmt(row.hr_approved_at);

                var rejCard = document.getElementById('d_rej_card');
                if (rejCard) {
                    if (row.rejected_at || row.rejection_reason) {
                        document.getElementById('d_rej_at').textContent = fmt(row.rejected_at);
                        document.getElementById('d_rej_reason').textContent = fmt(row.rejection_reason);
                        rejCard.style.display = 'flex';
                    } else {
                        rejCard.style.display = 'none';
                    }
                }

                $('#wfhDetailsModal').modal('show');
            });
        });

        var scopeSelect = document.querySelector('.js-assign-scope');
        var singleBox = document.querySelector('.js-scope-single');
        var multipleBox = document.querySelector('.js-scope-multiple');
        var departmentBox = document.querySelector('.js-scope-department');
        var designationBox = document.querySelector('.js-scope-designation');
        var toggleAssignScope = function() {
            if (!scopeSelect) return;
            var scope = scopeSelect.value;
            if (singleBox) singleBox.classList.toggle('d-none', scope !== 'single');
            if (multipleBox) multipleBox.classList.toggle('d-none', scope !== 'multiple');
            if (departmentBox) departmentBox.classList.toggle('d-none', scope !== 'department');
            if (designationBox) designationBox.classList.toggle('d-none', scope !== 'designation');
        };
        if (scopeSelect) {
            scopeSelect.addEventListener('change', toggleAssignScope);
            toggleAssignScope();
        }

        var approveForm = document.getElementById('approveForm');
        var approveQuotaWarning = document.getElementById('approveQuotaWarning');
        var approveSubmitBtn = document.getElementById('approveSubmitBtn');
        var approveOverrideInput = document.getElementById('approve_override_quota');
        var userCanOverride = @json($canOverrideQuota ?? false);

        document.querySelectorAll('.js-approve').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!approveForm) return;
                var row = JSON.parse(this.getAttribute('data-row') || '{}');
                var id = this.dataset.id || row.id;
                approveForm.action = "{{ route('hrms.attendance.wfh.approve', ['id' => '__ID__']) }}".replace('__ID__', id);

                if (row.exceeds_quota) {
                    if (approveQuotaWarning) {
                        approveQuotaWarning.classList.remove('d-none');
                        document.getElementById('approve_q_limit').textContent = row.monthly_quota || 0;
                        document.getElementById('approve_q_used').textContent = row.already_approved_quota || 0;
                        document.getElementById('approve_q_req').textContent = row.requested_working_days || 1;
                    }
                    if (userCanOverride) {
                        if (approveOverrideInput) approveOverrideInput.value = '1';
                        if (approveSubmitBtn) {
                            approveSubmitBtn.className = 'orb-btn btn-warning font-weight-bold';
                            approveSubmitBtn.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> Approve Anyway';
                        }
                    } else {
                        if (approveOverrideInput) approveOverrideInput.value = '0';
                        if (approveSubmitBtn) {
                            approveSubmitBtn.className = 'orb-btn orb-btn-gradient';
                            approveSubmitBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Confirm Approve';
                        }
                    }
                } else {
                    if (approveQuotaWarning) approveQuotaWarning.classList.add('d-none');
                    if (approveOverrideInput) approveOverrideInput.value = '0';
                    if (approveSubmitBtn) {
                        approveSubmitBtn.className = 'orb-btn orb-btn-gradient';
                        approveSubmitBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Confirm Approve';
                    }
                }

                $('#approveModal').modal('show');
            });
        });

        var rejectForm = document.getElementById('rejectForm');
        document.querySelectorAll('.js-reject').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!rejectForm) return;
                rejectForm.action = "{{ route('hrms.attendance.wfh.reject', ['id' => '__ID__']) }}".replace('__ID__', this.dataset.id);
                $('#rejectModal').modal('show');
            });
        });

        var markLwpForm = document.getElementById('markLwpForm');
        document.querySelectorAll('.js-mark-lwp').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!markLwpForm) return;
                markLwpForm.action = "{{ route('hrms.attendance.wfh.mark-lwp', ['id' => '__ID__']) }}".replace('__ID__', this.dataset.id);
                $('#markLwpModal').modal('show');
            });
        });

        document.querySelectorAll('.js-open-assign').forEach(function(btn) {
            btn.addEventListener('click', function() {
                $('#assignWfhModal').modal('show');
            });
        });

        if (window.jQuery && $.fn.DataTable) {
            var $table = $('.js-orb-datatable');
            var exportOptions = {
                columns: function(idx, data, node) {
                    var $th = $($table.find('thead th')[idx]);
                    var headerText = $th.text().trim().toLowerCase();
                    return headerText !== 'action' && !$th.hasClass('no-export');
                },
                format: {
                    body: function (data, row, column, node) {
                        var $node = $('<div>' + data + '</div>');
                        $node.find('button, .dropdown-menu, .dropdown, i.fas, i.fa, i.far').remove();
                        var text = $node.text().trim();
                        return text.replace(/\s+/g, ' ');
                    }
                }
            };

            var dataTable = $table.DataTable({
                paging: false,
                searching: false,
                info: false,
                lengthChange: false,
                responsive: false,
                autoWidth: false,
                order: [],
                language: {
                    emptyTable: 'No records found.',
                    zeroRecords: 'No matching records found.'
                },
                dom: '<"dt-buttons-container"B>rt',
                buttons: [
                    { 
                        extend: 'csvHtml5', 
                        text: '<i class="fas fa-file-csv text-muted"></i> CSV', 
                        className: 'crud-export-btn',
                        exportOptions: exportOptions 
                    },
                    { 
                        extend: 'excelHtml5', 
                        text: '<i class="fas fa-file-excel text-success"></i> Excel', 
                        className: 'crud-export-btn',
                        exportOptions: exportOptions 
                    },
                    { 
                        extend: 'pdfHtml5', 
                        text: '<i class="fas fa-file-pdf text-danger"></i> PDF', 
                        className: 'crud-export-btn',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: '{{ $branding["company_name"] ?? "OrboOne" }} — WFH Requests',
                        exportOptions: exportOptions,
                        customize: function (doc) {
                            doc.pageOrientation = 'landscape';
                            doc.pageSize = 'A4';
                            doc.pageMargins = [20, 48, 20, 32];

                            doc['header'] = function(currentPage, pageCount) {
                                return {
                                    margin: [20, 16, 20, 0],
                                    columns: [
                                        {
                                            text: '{{ strtoupper($branding["company_name"] ?? "ORBOONE HRMS") }} — WFH REQUESTS',
                                            fontSize: 9,
                                            bold: true,
                                            color: '{{ $branding["primary_color"] ?? "#4B00E8" }}'
                                        },
                                        {
                                            text: 'Generated on: ' + (new Date()).toLocaleDateString() + '  |  Page ' + currentPage.toString() + ' of ' + pageCount,
                                            alignment: 'right',
                                            fontSize: 8,
                                            color: '#64748B'
                                        }
                                    ]
                                };
                            };

                            if (doc.content && doc.content[1] && doc.content[1].table) {
                                var objLayout = {};
                                objLayout['hLineWidth'] = function(i) { return 0.5; };
                                objLayout['vLineWidth'] = function(i) { return 0; };
                                objLayout['hLineColor'] = function(i) { return '#E2E8F0'; };
                                objLayout['paddingLeft'] = function(i) { return 6; };
                                objLayout['paddingRight'] = function(i) { return 6; };
                                objLayout['paddingTop'] = function(i) { return 5; };
                                objLayout['paddingBottom'] = function(i) { return 5; };
                                doc.content[1].layout = objLayout;

                                var tableBody = doc.content[1].table.body;
                                var colCount = tableBody[0].length;
                                
                                for (var i = 0; i < colCount; i++) {
                                    tableBody[0][i].fillColor = '{{ $branding["primary_color"] ?? "#4B00E8" }}';
                                    tableBody[0][i].color = '#FFFFFF';
                                    tableBody[0][i].fontSize = 8.5;
                                    tableBody[0][i].bold = true;
                                }

                                for (var r = 1; r < tableBody.length; r++) {
                                    var rowColor = (r % 2 === 0) ? '#F8FAFC' : '#FFFFFF';
                                    for (var c = 0; c < colCount; c++) {
                                        tableBody[r][c].fontSize = 8;
                                        if (!tableBody[r][c].fillColor) {
                                            tableBody[r][c].fillColor = rowColor;
                                        }
                                    }
                                }

                                doc.content[1].table.widths = Array(colCount).fill('*');
                                if (colCount > 0) {
                                    doc.content[1].table.widths[0] = '5%';
                                }
                            }
                        }
                    },
                    { 
                        extend: 'print', 
                        text: '<i class="fas fa-print text-primary"></i> Print', 
                        className: 'crud-export-btn',
                        title: '',
                        exportOptions: exportOptions,
                        customize: function (win) {
                            var body = $(win.document.body);

                            $(win.document.head).append(`
                                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
                                <style>
                                    @media print {
                                        @page {
                                            size: A4 landscape;
                                            margin: 10mm 12mm;
                                        }
                                    }
                                    body {
                                        font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
                                        color: #0F172A !important;
                                        background: #FFFFFF !important;
                                        padding: 15px !important;
                                        margin: 0 !important;
                                    }
                                    .print-hero {
                                        background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%) !important;
                                        border-radius: 12px !important;
                                        padding: 16px 22px !important;
                                        color: #FFFFFF !important;
                                        margin-bottom: 20px !important;
                                        display: flex;
                                        align-items: center;
                                        justify-content: space-between;
                                    }
                                    .print-hero h2 {
                                        margin: 0 0 4px;
                                        font-size: 18px;
                                        font-weight: 800;
                                        color: #FFFFFF;
                                    }
                                    .print-hero p {
                                        margin: 0;
                                        font-size: 11px;
                                        opacity: 0.9;
                                    }
                                    .print-meta {
                                        font-size: 11px;
                                        opacity: 0.9;
                                        text-align: right;
                                    }
                                    table.dataTable {
                                        width: 100% !important;
                                        border-collapse: collapse !important;
                                        margin: 0 !important;
                                        font-size: 11px !important;
                                    }
                                    table.dataTable thead th {
                                        background: {{ $branding['primary_color'] ?? '#4B00E8' }} !important;
                                        color: #FFFFFF !important;
                                        font-weight: 700 !important;
                                        padding: 9px 10px !important;
                                        border: 1px solid #CBD5E1 !important;
                                        text-transform: uppercase;
                                        font-size: 10px;
                                        letter-spacing: 0.03em;
                                    }
                                    table.dataTable tbody td {
                                        padding: 8px 10px !important;
                                        border: 1px solid #E2E8F0 !important;
                                        color: #1E293B !important;
                                    }
                                    table.dataTable tbody tr:nth-child(even) td {
                                        background-color: #F8FAFC !important;
                                    }
                                </style>
                            `);

                            body.prepend(`
                                <div class="print-hero">
                                    <div>
                                        <h2>{{ $branding['company_name'] ?? 'OrboOne HRMS' }} — WFH Requests</h2>
                                        <p>Track, approve and manage employee Work From Home requests</p>
                                    </div>
                                    <div class="print-meta">
                                        <div>Generated: ${new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</div>
                                        <div>Total Records: ${$table.find('tbody tr').length}</div>
                                    </div>
                                </div>
                            `);
                        }
                    }
                ],
                initComplete: function() {
                    var $wrapper = $table.closest('.dataTables_wrapper');
                    var $buttons = $wrapper.find('.dt-buttons').first();
                    var $exportTarget = $table.closest('.orb-card').find('.crud-dt-right').first();

                    if ($exportTarget.length && $buttons.length) {
                        $exportTarget.empty().append($buttons);
                    }
                }
            });
        }

        $(document).on('shown.bs.modal', '.modal', function () {
            if (typeof $.fn.select2 !== 'undefined') {
                $(this).find('select.select2-modal-searchable').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    $(this).select2({
                        dropdownParent: $(this).closest('.modal'),
                        placeholder: $(this).data('placeholder') || $(this).attr('placeholder') || 'Search or select...',
                        allowClear: true,
                        width: '100%'
                    });
                });
            }
        });
    })();
</script>
@endsection
