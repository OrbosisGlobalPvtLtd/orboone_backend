@extends('layouts.panel', ['active' => 'team_attendance'])

@section('page_title', 'Team Attendance')

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
    max-width: 1600px;
    margin: 0 auto;
}

/* Signature Hero Header Banner */
.rep-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 22px 26px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.rep-hero h3 {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 4px 0;
    color: #ffffff;
}

.rep-hero p {
    font-size: 13px;
    opacity: 0.92;
    margin: 0;
}

/* Rich Summary Cards Grid */
.team-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}

.team-stat-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    padding: 16px 18px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.team-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
}

.team-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.team-stat-val {
    font-size: 22px;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.1;
}

.team-stat-label {
    font-size: 10.5px;
    font-weight: 800;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 2px;
}

/* Main Table Container Card */
.rep-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.filter-control-sm {
    height: 36px;
    border-radius: 9px;
    font-size: 12.5px;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    padding: 4px 10px;
    outline: none;
}

/* Sticky Table Header */
.table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #F8FAFC !important;
    color: #475569 !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.35px !important;
    border-bottom: 2px solid #E2E8F0 !important;
    white-space: nowrap !important;
    padding: 11px 14px !important;
}

.table tbody td {
    padding: 11px 14px !important;
    border-bottom: 1px solid #F1F5F8 !important;
    vertical-align: middle !important;
    font-size: 12.5px !important;
}

.table tbody tr:hover {
    background: #F8FAFC !important;
}

/* 3-Dot Action Button */
.btn-action-dots {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #F1F5F9;
    color: #475569;
    border: 1px solid #CBD5E1;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-action-dots:hover,
.btn-action-dots:focus {
    background: #EEF2FF;
    color: var(--orb-primary);
    border-color: #C7D2FE;
}

.dropdown-menu-action {
    min-width: 165px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
    border: 1px solid #E2E8F0;
    padding: 6px;
}

.dropdown-menu-action .dropdown-item {
    font-size: 12px;
    font-weight: 600;
    padding: 7px 12px;
    border-radius: 8px;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dropdown-menu-action .dropdown-item:hover {
    background: #EEF2FF;
    color: var(--orb-primary);
}
</style>
@endsection

@section('_content')
<div class="rep-page">
    <div class="rep-container">
        <!-- Hero Header Banner -->
        <div class="rep-hero">
            <div>
                <h3 class="text-white font-weight-bold mb-1"><i class="fas fa-calendar-check mr-2"></i>Team Attendance</h3>
                <p class="mb-0 opacity-90 small">Monitor attendance, working hours and work status of your team.</p>
            </div>
            <form method="GET" action="{{ route('reporting.attendance') }}" class="form-inline flex-wrap gap-2">
                <!-- Team Member Filter -->
                <select name="employee_id" class="filter-control-sm mr-2 mb-2" style="min-width: 180px;">
                    <option value="">Team Member</option>
                    @foreach($teamEmployees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->display_name }} ({{ $emp->employee_code }})
                        </option>
                    @endforeach
                </select>

                <!-- Date Filter -->
                <input type="date" name="date" class="filter-control-sm mr-2 mb-2" value="{{ $date }}">
                
                <!-- Action Buttons -->
                <button type="submit" class="btn btn-sm btn-light font-weight-bold mr-2 mb-2" style="height: 36px; border-radius: 9px; color: var(--orb-primary);">
                    <i class="fas fa-filter mr-1"></i> Filter Date
                </button>
                <a href="{{ route('reporting.attendance') }}" class="btn btn-sm btn-outline-light font-weight-bold mb-2" style="height: 36px; border-radius: 9px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-undo" style="font-size: 11px;"></i> Reset
                </a>
            </form>
        </div>

        <!-- Rich Summary Cards Grid -->
        <div class="team-stats-grid">
            <!-- Total Team Members -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE;">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="team-stat-label">Total Team</div>
                    <div class="team-stat-val">{{ $totalTeamCount }}</div>
                </div>
            </div>

            <!-- Present Today -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="team-stat-label">Present Today</div>
                    <div class="team-stat-val">{{ $presentCount }}</div>
                </div>
            </div>

            <!-- On Leave -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;">
                    <i class="fas fa-plane-departure"></i>
                </div>
                <div>
                    <div class="team-stat-label">On Leave</div>
                    <div class="team-stat-val">{{ $onLeaveCount }}</div>
                </div>
            </div>

            <!-- Not Punched In -->
            <div class="team-stat-card">
                <div class="team-stat-icon" style="background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="team-stat-label">Not Punched In</div>
                    <div class="team-stat-val">{{ $notPunchedCount }}</div>
                </div>
            </div>
        </div>

        <div class="rep-card">
            <!-- Card Header Title -->
            <div class="d-flex align-items-center justify-content-between border-bottom bg-white flex-wrap" style="padding: 14px 20px;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <span style="width: 34px; height: 34px; border-radius: 9px; background: #EEF2FF; color: #4F46E5; display: inline-flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="fas fa-calendar-check"></i>
                    </span>
                    <div>
                        <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 15px;">Team Attendance Records</h5>
                    </div>
                </div>
                <div>
                    <span class="badge badge-light border text-primary font-weight-bold px-3 py-1.5" style="border-radius: 8px; font-size: 12px;">
                        <i class="fas fa-calendar-day mr-1"></i> {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 45px;">#</th>
                            <th class="py-3 px-4">Employee</th>
                            <th class="py-3">Organization</th>
                            <th class="py-3 text-center">Punch In</th>
                            <th class="py-3 text-center">Punch Out</th>
                            <th class="py-3 text-center">Working Hours</th>
                            <th class="py-3 text-center">Work Mode</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-center" style="width: 60px;">⋮</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employeesPaginator as $emp)
                        @php
                            $att = $emp->attendance_record;
                            $leave = $emp->leave_record;
                            $displayName = $emp->display_name ?? optional($emp->user)->name ?? 'Employee';
                            $empCode = $emp->employee_code ?? 'N/A';
                            $deptName = optional($emp->department)->name ?? 'General';
                            $desigName = optional($emp->designation)->name ?? 'Staff';
                        @endphp
                        <tr>
                            <!-- 1. S.No. -->
                            <td class="py-3 px-3 align-middle text-center font-weight-bold text-muted" style="font-size: 12px;">
                                {{ $loop->iteration + ($employeesPaginator->currentPage() - 1) * $employeesPaginator->perPage() }}
                            </td>

                            <!-- 2. Employee Column (No Icon Circle) -->
                            <td class="py-3 px-4 align-middle">
                                <div>
                                    @if(Route::has('employees.show'))
                                        <a href="{{ route('employees.show', $emp->id) }}" class="text-dark font-weight-bold d-block text-hover-primary" style="line-height: 1.25; font-size: 13px;">
                                            {{ $displayName }}
                                        </a>
                                    @else
                                        <strong class="text-dark font-weight-bold d-block" style="line-height: 1.25; font-size: 13px;">{{ $displayName }}</strong>
                                    @endif
                                    <small class="text-muted font-weight-bold" style="font-size: 10.5px;">{{ $empCode }}</small>
                                </div>
                            </td>

                            <!-- 3. Organization Column -->
                            <td class="py-3 align-middle">
                                <div>
                                    <span class="font-weight-bold text-dark d-block" style="font-size: 12.5px; line-height: 1.25;">
                                        {{ $deptName }}
                                    </span>
                                    <small class="text-muted d-block" style="font-size: 11px; font-weight: 600;">{{ $desigName }}</small>
                                </div>
                            </td>

                            <!-- 4. Punch In -->
                            <td class="py-3 align-middle text-center font-weight-bold text-dark" style="font-size: 12px;">
                                @if($att && $att->punch_in_time)
                                    {{ \Carbon\Carbon::parse($att->punch_in_time)->format('h:i A') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <!-- 5. Punch Out -->
                            <td class="py-3 align-middle text-center font-weight-bold text-dark" style="font-size: 12px;">
                                @if($att && $att->punch_out_time)
                                    {{ \Carbon\Carbon::parse($att->punch_out_time)->format('h:i A') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <!-- 6. Working Hours -->
                            <td class="py-3 align-middle text-center font-weight-bold" style="font-size: 12px;">
                                @if($att && $att->punch_in_time && $att->punch_out_time)
                                    @php
                                        $in = \Carbon\Carbon::parse($att->punch_in_time);
                                        $out = \Carbon\Carbon::parse($att->punch_out_time);
                                        $diffMinutes = $in->diffInMinutes($out);
                                        $hours = floor($diffMinutes / 60);
                                        $mins = $diffMinutes % 60;
                                    @endphp
                                    <span class="text-primary">{{ $hours }}h {{ $mins }}m</span>
                                @elseif($att && $att->punch_in_time)
                                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-0.5" style="border-radius: 6px; font-size: 10px; background: #FEF3C7; border: 1px solid #FCD34D;">
                                        Active Session
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <!-- 7. Work Mode -->
                            <td class="py-3 align-middle text-center">
                                @if($att)
                                    @php
                                        $wmRaw = strtolower($att->work_mode ?? $att->work_type ?? '');
                                    @endphp
                                    @if(in_array($wmRaw, ['wfo', 'office']))
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 6px; font-size: 10px; background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE;">
                                            WFO
                                        </span>
                                    @elseif(in_array($wmRaw, ['wfh', 'home', 'remote']))
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 6px; font-size: 10px; background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;">
                                            WFH
                                        </span>
                                    @elseif($wmRaw === 'hybrid')
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 6px; font-size: 10px; background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD;">
                                            Hybrid
                                        </span>
                                    @elseif(!empty($wmRaw))
                                        <span class="badge badge-light border text-dark font-weight-bold px-2.5 py-1" style="border-radius: 6px; font-size: 10px;">
                                            {{ strtoupper($wmRaw) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <!-- 8. Status -->
                            <td class="py-3 align-middle text-center">
                                @if($leave)
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                                        ● On Leave
                                    </span>
                                @elseif($att)
                                    @php
                                        $stRaw = strtolower($att->attendance_status ?? $att->status ?? 'present');
                                    @endphp
                                    @if($stRaw === 'present')
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                            ● Present
                                        </span>
                                    @elseif($stRaw === 'half_day')
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;">
                                            ● Half Day
                                        </span>
                                    @elseif(in_array($stRaw, ['absent', 'missed_punch', 'lwp']))
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">
                                            ● {{ ucfirst(str_replace('_', ' ', $stRaw)) }}
                                        </span>
                                    @else
                                        <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;">
                                            ● {{ ucfirst(str_replace('_', ' ', $stRaw)) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="badge font-weight-bold px-2.5 py-1" style="border-radius: 7px; font-size: 10.5px; background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5;">
                                        ● Not Punched In
                                    </span>
                                @endif
                            </td>

                            <!-- 9. Actions Three-Dot Column (⋮) -->
                            <td class="py-3 align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn-action-dots" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-action">
                                        @if(Route::has('employees.show'))
                                            <a class="dropdown-item" href="{{ route('employees.show', $emp->id) }}">
                                                <i class="fas fa-user-circle text-primary"></i> View Employee Profile
                                            </a>
                                        @endif
                                        @if(Route::has('reporting.my_employees'))
                                            <a class="dropdown-item" href="{{ route('reporting.my_employees') }}">
                                                <i class="fas fa-users text-info"></i> View Team Workspace
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                                <h5 class="font-weight-bold text-dark">No Attendance Records Found</h5>
                                <p class="small mb-0">No team attendance records found for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employeesPaginator->hasPages())
                <div class="p-3 bg-light border-top">
                    {{ $employeesPaginator->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
