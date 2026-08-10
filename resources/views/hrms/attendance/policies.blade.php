@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => 'attendances'])

@section('_content')
<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        background: var(--orb-bg) !important;
        overflow-x: hidden !important;
    }

    .att-page {
        width: 100%;
        max-width: 100%;
        min-height: calc(100vh - 80px);
        padding: 24px;
        background: var(--orb-bg);
        overflow-x: hidden;
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* HERO BANNER */
    .orb-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 26px 28px;
        color: #fff;
        box-shadow: 0 20px 45px rgba(0, 0, 0, .12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin: 0 0 24px;
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

    .orb-hero-content, .orb-hero-actions {
        position: relative;
        z-index: 2;
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
        font-size: 14px;
    }

    /* STAT CARDS */
    .stat-card-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--orb-shadow);
    }

    .stat-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .stat-val {
        font-size: 24px;
        font-weight: 900;
        color: var(--orb-text);
        line-height: 1.2;
    }

    .stat-lbl {
        font-size: 12px;
        font-weight: 700;
        color: var(--orb-muted);
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    /* FILTER BAR */
    .filter-bar {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 14px 18px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .filter-pills {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-pill {
        padding: 8px 16px;
        border-radius: 999px;
        border: 1px solid var(--orb-border);
        background: #F9FAFB;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s ease;
        text-decoration: none !important;
    }

    .filter-pill.active, .filter-pill:hover {
        background: var(--orb-primary);
        color: #fff;
        border-color: var(--orb-primary);
    }

    .search-box {
        position: relative;
        min-width: 260px;
    }

    .search-box input {
        width: 100%;
        padding: 9px 16px 9px 38px;
        border-radius: 999px;
        border: 1px solid var(--orb-border);
        font-size: 13px;
        outline: none;
        transition: border-color .2s ease;
    }

    .search-box input:focus {
        border-color: var(--orb-primary);
    }

    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--orb-muted);
        font-size: 14px;
    }

    /* POLICY CARDS GRID */
    .policy-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 20px;
    }

    .policy-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: all .25s ease;
    }

    .policy-card:hover {
        box-shadow: var(--orb-shadow);
        border-color: rgba(75, 0, 232, .25);
        transform: translateY(-3px);
    }

    .policy-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .policy-title {
        font-size: 18px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0 0 4px;
    }

    .policy-desc {
        font-size: 13px;
        color: var(--orb-muted);
        margin: 0;
        line-height: 1.4;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .status-active {
        background: #ECFDF5;
        color: #10B981;
        border: 1px solid #A7F3D0;
    }

    .status-inactive {
        background: #FEF2F2;
        color: #EF4444;
        border: 1px solid #FECACA;
    }

    /* METRIC TILES INSIDE CARD */
    .metric-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        background: var(--orb-soft);
        padding: 14px;
        border-radius: 14px;
        margin-bottom: 16px;
    }

    .metric-item {
        display: flex;
        flex-direction: column;
    }

    .metric-val {
        font-size: 16px;
        font-weight: 900;
        color: var(--orb-primary);
    }

    .metric-lbl {
        font-size: 11px;
        font-weight: 700;
        color: var(--orb-muted);
        text-transform: uppercase;
    }

    /* BADGE PILLS */
    .badge-section {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 16px;
    }

    .feature-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        background: #F3F4F6;
        color: #374151;
    }

    .feature-pill.pill-primary {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .feature-pill.pill-success {
        background: #ECFDF5;
        color: #059669;
    }

    .feature-pill.pill-warning {
        background: #FFFBEB;
        color: #D97706;
    }

    .policy-footer {
        padding-top: 14px;
        border-top: 1px dashed var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .btn-orb-edit {
        background: var(--orb-soft);
        color: var(--orb-primary);
        font-weight: 800;
        font-size: 12px;
        border-radius: 10px;
        padding: 8px 16px;
        border: none;
        transition: all .2s ease;
    }

    .btn-orb-edit:hover {
        background: var(--orb-primary);
        color: #fff;
    }

    /* ========== HRMS PREMIUM MODAL STYLING ========== */
    .att-modal-content {
        border-radius: 24px !important;
        border: none !important;
        overflow: hidden !important;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25) !important;
        background: #fff !important;
    }

    .att-modal-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        padding: 22px 28px !important;
        color: #fff !important;
        border-bottom: none !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .att-modal-title {
        font-size: 18px !important;
        font-weight: 900 !important;
        color: #fff !important;
        margin: 0 !important;
        letter-spacing: -.02em;
    }

    .att-modal-subtitle {
        font-size: 12.5px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin-top: 3px !important;
        font-weight: 600 !important;
    }

    .att-modal-header .close {
        color: #fff !important;
        opacity: 0.85 !important;
        text-shadow: none !important;
        font-size: 24px !important;
        font-weight: 300 !important;
        outline: none !important;
        transition: all .2s ease;
        padding: 0 !important;
        margin: 0 !important;
    }

    .att-modal-header .close:hover {
        opacity: 1 !important;
        transform: scale(1.1);
    }

    .att-modal-body {
        padding: 24px 28px !important;
        background: #fff !important;
    }

    .att-modal-section {
        border: 1px solid #EEF2F6;
        background: #FCFCFD;
        border-radius: 18px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .att-modal-section-title {
        font-size: 12.5px;
        font-weight: 900;
        color: var(--orb-text);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .att-modal-section-title i {
        color: var(--orb-primary);
        font-size: 14px;
    }

    .att-modal-body label {
        font-size: 11px !important;
        font-weight: 900 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: .04em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .att-modal-body .form-control {
        height: 42px !important;
        border-radius: 12px !important;
        border: 1.5px solid #E2E8F0 !important;
        font-size: 13.5px !important;
        font-weight: 700 !important;
        color: #1E293B !important;
        background-color: #fff !important;
        transition: all .2s ease !important;
    }

    .att-modal-body .form-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1) !important;
    }

    .att-modal-footer {
        padding: 16px 28px !important;
        background: #F8FAFC !important;
        border-top: 1px solid #EEF2F6 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 12px !important;
    }

    .btn-modal-cancel {
        background: #E2E8F0 !important;
        color: #475569 !important;
        font-weight: 800 !important;
        border-radius: 999px !important;
        padding: 10px 24px !important;
        border: none !important;
        font-size: 13px !important;
        transition: all .2s ease !important;
    }

    .btn-modal-cancel:hover {
        background: #CBD5E1 !important;
        color: #1E293B !important;
    }

    .btn-modal-save {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
        font-weight: 800 !important;
        border-radius: 999px !important;
        padding: 10px 28px !important;
        border: none !important;
        font-size: 13px !important;
        box-shadow: 0 4px 14px rgba(75, 0, 232, .25) !important;
        transition: all .2s ease !important;
    }

    .btn-modal-save:hover {
        opacity: .92 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 18px rgba(75, 0, 232, .35) !important;
    }
</style>

<div class="att-page">
    <div class="att-container">

        {{-- ALERT MESSAGES --}}
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-15 mb-3" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- HERO BANNER USING DB BRANDING THEME --}}
        <div class="orb-hero">
            <div class="orb-hero-content">
                <div class="orb-hero-kicker">
                    <i class="fas fa-shield-alt"></i> HR Governance & Rules
                </div>
                <h1>Attendance Policy Master</h1>
                <p>Manage DB-driven attendance policies, work minute thresholds, WFH quotas, and discipline governance rules.</p>
            </div>
            <div class="orb-hero-actions">
                @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                    <button class="btn btn-light rounded-pill font-weight-bold shadow-sm text-primary px-4 py-2" data-toggle="modal" data-target="#createPolicyModal">
                        <i class="fas fa-plus-circle mr-1"></i> Create New Policy
                    </button>
                @endif
            </div>
        </div>

        {{-- STAT CARDS --}}
        <div class="stat-card-row">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background:var(--orb-soft); color:var(--orb-primary);">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $attendancePolicies->count() }}</div>
                    <div class="stat-lbl">Total Policies</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background:#ECFDF5; color:#10B981;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $attendancePolicies->where('is_active', true)->count() }}</div>
                    <div class="stat-lbl">Active Policies</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background:#F0F9FF; color:#0284C7;">
                    <i class="fas fa-laptop-house"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $attendancePolicies->where('wfh_enabled', true)->count() }}</div>
                    <div class="stat-lbl">WFH Allowed</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background:#FFFBEB; color:#D97706;">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $attendancePolicies->where('regularization_enabled', true)->count() }}</div>
                    <div class="stat-lbl">Regularization Enabled</div>
                </div>
            </div>
        </div>

        {{-- FILTER BAR --}}
        <div class="filter-bar">
            <div class="filter-pills" id="policyFilterPills">
                <button type="button" class="filter-pill active" onclick="filterPolicies('all', this)">All Policies</button>
                <button type="button" class="filter-pill" onclick="filterPolicies('full_time', this)">Full Time</button>
                <button type="button" class="filter-pill" onclick="filterPolicies('part_time', this)">Part Time</button>
                <button type="button" class="filter-pill" onclick="filterPolicies('wfh', this)">WFH Enabled</button>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="policySearchInput" onkeyup="searchPolicies()" placeholder="Search policy name...">
            </div>
        </div>

        {{-- DYNAMIC POLICY CARDS GRID --}}
        <div class="policy-grid" id="policyCardGrid">
            @forelse($attendancePolicies as $policy)
                @php
                    $isPartTime = str_contains(strtolower($policy->policy_name), 'part time');
                    $categoryTag = $isPartTime ? 'part_time' : 'full_time';
                    $wfhTag = $policy->wfh_enabled ? 'wfh' : '';
                @endphp
                <div class="policy-card policy-item" data-category="{{ $categoryTag }} {{ $wfhTag }}" data-name="{{ strtolower($policy->policy_name) }}">
                    <div>
                        <div class="policy-header">
                            <div>
                                <h3 class="policy-title">{{ $policy->policy_name }}</h3>
                                <p class="policy-desc">{{ $policy->description ?? 'No policy description specified.' }}</p>
                            </div>
                            <span class="status-badge {{ $policy->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        {{-- DYNAMIC WORK MINUTE THRESHOLDS --}}
                        <div class="metric-grid">
                            <div class="metric-item">
                                <span class="metric-val">{{ $policy->required_work_minutes ?? 480 }} mins</span>
                                <span class="metric-lbl">Req. Work Min</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-val">{{ $policy->half_day_min_minutes ?? 240 }} mins</span>
                                <span class="metric-lbl">Half Day Min</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-val">{{ $policy->absent_below_minutes ?? 120 }} mins</span>
                                <span class="metric-lbl">Absent Below</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-val">{{ $policy->lunch_break_minutes ?? 60 }} mins</span>
                                <span class="metric-lbl">Lunch Break</span>
                            </div>
                        </div>

                        {{-- DYNAMIC GOVERNANCE BADGES --}}
                        <div class="badge-section">
                            <span class="feature-pill pill-primary">
                                <i class="fas fa-clock"></i> Early Out Half Day: {{ $policy->early_out_half_day_minutes ?? 60 }}m
                            </span>
                            <span class="feature-pill {{ $policy->punch_block_enabled ? 'pill-warning' : 'pill-secondary' }}">
                                <i class="fas fa-lock"></i> Punch Block: {{ $policy->punch_block_enabled ? 'ON' : 'OFF' }}
                            </span>
                            <span class="feature-pill {{ $policy->auto_absent_enabled ? 'pill-danger' : 'pill-secondary' }}" style="{{ $policy->auto_absent_enabled ? 'background:#FEF2F2; color:#DC2626;' : '' }}">
                                <i class="fas fa-user-slash"></i> Auto Absent: {{ $policy->auto_absent_enabled ? 'ON' : 'OFF' }}
                            </span>
                            <span class="feature-pill pill-success">
                                <i class="fas fa-exclamation-circle"></i> Violation Limit: {{ $policy->combined_violation_limit ?? 3 }}
                            </span>
                            @if($policy->wfh_enabled)
                                <span class="feature-pill pill-primary">
                                    <i class="fas fa-laptop"></i> WFH Limit: {{ $policy->monthly_wfh_limit ?? 2 }}/mo
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="policy-footer">
                        <small class="text-muted font-weight-bold">
                            <i class="far fa-calendar-alt mr-1"></i> Updated {{ \Carbon\Carbon::parse($policy->updated_at)->diffForHumans() }}
                        </small>
                        @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                            <button class="btn btn-orb-edit" data-toggle="modal" data-target="#editPolicyModal{{ $policy->id }}">
                                <i class="fas fa-sliders-h mr-1"></i> Edit Rules
                            </button>
                        @endif
                    </div>
                </div>

                {{-- EDIT MODAL FOR DYNAMIC POLICY ROW (HRMS STANDARD UI) --}}
                <div class="modal fade" id="editPolicyModal{{ $policy->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <form method="POST" action="{{ route('attendance.policy_rules.update', $policy->id) }}" class="modal-content att-modal-content">
                            @csrf
                            @method('PUT')
                            
                            {{-- MODAL HEADER --}}
                            <div class="modal-header att-modal-header">
                                <div>
                                    <h5 class="att-modal-title"><i class="fas fa-shield-alt mr-2"></i> Edit Attendance Policy</h5>
                                    <div class="att-modal-subtitle">{{ $policy->policy_name }} &bull; Governance & Discipline Rules</div>
                                </div>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            {{-- MODAL BODY WITH STRUCTURED HRMS SECTIONS --}}
                            <div class="modal-body att-modal-body">
                                
                                {{-- SECTION 1: POLICY IDENTIFICATION --}}
                                <div class="att-modal-section">
                                    <div class="att-modal-section-title">
                                        <i class="fas fa-tag"></i> Policy Details & Scope
                                    </div>
                                    <div class="row">
                                        <div class="col-md-7 form-group">
                                            <label>Policy Name</label>
                                            <input type="text" name="policy_name" class="form-control" value="{{ $policy->policy_name }}" required>
                                        </div>
                                        <div class="col-md-5 form-group">
                                            <label>Working Hours / Day</label>
                                            <input type="number" step="0.5" name="working_hours_per_day" class="form-control" value="{{ $policy->working_hours_per_day ?? 8 }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- SECTION 2: WORK MINUTE THRESHOLDS --}}
                                <div class="att-modal-section">
                                    <div class="att-modal-section-title">
                                        <i class="fas fa-clock"></i> Work Minute Threshold Rules
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label>Req. Work Mins</label>
                                            <input type="number" name="required_work_minutes" class="form-control" value="{{ $policy->required_work_minutes ?? 480 }}">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Half Day Min Mins</label>
                                            <input type="number" name="half_day_min_minutes" class="form-control" value="{{ $policy->half_day_min_minutes ?? 240 }}">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Absent Below Mins</label>
                                            <input type="number" name="absent_below_minutes" class="form-control" value="{{ $policy->absent_below_minutes ?? 120 }}">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Lunch Break Mins</label>
                                            <input type="number" name="lunch_break_minutes" class="form-control" value="{{ $policy->lunch_break_minutes ?? 60 }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- SECTION 3: DISCIPLINE & GOVERNANCE --}}
                                <div class="att-modal-section mb-0">
                                    <div class="att-modal-section-title">
                                        <i class="fas fa-user-shield"></i> Discipline, Automation & Governance
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Allowed Missed Punches</label>
                                            <input type="number" name="allowed_missed_punches" class="form-control" value="{{ $policy->allowed_missed_punches ?? 2 }}">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Combined Violation Limit</label>
                                            <input type="number" name="combined_violation_limit" class="form-control" value="{{ $policy->combined_violation_limit ?? 3 }}">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Monthly WFH Limit</label>
                                            <input type="number" name="monthly_wfh_limit" class="form-control" value="{{ $policy->monthly_wfh_limit ?? 2 }}">
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6 form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="autoBlockSwitch{{ $policy->id }}" name="auto_block_enabled" value="1" {{ $policy->auto_block_enabled ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark" for="autoBlockSwitch{{ $policy->id }}">Auto Block Punch-In Enabled</label>
                                            </div>
                                            <small class="text-muted d-block mt-1">Blocks punch-in automatically when late time window expires.</small>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="autoAbsentSwitch{{ $policy->id }}" name="auto_absent_enabled" value="1" {{ $policy->auto_absent_enabled ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark" for="autoAbsentSwitch{{ $policy->id }}">Auto Absent Execution Enabled</label>
                                            </div>
                                            <small class="text-muted d-block mt-1">Marks unpunched or late un-regularized days as Absent at day end.</small>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6 form-group mb-0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="wfhEnabledSwitch{{ $policy->id }}" name="wfh_enabled" value="1" {{ $policy->wfh_enabled ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark" for="wfhEnabledSwitch{{ $policy->id }}">Allow Work From Home (WFH)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group mb-0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="isActiveSwitch{{ $policy->id }}" name="is_active" value="1" {{ $policy->is_active ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark" for="isActiveSwitch{{ $policy->id }}">Policy Is Active</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- MODAL FOOTER --}}
                            <div class="modal-footer att-modal-footer">
                                <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-modal-save">
                                    <i class="fas fa-check-circle mr-1"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-folder-open text-muted fa-3x mb-3"></i>
                    <h5 class="text-muted font-weight-bold">No Attendance Policies Found in Database</h5>
                </div>
            @endforelse
        </div>

    </div>
</div>

<script>
    function filterPolicies(category, element) {
        document.querySelectorAll('#policyFilterPills .filter-pill').forEach(el => el.classList.remove('active'));
        element.classList.add('active');

        const items = document.querySelectorAll('#policyCardGrid .policy-item');
        items.forEach(item => {
            const cat = item.getAttribute('data-category');
            if (category === 'all' || cat.includes(category)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function searchPolicies() {
        const query = document.getElementById('policySearchInput').value.toLowerCase();
        const items = document.querySelectorAll('#policyCardGrid .policy-item');
        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
@endsection
