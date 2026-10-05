<div class="modal fade" id="viewModal{{ $attendance->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal" style="border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.18);">
            
            {{-- MODAL HEADER --}}
            <div class="orb-modal-header d-flex align-items-center justify-content-between p-3 px-4" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); color: #fff;">
                <div class="d-flex align-items-center">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff; flex-shrink: 0; margin-right: 14px;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 font-weight-bold text-white" style="font-size: 16.5px; letter-spacing: -0.2px;">Attendance Record Details</h5>
                        <p class="orb-modal-subtitle mb-0 text-white-50" style="font-size: 12px; margin-top: 2px;">Full day punch timings, work metrics, compliance and audit logs.</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.85; border: 0; background: transparent; font-size: 22px; cursor: pointer; outline: none; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body orb-modal-body p-4" style="background: #F8FAFC; max-height: calc(85vh - 130px); overflow-y: auto;">
                @php
                    $isUnlocked = (bool) ($attendance->is_admin_unlocked || $attendance->unlocked_at || ($attendance->attendance_status ?? '') === 'unlocked');
                    $rawStatus = strtolower(trim($attendance->attendance_status ?? ''));
                    if (empty($rawStatus)) {
                        $rawStatus = strtolower(trim(optional($attendance->attendanceType)->code ?? ''));
                    }
                    if (($attendance->is_admin_unlocked || $attendance->unlocked_at || $attendance->unlock_type) && ($rawStatus === 'punch_blocked' || empty($rawStatus))) {
                        $rawStatus = $attendance->punch_in_time ? 'present' : 'unlocked';
                    }

                    $statusMap = [
                        'present'           => ['present', 'PRESENT'],
                        'half_day'          => ['half_day', 'HALF DAY'],
                        'absent'            => ['absent', 'ABSENT'],
                        'missed_punch'      => ['missed_punch', 'MISSED PUNCH'],
                        'leave'             => ['leave', 'LEAVE'],
                        'holiday'           => ['holiday', 'HOLIDAY'],
                        'week_off'          => ['week_off', 'WEEK OFF'],
                        'punch_blocked'     => ['punch_blocked', 'PUNCH BLOCKED'],
                        'unlocked'          => ['unlocked', 'UNLOCKED'],
                        'awaiting_punch_in' => ['unlocked', 'UNLOCKED'],
                        'lwp'               => ['absent', 'ABSENT'],
                    ];

                    if (isset($statusMap[$rawStatus])) {
                        $statusCode = $statusMap[$rawStatus][0];
                        $statusLabel = $statusMap[$rawStatus][1];
                    } else {
                        $statusCode = optional($attendance->attendanceType)->code ?? ($rawStatus ?: 'default');
                        $statusLabel = strtoupper(optional($attendance->attendanceType)->name ?? ($attendance->attendance_status ?? 'N/A'));
                    }

                    $attDateFormatted = $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y (l)') : '-';
                    $passportPhotoUrl = resolveEmployeePassportPhoto($attendance->employee ?? $attendance);
                    $employeeName = optional($attendance->user)->name ?? optional(optional($attendance->employee)->user)->name ?? 'Employee';
                    $employeeCode = optional($attendance->employee)->employee_code ?? 'EMP';
                    $employeeInitial = resolveEmployeeInitials($attendance->employee ?? $attendance);
                    $departmentName = optional(optional($attendance->employee)->department)->name ?? optional($attendance->department)->name ?? 'General Department';
                    $designationName = optional(optional($attendance->employee)->designation)->name ?? optional($attendance->designation)->name ?? 'Team Member';

                    $punchIn = $attendance->punch_in_time ?? $attendance->punch_in ?? null;
                    $punchOut = $attendance->punch_out_time ?? $attendance->punch_out ?? null;
                    $punchInStr = $punchIn ? \Carbon\Carbon::parse($punchIn)->format('h:i A') : 'Not Punched';
                    $punchOutStr = $punchOut ? \Carbon\Carbon::parse($punchOut)->format('h:i A') : ($punchIn ? 'Currently Active' : 'Not Punched');
                    $targetOutStr = $attendance->target_punch_out_time ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A') : '-';

                    $gross = $attendance->gross_duration ?? (isset($attendance->gross_work_minutes) ? floor($attendance->gross_work_minutes / 60).'h '.($attendance->gross_work_minutes % 60).'m' : '-');
                    $net = $attendance->net_duration ?? (isset($attendance->working_minutes) ? floor($attendance->working_minutes / 60).'h '.($attendance->working_minutes % 60).'m' : '-');
                    $breakMinutes = (int) ($attendance->break_minutes ?? 0) + (int) ($attendance->lunch_break_minutes ?? 0);
                    $breakStr = $breakMinutes > 0 ? (floor($breakMinutes / 60).'h '.($breakMinutes % 60).'m') : '0m';

                    $modeCode = strtoupper($attendance->work_mode ?? 'WFO');
                    $shiftName = optional($attendance->attendanceTime)->name ?? 'General Shift';

                    $reasonText = $attendance->half_day_reason 
                        ?: ($attendance->lwp_reason 
                        ?: ($attendance->status_reason 
                        ?: ($attendance->remarks 
                        ?: ($attendance->blocked_reason 
                        ?: ($attendance->block_reason 
                        ?: ($attendance->auto_block_reason 
                        ?: ($attendance->unlock_remarks ?: null)))))));

                    $isLate = (bool) ($attendance->is_late ?? $attendance->late_mark ?? false);
                    $lateMinutes = (int) ($attendance->late_minutes ?? 0);
                    $isEarly = (bool) ($attendance->is_early_out ?? $attendance->early_leave_mark ?? false);
                    $earlyMinutes = (int) ($attendance->early_out_minutes ?? 0);
                    $isBlocked = (bool) ($attendance->is_blocked ?? $attendance->is_punch_blocked ?? false);
                    $isMissed = (bool) ($attendance->missed_punch ?? $attendance->is_missed_punch ?? false);
                    $violationCount = (int) ($attendance->violation_count ?? 0);
                @endphp

                {{-- 1. EMPLOYEE & DATE HERO BANNER --}}
                <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #ffffff; border: 1px solid #EAECF0;">
                    <div class="card-body p-3 px-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
                            <div class="d-flex align-items-center">
                                <div style="width: 56px; height: 56px; border-radius: 16px; flex-shrink: 0; overflow: hidden; background: #F2F4F7; box-shadow: 0 2px 8px rgba(16, 24, 40, 0.08); border: 2px solid #ffffff; margin-right: 16px;">
                                    @if($passportPhotoUrl)
                                    <img src="{{ $passportPhotoUrl }}" alt="{{ $employeeName }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 19px; font-weight: 800; color: #475467; background: #EAECF0;">{{ $employeeInitial }}</div>
                                    @endif
                                </div>
                                <div>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                        <h5 class="mb-0 font-weight-bold" style="font-size: 16px; color: #101828; margin-right: 8px; line-height: 1.3;">{{ $employeeName }}</h5>
                                        <span style="font-size: 11px; font-weight: 700; background: #F2F4F7; color: #344054; border: 1px solid #D0D5DD; border-radius: 6px; padding: 2px 8px; letter-spacing: 0.3px;">{{ $employeeCode }}</span>
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 12.5px; font-weight: 500; color: #475467; display: flex; align-items: center; flex-wrap: wrap;">
                                        <span class="mr-2"><i class="fas fa-briefcase text-muted mr-1"></i> {{ $designationName }}</span>
                                        <span class="text-muted mr-2" style="opacity: 0.5;">&bull;</span>
                                        <span><i class="fas fa-building text-muted mr-1"></i> {{ $departmentName }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                <span class="att-badge badge-{{ $statusCode }}" style="font-size: 11px; padding: 6px 14px; font-weight: 800; border-radius: 8px; margin-right: 6px;">
                                    {{ $statusLabel }}
                                </span>
                                <span class="mode-badge mode-{{ strtolower($modeCode) }}" style="font-size: 11px; padding: 6px 14px; border-radius: 8px;">
                                    {{ $modeCode }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. PUNCH TIMINGS & WORK DURATION (4-METRIC GRID) --}}
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded h-100 shadow-sm" style="border-radius: 14px !important; border-color: #EAECF0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Punch In</span>
                                <i class="fas fa-sign-in-alt text-success" style="font-size: 13px;"></i>
                            </div>
                            <div class="font-weight-bold text-dark" style="font-size: 15px; margin-top: 3px;">{{ $punchInStr }}</div>
                            <div class="text-muted" style="font-size: 11px; margin-top: 3px;">Target: {{ $targetOutStr }}</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded h-100 shadow-sm" style="border-radius: 14px !important; border-color: #EAECF0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Punch Out</span>
                                <i class="fas fa-sign-out-alt text-danger" style="font-size: 13px;"></i>
                            </div>
                            <div class="font-weight-bold text-dark" style="font-size: 15px; margin-top: 3px;">{{ $punchOutStr }}</div>
                            <div class="text-muted" style="font-size: 11px; margin-top: 3px;">Shift: {{ $shiftName }}</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded h-100 shadow-sm" style="border-radius: 14px !important; border-color: #EAECF0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Gross Hours</span>
                                <i class="fas fa-hourglass-start text-primary" style="font-size: 13px;"></i>
                            </div>
                            <div class="font-weight-bold text-dark" style="font-size: 15px; margin-top: 3px;">{{ $gross }}</div>
                            <div class="text-muted" style="font-size: 11px; margin-top: 3px;">Total In-Office time</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded h-100 shadow-sm" style="border-radius: 14px !important; border-color: #EAECF0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Net Work Hours</span>
                                <i class="fas fa-business-time text-info" style="font-size: 13px;"></i>
                            </div>
                            <div class="font-weight-bold text-success" style="font-size: 15px; margin-top: 3px;">{{ $net }}</div>
                            <div class="text-muted" style="font-size: 11px; margin-top: 3px;">Breaks: {{ $breakStr }}</div>
                        </div>
                    </div>
                </div>

                {{-- 3. STATUS, REASONS & COMPLIANCE SECTION --}}
                <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #ffffff; border: 1px solid #EAECF0;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="font-weight-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-shield-alt text-primary mr-1"></i> Status, Reasons & Compliance Flags
                        </div>
                        <div class="small font-weight-bold text-muted">{{ $attDateFormatted }}</div>
                    </div>
                    <div class="card-body p-3 px-4">
                        <div class="row">
                            {{-- Reason Text --}}
                            <div class="col-12 mb-3">
                                <div class="p-3 rounded" style="background: #F9FAFB; border: 1px dashed #D0D5DD; border-radius: 12px;">
                                    <div class="text-muted font-weight-bold text-uppercase" style="font-size: 10.5px; letter-spacing: 0.3px;">Status / Adjustment Reason:</div>
                                    <div class="text-dark font-weight-bold mt-1" style="font-size: 13px; line-height: 1.4;">
                                        {{ $reasonText ?: 'No special reason or adjustment recorded.' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Compliance Flags Badges --}}
                            <div class="col-md-6 mb-2">
                                <div class="p-3 border rounded h-100" style="background: #ffffff; border-radius: 12px; border-color: #EAECF0 !important;">
                                    <div class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10.5px; letter-spacing: 0.3px;">Punctuality & Flags:</div>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        @if($isLate)
                                            <span class="flag flag-late mr-1 mb-1" style="font-size: 10.5px; padding: 4px 10px;">
                                                <i class="fas fa-clock mr-1"></i> Late by {{ $lateMinutes > 0 ? $lateMinutes.'m' : 'mark' }}
                                            </span>
                                        @endif
                                        @if($isEarly)
                                            <span class="flag flag-early mr-1 mb-1" style="font-size: 10.5px; padding: 4px 10px;">
                                                <i class="fas fa-sign-out-alt mr-1"></i> Early Out {{ $earlyMinutes > 0 ? $earlyMinutes.'m' : '' }}
                                            </span>
                                        @endif
                                        @if($isMissed)
                                            <span class="flag flag-missed mr-1 mb-1" style="font-size: 10.5px; padding: 4px 10px;">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Missed Punch
                                            </span>
                                        @endif
                                        @if($isBlocked)
                                            <span class="flag flag-blocked mr-1 mb-1" style="font-size: 10.5px; padding: 4px 10px;">
                                                <i class="fas fa-ban mr-1"></i> Punch Blocked
                                            </span>
                                        @endif
                                        @if(!$isLate && !$isEarly && !$isMissed && !$isBlocked)
                                            <span class="flag flag-clear mr-1 mb-1" style="font-size: 10.5px; padding: 4px 10px;">
                                                <i class="fas fa-check mr-1"></i> Clear (No Violations)
                                            </span>
                                        @endif
                                        @if($violationCount > 0)
                                            <span class="badge bg-danger text-white font-weight-bold mr-1 mb-1" style="font-size: 10px; padding: 4px 10px; border-radius: 999px;">
                                                Violation #{{ $violationCount }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Punch Notes --}}
                            <div class="col-md-6 mb-2">
                                <div class="p-3 border rounded h-100" style="background: #ffffff; border-radius: 12px; border-color: #EAECF0 !important;">
                                    <div class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10.5px; letter-spacing: 0.3px;">Employee Punch Notes:</div>
                                    <div style="font-size: 12px; color: #344054; line-height: 1.5;">
                                        <div><strong>In Note:</strong> {{ $attendance->punch_in_note ?: '-' }}</div>
                                        <div class="mt-1"><strong>Out Note:</strong> {{ $attendance->punch_out_note ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. HR UNLOCK & APPROVAL AUDIT (CONDITIONAL) --}}
                @if($isUnlocked || !empty($attendance->unlocked_at) || !empty($attendance->hr_approved_at))
                <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #ffffff; border-left: 4px solid #12B76A !important; border: 1px solid #EAECF0;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="font-weight-bold text-success" style="font-size: 13.5px;">
                            <i class="fas fa-unlock-alt mr-1"></i> HR Approval & Unlock Audit
                        </div>
                        <span class="badge bg-success-subtle text-success font-weight-bold" style="font-size: 11px;">APPROVED</span>
                    </div>
                    <div class="card-body p-3 px-4">
                        <div class="row" style="font-size: 12.5px;">
                            <div class="col-md-4 mb-2">
                                <span class="text-muted">Unlocked / Approved By:</span>
                                <div class="font-weight-bold text-dark mt-1">{{ optional($attendance->unlockedBy)->name ?? optional($attendance->hrApprovedBy)->name ?? 'HR Admin' }}</div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <span class="text-muted">Approved At:</span>
                                <div class="font-weight-bold text-dark mt-1">{{ $attendance->unlocked_at ? \Carbon\Carbon::parse($attendance->unlocked_at)->format('d M Y, h:i A') : ($attendance->hr_approved_at ? \Carbon\Carbon::parse($attendance->hr_approved_at)->format('d M Y, h:i A') : '-') }}</div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <span class="text-muted">Unlock Category:</span>
                                <div class="font-weight-bold text-dark mt-1">{{ ucwords(str_replace('_', ' ', $attendance->unlock_type ?? ($attendance->unlock_reason_category ?? 'General Unlock'))) }}</div>
                            </div>
                            @if(!empty($attendance->hr_approval_note) || !empty($attendance->unlock_remarks))
                            <div class="col-12 mt-2">
                                <div class="p-2 bg-light rounded text-muted" style="border-radius: 8px;">
                                    <strong>Approval Note:</strong> {{ $attendance->hr_approval_note ?: $attendance->unlock_remarks }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- 5. WORK REPORT LOGS (IF SUBMITTED) --}}
                @php
                    $firstLog = $attendance->workLogs->first();
                @endphp
                @if($firstLog)
                <div class="card border-0 shadow-sm" style="border-radius: 16px; background: #ffffff; border: 1px solid #EAECF0;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="font-weight-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-tasks text-primary mr-1"></i> Daily Work Report Summary
                        </div>
                        <span class="badge bg-primary text-white font-weight-bold" style="font-size: 10px;">SUBMITTED</span>
                    </div>
                    <div class="card-body p-3 px-4">
                        @php
                            $tasksJson = $firstLog->work_summary_json;
                            if (is_string($tasksJson)) {
                                $tasksJson = json_decode($tasksJson, true);
                            }
                            $pTitle = 'General Work Summary';
                            $pTasks = [];
                            if (is_array($tasksJson)) {
                                if (isset($tasksJson['projects']) && is_array($tasksJson['projects']) && !empty($tasksJson['projects'])) {
                                    $pTitle = $tasksJson['projects'][0]['project_name'] ?? $tasksJson['projects'][0]['name'] ?? 'Project Work';
                                    $pTasks = $tasksJson['projects'][0]['tasks'] ?? [];
                                } elseif (!empty($tasksJson['task_name'])) {
                                    $pTitle = $tasksJson['task_name'];
                                }
                            }
                        @endphp
                        <div class="font-weight-bold text-dark mb-2" style="font-size: 13.5px;">
                            Project: <span class="text-primary">{{ $pTitle }}</span>
                        </div>
                        @if(!empty($pTasks) && is_array($pTasks))
                        <ul class="list-unstyled mb-0 mt-2" style="font-size: 12.5px;">
                            @foreach($pTasks as $tsk)
                            <li class="d-flex align-items-start mb-2">
                                <i class="fas fa-check-circle text-success mt-1 mr-2" style="font-size: 12px; flex-shrink: 0;"></i>
                                <span class="text-dark">{{ is_array($tsk) ? ($tsk['task_name'] ?? ($tsk['title'] ?? ($tsk['description'] ?? 'Task'))) : $tsk }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @endif

            </div>

            {{-- MODAL FOOTER --}}
            <div class="modal-footer orb-modal-footer d-flex align-items-center justify-content-between p-3 px-4" style="background: #ffffff; border-top: 1px solid #EAECF0;">
                
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-secondary px-4 font-weight-bold mr-2" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 10px; font-size: 13px; height: 38px;">
                        Close
                    </button>
                    @if(!($isMyAttendance ?? request()->routeIs('hrms.attendance.my')) && (($canManageAttendance ?? false) || (auth()->user() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin'))))
                    <button type="button" class="btn btn-primary px-3 font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal" data-toggle="modal" data-target="#editModal{{ $attendance->id }}" style="border-radius: 10px; font-size: 13px; height: 38px; background: var(--orb-primary, #4B00E8); border: none;">
                        <i class="fas fa-edit mr-1"></i> Edit Attendance
                    </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
