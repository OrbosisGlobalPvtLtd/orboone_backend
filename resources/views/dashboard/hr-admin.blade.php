@extends('layouts.panel')
@section('page_title', $dashboard['meta']['title'] ?? 'HR Admin Dashboard')
@section('_content')
<style>
    .hr-dashboard {
        --hr-primary: var(--orb-primary, #4B00E8);
        --hr-secondary: var(--orb-secondary, #FF5252);
        --hr-bg: #F6F7FB;
        --hr-card: #FFFFFF;
        --hr-border: #E7EAF3;
        --hr-text: #101828;
        --hr-muted: #667085;
        --hr-shadow: 0 14px 35px rgba(16, 24, 40, .07);
        background: var(--hr-bg);
        padding: 22px;
    }

    .hr-hero {
        border-radius: 24px;
        padding: 26px;
        color: #fff;
        background: linear-gradient(135deg, var(--hr-primary), var(--hr-secondary));
        box-shadow: var(--hr-shadow);
        margin-bottom: 18px;
    }

    .hr-hero h3 { margin: 0; font-weight: 800; }
    .hr-hero p { margin: 6px 0 0; opacity: .9; }

    .hr-quick-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
    .hr-quick-actions a {
        background: rgba(255,255,255,.16);
        color: #fff;
        border: 1px solid rgba(255,255,255,.24);
        border-radius: 12px;
        padding: 10px 13px;
        text-decoration: none;
        font-weight: 700;
        font-size: 12px;
    }

    .hr-dashboard .hr-metric-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 18px;
    }

    .hr-dashboard .hr-metric-link { text-decoration: none !important; color: inherit; }

    .hr-dashboard .hr-metric-card {
        min-height: 88px;
        padding: 12px;
        border-radius: 18px;
        border: 1px solid var(--hr-border);
        background: #fff;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .045);
        position: relative;
        overflow: hidden;
        transition: .18s ease;
        display: block;
    }

    .hr-dashboard .hr-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(16, 24, 40, .08);
    }

    .hr-dashboard .hr-metric-card::after {
        content: "";
        position: absolute;
        right: -32px;
        top: -34px;
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: var(--tone-soft);
    }

    .hr-dashboard .hr-metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        position: relative;
        z-index: 1;
    }

    .hr-dashboard .hr-metric-icon {
        width: 34px;
        height: 34px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tone-soft);
        color: var(--tone);
        font-size: 14px;
        margin-bottom: 0;
    }

    .hr-dashboard .hr-metric-value {
        font-size: 25px;
        line-height: 1;
        font-weight: 950;
        color: var(--hr-text);
    }

    .hr-dashboard .hr-metric-label {
        margin-top: 10px;
        font-size: 10px;
        color: var(--hr-muted);
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .035em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        position: relative;
        z-index: 1;
    }

    .hr-dashboard .hr-metric-underline {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 9px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--tone), transparent);
    }

    /* Soft corner backgrounds / underline accents mapped to specific card tones */
    .hr-dashboard .tone-success {
        --tone: #12B76A;
        --tone-soft: rgba(18, 183, 106, .12);
    }

    .hr-dashboard .tone-danger {
        --tone: #F04438;
        --tone-soft: rgba(240, 68, 56, .12);
    }

    .hr-dashboard .tone-warning {
        --tone: #F79009;
        --tone-soft: rgba(247, 144, 9, .14);
    }

    .hr-dashboard .tone-orange {
        --tone: #EA580C;
        --tone-soft: rgba(234, 88, 12, .13);
    }

    .hr-dashboard .tone-amber {
        --tone: #D97706;
        --tone-soft: rgba(217, 119, 6, .13);
    }

    .hr-dashboard .tone-blocked {
        --tone: #B42318;
        --tone-soft: rgba(180, 35, 24, .13);
    }

    .hr-dashboard .tone-purple {
        --tone: #7A5AF8;
        --tone-soft: rgba(122, 90, 248, .13);
    }

    .hr-dashboard .tone-blue {
        --tone: #2563EB;
        --tone-soft: rgba(37, 99, 235, .12);
    }

    .hr-dashboard .tone-info {
        --tone: #0EA5E9;
        --tone-soft: rgba(14, 165, 233, .13);
    }

    .hr-dashboard .tone-indigo {
        --tone: #4F46E5;
        --tone-soft: rgba(79, 70, 229, .13);
    }

    .hr-dashboard .tone-teal {
        --tone: #0F766E;
        --tone-soft: rgba(15, 118, 110, .13);
    }

    .hr-grid-2 {
        display: grid;
        grid-template-columns: 1.4fr .9fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .hr-grid-2-eq {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .hr-section-card {
        background: #fff;
        border: 1px solid var(--hr-border);
        border-radius: 22px;
        box-shadow: var(--hr-shadow);
        overflow: hidden;
    }

    .hr-section-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--hr-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .hr-section-head h5 { margin: 0; font-weight: 800; color: var(--hr-text); }
    .hr-section-head small { color: var(--hr-muted); }

    .hr-section-body { padding: 16px 18px; }

    .hr-table { width: 100%; }
    .hr-table th {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--hr-muted);
        letter-spacing: .3px;
        padding: 11px 10px;
        border-bottom: 1px solid #EEF2F7;
        white-space: nowrap;
    }
    .hr-table td {
        font-size: 13px;
        color: #1D2939;
        padding: 10px;
        border-bottom: 1px solid #F1F4FA;
        vertical-align: middle;
    }

    .hr-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 6px 9px;
        font-size: 11px;
        font-weight: 700;
        background: #F4F2FF;
        color: var(--orb-primary);
    }

    .hr-empty {
        padding: 24px;
        text-align: center;
        color: var(--hr-muted);
        font-size: 13px;
        background: #FBFCFF;
        border: 1px dashed #E5EAF4;
        border-radius: 14px;
    }

    .hr-mini-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }

    .hr-mini {
        border: 1px solid #EAF0F8;
        border-radius: 14px;
        padding: 12px;
        background: #fff;
    }

    .hr-mini .label { font-size: 11px; text-transform: uppercase; color: var(--hr-muted); font-weight: 700; }
    .hr-mini .value { font-size: 25px; font-weight: 900; color: var(--hr-text); }

    .hr-activity-item {
        display: flex;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #F1F4FA;
    }

    .hr-activity-item:last-child { border-bottom: 0; }

    .hr-activity-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #F4F2FF;
        color: var(--orb-primary);
        flex-shrink: 0;
    }

    @media(max-width:1280px) {
        .hr-dashboard .hr-metric-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media(max-width:992px) {
        .hr-dashboard .hr-metric-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .hr-grid-2, .hr-grid-2-eq { grid-template-columns: 1fr; }
        .hr-quick-actions { justify-content: flex-start; margin-top: 12px; }
    }

    @media(max-width:576px) {
        .hr-dashboard { padding: 14px; }
        .hr-dashboard .hr-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .hr-dashboard .hr-metric-card {
            min-height: 86px;
            padding: 10px;
            border-radius: 16px;
        }

        .hr-dashboard .hr-metric-value {
            font-size: 22px;
        }

        .hr-dashboard .hr-metric-label {
            font-size: 9px;
        }
        .hr-mini-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="hr-dashboard">
    <div class="hr-hero">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h3>HR Operations Dashboard</h3>
                <p>Monitor attendance, approvals, onboarding, documents and employee lifecycle.</p>
                <small>{{ now('Asia/Kolkata')->format('l, d M Y h:i A') }}</small>
            </div>
            <div class="col-lg-5">
                <div class="hr-quick-actions">
                    <a href="{{ route('hrms.employees.create') }}">Add Employee</a>
                    <a href="{{ route('hrms.employees.pending_profiles') }}">Pending Profiles</a>
                    <a href="{{ route('leave-approvals.index') }}">Approve Leaves</a>
                    <a href="{{ route('announcements.index') }}">Publish Notice</a>
                </div>
            </div>
        </div>
    </div>

    @php
        $mkRoute = function (string $name) {
            return \Illuminate\Support\Facades\Route::has($name) ? route($name) : null;
        };

        $metrics = [
            ['title'=>'Active Employees','value'=>$dashboard['cards']['active_employees'] ?? 0,'icon'=>'fa-users','tone'=>'blue','url'=>$mkRoute('hrms.employees.index')],
            ['title'=>'Present Today','value'=>$dashboard['cards']['present_today'] ?? 0,'icon'=>'fa-user-check','tone'=>'success','url'=>$mkRoute('attendances.record')],
            ['title'=>'Absent Today','value'=>$dashboard['cards']['absent_today'] ?? 0,'icon'=>'fa-user-times','tone'=>'danger','url'=>$mkRoute('attendances.record')],
            ['title'=>'Late Employees','value'=>$dashboard['cards']['late_today'] ?? 0,'icon'=>'fa-clock','tone'=>'warning','url'=>$mkRoute('attendances.record')],
            ['title'=>'Early Logout','value'=>$dashboard['cards']['early_logout'] ?? 0,'icon'=>'fa-sign-out-alt','tone'=>'orange','url'=>$mkRoute('hrms.attendance.violations.index')],
            ['title'=>'Punch Blocked','value'=>$dashboard['cards']['punch_blocked'] ?? 0,'icon'=>'fa-ban','tone'=>'blocked','url'=>$mkRoute('attendances.pending-approval')],
            ['title'=>'Pending Punch Out','value'=>$dashboard['cards']['pending_punch_out'] ?? 0,'icon'=>'fa-hourglass-half','tone'=>'info','url'=>$mkRoute('attendances.record')],
            ['title'=>'Pending Leaves','value'=>$dashboard['cards']['pending_leaves'] ?? 0,'icon'=>'fa-calendar-check','tone'=>'purple','url'=>$mkRoute('leave-approvals.index')],
            ['title'=>'Pending Profiles','value'=>$dashboard['cards']['pending_profiles'] ?? 0,'icon'=>'fa-id-card','tone'=>'teal','url'=>$mkRoute('hrms.employees.pending_profiles')],
            ['title'=>'Pending Documents','value'=>$dashboard['cards']['pending_documents'] ?? 0,'icon'=>'fa-file-alt','tone'=>'indigo','url'=>$mkRoute('documents.hr.index')],
            ['title'=>'WFH Requests','value'=>$dashboard['cards']['wfh_requests'] ?? 0,'icon'=>'fa-home','tone'=>'teal','url'=>$mkRoute('hrms.attendance.wfh.index')],
            ['title'=>'Attendance Regularization','value'=>$dashboard['cards']['attendance_regularization'] ?? 0,'icon'=>'fa-user-edit','tone'=>'purple','url'=>$mkRoute('hrms.attendance.regularizations.index')],
            ['title'=>'Holiday Work Requests','value'=>$dashboard['cards']['holiday_work_requests'] ?? 0,'icon'=>'fa-calendar-plus','tone'=>'amber','url'=>$mkRoute('hrms.attendance.holiday_work.index')],
            ['title'=>'New Joiners','value'=>$dashboard['cards']['new_joiners'] ?? 0,'icon'=>'fa-user-plus','tone'=>'success','url'=>$mkRoute('hrms.employees.index')],
        ];
    @endphp

    <div class="hr-metric-grid">
        @foreach($metrics as $metric)
            @if(!empty($metric['url']))
                <a class="hr-metric-link" href="{{ $metric['url'] }}">
            @endif
            <div class="hr-metric-card tone-{{ $metric['tone'] }}">
                <div class="hr-metric-top">
                    <div class="hr-metric-icon"><i class="fas {{ $metric['icon'] }}"></i></div>
                    <div class="hr-metric-value">{{ $metric['value'] }}</div>
                </div>
                <div class="hr-metric-label" title="{{ $metric['title'] }}">{{ $metric['title'] }}</div>
                <div class="hr-metric-underline"></div>
            </div>
            @if(!empty($metric['url']))
                </a>
            @endif
        @endforeach
    </div>

    <div class="hr-grid-2">
        <div class="hr-section-card">
            <div class="hr-section-head">
                <div>
                    <h5>Punch-In Blocked Employees</h5>
                    <small>Employees blocked today based on attendance policy cut-off.</small>
                </div>
                <span class="hr-pill">{{ count($dashboard['tables']['blocked_employees'] ?? []) }} Blocked</span>
            </div>
            <div class="hr-section-body table-responsive">
                @if(!empty($dashboard['tables']['blocked_employees']))
                    <table class="hr-table">
                        <thead>
                            <tr><th>Employee</th><th>Department</th><th>Blocked At</th><th>Reason</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        @foreach($dashboard['tables']['blocked_employees'] as $row)
                            <tr>
                                <td><strong>{{ $row['employee_name'] ?? 'N/A' }}</strong><br><small>{{ $row['employee_code'] ?? '-' }}</small></td>
                                <td>{{ $row['department_name'] ?? '-' }}</td>
                                <td>{{ ($row['auto_blocked_at'] ?? null) ? \Carbon\Carbon::parse($row['auto_blocked_at'])->format('h:i A') : '-' }}</td>
                                <td>{{ $row['block_reason'] ?? '-' }}</td>
                                <td><a href="{{ route('attendances.pending-approval') }}" class="hr-pill">Review</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="hr-empty">No punch-in blocked employees today.</div>
                @endif
            </div>
        </div>

        <div class="hr-section-card">
            <div class="hr-section-head">
                <div>
                    <h5>Employee Lifecycle</h5>
                    <small>Stage distribution from active employee lifecycle data.</small>
                </div>
            </div>
            <div class="hr-section-body">
                <div class="hr-mini-grid">
                    <div class="hr-mini"><div class="label">Internship</div><div class="value">{{ $dashboard['employee_lifecycle']['interns'] ?? 0 }}</div></div>
                    <div class="hr-mini"><div class="label">Probation</div><div class="value">{{ $dashboard['employee_lifecycle']['probation'] ?? 0 }}</div></div>
                    <div class="hr-mini"><div class="label">Permanent</div><div class="value">{{ $dashboard['employee_lifecycle']['permanent'] ?? 0 }}</div></div>
                    <div class="hr-mini"><div class="label">Inactive / Exited</div><div class="value">{{ $dashboard['employee_lifecycle']['exit_process'] ?? 0 }}</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="hr-grid-2-eq">
        <div class="hr-section-card">
            <div class="hr-section-head">
                <div><h5>Pending Leave Requests</h5><small>Latest pending leave approvals.</small></div>
                <a href="{{ route('leave-approvals.index') }}" class="hr-pill">View All</a>
            </div>
            <div class="hr-section-body table-responsive">
                @if(!empty($dashboard['tables']['pending_leaves']))
                    <table class="hr-table">
                        <thead><tr><th>Employee</th><th>Leave Type</th><th>Date Range</th><th>Days</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                        @foreach($dashboard['tables']['pending_leaves'] as $leave)
                            <tr>
                                <td><strong>{{ $leave['employee_name'] ?? '-' }}</strong><br><small>{{ $leave['employee_code'] ?? '-' }}</small></td>
                                <td>{{ $leave['leave_type'] ?? '-' }}</td>
                                <td>{{ $leave['start_date'] ?? '-' }} to {{ $leave['end_date'] ?? '-' }}</td>
                                <td>{{ $leave['deducted_days'] ?? '-' }}</td>
                                <td><span class="hr-pill">{{ ucfirst(str_replace('_',' ', $leave['status'] ?? '-')) }}</span></td>
                                <td><a href="{{ route('leave-approvals.index') }}" class="hr-pill">Review</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="hr-empty">No pending leave requests.</div>
                @endif
            </div>
        </div>

        <div class="hr-section-card">
            <div class="hr-section-head">
                <div><h5>WFH Requests Pending</h5><small>Requests awaiting final approval.</small></div>
                <a href="{{ route('hrms.attendance.wfh.index') }}" class="hr-pill">View All</a>
            </div>
            <div class="hr-section-body table-responsive">
                @if(!empty($dashboard['tables']['pending_wfh']))
                    <table class="hr-table">
                        <thead><tr><th>Employee</th><th>Date</th><th>Reason Category</th><th>Quota</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                        @foreach($dashboard['tables']['pending_wfh'] as $wfh)
                            <tr>
                                <td><strong>{{ $wfh['employee_name'] ?? '-' }}</strong><br><small>{{ $wfh['employee_code'] ?? '-' }}</small></td>
                                <td>{{ $wfh['request_date'] ?? '-' }}</td>
                                <td>{{ ucfirst(str_replace('_',' ', $wfh['reason_category'] ?? '-')) }}</td>
                                <td><span class="hr-pill">{{ $wfh['quota_impact'] ?? '-' }}</span></td>
                                <td><span class="hr-pill">{{ ucfirst(str_replace('_',' ', $wfh['status'] ?? '-')) }}</span></td>
                                <td><a href="{{ route('hrms.attendance.wfh.index') }}" class="hr-pill">Review</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="hr-empty">No pending WFH requests.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="hr-grid-2-eq">
        <div class="hr-section-card">
            <div class="hr-section-head">
                <div><h5>Pending Profiles</h5><small>Profiles pending completion/review.</small></div>
                <a href="{{ route('hrms.employees.pending_profiles') }}" class="hr-pill">View All</a>
            </div>
            <div class="hr-section-body table-responsive">
                @if(!empty($dashboard['tables']['pending_profiles']))
                    <table class="hr-table">
                        <thead><tr><th>Employee</th><th>Code</th><th>Employment Stage</th><th>Profile Status</th><th>Action</th></tr></thead>
                        <tbody>
                        @foreach($dashboard['tables']['pending_profiles'] as $profile)
                            <tr>
                                <td>{{ $profile['employee_name'] ?? '-' }}</td>
                                <td>{{ $profile['employee_code'] ?? '-' }}</td>
                                <td>{{ ucfirst($profile['employee_stage'] ?? '-') }}</td>
                                <td><span class="hr-pill">{{ ucfirst(str_replace('_',' ', $profile['profile_status'] ?? '-')) }}</span></td>
                                <td><a href="{{ route('hrms.employees.pending_profiles') }}" class="hr-pill">Review</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="hr-empty">No pending profiles.</div>
                @endif
            </div>
        </div>

        <div class="hr-section-card">
            <div class="hr-section-head">
                <div><h5>Pending Documents</h5><small>Uploaded documents awaiting verification.</small></div>
                <a href="{{ route('documents.hr.index') }}" class="hr-pill">View All</a>
            </div>
            <div class="hr-section-body table-responsive">
                @if(!empty($dashboard['tables']['pending_documents']))
                    <table class="hr-table">
                        <thead><tr><th>Employee</th><th>Document</th><th>Uploaded At</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                        @foreach($dashboard['tables']['pending_documents'] as $doc)
                            <tr>
                                <td>{{ $doc['employee_name'] ?? '-' }}</td>
                                <td>{{ $doc['document_name'] ?? '-' }}</td>
                                <td>{{ ($doc['uploaded_at'] ?? null) ? \Carbon\Carbon::parse($doc['uploaded_at'])->format('d M Y h:i A') : '-' }}</td>
                                <td><span class="hr-pill">{{ ucfirst(str_replace('_',' ', $doc['verification_status'] ?? '-')) }}</span></td>
                                <td><a href="{{ route('documents.hr.index') }}" class="hr-pill">Review</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="hr-empty">No pending documents.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="hr-section-card">
        <div class="hr-section-head">
            <div><h5>Recent HR Activity</h5><small>Live updates from employees, leave, documents, WFH and regularizations.</small></div>
        </div>
        <div class="hr-section-body">
            @forelse(($dashboard['recent_activities'] ?? []) as $activity)
                <div class="hr-activity-item">
                    <span class="hr-activity-icon"><i class="{{ $activity['icon'] ?? 'fas fa-circle' }}"></i></span>
                    <div>
                        <div style="font-weight:700; color:#101828;">{{ $activity['title'] ?? '-' }}</div>
                        <div style="font-size:12px; color:#475467;">{{ $activity['description'] ?? '-' }}</div>
                        <div style="font-size:11px; color:#98A2B3; margin-top:2px;">{{ ($activity['created_at'] ?? null) ? \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() : (($activity['time'] ?? null) ? \Carbon\Carbon::parse($activity['time'])->diffForHumans() : '-') }}</div>
                    </div>
                </div>
            @empty
                <div class="hr-empty">No recent HR activity found.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
