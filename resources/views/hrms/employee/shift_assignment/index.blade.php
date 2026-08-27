@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Shift Assignment')

@section('_content')
@include('hrms.employee.partials.styles')

<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .shift-assignment-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    /* Premium Purple Gradient Hero Header */
    .report-header-premium {
        background: linear-gradient(135deg, var(--orb-primary, #6366F1) 0%, var(--orb-secondary, #4F46E5) 100%) !important;
        border-radius: 20px !important;
        padding: 24px 32px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 12px 30px rgba(99, 102, 241, 0.18) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
        border: none !important;
    }

    .report-header-premium::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 300px !important;
        height: 300px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border-radius: 50% !important;
        filter: blur(40px) !important;
        pointer-events: none !important;
    }

    .report-header-premium .title-area h3 {
        font-size: 24px !important;
        font-weight: 800 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .report-header-premium .title-area p {
        font-size: 13.5px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 4px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .report-header-premium .header-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.15em !important;
        color: rgba(255, 255, 255, 0.8) !important;
        margin-bottom: 6px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    /* Premium Pill Button */
    .report-btn-pill {
        height: 40px !important;
        padding: 0 20px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        cursor: pointer !important;
        text-decoration: none !important;
        outline: none !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        background: rgba(255, 255, 255, 0.2) !important;
        color: #fff !important;
    }

    .report-btn-pill:hover {
        background: rgba(255, 255, 255, 0.35) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    /* Table card styling */
    .orb-table-card {
        background: #fff !important;
        border-radius: 20px !important;
        border: 1px solid #E7EAF3 !important;
        box-shadow: 0 14px 35px rgba(16, 24, 40, .06) !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
    }

    .report-filters-attached {
        background: #FAFAFC !important;
        border-bottom: 1px solid #EEF2F7 !important;
        padding: 16px 24px !important;
    }

    .report-filter-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
        gap: 14px !important;
    }

    .report-filter-grid label {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #667085 !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .report-filter-grid select,
    .report-filter-grid input {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid #E2E8F0 !important;
        font-size: 13px !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    /* Modal Form Field Labels & Time Overlay */
    .shift-modal-card-title {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        color: #667085 !important;
        margin-bottom: 14px !important;
    }

    .shift-modal-label {
        font-weight: 700 !important;
        font-size: 13px !important;
        color: #101828 !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .time-picker-container {
        position: relative !important;
        width: 100% !important;
    }
    .native-time-input {
        color: transparent !important;
        caret-color: transparent !important;
        background: transparent !important;
    }
    .native-time-input::-webkit-calendar-picker-indicator {
        cursor: pointer !important;
        opacity: 1 !important;
    }
    .time-display-val {
        position: absolute !important;
        left: 14px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        pointer-events: none !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: #101828 !important;
    }
</style>

<div class="shift-assignment-page">
    <div class="report-container">

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show rounded-12 shadow-sm mb-4" role="alert" style="border-left: 5px solid #10B981;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-12 shadow-sm mb-4" role="alert" style="border-left: 5px solid #EF4444;">
                <i class="fas fa-exclamation-triangle mr-2"></i> <strong>Validation Error:</strong>
                <ul class="mb-0 mt-1 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Sleek Premium Hero Header -->
        <div class="report-header-premium">
            <div class="title-area">
                <div class="header-kicker">
                    <i class="fas fa-users-cog"></i> EMPLOYEE MANAGEMENT
                </div>
                <h3>Employee Shift Assignments</h3>
                <p>Manage employee shift mappings, custom shift windows, and active effective date ranges.</p>
            </div>
            <div>
                <button type="button" class="report-btn-pill" data-toggle="modal" data-target="#assignShiftModal">
                    <i class="fas fa-plus"></i> Assign Shift
                </button>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="card orb-table-card">

            <div class="orb-table-card-header d-flex align-items-center justify-content-between" style="padding: 20px 24px 16px; border-bottom: 1px solid #EEF2F7; background: #fff; flex-wrap: wrap; gap: 16px;">
                <div class="orb-title-wrap d-flex align-items-center" style="gap: 14px;">
                    <span class="orb-card-icon" style="width: 42px; height: 42px; border-radius: 12px; background: #F4F2FF; color: var(--orb-primary, #6366F1); display: inline-flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fas fa-business-time"></i>
                    </span>
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #101828;">Shift Assignments List</h3>
                        <p style="margin: 3px 0 0 0; font-size: 12.5px; color: #667085;">View and manage assigned shift timings, custom flexible windows, and effective ranges.</p>
                    </div>
                </div>

                <!-- Reset Filters Button in Card Header -->
                <a href="{{ route('employee.shift-assignment.index') }}" class="btn btn-undo btn-outline-secondary btn-sm d-flex align-items-center" style="height: 38px !important; border-radius: 10px !important; padding: 0 16px !important; font-size: 12.5px !important; font-weight: 700 !important; border: 1px solid #e2e8f0 !important; color: #475467 !important; background: #fff !important; transition: all 0.2s ease !important; text-decoration: none;">
                    <i class="fas fa-undo mr-2" style="font-size: 11px;"></i> Reset Filters
                </a>
            </div>

            <!-- Auto-Submitting Filter Grid Bar -->
            <div class="report-filters-attached">
                <form id="shiftFilterForm" method="GET" action="{{ route('employee.shift-assignment.index') }}">
                    <div class="report-filter-grid">

                        <div>
                            <label>Employee</label>
                            <select name="employee_id" class="form-control" onchange="this.form.submit()">
                                <option value="">All Staff</option>
                                @foreach($allEmployeesList as $empOption)
                                    <option value="{{ $empOption->id }}" {{ request('employee_id') == $empOption->id ? 'selected' : '' }}>
                                        {{ optional($empOption->user)->name ?? 'Employee #' . $empOption->id }} ({{ $empOption->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Department</label>
                            <select name="department_id" class="form-control" onchange="this.form.submit()">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Search Keyword</label>
                            <input type="text" name="search" class="form-control" placeholder="Search code or name..." value="{{ request('search') }}" onchange="this.form.submit()">
                        </div>

                    </div>
                </form>
            </div>

            <!-- Table Container -->
            <div class="table-responsive">
                <table class="report-table table mb-0" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th class="py-3 px-4">Employee</th>
                            <th class="py-3">Dept & Designation</th>
                            <th class="py-3">Current Shift</th>
                            <th class="py-3">Shift Timing</th>
                            <th class="py-3">Punch From</th>
                            <th class="py-3">Late After</th>
                            <th class="py-3">Blocked Punch</th>
                            <th class="py-3">Req. Work</th>
                            <th class="py-3">Effective From</th>
                            <th class="py-3">Effective Till</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                        @php
                            $shiftAssignment = $emp->currentShiftTiming;
                            $assignedShift = optional($shiftAssignment)->attendanceTime ?? $defaultShift;
                            $isCustomAssignment = !is_null($shiftAssignment);
                            
                            $startTime    = optional($shiftAssignment)->shift_start_time ?? optional($assignedShift)->shift_start_time;
                            $endTime      = optional($shiftAssignment)->shift_end_time ?? optional($assignedShift)->shift_end_time;
                            $punchFrom    = optional($shiftAssignment)->punch_allowed_from ?? optional($assignedShift)->punch_allowed_from;
                            $lateAfter    = optional($shiftAssignment)->late_after_time ?? optional($assignedShift)->late_after_time;
                            $blockedPunch = optional($shiftAssignment)->block_after_time ?? optional($assignedShift)->block_after_time ?? optional($shiftAssignment)->half_day_after_time;
                            $reqMins      = optional($shiftAssignment)->required_work_minutes ?? optional($assignedShift)->required_work_minutes;

                            $passportPhotoUrl = resolveEmployeePassportPhoto($emp);
                            $employeeInitial = resolveEmployeeInitials($emp);
                            $employeeName = optional($emp->user)->name ?? 'Employee #' . $emp->id;
                        @endphp
                        <tr>
                            <td class="py-3 px-4">
                                <div class="att-emp">
                                    <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                        @if($passportPhotoUrl)
                                            <img src="{{ $passportPhotoUrl }}" alt="{{ $employeeName }}" class="hrms-emp-avatar-img" onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');">
                                            <span class="hrms-emp-avatar-fallback is-hidden">{{ $employeeInitial }}</span>
                                        @else
                                            <span class="hrms-emp-avatar-fallback is-visible">{{ $employeeInitial }}</span>
                                        @endif
                                    </span>
                                    <div>
                                        <div class="att-emp-name">{{ $employeeName }}</div>
                                        <div class="att-emp-code">{{ $emp->employee_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 align-middle">
                                <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ optional($emp->department)->name ?? 'General HRMS' }}</div>
                                <div class="text-muted small" style="font-size: 11.5px;">{{ optional($emp->designation)->name ?? 'Staff Member' }}</div>
                            </td>
                            <td class="py-3 align-middle">
                                <span class="badge px-3 py-1 font-weight-bold" style="border-radius: 12px; {{ $isCustomAssignment ? 'background: #F4F2FF; color: var(--orb-primary, #6366F1); border: 1px solid rgba(99, 102, 241, 0.2);' : 'background: #F3F4F6; color: #4B5563; border: 1px solid #E5E7EB;' }}">
                                    <i class="fas {{ $isCustomAssignment ? 'fa-user-clock' : 'fa-cog' }} mr-1"></i>
                                    {{ optional($assignedShift)->name ?? 'Default Shift' }}
                                </span>
                            </td>
                            <td class="py-3 align-middle">
                                @if($startTime && $endTime)
                                    <span class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                        {{ \Carbon\Carbon::parse($startTime)->format('h:i A') }} - {{ \Carbon\Carbon::parse($endTime)->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="border-radius: 8px; font-size: 11px;">Flexible Timing</span>
                                @endif
                            </td>
                            <td class="py-3 align-middle font-weight-semibold text-dark">
                                {{ $punchFrom ? \Carbon\Carbon::parse($punchFrom)->format('h:i A') : 'Anytime' }}
                            </td>
                            <td class="py-3 align-middle font-weight-semibold text-dark">
                                {{ $lateAfter ? \Carbon\Carbon::parse($lateAfter)->format('h:i A') : '-' }}
                            </td>
                            <td class="py-3 align-middle font-weight-semibold text-dark">
                                {{ $blockedPunch ? \Carbon\Carbon::parse($blockedPunch)->format('h:i A') : '-' }}
                            </td>
                            <td class="py-3 align-middle font-weight-bold text-dark">
                                {{ $reqMins ? $reqMins . ' mins' : '-' }}
                            </td>
                            <td class="py-3 align-middle text-dark font-weight-semibold">
                                @if($shiftAssignment && $shiftAssignment->effective_from)
                                    {{ \Carbon\Carbon::parse($shiftAssignment->effective_from)->format('d M Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="py-3 align-middle text-dark font-weight-semibold">
                                @if($shiftAssignment && $shiftAssignment->effective_to)
                                    {{ \Carbon\Carbon::parse($shiftAssignment->effective_to)->format('d M Y') }}
                                @else
                                    <span class="text-muted">Ongoing</span>
                                @endif
                            </td>
                            <td class="py-3 align-middle">
                                @if($shiftAssignment)
                                    <span class="badge {{ $shiftAssignment->is_active ? 'badge-success' : 'badge-secondary' }} px-3 py-1 font-weight-bold" style="border-radius: 12px; font-size: 11px;">
                                        {{ $shiftAssignment->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                @else
                                    <span class="badge badge-light border text-muted px-3 py-1 font-weight-bold" style="border-radius: 12px; font-size: 11px;">Default</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right align-middle">
                                @if($shiftAssignment)
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 font-weight-bold" style="font-size: 12px; border-radius: 20px;" data-toggle="modal" data-target="#editShiftModal{{ $shiftAssignment->id }}">
                                        <i class="fas fa-edit mr-1"></i> 
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 font-weight-bold" style="font-size: 12px; border-radius: 20px; background: linear-gradient(135deg, var(--orb-primary, #6366F1), var(--orb-secondary, #4F46E5));" data-toggle="modal" data-target="#assignShiftModal" onclick="selectEmployeeForShift({{ $emp->id }})">
                                        <i class="fas fa-plus mr-1"></i> Assign Shift
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fa-2x mb-3 d-block opacity-50"></i>
                                No employees found matching the specified filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="text-muted small">Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} employees</div>
                <div>{{ $employees->links() }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Shift Modal -->
<div class="modal fade" id="assignShiftModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('employee.shift-assignment.store') }}" class="modal-content border-0 rounded-24 shadow-lg overflow-hidden">
            @csrf
            <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, var(--orb-primary, #6366F1), var(--orb-secondary, #4F46E5));">
                <div>
                    <h5 class="modal-title font-weight-bold text-white mb-1"><i class="fas fa-user-plus mr-2"></i> Assign Employee Shift</h5>
                    <div class="text-white-50 small">Map a shift template and active date boundaries for an employee</div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background: #F9FAFB;">
                <div class="card border-0 rounded-16 shadow-2xs p-4 mb-3 bg-white">
                    <h6 class="shift-modal-card-title"><i class="fas fa-user mr-1 text-primary"></i> Employee & Shift Selection</h6>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" id="assignEmployeeSelect" class="form-control rounded-12 border-light bg-light" required style="height: 42px; font-size: 13px;">
                                <option value="">-- Select Employee --</option>
                                @foreach($allEmployeesList as $empOption)
                                    <option value="{{ $empOption->id }}">{{ $empOption->employee_code }} - {{ optional($empOption->user)->name ?? 'Employee #' . $empOption->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Select Shift Template <span class="text-danger">*</span></label>
                            <select name="attendance_time_id" id="assignShiftSelect" class="form-control rounded-12 border-light bg-light" required style="height: 42px; font-size: 13px;" onchange="handleShiftTemplateSelect('assign', this)">
                                <option value="">-- Select Shift --</option>
                                @foreach($attendanceTimes as $timeOption)
                                    @php
                                        $isFlex = in_array(strtolower($timeOption->shift_type ?? ''), ['flexible', 'flexible_part_time']) || stripos($timeOption->name, 'flexible') !== false;
                                    @endphp
                                    <option value="{{ $timeOption->id }}"
                                        data-flexible="{{ $isFlex ? '1' : '0' }}"
                                        data-punch-allowed="{{ $timeOption->punch_allowed_from ? \Carbon\Carbon::parse($timeOption->punch_allowed_from)->format('H:i') : '' }}"
                                        data-shift-start="{{ $timeOption->shift_start_time ? \Carbon\Carbon::parse($timeOption->shift_start_time)->format('H:i') : '' }}"
                                        data-late-after="{{ $timeOption->late_after_time ? \Carbon\Carbon::parse($timeOption->late_after_time)->format('H:i') : '' }}"
                                        data-block-after="{{ $timeOption->block_after_time ? \Carbon\Carbon::parse($timeOption->block_after_time)->format('H:i') : '' }}"
                                        data-half-day-after="{{ $timeOption->half_day_after_time ? \Carbon\Carbon::parse($timeOption->half_day_after_time)->format('H:i') : '' }}"
                                        data-shift-end="{{ $timeOption->shift_end_time ? \Carbon\Carbon::parse($timeOption->shift_end_time)->format('H:i') : '' }}"
                                        data-req-mins="{{ $timeOption->required_work_minutes ?? 480 }}"
                                        data-lunch-mins="{{ $timeOption->lunch_break_minutes ?? 60 }}">
                                        {{ $timeOption->name }} ({{ $timeOption->shift_start_time ? \Carbon\Carbon::parse($timeOption->shift_start_time)->format('h:i A') : 'Flexible' }} - {{ $timeOption->shift_end_time ? \Carbon\Carbon::parse($timeOption->shift_end_time)->format('h:i A') : 'Flexible' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Shift Timing Customisation Section -->
                <div class="card border-0 rounded-16 shadow-2xs p-4 mb-3 bg-white" id="assignFlexibleSection">
                    <h6 class="shift-modal-card-title"><i class="fas fa-clock mr-1 text-primary"></i> Shift Timing Customisation</h6>
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Punch Allowed <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="punch_allowed_from" id="assign_punch_allowed_from" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Shift Start <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="shift_start_time" id="assign_shift_start_time" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Late After <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="late_after_time" id="assign_late_after_time" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Blocked Punch <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="block_after_time" id="assign_block_after_time" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Half Day After <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="half_day_after_time" id="assign_half_day_after_time" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Shift End <span class="text-danger">*</span></label>
                            <div class="time-picker-container">
                                <input type="time" name="shift_end_time" id="assign_shift_end_time" class="form-control native-time-input rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                                <span class="time-display-val">--:--</span>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Required Minutes <span class="text-danger">*</span></label>
                            <input type="number" name="required_work_minutes" id="assign_required_work_minutes" class="form-control rounded-12 border-light bg-light" value="480" min="0" placeholder="e.g. 480" style="height: 42px; font-size: 13px;">
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Lunch Minutes <span class="text-danger">*</span></label>
                            <input type="number" name="lunch_minutes" id="assign_lunch_minutes" class="form-control rounded-12 border-light bg-light" value="60" min="0" placeholder="e.g. 60" style="height: 42px; font-size: 13px;">
                        </div>
                    </div>
                </div>

                <div class="card border-0 rounded-16 shadow-2xs p-4 bg-white">
                    <h6 class="shift-modal-card-title"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Date Boundaries & Status</h6>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Effective From Date <span class="text-danger">*</span></label>
                            <input type="date" name="effective_from" class="form-control rounded-12 border-light bg-light" value="{{ date('Y-m-d') }}" required style="height: 42px; font-size: 13px;">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="shift-modal-label">Effective Till Date (Optional)</label>
                            <input type="date" name="effective_to" class="form-control rounded-12 border-light bg-light" style="height: 42px; font-size: 13px;">
                        </div>
                    </div>
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" id="assignShiftActive" name="is_active" value="1" checked>
                        <label class="custom-control-label font-weight-bold text-dark" for="assignShiftActive" style="font-size: 13px;">Set as Active Shift Assignment</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-0 px-4 py-3">
                <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold" style="background: linear-gradient(135deg, var(--orb-primary, #6366F1), var(--orb-secondary, #4F46E5)); font-size: 13px;">
                    <i class="fas fa-check-circle mr-1"></i> Save Shift Assignment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Shift Modals -->
@foreach($employees as $emp)
    @if($emp->currentShiftTiming)
    @php
        $assignment = $emp->currentShiftTiming;
        $currentShiftModel = optional($assignment)->attendanceTime;
        $isCurrentFlex = $currentShiftModel && (in_array(strtolower($currentShiftModel->shift_type ?? ''), ['flexible', 'flexible_part_time']) || stripos($currentShiftModel->name, 'flexible') !== false);
    @endphp
    <div class="modal fade" id="editShiftModal{{ $assignment->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('employee.shift-assignment.update', $assignment->id) }}" class="modal-content border-0 rounded-24 shadow-lg overflow-hidden">
                @csrf
                @method('PUT')
                <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, var(--orb-primary, #6366F1), var(--orb-secondary, #4F46E5));">
                    <div>
                        <h5 class="modal-title font-weight-bold text-white mb-1"><i class="fas fa-sliders-h mr-2"></i> Edit Employee Shift Assignment</h5>
                        <div class="text-white-50 small">{{ optional($emp->user)->name ?? $emp->employee_code }}</div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" style="background: #F9FAFB;">
                    <div class="card border-0 rounded-16 shadow-2xs p-4 mb-3 bg-white">
                        <h6 class="shift-modal-card-title"><i class="fas fa-business-time mr-1 text-primary"></i> Shift Template Selection</h6>
                        <div class="form-group mb-0">
                            <label class="shift-modal-label">Select Shift Template <span class="text-danger">*</span></label>
                            <select name="attendance_time_id" id="editShiftSelect{{ $assignment->id }}" class="form-control rounded-12 border-light bg-light" required style="height: 42px; font-size: 13px;" onchange="handleShiftTemplateSelect('edit_{{ $assignment->id }}', this)">
                                @foreach($attendanceTimes as $timeOption)
                                    @php
                                        $isFlexOpt = in_array(strtolower($timeOption->shift_type ?? ''), ['flexible', 'flexible_part_time']) || stripos($timeOption->name, 'flexible') !== false;
                                    @endphp
                                    <option value="{{ $timeOption->id }}"
                                        data-flexible="{{ $isFlexOpt ? '1' : '0' }}"
                                        data-punch-allowed="{{ $timeOption->punch_allowed_from ? \Carbon\Carbon::parse($timeOption->punch_allowed_from)->format('H:i') : '' }}"
                                        data-shift-start="{{ $timeOption->shift_start_time ? \Carbon\Carbon::parse($timeOption->shift_start_time)->format('H:i') : '' }}"
                                        data-late-after="{{ $timeOption->late_after_time ? \Carbon\Carbon::parse($timeOption->late_after_time)->format('H:i') : '' }}"
                                        data-block-after="{{ $timeOption->block_after_time ? \Carbon\Carbon::parse($timeOption->block_after_time)->format('H:i') : '' }}"
                                        data-half-day-after="{{ $timeOption->half_day_after_time ? \Carbon\Carbon::parse($timeOption->half_day_after_time)->format('H:i') : '' }}"
                                        data-shift-end="{{ $timeOption->shift_end_time ? \Carbon\Carbon::parse($timeOption->shift_end_time)->format('H:i') : '' }}"
                                        data-req-mins="{{ $timeOption->required_work_minutes ?? 480 }}"
                                        data-lunch-mins="{{ $timeOption->lunch_break_minutes ?? 60 }}"
                                        {{ $assignment->attendance_time_id == $timeOption->id ? 'selected' : '' }}>
                                        {{ $timeOption->name }} ({{ $timeOption->shift_start_time ? \Carbon\Carbon::parse($timeOption->shift_start_time)->format('h:i A') : 'Flexible' }} - {{ $timeOption->shift_end_time ? \Carbon\Carbon::parse($timeOption->shift_end_time)->format('h:i A') : 'Flexible' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Shift Timing Customisation Section -->
                    <div class="card border-0 rounded-16 shadow-2xs p-4 mb-3 bg-white" id="editFlexibleSection{{ $assignment->id }}">
                        <h6 class="shift-modal-card-title"><i class="fas fa-clock mr-1 text-primary"></i> Shift Timing Customisation</h6>
                        <div class="row">
                            <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                                <label class="shift-modal-label">Punch Allowed <span class="text-danger">*</span></label>
                                <div class="time-picker-container">
                                    <input type="time" name="punch_allowed_from" id="edit_punch_allowed_from_{{ $assignment->id }}" class="form-control native-time-input rounded-12 border-light bg-light" value="{{ $assignment->punch_allowed_from ? \Carbon\Carbon::parse($assignment->punch_allowed_from)->format('H:i') : '' }}" style="height: 42px; font-size: 13px;">
                                    <span class="time-display-val">--:--</span>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                                <label class="shift-modal-label">Shift Start <span class="text-danger">*</span></label>
                                <div class="time-picker-container">
                                    <input type="time" name="shift_start_time" id="edit_shift_start_time_{{ $assignment->id }}" class="form-control native-time-input rounded-12 border-light bg-light" value="{{ $assignment->shift_start_time ? \Carbon\Carbon::parse($assignment->shift_start_time)->format('H:i') : '' }}" style="height: 42px; font-size: 13px;">
                                    <span class="time-display-val">--:--</span>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                                <label class="shift-modal-label">Late After <span class="text-danger">*</span></label>
                                <div class="time-picker-container">
                                    <input type="time" name="late_after_time" id="edit_late_after_time_{{ $assignment->id }}" class="form-control native-time-input rounded-12 border-light bg-light" value="{{ $assignment->late_after_time ? \Carbon\Carbon::parse($assignment->late_after_time)->format('H:i') : '' }}" style="height: 42px; font-size: 13px;">
                                    <span class="time-display-val">--:--</span>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                                <label class="shift-modal-label">Blocked Punch <span class="text-danger">*</span></label>
                                <div class="time-picker-container">
                                    <input type="time" name="block_after_time" id="edit_block_after_time_{{ $assignment->id }}" class="form-control native-time-input rounded-12 border-light bg-light" value="{{ $assignment->block_after_time ? \Carbon\Carbon::parse($assignment->block_after_time)->format('H:i') : '' }}" style="height: 42px; font-size: 13px;">
                                    <span class="time-display-val">--:--</span>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                                <label class="shift-modal-label">Half Day After <span class="text-danger">*</span></label>
                                <div class="time-picker-container">
                                    <input type="time" name="half_day_after_time" id="edit_half_day_after_time_{{ $assignment->id }}" class="form-control native-time-input rounded-12 border-light bg-light" value="{{ $assignment->half_day_after_time ? \Carbon\Carbon::parse($assignment->half_day_after_time)->format('H:i') : '' }}" style="height: 42px; font-size: 13px;">
                                    <span class="time-display-val">--:--</span>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                                <label class="shift-modal-label">Shift End <span class="text-danger">*</span></label>
                                <div class="time-picker-container">
                                    <input type="time" name="shift_end_time" id="edit_shift_end_time_{{ $assignment->id }}" class="form-control native-time-input rounded-12 border-light bg-light" value="{{ $assignment->shift_end_time ? \Carbon\Carbon::parse($assignment->shift_end_time)->format('H:i') : '' }}" style="height: 42px; font-size: 13px;">
                                    <span class="time-display-val">--:--</span>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                                <label class="shift-modal-label">Required Minutes <span class="text-danger">*</span></label>
                                <input type="number" name="required_work_minutes" id="edit_required_work_minutes_{{ $assignment->id }}" class="form-control rounded-12 border-light bg-light" value="{{ $assignment->required_work_minutes ?? 300 }}" min="0" placeholder="e.g. 300" style="height: 42px; font-size: 13px;">
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 form-group mb-3">
                                <label class="shift-modal-label">Lunch Minutes <span class="text-danger">*</span></label>
                                <input type="number" name="lunch_minutes" id="edit_lunch_minutes_{{ $assignment->id }}" class="form-control rounded-12 border-light bg-light" value="{{ $assignment->lunch_minutes ?? 0 }}" min="0" placeholder="e.g. 60" style="height: 42px; font-size: 13px;">
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 rounded-16 shadow-2xs p-4 bg-white">
                        <h6 class="shift-modal-card-title"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Date Boundaries & Status</h6>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="shift-modal-label">Effective From Date <span class="text-danger">*</span></label>
                                <input type="date" name="effective_from" class="form-control rounded-12 border-light bg-light" value="{{ optional($assignment->effective_from)->format('Y-m-d') }}" required style="height: 42px; font-size: 13px;">
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="shift-modal-label">Effective Till Date (Optional)</label>
                                <input type="date" name="effective_to" class="form-control rounded-12 border-light bg-light" value="{{ optional($assignment->effective_to)->format('Y-m-d') }}" style="height: 42px; font-size: 13px;">
                            </div>
                        </div>
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" class="custom-control-input" id="editShiftActive{{ $assignment->id }}" name="is_active" value="1" {{ $assignment->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-dark" for="editShiftActive{{ $assignment->id }}" style="font-size: 13px;">Set as Active Shift Assignment</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0 px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold" style="background: linear-gradient(135deg, var(--orb-primary, #6366F1), var(--orb-secondary, #4F46E5)); font-size: 13px;">
                        <i class="fas fa-save mr-1"></i> Update Shift Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

@endsection

@push('scripts')
<script>
    function selectEmployeeForShift(empId) {
        document.getElementById('assignEmployeeSelect').value = empId;
    }

    function handleShiftTemplateSelect(context, selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        let panel;
        if (context === 'assign') {
            panel = document.getElementById('assignFlexibleSection');
        } else if (context.startsWith('edit_')) {
            const id = context.replace('edit_', '');
            panel = document.getElementById('editFlexibleSection' + id);
        }

        if (!panel) return;

        const punchFrom = selectedOption.getAttribute('data-punch-allowed') || '';
        const shiftStart = selectedOption.getAttribute('data-shift-start') || '';
        const lateAfter = selectedOption.getAttribute('data-late-after') || '';
        const blockAfter = selectedOption.getAttribute('data-block-after') || '';
        const halfDayAfter = selectedOption.getAttribute('data-half-day-after') || '';
        const shiftEnd = selectedOption.getAttribute('data-shift-end') || '';
        const reqMins = selectedOption.getAttribute('data-req-mins') || '480';
        const lunchMins = selectedOption.getAttribute('data-lunch-mins') || '60';

        const punchInput = panel.querySelector('input[name="punch_allowed_from"]');
        const startInput = panel.querySelector('input[name="shift_start_time"]');
        const lateInput = panel.querySelector('input[name="late_after_time"]');
        const blockInput = panel.querySelector('input[name="block_after_time"]');
        const halfDayInput = panel.querySelector('input[name="half_day_after_time"]');
        const endInput = panel.querySelector('input[name="shift_end_time"]');
        const reqInput = panel.querySelector('input[name="required_work_minutes"]');
        const lunchInput = panel.querySelector('input[name="lunch_minutes"]');

        if (punchInput && punchFrom) punchInput.value = punchFrom;
        if (startInput && shiftStart) startInput.value = shiftStart;
        if (lateInput && lateAfter) lateInput.value = lateAfter;
        if (blockInput && blockAfter) blockInput.value = blockAfter;
        if (halfDayInput && halfDayAfter) halfDayInput.value = halfDayAfter;
        if (endInput && shiftEnd) endInput.value = shiftEnd;
        if (reqInput) reqInput.value = reqMins;
        if (lunchInput) lunchInput.value = lunchMins;

        updateAllTimeDisplaysInScope(panel);
    }

    function formatTimeTo12Hour(timeStr) {
        if (!timeStr) return '--:--';
        const parts = timeStr.split(':');
        if (parts.length < 2) return '--:--';
        let hours = Number(parts[0]);
        const minutes = Number(parts[1]);
        if (isNaN(hours) || isNaN(minutes)) return '--:--';

        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        const strMinutes = String(minutes).padStart(2, '0');
        const strHours = String(hours).padStart(2, '0');
        return `${strHours}:${strMinutes} ${ampm}`;
    }

    function addMinutesToTime(timeStr, minutesToAdd) {
        if (!timeStr) return '';
        const parts = timeStr.split(':');
        if (parts.length < 2) return '';
        const hours = Number(parts[0]);
        const minutes = Number(parts[1]);
        const date = new Date();
        date.setHours(hours);
        date.setMinutes(minutes + minutesToAdd);
        date.setSeconds(0);

        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    }

    function updateAllTimeDisplaysInScope(scopeEl) {
        if (!scopeEl) return;
        const inputs = scopeEl.querySelectorAll('input[type="time"]');
        inputs.forEach(input => {
            const overlay = input.parentNode.querySelector('.time-display-val');
            if (overlay) {
                overlay.textContent = formatTimeTo12Hour(input.value);
            }
        });
    }

    function autoCalculateTimingsForScope(scopeEl) {
        if (!scopeEl) return;
        const shiftStartInput   = scopeEl.querySelector('input[name="shift_start_time"]');
        const lateAfterInput    = scopeEl.querySelector('input[name="late_after_time"]');
        const halfDayAfterInput = scopeEl.querySelector('input[name="half_day_after_time"]');
        const shiftEndInput     = scopeEl.querySelector('input[name="shift_end_time"]');
        const reqMinutesInput   = scopeEl.querySelector('input[name="required_work_minutes"]');
        const lunchMinutesInput = scopeEl.querySelector('input[name="lunch_minutes"]');
        const punchAllowedInput = scopeEl.querySelector('input[name="punch_allowed_from"]');
        const blockedPunchInput = scopeEl.querySelector('input[name="block_after_time"]');

        if (!shiftStartInput) return;

        const startTime = shiftStartInput.value;
        if (!startTime) return;

        // 1. Late After: Start + 65 mins
        if (lateAfterInput) {
            lateAfterInput.value = addMinutesToTime(startTime, 65);
        }

        // 2. Half Day After: Start + 240 mins (4 hours)
        if (halfDayAfterInput) {
            halfDayAfterInput.value = addMinutesToTime(startTime, 240);
        }

        // 3. Punch Allowed: Start - 60 mins (1 hour before)
        if (punchAllowedInput) {
            punchAllowedInput.value = addMinutesToTime(startTime, -60);
        }

        // 4. Shift End Time: Start + Required Minutes + Lunch Minutes
        const reqMin = Number(reqMinutesInput?.value || 0);
        const lunchMin = Number(lunchMinutesInput?.value || 0);
        if (shiftEndInput && (reqMin > 0 || lunchMin > 0)) {
            shiftEndInput.value = addMinutesToTime(startTime, reqMin + lunchMin);
        }

        // 5. Blocked Punch: Start + 75 mins
        if (blockedPunchInput) {
            blockedPunchInput.value = addMinutesToTime(startTime, 75);
        }

        updateAllTimeDisplaysInScope(scopeEl);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize time overlays for all card sections
        document.querySelectorAll('.card').forEach(panel => {
            updateAllTimeDisplaysInScope(panel);

            // Bind input/change listeners for time displays & auto-calculation
            const timeInputs = panel.querySelectorAll('input[type="time"]');
            timeInputs.forEach(input => {
                input.addEventListener('input', function() {
                    updateAllTimeDisplaysInScope(panel);
                });
                input.addEventListener('change', function() {
                    updateAllTimeDisplaysInScope(panel);
                });
            });

            // Bind auto calculation on shift_start_time, required_work_minutes, lunch_minutes
            const startInput = panel.querySelector('input[name="shift_start_time"]');
            const reqInput   = panel.querySelector('input[name="required_work_minutes"]');
            const lunchInput = panel.querySelector('input[name="lunch_minutes"]');

            if (startInput) {
                startInput.addEventListener('input', () => autoCalculateTimingsForScope(panel));
                startInput.addEventListener('change', () => autoCalculateTimingsForScope(panel));
            }
            if (reqInput) {
                reqInput.addEventListener('input', () => autoCalculateTimingsForScope(panel));
                reqInput.addEventListener('change', () => autoCalculateTimingsForScope(panel));
            }
            if (lunchInput) {
                lunchInput.addEventListener('input', () => autoCalculateTimingsForScope(panel));
                lunchInput.addEventListener('change', () => autoCalculateTimingsForScope(panel));
            }
        });
    });
</script>
@endpush
