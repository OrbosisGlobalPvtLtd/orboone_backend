@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Reporting Structure')

@section('_content')
<style>
    :root {

        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
        --orb-active-green: #10B981;
    }

    .eo-page {
        background: var(--orb-bg);
        min-height: calc(100vh - 120px);
        padding: 28px;
        color: var(--orb-text);
        font-family: 'Inter', sans-serif;
    }

    .eo-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Premium Header/Hero Card */
    .eo-header-premium {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 32px;
        margin-bottom: 24px;
        box-shadow: 0 14px 35px rgba(75, 0, 232, 0.15);
        position: relative;
        overflow: hidden;
    }

    .eo-header-kicker {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 8px;
    }

    .eo-header-title {
        font-size: 28px;
        font-weight: 950;
        margin: 0 0 8px 0;
        color: #fff;
    }

    .eo-header-subtitle {
        font-size: 14px;
        font-weight: 650;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
    }

    /* Main Container Card */
    .eo-card {
        background: #fff;
        border-radius: 22px;
        border: 1px solid var(--orb-border);
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-bottom: 28px;
    }

    /* Card Header */
    .eo-card-header-premium {
        padding: 24px 28px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .eo-card-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .eo-header-icon-circle {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .eo-card-title-premium {
        font-size: 18px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
    }

    .eo-card-subtitle-premium {
        font-size: 12px;
        font-weight: 650;
        color: var(--orb-muted);
        margin: 4px 0 0 0;
    }

    /* View Controls Buttons */
    .eo-view-toggle {
        display: inline-flex;
        background: var(--orb-bg);
        padding: 4px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
    }

    .eo-view-btn {
        border: 0;
        background: transparent;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.15s ease;
    }

    .eo-view-btn.active {
        background: #fff;
        color: var(--orb-primary);
        box-shadow: 0 4px 10px rgba(16, 24, 40, 0.05);
    }

    /* Filter Panel */
    .eo-filter-inside {
        padding: 20px 28px;
        background: #FAFCFF;
        border-bottom: 1px solid var(--orb-border);
    }

    .eo-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .eo-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .eo-field label {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        color: var(--orb-muted);
        letter-spacing: 0.5px;
    }

    .eo-control {
        height: 40px;
        border-radius: 10px;
        border: 1px solid var(--orb-border);
        padding: 0 14px;
        font-size: 13px;
        font-weight: 700;
        background: #fff;
        color: var(--orb-text);
        outline: none;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        transition: 0.15s ease;
        width: 100%;
    }

    .eo-control:focus {
        border-color: rgba(75, 0, 232, .45);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08);
    }

    .eo-btn {
        min-height: 40px;
        border-radius: 10px;
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid transparent;
        text-decoration: none !important;
        cursor: pointer;
        white-space: nowrap;
        background: #fff;
        color: #344054;
        border-color: var(--orb-border);
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
    }

    .eo-btn:hover {
        background: #F8FAFC;
        color: var(--orb-text);
        border-color: #D0D5DD;
    }

    /* ORG CHART TREE VIEW STYLE */
    .eo-tree-container {
        padding: 40px 28px;
        overflow-x: auto;
        min-height: 500px;
        background: #fff;
        display: flex;
        justify-content: center;
    }

    .org-tree {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .org-tree ul {
        padding-top: 24px;
        position: relative;
        transition: all 0.3s;
        display: flex;
        justify-content: center;
        margin: 0;
        padding-left: 0;
    }

    .org-tree li {
        text-align: center;
        list-style-type: none;
        position: relative;
        padding: 24px 10px 0 10px;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Connecting Lines */
    .org-tree li::before, .org-tree li::after {
        content: '';
        position: absolute;
        top: 0;
        right: 50%;
        border-top: 2px solid var(--orb-border);
        width: 50%;
        height: 24px;
    }

    .org-tree li::after {
        right: auto;
        left: 50%;
        border-left: 2px solid var(--orb-border);
    }

    /* Single child connector removal */
    .org-tree li:only-child::after, .org-tree li:only-child::before {
        display: none;
    }
    .org-tree li:only-child {
        padding-top: 0;
    }

    /* Borders for multiple siblings */
    .org-tree li:first-child::before, .org-tree li:last-child::after {
        border: 0 none;
    }
    .org-tree li:last-child::before {
        border-right: 2px solid var(--orb-border);
        border-radius: 0 8px 0 0;
    }
    .org-tree li:first-child::after {
        border-radius: 8px 0 0 0;
    }

    /* Connector downward from parent node */
    .org-tree ul ul::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        border-left: 2px solid var(--orb-border);
        width: 0;
        height: 24px;
        transform: translateX(-50%);
    }

    /* EMPLOYEE NODE CARD */
    .eo-node-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 16px;
        width: 250px;
        box-shadow: 0 4px 20px rgba(16, 24, 40, 0.04);
        position: relative;
        z-index: 10;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: left;
        display: inline-block;
    }

    .eo-node-card:hover {
        border-color: var(--orb-primary);
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.1);
        transform: translateY(-3px);
    }

    .eo-node-card.highlighted {
        border-color: var(--orb-secondary);
        box-shadow: 0 0 0 4px rgba(134, 0, 238, 0.15), 0 12px 30px rgba(134, 0, 238, 0.12);
        animation: pulseHighlight 2s infinite;
    }

    @keyframes pulseHighlight {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }

    .eo-node-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    /* Avatar & Image handling */
    .eo-node-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        background: var(--orb-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: var(--orb-primary);
        font-size: 15px;
        flex-shrink: 0;
        border: 1px solid var(--orb-border);
    }

    .eo-node-info {
        flex-grow: 1;
        min-width: 0;
    }

    .eo-node-name {
        font-size: 13px;
        font-weight: 850;
        color: var(--orb-text);
        margin: 0 0 2px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .eo-node-code {
        font-size: 10px;
        font-weight: 800;
        color: var(--orb-muted);
    }

    .eo-node-detail-line {
        font-size: 11px;
        font-weight: 650;
        color: var(--orb-muted);
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .eo-node-detail-line i {
        width: 14px;
        color: var(--orb-primary);
    }

    /* Badges & Metrics inside Node */
    .eo-node-badges {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 12px;
        padding-top: 10px;
        border-top: 1px dashed var(--orb-border);
    }

    .eo-reportees-badge {
        font-size: 10px;
        font-weight: 800;
        color: var(--orb-primary);
        background: var(--orb-soft);
        padding: 4px 8px;
        border-radius: 8px;
    }

    .eo-profile-link {
        font-size: 11px;
        font-weight: 800;
        color: var(--orb-secondary);
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: 0.15s ease;
    }

    .eo-profile-link:hover {
        color: var(--orb-primary);
    }

    /* Expand / Collapse Branch Button */
    .eo-toggle-branch {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: var(--orb-muted);
        cursor: pointer;
        position: absolute;
        bottom: -11px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 15;
        transition: 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .eo-toggle-branch:hover {
        border-color: var(--orb-primary);
        color: var(--orb-primary);
        transform: translateX(-50%) scale(1.1);
    }

    /* Stacked Collapsible List Style */
    .eo-list-container {
        padding: 24px 28px;
        background: #fff;
    }

    .eo-list-item {
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        padding: 14px 18px;
        background: #fff;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        transition: 0.2s ease;
    }

    .eo-list-item:hover {
        border-color: var(--orb-primary);
        box-shadow: 0 4px 12px rgba(16, 24, 40, 0.03);
    }

    .eo-list-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .eo-list-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Empty state */
    .eo-empty-state {
        text-align: center;
        padding: 60px 40px;
        background: #fff;
    }

    .eo-empty-icon {
        font-size: 52px;
        color: var(--orb-muted);
        opacity: 0.4;
        margin-bottom: 16px;
    }

    .eo-empty-title {
        font-size: 16px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0 0 6px 0;
    }

    .eo-empty-sub {
        font-size: 13px;
        color: var(--orb-muted);
        margin: 0;
    }

    /* Responsive adjustments */
    @media (max-width: 991px) {
        .eo-filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .eo-page {
            padding: 12px 10px;
        }

        .eo-header-premium {
            border-radius: 18px;
            padding: 24px;
        }

        .eo-card-header-premium {
            padding: 16px 20px;
        }

        .eo-filter-grid {
            grid-template-columns: 1fr;
        }

        .eo-filter-inside {
            padding: 16px 20px;
        }
    }
</style>

@php
    $privateFileUrl = function ($path) {
        if (empty($path)) {
            return '#';
        }

        if (Route::has('hrms.documents.file')) {
            return route('hrms.documents.file', $path);
        }

        if (Route::has('hrms.employee.file')) {
            return route('hrms.documents.file', ['path' => $path]);
        }

        return route('hrms.documents.file', ['path' => $path]);
    };

    $profileViewUrl = function ($id) {
        if (Route::has('hrms.employees.profile.view')) {
            return route('hrms.employees.profile.view', $id);
        }
        return url("/hrms/employees/{$id}/profile-view");
    };
@endphp

<div class="eo-page">
    <div class="eo-container">

        <!-- Top Header Hero Section -->
        <div class="eo-header-premium">
            <div class="eo-header-kicker">HRMS • EMPLOYEE MANAGEMENT</div>
            <h1 class="eo-header-title">Reporting Structure</h1>
            <p class="eo-header-subtitle">View organization reporting hierarchy and team relationships.</p>
        </div>

        <div class="eo-card">
            <!-- Card Header with Filters/Toggles -->
            <div class="eo-card-header-premium">
                <div class="eo-card-header-left">
                    <div class="eo-header-icon-circle">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div>
                        <h4 class="eo-card-title-premium">Organization Chart</h4>
                        <p class="eo-card-subtitle-premium">Explore interactive team hierarchy reporting structures.</p>
                    </div>
                </div>

                <div class="eo-view-toggle">
                    <button type="button" id="btnTreeView" class="eo-view-btn active">
                        <i class="fas fa-network-wired"></i> Tree Chart
                    </button>
                    <button type="button" id="btnListView" class="eo-view-btn">
                        <i class="fas fa-list-ul"></i> Stacked List
                    </button>
                </div>
            </div>

            <!-- Toolbar Filters -->
            <div class="eo-filter-inside">
                <div class="eo-filter-grid">
                    <div class="eo-field">
                        <label>Search Employee</label>
                        <input type="text" id="filterSearch" class="eo-control" placeholder="Search by name or code...">
                    </div>

                    <div class="eo-field">
                        <label>Department</label>
                        <select id="filterDepartment" class="eo-control">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ strtolower($dept) }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Designation</label>
                        <select id="filterDesignation" class="eo-control">
                            <option value="">All Designations</option>
                            @foreach($designations as $desg)
                                <option value="{{ strtolower($desg) }}">{{ $desg }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>&nbsp;</label>
                        <button type="button" id="btnResetFilters" class="eo-btn" style="width:fit-content;">
                            <i class="fas fa-undo"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tree Visualization Viewport -->
            <div id="treeViewContainer" class="eo-tree-container">
                <div id="orgTreeWrapper" class="org-tree">
                    <!-- Tree dynamically generated here -->
                </div>
            </div>

            <!-- Stacked List Viewport -->
            <div id="listViewContainer" class="eo-list-container" style="display:none;">
                <div id="stackedListWrapper">
                    <!-- Stacked list dynamically generated here -->
                </div>
            </div>

            <!-- Empty State Container -->
            <div id="emptyStateContainer" class="eo-empty-state" style="display:none;">
                <div class="eo-empty-icon"><i class="fas fa-users-slash"></i></div>
                <h5 class="eo-empty-title">No reporting structure available.</h5>
                <p class="eo-empty-sub">No active employees matching filters are registered in the directory.</p>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rawEmployees = @json($employees);

        // State selectors
        const searchInput = document.getElementById('filterSearch');
        const departmentFilter = document.getElementById('filterDepartment');
        const designationFilter = document.getElementById('filterDesignation');
        const resetBtn = document.getElementById('btnResetFilters');
        
        const btnTreeView = document.getElementById('btnTreeView');
        const btnListView = document.getElementById('btnListView');
        
        const treeContainer = document.getElementById('treeViewContainer');
        const listContainer = document.getElementById('listViewContainer');
        const emptyState = document.getElementById('emptyStateContainer');
        
        const treeWrapper = document.getElementById('orgTreeWrapper');
        const listWrapper = document.getElementById('stackedListWrapper');

        let currentView = 'tree'; // 'tree' or 'list'
        let collapsedNodes = new Set();

        // 1. Sanitize & check circular dependencies
        function checkCircularDependency(emp, visited = new Set()) {
            if (visited.has(emp.id)) return true;
            visited.add(emp.id);
            const managerId = emp.reporting_manager_employee_id;
            if (!managerId) return false;
            const manager = rawEmployees.find(e => e.id == managerId);
            if (!manager) return false;
            return checkCircularDependency(manager, visited);
        }

        const sanitizedEmployees = rawEmployees.map(emp => {
            const hasLoop = checkCircularDependency(emp);
            return {
                ...emp,
                reporting_manager_employee_id: hasLoop ? null : emp.reporting_manager_employee_id
            };
        });

        // 2. Pre-calculate children maps
        const childrenMap = new Map();
        sanitizedEmployees.forEach(emp => {
            const mId = emp.reporting_manager_employee_id;
            if (mId) {
                if (!childrenMap.has(mId)) {
                    childrenMap.set(mId, []);
                }
                childrenMap.get(mId).push(emp);
            }
        });

        // 3. Helper to calculate descendants count recursively
        function getDescendantsCount(empId) {
            const children = childrenMap.get(empId) || [];
            let count = children.length;
            children.forEach(child => {
                count += getDescendantsCount(child.id);
            });
            return count;
        }

        // 4. Render a single Node Card
        function renderNodeCard(emp) {
            const initials = emp.name ? emp.name.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase() : '?';
            const baseFileUrl = '{{ $privateFileUrl("PLACEHOLDER") }}';
            const imgPath = emp.profile_image ? baseFileUrl.replace('PLACEHOLDER', emp.profile_image) : null;
            const avatarHtml = imgPath 
                ? `<img class="eo-node-avatar" src="${imgPath}" alt="${emp.name}" onerror="this.outerHTML='<div class=&quot;eo-node-avatar&quot;>${initials}</div>'">`
                : `<div class="eo-node-avatar">${initials}</div>`;

            const reports = childrenMap.get(emp.id) || [];
            const directCount = reports.length;
            const totalCount = getDescendantsCount(emp.id);

            const hasChildren = directCount > 0;
            const isCollapsed = collapsedNodes.has(emp.id);

            const toggleBtnHtml = hasChildren 
                ? `<button type="button" class="eo-toggle-branch" data-id="${emp.id}" title="${isCollapsed ? 'Expand branch' : 'Collapse branch'}">
                     <i class="fas ${isCollapsed ? 'fa-plus' : 'fa-minus'}"></i>
                   </button>`
                : '';

            const badgeHtml = hasChildren 
                ? `<div class="eo-reportees-badge" title="${totalCount} total reports recursively">
                     <i class="fas fa-users mr-1"></i> ${directCount} / ${totalCount} Team
                   </div>`
                : `<div class="eo-reportees-badge" style="background:#F9FAFB;color:#98A2B3;">
                     <i class="fas fa-user-friends mr-1"></i> 0 Team
                   </div>`;

            const profileUrl = `{{ url('/hrms/employees') }}/${emp.id}/profile-view`;

            return `
                <div class="eo-node-card" id="card-${emp.id}">
                    <div class="eo-node-top">
                        ${avatarHtml}
                        <div class="eo-node-info">
                            <h5 class="eo-node-name" title="${emp.name || '-'}">${emp.name || '-'}</h5>
                            <span class="eo-node-code">${emp.employee_code || 'EMP-' + emp.id}</span>
                        </div>
                    </div>
                    <div class="eo-node-detail-line" title="${emp.designation_name || '-'}">
                        <i class="fas fa-briefcase"></i> ${emp.designation_name || '-'}
                    </div>
                    <div class="eo-node-detail-line" title="${emp.department_name || '-'}">
                        <i class="fas fa-building"></i> ${emp.department_name || '-'}
                    </div>
                    <div class="eo-node-badges">
                        ${badgeHtml}
                        <a href="${profileUrl}" class="eo-profile-link">
                            Profile <i class="fas fa-chevron-right" style="font-size:8px;"></i>
                        </a>
                    </div>
                    ${toggleBtnHtml}
                </div>
            `;
        }

        // 5. Build dynamic Tree DOM recursively
        function buildTreeHtml(managerId) {
            const employeesAtThisLevel = sanitizedEmployees.filter(emp => {
                if (!managerId) {
                    // Roots are employees without a valid active manager in the active set
                    const hasActiveManager = sanitizedEmployees.some(m => m.id == emp.reporting_manager_employee_id);
                    return !emp.reporting_manager_employee_id || !hasActiveManager;
                }
                return emp.reporting_manager_employee_id == managerId;
            });

            if (employeesAtThisLevel.length === 0) return '';

            let html = '<ul>';
            employeesAtThisLevel.forEach(emp => {
                const reports = childrenMap.get(emp.id) || [];
                const isCollapsed = collapsedNodes.has(emp.id);

                html += `<li>`;
                html += renderNodeCard(emp);
                
                if (reports.length > 0 && !isCollapsed) {
                    html += buildTreeHtml(emp.id);
                }
                
                html += `</li>`;
            });
            html += '</ul>';
            return html;
        }

        // 6. Build list DOM
        function renderListView(filteredList) {
            if (filteredList.length === 0) return '';

            return filteredList.map(emp => {
                const initials = emp.name ? emp.name.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase() : '?';
                const baseFileUrl = '{{ $privateFileUrl("PLACEHOLDER") }}';
                const imgPath = emp.profile_image ? baseFileUrl.replace('PLACEHOLDER', emp.profile_image) : null;
                const avatarHtml = imgPath 
                    ? `<img class="eo-node-avatar" src="${imgPath}" alt="${emp.name}" onerror="this.outerHTML='<div class=&quot;eo-node-avatar&quot;>${initials}</div>'">`
                    : `<div class="eo-node-avatar">${initials}</div>`;

                const reports = childrenMap.get(emp.id) || [];
                const directCount = reports.length;
                const totalCount = getDescendantsCount(emp.id);

                // Find manager name
                const manager = sanitizedEmployees.find(m => m.id == emp.reporting_manager_employee_id);
                const managerName = manager ? manager.name : 'Unassigned / Top Root';

                const profileUrl = `{{ url('/hrms/employees') }}/${emp.id}/profile-view`;

                return `
                    <div class="eo-list-item" id="list-card-${emp.id}">
                        <div class="eo-list-left">
                            ${avatarHtml}
                            <div>
                                <h5 class="eo-node-name" style="font-size:14px;font-weight:900;">${emp.name || '-'}</h5>
                                <span class="eo-node-code" style="font-size:11px;">${emp.employee_code || 'EMP-' + emp.id} • ${emp.designation_name || '-'}</span>
                            </div>
                        </div>
                        <div class="eo-list-right">
                            <span class="eo-reportees-badge" style="font-size:11px;padding:6px 12px;">
                                <i class="fas fa-building mr-1"></i> ${emp.department_name || '-'}
                            </span>
                            <span class="eo-reportees-badge" style="background:#F0FDF4;color:#15803D;font-size:11px;padding:6px 12px;">
                                Manager: ${managerName}
                            </span>
                            <span class="eo-reportees-badge" style="font-size:11px;padding:6px 12px;">
                                Team Size: ${directCount} / ${totalCount}
                            </span>
                            <a href="${profileUrl}" class="eo-btn" style="min-height:34px;height:34px;padding:0 12px;border-radius:8px;font-size:12px;">
                                View Profile
                            </a>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // 7. Core filter & search evaluator
        function evaluateFilters() {
            const search = (searchInput.value || '').toLowerCase().trim();
            const dept = (departmentFilter.value || '').toLowerCase().trim();
            const desg = (designationFilter.value || '').toLowerCase().trim();

            // Clear card highlights
            document.querySelectorAll('.eo-node-card').forEach(c => c.classList.remove('highlighted'));
            document.querySelectorAll('.eo-list-item').forEach(c => c.style.display = '');

            // List of matching employee nodes
            const matches = sanitizedEmployees.filter(emp => {
                const matchSearch = !search || 
                    (emp.name || '').toLowerCase().includes(search) || 
                    (emp.employee_code || '').toLowerCase().includes(search);
                const matchDept = !dept || (emp.department_name || '').toLowerCase() === dept;
                const matchDesg = !desg || (emp.designation_name || '').toLowerCase() === desg;

                return matchSearch && matchDept && matchDesg;
            });

            if (sanitizedEmployees.length === 0) {
                treeContainer.style.display = 'none';
                listContainer.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            if (currentView === 'tree') {
                treeContainer.style.display = 'flex';
                listContainer.style.display = 'none';
                emptyState.style.display = 'none';

                // Redraw tree
                treeWrapper.innerHTML = buildTreeHtml(null);

                // Highlight matches in tree view
                if (search || dept || desg) {
                    if (matches.length === 0) {
                        treeContainer.style.display = 'none';
                        emptyState.style.display = 'block';
                    } else {
                        matches.forEach(m => {
                            const card = document.getElementById(`card-${m.id}`);
                            if (card) {
                                card.classList.add('highlighted');
                                // Ensure ancestors are expanded so matches are visible
                                expandAncestors(m.reporting_manager_employee_id);
                            }
                        });
                    }
                }
            } else {
                treeContainer.style.display = 'none';
                listContainer.style.display = 'block';
                emptyState.style.display = 'none';

                if (matches.length === 0) {
                    listContainer.style.display = 'none';
                    emptyState.style.display = 'block';
                } else {
                    listWrapper.innerHTML = renderListView(matches);
                }
            }
        }

        // Expand manager node recursively
        function expandAncestors(managerId) {
            if (!managerId) return;
            if (collapsedNodes.has(managerId)) {
                collapsedNodes.delete(managerId);
                // Re-evaluate to render
                treeWrapper.innerHTML = buildTreeHtml(null);
            }
            const parent = sanitizedEmployees.find(e => e.id == managerId);
            if (parent) {
                expandAncestors(parent.reporting_manager_employee_id);
            }
        }

        // 8. Bind collapsible branch toggles
        document.addEventListener('click', function(e) {
            const toggleBtn = e.target.closest('.eo-toggle-branch');
            if (toggleBtn) {
                const nodeId = parseInt(toggleBtn.dataset.id);
                if (collapsedNodes.has(nodeId)) {
                    collapsedNodes.delete(nodeId);
                } else {
                    collapsedNodes.add(nodeId);
                }
                evaluateFilters();
            }
        });

        // 9. Input bindings
        searchInput.addEventListener('keyup', evaluateFilters);
        departmentFilter.addEventListener('change', evaluateFilters);
        designationFilter.addEventListener('change', evaluateFilters);

        // 10. View selection bindings
        btnTreeView.addEventListener('click', function() {
            btnTreeView.classList.add('active');
            btnListView.classList.remove('active');
            currentView = 'tree';
            evaluateFilters();
        });

        btnListView.addEventListener('click', function() {
            btnListView.classList.add('active');
            btnTreeView.classList.remove('active');
            currentView = 'list';
            evaluateFilters();
        });

        // 11. Reset action
        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            departmentFilter.value = '';
            designationFilter.value = '';
            collapsedNodes.clear();
            evaluateFilters();
        });

        // Initial draw
        evaluateFilters();
    });
</script>
@endsection
