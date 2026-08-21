@extends('layouts.panel', ['active' => 'reporting_dashboard'])

@section('page_title', 'Team Dashboard')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.rep-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.rep-container {
    max-width: 1550px;
    margin: 0 auto;
}

.rep-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 32px 36px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.rep-metric-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--orb-shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    height: 100%;
}

.rep-metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: rgba(75, 0, 232, 0.08);
    color: var(--orb-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.rep-section-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.rep-section-header {
    padding: 18px 24px;
    background: #FAFAFC;
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Header -->
        <div class="rep-hero">
            <div>
                <h1 class="text-white font-weight-bold mb-1"><i class="fas fa-tachometer-alt mr-2"></i>Team Dashboard</h1>
                <p class="mb-0 opacity-90">Daily operational status, attendance, and task monitoring for your team members.</p>
            </div>
            <div>
                <a href="{{ route('reporting.my_employees') }}" class="btn btn-light font-weight-bold px-4 py-2" style="border-radius: 12px; color: var(--orb-primary);">
                    <i class="fas fa-users mr-2"></i>My Team
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row mb-3">
            <div class="col-6 col-md-4 col-lg-2 mb-3">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Total Team Members</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $employeesCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 mb-3">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;"><i class="fas fa-user-shield"></i></div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Reporting Managers</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $supervisorsCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 mb-3">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;"><i class="fas fa-user-check"></i></div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Present Today</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $presentCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 mb-3">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;"><i class="fas fa-home"></i></div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">WFH Today</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $wfhCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 mb-3">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;"><i class="fas fa-user-times"></i></div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">On Leave / Absent</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $onLeaveCount + $absentCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 mb-3">
                <div class="rep-metric-card">
                    <div class="rep-metric-icon" style="background: rgba(168, 85, 247, 0.1); color: #A855F7;"><i class="fas fa-file-invoice"></i></div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 10px;">Daily Work Reports</div>
                        <div class="h4 font-weight-extrabold mb-0 text-dark">{{ $workReportsSubmittedToday }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <div class="col-lg-8">
                <div class="rep-section-card">
                    <div class="rep-section-header">
                        <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-user-clock mr-2 text-primary"></i>Today's Employee Status</h5>
                        <a href="{{ route('reporting.my_employees') }}" class="small font-weight-bold" style="color: var(--orb-primary);">View All &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 px-4">Employee</th>
                                    <th class="py-3">Attendance</th>
                                    <th class="py-3">Leave</th>
                                    <th class="py-3">Work Report</th>
                                    <th class="py-3">Task Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDevelopers as $item)
                                <tr>
                                    <td class="py-3 px-4 align-middle">
                                        <strong class="text-dark font-weight-bold d-block">{{ $item['employee']->display_name }}</strong>
                                        <small class="text-muted">{{ $item['employee']->employee_code }} &bull; {{ optional($item['employee']->designation)->name ?? 'Employee' }}</small>
                                    </td>
                                     <td class="py-3 align-middle">
                                         @if($item['leave'])
                                             <span class="badge badge-warning px-3 py-1 font-weight-bold" style="border-radius: 8px; background-color: #FEF3C7; color: #D97706; border: 1px solid #FCD34D;">
                                                 ON LEAVE
                                             </span>
                                         @elseif($item['attendance'])
                                             <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 8px;">
                                                 {{ strtoupper($item['attendance']->work_type ?? 'Present') }}
                                             </span>
                                         @else
                                             <span class="badge badge-light border text-muted px-2.5 py-1" style="border-radius: 8px;">Not Punched In</span>
                                         @endif
                                     </td>
                                    <td class="py-3 align-middle">
                                        @if($item['leave'])
                                            <span class="badge badge-warning px-2.5 py-1 font-weight-bold" style="border-radius: 8px;">On Leave</span>
                                        @else
                                            <span class="small text-muted">No Leave</span>
                                        @endif
                                    </td>
                                    <td class="py-3 align-middle">
                                        @if($item['work_log'])
                                            <span class="badge badge-info px-2.5 py-1 font-weight-bold" style="border-radius: 8px;"><i class="fas fa-check-circle mr-1"></i> Submitted</span>
                                        @else
                                            <span class="badge badge-light border text-muted px-2.5 py-1" style="border-radius: 8px;">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-3 align-middle">
                                        @if($item['total_tasks'] > 0)
                                            <span class="small font-weight-bold text-dark">{{ $item['completed_tasks'] }}/{{ $item['total_tasks'] }} Tasks</span>
                                            <div class="progress mt-1" style="height: 6px; border-radius: 4px; background: #E2E8F0;">
                                                <div class="progress-bar bg-success" style="width: {{ round(($item['completed_tasks'] / $item['total_tasks']) * 100) }}%;"></div>
                                            </div>
                                        @else
                                            <span class="small text-muted">No Tasks</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">No active reporting employees found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Task Breakdown -->
            <div class="col-lg-4">
                <div class="rep-section-card">
                    <div class="rep-section-header">
                        <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-tasks mr-2 text-primary"></i>Reporting Tasks Breakdown</h5>
                    </div>
                    <div class="p-4">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small font-weight-bold">TOTAL TASKS</span>
                            <strong class="text-dark h5 mb-0">{{ $taskStats['total'] }}</strong>
                        </div>
                        <hr class="my-3">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-check-circle text-success mr-2"></i>Completed</span>
                            <span class="badge badge-success font-weight-bold px-3 py-1" style="border-radius: 8px;">{{ $taskStats['completed'] }}</span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-spinner text-info mr-2"></i>In Progress</span>
                            <span class="badge badge-info font-weight-bold px-3 py-1" style="border-radius: 8px;">{{ $taskStats['in_progress'] }}</span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-list text-secondary mr-2"></i>To Do</span>
                            <span class="badge badge-secondary font-weight-bold px-3 py-1" style="border-radius: 8px;">{{ $taskStats['todo'] }}</span>
                        </div>
                        <div class="mb-0 d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Blocked</span>
                            <span class="badge badge-danger font-weight-bold px-3 py-1" style="border-radius: 8px;">{{ $taskStats['blocked'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
