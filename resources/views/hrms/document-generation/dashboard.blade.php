@extends('layouts.panel', ['active' => 'document_generation'])

@section('page_title', 'Document Generation Dashboard')

@section('_head')
<style>
    :root {
        --orb-success: #00C896;
        --orb-warning: #F59E0B;
        --orb-info: #3B82F6;
        --orb-purple: #8B5CF6;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft-bg: rgba(106, 17, 203, 0.05);
        --orb-shadow: 0 16px 36px rgba(16, 24, 40, 0.05);
    }

    .document-page {
        background: var(--orb-bg);
        padding: 24px;
        min-height: calc(100vh - 80px);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* PREMIUM GRADIENT GLASS HERO */
    .orb-hero-glass {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        border-radius: 24px;
        padding: 40px;
        color: white;
        margin-bottom: 28px;
        box-shadow: 0 20px 40px rgba(106, 17, 203, 0.15);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .orb-hero-glass::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    .orb-hero-content {
        position: relative;
        z-index: 2;
    }

    .orb-kicker {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .orb-hero-glass h1 {
        font-size: 36px;
        font-weight: 900;
        margin: 0 0 10px 0;
        letter-spacing: -0.5px;
        line-height: 1.15;
    }

    .orb-hero-glass p {
        margin: 0 0 24px 0;
        opacity: 0.9;
        font-size: 15px;
        font-weight: 500;
        max-width: 680px;
        line-height: 1.6;
    }

    /* QUICK STATS CHIPS */
    .orb-hero-chips {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .stat-chip {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 50px;
        padding: 6px 16px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: white;
    }

    .stat-chip i {
        font-size: 10px;
        opacity: 0.8;
    }

    .orb-hero-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    /* MODERN PILL BUTTONS */
    .btn-pill {
        border-radius: 50px;
        padding: 12px 24px;
        font-weight: 700;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .btn-pill-white {
        background: #ffffff;
        color: var(--orb-primary) !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .btn-pill-white:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        background: #f8f9fa;
    }

    .btn-pill-trans {
        background: rgba(255, 255, 255, 0.12);
        color: white !important;
        border-color: rgba(255, 255, 255, 0.2);
    }

    .btn-pill-trans:hover {
        background: rgba(255, 255, 255, 0.22);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    /* KPI GLASS CARDS */
    .kpi-card {
        background: #ffffff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        padding: 24px;
        box-shadow: var(--orb-shadow);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .kpi-card::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: transparent;
        transition: background-color 0.3s;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 35px rgba(106, 17, 203, 0.08);
    }

    .kpi-card-1:hover::after { background: var(--orb-primary); }
    .kpi-card-2:hover::after { background: var(--orb-secondary); }
    .kpi-card-3:hover::after { background: var(--orb-success); }
    .kpi-card-4:hover::after { background: var(--orb-info); }

    .kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .kpi-title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--orb-muted);
        letter-spacing: 0.5px;
    }

    .kpi-value {
        font-size: 32px;
        font-weight: 900;
        color: var(--orb-text);
        line-height: 1;
        margin-top: 4px;
    }

    .kpi-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.3s;
    }

    .kpi-card:hover .kpi-icon-box {
        transform: scale(1.1) rotate(5deg);
    }

    .kpi-sub {
        font-size: 12px;
        color: var(--orb-muted);
        font-weight: 550;
        margin: 0;
    }

    /* QUICK ACTIONS */
    .quick-actions-section {
        margin-bottom: 28px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--orb-text);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        color: var(--orb-primary);
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
    }

    .action-card {
        background: #ffffff;
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        text-decoration: none !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.01);
    }

    .action-card:hover {
        transform: translateY(-4px);
        border-color: var(--orb-primary);
        box-shadow: 0 12px 24px rgba(106, 17, 203, 0.06);
    }

    .action-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 14px;
        transition: all 0.3s;
    }

    .action-card:hover .action-icon {
        transform: scale(1.1);
    }

    .action-title {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--orb-text);
        margin-bottom: 6px;
    }

    .action-desc {
        font-size: 11px;
        color: var(--orb-muted);
        margin: 0;
        line-height: 1.4;
    }

    /* RECENT DOCUMENTS TABLE CARD */
    .dashboard-card {
        background: #ffffff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        height: 100%;
    }

    .dashboard-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--orb-border);
        background: linear-gradient(180deg, #ffffff, #fafbfe);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .dashboard-card-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--orb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dashboard-card-title i {
        color: var(--orb-primary);
    }

    /* RECENT DOCUMENTS REDESIGN */
    .premium-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .premium-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 14px 20px;
        border-bottom: 1px solid #e2e8f0;
        letter-spacing: 0.5px;
    }

    .premium-table tbody tr {
        transition: background-color 0.2s;
    }

    .premium-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    .premium-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: var(--orb-text);
        font-size: 13.5px;
    }

    .doc-block {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .doc-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .doc-name {
        font-weight: 750;
        color: var(--orb-text);
        text-decoration: none !important;
    }

    .doc-name:hover {
        color: var(--orb-primary);
    }

    .doc-number {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 1px;
        font-family: monospace;
    }

    .recipient-block {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .recipient-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
        color: #4f46e5;
        font-weight: 700;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #e0e7ff;
    }

    .recipient-name {
        font-weight: 700;
        color: var(--orb-text);
        font-size: 13px;
    }

    .recipient-code {
        font-size: 11px;
        color: var(--orb-muted);
    }

    .badge-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: #eef2ff;
        color: #4f46e5;
    }

    .badge-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .status-generated { background: #e0f2fe; color: #0369a1; }
    .status-sent { background: #dcfce7; color: #166534; }
    .status-viewed { background: #faf5ff; color: #6b21a8; }
    .status-downloaded { background: #e0f2fe; color: #0369a1; }
    .status-draft { background: #f1f5f9; color: #475569; }
    .status-failed { background: #fee2e2; color: #991b1b; }

    .date-main {
        font-weight: 650;
        color: var(--orb-text);
    }

    .date-sub {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 1px;
    }

    /* ACTIONS & BUTTONS */
    .att-actions-container {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-action-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        transition: all 0.2s;
        font-size: 12px;
    }

    .btn-action-icon:hover {
        background: #f1f5f9;
        color: var(--orb-primary);
        border-color: #cbd5e1;
    }

    /* RECENT ACTIVITY TIMELINE */
    .timeline {
        position: relative;
        padding-left: 10px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 24px;
        padding-left: 28px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 4px;
        top: 4px;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-dot {
        position: absolute;
        left: 0;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--orb-primary);
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px var(--orb-primary);
        z-index: 2;
    }

    .timeline-item.sent .timeline-dot {
        background: var(--orb-success);
        box-shadow: 0 0 0 2px var(--orb-success);
    }

    .timeline-item.reviewed .timeline-dot {
        background: var(--orb-info);
        box-shadow: 0 0 0 2px var(--orb-info);
    }

    .timeline-content {
        font-size: 13px;
        color: var(--orb-text);
    }

    .timeline-time {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 2px;
    }

    /* DOCUMENT ANALYTICS */
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-top: 28px;
    }

    .analytics-card {
        background: #ffffff;
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.01);
    }

    .analytics-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--orb-muted);
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .analytics-value-block {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .analytics-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .analytics-text-primary {
        font-size: 16px;
        font-weight: 800;
        color: var(--orb-text);
    }

    .analytics-text-sub {
        font-size: 11px;
        color: var(--orb-muted);
        margin-top: 2px;
    }

    /* RESPONSIVE LAYOUT */
    @media(max-width: 1400px) {
        .action-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .analytics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width: 991px) {
        .action-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .analytics-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width: 768px) {
        .orb-hero-glass {
            padding: 30px;
        }
        .orb-hero-glass h1 {
            font-size: 28px;
        }
        .action-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('_content')
<div class="document-page">

    <!-- SECTION 1 – HERO AREA -->
    <div class="orb-hero-glass" style="padding: 24px 32px; margin-bottom: 24px;">
        <div class="orb-hero-content">
            <div class="orb-kicker">
                <i class="fas fa-file-signature"></i>
                HR DOCUMENT CENTER
            </div>
            <h1 style="font-size: 30px; margin-bottom: 6px;">Document Generation</h1>
            <p style="margin-bottom: 0; font-size: 14px;">Create, preview, generate, download and securely deliver HR documents from a centralized workspace.</p>
        </div>
    </div>

    <!-- SECTION 2 – KPI CARDS -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="kpi-card kpi-card-1">
                <div class="kpi-top">
                    <div>
                        <div class="kpi-title">Total Documents</div>
                        <div class="kpi-value">{{ $generatedDocuments }}</div>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(106, 17, 203, 0.1); color: var(--orb-primary);">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="kpi-sub">Generated records</div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="kpi-card kpi-card-2">
                <div class="kpi-top">
                    <div>
                        <div class="kpi-title">Active Templates</div>
                        <div class="kpi-value">{{ $activeTemplates }}</div>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(255, 75, 110, 0.1); color: var(--orb-secondary);">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
                <div class="kpi-sub">Available templates</div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="kpi-card kpi-card-3">
                <div class="kpi-top">
                    <div>
                        <div class="kpi-title">Emails Delivered</div>
                        <div class="kpi-value">{{ $sentDocuments }}</div>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(0, 200, 150, 0.1); color: var(--orb-success);">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                </div>
                <div class="kpi-sub">Successfully sent</div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="kpi-card kpi-card-4">
                <div class="kpi-top">
                    <div>
                        <div class="kpi-title">Recent Activity</div>
                        <div class="kpi-value">Last 7 Days</div>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(59, 130, 246, 0.1); color: var(--orb-info);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="kpi-sub">Document operations</div>
            </div>
        </div>
    </div>

    <!-- SECTION 3 – QUICK ACTIONS AREA -->
    <div class="quick-actions-section">
        <h4 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h4>
        <div class="action-grid">
            @if(Route::has('hrms.document-generation.generated.create'))
            <a href="{{ route('hrms.document-generation.generated.create') }}" class="action-card">
                <div class="action-icon" style="background: rgba(106, 17, 203, 0.08); color: var(--orb-primary);">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="action-title">Generate Document</div>
                <div class="action-desc">Generate candidate and employee letters instantly.</div>
            </a>
            @endif

            @if(Route::has('hrms.document-generation.generated.index'))
            <a href="{{ route('hrms.document-generation.generated.index') }}" class="action-card">
                <div class="action-icon" style="background: rgba(255, 75, 110, 0.08); color: var(--orb-secondary);">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="action-title">View Generated Documents</div>
                <div class="action-desc">Search, preview, download, and email generated records.</div>
            </a>
            @endif

            @if(Route::has('hrms.document-generation.templates.index'))
            <a href="{{ route('hrms.document-generation.templates.index') }}" class="action-card">
                <div class="action-icon" style="background: rgba(0, 200, 150, 0.08); color: var(--orb-success);">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="action-title">Manage Templates</div>
                <div class="action-desc">Design and update HTML document blueprints.</div>
            </a>
            @endif

            @if(Route::has('hrms.document-generation.generated.index'))
            <a href="{{ route('hrms.document-generation.generated.index') }}?status=sent" class="action-card">
                <div class="action-icon" style="background: rgba(59, 130, 246, 0.08); color: var(--orb-info);">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div class="action-title">Email History</div>
                <div class="action-desc">Audit logs and delivery status of emailed letters.</div>
            </a>
            @endif

            <a href="#analytics-section" class="action-card">
                <div class="action-icon" style="background: rgba(139, 92, 246, 0.08); color: var(--orb-purple);">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="action-title">Document Analytics</div>
                <div class="action-desc">View generation trends, most used templates, and volumes.</div>
            </a>
        </div>
    </div>

    <!-- MAIN DASHBOARD CONTENT: TABLE (Full Width) -->
    <div class="row">
        <!-- SECTION 4 – RECENT GENERATED DOCUMENTS -->
        <div class="col-lg-12 mb-4">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h5 class="dashboard-card-title"><i class="fas fa-clock"></i> Recent Generated Documents</h5>
                    <div>
                        @if(Route::has('hrms.document-generation.generated.index'))
                        <a href="{{ route('hrms.document-generation.generated.index') }}" class="btn btn-sm btn-light rounded-pill px-3 border fw-bold">View All History</a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Recipient</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Generated Date</th>
                                    <th class="text-right pr-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDocuments as $doc)
                                <tr>
                                    <!-- Document Column -->
                                    <td>
                                        <div class="doc-block">
                                            <div class="doc-icon"><i class="fas fa-file-pdf text-danger"></i></div>
                                            <div>
                                                @if(Route::has('hrms.document-generation.generated.show'))
                                                <a href="{{ route('hrms.document-generation.generated.show', $doc->id) }}" class="doc-name">
                                                    {{ $doc->template->name ?? ucwords(str_replace('_', ' ', $doc->document_type)) }}
                                                </a>
                                                @else
                                                <span class="doc-name">{{ $doc->template->name ?? ucwords(str_replace('_', ' ', $doc->document_type)) }}</span>
                                                @endif
                                                <div class="doc-number">{{ $doc->document_number }}</div>
                                            </div>
                                        </div>
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
                                                <div class="recipient-code">{{ $doc->employee->employee_code }}</div>
                                            </div>
                                        </div>
                                        @else
                                        <div class="recipient-block">
                                            <div class="recipient-avatar" style="background: #fff7ed; color: #ea580c;"><i class="fas fa-user"></i></div>
                                            <div>
                                                <div class="recipient-name">{{ $doc->candidate_name ?: 'Candidate' }}</div>
                                                <div class="recipient-code"><span class="badge bg-warning text-dark" style="font-size: 9px; padding: 2px 6px;">Manual Document</span></div>
                                            </div>
                                        </div>
                                        @endif
                                    </td>

                                    <!-- Type Column -->
                                    <td>
                                        <span class="badge-chip">
                                            {{ ucwords(str_replace('_', ' ', $doc->document_type)) }}
                                        </span>
                                    </td>

                                    <!-- Status Column -->
                                    <td>
                                        @if($doc->status == 'sent')
                                            <span class="badge-status-pill status-sent"><i class="fas fa-paper-plane mr-1"></i> Sent</span>
                                        @elseif($doc->status == 'reviewed')
                                            <span class="badge-status-pill status-viewed"><i class="fas fa-check-double mr-1"></i> Reviewed</span>
                                        @elseif($doc->status == 'generated')
                                            <span class="badge-status-pill status-generated"><i class="fas fa-file-alt mr-1"></i> Generated</span>
                                        @elseif($doc->status == 'draft')
                                            <span class="badge-status-pill status-draft"><i class="fas fa-edit mr-1"></i> Draft</span>
                                        @else
                                            <span class="badge-status-pill status-draft"><i class="fas fa-circle mr-1"></i> {{ ucfirst($doc->status) }}</span>
                                        @endif
                                    </td>

                                    <!-- Generated Date -->
                                    <td>
                                        <div class="date-main">{{ $doc->created_at->format('d M Y') }}</div>
                                        <div class="date-sub">{{ $doc->created_at->format('h:i A') }}</div>
                                    </td>

                                    <!-- Actions Column -->
                                    <td>
                                        <div class="att-actions-container justify-content-end pr-3">
                                            @if(Route::has('hrms.document-generation.generated.show'))
                                            <a href="{{ route('hrms.document-generation.generated.show', $doc->id) }}" class="btn-action-icon" title="View Document" style="color: var(--orb-primary);">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endif
                                            @if(Route::has('hrms.document-generation.generated.download'))
                                            <a href="{{ route('hrms.document-generation.generated.download', $doc->id) }}" class="btn-action-icon" title="Download PDF" style="color: var(--orb-success);">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="p-0">
                                        <div class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fs-1 mb-3 text-secondary"></i>
                                            <h5>No documents generated yet.</h5>
                                            <p class="small mb-3">Create your first HR document to get started.</p>
                                            @if(Route::has('hrms.document-generation.generated.create'))
                                            <a href="{{ route('hrms.document-generation.generated.create') }}" class="btn btn-sm btn-primary rounded-pill px-4" style="background: var(--orb-primary); border: none;">Generate Document</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 6 – DOCUMENT ANALYTICS -->
    <div id="analytics-section" class="quick-actions-section mt-4">
        <h4 class="section-title"><i class="fas fa-chart-pie"></i> Document Analytics</h4>
        <div class="analytics-grid">
            @php
                $mostGenerated = $recentDocuments->groupBy('document_type')->sortByDesc(fn($g) => $g->count())->keys()->first();
                $mostGeneratedName = $mostGenerated ? ucwords(str_replace('_', ' ', $mostGenerated)) : 'Offer Letter';

                $sentCount = $recentDocuments->where('status', 'sent')->count();
                $draftCount = $recentDocuments->where('status', 'draft')->count();
            @endphp
            <div class="analytics-card">
                <div class="analytics-label">Most Generated Document Type</div>
                <div class="analytics-value-block">
                    <div class="analytics-icon" style="background: rgba(106, 17, 203, 0.1); color: var(--orb-primary);">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div>
                        <div class="analytics-text-primary">{{ $mostGeneratedName }}</div>
                        <div class="analytics-text-sub">Based on recent generations</div>
                    </div>
                </div>
            </div>

            <div class="analytics-card">
                <div class="analytics-label">Pending Delivery / Drafts</div>
                <div class="analytics-value-block">
                    <div class="analytics-icon" style="background: rgba(255, 75, 110, 0.1); color: var(--orb-secondary);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="analytics-text-primary">{{ $draftCount }} Documents</div>
                        <div class="analytics-text-sub">Requires approval/sending</div>
                    </div>
                </div>
            </div>

            <div class="analytics-card">
                <div class="analytics-label">Delivery Success Rate</div>
                <div class="analytics-value-block">
                    <div class="analytics-icon" style="background: rgba(0, 200, 150, 0.1); color: var(--orb-success);">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div>
                        <div class="analytics-text-primary">
                            {{ $generatedDocuments > 0 ? round(($sentDocuments / $generatedDocuments) * 100, 1) : 100 }}%
                        </div>
                        <div class="analytics-text-sub">Sent vs total generated ratio</div>
                    </div>
                </div>
            </div>

            <div class="analytics-card">
                <div class="analytics-label">Recent Trend</div>
                <div class="analytics-value-block">
                    <div class="analytics-icon" style="background: rgba(59, 130, 246, 0.1); color: var(--orb-info);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div class="analytics-text-primary">Active</div>
                        <div class="analytics-text-sub">Continuous operations</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
