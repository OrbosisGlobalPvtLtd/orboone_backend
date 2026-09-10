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
                    @if($isCustomAssignment)
                        <span class="badge px-3 py-1 font-weight-bold" style="border-radius: 12px; background: #F4F2FF; color: var(--orb-primary, #6366F1); border: 1px solid rgba(99, 102, 241, 0.2);">
                            <i class="fas fa-user-clock mr-1"></i>
                            {{ optional($assignedShift)->name ?? 'Default Shift' }}
                        </span>
                    @else
                        <span class="badge px-3 py-1 font-weight-bold" style="border-radius: 12px; background: #F3F4F6; color: #4B5563; border: 1px solid #E5E7EB;">
                            <i class="fas fa-cog mr-1"></i>
                            {{ optional($assignedShift)->name ?? 'Default Shift' }}
                        </span>
                    @endif
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
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 font-weight-bold" style="font-size: 12px; border-radius: 20px; background: linear-gradient(135deg, var(--orb-primary, #6366F1), var(--orb-secondary, #4F46E5));" data-toggle="modal" data-target="#assignShiftModal" onclick="selectEmployeeForShift('{{ $emp->id }}')">
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
