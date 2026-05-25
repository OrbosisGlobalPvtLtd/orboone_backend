@extends('layouts.panel')

@section('page_title', ($dashboard['meta']['title'] ?? 'Super Admin Dashboard') . ' | OrboOne HRMS')

@section('_content')
@php
use Illuminate\Support\Facades\Route;

$meta = $dashboard['meta'] ?? [];
$cards = $dashboard['cards'] ?? [];
$quickActions = $dashboard['quick_actions'] ?? [];
$attendanceCards = $dashboard['attendance_cards'] ?? [];
$employeeCards = $dashboard['employee_cards'] ?? [];
$actionRequired = $dashboard['action_required'] ?? [];
$liveAttendance = $dashboard['live_attendance'] ?? [];
$charts = $dashboard['charts'] ?? [];
$recentActivities = $dashboard['recent_activities'] ?? [];

$blockedEmployees = collect($dashboard['tables']['blocked_employees'] ?? []);
$pendingLeaves = collect($dashboard['tables']['pending_leaves'] ?? []);
$pendingProfiles = collect($dashboard['tables']['pending_profiles'] ?? []);
$pendingDocuments = collect($dashboard['tables']['pending_documents'] ?? []);
$lifecycle = $dashboard['lifecycle'] ?? [];
$leave = $dashboard['leave'] ?? [];
$payroll = $dashboard['payroll'] ?? [];
$documents = $dashboard['documents'] ?? [];
$announcements = $dashboard['announcements'] ?? [];
$system = $dashboard['system'] ?? [];
$systemHealth = $dashboard['system_health'] ?? [];

$money = fn($v) => '₹' . number_format((float)($v ?? 0), 2);
$num = fn($v) => number_format((float)($v ?? 0));

$safeRoute = function(array $names) {
foreach ($names as $name) {
if (Route::has($name)) return route($name);
}
return null;
};

$actionUrl = fn($item) => $item['url'] ?? (
!empty($item['route']) && Route::has($item['route']) ? route($item['route']) : null
);

if (empty($attendanceCards)) {
$attendanceCards = [
['label'=>'Present Today','value'=>$cards['present_today'] ?? 0,'icon'=>'fa-user-check','tone'=>'success','url'=>$safeRoute(['attendances.index','attendance.index'])],
['label'=>'Absent Today','value'=>$cards['absent_today'] ?? 0,'icon'=>'fa-user-times','tone'=>'danger','url'=>$safeRoute(['attendances.index','attendance.index'])],
['label'=>'Late Employees','value'=>$cards['late_today'] ?? 0,'icon'=>'fa-clock','tone'=>'warning','url'=>$safeRoute(['attendances.index','attendance.index'])],
['label'=>'Early Logout','value'=>$cards['early_logout'] ?? 0,'icon'=>'fa-sign-out-alt','tone'=>'warning','url'=>$safeRoute(['attendances.index','attendance.index'])],
['label'=>'Half Day','value'=>$cards['half_day'] ?? 0,'icon'=>'fa-adjust','tone'=>'info','url'=>$safeRoute(['attendances.index','attendance.index'])],
['label'=>'LWP','value'=>$cards['lwp_count'] ?? 0,'icon'=>'fa-ban','tone'=>'danger','url'=>$safeRoute(['attendances.index','attendance.index'])],
['label'=>'Punch Blocked','value'=>$cards['punch_blocked'] ?? 0,'icon'=>'fa-lock','tone'=>'danger','url'=>$safeRoute(['attendances.pending-approval','attendances.index'])],
['label'=>'Pending HR','value'=>$cards['pending_hr'] ?? 0,'icon'=>'fa-user-shield','tone'=>'primary','url'=>$safeRoute(['attendances.pending-approval','attendances.index'])],
];
}

if (empty($employeeCards)) {
$employeeCards = [
['label'=>'Total Employees','value'=>$lifecycle['total_employees'] ?? ($cards['total_employees'] ?? 0),'icon'=>'fa-users','tone'=>'primary'],
['label'=>'Active Employees','value'=>$lifecycle['active_employees'] ?? ($cards['active_employees'] ?? 0),'icon'=>'fa-user-check','tone'=>'success'],
['label'=>'Pending Profiles','value'=>$lifecycle['pending_profiles'] ?? ($cards['pending_profiles'] ?? 0),'icon'=>'fa-user-clock','tone'=>'warning'],
['label'=>'Rejected Profiles','value'=>$lifecycle['rejected_profiles'] ?? ($cards['rejected_profiles'] ?? 0),'icon'=>'fa-user-times','tone'=>'danger'],
['label'=>'Interns','value'=>$lifecycle['interns'] ?? ($cards['interns'] ?? 0),'icon'=>'fa-user-graduate','tone'=>'primary'],
['label'=>'Probation','value'=>$lifecycle['probation'] ?? ($cards['probation'] ?? 0),'icon'=>'fa-hourglass-half','tone'=>'warning'],
['label'=>'Permanent','value'=>$lifecycle['permanent'] ?? ($cards['permanent'] ?? 0),'icon'=>'fa-id-badge','tone'=>'success'],
['label'=>'Exit Process','value'=>$lifecycle['exit_process'] ?? ($cards['exit_process'] ?? 0),'icon'=>'fa-person-walking-arrow-right','tone'=>'danger'],
];
}

$payrollCards = [
['label'=>'Gross Payroll (Month)','value'=>$money($payroll['gross_payroll'] ?? 0),'icon'=>'fa-money-check-alt','tone'=>'primary'],
['label'=>'Net Payroll (Month)','value'=>$money($payroll['net_payroll'] ?? 0),'icon'=>'fa-wallet','tone'=>'success'],
['label'=>'Total Deductions','value'=>$money($payroll['total_deductions'] ?? 0),'icon'=>'fa-file-invoice-dollar','tone'=>'danger'],
['label'=>'Payslips Generated','value'=>$payroll['payslips_generated'] ?? 0,'icon'=>'fa-receipt','tone'=>'info'],
['label'=>'Pending Approval','value'=>$payroll['pending_approval'] ?? 0,'icon'=>'fa-user-clock','tone'=>'warning'],
['label'=>'Missing Structure','value'=>$payroll['missing_structure'] ?? 0,'icon'=>'fa-exclamation-triangle','tone'=>'danger'],
];

$leaveCards = [
['label'=>'On Leave Today','value'=>$leave['on_leave_today'] ?? 0,'icon'=>'fa-calendar-day','tone'=>'primary'],
['label'=>'Paid Leave Used','value'=>$leave['paid_leave'] ?? 0,'icon'=>'fa-calendar-check','tone'=>'success'],
['label'=>'Sick Leave Used','value'=>$leave['sick_leave'] ?? 0,'icon'=>'fa-notes-medical','tone'=>'warning'],
['label'=>'Comp Off Pending','value'=>$leave['comp_off'] ?? 0,'icon'=>'fa-calendar-plus','tone'=>'info'],
['label'=>'LWP This Month','value'=>$leave['lwp'] ?? 0,'icon'=>'fa-ban','tone'=>'danger'],
['label'=>'Sandwich Cases','value'=>$leave['sandwich_leave'] ?? 0,'icon'=>'fa-layer-group','tone'=>'warning'],
];

$documentCards = [
['label'=>'Pending Verification','value'=>$documents['pending_verification'] ?? 0,'icon'=>'fa-file-signature','tone'=>'warning'],
['label'=>'Rejected Documents','value'=>$documents['rejected_documents'] ?? 0,'icon'=>'fa-file-excel','tone'=>'danger'],
['label'=>'Missing Documents','value'=>$documents['missing_documents'] ?? 0,'icon'=>'fa-file-circle-xmark','tone'=>'danger'],
['label'=>'Expired Documents','value'=>$documents['expired_documents'] ?? 0,'icon'=>'fa-calendar-times','tone'=>'warning'],
['label'=>'Recently Uploaded','value'=>$documents['recently_uploaded'] ?? 0,'icon'=>'fa-cloud-upload-alt','tone'=>'primary'],
];

$announcementCards = [
['label'=>'Active Announcements','value'=>$announcements['active'] ?? 0,'icon'=>'fa-bullhorn','tone'=>'primary'],
['label'=>'Published Today','value'=>$announcements['published_today'] ?? 0,'icon'=>'fa-calendar-day','tone'=>'success'],
['label'=>'Notifications Today','value'=>$announcements['notifications_today'] ?? 0,'icon'=>'fa-bell','tone'=>'info'],
['label'=>'Failed Push','value'=>$announcements['failed_pushes'] ?? 0,'icon'=>'fa-exclamation-circle','tone'=>'danger'],
];
@endphp

<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #fff;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-success: #12B76A;
        --orb-warning: #F79009;
        --orb-danger: #F04438;
        --orb-info: #0BA5EC;
        --orb-shadow: 0 16px 40px rgba(16, 24, 40, .08);
        --orb-shadow-sm: 0 8px 22px rgba(16, 24, 40, .055);
    }

    .sa-page {
        background: var(--orb-bg);
        padding: 22px;
        min-height: calc(100vh - 80px);
    }

    .sa-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 20px;
        align-items: center;
        padding: 24px;
        border-radius: 20px;
        background: linear-gradient(135deg, #4B00E8, #8600EE);
        color: #fff;
        overflow: hidden;
        margin-bottom: 22px;
    }

    .sa-hero-content {
        min-width: 0;
        max-width: 720px;
    }

    .sa-kicker {
        display: inline-flex;
        gap: 8px;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .15);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 8px;
    }

    .sa-hero h1 {
        margin: 0;
        font-size: 32px;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: -0.02em;
        white-space: normal;
        word-break: normal;
        overflow-wrap: normal;
    }

    .sa-hero p {
        max-width: 620px;
        margin: 8px 0 0;
        font-size: 14px;
        line-height: 1.6;
        color: rgba(255, 255, 255, .9);
    }

    .sa-hero-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
        max-width: 720px;
    }

    .sa-hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        padding: 10px 16px;
        border-radius: 12px;
        background: rgba(255, 255, 255, .15);
        border: 1px solid rgba(255, 255, 255, .28);
        color: #fff;
        text-decoration: none;
        font-weight: 700;
        white-space: nowrap;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .sa-hero-btn:hover {
        background: #fff;
        color: #4B00E8;
    }

    .sa-section {
        margin-bottom: 22px;
    }

    .sa-section-head {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 10px;
    }

    .sa-section-title {
        margin: 0;
        font-size: 18px;
        font-weight: 950;
        color: var(--orb-text);
        display: flex;
        gap: 10px;
        align-items: center;
        letter-spacing: -.02em;
    }

    .sa-section-title i {
        color: var(--orb-primary);
    }

    .sa-section-sub {
        margin: 2px 0 0;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .sa-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .sa-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .sa-grid-main {
        display: grid;
        grid-template-columns: 1.35fr .65fr;
        gap: 18px;
    }

    .sa-grid-half {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .sa-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        box-shadow: var(--orb-shadow-sm);
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .sa-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--orb-shadow);
    }

    .sa-stat {
        padding: 16px 18px;
        display: flex;
        gap: 14px;
        align-items: center;
        text-decoration: none;
        color: inherit;
        min-height: auto;
        position: relative;
        overflow: hidden;
    }

    .sa-stat::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: transparent;
    }

    .sa-stat.tone-primary::after,
    .sa-stat:has(.tone-primary)::after { background: var(--orb-primary); }
    .sa-stat.tone-success::after,
    .sa-stat:has(.tone-success)::after { background: #12B76A; }
    .sa-stat.tone-warning::after,
    .sa-stat:has(.tone-warning)::after { background: #F79009; }
    .sa-stat.tone-danger::after,
    .sa-stat:has(.tone-danger)::after { background: #F04438; }
    .sa-stat.tone-info::after,
    .sa-stat:has(.tone-info)::after { background: #0BA5EC; }
    .sa-stat.tone-neutral::after,
    .sa-stat:has(.tone-neutral)::after { background: #667085; }

    .sa-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 50% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex: 0 0 auto;
    }

    .tone-primary {
        background: #F4F2FF;
        color: #4B00E8;
    }

    .tone-success {
        background: #ECFDF3;
        color: #027A48;
    }

    .tone-warning {
        background: #FFFAEB;
        color: #B54708;
    }

    .tone-danger {
        background: #FEF3F2;
        color: #B42318;
    }

    .tone-info {
        background: #F0F9FF;
        color: #026AA2;
    }

    .tone-neutral {
        background: #F1F5F9;
        color: #475569;
    }

    .sa-stat-value {
        font-size: 28px;
        font-weight: 950;
        line-height: 1.1;
        color: var(--orb-text);
    }

    .sa-stat-label {
        margin-top: 4px;
        font-size: 11px;
        color: var(--orb-muted);
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .sa-panel-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .sa-panel-title {
        margin: 0;
        font-size: 17px;
        font-weight: 900;
        color: var(--orb-text);
    }

    .sa-panel-sub {
        margin: 4px 0 0;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .sa-panel-body {
        padding: 18px 20px;
    }

    .sa-action-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .sa-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px;
        border-radius: 18px;
        background: #F8FAFC;
        border: 1px solid #EEF2F7;
        color: inherit;
        text-decoration: none;
    }

    .sa-action:hover {
        background: #fff;
        box-shadow: var(--orb-shadow-sm);
        color: inherit;
    }

    .sa-action-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .sa-action-icon {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--orb-soft);
        color: var(--orb-primary);
        flex: 0 0 auto;
    }

    .sa-action-title {
        font-size: 14px;
        font-weight: 900;
        margin: 0;
    }

    .sa-action-sub {
        margin: 3px 0 0;
        font-size: 12px;
        color: var(--orb-muted);
    }

    .sa-badge {
        padding: 7px 11px;
        border-radius: 999px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .sa-badge-tone-danger {
        background: #FEF3F2;
        color: #B42318;
    }

    .sa-badge-tone-warning {
        background: #FFFAEB;
        color: #B54708;
    }

    .sa-badge-tone-success {
        background: #ECFDF3;
        color: #027A48;
    }

    .sa-table-wrap {
        overflow: auto;
        border-radius: 18px;
        border: 1px solid var(--orb-border);
    }

    .sa-table {
        width: 100%;
        margin: 0;
        white-space: nowrap;
        border-collapse: collapse;
    }

    .sa-table thead th {
        background: #F8FAFC;
        color: var(--orb-muted);
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: .03em;
        font-weight: 900;
        border-bottom: 1px solid var(--orb-border);
        padding: 13px 14px;
    }

    .sa-table tbody td {
        padding: 14px;
        border-bottom: 1px solid #F1F3F8;
        vertical-align: middle;
        font-weight: 600;
        color: #344054;
    }

    .sa-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .sa-name {
        font-weight: 900;
        color: var(--orb-text);
    }

    .sa-empty {
        padding: 30px;
        text-align: center;
        color: var(--orb-muted);
        font-weight: 700;
    }

    .sa-chart {
        min-height: 310px;
    }

    .sa-activity {
        display: flex;
        gap: 12px;
        padding: 13px 0;
        border-bottom: 1px solid #F1F3F8;
    }

    .sa-activity:last-child {
        border-bottom: 0;
    }

    .sa-activity-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--orb-primary);
        margin-top: 7px;
        flex: 0 0 auto;
    }

    .sa-activity-title {
        font-weight: 800;
        font-size: 13px;
        color: var(--orb-text);
    }

    .sa-quick-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .sa-quick {
        padding: 16px 10px;
        border-radius: 18px;
        background: #F8FAFC;
        border: 1px solid #EEF2F7;
        color: var(--orb-text);
        text-decoration: none;
        text-align: center;
        font-weight: 900;
        font-size: 12px;
    }

    .sa-quick i {
        display: block;
        color: var(--orb-primary);
        font-size: 22px;
        margin-bottom: 8px;
    }

    .sa-quick:hover {
        color: #fff;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    }

    .sa-quick:hover i {
        color: #fff;
    }

    .dt-buttons .btn {
        background: #fff;
        border: 1px solid var(--orb-border);
        color: var(--orb-text);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        margin-right: 6px;
    }

    .dt-buttons .btn:hover {
        background: #F8FAFC;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--orb-border);
        border-radius: 8px;
        padding: 6px 12px;
        outline: none;
    }

    @media(max-width:1200px) {
        .sa-grid-4,
        .sa-grid-3 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .sa-grid-main,
        .sa-grid-half {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width:768px) {
        .sa-page {
            padding: 14px;
        }

        .sa-hero {
            grid-template-columns: 1fr;
            padding: 22px;
            border-radius: 22px;
        }

        .sa-hero h1 {
            font-size: 26px;
        }

        .sa-hero-actions {
            justify-content: flex-start;
        }

        .sa-grid-4,
        .sa-grid-3 {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .sa-quick-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .sa-table-wrap {
            border-radius: 0;
            border-left: 0;
            border-right: 0;
            margin: 0 -20px;
        }
    }
</style>

<div class="sa-page">

    {{-- SECTION 1: HERO HEADER --}}
    <div class="sa-hero">
        <div class="sa-hero-content">
            <!-- <div class="sa-kicker">
                <i class="fas fa-shield-alt"></i>
                Super Admin Command Center
            </div> -->

            <h1>{{ $meta['title'] ?? 'Super Admin Dashboard' }}</h1>

            <p>
                {{ $meta['subtitle'] ?? 'Monitor HRMS operations, attendance, payroll, approvals and system health.' }}
            </p>

            <div class="sa-hero-meta" style="margin-top:12px; font-weight:700;">
                <i class="far fa-clock"></i>
                {{ $meta['current_date'] ?? now('Asia/Kolkata')->format('l, d M Y h:i A') }}
            </div>
        </div>

        <div class="sa-hero-actions">
            @foreach($quickActions as $action)
            @php
            $url = $actionUrl($action);
            $title = trim($action['title'] ?? '');
            @endphp
            @if($url && $title !== '')
            <a href="{{ $url }}" class="sa-hero-btn">
                <i class="{{ $action['icon'] ?? 'fas fa-arrow-right' }}"></i>
                {{ $title }}
            </a>
            @endif
            @endforeach
        </div>
    </div>

    {{-- SECTION 2: TODAY ATTENDANCE LIVE CARDS --}}
    <div class="sa-section">
        <div class="sa-section-head">
            <div>
                <h2 class="sa-section-title"><i class="fas fa-fingerprint"></i> Today Attendance</h2>
                <p class="sa-section-sub">Live attendance health for today.</p>
            </div>
        </div>

        <div class="sa-grid-4">
            @foreach($attendanceCards as $card)
            <a href="{{ $card['url'] ?? 'javascript:void(0)' }}" class="sa-card sa-stat">
                <div class="sa-stat-icon tone-{{ $card['tone'] }}">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <div>
                    <div class="sa-stat-value">{{ $num($card['value']) }}</div>
                    <div class="sa-stat-label">{{ $card['label'] }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- SECTION 3: EMPLOYEE LIFECYCLE OVERVIEW --}}
    <div class="sa-section">
        <div class="sa-section-head">
            <div>
                <h2 class="sa-section-title"><i class="fas fa-users"></i> Employee Lifecycle</h2>
                <p class="sa-section-sub">Employee onboarding, verification and lifecycle status.</p>
            </div>
        </div>

        <div class="sa-grid-4">
            @foreach($employeeCards as $card)
            <div class="sa-card sa-stat">
                <div class="sa-stat-icon tone-{{ $card['tone'] }}">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <div>
                    <div class="sa-stat-value">{{ $num($card['value']) }}</div>
                    <div class="sa-stat-label">{{ $card['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- SECTION 4: ACTION REQUIRED & SECTION 13: QUICK ACCESS --}}
    <div class="sa-grid-main sa-section">
        <div class="sa-card">
            <div class="sa-panel-head">
                <div>
                    <h3 class="sa-panel-title">Action Required Center</h3>
                    <p class="sa-panel-sub">Pending approvals and operational items that need attention.</p>
                </div>
            </div>
            <div class="sa-panel-body">
                <div class="sa-action-list">
                    @forelse($actionRequired as $item)
                    @if(!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="sa-action">
                        <div class="sa-action-left">
                            <div class="sa-action-icon"><i class="{{ $item['icon'] ?? 'fas fa-exclamation-circle' }}"></i></div>
                            <div>
                                <p class="sa-action-title">{{ $item['title'] }}</p>
                                <p class="sa-action-sub">{{ $item['subtitle'] ?? 'Click to review' }}</p>
                            </div>
                        </div>
                        <span class="sa-badge sa-badge-tone-{{ $item['tone'] ?? 'warning' }}">{{ $num($item['count']) }}</span>
                    </a>
                    @endif
                    @empty
                    <div class="sa-empty">No pending actions found. All caught up!</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-panel-head">
                <div>
                    <h3 class="sa-panel-title">Quick Access</h3>
                    <p class="sa-panel-sub">Frequently used HRMS modules.</p>
                </div>
            </div>
            <div class="sa-panel-body">
                <div class="sa-quick-grid">
                    @php $hasGridActions = false; @endphp
                    @foreach($quickActions as $action)
                    @php
                    $url = $actionUrl($action);
                    $title = trim($action['title'] ?? '');
                    @endphp

                    @if($url && $title !== '')
                    @php $hasGridActions = true; @endphp
                    <a href="{{ $url }}" class="sa-quick">
                        <i class="{{ $action['icon'] ?? 'fas fa-arrow-right' }}"></i>
                        {{ $title }}
                    </a>
                    @endif
                    @endforeach

                    @if(!$hasGridActions)
                    <div class="sa-empty" style="grid-column: 1 / -1; padding: 20px; border: 1px dashed var(--orb-border); border-radius: 18px;">
                        No quick actions available
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 6: PAYROLL OVERVIEW --}}
    <div class="sa-section">
        <div class="sa-section-head">
            <div>
                <h2 class="sa-section-title"><i class="fas fa-money-check-alt"></i> Payroll Overview</h2>
                <p class="sa-section-sub">Enterprise payroll and salary processing summary.</p>
            </div>
        </div>

        <div class="sa-grid-3">
            @foreach($payrollCards as $card)
            <div class="sa-card sa-stat">
                <div class="sa-stat-icon tone-{{ $card['tone'] }}">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <div>
                    <div class="sa-stat-value" style="font-size: 22px;">{{ $card['value'] }}</div>
                    <div class="sa-stat-label">{{ $card['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- SECTION 7: LEAVE MANAGEMENT & SECTION 8: DOCUMENT MANAGEMENT --}}
    <div class="sa-grid-half sa-section">
        <div class="sa-card">
            <div class="sa-panel-head">
                <div>
                    <h3 class="sa-panel-title">Leave Management Overview</h3>
                    <p class="sa-panel-sub">Monthly leave statistics and pending requests.</p>
                </div>
            </div>
            <div class="sa-panel-body">
                <div class="sa-grid-3" style="gap:12px;">
                    @foreach($leaveCards as $card)
                    <div class="sa-stat" style="border:1px solid #E7EAF3; border-radius: 12px; padding: 12px; flex-direction: column; text-align: center; gap: 8px; min-height: auto;">
                        <div class="sa-stat-icon tone-{{ $card['tone'] }}" style="margin:0 auto; width:36px; height:36px; font-size:16px;">
                            <i class="fas {{ $card['icon'] }}"></i>
                        </div>
                        <div style="font-weight:900; font-size:18px;">{{ $num($card['value']) }}</div>
                        <div class="sa-stat-label" style="margin-top:0;">{{ $card['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-panel-head">
                <div>
                    <h3 class="sa-panel-title">Document Management Overview</h3>
                    <p class="sa-panel-sub">Employee document compliance and verification.</p>
                </div>
            </div>
            <div class="sa-panel-body">
                <div class="sa-grid-3" style="gap:12px;">
                    @foreach($documentCards as $card)
                    <div class="sa-stat" style="border:1px solid #E7EAF3; border-radius: 12px; padding: 12px; flex-direction: column; text-align: center; gap: 8px; min-height: auto;">
                        <div class="sa-stat-icon tone-{{ $card['tone'] }}" style="margin:0 auto; width:36px; height:36px; font-size:16px;">
                            <i class="fas {{ $card['icon'] }}"></i>
                        </div>
                        <div style="font-weight:900; font-size:18px;">{{ $num($card['value']) }}</div>
                        <div class="sa-stat-label" style="margin-top:0;">{{ $card['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 5: LIVE ATTENDANCE TABLE --}}
    <div class="sa-section sa-card">
        <div class="sa-panel-head">
            <div>
                <h3 class="sa-panel-title">Live Attendance Table</h3>
                <p class="sa-panel-sub">Real-time attendance logs for today.</p>
            </div>
        </div>
        <div class="sa-panel-body">
            <div class="sa-table-wrap" style="border:0;">
                <table class="sa-table" id="liveAttendanceTable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Department</th>
                            <th>Shift</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Work Mode</th>
                            <th>Flags</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($liveAttendance as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="sa-name">{{ $row['employee_name'] ?? 'N/A' }}</div>
                            </td>
                            <td>{{ $row['employee_code'] ?? '-' }}</td>
                            <td>{{ $row['department_name'] ?? '-' }}</td>
                            <td>{{ $row['shift_name'] ?? '-' }}</td>
                            <td>{{ $row['punch_in_time'] ? \Carbon\Carbon::parse($row['punch_in_time'])->format('h:i A') : '-' }}</td>
                            <td>{{ $row['punch_out_time'] ? \Carbon\Carbon::parse($row['punch_out_time'])->format('h:i A') : '-' }}</td>
                            <td><span class="sa-badge tone-neutral">{{ $row['work_mode'] ?? 'WFO' }}</span></td>
                            <td>
                                @if(!empty($row['flags']))
                                @foreach($row['flags'] as $flag)
                                <span class="sa-badge tone-warning" style="padding: 4px 8px; font-size: 10px;">{{ $flag }}</span>
                                @endforeach
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SECTION 11: CHARTS & ANALYTICS --}}
    <div class="sa-grid-half sa-section">
        <div class="sa-card">
            <div class="sa-panel-head">
                <div>
                    <h3 class="sa-panel-title">Attendance Trend</h3>
                    <p class="sa-panel-sub">Present, late, and absent trend this month.</p>
                </div>
            </div>
            <div class="sa-panel-body">
                <div id="attendanceTrendChart" class="sa-chart"></div>
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-panel-head">
                <div>
                    <h3 class="sa-panel-title">Payroll Trend</h3>
                    <p class="sa-panel-sub">Monthly net and gross payroll trend.</p>
                </div>
            </div>
            <div class="sa-panel-body">
                <div id="payrollTrendChart" class="sa-chart"></div>
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-panel-head">
                <div>
                    <h3 class="sa-panel-title">Employee Lifecycle Distribution</h3>
                    <p class="sa-panel-sub">Current count of employees by stage.</p>
                </div>
            </div>
            <div class="sa-panel-body">
                <div id="employeeLifecycleChart" class="sa-chart"></div>
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-panel-head">
                <div>
                    <h3 class="sa-panel-title">Leave Distribution</h3>
                    <p class="sa-panel-sub">Current leave request status split.</p>
                </div>
            </div>
            <div class="sa-panel-body">
                <div id="leaveDistributionChart" class="sa-chart"></div>
            </div>
        </div>
    </div>

    {{-- SECTION 9: ANNOUNCEMENT & SECTION 10: SYSTEM HEALTH --}}
    <div class="sa-grid-half sa-section">
        <div class="sa-card">
            <div class="sa-panel-head">
                <div>
                    <h3 class="sa-panel-title">Announcement & Notification Center</h3>
                    <p class="sa-panel-sub">Communication and alerts overview.</p>
                </div>
            </div>
            <div class="sa-panel-body">
                <div class="sa-grid-4" style="gap: 12px; margin-bottom: 20px;">
                    @foreach($announcementCards as $card)
                    <div class="sa-stat" style="border:1px solid #E7EAF3; border-radius: 12px; padding: 12px; flex-direction: column; text-align: center; gap: 8px; min-height: auto;">
                        <div class="sa-stat-icon tone-{{ $card['tone'] }}" style="margin:0 auto; width:36px; height:36px; font-size:16px;">
                            <i class="fas {{ $card['icon'] }}"></i>
                        </div>
                        <div style="font-weight:900; font-size:18px;">{{ $num($card['value']) }}</div>
                        <div class="sa-stat-label" style="margin-top:0;">{{ $card['label'] }}</div>
                    </div>
                    @endforeach
                </div>

                <h4 style="font-size: 14px; font-weight: 800; margin-bottom: 12px;">Latest Announcements</h4>
                <div class="sa-table-wrap">
                    <table class="sa-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Published</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements['latest'] ?? [] as $ann)
                            <tr>
                                <td>
                                    <div class="sa-name">{{ $ann['title'] ?? 'N/A' }}</div>
                                </td>
                                <td>{{ !empty($ann['created_at']) ? \Carbon\Carbon::parse($ann['created_at'])->format('d M, Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2">
                                    <div class="sa-empty">No active announcements.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-panel-head">
                <div>
                    <h3 class="sa-panel-title">System Health</h3>
                    <p class="sa-panel-sub">Technical infrastructure and integration status.</p>
                </div>
            </div>
            <div class="sa-panel-body">
                <div class="sa-action-list">
                    @forelse($systemHealth as $health)
                    <div class="sa-action" style="cursor: default;">
                        <div class="sa-action-left">
                            <div class="sa-action-icon tone-{{ $health['status'] == 'ok' ? 'success' : ($health['status'] == 'danger' ? 'danger' : 'warning') }}">
                                <i class="{{ $health['icon'] ?? 'fas fa-server' }}"></i>
                            </div>
                            <div>
                                <p class="sa-action-title">{{ $health['label'] }}</p>
                                <p class="sa-action-sub">{{ $health['value'] }}</p>
                            </div>
                        </div>
                        <span class="sa-badge sa-badge-tone-{{ $health['status'] == 'ok' ? 'success' : ($health['status'] == 'danger' ? 'danger' : 'warning') }}">{{ strtoupper($health['status'] ?? 'UNKNOWN') }}</span>
                    </div>
                    @empty
                    <div class="sa-empty">System health data unavailable.</div>
                    @endforelse

                    <div class="sa-action" style="cursor: default;">
                        <div class="sa-action-left">
                            <div class="sa-action-icon tone-primary">
                                <i class="fab fa-android"></i>
                            </div>
                            <div>
                                <p class="sa-action-title">Mobile APK Version</p>
                                <p class="sa-action-sub">{{ $system['apk_version'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <span class="sa-badge tone-primary">CURRENT</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 12: RECENT ACTIVITY FEED --}}
    <div class="sa-section sa-card">
        <div class="sa-panel-head">
            <div>
                <h3 class="sa-panel-title">Recent Activity Feed</h3>
                <p class="sa-panel-sub">Latest cross-module actions across the HRMS.</p>
            </div>
        </div>
        <div class="sa-panel-body">
            @forelse($recentActivities as $activity)
            <div class="sa-activity">
                <div class="sa-activity-dot"></div>
                <div style="flex:1;">
                    <div class="sa-activity-title">{{ $activity['title'] ?? 'Action taken' }}</div>
                    <div class="sa-muted">{{ $activity['description'] ?? '' }}</div>
                </div>
                <div class="sa-muted" style="text-align:right;">
                    {{ !empty($activity['created_at']) ? \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() : '-' }}
                </div>
            </div>
            @empty
            <div class="sa-empty">No recent activity found.</div>
            @endforelse
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Initialize DataTable
        if ($('#liveAttendanceTable tbody tr').length > 0) {
            $('#liveAttendanceTable').DataTable({
                dom: 'Bfrtip',
                pageLength: 10,
                responsive: true,
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records..."
                }
            });
        }

        // 1. Attendance Trend Chart
        var attData = @json($charts['monthly_attendance'] ?? []);
        if (attData.labels && attData.labels.length > 0) {
            new ApexCharts(document.querySelector("#attendanceTrendChart"), {
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                        name: 'Present',
                        data: attData.present
                    },
                    {
                        name: 'Late',
                        data: attData.late
                    },
                    {
                        name: 'Absent',
                        data: attData.absent
                    }
                ],
                xaxis: {
                    categories: attData.labels
                },
                colors: ['#12B76A', '#F79009', '#F04438'],
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: {
                    enabled: false
                }
            }).render();
        }

        // 2. Payroll Trend Chart
        var payData = @json($payroll['monthly_trend'] ?? []);
        if (payData.labels && payData.labels.length > 0) {
            new ApexCharts(document.querySelector("#payrollTrendChart"), {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                        name: 'Net Pay',
                        data: payData.net
                    },
                    {
                        name: 'Gross Pay',
                        data: payData.gross
                    }
                ],
                xaxis: {
                    categories: payData.labels
                },
                colors: ['#4B00E8', '#0BA5EC'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: false,
                        columnWidth: '50%'
                    }
                },
                dataLabels: {
                    enabled: false
                }
            }).render();
        }

        // 3. Employee Lifecycle Distribution
        var lcData = @json($charts['employee_lifecycle'] ?? []);
        if (lcData.labels && lcData.labels.length > 0) {
            new ApexCharts(document.querySelector("#employeeLifecycleChart"), {
                chart: {
                    type: 'donut',
                    height: 300
                },
                series: lcData.values,
                labels: lcData.labels,
                colors: ['#4B00E8', '#12B76A', '#F79009', '#F04438', '#0BA5EC'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%'
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                }
            }).render();
        }

        // 4. Leave Distribution Chart
        var leaveData = @json($charts['leave_distribution'] ?? []);
        if (leaveData.labels && leaveData.labels.length > 0) {
            new ApexCharts(document.querySelector("#leaveDistributionChart"), {
                chart: {
                    type: 'donut',
                    height: 300
                },
                series: leaveData.values,
                labels: leaveData.labels,
                colors: ['#4B00E8', '#12B76A', '#F79009', '#F04438'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%'
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                }
            }).render();
        }

    });
</script>
@endsection