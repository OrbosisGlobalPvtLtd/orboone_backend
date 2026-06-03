@extends('layouts.panel', ['active' => 'announcements'])

@section('page_title', 'Notice & Announcements')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

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

    .ann-page {
        background: var(--orb-bg);
        padding: 24px;
        min-height: calc(100vh - 90px);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .ann-hero {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 32px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 10px 30px rgba(75, 0, 232, 0.15);
        margin-bottom: 24px;
    }

    .ann-kicker {
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.9;
        margin-bottom: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .ann-title {
        font-size: 28px;
        font-weight: 900;
        margin: 0;
        line-height: 1.15;
    }

    .ann-subtitle {
        font-size: 13px;
        font-weight: 600;
        margin: 8px 0 0;
        opacity: 0.85;
    }

    .ann-hero-btn {
        height: 42px;
        border-radius: 999px !important;
        padding: 0 24px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        color: var(--orb-primary) !important;
        background: #fff;
        box-shadow: 0 4px 12px rgba(255, 255, 255, 0.15);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .ann-hero-btn:hover {
        opacity: 0.95;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(255, 255, 255, 0.25);
    }

    .ann-hero-btn-outline {
        background: rgba(255, 255, 255, 0.18) !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
    }

    /* Premium Metric Cards */
    .stat-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 16px;
        box-shadow: var(--orb-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(16, 24, 40, .09);
    }

    /* Attached filter style */
    .ann-filters-wrapper {
        padding: 18px 24px;
        border-top: 1px solid var(--orb-border);
        border-bottom: 1px solid var(--orb-border);
        background: #fafafa;
    }

    /* Custom Table Card */
    .ann-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .ann-card-header {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        background: #fff;
    }

    .ann-head-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .ann-icon-box {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .ann-card-title {
        font-size: 16px;
        font-weight: 850;
        color: var(--orb-text);
        margin: 0;
    }

    .ann-card-subtitle {
        font-size: 12px;
        font-weight: 650;
        color: var(--orb-muted);
        margin: 4px 0 0;
    }

    .form-select, .form-control {
        height: 40px !important;
        border-radius: 12px !important;
        border: 1px solid var(--orb-border) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        background-color: #fff !important;
        transition: all 0.25s ease;
    }

    .form-select:focus, .form-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.08) !important;
    }

    /* DataTable Toolbar inside the card */
    .dm-table-toolbar-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 16px 24px !important;
        border-bottom: 1px solid var(--orb-border) !important;
        background: #fff !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }

    .dataTables_length,
    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--orb-muted) !important;
        white-space: nowrap !important;
    }

    .dataTables_length select {
        width: 70px !important;
        height: 34px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        padding: 4px 8px !important;
        outline: none !important;
        font-weight: 700 !important;
    }

    .dt-buttons {
        display: flex !important;
        gap: 6px !important;
    }

    .dt-buttons .btn {
        height: 34px !important;
        padding: 0 14px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-muted) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: none !important;
        transition: all 0.2s ease !important;
    }

    .dt-buttons .btn:hover {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        border-color: var(--orb-primary) !important;
    }

    .dm-table-footer-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 16px 24px !important;
        border-top: 1px solid var(--orb-border) !important;
        background: #fff !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }

    .dataTables_info {
        font-size: 12px !important;
        font-weight: 700 !important;
        color: var(--orb-muted) !important;
        margin: 0 !important;
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
        border-radius: 9px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        transition: all 0.2s ease !important;
    }

    .page-item:hover .page-link {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        text-decoration: none !important;
    }

    .page-item.active .page-link {
        background: var(--orb-primary) !important;
        color: #fff !important;
        border-color: var(--orb-primary) !important;
    }

    .page-item.disabled .page-link {
        opacity: 0.5 !important;
        pointer-events: none !important;
    }

    .ann-badge {
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        text-transform: capitalize;
        display: inline-block;
    }

    .badge-general { background: #EEF2FF; color: #3538CD; }
    .badge-holiday { background: #ECFDF3; color: #027A48; }
    .badge-emergency { background: #FEF3F2; color: #B42318; }
    .badge-policy { background: #F4F3FF; color: #5925DC; }
    .badge-meeting { background: #FFF7E6; color: #B54708; }

    .priority-low { background: #F2F4F7; color: #344054; }
    .priority-normal { background: #EEF2FF; color: #3538CD; }
    .priority-high { background: #FFF9E6; color: #B54708; }
    .priority-urgent { background: #FFE4E8; color: #C01048; }

    .target-badge { background: #F4F2FF; color: var(--orb-primary); }
    .status-on { background: #ECFDF3; color: #027A48; }
    .status-off { background: #F2F4F7; color: #667085; }

    .btn-soft {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 8px;
        font-weight: 800;
        height: 32px;
        width: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .btn-soft:hover {
        background: var(--orb-primary) !important;
        color: #fff !important;
    }

    .table {
        margin-bottom: 0 !important;
    }

    .table thead th {
        background: #F8FAFC;
        color: var(--orb-muted);
        font-weight: 850;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.6px;
        padding: 14px 20px !important;
        border-bottom: 1px solid var(--orb-border) !important;
        border-top: 0 !important;
    }

    .table tbody td {
        padding: 14px 20px !important;
        vertical-align: middle;
        border-bottom: 1px solid var(--orb-border) !important;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 600;
    }

    .table tbody tr:hover td {
        background-color: rgba(75, 0, 232, 0.012) !important;
    }

    /* Modal Form styling */
    .orb-modal-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        padding: 24px;
        border: none;
    }

    .orb-modal-body {
        padding: 24px;
        background: #fff;
    }

    .orb-modal-footer {
        border-top: 1px solid var(--orb-border);
        padding: 16px 24px;
        background: #F9FAFB;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .orb-form-section {
        margin-bottom: 24px;
    }

    .orb-form-section-title {
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        color: var(--orb-primary);
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--orb-border);
        padding-bottom: 8px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .orb-form-label {
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        color: var(--orb-muted);
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        display: block;
    }

    .orb-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .orb-form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .orb-col-span-3 {
        grid-column: span 3;
    }

    .orb-btn-primary {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 8px 20px;
        font-weight: 800;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(75,0,232,0.15);
        cursor: pointer;
        height: 40px;
    }

    .orb-btn-light {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 12px;
        padding: 8px 20px;
        font-weight: 800;
        color: var(--orb-text);
        font-size: 13px;
        cursor: pointer;
        height: 40px;
    }

    .announcement-modal {
        position: fixed !important;
        inset: 0 !important;
        z-index: 30000 !important;
        padding-left: 0 !important;
    }

    .announcement-modal .modal-dialog {
        margin: 1.75rem auto !important;
        max-width: 1040px;
        transform: none;
    }

    .announcement-modal .modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        max-height: calc(100vh - 56px);
        display: flex;
        flex-direction: column;
    }

    .announcement-modal .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 230px);
        padding: 26px 30px;
    }

    .announcement-modal .modal-footer {
        flex-shrink: 0;
        padding: 20px 30px;
        background: #fff;
        border-top: 1px solid var(--orb-border);
        display: flex;
        justify-content: flex-end;
        gap: 14px;
    }

    .modal-backdrop {
        z-index: 29990 !important;
    }

    body.modal-open {
        overflow: hidden;
    }

    @media (max-width: 768px) {
        .ann-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 24px;
        }

        .ann-hero-btn {
            width: 100%;
        }

        .orb-form-grid, .orb-form-grid-3 {
            grid-template-columns: 1fr;
        }

        .orb-col-span-3 {
            grid-column: span 1;
        }
    }
</style>
@endsection

@section('_content')
<div class="ann-page">

    <!-- Premium Purple Gradient Hero Header -->
    <div class="ann-hero">
        <div>
            <div class="ann-kicker">
                <i class="fas fa-bullhorn"></i> HRMS &bull; NOTICE &amp; ANNOUNCEMENT
            </div>
            <h1 class="ann-title">Notice &amp; Announcements</h1>
            <p class="ann-subtitle">Publish HR notices, holidays, policies, emergency alerts and employee updates.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($permissions['canPrint'])
            <a href="{{ route('announcements.print') }}" target="_blank" class="ann-hero-btn ann-hero-btn-outline">
                <i class="fas fa-print"></i> Print Records
            </a>
            @endif

            @if($permissions['canCreate'] || $permissions['canManage'])
            <button type="button" class="ann-hero-btn" data-toggle="modal" data-target="#announcementModal" data-bs-toggle="modal" data-bs-target="#announcementModal">
                <i class="fas fa-plus"></i> Publish Announcement
            </button>
            @endif
        </div>
    </div>

    @include('components.alerts')

    <!-- Summary Metric Cards -->
    <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card" style="border-bottom: 4px solid var(--orb-primary); position: relative; overflow: hidden; height: 96px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(75, 0, 232, 0.08); color: var(--orb-primary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div>
                        <small style="text-transform: uppercase; font-size: 11px; font-weight: 800; color: var(--orb-muted); letter-spacing: 0.5px;">Total Announcements</small>
                        <h4 style="margin: 4px 0 0; font-size: 24px; font-weight: 900; color: var(--orb-text);">{{ $stats['total'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card" style="border-bottom: 4px solid #10b981; position: relative; overflow: hidden; height: 96px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.08); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <small style="text-transform: uppercase; font-size: 11px; font-weight: 800; color: var(--orb-muted); letter-spacing: 0.5px;">Active Notices</small>
                        <h4 style="margin: 4px 0 0; font-size: 24px; font-weight: 900; color: var(--orb-text);">{{ $stats['active'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card" style="border-bottom: 4px solid #ef4444; position: relative; overflow: hidden; height: 96px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(239, 68, 68, 0.08); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <small style="text-transform: uppercase; font-size: 11px; font-weight: 800; color: var(--orb-muted); letter-spacing: 0.5px;">Urgent Alerts</small>
                        <h4 style="margin: 4px 0 0; font-size: 24px; font-weight: 900; color: var(--orb-text);">{{ $stats['urgent'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card" style="border-bottom: 4px solid #f59e0b; position: relative; overflow: hidden; height: 96px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245, 158, 11, 0.08); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <small style="text-transform: uppercase; font-size: 11px; font-weight: 800; color: var(--orb-muted); letter-spacing: 0.5px;">Published Today</small>
                        <h4 style="margin: 4px 0 0; font-size: 24px; font-weight: 900; color: var(--orb-text);">{{ $stats['today'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card section -->
    <div class="ann-card mt-4">
        <!-- Table Card Header -->
        <div class="ann-card-header">
            <div class="ann-head-left">
                <div class="ann-icon-box">
                    <i class="fas fa-bullhorn text-primary"></i>
                </div>
                <div>
                    <h5 class="ann-card-title">Announcement Records</h5>
                    <p class="ann-card-subtitle">Manage notices, priorities, targets, attachments and publish status.</p>
                </div>
            </div>
        </div>

        <!-- Filters Attached Inside Card -->
        <div class="ann-filters-wrapper">
            <div class="row align-items-end g-2">
                <div class="col-12 col-md-3">
                    <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Notice Type</label>
                    <select id="filterType" class="form-select">
                        <option value="">All Types</option>
                        <option value="general">General</option>
                        <option value="holiday">Holiday</option>
                        <option value="emergency">Emergency</option>
                        <option value="policy">Policy</option>
                        <option value="meeting">Meeting</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Priority</label>
                    <select id="filterPriority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Target Audience</label>
                    <select id="filterTarget" class="form-select">
                        <option value="">All Targets</option>
                        <option value="all">All</option>
                        <option value="employee">Employee</option>
                        <option value="admin">Admin</option>
                        <option value="hr">HR</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label style="font-size: 10px; font-weight: 850; color: var(--orb-muted); text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.5px;">Status</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="button" id="btnResetFilters" class="btn btn-light w-100" style="height: 40px; border-radius: 12px; border: 1px solid var(--orb-border); background: #fff; color: var(--orb-primary); font-weight: 800; font-size: 13px;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- DataTable Toolbar row -->
        <div class="dm-table-toolbar-row">
            <div id="entries-container"></div>
            <div id="buttons-container"></div>
        </div>

        <!-- Table Listing -->
        <div class="table-responsive">
            <table id="announcementTable" class="table table-hover w-100 nowrap">
                <thead>
                    <tr>
                        <th style="padding-left: 24px;">Title</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Attachment</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th class="text-right" style="padding-right: 24px; width: 140px;">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- DataTable Pagination/Footer row -->
        <div class="dm-table-footer-row">
            <div id="info-container"></div>
            <div id="pagination-container"></div>
        </div>
    </div>
</div>

<!-- Modal Publish/Edit Announcement -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header">
                <div>
                    <h5 class="modal-title text-white font-weight-bold" id="modalTitle">Publish Announcement</h5>
                    <p class="orb-modal-subtitle mb-0 opacity-75" style="font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.9);">Post general notices, holiday updates, emergency warnings or policy changes.</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('announcements.store') }}" enctype="multipart/form-data" id="announcementForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-body orb-modal-body">
                    <!-- Section 1: Content -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-edit"></i> Announcement Content
                        </div>
                        <div class="orb-form-grid" style="grid-template-columns: 1fr;">
                            <div>
                                <label class="orb-form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control" placeholder="e.g., Office Closed on Independence Day" required>
                            </div>
                            <div>
                                <label class="orb-form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="description" rows="4" class="form-control" placeholder="Write full details here..." required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Distribution Settings -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-cog"></i> Settings & Target Audience
                        </div>
                        <div class="orb-form-grid-3">
                            <div>
                                <label class="orb-form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-control" required style="height: 40px;">
                                    <option value="general">General</option>
                                    <option value="holiday">Holiday</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="policy">Policy</option>
                                    <option value="meeting">Meeting</option>
                                </select>
                            </div>
                            <div>
                                <label class="orb-form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-control" required style="height: 40px;">
                                    <option value="low">Low</option>
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label class="orb-form-label">Target Audience <span class="text-danger">*</span></label>
                                <select name="target_type" id="target_type" class="form-control" required style="height: 40px;">
                                    <option value="all">All</option>
                                    <option value="employee">Employee</option>
                                    <option value="admin">Admin</option>
                                    <option value="hr">HR</option>
                                    <option value="role">Specific Role</option>
                                    <option value="department">Specific Department</option>
                                    <option value="user">Specific User</option>
                                </select>
                            </div>

                            <div class="target-input d-none orb-col-span-3" id="target_role_div">
                                <label class="orb-form-label">Select Role <span class="text-danger">*</span></label>
                                <select name="target_role_id" id="target_role_id" class="form-control" style="height: 40px;">
                                    <option value="">-- Choose Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="target-input d-none orb-col-span-3" id="target_department_div">
                                <label class="orb-form-label">Select Department <span class="text-danger">*</span></label>
                                <select name="target_department_id" id="target_department_id" class="form-control" style="height: 40px;">
                                    <option value="">-- Choose Dept --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="target-input d-none orb-col-span-3" id="target_user_div">
                                <label class="orb-form-label">Select User <span class="text-danger">*</span></label>
                                <select name="target_user_id" id="target_user_id" class="form-control" style="height: 40px;">
                                    <option value="">-- Choose User --</option>
                                    @foreach($users as $targetUser)
                                        <option value="{{ $targetUser->id }}">{{ $targetUser->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="orb-form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" style="height: 40px;">
                            </div>
                            <div>
                                <label class="orb-form-label">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" style="height: 40px;">
                            </div>
                            <div>
                                <label class="orb-form-label">Attachment</label>
                                <input type="file" name="attachment" id="attachment" class="form-control p-1" style="height: 40px;">
                            </div>

                            <div id="currentAttachment" class="mt-2 d-none orb-col-span-3">
                                <small class="text-muted d-block mb-1">Current Attachment:</small>
                                <div id="attachmentPreview" class="p-2 border rounded-3 bg-light d-flex align-items-center gap-2">
                                    <i class="fas fa-file"></i>
                                    <a href="#" target="_blank" id="attachmentLink" class="text-primary fw-bold small text-truncate">View File</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Publishing Options -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-toggle-on"></i> Publishing Options
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" name="is_active" id="is_active" value="1" checked style="transform: scale(1.15);">
                            <label class="custom-control-label font-weight-bold ml-2" for="is_active" style="font-size: 13px; color: var(--orb-text); cursor: pointer;">Active / Publish Now</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer orb-modal-footer">
                    <button type="button" class="orb-btn-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="orb-btn-primary" id="submitBtn"><i class="fas fa-paper-plane"></i> Publish Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable) {
            console.error('DataTable not loaded');
            return;
        }

        function openAnnouncementModal() {
            const modalEl = document.getElementById('announcementModal');
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else {
                $('#announcementModal').modal('show');
            }
        }

        const table = $('#announcementTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'All']
            ],
            dom: "t", // Custom elements injected manually to prevent duplicates/formatting breaks
            ajax: {
                url: "{{ route('announcements.index') }}",
                data: function(d) {
                    d.ajax_table = 1;
                    d.type = $('#filterType').val();
                    d.priority = $('#filterPriority').val();
                    d.target_type = $('#filterTarget').val();
                    d.status = $('#filterStatus').val();
                }
            },
            columns: [
                {
                    data: 'title',
                    render: function(data, type, row) {
                        return `
                        <div class="fw-bold text-dark" style="font-size: 14px;">${data}</div>
                        <small class="text-muted d-block text-truncate" style="max-width: 300px; font-weight: 500;">${row.description ?? ''}</small>
                        `;
                    }
                },
                {
                    data: 'type',
                    render: data => `<span class="ann-badge badge-${data}">${data}</span>`
                },
                {
                    data: 'priority',
                    render: data => `<span class="ann-badge priority-${data}">${data}</span>`
                },
                {
                    data: 'target_type',
                    render: data => `<span class="ann-badge target-badge">${data}</span>`
                },
                {
                    data: 'is_active',
                    render: data =>
                        data ?
                        `<span class="ann-badge status-on"><i class="fas fa-check-circle mr-1"></i>Active</span>` : `<span class="ann-badge status-off"><i class="fas fa-times-circle mr-1"></i>Inactive</span>`
                },
                {
                    data: 'attachment_url',
                    render: function(data) {
                        if (data) {
                            return `
                                <a href="${data}" target="_blank" class="btn btn-sm btn-soft">
                                    <i class="fas fa-paperclip"></i>
                                </a>
                            `;
                        }
                        return '<span class="text-muted font-weight-bold">-</span>';
                    }
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-right',
                    render: function(data, type, row) {
                        const editData = encodeURIComponent(JSON.stringify(row.edit_data ?? row));
                        let html = `<div class="d-inline-flex gap-1 align-items-center justify-content-end" style="gap: 6px;">`;

                        html += `
                            <a href="{{ url('/announcements') }}/${row.id}"
                               class="btn btn-sm btn-soft"
                               style="background: #F4F2FF; border: 1px solid #E7EAF3; color: var(--orb-primary) !important;"
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        `;

                        @if($permissions['canUpdate'] || $permissions['canManage'])
                        html += `
                            <button type="button"
                                    class="btn btn-sm btn-soft editBtn"
                                    data-row="${editData}"
                                    title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                        `;
                        @endif

                        @if($permissions['canToggle'] || $permissions['canManage'])
                        html += `
                            <button type="button"
                                    class="btn btn-sm btn-soft toggleBtn"
                                    data-id="${row.id}"
                                    title="Toggle Status">
                                <i class="fas fa-power-off"></i>
                            </button>
                        `;
                        @endif

                        @if($permissions['canDelete'] || $permissions['canManage'])
                        html += `
                            <button type="button"
                                    class="btn btn-sm btn-soft text-danger deleteBtn"
                                    style="background: #FEF2F2; border: 1px solid #FEE2E2;"
                                    data-id="${row.id}"
                                    title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        `;
                        @endif

                        html += `</div>`;
                        return html;
                    }
                }
            ],
            language: {
                paginate: {
                    next: '<i class="fas fa-chevron-right"></i>',
                    previous: '<i class="fas fa-chevron-left"></i>'
                }
            }
        });

        // Initialize Custom Controls & Exports In Custom Containers
        new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                    className: 'btn btn-sm btn-light border',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 6, 7] }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'btn btn-sm btn-light border',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 6, 7] }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    className: 'btn btn-sm btn-light border',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 6, 7] }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    className: 'btn btn-sm btn-light border',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 6, 7] }
                }
            ]
        });

        // Append custom DOM pieces
        table.buttons().container().appendTo('#buttons-container');
        
        // Setup entries length menu manually
        const lengthMenuHtml = `
            <label>
                Show 
                <select name="announcementTable_length" class="form-select" style="width: auto; display: inline-block;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                entries
            </label>
        `;
        $('#entries-container').html(lengthMenuHtml);
        
        $('#entries-container select').val(table.page.len()).on('change', function() {
            table.page.len(parseInt($(this).val())).draw();
        });

        // Trigger updates on info and pagination during draw
        table.on('draw', function() {
            const pageInfo = table.page.info();
            
            // Build visual pagination manually
            let paginationHtml = '<ul class="pagination">';
            
            // Previous button
            paginationHtml += `
                <li class="page-item ${pageInfo.page === 0 ? 'disabled' : ''}">
                    <a href="#" class="page-link prev-page-btn"><i class="fas fa-chevron-left"></i></a>
                </li>
            `;
            
            // Page buttons
            const totalPages = pageInfo.pages;
            for (let i = 0; i < totalPages; i++) {
                paginationHtml += `
                    <li class="page-item ${pageInfo.page === i ? 'active' : ''}">
                        <a href="#" class="page-link num-page-btn" data-page="${i}">${i + 1}</a>
                    </li>
                `;
            }
            
            // Next button
            paginationHtml += `
                <li class="page-item ${pageInfo.page === totalPages - 1 ? 'disabled' : ''}">
                    <a href="#" class="page-link next-page-btn"><i class="fas fa-chevron-right"></i></a>
                </li>
            `;
            
            paginationHtml += '</ul>';
            
            $('#pagination-container').html(paginationHtml);
            $('#info-container').html(`Showing ${pageInfo.start + 1} to ${pageInfo.end} of ${pageInfo.recordsTotal} entries`);
        });

        // Bind custom pagination click events
        $(document).on('click', '.prev-page-btn', function(e) {
            e.preventDefault();
            table.page('previous').draw('page');
        });

        $(document).on('click', '.next-page-btn', function(e) {
            e.preventDefault();
            table.page('next').draw('page');
        });

        $(document).on('click', '.num-page-btn', function(e) {
            e.preventDefault();
            table.page(parseInt($(this).data('page'))).draw('page');
        });

        $('#filterType, #filterPriority, #filterTarget, #filterStatus')
            .on('change', function() {
                table.ajax.reload();
            });

        $('#btnResetFilters').on('click', function() {
            $('#filterType').val('');
            $('#filterPriority').val('');
            $('#filterTarget').val('');
            $('#filterStatus').val('');
            table.ajax.reload();
        });

        $(document).on('click', '.editBtn', function() {
            const row = JSON.parse(decodeURIComponent($(this).attr('data-row')));

            $('#modalTitle').text('Edit Announcement');
            $('#submitBtn').html('<i class="fas fa-savemr-1"></i> Update Announcement');

            $('#announcementForm').attr('action', "{{ url('/announcements') }}/" + row.id);
            $('#formMethod').val('PUT');

            $('#title').val(row.title ?? '');
            $('#description').val(row.description ?? '');
            $('#type').val(row.type ?? 'general');
            $('#priority').val(row.priority ?? 'normal');
            $('#target_type').val(row.target_type ?? 'all');
            $('#target_role_id').val(row.target_role_id ?? '');
            $('#target_department_id').val(row.target_department_id ?? '');
            $('#target_user_id').val(row.target_user_id ?? '');

            triggerTargetVisibility(row.target_type ?? 'all');

            $('#start_date').val(row.start_date ? row.start_date.substring(0, 10) : '');
            $('#end_date').val(row.end_date ? row.end_date.substring(0, 10) : '');

            $('#is_active').prop('checked', row.is_active == true);

            if (row.attachment_url) {
                $('#currentAttachment').removeClass('d-none');
                $('#attachmentLink').attr('href', row.attachment_url).text(row.attachment ? row.attachment.split('/').pop() : 'View File');
                const isImage = /\.(jpg|jpeg|png|webp|gif)$/i.test(row.attachment_url);
                $('#attachmentPreview i').attr('class', isImage ? 'fas fa-image text-success' : 'fas fa-file-pdf text-danger');
            } else {
                $('#currentAttachment').addClass('d-none');
            }

            openAnnouncementModal();
        });

        $('#announcementModal').on('hidden.bs.modal hidden', function() {
            $('#modalTitle').text('Publish Announcement');
            $('#submitBtn').html('<i class="fas fa-paper-plane mr-1"></i> Publish Announcement');
            $('#announcementForm').attr('action', "{{ route('announcements.store') }}");
            $('#announcementForm')[0].reset();
            $('#formMethod').val('POST');
            $('#priority').val('normal');
            $('#type').val('general');
            $('#target_type').val('all');
            $('#is_active').prop('checked', true);
            $('#currentAttachment').addClass('d-none');
        });

        $(document).on('click', '.toggleBtn', function() {
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('/announcements') }}/" + id + "/toggle-status",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    _method: "PATCH"
                },
                success: function() {
                    table.ajax.reload(null, false);
                }
            });
        });

        $(document).on('click', '.deleteBtn', function() {
            const id = $(this).data('id');
            if (!confirm('Are you sure you want to delete this announcement?')) {
                return;
            }
            $.ajax({
                url: "{{ url('/announcements') }}/" + id,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    _method: "DELETE"
                },
                success: function() {
                    table.ajax.reload(null, false);
                }
            });
        });

        $('#target_type').on('change', function() {
            triggerTargetVisibility($(this).val());
        });

        function triggerTargetVisibility(type) {
            $('.target-input').addClass('d-none');
            if (type === 'role') $('#target_role_div').removeClass('d-none');
            if (type === 'department') $('#target_department_div').removeClass('d-none');
            if (type === 'user') $('#target_user_div').removeClass('d-none');
        }
    });
</script>
@endsection