@extends('layouts.panel', [
'accesses' => $accesses ?? [],
'active' => $active ?? 'hrms'
])

@section('_head')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

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
    }

    .orb-page {
        width: 100%;
        max-width: 100%;
        min-height: calc(100vh - 90px);
        padding: 24px !important;
        background: var(--orb-bg);
        box-sizing: border-box;
    }

    /* HERO */
    .orb-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
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

    .orb-hero-content,
    .orb-hero-actions {
        position: relative;
        z-index: 2;
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

    .orb-btn-primary {
        background: #fff;
        color: var(--orb-primary);
        border-color: rgba(255, 255, 255, .65);
        box-shadow: 0 12px 24px rgba(16, 24, 40, .12);
    }

    .orb-btn-gradient {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        box-shadow: 0 14px 30px rgba(75, 0, 232, .18);
    }

    .orb-btn-light {
        background: #fff;
        color: var(--orb-text);
        border-color: var(--orb-border);
    }

    .orb-btn-light:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, .18);
    }

    .orb-btn-reset {
        min-height: 34px;
        height: 34px;
        padding: 0 12px;
        border-radius: 11px;
        font-size: 12px;
        box-shadow: none;
    }

    /* CARDS */
    .orb-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        margin-bottom: 18px;
        overflow: hidden;
    }

    .orb-card-body {
        padding: 18px;
    }

    /* SUMMARY */
    .orb-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .orb-summary-card {
        position: relative;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 15px;
        box-shadow: var(--orb-shadow);
        min-height: 96px;
    }

    .orb-summary-card::after {
        content: '';
        position: absolute;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        right: -26px;
        bottom: -26px;
        background: rgba(75, 0, 232, .06);
    }

    .orb-summary-label {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--orb-muted);
        font-weight: 900;
        margin-bottom: 10px;
        letter-spacing: .04em;
    }

    .orb-summary-value {
        font-size: 26px;
        font-weight: 950;
        color: var(--orb-text);
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
        background: var(--orb-soft);
        color: var(--orb-primary);
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
        color: var(--orb-text);
        letter-spacing: -.02em;
    }

    .orb-table-subtitle {
        margin: 3px 0 0;
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 600;
    }

    .orb-table-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .10);
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    /* ATTACHED FILTER */
    .orb-filter {
        margin: 0;
        border-bottom: 1px solid #EEF2F6;
        background: #FCFCFD;
        padding: 14px 18px;
        width: 100%;
        box-sizing: border-box;
    }

    .orb-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
        margin: 0;
        width: 100%;
        box-sizing: border-box;
    }

    .orb-filter-item {
        flex: 1 1 140px;
        min-width: 130px;
        margin: 0 !important;
    }

    .orb-filter-actions {
        flex: 0 0 auto !important;
        min-width: 200px !important;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .orb-btn-search {
        min-height: 38px;
        height: 38px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        padding: 0 18px;
        min-width: 100px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25);
    }

    .orb-btn-reset {
        min-height: 38px;
        height: 38px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        padding: 0 16px;
        min-width: 90px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .orb-filter .orb-form-group {
        margin-bottom: 0 !important;
    }

    .orb-filter .form-control,
    .orb-filter .custom-select {
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        height: 38px;
        min-height: 38px;
        font-size: 12px;
        font-weight: 700;
        color: var(--orb-text);
        box-shadow: none !important;
        padding: 0 10px;
        background-color: #fff;
        width: 100%;
        box-sizing: border-box;
    }

    .orb-filter .select2-container--default .select2-selection--single {
        height: 38px !important;
        border-radius: 12px !important;
        border: 1px solid var(--orb-border) !important;
        padding-left: 10px !important;
        padding-right: 28px !important;
    }

    .orb-filter .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
    }

    .orb-filter .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }

    .orb-filter label,
    .orb-form-label {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--orb-muted);
        font-weight: 800;
        margin-bottom: 5px;
        letter-spacing: .04em;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .orb-filter .form-control:focus,
    .orb-filter .custom-select:focus {
        border-color: rgba(75, 0, 232, .30);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    /* TABLE WRAP */
    .orb-table-wrap,
    .crud-table-responsive {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }

    /* DATATABLE TOOLBAR */
    .orb-table-tools {
        padding: 0 !important;
        border-bottom: 1px solid #EEF2F6;
        background: #fff;
        min-height: 54px;
        display: flex;
        align-items: center;
    }

    .orb-table-tools:empty {
        display: none;
    }

    .dataTables_wrapper {
        width: 100%;
        overflow: visible !important;
    }

    .dataTables_wrapper .dataTables_filter {
        display: none !important;
    }

    .crud-dt-toolbar,
    .orb-dt-toolbar {
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

    .crud-dt-left,
    .dataTables_length {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        white-space: nowrap !important;
        flex-wrap: nowrap !important;
    }

    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
        white-space: nowrap !important;
    }

    .dataTables_length select {
        width: auto !important;
        min-width: 64px !important;
        height: 32px !important;
        padding: 2px 24px 2px 8px !important;
        display: inline-block !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 9px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        background: #fff !important;
        color: var(--orb-text) !important;
        outline: none !important;
    }

    .crud-dt-right,
    .orb-dt-toolbar .dt-right {
        display: inline-flex !important;
        align-items: center !important;
        width: auto !important;
        max-width: none !important;
        flex: 0 0 auto !important;
        padding: 0 !important;
        margin-left: auto !important;
        justify-content: flex-end !important;
        gap: 6px !important;
    }

    .dataTables_wrapper .dt-buttons {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
        margin: 0 !important;
        width: auto !important;
        flex: 0 0 auto !important;
    }

    .crud-export-btn,
    .dataTables_wrapper .dt-buttons .btn,
    .dataTables_wrapper .dt-buttons .dt-button {
        width: auto !important;
        min-width: auto !important;
        max-width: none !important;
        height: 32px !important;
        min-height: 32px !important;
        padding: 0 10px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
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
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, .22) !important;
    }

    /* TABLE SCROLL ONLY */
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
        min-width: 1050px;
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
        border-bottom: 1px solid var(--orb-border) !important;
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
        padding: 13px 12px !important;
        border-color: #F2F4F7 !important;
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
    }

    .orb-table tbody tr {
        transition: all .15s ease;
    }

    .orb-table tbody tr:hover {
        background: #FAFAFF;
    }

    .orb-table-footer-target {
        width: 100% !important;
        position: relative;
        z-index: 5;
    }

    .orb-table-footer-target:empty {
        display: none;
    }

    .orb-table-footer,
    .dataTables_wrapper .row:last-child {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        margin: 0 !important;
        padding: 12px 18px 14px !important;
        border-top: 1px solid #EEF2F6;
        background: #fff;
        width: 100% !important;
        box-sizing: border-box !important;
        overflow: visible !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding: 0 !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-muted, #6B7280) !important;
        white-space: nowrap;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding: 0 !important;
        margin: 0 !important;
        white-space: nowrap;
        overflow-x: visible !important;
    }

    .dataTables_paginate .pagination {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    .dataTables_paginate .pagination .page-item {
        margin: 0 !important;
        border: none !important;
        background: transparent !important;
    }

    .dataTables_paginate .pagination .page-item .page-link,
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

    .dataTables_paginate .pagination .page-item:not(.active):not(.disabled) .page-link:hover,
    .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: var(--orb-soft, #F3EDFF) !important;
        color: var(--orb-primary, #4B00E8) !important;
        border-color: transparent !important;
    }

    .dataTables_paginate .pagination .page-item.active .page-link,
    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)) !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.35) !important;
        cursor: default !important;
    }

    .dataTables_paginate .pagination .page-item.disabled .page-link,
    .dataTables_paginate .paginate_button.disabled,
    .dataTables_paginate .paginate_button.disabled:hover {
        background: transparent !important;
        color: #94A3B8 !important;
        border-color: transparent !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        opacity: 0.6 !important;
    }

    /* BADGE */
    .orb-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
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

    .orb-badge-muted {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .orb-badge-primary {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    /* ACTION */
    .orb-action-btn {
        width: 34px;
        height: 34px;
        border-radius: 11px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #667085;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: none;
    }

    .orb-action-btn:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, .18);
    }

    .dropdown-menu {
        border: 1px solid var(--orb-border);
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(16, 24, 40, .12);
        padding: 8px;
    }

    .dropdown-item {
        border-radius: 10px;
        font-size: 13px;
        font-weight: 800;
        padding: 9px 12px;
    }

    /* EMPTY */
    .orb-empty {
        padding: 44px 18px !important;
        text-align: center;
        color: var(--orb-muted);
    }

    .orb-empty i {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--orb-soft);
        color: var(--orb-primary);
        font-size: 20px;
        margin-bottom: 12px;
    }

    /* MODAL */
    .orb-modal .modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
    }

    .orb-modal .modal-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border: 0;
        color: #fff;
    }

    .orb-modal .modal-title {
        font-weight: 950;
    }

    .orb-modal .modal-body {
        background: #fff;
        padding: 22px;
    }

    .orb-modal .modal-footer {
        background: #F8FAFC;
        border-top: 1px solid #EEF2F6;
    }

    .orb-modal .form-control,
    .orb-modal .custom-select,
    .orb-modal select {
        border-radius: 14px;
        border: 1px solid var(--orb-border);
        min-height: 44px;
        height: 44px;
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
        padding: 0 14px;
    }

    .orb-modal select.form-control,
    .orb-modal .custom-select {
        padding-right: 36px;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 12px 12px;
        appearance: none;
        -webkit-appearance: none;
    }

    .orb-modal .form-control:focus,
    .orb-modal .custom-select:focus,
    .orb-modal select:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .10) !important;
        outline: none;
    }

    /* Select2 Option Highlighting and Text Color */
    .select2-container--default .select2-results__option--highlighted,
    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .select2-container--default .select2-results__option--highlighted[aria-selected="true"],
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)) !important;
        color: #FFFFFF !important;
    }

    .select2-container--default .select2-results__option--highlighted *,
    .select2-container--default .select2-results__option--highlighted[aria-selected] *,
    .select2-container--default .select2-results__option--highlighted[aria-selected="true"] * {
        color: #FFFFFF !important;
    }

    @media(max-width: 1200px) {
        .orb-page {
            padding: 18px 20px 28px !important;
        }
    }

    @media(max-width: 991px) {
        .orb-page {
            padding: 16px 16px 24px !important;
        }

        .orb-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .orb-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 18px 20px;
            border-radius: 20px;
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

        .orb-summary-grid {
            grid-template-columns: 1fr;
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
            padding: 12px 14px;
        }

        .orb-filter-form {
            flex-direction: column;
            gap: 10px;
        }

        .orb-filter-item {
            width: 100% !important;
            flex: 1 1 100% !important;
        }

        .orb-filter-actions {
            width: 100% !important;
            min-width: 100% !important;
            display: flex;
            gap: 8px;
        }

        .orb-filter-actions .orb-btn {
            flex: 1 1 50% !important;
            width: 50% !important;
        }

        .orb-table-tools {
            padding: 10px 14px;
        }

        .crud-dt-toolbar,
        .orb-dt-toolbar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
            padding: 10px 14px !important;
        }

        .crud-dt-left,
        .crud-dt-right,
        .orb-dt-toolbar .dt-left,
        .orb-dt-toolbar .dt-right {
            width: 100% !important;
            justify-content: space-between !important;
            margin: 0 !important;
        }

        .dataTables_wrapper .dt-buttons {
            width: 100% !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            margin: 0 !important;
            gap: 4px !important;
        }

        .crud-export-btn,
        .dataTables_wrapper .dt-buttons .btn,
        .dataTables_wrapper .dt-buttons .dt-button {
            flex: 1 1 auto !important;
            padding: 0 6px !important;
            font-size: 11px !important;
        }

        .orb-table-footer,
        .dataTables_wrapper .row:last-child {
            flex-direction: column !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 12px 14px !important;
            text-align: center;
        }

        .dataTables_wrapper .dataTables_paginate {
            width: 100%;
            display: flex;
            justify-content: center;
            overflow-x: auto !important;
            padding-bottom: 3px !important;
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

        .orb-btn {
            padding: 0 12px;
            font-size: 12px;
        }
    }
</style>
@endsection

@section('_content')
<div class="orb-page">

    <div class="orb-hero">
        <div class="orb-hero-content">
            <div class="orb-hero-kicker">
                <i class="fas fa-layer-group"></i>
                HRMS Management
            </div>

            <h1>{{ $pageTitle }}</h1>

            <p>
                {{ $pageSubtitle ?? 'Manage HRMS records with filters, audit-friendly actions, exports and premium management workflow.' }}
            </p>
        </div>
    </div>

    @if(session('success') || session('status'))
    <div class="alert alert-success border-0 shadow-sm">
        {{ session('success') ?: session('status') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        {{ $errors->first() }}
    </div>
    @endif

    @if(!empty($summaryCards))
    <div class="orb-summary-grid">
        @foreach($summaryCards as $card)
        <div class="orb-summary-card">
            <div class="orb-summary-label">
                {{ $card['label'] }}
            </div>

            <div class="orb-summary-value">
                {{ $card['value'] }}
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="orb-card orb-table-card">
        <div class="orb-card-body">
            @php
            $lowerTitle = strtolower($pageTitle ?? '');
            $headerIcon = 'fa-database';

            if (strpos($lowerTitle, 'attendance') !== false) {
            $headerIcon = 'fa-calendar-check';
            } elseif (strpos($lowerTitle, 'employee') !== false || strpos($lowerTitle, 'staff') !== false) {
            $headerIcon = 'fa-users';
            } elseif (strpos($lowerTitle, 'report') !== false) {
            $headerIcon = 'fa-chart-bar';
            } elseif (strpos($lowerTitle, 'payroll') !== false || strpos($lowerTitle, 'salary') !== false || strpos($lowerTitle, 'work') !== false) {
            $headerIcon = 'fa-money-bill-wave';
            } elseif (strpos($lowerTitle, 'leave') !== false || strpos($lowerTitle, 'holiday') !== false) {
            $headerIcon = 'fa-plane-departure';
            } elseif (strpos($lowerTitle, 'document') !== false || strpos($lowerTitle, 'file') !== false) {
            $headerIcon = 'fa-file-alt';
            } elseif (strpos($lowerTitle, 'announcement') !== false || strpos($lowerTitle, 'notice') !== false) {
            $headerIcon = 'fa-bullhorn';
            } elseif (strpos($lowerTitle, 'approval') !== false || strpos($lowerTitle, 'request') !== false || strpos($lowerTitle, 'regular') !== false) {
            $headerIcon = 'fa-user-check';
            }
            @endphp

            <div class="orb-table-header">
                <div class="orb-table-head-left d-flex align-items-center" style="gap: 14px;">
                    <div class="orb-icon-box">
                        <i class="fas {{ $headerIcon }}"></i>
                    </div>
                    <div>
                        <h3 class="orb-table-title">{{ $pageTitle }}</h3>
                        <p class="orb-table-subtitle">Manage records, filters, actions and exports from one clean table.</p>
                    </div>
                </div>

                <div class="orb-table-head-right">
                    @if(!empty($canCreate))
                    <button type="button"
                        class="orb-btn orb-btn-gradient"
                        data-toggle="modal"
                        data-target="#createModal">
                        <i class="fas fa-plus"></i>
                        Add New
                    </button>
                    @endif
                </div>
            </div>


            @if(!empty($filters))
            <div class="orb-filter">
                <form method="GET" id="filterForm" class="orb-filter-form">
                    @foreach($filters as $filter)

                    <div class="orb-filter-item">

                        @if(($filter['type'] ?? 'text') === 'select')

                        <x-form.select 
                            :name="$filter['name']"
                            :label="$filter['label']"
                            :options="$filter['options'] ?? []"
                            :selected="request($filter['name'])"
                            :placeholder="$filter['placeholder'] ?? 'All'"
                            :searchable="($filter['name'] ?? '') === 'employee_id' || count($filter['options'] ?? []) > 5"
                            wrapper-class="mb-0"
                        />

                        @else

                        <div class="orb-form-group mb-0">
                            <label class="orb-form-label">
                                {{ $filter['label'] }}
                            </label>
                            <input type="{{ $filter['type'] ?? 'text' }}"
                                name="{{ $filter['name'] }}"
                                value="{{ request($filter['name']) }}"
                                class="form-control"
                                placeholder="{{ $filter['placeholder'] ?? '' }}">
                        </div>

                        @endif

                    </div>

                    @endforeach

                    <div class="orb-filter-item orb-filter-actions">
                        <button type="submit" class="orb-btn orb-btn-gradient orb-btn-search">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="{{ url()->current() }}" class="orb-btn orb-btn-light orb-btn-reset" title="Reset Filters">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
            @endif

            <div class="orb-table-tools">
                <div class="crud-dt-toolbar w-100 d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px; padding: 10px 18px;">
                    <div class="crud-dt-left d-flex align-items-center" style="gap: 8px;">
                        <label class="mb-0 d-flex align-items-center font-weight-bold text-muted" style="font-size: 13px; gap: 8px;">
                            Show
                            <select name="per_page" class="form-control form-control-sm custom-select" style="width: 75px; height: 34px; font-weight: 700; border-radius: 8px; padding: 0 8px;" onchange="var u = new URL(window.location.href); u.searchParams.set('per_page', this.value); u.searchParams.set('page', '1'); window.location.href = u.toString();">
                                @foreach([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                            entries
                        </label>
                    </div>
                    <div class="crud-dt-right d-flex align-items-center" style="gap: 8px;"></div>
                </div>
            </div>

            <div class="orb-table-wrap crud-table-responsive">

                <table class="table table-hover orb-table js-orb-datatable">

                    <thead>
                        <tr>

                            <th>S.No.</th>

                            @foreach($columns as $column)
                            <th>{{ $column['label'] }}</th>
                            @endforeach

                            @if(!empty($rowActions) || !empty($canEdit) || !empty($canDelete))
                            <th>Action</th>
                            @endif

                        </tr>
                    </thead>

                    <tbody>

                        @if(!empty($rows) && (method_exists($rows, 'count') ? $rows->count() : count($rows)) > 0)
                        @foreach($rows as $row)

                        <tr>

                            <td>
                                <strong>{{ $loop->iteration }}</strong>
                            </td>

                            @foreach($columns as $column)

                            @php
                            $value = data_get($row, $column['key']);
                            @endphp

                            <td>

                                @if(($column['type'] ?? '') === 'badge')

                                @php
                                $valLower = is_string($value) ? strtolower(trim($value)) : $value;
                                $badge =
                                in_array($valLower, ['approved','active','earned','processed',1,true,'wfo'], true)
                                ? 'orb-badge-success'
                                : (
                                in_array($valLower, ['pending','unprocessed'], true)
                                ? 'orb-badge-warning'
                                : (
                                in_array($valLower, ['rejected','cancelled','expired','inactive',0,false], true)
                                ? 'orb-badge-danger'
                                : 'orb-badge-primary'
                                )
                                );
                                @endphp

                                <span class="orb-badge {{ $badge }}">
                                    {{ is_bool($value) ? ($value ? 'Active' : 'Inactive') : (in_array($valLower, ['wfh','wfo'], true) ? strtoupper((string) $value) : ucfirst((string) $value)) }}
                                </span>

                                @elseif(($column['type'] ?? '') === 'date' && $value)

                                {{ \Carbon\Carbon::parse($value)->format('d M Y') }}

                                @elseif(($column['type'] ?? '') === 'datetime' && $value)

                                {{ \Carbon\Carbon::parse($value)->format('d M Y h:i A') }}

                                @elseif(($column['type'] ?? '') === 'json')

                                <pre class="mb-0 small"
                                    style="max-width:360px;white-space:pre-wrap">{{ json_encode(is_string($value) ? json_decode($value, true) : $value, JSON_PRETTY_PRINT) }}</pre>

                                @else

                                {{ $value ?? '-' }}

                                @endif

                            </td>

                            @endforeach

                            @if(!empty($rowActions) || !empty($canEdit) || !empty($canDelete))

                            <td>

                                <div class="dropdown">

                                    <button class="orb-action-btn"
                                        type="button"
                                        data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right">

                                        @if(!empty($canEdit))

                                        <button type="button"
                                            class="dropdown-item"
                                            data-toggle="modal"
                                            data-target="#editModal{{ data_get($row, 'id') }}">
                                            <i class="fas fa-edit mr-2 text-primary"></i>
                                            Edit
                                        </button>

                                        @endif

                                        @foreach($rowActions ?? [] as $action)
                                            @php
                                                $rowStatus = strtolower((string) data_get($row, 'status', ''));
                                                $actionLabel = strtolower((string) ($action['label'] ?? ''));
                                                $skipAction = false;

                                                if (isset($action['show_when_status'])) {
                                                    $allowedStatuses = (array) $action['show_when_status'];
                                                    $skipAction = !in_array($rowStatus, array_map('strtolower', $allowedStatuses), true);
                                                } elseif (in_array($actionLabel, ['approve', 'accept'])) {
                                                    $skipAction = ($rowStatus === 'approved' || $rowStatus === 'rejected' || $rowStatus === 'cancelled');
                                                } elseif (in_array($actionLabel, ['reject', 'decline'])) {
                                                    $skipAction = ($rowStatus === 'rejected' || $rowStatus === 'approved' || $rowStatus === 'cancelled');
                                                }
                                            @endphp

                                            @if(!$skipAction)
                                            <form method="POST"
                                                action="{{ route($action['route'], data_get($row, 'id')) }}"
                                                onsubmit="return confirm('{{ $action['confirm'] ?? 'Continue?' }}')">

                                                @csrf

                                                <button class="dropdown-item"
                                                    type="submit">
                                                    <i class="{{ $action['icon'] ?? 'fas fa-check' }} mr-2"></i>
                                                    {{ $action['label'] }}
                                                </button>

                                            </form>
                                            @endif
                                        @endforeach

                                        @if(!empty($canDelete))

                                        <form method="POST"
                                            action="{{ route($deleteRoute, data_get($row, 'id')) }}"
                                            onsubmit="return confirm('Delete this record?')">

                                            @csrf
                                            @method('DELETE')

                                            <button class="dropdown-item text-danger"
                                                type="submit">
                                                <i class="fas fa-trash mr-2"></i>
                                                Delete
                                            </button>

                                        </form>

                                        @endif

                                    </div>

                                </div>

                            </td>

                            @endif

                        </tr>

                        @endforeach
                        @endif

                    </tbody>

                </table>

            </div>

            @if(method_exists($rows, 'links'))
                {{ $rows->appends(request()->query())->links('vendor.pagination.orbo') }}
            @endif

        </div>
    </div>

    @if(!empty($canCreate))
    @include('hrms.shared.crud-modal', [
    'modalId' => 'createModal',
    'modalTitle' => 'Add '.$pageTitle,
    'action' => route($storeRoute),
    'method' => 'POST',
    'fields' => $formFields,
    'row' => null
    ])
    @endif

    @if(!empty($canEdit))
    @foreach($rows as $row)
    @include('hrms.shared.crud-modal', [
    'modalId' => 'editModal'.data_get($row, 'id'),
    'modalTitle' => 'Edit '.$pageTitle,
    'action' => route($updateRoute, data_get($row, 'id')),
    'method' => 'PUT',
    'fields' => $formFields,
    'row' => $row
    ])
    @endforeach
    @endif

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
    if (window.jQuery && $.fn.DataTable) {

        $('.js-orb-datatable').each(function() {
            var $table = $(this);

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
                        title: '{{ $branding["company_name"] ?? "OrboOne" }} — {{ $pageTitle }}',
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
                                            text: '{{ strtoupper($branding["company_name"] ?? "ORBOONE HRMS") }} — {{ strtoupper($pageTitle) }}',
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
                                objLayout['paddingLeft'] = function(i) { return 8; };
                                objLayout['paddingRight'] = function(i) { return 8; };
                                objLayout['paddingTop'] = function(i) { return 6; };
                                objLayout['paddingBottom'] = function(i) { return 6; };
                                doc.content[1].layout = objLayout;

                                var tableBody = doc.content[1].table.body;
                                var colCount = tableBody[0].length;
                                
                                for (var i = 0; i < colCount; i++) {
                                    tableBody[0][i].fillColor = '{{ $branding["primary_color"] ?? "#4B00E8" }}';
                                    tableBody[0][i].color = '#FFFFFF';
                                    tableBody[0][i].fontSize = 9;
                                    tableBody[0][i].bold = true;
                                }

                                for (var r = 1; r < tableBody.length; r++) {
                                    var rowColor = (r % 2 === 0) ? '#F8FAFC' : '#FFFFFF';
                                    for (var c = 0; c < colCount; c++) {
                                        tableBody[r][c].fontSize = 8.5;
                                        if (!tableBody[r][c].fillColor) {
                                            tableBody[r][c].fillColor = rowColor;
                                        }
                                    }
                                }

                                doc.content[1].table.widths = Array(colCount).fill('*');
                                if (colCount > 0) {
                                    doc.content[1].table.widths[0] = '6%';
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
                                        <h2>{{ $branding['company_name'] ?? 'OrboOne HRMS' }} — {{ $pageTitle }}</h2>
                                        <p>{{ $pageSubtitle ?? 'Generated Report & Audit View' }}</p>
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
        });

        // Initialize Select2 in modals
        $(document).on('shown.bs.modal', '.modal', function () {
            if (typeof $.fn.select2 !== 'undefined') {
                $(this).find('select.select2-searchable, select.js-searchable, select.select2-modal-searchable').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    $(this).select2({
                        dropdownParent: $(this).closest('.modal'),
                        placeholder: $(this).data('placeholder') || $(this).attr('placeholder') || $(this).find('option:first').text() || 'Search or select...',
                        allowClear: true,
                        width: '100%'
                    });
                });
            }
        });

    }
</script>
@endsection