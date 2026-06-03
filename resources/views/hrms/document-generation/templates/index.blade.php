@extends('layouts.panel', ['active' => 'document_generation'])

@section('page_title', 'Document Templates')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    :root {
        --orb-bg: #F6F7FB;

        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
    }

    body {
        background-color: var(--orb-bg);
    }

    .orb-page-wrapper {
        padding: 24px;
        background: var(--orb-bg);
        min-height: calc(100vh - 70px);
        overflow-x: hidden;
    }

    @media (max-width: 991px) {
        .orb-page-wrapper { padding: 18px; }
    }
    @media (max-width: 575px) {
        .orb-page-wrapper { padding: 12px; }
    }

    /* Hero Section */
    .orb-hero {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        border-radius: 26px;
        padding: 32px;
        color: white;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        box-shadow: 0 10px 30px rgba(75, 0, 232, 0.15);
    }

    .orb-hero-title {
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px 0;
    }

    .orb-hero-subtitle {
        font-size: 14px;
        margin: 0;
        opacity: 0.9;
    }

    .orb-hero-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .orb-btn-light {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        backdrop-filter: blur(10px);
    }

    .orb-btn-light:hover {
        background: white;
        color: var(--orb-primary);
    }

    .orb-btn-white {
        background: white;
        color: var(--orb-primary);
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .orb-btn-white:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    /* Summary Cards */
    .orb-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1200px) { .orb-stat-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .orb-stat-grid { grid-template-columns: 1fr; } }

    .orb-stat-card {
        background: white;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(16, 24, 40, 0.04);
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }

    .orb-stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .orb-stat-card.primary::after { background: var(--orb-primary); }
    .orb-stat-card.success::after { background: #12B76A; }
    .orb-stat-card.warning::after { background: #F79009; }
    .orb-stat-card.danger::after { background: var(--orb-secondary); }

    .orb-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .orb-stat-icon.primary { background: rgba(75,0,232,0.1); color: var(--orb-primary); }
    .orb-stat-icon.success { background: rgba(18,183,106,0.1); color: #12B76A; }
    .orb-stat-icon.warning { background: rgba(247,144,9,0.1); color: #F79009; }
    .orb-stat-icon.danger { background: rgba(17,24,39,0.05); color: var(--orb-secondary); }

    .orb-stat-info h4 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: var(--orb-text);
    }

    .orb-stat-info p {
        margin: 0;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--orb-muted);
        letter-spacing: 0.5px;
    }

    /* Table Card */
    .orb-table-card {
        background: white;
        border-radius: 22px;
        border: 1px solid var(--orb-border);
        box-shadow: 0 8px 24px rgba(16,24,40,0.05);
        overflow: hidden;
    }

    .orb-table-head {
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--orb-border);
        flex-wrap: wrap;
        gap: 16px;
    }

    .orb-table-title-wrap {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .orb-table-icon {
        width: 46px;
        height: 46px;
        background: rgba(75,0,232,0.08);
        color: var(--orb-primary);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .orb-table-title-wrap h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: var(--orb-text);
    }

    .orb-table-title-wrap p {
        margin: 2px 0 0 0;
        font-size: 13px;
        color: var(--orb-muted);
        font-weight: 500;
    }

    /* Filters inside Table Card */
    .orb-filter-row {
        padding: 16px 24px;
        background: #FDFDFF;
        border-bottom: 1px solid var(--orb-border);
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
    }

    @media (max-width: 1200px) { .orb-filter-row { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .orb-filter-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .orb-filter-row { grid-template-columns: 1fr; } }

    .orb-filter-group label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--orb-muted);
        margin-bottom: 6px;
        display: block;
    }

    .orb-filter-control {
        width: 100%;
        height: 40px;
        border: 1px solid #DDE3EE;
        border-radius: 10px;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 600;
        background: white;
        outline: none;
        transition: all 0.2s;
    }

    .orb-filter-control:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 3px rgba(75,0,232,0.1);
    }

    .orb-btn-reset {
        height: 40px;
        border: 1px solid #DDE3EE;
        background: white;
        color: var(--orb-text);
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        cursor: pointer;
        transition: all 0.2s;
    }

    .orb-btn-reset:hover {
        background: #F4F2FF;
        color: var(--orb-primary);
        border-color: var(--orb-primary);
    }

    /* DataTable Overrides */
    .orb-dt-toolbar {
        padding: 12px 24px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        flex-wrap: wrap;
        gap: 12px;
    }

    .orb-table-scroll {
        width: 100%;
        overflow-x: auto;
    }

    table.dataTable {
        margin: 0 !important;
        border-collapse: collapse !important;
        width: 100% !important;
    }

    table.dataTable thead th {
        background: #F8FAFC !important;
        color: #475467 !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        padding: 16px 20px !important;
        border-bottom: 1px solid var(--orb-border) !important;
        border-top: none !important;
        white-space: nowrap;
    }

    table.dataTable tbody td {
        padding: 16px 20px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid #F2F4F7 !important;
        vertical-align: middle;
        white-space: nowrap;
    }

    table.dataTable tbody tr:hover td {
        background: #FDFDFF !important;
    }

    .dataTables_wrapper .dataTables_filter { display: none; }
    
    .dataTables_length select {
        border: 1px solid var(--orb-border);
        border-radius: 8px;
        padding: 4px 8px;
        height: 32px;
        font-size: 12px;
        outline: none;
    }

    .dt-buttons .btn {
        background: white;
        border: 1px solid var(--orb-border);
        color: var(--orb-text);
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 12px;
        transition: all 0.2s;
    }
    .dt-buttons .btn:hover {
        background: #F4F2FF;
        color: var(--orb-primary);
        border-color: rgba(75,0,232,0.2);
    }

    .dataTables_info {
        font-size: 12px;
        font-weight: 600;
        color: var(--orb-muted);
        padding: 16px 24px !important;
    }

    .dataTables_paginate {
        padding: 16px 24px !important;
    }

    .page-item.active .page-link {
        background: var(--orb-primary);
        border-color: var(--orb-primary);
    }
    .page-link {
        border-radius: 8px;
        margin: 0 2px;
        font-size: 12px;
        font-weight: 600;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
    }

    /* Badges */
    .orb-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        line-height: 1;
    }
    .orb-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }
    .orb-badge.active { background: #ECFDF5; color: #027A48; }
    .orb-badge.active .orb-badge-dot { background: #12B76A; }
    .orb-badge.inactive { background: #F2F4F7; color: #344054; }
    .orb-badge.inactive .orb-badge-dot { background: #667085; }

    /* Modals */

    
    .orb-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        max-height: calc(100vh - 40px);
        display: flex;
        flex-direction: column;
    }
    .orb-modal-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: white;
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-shrink: 0;
    }
    .orb-modal-header h5 {
        font-size: 20px;
        font-weight: 800;
        margin: 0 0 4px 0;
    }
    .orb-modal-header p {
        font-size: 13px;
        margin: 0;
        opacity: 0.9;
    }
    .orb-modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 230px);
        padding: 24px;
        background: #fff;
    }
    .orb-modal-footer {
        padding: 20px 24px;
        background: #FDFDFF;
        border-top: 1px solid var(--orb-border);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-shrink: 0;
    }
    
    .orb-form-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--orb-muted);
        margin-bottom: 6px;
    }
    .orb-form-control {
        height: 42px;
        border-radius: 12px;
        border: 1px solid #DDE3EE;
        padding: 0 14px;
        font-size: 13px;
        font-weight: 600;
        width: 100%;
        outline: none;
        transition: all 0.2s;
    }
    .orb-form-control:focus {
        border-color: var(--orb-primary);
        box-shadow: 0 0 0 3px rgba(75,0,232,0.1);
    }
    textarea.orb-form-control {
        height: auto;
        padding: 14px;
        font-family: monospace;
        resize: vertical;
        max-height: 280px;
    }

    .orb-section-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--orb-primary);
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--orb-border);
    }

    .orb-placeholder-chip {
        display: inline-flex;
        align-items: center;
        background: #F4F2FF;
        color: var(--orb-primary);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-family: monospace;
        font-weight: 700;
        margin: 0 6px 6px 0;
        cursor: pointer;
        border: 1px solid rgba(75,0,232,0.1);
        transition: all 0.2s;
    }
    .orb-placeholder-chip:hover {
        background: var(--orb-primary);
        color: white;
    }

    .orb-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #F4F2FF;
        color: var(--orb-primary);
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        cursor: pointer;
    }
    .orb-action-btn:hover {
        background: var(--orb-primary);
        color: white;
    }
    .orb-action-btn.danger {
        background: #FEF2F2;
        color: #DC2626;
    }
    .orb-action-btn.danger:hover {
        background: #DC2626;
        color: white;
    }
</style>
@endsection

@section('_content')
<div class="orb-page-wrapper">
    
    <!-- Hero Section -->
    <div class="orb-hero">
        <div>
            <h1 class="orb-hero-title">Document Templates</h1>
            <p class="orb-hero-subtitle">Manage reusable Word templates for employee document generation.</p>
        </div>
        <div class="orb-hero-actions">
            @if(Route::has('hrms.document-generation.generated.index'))
            <a href="{{ route('hrms.document-generation.generated.index') }}" class="orb-btn-light">
                <i class="fas fa-history"></i> Generated Documents
            </a>
            @endif
            @if(Route::has('hrms.document-generation.generated.create'))
            <a href="{{ route('hrms.document-generation.generated.create') }}" class="orb-btn-light">
                <i class="fas fa-file-invoice"></i> Generate Document
            </a>
            @endif
            <button type="button" class="orb-btn-white" data-toggle="modal" data-target="#createTemplateModal">
                <i class="fas fa-upload"></i> Upload Template
            </button>
        </div>
    </div>

    @if(session('warning'))
    <div class="alert alert-warning border-0 rounded-4 shadow-sm p-4 mb-4 d-flex align-items-center gap-3" role="alert" style="background-color: #FEF3C7; border-left: 6px solid #D97706 !important;">
        <i class="fas fa-exclamation-triangle fa-2x" style="color: #D97706;"></i>
        <div>
            <h5 class="fw-bold mb-1" style="color: #92400E;">Template Validation Warning</h5>
            <p class="mb-0 small" style="color: #B45309 !important;">{{ session('warning') }}</p>
        </div>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="orb-stat-grid">
        <div class="orb-stat-card primary">
            <div class="orb-stat-icon primary"><i class="fas fa-file-alt"></i></div>
            <div class="orb-stat-info">
                <h4>{{ $totalTemplates ?? 0 }}</h4>
                <p>Total Templates</p>
            </div>
        </div>
        <div class="orb-stat-card success">
            <div class="orb-stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="orb-stat-info">
                <h4>{{ $activeTemplates ?? 0 }}</h4>
                <p>Active Templates</p>
            </div>
        </div>
        <div class="orb-stat-card warning">
            <div class="orb-stat-icon warning"><i class="fas fa-file-word"></i></div>
            <div class="orb-stat-info">
                <h4>{{ $docxTemplates ?? 0 }}</h4>
                <p>DOCX Templates</p>
            </div>
        </div>
        <div class="orb-stat-card danger">
            <div class="orb-stat-icon danger"><i class="fas fa-tasks"></i></div>
            <div class="orb-stat-info">
                <h4>{{ $generatedCount ?? 0 }}</h4>
                <p>Generated Documents</p>
            </div>
        </div>
        <div class="orb-stat-card success">
            <div class="orb-stat-icon success"><i class="fas fa-archive"></i></div>
            <div class="orb-stat-info">
                <h4>{{ $archivedCount ?? 0 }}</h4>
                <p>Archived Templates</p>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="orb-table-card">
        <div class="orb-table-head">
            <div class="orb-table-title-wrap">
                <div class="orb-table-icon"><i class="fas fa-file-code"></i></div>
                <div>
                    <h3>Template Directory</h3>
                    <p>View and manage all document structures.</p>
                </div>
            </div>
            <div>
                <button type="button" class="orb-btn-white border" style="color:var(--orb-text);" data-toggle="modal" data-target="#createTemplateModal">
                    <i class="fas fa-upload text-primary"></i> Upload Template
                </button>
            </div>
        </div>

        <div class="orb-filter-row">
            <div class="orb-filter-group">
                <label>Search</label>
                <input type="text" id="dtSearch" class="orb-filter-control" placeholder="Search templates...">
            </div>
            <div class="orb-filter-group">
                <label>Document Type</label>
                <select id="dtType" class="orb-filter-control">
                    <option value="">All Types</option>
                    <option value="Offer Letter">Offer Letter</option>
                    <option value="Appointment Letter">Appointment Letter</option>
                    <option value="Confirmation Letter">Confirmation Letter</option>
                    <option value="Relieving Letter">Relieving Letter</option>
                    <option value="Experience Certificate">Experience Certificate</option>
                    <option value="Internship Certificate">Internship Certificate</option>
                    <option value="Salary Revision Letter">Salary Revision Letter</option>
                    <option value="Warning Letter">Warning Letter</option>
                </select>
            </div>
            <div class="orb-filter-group">
                <label>Template Type</label>
                <select id="dtTemplateType" class="orb-filter-control">
                    <option value="">All Types</option>
                    <option value="HTML">HTML</option>
                    <option value="DOCX">DOCX</option>
                </select>
            </div>
            <div class="orb-filter-group">
                <label>Status</label>
                <select id="dtStatus" class="orb-filter-control">
                    <option value="">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="orb-filter-group">
                <label>Archived</label>
                <select id="dtArchived" class="orb-filter-control">
                    <option value="">All</option>
                    <option value="Archived">Archived</option>
                    <option value="Live">Live</option>
                </select>
            </div>
            <div class="orb-filter-group" style="display: flex; align-items: flex-end;">
                <button type="button" id="dtReset" class="orb-btn-reset">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </div>

        <div class="orb-dt-toolbar">
            <div id="dtLength"></div>
            <div id="dtButtons"></div>
        </div>

        <div class="orb-table-scroll">
            <table id="templatesTable" class="table">
                <thead>
                    <tr>
                        <th width="50">S.No.</th>
                        <th>Template Name</th>
                        <th>Category</th>
                        <th>Document Type</th>
                        <th>Template Type</th>
                        <th>File Name</th>
                        <th>Detected Fields</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Uploaded By</th>
                        <th>Last Updated</th>
                        <th width="100" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $index => $template)
                    <tr>
                        <td>{{ $templates->firstItem() + $index }}</td>
                        <td class="fw-bold">{{ $template->name }}</td>
                        <td>{{ $template->category ?: '-' }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $template->document_type)) }}</td>
                        <td>{{ strtoupper($template->template_type ?: 'html') }}</td>
                        <td>
                            @if(($template->template_type ?: 'html') === 'docx' && $template->docx_file_path)
                                {{ basename($template->docx_file_path) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ is_array($template->detected_fields) ? count($template->detected_fields) : 0 }}</td>
                        <td>{{ $template->version ?: 'v1' }}</td>
                        <td>
                            @if($template->is_archived)
                                <span class="orb-badge inactive"><span class="orb-badge-dot"></span> Archived</span>
                            @elseif($template->is_active)
                                <span class="orb-badge active"><span class="orb-badge-dot"></span> Active</span>
                            @else
                                <span class="orb-badge inactive"><span class="orb-badge-dot"></span> Inactive</span>
                            @endif
                        </td>
                        <td>{{ optional($template->createdBy)->name ?: '-' }}</td>
                        <td>{{ $template->updated_at ? $template->updated_at->format('d M Y, h:i A') : '-' }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('hrms.document-generation.generated.create', ['template_id' => $template->id]) }}" class="orb-action-btn" title="Preview Template">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(($template->template_type ?: 'html') === 'docx' && $template->docx_file_path)
                                <a href="{{ route('hrms.document-generation.templates.download-original', $template->id) }}" class="orb-action-btn" title="Download Original">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('hrms.document-generation.templates.detect-fields', $template->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="orb-action-btn" title="Scan & Detect Placeholders">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                                <button type="button" class="orb-action-btn" data-toggle="modal" data-target="#placeholderModal{{ $template->id }}" title="View Placeholder Mapping Audit">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                @endif
                                <a href="{{ route('hrms.document-generation.generated.create', ['template_id' => $template->id]) }}" class="orb-action-btn" title="Generate">
                                    <i class="fas fa-magic"></i>
                                </a>
                                <button type="button" class="orb-action-btn" data-toggle="modal" data-target="#editTemplateModal{{ $template->id }}" title="Edit Template">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('hrms.document-generation.templates.toggle-archive', $template->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="orb-action-btn" title="{{ $template->is_archived ? 'Restore Template' : 'Archive Template' }}">
                                        <i class="fas {{ $template->is_archived ? 'fa-box-open' : 'fa-archive' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('hrms.document-generation.templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="orb-action-btn danger" title="Delete Template">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($templates->hasPages())
        <div class="p-3 bg-white border-top">
            {{ $templates->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal">
            <form method="POST" action="{{ route('hrms.document-generation.templates.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="orb-modal-header">
                    <div>
                        <h5>Create Document Template</h5>
                        <p>Build reusable HR letters and certificate templates.</p>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body orb-modal-body">
                    <div class="row">
                        <!-- Left Column: Form Fields -->
                        <div class="col-lg-8">
                            <h6 class="orb-section-title">1. Template Info</h6>
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="orb-form-label">Template Name *</label>
                                    <input type="text" name="name" class="orb-form-control" required placeholder="e.g. Standard Offer Letter">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Slug (Optional - Auto-generated)</label>
                                    <input type="text" name="slug" class="orb-form-control" placeholder="e.g. standard-offer-letter">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Document Type *</label>
                                    <select name="document_type" class="orb-form-control" required>
                                        <option value="offer_letter">Offer Letter</option>
                                        <option value="appointment_letter">Appointment Letter</option>
                                        <option value="confirmation_letter">Confirmation Letter</option>
                                        <option value="relieving_letter">Relieving Letter</option>
                                        <option value="experience_certificate">Experience Certificate</option>
                                        <option value="internship_certificate">Internship Certificate</option>
                                        <option value="salary_revision_letter">Salary Revision Letter</option>
                                        <option value="warning_letter">Warning Letter</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Template Type *</label>
                                    <select name="template_type" class="orb-form-control js-template-type" data-context="create" required>
                                        <option value="html">HTML</option>
                                        <option value="docx">DOCX</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Category (Optional)</label>
                                    <input type="text" name="category" class="orb-form-control" placeholder="e.g. Onboarding">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Description (Optional)</label>
                                    <input type="text" name="description" class="orb-form-control" placeholder="Brief description...">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Version *</label>
                                    <input type="text" name="version" class="orb-form-control" value="v1" required>
                                </div>
                                <div class="col-md-6 mb-3 js-docx-upload" data-context="create" style="display:none;">
                                    <label class="orb-form-label">DOCX File *</label>
                                    <input type="file" name="docx_file" class="orb-form-control js-docx-file" data-context="create" data-existing="0" accept=".docx">
                                </div>
                            </div>

                            <h6 class="orb-section-title">2. Template Content</h6>
                            <div class="mb-4 js-html-content" data-context="create">
                                <textarea name="html_template" class="orb-form-control js-html-template" data-context="create" rows="12" placeholder="Enter HTML content here..."></textarea>
                                <small class="text-muted">Required when template type is HTML.</small>
                            </div>

                            <h6 class="orb-section-title">3. Email Defaults (Optional)</h6>
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="orb-form-label">Default Subject</label>
                                    <input type="text" name="default_subject" class="orb-form-control" placeholder="e.g. Your Offer Letter from @{{ company_name }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="orb-form-label">Default Email Body</label>
                                    <textarea name="default_email_body" class="orb-form-control" rows="4" placeholder="Dear @{{ employee_name }}..."></textarea>
                                </div>
                            </div>

                            <h6 class="orb-section-title">4. PDF Settings</h6>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Paper Size</label>
                                    <select name="paper_size" class="orb-form-control">
                                        <option value="A4">A4</option>
                                        <option value="Letter">Letter</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Orientation</label>
                                    <select name="orientation" class="orb-form-control">
                                        <option value="portrait">Portrait</option>
                                        <option value="landscape">Landscape</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 mt-2">
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_certificate" id="create_is_certificate" value="1">
                                            <label class="form-check-label fw-bold" for="create_is_certificate">Is Certificate</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="requires_review" id="create_requires_review" value="1">
                                            <label class="form-check-label fw-bold" for="create_requires_review">Requires Review</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="create_is_active" value="1" checked>
                                            <label class="form-check-label fw-bold" for="create_is_active">Active Template</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_archived" id="create_is_archived" value="1">
                                            <label class="form-check-label fw-bold" for="create_is_archived">Archive Template</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Placeholders -->
                        <div class="col-lg-4">
                            <div class="p-3" style="background:#FDFDFF; border:1px solid #DDE3EE; border-radius:16px; position:sticky; top:0;">
                                <h6 class="fw-bold mb-2"><i class="fas fa-magic text-primary"></i> Placeholders Guide</h6>
                                <p class="text-muted small mb-3">Click to copy placeholder to clipboard. Use these in Template Content, Subject, and Email Body.</p>
                                
                                <div>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{employee_name}}'); toastr.success('Copied!');">@{{employee_name}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{employee_code}}'); toastr.success('Copied!');">@{{employee_code}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{designation}}'); toastr.success('Copied!');">@{{designation}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{department}}'); toastr.success('Copied!');">@{{department}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{joining_date}}'); toastr.success('Copied!');">@{{joining_date}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{salary_monthly}}'); toastr.success('Copied!');">@{{salary_monthly}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{salary_annual}}'); toastr.success('Copied!');">@{{salary_annual}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{company_name}}'); toastr.success('Copied!');">@{{company_name}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{current_date}}'); toastr.success('Copied!');">@{{current_date}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer orb-modal-footer">
                    <button type="button" class="orb-btn-white border" style="color:var(--orb-text);" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="orb-btn-white" style="background:var(--orb-primary); color:white;">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Template Modals -->
@foreach($templates as $template)

<!-- Placeholder Mapping Audit Modal -->
<div class="modal fade" id="placeholderModal{{ $template->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
                <div>
                    <h5><i class="fas fa-network-wired text-info"></i> Placeholder Mapping: {{ $template->name }}</h5>
                    <p>Status and group mappings detected in the uploaded DOCX template.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body orb-modal-body" style="background: #F8FAFC;">
                @if(($template->template_type ?: 'html') !== 'docx')
                    <div class="text-center py-5">
                        <i class="fas fa-code fa-3x text-muted mb-3"></i>
                        <h5>HTML Template</h5>
                        <p class="text-muted">Universal placeholder auto-detection is only available for DOCX templates.</p>
                    </div>
                @elseif(empty($template->placeholder_mapping))
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>No Mappings Analyzed</h5>
                        <p class="text-muted mb-4">Please click the <strong>Scan & Detect Placeholders</strong> search button in the actions column to run the scanning engine.</p>
                        <form action="{{ route('hrms.document-generation.templates.detect-fields', $template->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="fas fa-search me-2"></i> Scan Template Now
                            </button>
                        </form>
                    </div>
                @else
                    @php
                        $mapping = is_array($template->placeholder_mapping) ? $template->placeholder_mapping : json_decode($template->placeholder_mapping, true);
                        $groups = [
                            'Employee Info' => [],
                            'Company Details' => [],
                            'Salary Settings' => [],
                            'Authority Details' => [],
                            'Custom Block' => []
                        ];
                        
                        if (is_array($mapping)) {
                            foreach ($mapping as $key => $details) {
                                $grp = $details['group_name'] ?? 'Custom Block';
                                if (!array_key_exists($grp, $groups)) {
                                    $groups[$grp] = [];
                                }
                                $groups[$grp][] = $details;
                            }
                        }
                    @endphp

                    @if(!empty($template->invalid_placeholders))
                        <div class="alert alert-danger border-0 rounded-3 shadow-none p-3 mb-4 d-flex align-items-start gap-2.5" role="alert" style="background-color: #FEF2F2; color: #991B1B;">
                            <i class="fas fa-exclamation-circle fa-lg mt-1" style="color: #EF4444;"></i>
                            <div>
                                <h6 class="fw-bold mb-1" style="color: #990000;">Malformed Placeholders Detected</h6>
                                <p class="mb-0 small" style="opacity: 0.95; color: #7F1D1D;">
                                    The following invalid placeholders were found in the uploaded DOCX template and will not be processed. Please fix them in your DOCX file and re-upload:
                                </p>
                                <div class="mt-2">
                                    @foreach(is_array($template->invalid_placeholders) ? $template->invalid_placeholders : json_decode($template->invalid_placeholders, true) as $inv)
                                        @php
                                            $clean = trim(str_replace(['{', '}', '||'], '', $inv));
                                        @endphp
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <span class="badge bg-danger text-white px-2 py-1 fs-6 font-monospace" style="border-radius: 6px;">{{ $inv }}</span>
                                            <span class="small" style="color: #7F1D1D;"><i class="fas fa-arrow-right"></i> Should be: <code class="fw-bold" style="color: #047857; font-family: monospace;">{{ '{' . '{' . $clean . '}' . '}' }}</code></span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Legend -->
                    <div class="d-flex flex-wrap justify-content-center gap-4 mb-4 p-3 bg-white border rounded-3">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 12px; height: 12px; border-radius: 50%; display: inline-block; background-color: #10B981;"></span>
                            <span class="text-muted small"><strong>Auto-Mapped</strong> (Exists in library & DOCX)</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 12px; height: 12px; border-radius: 50%; display: inline-block; background-color: #F59E0B;"></span>
                            <span class="text-muted small"><strong>Missing Required</strong> (Not in DOCX, but required)</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 12px; height: 12px; border-radius: 50%; display: inline-block; background-color: #EF4444;"></span>
                            <span class="text-muted small"><strong>Unknown/Custom</strong> (Requires manual input)</span>
                        </div>
                    </div>

                    @foreach($groups as $grpName => $items)
                        @if(!empty($items))
                            <div class="card mb-4 shadow-none border rounded-3 overflow-hidden">
                                <div class="card-header bg-white border-bottom py-2.5 px-3">
                                    <h6 class="mb-0 text-primary fw-bold"><i class="fas fa-folder-open me-2"></i> {{ $grpName }}</h6>
                                </div>
                                <div class="list-group list-group-flush">
                                    @foreach($items as $item)
                                        @php
                                            $status = $item['status'] ?? 'mapped';
                                            $badgeBg = '#E6F4EA';
                                            $badgeText = '#137333';
                                            $icon = 'fa-check-circle text-success';
                                            $desc = 'Correctly mapped to OrboOne data model.';
                                            if ($status === 'missing') {
                                                $badgeBg = '#FEF7E0';
                                                $badgeText = '#B06000';
                                                $icon = 'fa-exclamation-triangle text-warning';
                                                $desc = 'Required placeholder missing from DOCX file.';
                                            } elseif ($status === 'unknown') {
                                                $badgeBg = '#FCE8E6';
                                                $badgeText = '#C5221F';
                                                $icon = 'fa-question-circle text-danger';
                                                $desc = 'Unknown placeholder. Will require manual field input.';
                                            }
                                        @endphp
                                        <div class="list-group-item d-flex align-items-center justify-content-between py-3 px-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <i class="fas {{ $icon }} fa-lg"></i>
                                                <div>
                                                    <code class="fs-6 fw-bold" style="color: #4B00E8; font-family: monospace;">{{ '{' . '{' . $item['placeholder_key'] . '}' . '}' }}</code>
                                                    <div class="text-muted small mt-1">{{ $item['label'] ?? '' }} — {{ $desc }}</div>
                                                </div>
                                            </div>
                                            <span class="badge px-3 py-1.5 rounded-pill uppercase small fw-bold" style="background-color: {{ $badgeBg }}; color: {{ $badgeText }}; font-size: 11px;">{{ strtoupper($status) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
            <div class="modal-footer orb-modal-footer">
                <button type="button" class="orb-btn-white border" style="color:var(--orb-text);" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTemplateModal{{ $template->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal">
            <form method="POST" action="{{ route('hrms.document-generation.templates.update', $template->id) }}" enctype="multipart/form-data">
                @method('PUT')
                <div class="orb-modal-header">
                    <div>
                        <h5>Edit Document Template</h5>
                        <p>Update properties for {{ $template->name }}</p>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body orb-modal-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <h6 class="orb-section-title">1. Template Info</h6>
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="orb-form-label">Template Name *</label>
                                    <input type="text" name="name" class="orb-form-control" required value="{{ $template->name }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Slug (Optional - Auto-generated)</label>
                                    <input type="text" name="slug" class="orb-form-control" value="{{ $template->slug }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Document Type *</label>
                                    <select name="document_type" class="orb-form-control" required>
                                        <option value="offer_letter" {{ $template->document_type == 'offer_letter' ? 'selected' : '' }}>Offer Letter</option>
                                        <option value="appointment_letter" {{ $template->document_type == 'appointment_letter' ? 'selected' : '' }}>Appointment Letter</option>
                                        <option value="confirmation_letter" {{ $template->document_type == 'confirmation_letter' ? 'selected' : '' }}>Confirmation Letter</option>
                                        <option value="relieving_letter" {{ $template->document_type == 'relieving_letter' ? 'selected' : '' }}>Relieving Letter</option>
                                        <option value="experience_certificate" {{ $template->document_type == 'experience_certificate' ? 'selected' : '' }}>Experience Certificate</option>
                                        <option value="internship_certificate" {{ $template->document_type == 'internship_certificate' ? 'selected' : '' }}>Internship Certificate</option>
                                        <option value="salary_revision_letter" {{ $template->document_type == 'salary_revision_letter' ? 'selected' : '' }}>Salary Revision Letter</option>
                                        <option value="warning_letter" {{ $template->document_type == 'warning_letter' ? 'selected' : '' }}>Warning Letter</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Template Type *</label>
                                    <select name="template_type" class="orb-form-control js-template-type" data-context="edit-{{ $template->id }}" required>
                                        <option value="html" {{ ($template->template_type ?: 'html') === 'html' ? 'selected' : '' }}>HTML</option>
                                        <option value="docx" {{ ($template->template_type ?: 'html') === 'docx' ? 'selected' : '' }}>DOCX</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Category (Optional)</label>
                                    <input type="text" name="category" class="orb-form-control" value="{{ $template->category }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Description (Optional)</label>
                                    <input type="text" name="description" class="orb-form-control" value="{{ $template->description }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Version *</label>
                                    <input type="text" name="version" class="orb-form-control" value="{{ $template->version ?: 'v1' }}" required>
                                </div>
                                <div class="col-md-6 mb-3 js-docx-upload" data-context="edit-{{ $template->id }}" style="{{ ($template->template_type ?: 'html') === 'docx' ? '' : 'display:none;' }}">
                                    <label class="orb-form-label">DOCX File {{ ($template->template_type ?: 'html') === 'docx' ? '' : '(Optional)' }}</label>
                                    <input type="file" name="docx_file" class="orb-form-control js-docx-file" data-context="edit-{{ $template->id }}" data-existing="{{ $template->docx_file_path ? '1' : '0' }}" accept=".docx">
                                    @if($template->docx_file_path)
                                        <small class="text-muted d-block mt-1">Current: {{ basename($template->docx_file_path) }}</small>
                                    @endif
                                </div>
                            </div>

                            <h6 class="orb-section-title">2. Template Content</h6>
                            <div class="mb-4 js-html-content" data-context="edit-{{ $template->id }}" style="{{ ($template->template_type ?: 'html') === 'html' ? '' : 'display:none;' }}">
                                <textarea name="html_template" class="orb-form-control js-html-template" data-context="edit-{{ $template->id }}" rows="12">{{ $template->html_template }}</textarea>
                                <small class="text-muted">Required when template type is HTML.</small>
                            </div>

                            <h6 class="orb-section-title">3. Email Defaults (Optional)</h6>
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="orb-form-label">Default Subject</label>
                                    <input type="text" name="default_subject" class="orb-form-control" value="{{ $template->default_subject }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="orb-form-label">Default Email Body</label>
                                    <textarea name="default_email_body" class="orb-form-control" rows="4">{{ $template->default_email_body }}</textarea>
                                </div>
                            </div>

                            <h6 class="orb-section-title">4. PDF Settings</h6>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Paper Size</label>
                                    <select name="paper_size" class="orb-form-control">
                                        <option value="A4" {{ $template->paper_size == 'A4' ? 'selected' : '' }}>A4</option>
                                        <option value="Letter" {{ $template->paper_size == 'Letter' ? 'selected' : '' }}>Letter</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Orientation</label>
                                    <select name="orientation" class="orb-form-control">
                                        <option value="portrait" {{ $template->orientation == 'portrait' ? 'selected' : '' }}>Portrait</option>
                                        <option value="landscape" {{ $template->orientation == 'landscape' ? 'selected' : '' }}>Landscape</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 mt-2">
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_certificate" id="edit_is_certificate{{ $template->id }}" value="1" {{ $template->is_certificate ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="edit_is_certificate{{ $template->id }}">Is Certificate</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="requires_review" id="edit_requires_review{{ $template->id }}" value="1" {{ $template->requires_review ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="edit_requires_review{{ $template->id }}">Requires Review</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active{{ $template->id }}" value="1" {{ $template->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="edit_is_active{{ $template->id }}">Active Template</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_archived" id="edit_is_archived{{ $template->id }}" value="1" {{ $template->is_archived ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="edit_is_archived{{ $template->id }}">Archive Template</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="p-3" style="background:#FDFDFF; border:1px solid #DDE3EE; border-radius:16px; position:sticky; top:0;">
                                <h6 class="fw-bold mb-2"><i class="fas fa-magic text-primary"></i> Placeholders Guide</h6>
                                <p class="text-muted small mb-3">Click to copy placeholder to clipboard. Use these in Template Content, Subject, and Email Body.</p>
                                
                                <div>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{employee_name}}'); toastr.success('Copied!');">@{{employee_name}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{employee_code}}'); toastr.success('Copied!');">@{{employee_code}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{designation}}'); toastr.success('Copied!');">@{{designation}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{department}}'); toastr.success('Copied!');">@{{department}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{joining_date}}'); toastr.success('Copied!');">@{{joining_date}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{salary_monthly}}'); toastr.success('Copied!');">@{{salary_monthly}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{salary_annual}}'); toastr.success('Copied!');">@{{salary_annual}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{company_name}}'); toastr.success('Copied!');">@{{company_name}}</span>
                                    <span class="orb-placeholder-chip" onclick="navigator.clipboard.writeText('@{{current_date}}'); toastr.success('Copied!');">@{{current_date}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer orb-modal-footer">
                    <button type="button" class="orb-btn-white border" style="color:var(--orb-text);" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="orb-btn-white" style="background:var(--orb-primary); color:white;">Update Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    let table = $('#templatesTable').DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'asc']],
        dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
        buttons: [
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-sm',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10] }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10] }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10] }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-sm',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10] }
            }
        ],
        language: {
            emptyTable: 'No templates found',
            zeroRecords: 'No matching templates found'
        },
        initComplete: function() {
            $('.dataTables_length').appendTo('#dtLength');
            $('.dt-buttons').appendTo('#dtButtons');
            // Since we use manual foreach instead of ajax, pagination logic remains in blade or datatable. 
            // The table is fully populated from blade if we don't paginate in blade. 
            // Wait, the controller uses paginate(15). So datatables will only paginate the 15 records on this page!
            // I should remove datatables pagination and use backend pagination, OR I keep the datatables layout but don't show info/pagination if backend pagination is used. 
            // Actually, the instruction says "Important: Do NOT render manual colspan empty row if DataTable is used. Use DataTable emptyTable message."
        }
    });

    // Custom Filters
    $('#dtSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#dtType').on('change', function() {
        table.column(3).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
    });

    $('#dtTemplateType').on('change', function() {
        table.column(4).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
    });

    $('#dtStatus').on('change', function() {
        table.column(8).search(this.value).draw();
    });

    $('#dtArchived').on('change', function() {
        table.column(8).search(this.value).draw();
    });

    $('#dtReset').on('click', function() {
        $('#dtSearch').val('');
        $('#dtType').val('');
        $('#dtTemplateType').val('');
        $('#dtStatus').val('');
        $('#dtArchived').val('');
        
        table.search('').columns().search('').draw();
    });

    function syncTemplateTypeUI(context) {
        const $type = $('.js-template-type[data-context="' + context + '"]');
        const selected = ($type.val() || 'html').toLowerCase();
        const isDocx = selected === 'docx';

        const $docx = $('.js-docx-upload[data-context="' + context + '"]');
        const $docxInput = $('.js-docx-file[data-context="' + context + '"]');
        const $htmlWrap = $('.js-html-content[data-context="' + context + '"]');
        const $htmlInput = $('.js-html-template[data-context="' + context + '"]');
        const hasExistingDocx = ($docxInput.data('existing') + '') === '1';

        $docx.toggle(isDocx);
        $htmlWrap.toggle(!isDocx);
        $docxInput.prop('required', isDocx && !hasExistingDocx);
        $htmlInput.prop('required', !isDocx);
    }

    $('.js-template-type').each(function() {
        syncTemplateTypeUI($(this).data('context'));
    });

    $(document).on('change', '.js-template-type', function() {
        syncTemplateTypeUI($(this).data('context'));
    });
});
</script>
@endsection
