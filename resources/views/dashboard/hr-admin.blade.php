@extends('layouts.panel')
@section('page_title', $dashboard['meta']['title'] ?? 'HR Admin Dashboard')
@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .hr-dash {
        background: var(--orb-bg);
        padding: 22px;
    }

    .hr-hero {
        border-radius: 26px;
        padding: 28px;
        color: #fff;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        box-shadow: var(--orb-shadow);
        margin-bottom: 22px;
    }

    .hr-hero h3 {
        font-weight: 800;
        margin: 0;
    }

    .hr-hero p {
        opacity: .9;
        margin: 6px 0 0;
    }

    .quick-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .quick-actions a {
        background: rgba(255, 255, 255, .16);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .25);
        padding: 10px 14px;
        border-radius: 14px;
        text-decoration: none;
        font-weight: 600;
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        padding: 18px;
        box-shadow: var(--orb-shadow);
        transition: .2s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-icon {
        height: 42px;
        width: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--orb-soft);
        color: var(--orb-primary);
        margin-bottom: 12px;
    }

    .stat-title {
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 900;
        color: var(--orb-text);
    }

    .stat-sub {
        font-size: 12px;
        color: var(--orb-muted);
    }

    .dash-grid {
        display: grid;
        grid-template-columns: 1.3fr .7fr;
        gap: 18px;
        margin-bottom: 22px;
    }

    .orb-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .orb-card-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .orb-card-head h5 {
        margin: 0;
        font-weight: 800;
        color: var(--orb-text);
    }

    .orb-card-head small {
        color: var(--orb-muted);
    }

    .orb-card-body {
        padding: 18px 20px;
    }

    .premium-table {
        width: 100%;
    }

    .premium-table th {
        font-size: 12px;
        color: var(--orb-muted);
        text-transform: uppercase;
        border-bottom: 1px solid var(--orb-border);
        padding: 12px;
    }

    .premium-table td {
        padding: 12px;
        border-bottom: 1px solid #F1F3F8;
        vertical-align: middle;
    }

    .badge-soft {
        background: var(--orb-soft);
        color: var(--orb-primary);
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .badge-danger-soft {
        background: #FEF3F2;
        color: #B42318;
    }

    .badge-warning-soft {
        background: #FFFAEB;
        color: #B54708;
    }

    .badge-success-soft {
        background: #ECFDF3;
        color: #027A48;
    }

    .activity-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #F1F3F8;
    }

    .activity-dot {
        height: 10px;
        width: 10px;
        border-radius: 50%;
        background: var(--orb-primary);
        margin-top: 7px;
    }

    .mini-card-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }

    @media(max-width:1100px) {
        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .dash-grid {
            grid-template-columns: 1fr;
        }

        .quick-actions {
            justify-content: flex-start;
            margin-top: 16px;
        }
    }

    @media(max-width:600px) {

        .stat-grid,
        .mini-card-grid {
            grid-template-columns: 1fr;
        }

        .hr-dash {
            padding: 14px;
        }
    }
</style>

<div class="hr-dash">

    <div class="hr-hero">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h3>HR Operations Dashboard</h3>
                <p>Monitor attendance, approvals, onboarding, documents and employee lifecycle.</p>
                <small>{{ now('Asia/Kolkata')->format('l, d M Y h:i A') }}</small>
            </div>
            <div class="col-lg-5">
                <div class="quick-actions">
                    <a href="{{ route('hrms.employees.create') }}">Add Employee</a>
                    <a href="{{ route('hrms.employees.pending_profiles') }}">Pending Profiles</a>
                    <a href="{{ route('leave-approvals.index') }}">Approve Leaves</a>
                    <a href="{{ route('announcements.index') }}">Publish Notice</a>
                </div>
            </div>
        </div>
    </div>

    @php
    $cards = [
    ['title'=>'Active Employees','value'=>$dashboard['cards']['active_employees'] ?? 0,'icon'=>'fa-users','sub'=>'Current active workforce'],
    ['title'=>'Present Today','value'=>$dashboard['cards']['present_today'] ?? 0,'icon'=>'fa-user-check','sub'=>'Employees present today'],
    ['title'=>'Absent Today','value'=>$dashboard['cards']['absent_today'] ?? 0,'icon'=>'fa-user-times','sub'=>'Marked absent today'],
    ['title'=>'Late Employees','value'=>$dashboard['cards']['late_today'] ?? 0,'icon'=>'fa-clock','sub'=>'Late punch-ins today'],
    ['title'=>'Early Logout','value'=>$dashboard['cards']['early_logout'] ?? 0,'icon'=>'fa-sign-out-alt','sub'=>'Early exits today'],
    ['title'=>'Punch Blocked','value'=>$dashboard['cards']['punch_blocked'] ?? 0,'icon'=>'fa-ban','sub'=>'Auto blocked after 11:15'],
    ['title'=>'Pending Leaves','value'=>$dashboard['cards']['pending_leaves'] ?? 0,'icon'=>'fa-calendar-check','sub'=>'Awaiting HR approval'],
    ['title'=>'Pending Documents','value'=>$dashboard['cards']['pending_documents'] ?? 0,'icon'=>'fa-file-alt','sub'=>'Need verification'],
    ];
    @endphp

    <div class="stat-grid">
        @foreach($cards as $card)
        <div class="stat-card">
            <div class="stat-icon"><i class="fas {{ $card['icon'] }}"></i></div>
            <div class="stat-title">{{ $card['title'] }}</div>
            <div class="stat-value">{{ $card['value'] }}</div>
            <div class="stat-sub">{{ $card['sub'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="dash-grid">
        <div class="orb-card">
            <div class="orb-card-head">
                <div>
                    <h5>Punch-In Blocked Employees</h5>
                    <small>Employees auto-blocked after 11:15 AM because they did not punch in.</small>
                </div>
                <span class="badge-soft badge-danger-soft">{{ count($dashboard['tables']['blocked_employees'] ?? []) }} Blocked</span>
            </div>
            <div class="orb-card-body table-responsive">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Blocked At</th>
                            <th>Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dashboard['tables']['blocked_employees'] ?? [] as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['employee_name'] ?? 'N/A' }}</strong><br>
                                <small>{{ $row['employee_code'] ?? '-' }}</small>
                            </td>
                            <td>{{ $row['department_name'] ?? '-' }}</td>
                            <td>{{ ($row['auto_blocked_at'] ?? null) ? \Carbon\Carbon::parse($row['auto_blocked_at'])->format('h:i A') : '-' }}</td>
                            <td>{{ $row['block_reason'] ?? '-' }}</td>
                            <td><a href="{{ route('attendances.pending-approval') }}" class="badge-soft">Unlock</a></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No blocked employees today.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="orb-card">
            <div class="orb-card-head">
                <div>
                    <h5>Employee Lifecycle</h5>
                    <small>Current workforce stage split</small>
                </div>
            </div>
            <div class="orb-card-body">
                <div class="mini-card-grid">
                    <div class="stat-card">
                        <div class="stat-title">Interns</div>
                        <div class="stat-value">{{ $dashboard['lifecycle']['interns'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Probation</div>
                        <div class="stat-value">{{ $dashboard['lifecycle']['probation'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Permanent</div>
                        <div class="stat-value">{{ $dashboard['lifecycle']['permanent'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dash-grid">
        <div class="orb-card">
            <div class="orb-card-head">
                <div>
                    <h5>Pending Leave Requests</h5>
                    <small>Latest leave requests waiting for approval</small>
                </div>
                <a href="{{ route('leave-approvals.index') }}" class="badge-soft">View All</a>
            </div>
            <div class="orb-card-body table-responsive">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Date Range</th>
                            <th>Days</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dashboard['tables']['pending_leaves'] ?? [] as $leave)
                        <tr>
                            <td>{{ $leave['employee_name'] ?? '-' }}</td>
                            <td><span class="badge-soft">{{ $leave['leave_type'] ?? '-' }}</span></td>
                            <td>{{ $leave['start_date'] ?? '-' }} to {{ $leave['end_date'] ?? '-' }}</td>
                            <td>{{ $leave['deducted_days'] ?? '-' }}</td>
                            <td><a href="{{ route('leave-approvals.index') }}" class="badge-soft badge-success-soft">Review</a></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No pending leaves.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="orb-card">
            <div class="orb-card-head">
                <div>
                    <h5>Recent HR Activity</h5>
                    <small>Latest HRMS movement</small>
                </div>
            </div>
            <div class="orb-card-body">
                @forelse($dashboard['recent_activities'] as $activity)
                <div class="activity-item">
                    <span class="activity-dot"></span>
                    <div>
                        <strong>{{ $activity['title'] ?? '-' }}</strong><br>
                        <small class="text-muted">
                            {{ ($activity['created_at'] ?? null) ? \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() : '' }}
                        </small>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">No recent activity.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="dash-grid">
        <div class="orb-card">
            <div class="orb-card-head">
                <div>
                    <h5>Pending Profiles</h5>
                    <small>Employees waiting for profile completion or HR verification</small>
                </div>
            </div>
            <div class="orb-card-body table-responsive">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Employment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dashboard['tables']['pending_profiles'] ?? [] as $profile)
                        <tr>
                            <td>{{ $profile['employee_name'] ?? '-' }}</td>
                            <td>{{ $profile['employee_code'] ?? '-' }}</td>
                            <td>{{ ucfirst($profile['employment_type'] ?? '-') }}</td>
                            <td><span class="badge-soft badge-warning-soft">{{ ucfirst($profile['profile_status'] ?? '-') }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No pending profiles.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="orb-card">
            <div class="orb-card-head">
                <div>
                    <h5>Pending Documents</h5>
                    <small>Employee documents waiting for verification</small>
                </div>
            </div>
            <div class="orb-card-body table-responsive">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Document</th>
                            <th>Uploaded</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dashboard['tables']['pending_documents'] ?? [] as $doc)
                        <tr>
                            <td>{{ $doc['employee_name'] ?? '-' }}</td>
                            <td>{{ $doc['document_name'] ?? '-' }}</td>
                            <td>{{ ($doc['uploaded_at'] ?? null) ? \Carbon\Carbon::parse($doc['uploaded_at'])->format('d M Y') : '-' }}</td>
                            <td><span class="badge-soft badge-warning-soft">{{ ucfirst($doc['verification_status'] ?? '-') }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No pending documents.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection