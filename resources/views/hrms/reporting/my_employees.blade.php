@extends('layouts.panel', ['active' => 'team_my_team'])

@section('page_title', 'My Team')

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
    padding: 20px 24px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
}

.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <div class="rep-hero" style="padding: 20px 24px;">
            <h3 class="text-white font-weight-bold mb-1" style="font-size: 22px;"><i class="fas fa-users mr-2"></i>My Team</h3>
            <p class="mb-0 opacity-90 small">Unified operational workspace for team members under your supervision (Project Team Members & Reporting Employees).</p>
        </div>

        <div class="rep-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Employee</th>
                            <th class="py-3">Department & Designation</th>
                            <th class="py-3">Reporting Manager</th>
                            <th class="py-3 text-center">Team Source</th>
                            <th class="py-3">Current Projects & Teams</th>
                            <th class="py-3 text-center">Attendance Today</th>
                            <th class="py-3 text-center">Leave Status</th>
                            <th class="py-3 text-center">Work Report</th>
                            <th class="py-3">Tasks & Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                        <tr>
                            <!-- Employee Details -->
                            <td class="py-3 px-4 align-middle">
                                <strong class="text-dark font-weight-bold d-block">{{ $emp->display_name }}</strong>
                                <small class="text-muted">{{ $emp->employee_code }}</small>
                            </td>

                            <!-- Department & Designation -->
                            <td class="py-3 align-middle">
                                <span class="badge badge-light border text-primary font-weight-bold px-2 py-0.5" style="border-radius: 6px;">{{ optional($emp->department)->name ?? 'General' }}</span>
                                <small class="text-muted d-block mt-0.5">{{ optional($emp->designation)->name ?? 'Employee' }}</small>
                            </td>

                            <!-- Reporting Manager -->
                            <td class="py-3 align-middle">
                                @if($emp->reportingManager)
                                    <strong class="text-dark small d-block"><i class="fas fa-user-shield text-info mr-1"></i>{{ $emp->reportingManager->display_name }}</strong>
                                    <small class="text-muted">{{ $emp->reportingManager->employee_code }}</small>
                                @else
                                    <span class="small text-muted">—</span>
                                @endif
                            </td>

                            <!-- Team Source -->
                            <td class="py-3 align-middle text-center">
                                @if(($emp->team_source ?? '') === 'Both')
                                    <span class="badge badge-purple bg-gradient-purple text-white px-2.5 py-1 font-weight-bold" style="border-radius: 6px; background: linear-gradient(135deg, #7C3AED, #C084FC);"><i class="fas fa-layer-group mr-1"></i>Both</span>
                                @elseif(($emp->team_source ?? '') === 'Reporting Team')
                                    <span class="badge badge-info px-2.5 py-1 font-weight-bold" style="border-radius: 6px;"><i class="fas fa-user-check mr-1"></i>Reporting Team</span>
                                @else
                                    <span class="badge badge-primary px-2.5 py-1 font-weight-bold" style="border-radius: 6px;"><i class="fas fa-project-diagram mr-1"></i>Project Team</span>
                                @endif
                            </td>

                            <!-- Projects & Teams -->
                            <td class="py-3 align-middle">
                                @forelse($emp->project_assignments_list as $prj)
                                    <div class="mb-1">
                                        <strong class="text-dark small d-block"><i class="fas fa-folder text-primary mr-1"></i>{{ $prj->project_name }}</strong>
                                        <small class="text-muted">
                                            @if($prj->team_name)<span class="badge badge-light border">{{ $prj->team_name }}</span>@endif
                                            @if($prj->role_name)<span class="text-info font-weight-bold ml-1">({{ $prj->role_name }})</span>@endif
                                        </small>
                                    </div>
                                @empty
                                    <span class="small text-muted">No Active Projects</span>
                                @endforelse
                            </td>

                            <!-- Attendance Today -->
                            <td class="py-3 align-middle text-center">
                                @if($emp->leave_today)
                                    <span class="badge badge-warning px-3 py-1 font-weight-bold" style="border-radius: 8px; background-color: #FEF3C7; color: #D97706; border: 1px solid #FCD34D;">
                                        ON LEAVE
                                    </span>
                                    @if($emp->attendance_today && $emp->attendance_today->punch_in_time)
                                        <small class="d-block text-muted mt-1">Punched: {{ \Carbon\Carbon::parse($emp->attendance_today->punch_in_time)->format('h:i A') }}</small>
                                    @endif
                                @elseif($emp->attendance_today)
                                    <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 8px;">
                                        {{ strtoupper($emp->attendance_today->work_type ?? 'Present') }}
                                    </span>
                                    <small class="d-block text-muted mt-1">{{ \Carbon\Carbon::parse($emp->attendance_today->punch_in_time)->format('h:i A') }}</small>
                                @else
                                    <span class="badge badge-light border text-muted px-2.5 py-1" style="border-radius: 8px;">Not Punched In</span>
                                @endif
                            </td>

                            <!-- Leave Status -->
                            <td class="py-3 align-middle text-center">
                                @if($emp->leave_today)
                                    <span class="badge badge-warning px-2.5 py-1 font-weight-bold" style="border-radius: 8px;">On Leave</span>
                                @else
                                    <span class="small text-muted font-weight-bold">No Leave</span>
                                @endif
                            </td>

                            <!-- Work Report Status -->
                            <td class="py-3 align-middle text-center">
                                @if($emp->work_report_today)
                                    <span class="badge badge-info px-2.5 py-1 font-weight-bold" style="border-radius: 8px;"><i class="fas fa-check-circle mr-1"></i> Submitted</span>
                                @else
                                    <span class="badge badge-light border text-muted px-2.5 py-1" style="border-radius: 8px;">Pending</span>
                                @endif
                            </td>

                            <!-- Active Task & Progress -->
                            <td class="py-3 align-middle" style="min-width: 180px;">
                                @if($emp->active_task)
                                    <strong class="text-dark small d-block text-truncate" style="max-width: 180px;">{{ $emp->active_task->title }}</strong>
                                    <div class="d-flex align-items-center mt-1">
                                        <div class="progress flex-grow-1 mr-2" style="height: 6px; border-radius: 4px; background: #E2E8F0;">
                                            <div class="progress-bar bg-primary" style="width: {{ $emp->active_task->progress_percentage ?? 0 }}%;"></div>
                                        </div>
                                        <small class="font-weight-bold text-dark">{{ $emp->active_task->progress_percentage ?? 0 }}%</small>
                                    </div>
                                @elseif($emp->total_tasks_count > 0)
                                    <span class="small font-weight-bold text-dark">{{ $emp->completed_tasks_count }}/{{ $emp->total_tasks_count }} Tasks Done</span>
                                @else
                                    <span class="small text-muted">No Active Tasks</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Team Members Currently Assigned</h5>
                                <p class="small mb-0">Employees belonging to your project team or reporting scope will appear here.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
