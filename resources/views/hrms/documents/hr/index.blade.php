@extends('layouts.panel', ['active' => 'documents'])

@section('page_title', 'Pending Verifications')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@include('hrms.documents.partials.styles')
<style>
    /* DataTable Toolbar layout inside the table card */
    .dm-table-toolbar-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 16px 24px !important;
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

    /* DataTable buttons styling */
    .dt-buttons {
        display: flex !important;
        gap: 6px !important;
    }

    .dt-buttons .btn {
        height: 32px !important;
        padding: 0 12px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border-radius: 9px !important;
        border: 1px solid var(--dm-border) !important;
        background: #fff !important;
        color: var(--dm-muted) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: none !important;
        transition: all 0.2s ease !important;
    }

    .dt-buttons .btn:hover {
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        border-color: var(--dm-primary) !important;
    }

    /* Pagination design styling */
    .dataTables_paginate {
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
        color: var(--dm-muted) !important;
        border: 1px solid var(--dm-border) !important;
        background: #fff !important;
        transition: all 0.2s ease !important;
    }

    .page-item:hover .page-link {
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        text-decoration: none !important;
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

    /* Card avatar alignment */
    .dm-avatar-wrapper {
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 900 !important;
        font-size: 13px !important;
        border: 1px solid rgba(75, 0, 232, 0.15) !important;
    }

    .dm-table-footer-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 16px 24px !important;
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

    /* Toggle Switch Custom styling */
    .verify-switch {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 28px;
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
        height: 22px;
        width: 22px;
        left: 3px;
        bottom: 3px;
        background: #fff;
        border-radius: 50%;
        transition: .2s;
        box-shadow: 0 3px 8px rgba(16, 24, 40, .18);
    }

    .verify-switch input:checked+.verify-slider {
        background: #12B76A;
    }

    .verify-switch input:checked+.verify-slider:before {
        transform: translateX(24px);
    }

    /* Health Strip Segment Separators */
    .dm-health-segment {
        position: relative;
        padding: 16px;
    }

    @media (min-width: 992px) {
        .dm-health-segment:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20%;
            right: 0;
            height: 60%;
            width: 1px;
            background: var(--dm-border);
        }
    }

    @media (min-width: 768px) and (max-width: 991px) {
        .dm-health-segment:nth-child(odd)::after {
            content: '';
            position: absolute;
            top: 20%;
            right: 0;
            height: 60%;
            width: 1px;
            background: var(--dm-border);
        }

        .dm-health-segment:nth-child(1)::before,
        .dm-health-segment:nth-child(2)::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 10%;
            height: 1px;
            width: 80%;
            background: var(--dm-border);
        }
    }

    @media (max-width: 767px) {
        .dm-health-segment:not(:last-child)::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 10%;
            height: 1px;
            width: 80%;
            background: var(--dm-border);
        }
    }

    .dm-kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .dm-kpi {
        min-height: 88px;
        padding: 12px;
        border-radius: 18px;
        border: 1px solid var(--dm-border);
        background: #fff;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .045);
        position: relative;
        overflow: hidden;
        transition: .18s ease;
    }

    .dm-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(16, 24, 40, .08);
    }

    .dm-kpi:after {
        content: "";
        position: absolute;
        right: -32px;
        top: -34px;
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: var(--tone-soft);
    }

    .dm-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        position: relative;
        z-index: 1;
    }

    .dm-kpi-icon {
        width: 34px;
        height: 34px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tone-soft);
        color: var(--tone);
        font-size: 14px;
    }

    .dm-kpi-value {
        font-size: 25px;
        line-height: 1;
        font-weight: 950;
        color: var(--dm-text);
    }

    .dm-kpi-label {
        margin-top: 10px;
        font-size: 10px;
        color: var(--dm-muted);
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .035em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        position: relative;
        z-index: 1;
    }

    .dm-kpi-line {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 9px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--tone), transparent);
    }

    .tone-success {
        --tone: #12B76A;
        --tone-soft: rgba(18, 183, 106, .12);
    }

    .tone-danger {
        --tone: #F04438;
        --tone-soft: rgba(240, 68, 56, .12);
    }

    .tone-warning {
        --tone: #F79009;
        --tone-soft: rgba(247, 144, 9, .14);
    }

    .tone-purple {
        --tone: #7A5AF8;
        --tone-soft: rgba(122, 90, 248, .13);
    }

    .tone-info {
        --tone: #0EA5E9;
        --tone-soft: rgba(14, 165, 233, .13);
    }

    .tone-orange {
        --tone: #EA580C;
        --tone-soft: rgba(234, 88, 12, .13);
    }

    @media(max-width:1280px) {
        .dm-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media(max-width:768px) {
        .dm-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>


@endsection

@section('_content')
<div class="dm-page">
    <!-- Premium Purple Gradient Hero -->
    <div class="dm-hero" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px; padding: 24px; border-radius: 16px; margin-bottom: 24px;">
        <div style="max-width: 600px;">
            <div class="dm-kicker" style="margin-bottom: 8px; font-size: 11px; font-weight: 800; letter-spacing: 1px; color: rgba(255,255,255,0.9);">
                <i class="fas fa-file-signature mr-1"></i> HRMS &bull; DOCUMENT VERIFICATION
            </div>
            <h1 style="margin-bottom: 8px; font-size: 24px; font-weight: 800; color: #fff; text-transform: uppercase;">HR Document Review</h1>
            <p style="margin-bottom: 0; font-size: 13px; color: rgba(255,255,255,0.8); line-height: 1.5;">Manage employee KYC, mandatory documents, verification lifecycle and compliance tracking.</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('documents.types.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: #fff; font-weight: 700; border-radius: 8px; padding: 6px 16px; backdrop-filter: blur(4px);">
                <i class="fas fa-file-alt mr-1"></i> Document Types
            </a>
            <a href="{{ route('documents.policies.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: #fff; font-weight: 700; border-radius: 8px; padding: 6px 16px; backdrop-filter: blur(4px);">
                <i class="fas fa-folder-open mr-1"></i> Company Documents & Policies
            </a>
            <button onclick="$('.buttons-excel').click()" class="btn btn-sm shadow-sm" style="background: #fff; color: var(--dm-primary); font-weight: 800; border-radius: 8px; padding: 6px 16px;">
                <i class="fas fa-file-export mr-1"></i> Export Report
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 14px; font-weight: 700; font-size: 13px;">
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
        <div class="dm-table-header d-flex justify-content-between align-items-center flex-wrap" style="padding: 16px 24px;">
            <div class="dm-table-head-left d-flex align-items-center gap-3">
                <div class="dm-icon-box"><i class="fas fa-list-ul"></i></div>
                <div>
                    <h5 class="dm-table-title mb-0">Compliance Matrix</h5>
                    <p class="dm-table-subtitle mb-0">Manage document verifications, statuses, and compliance tracking.</p>
                </div>
            </div>
            <div class="dm-table-head-right mt-2 mt-md-0">
                <a href="{{ route('documents.hr.index') }}" class="btn btn-sm shadow-sm" style="border-radius: 8px; border: 1px solid var(--dm-border); background: #fff; color: var(--dm-text); font-weight: 700;">
                    <i class="fas fa-undo mr-1"></i> Reset Filters
                </a>
            </div>
        </div>

        <!-- Filter Row Attached inside card -->
        <form method="GET" action="{{ route('documents.hr.index') }}" id="docFilterForm">
            <div class="dm-filter-wrapper" style="padding: 16px 24px; border-top: 1px solid var(--dm-border); border-bottom: 1px solid var(--dm-border); background: #fafafa;">
                <div class="row mx-0" style="gap: 12px 0;">
                    <div class="col-12 col-md-3 px-2">
                        <label class="dm-filter-label" style="font-size: 11px; font-weight: 700; color: var(--dm-muted); text-transform: uppercase; margin-bottom: 6px; display: block;">Search Employee</label>
                        <input type="text" name="employee" id="filterSearch" value="{{ request('employee') }}" class="dm-filter-control w-100" style="border-radius: 8px; height: 38px; border: 1px solid var(--dm-border); padding: 0 12px; outline: none; font-weight: 600; font-size: 13px;" placeholder="Name, code, email...">
                    </div>

                    <div class="col-12 col-md-3 px-2">
                        <label class="dm-filter-label" style="font-size: 11px; font-weight: 700; color: var(--dm-muted); text-transform: uppercase; margin-bottom: 6px; display: block;">Department</label>
                        <select name="department_id" id="filterDepartment" class="dm-filter-control w-100" style="border-radius: 8px; height: 38px; border: 1px solid var(--dm-border); padding: 0 12px; outline: none; font-weight: 600; font-size: 13px;">
                            <option value="">All Depts</option>
                            @if(isset($departments) && count($departments) > 0)
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-12 col-md-3 px-2">
                        <label class="dm-filter-label" style="font-size: 11px; font-weight: 700; color: var(--dm-muted); text-transform: uppercase; margin-bottom: 6px; display: block;">Doc Type</label>
                        <select name="document_type_id" id="filterDocumentType" class="dm-filter-control w-100" style="border-radius: 8px; height: 38px; border: 1px solid var(--dm-border); padding: 0 12px; outline: none; font-weight: 600; font-size: 13px;">
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

                    <div class="col-12 col-md-3 px-2">
                        <label class="dm-filter-label" style="font-size: 11px; font-weight: 700; color: var(--dm-muted); text-transform: uppercase; margin-bottom: 6px; display: block;">Status</label>
                        <select name="status" id="filterStatus" class="dm-filter-control w-100" style="border-radius: 8px; height: 38px; border: 1px solid var(--dm-border); padding: 0 12px; outline: none; font-weight: 600; font-size: 13px;">
                            <option value="">Pending Only</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Docs</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>

        <!-- DataTable Toolbar (Length and buttons appended here) -->
        <div class="dm-table-toolbar-row">
            <div id="employeeLengthBox"></div>
            <div id="employeeExportButtons"></div>
        </div>

        <!-- Table Listing -->
        <!-- Table Listing -->
        <div class="dm-table-wrap table-responsive" style="border-bottom: 1px solid var(--dm-border);">
            <table id="employeeDocTable" class="table dm-table mb-0" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 24px;">Employee</th>
                        <th style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 16px;">Required Docs</th>
                        <th style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 16px;">Pending</th>
                        <th style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 16px;">Verified</th>
                        <th style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 16px;">Rejected</th>
                        <th style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 16px;">Missing</th>
                        <th style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 16px;">Expiring</th>
                        <th style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 16px;">Profile Status</th>
                        <!-- <th style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 16px;">Last Updated</th> -->
                        <th class="text-center" style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 16px;">Verify All</th>
                        <th class="text-right" style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--dm-muted); letter-spacing: 0.5px; white-space: nowrap; padding: 12px 24px; width: 180px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td style="padding: 12px 24px; vertical-align: middle;">
                            <div class="d-flex align-items-center gap-3">
                                <!-- <div class="dm-avatar-wrapper shadow-sm">
                                    {{ strtoupper(substr($employee->user->name ?? 'E', 0, 1)) }}
                                </div> -->
                                <div style="line-height: 1.4;">
                                    <div style="font-weight: 800; color: var(--dm-text); font-size: 13px; max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $employee->user->name ?? '-' }}">{{ $employee->user->name ?? '-' }}</div>
                                    <div style="font-size: 11px; color: var(--dm-muted); font-weight: 700;">
                                        {{ $employee->employee_code ?? '-' }} &bull; {{ $employee->designation->name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td style="padding: 12px 16px; vertical-align: middle;">
                            <span class="badge" style="background: var(--dm-soft); color: var(--dm-primary); font-size: 11px; font-weight: 800; padding: 6px 10px; border-radius: 6px;">{{ $employee->doc_required }}</span>
                        </td>
                        <td style="padding: 12px 16px; vertical-align: middle;">
                            <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 11px; font-weight: 800; padding: 6px 10px; border-radius: 6px;">{{ $employee->doc_pending }}</span>
                        </td>
                        <td style="padding: 12px 16px; vertical-align: middle;">
                            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 11px; font-weight: 800; padding: 6px 10px; border-radius: 6px;">{{ $employee->doc_verified }}</span>
                        </td>
                        <td style="padding: 12px 16px; vertical-align: middle;">
                            <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 11px; font-weight: 800; padding: 6px 10px; border-radius: 6px;">{{ $employee->doc_rejected }}</span>
                        </td>
                        <td style="padding: 12px 16px; vertical-align: middle;">
                            <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 11px; font-weight: 800; padding: 6px 10px; border-radius: 6px;">{{ $employee->doc_missing }}</span>
                        </td>
                        <td style="padding: 12px 16px; vertical-align: middle;">
                            <span class="badge" style="background: rgba(249, 115, 22, 0.1); color: #f97316; font-size: 11px; font-weight: 800; padding: 6px 10px; border-radius: 6px;">{{ $employee->doc_expiring }}</span>
                        </td>

                        <td style="padding: 12px 16px; vertical-align: middle;">
                            @php
                            $profStatus = $employee->profile->profile_status ?? 'pending';
                            $profStatusColor = 'rgba(245, 158, 11, 0.1)';
                            $profStatusText = '#f59e0b';
                            if($profStatus === 'approved') {
                            $profStatusColor = 'rgba(16, 185, 129, 0.1)';
                            $profStatusText = '#10b981';
                            } elseif($profStatus === 'rejected') {
                            $profStatusColor = 'rgba(239, 68, 68, 0.1)';
                            $profStatusText = '#ef4444';
                            }
                            @endphp
                            <span class="badge" style="background: {{ $profStatusColor }}; color: {{ $profStatusText }}; font-size: 11px; font-weight: 800; padding: 6px 10px; border-radius: 6px; text-transform: capitalize;">
                                {{ $profStatus }}
                            </span>
                        </td>

                        <!-- <td style="padding: 12px 16px; vertical-align: middle;">
                            <div style="font-size: 11px; font-weight: 700; color: var(--dm-muted); white-space: nowrap;">{{ $employee->updated_at ? $employee->updated_at->format('d M Y') : '-' }}</div>
                        </td> -->

                        <td style="padding: 12px 16px; vertical-align: middle;" class="text-center">
                            @if($employee->doc_status !== 'verified')
                            <form action="{{ route('documents.hr.verify_employee', $employee->id) }}"
                                method="POST"
                                class="verify-all-form mb-0 d-flex justify-content-center">
                                @csrf
                                <label class="verify-switch mb-0" title="Verify all documents" style="transform: scale(0.8);">
                                    <input type="checkbox" class="verify-all-toggle">
                                    <span class="verify-slider"></span>
                                </label>
                            </form>
                            @else
                            <span class="badge shadow-sm w-100 text-center" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; font-size: 10px; font-weight: 800; padding: 6px 8px; border-radius: 6px;"><i class="fas fa-check-double mr-1"></i> Verified</span>
                            @endif
                        </td>

                        <td style="padding: 12px 24px; vertical-align: middle;">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                @if($employee->doc_status !== 'verified')
                                <button type="button" onclick="alert('Please view details to reject specific documents.')" class="btn btn-sm shadow-sm" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); color: #f59e0b; font-size: 11px; font-weight: 800; border-radius: 6px; padding: 4px 10px; white-space: nowrap;">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </button>
                                @endif
                                <a href="{{ route('documents.hr.show', $employee->user_id) }}"
                                    class="btn btn-sm shadow-sm" style="background: #fff; border: 1px solid var(--dm-border); color: var(--dm-primary); font-size: 11px; font-weight: 800; border-radius: 6px; padding: 4px 10px; white-space: nowrap;">
                                    View Details
                                </a>
                                <a href="{{ route('hrms.employees.profile.view', $employee->id) }}"
                                    class="btn btn-sm shadow-sm" style="background: rgba(75, 0, 232, 0.05); border: 1px solid rgba(75, 0, 232, 0.1); color: var(--dm-primary); font-size: 11px; font-weight: 800; border-radius: 6px; padding: 4px 10px; white-space: nowrap;">
                                    <i class="fas fa-user mr-1"></i> View Profile
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
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
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
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
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
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
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
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
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

        let searchTimer = null;

        $('#filterSearch').on('keyup', function() {
            clearTimeout(searchTimer);

            searchTimer = setTimeout(function() {
                $('#docFilterForm').submit();
            }, 500);
        });

        $('#filterDepartment, #filterDocumentType, #filterStatus').on('change', function() {
            $('#docFilterForm').submit();
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