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
        padding: 16px 12px 36px;
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* PREMIUM HEADER */
    .att-hero {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        border-radius: 30px;
        padding: 30px;
        margin-bottom: 18px;
        box-shadow: 0 18px 45px rgba(75, 0, 232, .20);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .att-hero::before {
        content: "";
        position: absolute;
        right: -80px;
        top: -110px;
        width: 360px;
        height: 360px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .12);
        pointer-events: none;
    }

    .att-hero-content {
        position: relative;
        z-index: 2;
    }

    .att-kicker {
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
        opacity: .9;
        margin-bottom: 10px;
        display: flex;
        gap: 9px;
        align-items: center;
    }

    .att-title {
        font-size: 34px;
        font-weight: 950;
        margin: 0;
        line-height: 1.1;
        color: #fff;
    }

    .att-subtitle {
        font-size: 15px;
        font-weight: 650;
        margin-top: 10px;
        opacity: .92;
        max-width: 850px;
    }

    .att-hero-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }

    .btn-action {
        border: 0;
        border-radius: 14px;
        padding: 13px 18px;
        font-weight: 950;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        text-decoration: none !important;
        white-space: nowrap;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-action-primary {
        background: #fff;
        color: #101828 !important;
        box-shadow: 0 10px 22px rgba(16, 24, 40, .08);
    }

    .btn-action-primary:hover {
        background: #F9F5FF;
        color: var(--orb-primary) !important;
    }

    .btn-action-outline {
        background: rgba(255, 255, 255, 0.12);
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .btn-action-outline:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
    }

    /* TOP STATISTICS GRID */
    .document-metric-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .att-metric {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 14px 14px 10px;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .055);
        position: relative;
        overflow: hidden;
        min-height: 92px;
        transition: all 0.2s ease;
    }

    .att-metric:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(16, 24, 40, .08);
    }

    .att-metric::after {
        content: "";
        position: absolute;
        right: -22px;
        top: -30px;
        width: 86px;
        height: 86px;
        border-radius: 50%;
        background: var(--metric-soft, #F4F2FF);
        pointer-events: none;
    }

    .att-metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        position: relative;
        z-index: 1;
    }

    .att-metric-value {
        font-size: 25px;
        font-weight: 950;
        color: #101828;
        line-height: 1;
    }

    .att-metric-icon {
        width: 36px;
        height: 36px;
        border-radius: 13px;
        background: var(--metric-soft, #F4F2FF);
        color: var(--metric-color, var(--orb-primary));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }

    .att-metric-label {
        font-size: 11px;
        font-weight: 950;
        color: #475467;
        text-transform: uppercase;
        margin-top: 14px;
        position: relative;
        z-index: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .att-metric-subtext {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 4px;
        display: none;
    }

    /* CARD STRUCTURE */
    .att-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        box-shadow: var(--orb-shadow);
        overflow: visible;
    }

    .att-section-head {
        padding: 18px 22px;
        border-bottom: 1px solid var(--orb-border);
        background: linear-gradient(180deg, #fff, #FAFBFF);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .att-section-title {
        font-size: 19px;
        font-weight: 950;
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
        font-size: 13px;
        color: var(--orb-muted);
        font-weight: 650;
        margin-top: 4px;
    }

    .att-head-badges {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .att-total-pill {
        border: 1px solid rgba(75, 0, 232, 0.2);
        background: rgba(75, 0, 232, 0.05);
        color: var(--orb-primary);
        border-radius: 12px;
        padding: 9px 12px;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }

    /* FILTER SECTION */
    .att-filter-panel {
        padding: 20px 24px;
        background: #fff;
        border-bottom: 1px solid var(--orb-border);
    }

    .doc-filter-grid {
        display: grid;
        grid-template-columns: 1.2fr 1.2fr 1.2fr 0.8fr 1.6fr;
        gap: 16px;
        align-items: flex-end;
    }

    .att-filter-group {
        display: flex;
        flex-direction: column;
    }

    .att-filter-group label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--orb-muted);
        margin-bottom: 6px;
        letter-spacing: 0.05em;
    }

    .att-filter-group .form-control,
    .att-filter-group .form-select {
        height: 40px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 13px;
        font-weight: 500;
        color: var(--orb-text);
        padding: 0 12px;
        box-shadow: none !important;
        background-color: #fff;
        transition: border-color 0.15s ease-in-out;
    }

    .att-filter-group .form-control:focus,
    .att-filter-group .form-select:focus {
        border-color: var(--orb-primary);
        background-color: #fff;
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
        justify-content: flex-end;
        padding-top: 16px;
        border-top: 1px dashed #e2e8f0;
    }

    .btn-filter-action {
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none !important;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .btn-filter-reset {
        background: #f1f5f9;
        color: #475569 !important;
    }

    .btn-filter-reset:hover {
        background: #e2e8f0;
    }

    .btn-filter-export {
        background: #fff;
        color: #334155 !important;
        border-color: #cbd5e1;
    }

    .btn-filter-export:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    /* TABLE LAYOUT */
    .att-table-wrap {
        padding: 0;
    }

    .att-table {
        width: 100% !important;
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
        padding: 14px 20px !important;
        border-bottom: 1px solid #e2e8f0 !important;
        letter-spacing: 0.05em;
        vertical-align: middle !important;
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
        padding: 16px 20px !important;
        vertical-align: middle !important;
        color: var(--orb-text);
    }

    /* TYPOGRAPHY */
    .doc-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--orb-text);
        text-decoration: none !important;
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
        gap: 12px;
    }

    .recipient-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #4f46e5;
        font-weight: 700;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #e0e7ff;
    }

    .recipient-name {
        font-weight: 700;
        font-size: 13.5px;
        color: var(--orb-text);
    }

    .recipient-meta {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 2px;
    }

    /* PREMIUM BADGES */
    .badge-doc-type {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
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
        padding: 5px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: capitalize;
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
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
    }

    .date-sub {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 2px;
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
        font-size: 13px;
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
        padding: 80px 40px;
        text-align: center;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin-bottom: 24px;
        border: 4px solid #fff;
        box-shadow: 0 0 0 4px #f1f5f9;
    }

    .empty-state-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--orb-text);
        margin-bottom: 8px;
    }

    .empty-state-text {
        font-size: 14px;
        color: var(--orb-muted);
        max-width: 360px;
        margin: 0 auto 24px;
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

    /* RESPONSIVE LAYOUTS */
    @media(max-width: 1400px) {
        .document-metric-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media(max-width: 1200px) {
        .doc-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media(max-width: 992px) {
        .document-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width: 768px) {
        .att-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 24px 30px;
            border-radius: 16px;
        }

        .document-metric-grid {
            grid-template-columns: 1fr;
        }

        .doc-filter-grid {
            grid-template-columns: 1fr;
        }

        .att-title {
            font-size: 26px;
        }
    }

    .no-caret::after {
        display: none !important;
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
                <button type="button" onclick="exportTableToCSV('generated_documents_export.csv')" class="btn-action btn-action-outline">
                    <i class="fas fa-file-export"></i> Export Documents
                </button>
                <button type="button" onclick="window.location.reload();" class="btn-action btn-action-outline">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                @if(Route::has('hrms.document-generation.generated.create'))
                <a href="{{ route('hrms.document-generation.generated.create') }}" class="btn-action btn-action-primary">
                    <i class="fas fa-plus-circle"></i> Create Document
                </a>
                @endif
            </div>
        </div>

        <!-- TOP STATISTICS CARDS -->
        <div class="document-metric-grid">
            <!-- Total Documents -->
            <div class="att-metric">
                <div class="att-metric-top">
                    <div class="att-metric-value">{{ $totalDocuments ?? 0 }}</div>
                    <div class="att-metric-icon" style="background: #eef2ff; color: #4f46e5;"><i class="fas fa-file-alt"></i></div>
                </div>
                <div class="att-metric-label">Total Documents</div>
                <div class="att-metric-subtext">All generated items</div>
            </div>

            <!-- Generated Today -->
            <div class="att-metric">
                <div class="att-metric-top">
                    <div class="att-metric-value">{{ $generatedToday ?? 0 }}</div>
                    <div class="att-metric-icon" style="background: #ecfdf5; color: #059669;"><i class="fas fa-calendar-day"></i></div>
                </div>
                <div class="att-metric-label">Generated Today</div>
                <div class="att-metric-subtext">Created in the last 24h</div>
            </div>

            <!-- Employee Documents -->
            <div class="att-metric">
                <div class="att-metric-top">
                    <div class="att-metric-value">{{ $employeeDocuments ?? 0 }}</div>
                    <div class="att-metric-icon" style="background: #f0fdf4; color: #16a34a;"><i class="fas fa-user-tie"></i></div>
                </div>
                <div class="att-metric-label">Employee Documents</div>
                <div class="att-metric-subtext">Linked to staff profiles</div>
            </div>

            <!-- Manual Documents -->
            <div class="att-metric">
                <div class="att-metric-top">
                    <div class="att-metric-value">{{ $manualDocuments ?? 0 }}</div>
                    <div class="att-metric-icon" style="background: #fffbeb; color: #d97706;"><i class="fas fa-file-signature"></i></div>
                </div>
                <div class="att-metric-label">Manual Documents</div>
                <div class="att-metric-subtext">Candidate / standalone files</div>
            </div>

            <!-- Emailed Documents -->
            <div class="att-metric">
                <div class="att-metric-top">
                    <div class="att-metric-value">{{ $emailedDocuments ?? 0 }}</div>
                    <div class="att-metric-icon" style="background: #fdf2f8; color: #db2777;"><i class="fas fa-paper-plane"></i></div>
                </div>
                <div class="att-metric-label">Emailed Documents</div>
                <div class="att-metric-subtext">Successfully sent via SMTP</div>
            </div>
        </div>

        <!-- TABLE SECTION CARD -->
        <div class="att-card document-generated-table-card">
            <div class="att-section-head">
                <div>
                    <h3 class="att-section-title">
                        <i class="fas fa-file-invoice"></i> Generated Documents List
                    </h3>
                    <p class="att-section-sub">Filters are attached with this table and auto-apply on change/search.</p>
                </div>
                <div class="att-head-badges d-flex align-items-center" style="gap: 8px;">
                    <a href="{{ route('hrms.document-generation.generated.index') }}" class="btn btn-light d-flex align-items-center justify-content-center gap-2" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 12.5px; font-weight: 600; color: #475569; padding: 0 14px; margin-right: 6px;">
                        <i class="fas fa-undo" style="margin-right: 6px;"></i> Reset Filters
                    </a>
                    <button type="button" onclick="exportTableToCSV('documents_filter_results.csv')" class="btn btn-light d-flex align-items-center justify-content-center gap-2" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 12.5px; font-weight: 600; color: #475569; padding: 0 14px; margin-right: 6px;">
                        <i class="fas fa-file-csv" style="margin-right: 6px;"></i> Export Results
                    </button>
                    <span class="att-total-pill d-inline-flex align-items-center" style="height: 38px; margin: 0; padding: 0 14px;">Total: {{ $documents->total() }}</span>
                </div>
            </div>

            <!-- FILTER SECTION -->
            <div class="att-filter-panel" style="padding-bottom: 24px;">
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
                            <select name="employee_id" id="filterEmployee" class="form-select">
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
                            <div class="d-flex gap-2 align-items-center">
                                <input type="date" name="start_date" id="filterStartDate" class="form-control" value="{{ request('start_date') }}" style="padding: 0 8px; font-size:12px;">
                                <span class="text-muted small">to</span>
                                <input type="date" name="end_date" id="filterEndDate" class="form-control" value="{{ request('end_date') }}" style="padding: 0 8px; font-size:12px;">
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
        const filterForm = document.getElementById('filterForm');
        const searchInput = document.getElementById('filterSearch');
        const selects = filterForm.querySelectorAll('select, input[type="date"]');

        // Submit form automatically on change of any select or date input
        selects.forEach(elem => {
            elem.addEventListener('change', function() {
                filterForm.submit();
            });
        });

        // Keyup debounce for search input to prevent immediate trigger on every keypress
        let debounceTimer;
        searchInput.addEventListener('keyup', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterForm.submit();
            }, 600);
        });

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