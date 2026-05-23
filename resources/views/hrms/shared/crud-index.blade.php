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
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    html,
    body {
        background: var(--orb-bg) !important;
        overflow-x: hidden !important;
    }

    .orb-page {
        width: 100%;
        max-width: 100%;
        min-height: calc(100vh - 80px);
        padding: 22px 24px 34px;
        background: var(--orb-bg);
        overflow-x: hidden;
    }

    /* HERO */
    .orb-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 26px 28px;
        color: #fff;
        box-shadow: 0 20px 45px rgba(75, 0, 232, .22);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin: 0 0 18px;
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
        padding: 12px 18px;
    }

    .orb-filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        align-items: end;
        margin: 0;
        width: 100%;
    }

    .orb-filter-item {
        min-width: 0;
        margin: 0 !important;
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
        padding: 0 11px;
        background-color: #fff;
    }

    .orb-filter label,
    .orb-form-label {
        font-size: 10.5px;
        text-transform: uppercase;
        color: var(--orb-muted);
        font-weight: 900;
        margin-bottom: 5px;
        letter-spacing: .04em;
        display: block;
    }

    .orb-filter .form-control:focus,
    .orb-filter .custom-select:focus {
        border-color: rgba(75, 0, 232, .30);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
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
        color: #4B00E8 !important;
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
        overflow: visible !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding: 0 !important;
        font-size: 12px;
        font-weight: 700;
        color: var(--orb-muted);
        white-space: nowrap;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding: 0 !important;
        margin: 0 !important;
        white-space: nowrap;
        overflow-x: visible !important;
    }

    .dataTables_wrapper .paginate_button {
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        margin: 0 2px !important;
        padding: 5px 10px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
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
    .orb-modal .custom-select {
        border-radius: 14px;
        border: 1px solid var(--orb-border);
        min-height: 44px;
        font-size: 13px;
    }

    .orb-modal .form-control:focus,
    .orb-modal .custom-select:focus {
        border-color: rgba(75, 0, 232, .28);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    @media(max-width: 1199px) {
        .orb-page {
            padding: 18px 18px 28px;
        }

        .orb-filter-form {
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        }
    }

    @media(max-width: 991px) {
        .orb-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .orb-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .orb-hero-actions,
        .orb-hero-actions .orb-btn {
            width: 100%;
        }
    }

    @media(max-width: 768px) {
        .orb-page {
            padding: 14px 12px 22px;
        }

        .orb-hero {
            padding: 18px;
            border-radius: 20px;
        }

        .orb-hero h1 {
            font-size: 22px;
        }

        .orb-summary-grid {
            grid-template-columns: 1fr;
        }

        .orb-table-header {
            flex-direction: column;
            align-items: stretch;
            padding: 14px;
        }

        .orb-table-head-right {
            justify-content: space-between;
            width: 100%;
        }

        .orb-filter {
            padding: 12px 14px;
        }

        .orb-filter-form {
            grid-template-columns: 1fr;
        }

        .orb-table-tools {
            padding: 10px 14px;
        }

        .crud-dt-toolbar,
        .orb-dt-toolbar {
            flex-wrap: wrap !important;
            align-items: flex-start !important;
        }

        .crud-dt-toolbar .crud-dt-left,
        .crud-dt-toolbar .crud-dt-right,
        .orb-dt-toolbar .dt-left,
        .orb-dt-toolbar .dt-right {
            width: 100% !important;
        }

        .dataTables_wrapper .dt-buttons {
            justify-content: flex-start !important;
            flex-wrap: wrap !important;
            margin-top: 6px !important;
        }

        .orb-table-footer,
        .dataTables_wrapper .row:last-child {
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 12px 14px 14px !important;
        }

        .dataTables_wrapper .dataTables_paginate {
            width: 100%;
            overflow-x: auto !important;
            padding-bottom: 3px !important;
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

        @if(!empty($canCreate))
        <div class="orb-hero-actions">
            <button type="button"
                class="orb-btn orb-btn-primary"
                data-toggle="modal"
                data-target="#createModal">
                <i class="fas fa-plus"></i>
                Add New
            </button>
        </div>
        @endif
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
                    <span class="orb-table-count">
                        <i class="fas fa-database"></i>
                        Total: {{ method_exists($rows, 'total') ? $rows->total() : collect($rows ?? [])->count() }}
                    </span>

                    @if(!empty($filters))
                    <a href="{{ url()->current() }}"
                        class="orb-btn orb-btn-light orb-btn-reset">
                        <i class="fas fa-undo"></i>
                        Reset
                    </a>
                    @endif
                </div>
            </div>


            @if(!empty($filters))
            <div class="orb-filter">
                <form method="GET" id="filterForm" class="orb-filter-form">
                    @foreach($filters as $filter)

                    <div class="orb-filter-item">

                        <label>
                            {{ $filter['label'] }}
                        </label>

                        @if(($filter['type'] ?? 'text') === 'select')

                        <select name="{{ $filter['name'] }}"
                            class="form-control js-auto-filter">

                            <option value="">
                                {{ $filter['placeholder'] ?? 'All' }}
                            </option>

                            @foreach($filter['options'] as $value => $label)
                            <option value="{{ $value }}"
                                {{ (string) request($filter['name']) === (string) $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>

                        @else

                        <input type="{{ $filter['type'] ?? 'text' }}"
                            name="{{ $filter['name'] }}"
                            value="{{ request($filter['name']) }}"
                            class="form-control js-auto-filter"
                            placeholder="{{ $filter['placeholder'] ?? '' }}">

                        @endif

                    </div>

                    @endforeach
                </form>
            </div>
            @endif

            <div class="orb-table-tools"></div>

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
                                $badge =
                                in_array($value, ['approved','active','earned','processed',1,true], true)
                                ? 'orb-badge-success'
                                : (
                                in_array($value, ['pending','unprocessed'], true)
                                ? 'orb-badge-warning'
                                : (
                                in_array($value, ['rejected','cancelled','expired','inactive',0,false], true)
                                ? 'orb-badge-danger'
                                : 'orb-badge-primary'
                                )
                                );
                                @endphp

                                <span class="orb-badge {{ $badge }}">
                                    {{ is_bool($value) ? ($value ? 'Active' : 'Inactive') : ucfirst((string) $value) }}
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
            <div class="mt-3">
                {{ $rows->appends(request()->query())->links() }}
            </div>
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
    document.querySelectorAll('.js-auto-filter').forEach(function(input) {

        input.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        if (input.tagName === 'INPUT') {

            let timeout = null;

            input.addEventListener('keyup', function() {

                clearTimeout(timeout);

                timeout = setTimeout(function() {
                    document.getElementById('filterForm').submit();
                }, 500);

            });

        }

    });

    if (window.jQuery && $.fn.DataTable) {

        $('.js-orb-datatable').each(function() {
            var $table = $(this);

            var dataTable = $table.DataTable({
                paging: true,
                searching: false,
                info: true,
                lengthChange: true,
                responsive: false,
                autoWidth: false,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [],

                language: {
                    emptyTable: 'No records found.',
                    zeroRecords: 'No matching records found.'
                },

                dom: '<"crud-dt-toolbar"<"crud-dt-left"l><"crud-dt-right"B>>rt<"orb-table-footer"ip>',

                buttons: [
                    { extend: 'csvHtml5', text: '<i class="fas fa-file-csv text-muted"></i> CSV', className: 'crud-export-btn' },
                    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel text-success"></i> Excel', className: 'crud-export-btn' },
                    { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf text-danger"></i> PDF', className: 'crud-export-btn' },
                    { extend: 'print', text: '<i class="fas fa-print text-primary"></i> Print', className: 'crud-export-btn' }
                ],

                initComplete: function() {
                    var $wrapper = $table.closest('.dataTables_wrapper');
                    var $toolbar = $wrapper.find('.crud-dt-toolbar').first();
                    var $toolsTarget = $table.closest('.orb-card').find('.orb-table-tools').first();

                    if ($toolsTarget.length && $toolbar.length) {
                        $toolsTarget.empty().append($toolbar);
                    }

                    console.log(
                      'TH:', $table.find('thead tr:first th').length,
                      'TD first row:', $table.find('tbody tr:first td').length
                    );
                }
            });
        });

    }
</script>
@endsection