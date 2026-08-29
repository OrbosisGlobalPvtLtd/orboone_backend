@extends('layouts.panel', ['active' => 'access_control'])

@section('page_title', 'RBAC Access Visualizer & Matrix Explorer')

@section('_head')
@include('access_control.partials.styles')
<style>
    /* Visualizer Custom Styling */
    .vis-hero {
        background: linear-gradient(135deg, var(--ac-primary, #4B00E8) 0%, #7622FF 50%, var(--ac-secondary, #FF5252) 100%);
        border-radius: 24px;
        padding: 28px 32px;
        color: #ffffff;
        margin-bottom: 24px;
        box-shadow: 0 12px 32px rgba(75, 0, 232, 0.22);
        position: relative;
        overflow: hidden;
    }
    .vis-hero::after {
        content: "";
        position: absolute;
        width: 320px;
        height: 320px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
        right: -60px;
        top: -100px;
        pointer-events: none;
    }
    .vis-hero-title {
        font-size: 26px;
        font-weight: 900;
        margin: 0 0 6px;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .vis-hero-subtitle {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.88);
        margin: 0;
        max-width: 800px;
        line-height: 1.5;
    }

    /* Metric Strip */
    .vis-metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .vis-metric-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        padding: 18px 20px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .vis-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }
    .vis-metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .vis-icon-primary { background: #EEF2FF; color: var(--ac-primary, #4B00E8); }
    .vis-icon-success { background: #ECFDF5; color: #059669; }
    .vis-icon-warning { background: #FFFBEB; color: #D97706; }
    .vis-icon-purple { background: #FAF5FF; color: #9333EA; }
    .vis-icon-danger { background: #FEF2F2; color: #DC2626; }

    .vis-metric-val {
        font-size: 24px;
        font-weight: 900;
        color: #0F172A;
        line-height: 1.1;
    }
    .vis-metric-lbl {
        font-size: 11px;
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }

    /* Controls & Filter Bar */
    .vis-toolbar-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    }
    .vis-view-tabs {
        display: flex;
        gap: 8px;
        background: #F1F5F9;
        padding: 4px;
        border-radius: 14px;
        overflow-x: auto;
    }
    .vis-view-tab {
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 750;
        color: #475569;
        text-decoration: none !important;
        cursor: pointer;
        border: none;
        background: transparent;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    .vis-view-tab:hover {
        color: var(--ac-primary, #4B00E8);
        background: rgba(255, 255, 255, 0.6);
    }
    .vis-view-tab.active {
        background: #ffffff;
        color: var(--ac-primary, #4B00E8);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .vis-search-box {
        position: relative;
        flex: 1;
        min-width: 240px;
    }
    .vis-search-box input {
        width: 100%;
        height: 42px;
        border-radius: 12px;
        border: 1px solid #CBD5E1;
        padding: 0 16px 0 42px;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        transition: all 0.2s ease;
    }
    .vis-search-box input:focus {
        border-color: var(--ac-primary, #4B00E8);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
    }
    .vis-search-box i {
        position: absolute;
        left: 14px;
        top: 13px;
        color: #94A3B8;
        font-size: 15px;
    }

    .vis-filter-select {
        height: 42px;
        border-radius: 12px;
        border: 1px solid #CBD5E1;
        padding: 0 14px;
        font-size: 13px;
        font-weight: 700;
        color: #0F172A;
        outline: none;
        background: #ffffff;
        min-width: 180px;
    }

    /* Role Pills Selector */
    .vis-roles-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #F1F5F9;
        align-items: center;
    }
    .vis-role-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        background: #F8FAFC;
        border: 1px solid #CBD5E1;
        color: #334155;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
    }
    .vis-role-pill:hover {
        background: #EEF2FF;
        border-color: #A5B4FC;
    }
    .vis-role-pill.active {
        background: #EEF2FF;
        border-color: var(--ac-primary, #4B00E8);
        color: var(--ac-primary, #4B00E8);
    }
    .vis-role-pill input[type="checkbox"] {
        accent-color: var(--ac-primary, #4B00E8);
        cursor: pointer;
    }

    /* Matrix Module Accordion Card */
    .vis-mod-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .vis-mod-header {
        padding: 16px 24px;
        background: #F8FAFC;
        border-bottom: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
    }
    .vis-mod-header:hover {
        background: #F1F5F9;
    }
    .vis-mod-title {
        font-size: 16px;
        font-weight: 900;
        color: #0F172A;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .vis-mod-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 20px;
        background: #EEF2FF;
        color: var(--ac-primary, #4B00E8);
        text-transform: uppercase;
    }

    /* Submenu Row */
    .vis-sub-row {
        padding: 18px 24px;
        border-bottom: 1px solid #F1F5F9;
        background: #ffffff;
    }
    .vis-sub-row:last-child {
        border-bottom: none;
    }
    .vis-sub-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .vis-sub-title {
        font-size: 14px;
        font-weight: 800;
        color: #1E293B;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Matrix Table */
    .vis-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-top: 10px;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
    }
    .vis-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        min-width: 800px;
    }
    .vis-table th {
        background: #F8FAFC;
        padding: 10px 14px;
        font-weight: 850;
        color: #475569;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #E2E8F0;
        border-right: 1px solid #F1F5F9;
        white-space: nowrap;
        text-align: center;
    }
    .vis-table th.col-feature {
        text-align: left;
        min-width: 240px;
        width: 25%;
    }
    .vis-table td {
        padding: 10px 14px;
        border-bottom: 1px solid #F1F5F9;
        border-right: 1px solid #F1F5F9;
        text-align: center;
        vertical-align: middle;
        background: #ffffff;
    }
    .vis-table tr:hover td {
        background: #F8FAFC;
    }
    .vis-table td.col-feature {
        text-align: left;
        font-weight: 750;
        color: #1E293B;
    }

    /* Badges & Status Icons */
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 11px;
        line-height: 1;
        cursor: pointer;
        transition: transform 0.15s ease;
    }
    .status-badge:hover {
        transform: scale(1.05);
    }
    .status-badge-granted {
        background: #ECFDF5;
        color: #059669;
        border: 1px solid #A7F3D0;
    }
    .status-badge-denied {
        background: #FEF2F2;
        color: #DC2626;
        border: 1px solid #FECACA;
        opacity: 0.7;
    }
    .status-badge-super {
        background: linear-gradient(135deg, #FEF3C7, #FDE68A);
        color: #B45309;
        border: 1px solid #FCD34D;
        font-weight: 900;
    }
    .status-badge-page-on {
        background: #EFF6FF;
        color: #2563EB;
        border: 1px solid #BFDBFE;
    }
    .status-badge-page-off {
        background: #F1F5F9;
        color: #94A3B8;
        border: 1px solid #E2E8F0;
    }

    .crud-type-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .crud-type-view { background: #EFF6FF; color: #1D4ED8; }
    .crud-type-create { background: #ECFDF5; color: #047857; }
    .crud-type-edit { background: #FFFBEB; color: #B45309; }
    .crud-type-delete { background: #FEF2F2; color: #B91C1C; }
    .crud-type-manage { background: #FAF5FF; color: #7E22CE; }

    /* Simulator Styling */
    .sim-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.04);
    }
    .sim-step-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 10px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
    }
    .sim-step-item.step-granted {
        background: #ECFDF5;
        border-color: #A7F3D0;
    }
    .sim-step-item.step-denied {
        background: #FEF2F2;
        border-color: #FECACA;
    }
    .sim-step-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .sim-verdict-box {
        padding: 20px;
        border-radius: 16px;
        margin-top: 18px;
        text-align: center;
    }
    .verdict-allowed {
        background: linear-gradient(135deg, #ECFDF5, #D1FAE5);
        border: 2px solid #34D399;
        color: #065F46;
    }
    .verdict-denied {
        background: linear-gradient(135deg, #FEF2F2, #FEE2E2);
        border: 2px solid #F87171;
        color: #991B1B;
    }

    /* Role Deep-Dive Card */
    .role-dive-header {
        background: linear-gradient(135deg, #1E293B 0%, #334155 100%);
        border-radius: 20px;
        padding: 24px;
        color: #ffffff;
        margin-bottom: 20px;
    }
    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
        background: #E2E8F0;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.4s ease;
    }

    @media print {
        .sidebar, .topbar, .vis-toolbar-card, .ac-nav-tabs-wrapper, .ac-btn, .vis-roles-bar {
            display: none !important;
        }
        .ac-page, .vis-hero {
            padding: 0 !important;
            background: none !important;
            box-shadow: none !important;
        }
        .vis-mod-card {
            border: 1px solid #000 !important;
            page-break-inside: avoid;
        }
    }
</style>
@endsection

@section('_content')
<div class="ac-page">
    <div class="ac-container">

        <!-- Navigation Tabs -->
        @include('access_control.partials.nav')

        <!-- Hero Header -->
        <div class="vis-hero">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px; position: relative; z-index: 2;">
                <div>
                    <div class="ac-kicker mb-2">
                        <i class="fas fa-shield-alt"></i> SYSTEM ACCESS GOVERNANCE
                    </div>
                    <h1 class="vis-hero-title">
                        <i class="fas fa-chart-network"></i> RBAC Dynamic Access Visualizer
                    </h1>
                    <p class="vis-hero-subtitle">
                        Interactive cross-role permission matrix, real-time page visibility mapping, granular CRUD distribution, and instant authorization diagnostic simulator.
                    </p>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                    <a href="{{ route('access_control.visualizer.export') }}" class="ac-btn ac-btn-primary" style="background: #ffffff !important; color: var(--ac-primary, #4B00E8) !important; font-weight: 800;">
                        <i class="fas fa-file-csv mr-1"></i> Export Full Matrix (CSV)
                    </a>
                    <button type="button" onclick="window.print()" class="ac-btn ac-btn-soft">
                        <i class="fas fa-print mr-1"></i> Print Report
                    </button>
                </div>
            </div>
        </div>

        @include('access_control.partials.flash')

        <!-- Metric Summary Strip -->
        <div class="vis-metric-grid">
            <div class="vis-metric-card">
                <div class="vis-metric-icon vis-icon-primary">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <div class="vis-metric-val">{{ $systemTotals['total_roles'] }}</div>
                    <div class="vis-metric-lbl">Total System Roles</div>
                </div>
            </div>

            <div class="vis-metric-card">
                <div class="vis-metric-icon vis-icon-success">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <div class="vis-metric-val">{{ $systemTotals['total_permissions'] }}</div>
                    <div class="vis-metric-lbl">CRUD Permissions</div>
                </div>
            </div>

            <div class="vis-metric-card">
                <div class="vis-metric-icon vis-icon-warning">
                    <i class="fas fa-browser"></i>
                </div>
                <div>
                    <div class="vis-metric-val">{{ $systemTotals['total_menus'] }}</div>
                    <div class="vis-metric-lbl">Navigation Pages</div>
                </div>
            </div>

            <div class="vis-metric-card">
                <div class="vis-metric-icon vis-icon-purple">
                    <i class="fas fa-crown"></i>
                </div>
                <div>
                    <div class="vis-metric-val">{{ $systemTotals['high_privilege_roles'] }}</div>
                    <div class="vis-metric-lbl">High-Privilege Roles</div>
                </div>
            </div>
        </div>

        <!-- Interactive Toolbar & Filter Console -->
        <div class="vis-toolbar-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
                <!-- View Mode Switcher -->
                <div class="vis-view-tabs" id="viewModeTabs">
                    <button type="button" class="vis-view-tab active" onclick="switchViewMode('matrix')">
                        <i class="fas fa-th"></i> Cross-Role Matrix
                    </button>
                    <button type="button" class="vis-view-tab" onclick="switchViewMode('role')">
                        <i class="fas fa-user-tag"></i> Role Deep-Dive
                    </button>
                    <button type="button" class="vis-view-tab" onclick="switchViewMode('module')">
                        <i class="fas fa-folder-open"></i> Module Inspector
                    </button>
                    <button type="button" class="vis-view-tab" onclick="switchViewMode('simulator')">
                        <i class="fas fa-bolt"></i> Live Access Tester
                    </button>
                </div>

                <!-- Global Live Search & Quick Filter -->
                <div class="d-flex align-items-center flex-wrap" style="gap: 10px; flex: 1; max-width: 600px;">
                    <div class="vis-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="liveSearchInput" placeholder="Search any module, page, route, or permission key..." oninput="handleLiveFilter()">
                    </div>

                    <select id="moduleFilterSelect" class="vis-filter-select" onchange="handleLiveFilter()">
                        <option value="">All Modules ({{ count($modulesTree) }})</option>
                        @foreach($modulesTree as $m)
                            <option value="{{ strtolower($m['name']) }}">{{ $m['name'] }}</option>
                        @endforeach
                    </select>

                    <select id="crudFilterSelect" class="vis-filter-select" style="min-width: 140px;" onchange="handleLiveFilter()">
                        <option value="">All Actions</option>
                        <option value="view">View Only</option>
                        <option value="create">Create Only</option>
                        <option value="edit">Edit Only</option>
                        <option value="delete">Delete Only</option>
                        <option value="manage">Manage Only</option>
                    </select>
                </div>
            </div>

            <!-- Role Visibility Toggle Bar (For Cross-Role Matrix Mode) -->
            <div class="vis-roles-bar" id="roleToggleBar">
                <span class="small font-weight-bold text-muted mr-2"><i class="fas fa-filter mr-1"></i> Active Roles to Compare:</span>
                @foreach($roles as $r)
                    <label class="vis-role-pill active" id="pill_role_{{ $r->id }}">
                        <input type="checkbox" class="role-column-toggle" value="{{ $r->id }}" checked onchange="toggleRoleColumn({{ $r->id }}, this.checked)">
                        <span>{{ $r->name }}</span>
                        @if($r->slug === 'super_admin') <i class="fas fa-crown text-warning ml-1" style="font-size: 10px;"></i> @endif
                    </label>
                @endforeach
                <button type="button" class="btn btn-xs btn-link font-weight-bold text-primary ml-auto p-0" onclick="expandCollapseAll(true)">
                    <i class="fas fa-angle-double-down mr-1"></i> Expand All
                </button>
                <button type="button" class="btn btn-xs btn-link font-weight-bold text-secondary p-0" onclick="expandCollapseAll(false)">
                    <i class="fas fa-angle-double-up mr-1"></i> Collapse All
                </button>
            </div>
        </div>

        <!-- ====================================================================== -->
        <!-- VIEW MODE 1: UNIFIED CROSS-ROLE MATRIX                                  -->
        <!-- ====================================================================== -->
        <div id="viewMatrixContainer" class="vis-view-panel">
            @foreach($modulesTree as $module)
                <div class="vis-mod-card module-block" data-module-name="{{ strtolower($module['name']) }}">
                    <!-- Module Level Header -->
                    <div class="vis-mod-header" onclick="toggleModuleAccordion(this)">
                        <div class="vis-mod-title">
                            <i class="fas fa-folder-open text-primary"></i>
                            <span>{{ $module['name'] }}</span>
                            <span class="vis-mod-badge">{{ count($module['submenus']) }} Pages &bull; {{ count($module['permissions_list']) }} Actions</span>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 12px;">
                            <i class="fas fa-chevron-down text-muted accordion-arrow transition"></i>
                        </div>
                    </div>

                    <!-- Module Body Content -->
                    <div class="vis-mod-body">
                        <!-- Module Root Actions Matrix (if any) -->
                        @if(!empty($module['permissions_list']))
                            <div class="px-4 py-3 bg-light border-bottom">
                                <div class="small font-weight-bold text-muted mb-2"><i class="fas fa-layer-group mr-1"></i> Module Level Action Permissions:</div>
                                <div class="vis-table-wrap mb-0">
                                    <table class="vis-table">
                                        <thead>
                                            <tr>
                                                <th class="col-feature">Action Permission</th>
                                                <th style="width: 100px;">CRUD Type</th>
                                                @foreach($roles as $role)
                                                    <th class="role-col-{{ $role->id }}">{{ $role->name }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($module['permissions_list'] as $perm)
                                                <tr class="permission-row" data-action="{{ $perm['crud_type'] }}" data-search="{{ strtolower($perm['key'] . ' ' . $perm['description'] . ' ' . $module['name']) }}">
                                                    <td class="col-feature">
                                                        <div class="text-dark font-weight-bold">{{ $perm['key'] }}</div>
                                                        <div class="text-muted small" style="font-size: 11px;">{{ $perm['description'] }}</div>
                                                    </td>
                                                    <td>
                                                        <span class="crud-type-pill crud-type-{{ $perm['crud_type'] }}">{{ $perm['crud_type'] }}</span>
                                                    </td>
                                                    @foreach($roles as $role)
                                                        @php
                                                            $access = $perm['role_access'][$role->id] ?? ['granted' => false, 'is_super_admin' => false];
                                                        @endphp
                                                        <td class="role-col-{{ $role->id }}">
                                                            @if($access['is_super_admin'])
                                                                <span class="status-badge status-badge-super" title="Super Admin: Unconditional Bypass"><i class="fas fa-crown mr-1"></i> Super</span>
                                                            @elseif($access['granted'])
                                                                <span class="status-badge status-badge-granted" title="Granted for {{ $role->name }}"><i class="fas fa-check"></i> Allowed</span>
                                                            @else
                                                                <span class="status-badge status-badge-denied" title="Denied for {{ $role->name }}"><i class="fas fa-times"></i> Denied</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Submenus / Pages Section -->
                        @if(!empty($module['submenus']))
                            @foreach($module['submenus'] as $sub)
                                <div class="vis-sub-row submenu-block" data-search="{{ strtolower($sub['name'] . ' ' . ($sub['route'] ?? '') . ' ' . $module['name']) }}">
                                    <!-- Submenu Header Strip -->
                                    <div class="vis-sub-header">
                                        <div class="vis-sub-title">
                                            <i class="fas fa-file-alt text-muted"></i>
                                            <span>{{ $sub['name'] }}</span>
                                            @if(!empty($sub['route']))
                                                <span class="badge badge-light border text-monospace font-weight-normal small">{{ $sub['route'] }}</span>
                                            @endif
                                            @if(!empty($sub['permission_key']))
                                                <span class="badge badge-info text-monospace small" title="Primary Key"><i class="fas fa-key mr-1"></i>{{ $sub['permission_key'] }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Table for Page & Submenu CRUD -->
                                    <div class="vis-table-wrap">
                                        <table class="vis-table">
                                            <thead>
                                                <tr>
                                                    <th class="col-feature">Feature / Action</th>
                                                    <th style="width: 110px;">Type</th>
                                                    @foreach($roles as $role)
                                                        <th class="role-col-{{ $role->id }}">{{ $role->name }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Row 1: Page Navigation / Menu Access -->
                                                <tr class="permission-row" data-action="view" data-search="{{ strtolower($sub['name'] . ' ' . ($sub['route'] ?? '') . ' page access') }}">
                                                    <td class="col-feature">
                                                        <div class="font-weight-bold text-primary"><i class="fas fa-browser mr-1"></i> Page Visibility in Sidebar</div>
                                                        <div class="text-muted small" style="font-size: 11px;">Access to visit URL and view page in navigation</div>
                                                    </td>
                                                    <td>
                                                        <span class="crud-type-pill crud-type-view">PAGE ACCESS</span>
                                                    </td>
                                                    @foreach($roles as $role)
                                                        @php
                                                            $menuAcc = $sub['role_access'][$role->id] ?? ['menu_access' => false, 'is_super_admin' => false];
                                                        @endphp
                                                        <td class="role-col-{{ $role->id }}">
                                                            @if($menuAcc['is_super_admin'])
                                                                <span class="status-badge status-badge-super" title="Super Admin Full Access"><i class="fas fa-crown mr-1"></i> Super</span>
                                                            @elseif($menuAcc['menu_access'])
                                                                <span class="status-badge status-badge-page-on" title="Page is visible in sidebar for {{ $role->name }}"><i class="fas fa-eye mr-1"></i> Visible</span>
                                                            @else
                                                                <span class="status-badge status-badge-page-off" title="Hidden for {{ $role->name }}"><i class="fas fa-eye-slash mr-1"></i> Hidden</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>

                                                <!-- Rows: Granular CRUD Actions under Page -->
                                                @foreach($sub['permissions_list'] as $perm)
                                                    <tr class="permission-row" data-action="{{ $perm['crud_type'] }}" data-search="{{ strtolower($perm['key'] . ' ' . $perm['description'] . ' ' . $sub['name']) }}">
                                                        <td class="col-feature">
                                                            <div class="text-dark font-weight-bold">{{ $perm['key'] }}</div>
                                                            <div class="text-muted small" style="font-size: 11px;">{{ $perm['description'] }}</div>
                                                        </td>
                                                        <td>
                                                            <span class="crud-type-pill crud-type-{{ $perm['crud_type'] }}">{{ $perm['crud_type'] }}</span>
                                                        </td>
                                                        @foreach($roles as $role)
                                                            @php
                                                                $access = $perm['role_access'][$role->id] ?? ['granted' => false, 'is_super_admin' => false];
                                                            @endphp
                                                            <td class="role-col-{{ $role->id }}">
                                                                @if($access['is_super_admin'])
                                                                    <span class="status-badge status-badge-super" title="Super Admin"><i class="fas fa-crown mr-1"></i> Super</span>
                                                                @elseif($access['granted'])
                                                                    <span class="status-badge status-badge-granted" title="Granted"><i class="fas fa-check"></i> Allowed</span>
                                                                @else
                                                                    <span class="status-badge status-badge-denied" title="Denied"><i class="fas fa-times"></i> Denied</span>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- ====================================================================== -->
        <!-- VIEW MODE 2: ROLE DEEP-DIVE EXPLORER                                    -->
        <!-- ====================================================================== -->
        <div id="viewRoleContainer" class="vis-view-panel" style="display: none;">
            <div class="role-dive-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
                    <div>
                        <span class="text-uppercase small font-weight-bold" style="letter-spacing: 1px; color: #94A3B8;">Target Role Profile Inspection</span>
                        <h2 class="m-0 font-weight-bold text-white mt-1" id="roleProfileName">{{ $roles->first()->name }}</h2>
                        <p class="m-0 text-muted small mt-1" id="roleProfileSlug">Slug: {{ $roles->first()->slug }} &bull; Active Users: {{ $roleMetrics[$roles->first()->id]['user_count'] ?? 0 }}</p>
                    </div>
                    <div>
                        <label class="small text-white font-weight-bold d-block mb-1">Switch Role to Inspect:</label>
                        <select id="roleDeepDiveSelect" class="form-control form-control-lg font-weight-bold" style="border-radius: 12px; min-width: 260px;" onchange="loadRoleDeepDive(this.value)">
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ $r->id === $roles->first()->id ? 'selected' : '' }}>
                                    {{ $r->name }} ({{ $r->slug }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Role Metrics Cards -->
            <div class="row mb-4" id="roleMetricGauges">
                <div class="col-md-3 mb-3">
                    <div class="ac-card p-3 h-100">
                        <div class="small font-weight-bold text-muted text-uppercase">Permission Coverage</div>
                        <div class="h3 font-weight-bold text-primary mt-2 mb-1" id="rolePermPct">100%</div>
                        <div class="progress-bar-custom mt-2">
                            <div class="progress-fill bg-primary" id="rolePermFill" style="width: 100%;"></div>
                        </div>
                        <div class="small text-muted mt-2" id="rolePermFraction">244 of 244 permissions</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="ac-card p-3 h-100">
                        <div class="small font-weight-bold text-muted text-uppercase">Navigation Page Access</div>
                        <div class="h3 font-weight-bold text-success mt-2 mb-1" id="roleMenuPct">100%</div>
                        <div class="progress-bar-custom mt-2">
                            <div class="progress-fill bg-success" id="roleMenuFill" style="width: 100%;"></div>
                        </div>
                        <div class="small text-muted mt-2" id="roleMenuFraction">112 of 126 menus</div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="ac-card p-3 h-100">
                        <div class="small font-weight-bold text-muted text-uppercase mb-2">CRUD Action Breakdown</div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;" id="roleCrudChips">
                            <span class="crud-type-pill crud-type-view p-2 font-weight-bold" id="roleCrudView">View: 120</span>
                            <span class="crud-type-pill crud-type-create p-2 font-weight-bold" id="roleCrudCreate">Create: 45</span>
                            <span class="crud-type-pill crud-type-edit p-2 font-weight-bold" id="roleCrudEdit">Edit: 50</span>
                            <span class="crud-type-pill crud-type-delete p-2 font-weight-bold" id="roleCrudDelete">Delete: 20</span>
                            <span class="crud-type-pill crud-type-manage p-2 font-weight-bold" id="roleCrudManage">Manage: 9</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deep Dive Module Tree -->
            <div id="roleDeepDiveTree"></div>
        </div>

        <!-- ====================================================================== -->
        <!-- VIEW MODE 3: MODULE INSPECTOR                                          -->
        <!-- ====================================================================== -->
        <div id="viewModuleContainer" class="vis-view-panel" style="display: none;">
            <div class="ac-card p-4 mb-4">
                <h4 class="font-weight-bold text-dark mb-3"><i class="fas fa-search-plus text-primary mr-2"></i>Module & Page Centric Inspector</h4>
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="font-weight-bold small text-muted text-uppercase">Select Target Module to Inspect:</label>
                        <select id="moduleInspectorSelect" class="form-control form-control-lg font-weight-bold" style="border-radius: 12px;" onchange="loadModuleInspector(this.value)">
                            @foreach($modulesTree as $m)
                                <option value="{{ $m['id'] }}">{{ $m['name'] }} ({{ count($m['submenus']) }} Pages)</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div id="moduleInspectorOutput"></div>
        </div>

        <!-- ====================================================================== -->
        <!-- VIEW MODE 4: LIVE ACCESS TESTER & DIAGNOSTIC SIMULATOR                 -->
        <!-- ====================================================================== -->
        <div id="viewSimulatorContainer" class="vis-view-panel" style="display: none;">
            <div class="row">
                <!-- Test Configuration Column -->
                <div class="col-lg-5 mb-4">
                    <div class="sim-card h-100">
                        <h4 class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-bolt text-warning mr-2"></i>Live Access Tester
                        </h4>
                        <p class="text-muted small mb-4">Simulate real-time RBAC policy resolution with step-by-step audit tracing.</p>

                        <form id="simulatorForm" onsubmit="runSimulator(event)">
                            <!-- Target Type -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase">1. Evaluation Target Type:</label>
                                <div class="d-flex" style="gap: 12px;">
                                    <label class="ac-check flex-fill active" id="targetTypeRoleLabel">
                                        <input type="radio" name="target_type" value="role" checked onchange="toggleSimTargetType('role')">
                                        <div><strong>System Role</strong><span>Test by Role Matrix</span></div>
                                    </label>
                                    <label class="ac-check flex-fill" id="targetTypeUserLabel">
                                        <input type="radio" name="target_type" value="user" onchange="toggleSimTargetType('user')">
                                        <div><strong>Specific User</strong><span>Include Overrides</span></div>
                                    </label>
                                </div>
                            </div>

                            <!-- Target Selector: Role -->
                            <div class="form-group mb-3" id="simRoleSelectGroup">
                                <label class="font-weight-bold small text-muted text-uppercase">Target System Role:</label>
                                <select id="simTargetRoleId" class="form-control font-weight-bold" style="border-radius: 10px; height: 44px;">
                                    @foreach($roles as $r)
                                        <option value="{{ $r->id }}" {{ $r->slug === 'employee' ? 'selected' : '' }}>
                                            {{ $r->name }} ({{ $r->slug }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Target Selector: User -->
                            <div class="form-group mb-3" id="simUserSelectGroup" style="display: none;">
                                <label class="font-weight-bold small text-muted text-uppercase">Target Active User:</label>
                                <select id="simTargetUserId" class="form-control font-weight-bold select2-searchable" style="border-radius: 10px; height: 44px;">
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">
                                            {{ $u->name }} ({{ $u->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Resource Type -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase">2. Resource to Check:</label>
                                <select id="simResourceType" class="form-control font-weight-bold mb-2" style="border-radius: 10px; height: 44px;" onchange="toggleSimResourceType(this.value)">
                                    <option value="permission" selected>Permission Key (e.g. leave.approve, payroll.generate)</option>
                                    <option value="menu">Page Navigation / Menu (Sidebar Access)</option>
                                </select>
                            </div>

                            <!-- Resource Key: Permission Autocomplete -->
                            <div class="form-group mb-4" id="simPermKeyGroup">
                                <label class="font-weight-bold small text-muted text-uppercase">Select or Type Permission Key:</label>
                                <select id="simPermissionKey" class="form-control font-weight-bold" style="border-radius: 10px; height: 44px;">
                                    @foreach($allPermissions as $p)
                                        <option value="{{ $p->key }}">
                                            [{{ $p->module }}] {{ $p->key }} ({{ $p->description ?: $p->action }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Resource Key: Menu Select -->
                            <div class="form-group mb-4" id="simMenuKeyGroup" style="display: none;">
                                <label class="font-weight-bold small text-muted text-uppercase">Select Navigation Page:</label>
                                <select id="simMenuKey" class="form-control font-weight-bold" style="border-radius: 10px; height: 44px;">
                                    @foreach($allMenus as $m)
                                        <option value="{{ $m->id }}">
                                            {{ $m->name }} (Route: {{ $m->route ?: 'Parent Container' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="ac-btn ac-btn-primary w-100" style="background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important; color: #fff !important; height: 46px; font-size: 14px; border-radius: 12px;">
                                <i class="fas fa-play mr-2"></i> Run Diagnostic Authorization Trace
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Simulation Output Trace Column -->
                <div class="col-lg-7 mb-4">
                    <div class="sim-card h-100" id="simResultsContainer">
                        <div class="text-center py-5 text-muted" id="simPlaceholder">
                            <i class="fas fa-shield-check fa-4x text-light mb-3"></i>
                            <h5 class="font-weight-bold text-secondary">Ready to Simulate</h5>
                            <p class="small text-muted">Select a target Role or User and resource key on the left, then click <strong>Run Diagnostic Trace</strong> to view the real-time authorization decision breakdown.</p>
                        </div>

                        <div id="simResultsContent" style="display: none;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="font-weight-bold text-dark m-0"><i class="fas fa-microscope text-primary mr-2"></i>Diagnostic Decision Trace</h5>
                                <span class="badge badge-light border text-monospace" id="simExecutionTime">Resolved in &lt;1ms</span>
                            </div>

                            <div id="simVerdictBox" class="sim-verdict-box mb-4">
                                <h3 class="font-weight-bold m-0" id="simVerdictTitle">ALLOWED</h3>
                                <p class="m-0 mt-1 font-weight-bold small" id="simVerdictReason"></p>
                            </div>

                            <h6 class="font-weight-bold text-muted text-uppercase small mb-2"><i class="fas fa-list-ol mr-1"></i> Step-by-Step Authorization Evaluation Pipeline:</h6>
                            <div id="simStepsList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ====================================================================== -->
<!-- JAVASCRIPT LOGIC & DYNAMIC FILTERING                                    -->
<!-- ====================================================================== -->
<script>
const rawModulesTree = @json($modulesTree);
const rawRoleMetrics = @json($roleMetrics);
const rawRoles = @json($roles);

// Switch View Modes
function switchViewMode(mode) {
    document.querySelectorAll('.vis-view-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.vis-view-panel').forEach(p => p.style.display = 'none');

    const btn = event.currentTarget || document.querySelector(`.vis-view-tab[onclick*="${mode}"]`);
    if (btn) btn.classList.add('active');

    const roleToggle = document.getElementById('roleToggleBar');

    if (mode === 'matrix') {
        document.getElementById('viewMatrixContainer').style.display = 'block';
        if (roleToggle) roleToggle.style.display = 'flex';
    } else if (mode === 'role') {
        document.getElementById('viewRoleContainer').style.display = 'block';
        if (roleToggle) roleToggle.style.display = 'none';
        const curRole = document.getElementById('roleDeepDiveSelect').value;
        loadRoleDeepDive(curRole);
    } else if (mode === 'module') {
        document.getElementById('viewModuleContainer').style.display = 'block';
        if (roleToggle) roleToggle.style.display = 'none';
        const curMod = document.getElementById('moduleInspectorSelect').value;
        loadModuleInspector(curMod);
    } else if (mode === 'simulator') {
        document.getElementById('viewSimulatorContainer').style.display = 'block';
        if (roleToggle) roleToggle.style.display = 'none';
    }
}

// Toggle Role Column in Matrix Table
function toggleRoleColumn(roleId, isChecked) {
    const pill = document.getElementById('pill_role_' + roleId);
    if (pill) {
        if (isChecked) pill.classList.add('active');
        else pill.classList.remove('active');
    }

    document.querySelectorAll('.role-col-' + roleId).forEach(cell => {
        cell.style.display = isChecked ? '' : 'none';
    });
}

// Expand / Collapse Accordions
function toggleModuleAccordion(header) {
    const body = header.nextElementSibling;
    const arrow = header.querySelector('.accordion-arrow');
    if (body.style.display === 'none') {
        body.style.display = 'block';
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    } else {
        body.style.display = 'none';
        if (arrow) arrow.style.transform = 'rotate(-90deg)';
    }
}

function expandCollapseAll(expand) {
    document.querySelectorAll('.vis-mod-body').forEach(body => {
        body.style.display = expand ? 'block' : 'none';
    });
    document.querySelectorAll('.accordion-arrow').forEach(arrow => {
        arrow.style.transform = expand ? 'rotate(0deg)' : 'rotate(-90deg)';
    });
}

// Live Search & Multi-Filter Handler
function handleLiveFilter() {
    const query = (document.getElementById('liveSearchInput').value || '').toLowerCase().trim();
    const selectedMod = (document.getElementById('moduleFilterSelect').value || '').toLowerCase().trim();
    const selectedCrud = (document.getElementById('crudFilterSelect').value || '').toLowerCase().trim();

    document.querySelectorAll('.module-block').forEach(modCard => {
        const modName = (modCard.getAttribute('data-module-name') || '').toLowerCase();
        let matchMod = (selectedMod === '' || modName.includes(selectedMod));

        let hasVisibleSub = false;

        // Check rows inside module
        modCard.querySelectorAll('.permission-row').forEach(row => {
            const rowSearch = (row.getAttribute('data-search') || '').toLowerCase();
            const rowAction = (row.getAttribute('data-action') || '').toLowerCase();

            const matchQuery = (query === '' || rowSearch.includes(query));
            const matchCrud = (selectedCrud === '' || rowAction === selectedCrud);

            if (matchMod && matchQuery && matchCrud) {
                row.style.display = '';
                hasVisibleSub = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Hide / show submenu blocks
        modCard.querySelectorAll('.submenu-block').forEach(subBlock => {
            const visibleRows = subBlock.querySelectorAll('.permission-row:not([style*="display: none"])');
            if (visibleRows.length > 0) {
                subBlock.style.display = '';
                hasVisibleSub = true;
            } else {
                subBlock.style.display = 'none';
            }
        });

        if (hasVisibleSub || (matchMod && query === '' && selectedCrud === '')) {
            modCard.style.display = '';
        } else {
            modCard.style.display = 'none';
        }
    });
}

// Load Role Deep Dive
function loadRoleDeepDive(roleId) {
    roleId = parseInt(roleId);
    const metric = rawRoleMetrics[roleId];
    if (!metric) return;

    document.getElementById('roleProfileName').textContent = metric.name;
    document.getElementById('roleProfileSlug').innerHTML = `Slug: <span class="text-monospace text-warning">${metric.slug}</span> &bull; Active Users: <strong>${metric.user_count}</strong> &bull; System Default: ${metric.is_system ? 'Yes' : 'No'}`;

    document.getElementById('rolePermPct').textContent = metric.permission_coverage_pct + '%';
    document.getElementById('rolePermFill').style.width = metric.permission_coverage_pct + '%';
    document.getElementById('rolePermFraction').textContent = `${metric.assigned_permissions_count} of ${rawModulesTree.reduce((acc, m) => acc + m.permissions_list.length + m.submenus.reduce((sAcc, s) => sAcc + s.permissions_list.length, 0), 0)} permissions`;

    document.getElementById('roleMenuPct').textContent = metric.menu_coverage_pct + '%';
    document.getElementById('roleMenuFill').style.width = metric.menu_coverage_pct + '%';
    document.getElementById('roleMenuFraction').textContent = `${metric.assigned_menus_count} navigation pages`;

    document.getElementById('roleCrudView').textContent = `View: ${metric.crud_counts.view}`;
    document.getElementById('roleCrudCreate').textContent = `Create: ${metric.crud_counts.create}`;
    document.getElementById('roleCrudEdit').textContent = `Edit: ${metric.crud_counts.edit}`;
    document.getElementById('roleCrudDelete').textContent = `Delete: ${metric.crud_counts.delete}`;
    document.getElementById('roleCrudManage').textContent = `Manage: ${metric.crud_counts.manage}`;

    // Render tree for this role
    let treeHtml = '';
    rawModulesTree.forEach(module => {
        let subItems = '';
        let modHasAny = false;

        module.submenus.forEach(sub => {
            const hasMenu = sub.role_access[roleId] ? sub.role_access[roleId].menu_access : false;
            let actionPills = '';

            sub.permissions_list.forEach(p => {
                const granted = p.role_access[roleId] ? p.role_access[roleId].granted : false;
                if (granted) modHasAny = true;
                actionPills += `
                    <span class="status-badge ${granted ? 'status-badge-granted' : 'status-badge-denied'} mr-1 mb-1">
                        <i class="fas ${granted ? 'fa-check' : 'fa-times'} mr-1"></i> ${p.key}
                    </span>
                `;
            });

            subItems += `
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                    <div>
                        <div class="font-weight-bold text-dark"><i class="fas fa-file-alt text-muted mr-1"></i> ${sub.name}</div>
                        <div class="small text-muted text-monospace">${sub.route || 'No Route'}</div>
                    </div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                        <span class="status-badge ${hasMenu ? 'status-badge-page-on' : 'status-badge-page-off'}">
                            <i class="fas ${hasMenu ? 'fa-eye' : 'fa-eye-slash'} mr-1"></i> ${hasMenu ? 'Page Visible' : 'Hidden'}
                        </span>
                        ${actionPills}
                    </div>
                </div>
            `;
        });

        treeHtml += `
            <div class="vis-mod-card mb-3">
                <div class="vis-mod-header">
                    <div class="vis-mod-title">
                        <i class="fas fa-folder-open text-primary"></i> ${module.name}
                    </div>
                </div>
                <div>${subItems}</div>
            </div>
        `;
    });

    document.getElementById('roleDeepDiveTree').innerHTML = treeHtml;
}

// Load Module Inspector
function loadModuleInspector(moduleId) {
    moduleId = parseInt(moduleId);
    const module = rawModulesTree.find(m => m.id === moduleId);
    if (!module) return;

    let html = `
        <div class="vis-mod-card">
            <div class="vis-mod-header bg-light">
                <h4 class="m-0 font-weight-bold text-primary"><i class="fas fa-layer-group mr-2"></i> ${module.name}</h4>
            </div>
            <div class="p-4">
                <div class="vis-table-wrap">
                    <table class="vis-table">
                        <thead>
                            <tr>
                                <th class="col-feature">Page / Action</th>
                                <th>Type</th>
                                ${rawRoles.map(r => `<th>${r.name}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
    `;

    module.submenus.forEach(sub => {
        html += `
            <tr style="background: #F8FAFC; font-weight: 800;">
                <td colspan="${rawRoles.length + 2}" class="text-left text-primary">
                    <i class="fas fa-link mr-1"></i> Page: ${sub.name} &bull; Route: <span class="text-monospace">${sub.route || 'N/A'}</span>
                </td>
            </tr>
            <tr>
                <td class="col-feature"><i class="fas fa-browser mr-1"></i> Sidebar Visibility</td>
                <td><span class="crud-type-pill crud-type-view">PAGE</span></td>
                ${rawRoles.map(r => {
                    const acc = sub.role_access[r.id] || {};
                    return `<td><span class="status-badge ${acc.menu_access ? 'status-badge-page-on' : 'status-badge-page-off'}">${acc.menu_access ? 'Visible' : 'Hidden'}</span></td>`;
                }).join('')}
            </tr>
        `;

        sub.permissions_list.forEach(p => {
            html += `
                <tr>
                    <td class="col-feature font-weight-bold">${p.key}<div class="small text-muted font-weight-normal">${p.description}</div></td>
                    <td><span class="crud-type-pill crud-type-${p.crud_type}">${p.crud_type}</span></td>
                    ${rawRoles.map(r => {
                        const acc = p.role_access[r.id] || {};
                        return `<td><span class="status-badge ${acc.granted ? 'status-badge-granted' : 'status-badge-denied'}">${acc.granted ? 'Allowed' : 'Denied'}</span></td>`;
                    }).join('')}
                </tr>
            `;
        });
    });

    html += `</tbody></table></div></div></div>`;
    document.getElementById('moduleInspectorOutput').innerHTML = html;
}

// Simulator Toggle Target Type
function toggleSimTargetType(type) {
    if (type === 'role') {
        document.getElementById('simRoleSelectGroup').style.display = 'block';
        document.getElementById('simUserSelectGroup').style.display = 'none';
        document.getElementById('targetTypeRoleLabel').classList.add('active');
        document.getElementById('targetTypeUserLabel').classList.remove('active');
    } else {
        document.getElementById('simRoleSelectGroup').style.display = 'none';
        document.getElementById('simUserSelectGroup').style.display = 'block';
        document.getElementById('targetTypeRoleLabel').classList.remove('active');
        document.getElementById('targetTypeUserLabel').classList.add('active');
    }
}

function toggleSimResourceType(type) {
    if (type === 'permission') {
        document.getElementById('simPermKeyGroup').style.display = 'block';
        document.getElementById('simMenuKeyGroup').style.display = 'none';
    } else {
        document.getElementById('simPermKeyGroup').style.display = 'none';
        document.getElementById('simMenuKeyGroup').style.display = 'block';
    }
}

// Run Diagnostic Simulator via AJAX
function runSimulator(e) {
    e.preventDefault();

    const targetType = document.querySelector('input[name="target_type"]:checked').value;
    const targetId = targetType === 'role' ? document.getElementById('simTargetRoleId').value : document.getElementById('simTargetUserId').value;
    const resourceType = document.getElementById('simResourceType').value;
    const resourceKey = resourceType === 'permission' ? document.getElementById('simPermissionKey').value : document.getElementById('simMenuKey').value;

    const btn = document.querySelector('#simulatorForm button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Resolving Access Policy...';
    btn.disabled = true;

    const startTime = performance.now();

    fetch("{{ route('access_control.visualizer.simulate') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            target_type: targetType,
            target_id: targetId,
            resource_type: resourceType,
            resource_key: resourceKey
        })
    })
    .then(r => r.json())
    .then(res => {
        const endTime = performance.now();
        const duration = Math.round(endTime - startTime);

        btn.innerHTML = originalText;
        btn.disabled = false;

        document.getElementById('simPlaceholder').style.display = 'none';
        document.getElementById('simResultsContent').style.display = 'block';
        document.getElementById('simExecutionTime').textContent = `Evaluated in ${duration}ms`;

        const verdictBox = document.getElementById('simVerdictBox');
        const verdictTitle = document.getElementById('simVerdictTitle');
        const verdictReason = document.getElementById('simVerdictReason');

        if (res.allowed) {
            verdictBox.className = 'sim-verdict-box verdict-allowed mb-4';
            verdictTitle.innerHTML = '<i class="fas fa-check-circle mr-2"></i> ACCESS GRANTED';
        } else {
            verdictBox.className = 'sim-verdict-box verdict-denied mb-4';
            verdictTitle.innerHTML = '<i class="fas fa-times-circle mr-2"></i> ACCESS DENIED';
        }

        verdictReason.textContent = res.reason;

        let stepsHtml = '';
        (res.steps || []).forEach((step, idx) => {
            let statusIcon = '<i class="fas fa-info text-primary"></i>';
            let stepClass = '';

            if (step.status === 'granted') {
                statusIcon = '<i class="fas fa-check text-success"></i>';
                stepClass = 'step-granted';
            } else if (step.status === 'denied') {
                statusIcon = '<i class="fas fa-times text-danger"></i>';
                stepClass = 'step-denied';
            }

            stepsHtml += `
                <div class="sim-step-item ${stepClass}">
                    <div class="sim-step-icon bg-white shadow-sm">${statusIcon}</div>
                    <div class="flex-fill">
                        <div class="font-weight-bold text-dark small">Step ${idx + 1}: ${step.stage}</div>
                        <div class="small text-secondary">${step.description}</div>
                    </div>
                </div>
            `;
        });

        document.getElementById('simStepsList').innerHTML = stepsHtml;
    })
    .catch(err => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Simulation request failed. Please check network console.');
    });
}
</script>
@endsection
