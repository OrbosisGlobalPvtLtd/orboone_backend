@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Pending Verifications')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@include('hrms.documents.partials.styles')
<style>
    /* PAGE CONTAINER */
    .dm-page {
        padding: 20px 16px 40px !important;
        background: #F6F7FB !important;
        min-height: calc(100vh - 90px) !important;
        font-family: 'Outfit', 'Inter', sans-serif !important;
        max-width: 1600px !important;
        margin: 0 auto !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    /* PREMIUM GRADIENT HERO */
    .dm-hero {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
        border-radius: 24px !important;
        padding: 28px 32px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 16px 40px rgba(75, 0, 232, 0.18) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 20px !important;
    }

    .dm-hero::before {
        content: "" !important;
        position: absolute !important;
        right: -60px !important;
        top: -90px !important;
        width: 320px !important;
        height: 320px !important;
        border-radius: 50% !important;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 70%) !important;
        pointer-events: none !important;
    }

    .dm-hero-left {
        position: relative !important;
        z-index: 2 !important;
        max-width: 700px !important;
    }

    .dm-hero .dm-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.14em !important;
        color: #E0E7FF !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .dm-hero h1 {
        font-size: clamp(22px, 3vw, 28px) !important;
        font-weight: 900 !important;
        margin: 0 0 6px 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
        text-transform: uppercase !important;
        line-height: 1.2 !important;
    }

    .dm-hero p {
        font-size: clamp(12.5px, 1.3vw, 14px) !important;
        color: #F3E8FF !important;
        margin: 0 !important;
        font-weight: 500 !important;
        line-height: 1.45 !important;
    }

    .dm-hero-actions {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
        position: relative !important;
        z-index: 2 !important;
        flex-shrink: 0 !important;
    }

    @media (max-width: 900px) {
        .dm-hero {
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 22px 24px !important;
        }
        .dm-hero-actions {
            width: 100% !important;
        }
        .dm-hero-actions .btn-pill {
            flex: 1 1 auto !important;
            justify-content: center !important;
        }
    }

    /* PILL BUTTONS */
    .btn-pill {
        height: 40px !important;
        padding: 0 18px !important;
        border-radius: 999px !important;
        font-size: 12.5px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        border: none !important;
        cursor: pointer !important;
        text-decoration: none !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        white-space: nowrap !important;
    }

    .btn-pill-white {
        background: #FFFFFF !important;
        color: var(--dm-primary, #4B00E8) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1) !important;
    }

    .btn-pill-white:hover {
        background: #F8FAFC !important;
        color: var(--dm-primary, #4B00E8) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.14) !important;
    }

    .btn-pill-glass {
        background: rgba(255, 255, 255, 0.18) !important;
        color: #FFFFFF !important;
        border: 1px solid rgba(255, 255, 255, 0.32) !important;
        backdrop-filter: blur(8px) !important;
    }

    .btn-pill-glass:hover {
        background: rgba(255, 255, 255, 0.28) !important;
        color: #FFFFFF !important;
        border-color: rgba(255, 255, 255, 0.45) !important;
        transform: translateY(-2px) !important;
    }

    .btn-pill-light {
        height: 36px !important;
        padding: 0 14px !important;
        border-radius: 999px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        background: #fff !important;
        border: 1px solid var(--dm-border) !important;
        color: var(--dm-muted) !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
    }

    .btn-pill-light:hover {
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        border-color: var(--dm-primary) !important;
        text-decoration: none !important;
        transform: translateY(-1px) !important;
    }

    /* KPI STAT CARDS */
    .dm-kpi-grid {
        display: grid !important;
        grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
        gap: 12px !important;
        margin-bottom: 20px !important;
    }

    .dm-kpi {
        min-height: 94px !important;
        padding: 16px 18px 14px !important;
        border-radius: 20px !important;
        border: 1px solid var(--dm-border) !important;
        background: #fff !important;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .045) !important;
        position: relative !important;
        overflow: hidden !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }

    .dm-kpi:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 16px 34px rgba(16, 24, 40, .08) !important;
    }

    .dm-kpi-top {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        position: relative !important;
        z-index: 1 !important;
    }

    .dm-kpi-icon {
        width: 38px !important;
        height: 38px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: var(--tone-soft) !important;
        color: var(--tone) !important;
        font-size: 15px !important;
        flex-shrink: 0 !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important;
    }

    .dm-kpi-value {
        font-size: 26px !important;
        line-height: 1 !important;
        font-weight: 950 !important;
        color: var(--dm-text) !important;
    }

    .dm-kpi-label {
        margin-top: 8px !important;
        font-size: 11px !important;
        color: var(--dm-muted) !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: .04em !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        position: relative !important;
        z-index: 1 !important;
    }

    .dm-kpi-line {
        position: absolute !important;
        left: 14px !important;
        right: 14px !important;
        bottom: 8px !important;
        height: 3px !important;
        border-radius: 999px !important;
        background: linear-gradient(90deg, var(--tone), transparent) !important;
    }

    .tone-success { --tone: #12B76A; --tone-soft: rgba(18, 183, 106, .12); }
    .tone-danger  { --tone: #F04438; --tone-soft: rgba(240, 68, 56, .12); }
    .tone-warning { --tone: #F79009; --tone-soft: rgba(247, 144, 9, .14); }
    .tone-purple  { --tone: #7A5AF8; --tone-soft: rgba(122, 90, 248, .13); }
    .tone-info    { --tone: #0EA5E9; --tone-soft: rgba(14, 165, 233, .13); }
    .tone-orange  { --tone: #EA580C; --tone-soft: rgba(234, 88, 12, .13); }

    @media(max-width: 1300px) {
        .dm-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    @media(max-width: 768px) {
        .dm-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media(max-width: 480px) {
        .dm-kpi-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* MAIN TABLE CARD */
    .dm-card {
        background: #fff !important;
        border: 1px solid var(--dm-border) !important;
        border-radius: 22px !important;
        box-shadow: 0 14px 35px rgba(16, 24, 40, .06) !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
    }

    .dm-table-header {
        padding: 18px 24px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 14px !important;
    }

    .dm-table-head-left {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
    }

    .dm-table-head-left .dm-icon-box {
        width: 44px !important;
        height: 44px !important;
        border-radius: 14px !important;
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        flex-shrink: 0 !important;
    }

    .dm-table-title {
        font-size: 16px !important;
        font-weight: 900 !important;
        color: var(--dm-text) !important;
        margin: 0 !important;
    }

    .dm-table-subtitle {
        font-size: 12px !important;
        color: var(--dm-muted) !important;
        margin: 2px 0 0 0 !important;
        font-weight: 500 !important;
    }

    /* FILTER GRID */
    .dm-filter-wrapper {
        padding: 16px 24px !important;
        border-top: 1px solid var(--dm-border) !important;
        border-bottom: 1px solid var(--dm-border) !important;
        background: #F8FAFC !important;
    }

    .dm-filter-grid {
        display: grid !important;
        grid-template-columns: 1.4fr 1.2fr 1fr 1fr auto !important;
        gap: 12px !important;
        align-items: flex-end !important;
    }

    .dm-filter-item {
        display: flex !important;
        flex-direction: column !important;
        min-width: 0 !important;
    }

    .dm-filter-label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--dm-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .dm-input-icon-wrap {
        position: relative !important;
        width: 100% !important;
    }

    .dm-input-icon {
        position: absolute !important;
        left: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: var(--dm-muted) !important;
        font-size: 13px !important;
        pointer-events: none !important;
    }

    .dm-filter-control {
        height: 38px !important;
        border-radius: 9px !important;
        border: 1px solid var(--dm-border) !important;
        background: #fff !important;
        padding: 0 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--dm-text) !important;
        width: 100% !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    .dm-filter-control.with-icon {
        padding-left: 34px !important;
    }

    .dm-filter-control:focus {
        border-color: var(--dm-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    .dm-filter-actions {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .btn-filter-search {
        height: 38px !important;
        border-radius: 9px !important;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
        color: #fff !important;
        border: none !important;
        font-weight: 800 !important;
        font-size: 12.5px !important;
        padding: 0 16px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.18) !important;
        white-space: nowrap !important;
    }

    .btn-filter-search:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.28) !important;
        color: #fff !important;
    }

    .btn-filter-reset {
        height: 38px !important;
        width: 38px !important;
        border-radius: 9px !important;
        background: #fff !important;
        border: 1px solid var(--dm-border) !important;
        color: var(--dm-muted) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
        flex-shrink: 0 !important;
    }

    .btn-filter-reset:hover {
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        border-color: var(--dm-primary) !important;
    }

    @media (max-width: 1080px) {
        .dm-filter-grid {
            grid-template-columns: 1fr 1fr !important;
        }
        .dm-filter-actions {
            grid-column: span 2 !important;
            justify-content: flex-end !important;
        }
    }

    @media (max-width: 580px) {
        .dm-filter-grid {
            grid-template-columns: 1fr !important;
        }
        .dm-filter-actions {
            grid-column: span 1 !important;
        }
        .dm-filter-actions .btn-filter-search {
            flex: 1 !important;
        }
    }

    /* DATA TABLE TOOLBAR */
    .dm-table-toolbar-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 14px 24px !important;
        border-bottom: 1px solid var(--dm-border) !important;
        background: #fff !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }

    #employeeLengthBox .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--dm-muted) !important;
        white-space: nowrap !important;
    }

    #employeeLengthBox .dataTables_length select {
        width: 70px !important;
        height: 34px !important;
        border-radius: 9px !important;
        border: 1px solid var(--dm-border) !important;
        padding: 4px 8px !important;
        outline: none !important;
        font-weight: 700 !important;
    }

    .dt-buttons {
        display: flex !important;
        gap: 6px !important;
        flex-wrap: wrap !important;
    }

    .dt-buttons .btn {
        height: 34px !important;
        padding: 0 14px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border-radius: 8px !important;
        border: 1px solid var(--dm-border) !important;
        background: #fff !important;
        color: var(--dm-muted) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03) !important;
        transition: all 0.2s ease !important;
    }

    .dt-buttons .btn:hover {
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        border-color: var(--dm-primary) !important;
        transform: translateY(-1px) !important;
    }

    /* TABLE */
    .dm-table-wrap {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .dm-table {
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .dm-table thead th {
        background: #F8FAFC !important;
        color: var(--dm-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 13px 18px !important;
        border-top: none !important;
        border-bottom: 1px solid var(--dm-border) !important;
        white-space: nowrap !important;
    }

    .dm-table tbody td {
        padding: 14px 18px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--dm-text) !important;
        border-bottom: 1px solid var(--dm-border) !important;
        vertical-align: middle !important;
    }

    .dm-table tbody tr:hover td {
        background: #FDFDFF !important;
    }

    /* AVATAR */
    .dm-avatar-wrapper {
        width: 38px !important;
        height: 38px !important;
        border-radius: 12px !important;
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 900 !important;
        font-size: 13px !important;
        border: 1px solid rgba(75, 0, 232, 0.15) !important;
        flex-shrink: 0 !important;
    }

    /* BADGES */
    .dm-num-badge {
        font-size: 11.5px !important;
        font-weight: 800 !important;
        padding: 5px 10px !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 32px !important;
    }

    /* ACTION BUTTONS */
    .dm-action-btn {
        height: 32px !important;
        padding: 0 12px !important;
        border-radius: 999px !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        white-space: nowrap !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important;
    }

    .dm-action-primary {
        background: var(--dm-soft, #F4F2FF) !important;
        border: 1px solid rgba(75, 0, 232, 0.18) !important;
        color: var(--dm-primary, #4B00E8) !important;
    }

    .dm-action-primary:hover {
        background: var(--dm-primary, #4B00E8) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    .dm-action-secondary {
        background: #F1F5F9 !important;
        border: 1px solid #CBD5E1 !important;
        color: #475569 !important;
    }

    .dm-action-secondary:hover {
        background: #E2E8F0 !important;
        color: #1E293B !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    /* FOOTER & PAGINATION */
    .dm-table-footer-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 14px 24px !important;
        border-top: 1px solid var(--dm-border) !important;
        background: #fff !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }

    #employeeInfoBox {
        font-size: 12px !important;
        font-weight: 700 !important;
        color: var(--dm-muted) !important;
    }

    .pagination {
        display: flex !important;
        gap: 4px !important;
        margin: 0 !important;
        list-style: none !important;
    }

    .page-item .page-link {
        height: 32px !important;
        padding: 0 12px !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        color: var(--dm-muted) !important;
        border: 1px solid var(--dm-border) !important;
        background: #fff !important;
        transition: all 0.2s ease !important;
    }

    .page-item:hover .page-link {
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        border-color: var(--dm-primary) !important;
    }

    .page-item.active .page-link {
        background: var(--dm-primary) !important;
        color: #fff !important;
        border-color: var(--dm-primary) !important;
    }

    .page-item.disabled .page-link {
        opacity: 0.5 !important;
        pointer-events: none !important;
    }

    /* TOGGLE SWITCH */
    .verify-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        margin: 0;
    }

    .verify-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .verify-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #E4E7EC;
        border-radius: 999px;
        transition: .2s;
    }

    .verify-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background: #fff;
        border-radius: 50%;
        transition: .2s;
        box-shadow: 0 2px 5px rgba(16, 24, 40, .18);
    }

    .verify-switch input:checked+.verify-slider {
        background: #12B76A;
    }

    .verify-switch input:checked+.verify-slider:before {
        transform: translateX(20px);
    }
</style>
@endsection

@section('_content')
<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero">
        <div class="dm-hero-left">
            <div class="dm-kicker">
                <i class="fas fa-file-signature"></i> HRMS &bull; DOCUMENT VERIFICATION
            </div>
            <h1>HR Document Review</h1>
            <p>Manage employee KYC, mandatory documents, verification lifecycle and compliance tracking.</p>
        </div>
        <div class="dm-hero-actions">
            <a href="{{ route('documents.types.index') }}" class="btn-pill btn-pill-glass">
                <i class="fas fa-file-alt"></i> Document Types
            </a>
            <a href="{{ route('documents.policies.index') }}" class="btn-pill btn-pill-glass">
                <i class="fas fa-folder-open"></i> Company Documents & Policies
            </a>
            {{-- <button type="button" onclick="$('.buttons-excel').click()" class="btn-pill btn-pill-white">
                <i class="fas fa-file-export"></i> Export Report
            </button> --}}
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    @php
    $statsTotalDocs = 0;
    $statsVerified = 0;
    $statsPending = 0;
    $statsRejected = 0;
    $statsMissing = 0;
    $statsExpiring = 0;
    if(isset($employees) && count($employees) > 0) {
        $statsTotalDocs = collect($employees->items())->sum('doc_required');
        $statsVerified = collect($employees->items())->sum('doc_verified');
        $statsPending = collect($employees->items())->sum('doc_pending');
        $statsRejected = collect($employees->items())->sum('doc_rejected');
        $statsMissing = collect($employees->items())->sum('doc_missing');
        $statsExpiring = collect($employees->items())->sum('doc_expiring');
    }
    @endphp

    <!-- Compact Summary Cards -->
    <div class="dm-kpi-grid">
        <div class="dm-kpi tone-purple">
            <div class="dm-kpi-top">
                <div class="dm-kpi-value">{{ isset($employees) ? $employees->total() : 0 }}</div>
                <div class="dm-kpi-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="dm-kpi-label">Total Employees</div>
            <div class="dm-kpi-line"></div>
        </div>
        <div class="dm-kpi tone-warning">
            <div class="dm-kpi-top">
                <div class="dm-kpi-value">{{ $statsPending }}</div>
                <div class="dm-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="dm-kpi-label">Pending Docs</div>
            <div class="dm-kpi-line"></div>
        </div>
        <div class="dm-kpi tone-success">
            <div class="dm-kpi-top">
                <div class="dm-kpi-value">{{ $statsVerified }}</div>
                <div class="dm-kpi-icon"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="dm-kpi-label">Verified Docs</div>
            <div class="dm-kpi-line"></div>
        </div>
        <div class="dm-kpi tone-danger">
            <div class="dm-kpi-top">
                <div class="dm-kpi-value">{{ $statsRejected }}</div>
                <div class="dm-kpi-icon"><i class="fas fa-times-circle"></i></div>
            </div>
            <div class="dm-kpi-label">Rejected</div>
            <div class="dm-kpi-line"></div>
        </div>
        <div class="dm-kpi tone-info">
            <div class="dm-kpi-top">
                <div class="dm-kpi-value">{{ $statsMissing }}</div>
                <div class="dm-kpi-icon"><i class="fas fa-file-excel"></i></div>
            </div>
            <div class="dm-kpi-label">Missing Docs</div>
            <div class="dm-kpi-line"></div>
        </div>
        <div class="dm-kpi tone-orange">
            <div class="dm-kpi-top">
                <div class="dm-kpi-value">{{ $statsExpiring }}</div>
                <div class="dm-kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="dm-kpi-label">Expiring Soon</div>
            <div class="dm-kpi-line"></div>
        </div>
    </div>

    <!-- Compliance Health Strip
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: #fff; overflow: hidden;">
        <div class="card-body p-0 border" style="border-radius: 12px; border-color: var(--dm-border) !important;">
            <div class="row m-0">
                <div class="col-12 col-md-6 col-lg-3 dm-health-segment">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dm-icon-box" style="width: 38px; height: 38px; background: rgba(75, 0, 232, 0.08); color: var(--dm-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-size: 11px; font-weight: 800; color: var(--dm-muted); text-transform: uppercase; margin-bottom: 4px;">Overall Compliance</div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px; border-radius: 4px; background: #f1f5f9;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $statsTotalDocs > 0 ? round(($statsVerified / $statsTotalDocs) * 100) : 0 }}%; border-radius: 4px;"></div>
                                </div>
                                <div class="text-success" style="font-size: 14px; font-weight: 900;">{{ $statsTotalDocs > 0 ? round(($statsVerified / $statsTotalDocs) * 100) : 0 }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 dm-health-segment">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dm-icon-box" style="width: 38px; height: 38px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--dm-muted); text-transform: uppercase;">Pending HR Action</div>
                            <div class="text-dark" style="font-weight: 900; font-size: 18px; line-height: 1;">{{ isset($employees) ? $employees->where('doc_status', '!=', 'verified')->count() : 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 dm-health-segment">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dm-icon-box" style="width: 38px; height: 38px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--dm-muted); text-transform: uppercase;">Missing Mandatory</div>
                            <div class="text-dark" style="font-weight: 900; font-size: 18px; line-height: 1;">{{ $statsMissing }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 dm-health-segment">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dm-icon-box" style="width: 38px; height: 38px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--dm-muted); text-transform: uppercase;">Recent Updates</div>
                            <div class="text-dark" style="font-weight: 900; font-size: 18px; line-height: 1;">{{ $statsExpiring }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Main Card -->
    <div class="dm-card shadow-sm">
        <!-- Card Header with circular icon -->
        <div class="dm-table-header">
            <div class="dm-table-head-left">
                <div class="dm-icon-box"><i class="fas fa-tasks"></i></div>
                <div>
                    <h5 class="dm-table-title">Compliance Matrix</h5>
                    <p class="dm-table-subtitle">Manage employee document verifications, statuses, and compliance tracking.</p>
                </div>
            </div>
            {{-- <div class="dm-table-head-right">
                <a href="{{ route('documents.hr.index') }}" class="btn-pill-light">
                    <i class="fas fa-undo"></i> Reset Filters
                </a>
            </div> --}}
        </div>

        <!-- Filter Grid Attached inside card -->
        <form method="GET" action="{{ route('documents.hr.index') }}" id="docFilterForm">
            <div class="dm-filter-wrapper">
                <div class="dm-filter-grid">
                    <div class="dm-filter-item">
                        <label class="dm-filter-label">Search Employee</label>
                        <div class="dm-input-icon-wrap">
                            <i class="fas fa-search dm-input-icon"></i>
                            <input type="text" name="employee" id="filterSearch" value="{{ request('employee') }}" class="dm-filter-control with-icon" placeholder="Search name, code, email...">
                        </div>
                    </div>

                    <div class="dm-filter-item">
                        <label class="dm-filter-label">Department</label>
                        <select name="department_id" id="filterDepartment" class="dm-filter-control">
                            <option value="">All Departments</option>
                            @if(isset($departments) && count($departments) > 0)
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="dm-filter-item">
                        <label class="dm-filter-label">Doc Type</label>
                        <select name="document_type_id" id="filterDocumentType" class="dm-filter-control">
                            <option value="">All Types</option>
                            @if(isset($documentTypes) && count($documentTypes) > 0)
                            @foreach($documentTypes as $type)
                            <option value="{{ $type->id }}" {{ request('document_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                            @else
                            <option value="" disabled>No document types found</option>
                            @endif
                        </select>
                    </div>

                    <div class="dm-filter-item">
                        <label class="dm-filter-label">Status</label>
                        <select name="status" id="filterStatus" class="dm-filter-control">
                            <option value="">Pending Only</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Docs</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        </select>
                    </div>

                    <div class="dm-filter-actions">
                        <button type="submit" class="btn-filter-search">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="{{ route('documents.hr.index') }}" class="btn-filter-reset" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- DataTable Toolbar (Length and buttons appended here) -->
        <div class="dm-table-toolbar-row">
            <div id="employeeLengthBox"></div>
            <div id="employeeExportButtons"></div>
        </div>

        <div class="dm-table-wrap table-responsive">
            <table id="employeeDocTable" class="table dm-table mb-0" style="min-width: 1250px;">
                <thead>
                    <tr>
                        <th style="padding-left: 24px;">Employee</th>
                        <th class="text-center">Required Docs</th>
                        <th class="text-center">Pending</th>
                        <th class="text-center">Verified</th>
                        <th class="text-center">Rejected</th>
                        <th class="text-center">Missing</th>
                        <th class="text-center">Expiring</th>
                        <th class="text-center">Profile Status</th>
                        <th class="text-center">Verify All</th>
                        <th class="text-center" style="width: 220px; min-width: 220px; padding-right: 24px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td style="padding-left: 24px;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="dm-avatar-wrapper shadow-sm">
                                    {{ strtoupper(substr($employee->user->name ?? 'E', 0, 1)) }}
                                </div>
                                <div style="line-height: 1.35;">
                                    <div style="font-weight: 800; color: var(--dm-text); font-size: 13.5px; white-space: nowrap;" title="{{ $employee->user->name ?? '-' }}">
                                        {{ $employee->user->name ?? '-' }}
                                    </div>
                                    <div style="font-size: 11px; color: var(--dm-muted); font-weight: 700; margin-top: 2px;">
                                        <span style="color: var(--dm-primary); font-weight: 800;">{{ $employee->employee_code ?? '-' }}</span> &bull; {{ $employee->designation->name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="dm-num-badge" style="background: var(--dm-soft); color: var(--dm-primary);">
                                {{ $employee->doc_required }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="dm-num-badge" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                                {{ $employee->doc_pending }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="dm-num-badge" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                                {{ $employee->doc_verified }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="dm-num-badge" style="background: rgba(239, 68, 68, 0.12); color: #dc2626;">
                                {{ $employee->doc_rejected }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="dm-num-badge" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5;">
                                {{ $employee->doc_missing }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="dm-num-badge" style="background: rgba(249, 115, 22, 0.12); color: #ea580c;">
                                {{ $employee->doc_expiring }}
                            </span>
                        </td>

                        <td class="text-center">
                            @php
                            $profStatus = $employee->profile->profile_status ?? 'pending';
                            $profStatusBg = 'rgba(245, 158, 11, 0.12)';
                            $profStatusText = '#d97706';
                            if($profStatus === 'approved') {
                                $profStatusBg = 'rgba(16, 185, 129, 0.12)';
                                $profStatusText = '#059669';
                            } elseif($profStatus === 'rejected') {
                                $profStatusBg = 'rgba(239, 68, 68, 0.12)';
                                $profStatusText = '#dc2626';
                            }
                            @endphp
                            <span class="badge" style="background: {{ $profStatusBg }}; color: {{ $profStatusText }}; font-size: 11px; font-weight: 800; padding: 5px 10px; border-radius: 6px; text-transform: capitalize;">
                                {{ $profStatus }}
                            </span>
                        </td>

                        <td class="text-center">
                            @if($employee->doc_status !== 'verified')
                            <form action="{{ route('documents.hr.verify_employee', $employee->id) }}"
                                method="POST"
                                class="verify-all-form mb-0 d-flex justify-content-center">
                                @csrf
                                <label class="verify-switch mb-0" title="Verify all documents">
                                    <input type="checkbox" class="verify-all-toggle">
                                    <span class="verify-slider"></span>
                                </label>
                            </form>
                            @else
                            <span class="badge shadow-sm" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25); color: #059669; font-size: 10.5px; font-weight: 800; padding: 5px 10px; border-radius: 6px;">
                                <i class="fas fa-check-double mr-1"></i> Verified
                            </span>
                            @endif
                        </td>

                        <td class="text-center" style="padding-right: 24px;">
                            <div class="d-flex align-items-center justify-content-center gap-2" style="white-space: nowrap;">
                                <a href="{{ route('documents.hr.show', $employee->user_id) }}" class="dm-action-btn dm-action-primary">
                                    <i class="fas fa-folder-open"></i> View Docs
                                </a>
                                <a href="{{ route('hrms.employees.profile.view', $employee->id) }}" class="dm-action-btn dm-action-secondary">
                                    <i class="fas fa-external-link-alt"></i> Profile
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- DataTable Footer (Pagination & info appended here) -->
        <div class="dm-table-footer-row">
            <div id="employeeInfoBox"></div>
            <div id="employeePaginationBox"></div>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        function cleanExportText(data) {
            return $('<div>').html(data).text().replace(/\s+/g, ' ').trim();
        }

        let table = $('#employeeDocTable').DataTable({
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'All']
            ],
            order: [
                [0, 'asc']
            ],
            dom: "<'d-none'lB>" +
                "<'row'<'col-12'tr>>" +
                "<'d-none'i p>",
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    title: 'Employee Document Verifications',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                    title: 'Employee Document Verifications',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    title: 'Employee Document Verifications',
                    className: 'btn btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    title: 'Employee Document Verifications',
                    className: 'btn btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        format: {
                            body: function(data) {
                                return cleanExportText(data);
                            }
                        }
                    }
                }
            ],
            language: {
                emptyTable: 'No pending employee documents found',
                zeroRecords: 'No matching employee found'
            },
            initComplete: function() {
                $('.dataTables_length').appendTo('#employeeLengthBox');
                $('.dt-buttons').appendTo('#employeeExportButtons');
                $('.dataTables_info').appendTo('#employeeInfoBox');
                $('.dataTables_paginate').appendTo('#employeePaginationBox');
            }
        });

        $('.verify-all-toggle').on('change', function() {
            const checkbox = this;
            const form = $(checkbox).closest('form');

            if (checkbox.checked) {
                if (confirm('All documents for this employee will be verified and removed from the pending list. Continue?')) {
                    form.submit();
                } else {
                    checkbox.checked = false;
                }
            }
        });
    });
</script>
@endsection