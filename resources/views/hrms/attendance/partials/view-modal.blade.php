<div class="modal fade" id="viewModal{{ $attendance->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal" style="border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 45px rgba(0,0,0,0.18);">
            
            {{-- MODAL HEADER --}}
            <div class="orb-modal-header d-flex align-items-center justify-content-between p-3 px-4" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); color: #fff;">
                <div class="d-flex align-items-center">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff; flex-shrink: 0; margin-right: 14px;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 font-weight-bold text-white" style="font-size: 16.5px; letter-spacing: -0.2px;">Attendance Record Details</h5>
                        <p class="orb-modal-subtitle mb-0 text-white-50" style="font-size: 12px; margin-top: 2px;">Daily punch metrics, payable units, violation diagnostics & administrative audit logs.</p>
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
                        'present'           => ['present', 'PRESENT', '#12B76A', 'fas fa-check-circle', '#ECFDF3'],
                        'half_day'          => ['half_day', 'HALF DAY', '#F79009', 'fas fa-adjust', '#FFFAEB'],
                        'absent'            => ['absent', 'ABSENT', '#F04438', 'fas fa-times-circle', '#FEF3F2'],
                        'lwp'               => ['lwp', 'LWP', '#D92D20', 'fas fa-minus-circle', '#FEF3F2'],
                        'missed_punch'      => ['missed_punch', 'MISSED PUNCH', '#E04F16', 'fas fa-exclamation-circle', '#FFF6ED'],
                        'leave'             => ['leave', 'LEAVE', '#0BA5EC', 'fas fa-calendar-minus', '#F0F9FF'],
                        'holiday'           => ['holiday', 'HOLIDAY', '#7A5AF8', 'fas fa-glass-cheers', '#F4F3FF'],
                        'week_off'          => ['week_off', 'WEEK OFF', '#667085', 'fas fa-bed', '#F8F9FA'],
                        'punch_blocked'     => ['punch_blocked', 'PUNCH BLOCKED', '#D92D20', 'fas fa-ban', '#FEF3F2'],
                        'unlocked'          => ['unlocked', 'UNLOCKED', '#12B76A', 'fas fa-unlock', '#ECFDF3'],
                        'awaiting_punch_in' => ['unlocked', 'UNLOCKED', '#12B76A', 'fas fa-unlock', '#ECFDF3'],
                    ];

                    if (isset($statusMap[$rawStatus])) {
                        $statusCode = $statusMap[$rawStatus][0];
                        $statusLabel = $statusMap[$rawStatus][1];
                        $statusColor = $statusMap[$rawStatus][2];
                        $statusIcon = $statusMap[$rawStatus][3];
                        $statusBg = $statusMap[$rawStatus][4];
                    } else {
                        $statusCode = optional($attendance->attendanceType)->code ?? ($rawStatus ?: 'default');
                        $statusLabel = strtoupper(optional($attendance->attendanceType)->name ?? ($attendance->attendance_status ?? 'N/A'));
                        $statusColor = '#667085';
                        $statusIcon = 'fas fa-info-circle';
                        $statusBg = '#F8F9FA';
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

                    $gross = $attendance->gross_duration ?? (isset($attendance->gross_work_minutes) && $attendance->gross_work_minutes > 0 ? floor($attendance->gross_work_minutes / 60).'h '.($attendance->gross_work_minutes % 60).'m' : '-');
                    $net = $attendance->net_duration ?? (isset($attendance->total_work_minutes) && $attendance->total_work_minutes > 0 ? floor($attendance->total_work_minutes / 60).'h '.($attendance->total_work_minutes % 60).'m' : (isset($attendance->working_minutes) && $attendance->working_minutes > 0 ? floor($attendance->working_minutes / 60).'h '.($attendance->working_minutes % 60).'m' : '-'));
                    $breakMinutes = (int) ($attendance->break_minutes ?? 0) + (int) ($attendance->lunch_break_minutes ?? 0);
                    $breakStr = $breakMinutes > 0 ? (floor($breakMinutes / 60).'h '.($breakMinutes % 60).'m') : '0m';

                    $modeCode = strtoupper($attendance->work_mode ?? 'WFO');
                    $shiftName = optional($attendance->attendanceTime)->name ?? 'General Shift';
                    $shiftStartTime = optional($attendance->attendanceTime)->shift_start_time ? \Carbon\Carbon::parse(optional($attendance->attendanceTime)->shift_start_time)->format('h:i A') : '10:00 AM';
                    $shiftEndTime = optional($attendance->attendanceTime)->shift_end_time ? \Carbon\Carbon::parse(optional($attendance->attendanceTime)->shift_end_time)->format('h:i A') : '07:00 PM';
                    $shiftWindowStr = "{$shiftStartTime} - {$shiftEndTime}";
                    $reqWorkMins = optional($attendance->attendanceTime)->required_work_minutes ?? 540;
                    $reqHoursStr = floor($reqWorkMins / 60).'h '.($reqWorkMins % 60).'m';
                    $halfDayMinMins = optional($attendance->attendanceTime)->half_day_min_minutes ?? 270;
                    $halfDayHoursStr = floor($halfDayMinMins / 60).'h '.($halfDayMinMins % 60).'m';

                    $isLate = (bool) ($attendance->is_late ?? $attendance->late_mark ?? false);
                    $lateMinutes = (int) ($attendance->late_minutes ?? 0);
                    $isEarly = (bool) ($attendance->is_early_out ?? $attendance->early_leave_mark ?? false);
                    $earlyMinutes = (int) ($attendance->early_out_minutes ?? 0);
                    $isBlocked = (bool) ($attendance->is_blocked ?? $attendance->is_punch_blocked ?? false);
                    $isMissed = (bool) ($attendance->missed_punch ?? $attendance->is_missed_punch ?? ($statusCode === 'missed_punch'));
                    $violationCount = (int) ($attendance->violation_count ?? 0);

                    // ================= PAYROLL IMPACT RESOLUTION =================
                    $payableDay = 0.0;
                    $payrollImpactType = 'unpaid';
                    $payrollExplanation = '';
                    $isPayableResolved = true;

                    try {
                        $payableResolver = app(\App\Services\HRMS\Attendance\AttendancePayableDayResolver::class);
                        $res = $payableResolver->resolve($attendance);
                        $payableDay = (float) ($res['payable_day'] ?? 0.0);
                        $payrollImpactType = $res['payroll_impact'] ?? 'unpaid';
                        $isPayableResolved = !($res['is_unresolved'] ?? false);
                        $payrollExplanation = $res['reason'] ?? '';
                    } catch (\Throwable $e) {
                        if (in_array($statusCode, ['present', 'holiday', 'week_off', 'leave'], true)) {
                            $payableDay = 1.0;
                            $payrollImpactType = 'paid';
                            $payrollExplanation = 'Full day payable units credited (1.0 Day).';
                        } elseif ($statusCode === 'half_day') {
                            $payableDay = 0.5;
                            $payrollImpactType = 'partial_paid';
                            $payrollExplanation = 'Half day payable units calculated (0.5 Day).';
                        } else {
                            $payableDay = 0.0;
                            $payrollImpactType = 'unpaid';
                            $payrollExplanation = 'Unpaid attendance record (0.0 Day).';
                        }
                    }

                    $isProcessedInPayroll = (bool) ($attendance->payroll_processed ?? false);
                    $payrollProcessedAt = $attendance->payroll_processed_at ? \Carbon\Carbon::parse($attendance->payroll_processed_at)->format('d M Y, h:i A') : null;

                    // ================= ATTENDANCE EDIT & AUDIT REASON =================
                    $isEditedByAdmin = (bool) (
                        $attendance->attendance_source === 'admin_override' 
                        || $isUnlocked 
                        || !empty($attendance->unlocked_at) 
                        || !empty($attendance->hr_approved_at) 
                        || (!empty($attendance->statusLogs) && $attendance->statusLogs->count() > 0)
                    );

                    $updateReason = $attendance->hr_approval_note 
                        ?: ($attendance->unlock_remarks 
                        ?: ($attendance->status_reason 
                        ?: ($attendance->remarks 
                        ?: ($attendance->half_day_reason 
                        ?: ($attendance->lwp_reason 
                        ?: ($attendance->missed_punch_reason 
                        ?: ($attendance->blocked_reason ?: null)))))));

                    $updatedBy = optional($attendance->hrApprovedBy)->name 
                        ?? optional($attendance->unlockedBy)->name 
                        ?? (optional(optional($attendance->statusLogs)->first())->createdBy->name ?? 'Administrator');

                    $updatedAtStr = $attendance->hr_approved_at 
                        ? \Carbon\Carbon::parse($attendance->hr_approved_at)->format('d M Y, h:i A')
                        : ($attendance->unlocked_at 
                            ? \Carbon\Carbon::parse($attendance->unlocked_at)->format('d M Y, h:i A')
                            : ($attendance->updated_at ? \Carbon\Carbon::parse($attendance->updated_at)->format('d M Y, h:i A') : '-'));

                    $updateCategory = match(strtolower($attendance->unlock_type ?? '')) {
                        'hr_manual_override', 'manual_override', 'manual_punch_in' => 'Admin Manual Override',
                        'biometric_sync' => 'Biometric Sync Correction',
                        'regularization_approval' => 'Regularization Approval',
                        default => ($attendance->unlock_type ? ucwords(str_replace('_', ' ', $attendance->unlock_type)) : ($attendance->attendance_source === 'admin_override' ? 'Admin Manual Override' : 'System Record'))
                    };

                    $sourceLabel = match(strtolower($attendance->attendance_source ?? '')) {
                        'admin_override' => 'Admin Override',
                        'mobile', 'mobile_app' => 'Mobile App',
                        'web' => 'Web Portal',
                        'biometric' => 'Biometric Device',
                        'leave_sync' => 'Leave Engine',
                        'comp_off_work' => 'Comp-Off Work',
                        default => ($attendance->attendance_source ? ucwords(str_replace('_', ' ', $attendance->attendance_source)) : 'System Punch')
                    };

                    $violationsList = $attendance->violations ?? collect([]);
                    $regList = $attendance->regularizations ?? collect([]);
                    $statusLogsList = $attendance->statusLogs ?? collect([]);
                    $payrollImpactsList = $attendance->payrollImpacts ?? collect([]);
                    $approvedReg = $regList->where('status', 'approved')->first();
                    $pendingReg = $regList->where('status', 'pending')->first();
                @endphp

                {{-- 1. EMPLOYEE & DATE HERO BANNER --}}
                <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #ffffff; border: 1px solid #EAECF0;">
                    <div class="card-body p-3 px-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
                            <div class="d-flex align-items-center">
                                <div style="width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0; overflow: hidden; background: #F2F4F7; box-shadow: 0 2px 8px rgba(16, 24, 40, 0.08); border: 2px solid #ffffff; margin-right: 14px;">
                                    @if($passportPhotoUrl)
                                    <img src="{{ $passportPhotoUrl }}" alt="{{ $employeeName }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; color: #475467; background: #EAECF0;">{{ $employeeInitial }}</div>
                                    @endif
                                </div>
                                <div>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                        <h5 class="mb-0 font-weight-bold" style="font-size: 16px; color: #101828; margin-right: 6px; line-height: 1.3;">{{ $employeeName }}</h5>
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
                                <span class="badge" style="background: {{ $statusBg }}; color: {{ $statusColor }}; border: 1px solid {{ $statusColor }}33; font-size: 11.5px; padding: 7px 14px; font-weight: 800; border-radius: 8px;">
                                    <i class="{{ $statusIcon }} mr-1"></i> {{ $statusLabel }}
                                </span>
                                <span class="mode-badge mode-{{ strtolower($modeCode) }}" style="font-size: 11px; padding: 6px 12px; border-radius: 8px;">
                                    {{ $modeCode }}
                                </span>
                                <span class="badge bg-light text-muted border" style="font-size: 11px; padding: 6px 10px; border-radius: 8px;">
                                    {{ $sourceLabel }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. PUNCH TIMINGS & WORK METRICS (4-METRIC GRID) --}}
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded h-100 shadow-sm" style="border-radius: 14px !important; border-color: #EAECF0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.4px;">Punch In</span>
                                <i class="fas fa-sign-in-alt text-success" style="font-size: 13px;"></i>
                            </div>
                            <div class="font-weight-bold text-dark" style="font-size: 16px; margin-top: 3px;">{{ $punchInStr }}</div>
                            <div class="text-muted" style="font-size: 11px; margin-top: 3px;">
                                Shift Starts: <strong>{{ $shiftStartTime }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded h-100 shadow-sm" style="border-radius: 14px !important; border-color: #EAECF0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.4px;">Punch Out</span>
                                <i class="fas fa-sign-out-alt text-danger" style="font-size: 13px;"></i>
                            </div>
                            <div class="font-weight-bold text-dark" style="font-size: 16px; margin-top: 3px;">{{ $punchOutStr }}</div>
                            <div class="text-muted" style="font-size: 11px; margin-top: 3px;">
                                Target Out: <strong>{{ $targetOutStr }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded h-100 shadow-sm" style="border-radius: 14px !important; border-color: #EAECF0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.4px;">Gross Duration</span>
                                <i class="fas fa-hourglass-start text-primary" style="font-size: 13px;"></i>
                            </div>
                            <div class="font-weight-bold text-dark" style="font-size: 16px; margin-top: 3px;">{{ $gross }}</div>
                            <div class="text-muted" style="font-size: 11px; margin-top: 3px;">Total in-office span</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded h-100 shadow-sm" style="border-radius: 14px !important; border-color: #EAECF0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted text-uppercase font-weight-bold" style="font-size: 10.5px; letter-spacing: 0.4px;">Net Work Hours</span>
                                <i class="fas fa-business-time text-info" style="font-size: 13px;"></i>
                            </div>
                            <div class="font-weight-bold text-success" style="font-size: 16px; margin-top: 3px;">{{ $net }}</div>
                            <div class="text-muted" style="font-size: 11px; margin-top: 3px;">Breaks: <strong>{{ $breakStr }}</strong></div>
                        </div>
                    </div>
                </div>

                {{-- SHIFT WINDOW REFERENCE STRIP --}}
                <div class="p-2 px-3 mb-3 rounded d-flex align-items-center justify-content-between flex-wrap shadow-sm" style="background: #ffffff; border: 1px solid #EAECF0; font-size: 12px; gap: 8px;">
                    <div>
                        <i class="fas fa-clock text-primary mr-1"></i> <strong>Shift Window:</strong> {{ $shiftName }} ({{ $shiftWindowStr }})
                    </div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                        <span><i class="fas fa-check-circle text-muted mr-1"></i> Full Day Requirement: <strong>{{ $reqHoursStr }}</strong></span>
                        <span><i class="fas fa-adjust text-muted mr-1"></i> Half Day Threshold: <strong>{{ $halfDayHoursStr }}</strong></span>
                    </div>
                </div>

                {{-- 3. ⭐ PAYROLL & PAYABLE UNITS --}}
                <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #ffffff; border: 1px solid #EAECF0; border-left: 5px solid {{ $payableDay >= 1.0 ? '#12B76A' : ($payableDay > 0 ? '#F79009' : '#D92D20') }} !important;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
                        <div class="font-weight-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Payroll & Payable Units
                        </div>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            @if($isProcessedInPayroll)
                                <span class="badge" style="background: #ECFDF3; color: #027A48; border: 1px solid #A6F4C7; font-size: 11px; padding: 4px 10px; border-radius: 8px;">
                                    <i class="fas fa-check-double mr-1"></i> PROCESSED IN PAYROLL
                                </span>
                            @else
                                <span class="badge" style="background: #F8F9FC; color: #475467; border: 1px solid #D0D5DD; font-size: 11px; padding: 4px 10px; border-radius: 8px;">
                                    <i class="fas fa-clock mr-1"></i> PENDING PAYROLL CYCLE
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-3 px-4">
                        <div class="row align-items-center">
                            {{-- Payable Day Metric --}}
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="p-3 rounded text-center" style="background: {{ $payableDay >= 1.0 ? '#F6FEF9' : ($payableDay > 0 ? '#FFFAEB' : '#FEF3F2') }}; border: 1px solid {{ $payableDay >= 1.0 ? '#A6F4C7' : ($payableDay > 0 ? '#FEDF89' : '#FECDCA') }}; border-radius: 12px;">
                                    <div class="text-muted font-weight-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Payable Units</div>
                                    <div class="font-weight-bold mt-1" style="font-size: 24px; color: {{ $payableDay >= 1.0 ? '#027A48' : ($payableDay > 0 ? '#B54708' : '#B42318') }};">
                                        {{ number_format($payableDay, 1) }} Day
                                    </div>
                                    <div class="font-weight-bold mt-1" style="font-size: 11.5px; color: {{ $payableDay >= 1.0 ? '#027A48' : ($payableDay > 0 ? '#B54708' : '#B42318') }};">
                                        @if($payableDay >= 1.0)
                                            <i class="fas fa-check-circle mr-1"></i> Full Paid (1.0 Payable Unit)
                                        @elseif($payableDay > 0)
                                            <i class="fas fa-adjust mr-1"></i> Half Day (0.5 Payable Unit)
                                        @else
                                            <i class="fas fa-times-circle mr-1"></i> Unpaid / LWP (0.0 Payable Unit)
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Payroll Impact Explanation --}}
                            <div class="col-md-8">
                                <div class="p-3 rounded h-100" style="background: #F9FAFB; border: 1px solid #EAECF0; border-radius: 12px; font-size: 12.5px;">
                                    <div class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 10.5px; letter-spacing: 0.3px;">Payroll Calculation Policy:</div>
                                    <div class="text-dark font-weight-bold" style="line-height: 1.45;">
                                        @if($statusCode === 'missed_punch' || $isMissed)
                                            @if($approvedReg)
                                                Missed punch regularized and approved. Calculated as <strong>1.0 Payable Day (Full Paid)</strong>.
                                            @else
                                                Unregularized missed punch is designated as <strong>LWP (0.0 Payable Day)</strong> subject to regularization approval.
                                            @endif
                                        @elseif($statusCode === 'half_day')
                                            Half Day working logged. Calculated as <strong>0.5 Payable Day</strong> for monthly payroll processing.
                                        @elseif($statusCode === 'present')
                                            Standard working requirements completed. Calculated as <strong>1.0 Payable Day (Full Paid)</strong>.
                                        @elseif($statusCode === 'lwp' || $statusCode === 'absent')
                                            Marked as <strong>{{ strtoupper($statusCode) }}</strong>. Designated as <strong>Unpaid (0.0 Payable Day)</strong>.
                                        @elseif($statusCode === 'leave')
                                            Leave recorded and reconciled against approved employee leave balance.
                                        @elseif($statusCode === 'holiday' || $statusCode === 'week_off')
                                            Scheduled non-working day ({{ $statusLabel }}). Full day payable unit credited per company policy.
                                        @else
                                            {{ $payrollExplanation ?: 'Standard payroll attendance resolution applied.' }}
                                        @endif
                                    </div>
                                    @if($payrollProcessedAt)
                                    <div class="text-muted mt-2 pt-2 border-top" style="font-size: 11px;">
                                        <i class="fas fa-calendar-check text-success mr-1"></i> Reconciled in payroll run on: <strong>{{ $payrollProcessedAt }}</strong>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. ⭐ POLICY VIOLATION & COMPLIANCE DIAGNOSTICS --}}
                <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #ffffff; border: 1px solid #EAECF0;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="font-weight-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-shield-alt text-primary mr-2"></i> Punctuality & Policy Diagnostics
                        </div>
                        <div class="small font-weight-bold text-muted">{{ $attDateFormatted }}</div>
                    </div>
                    <div class="card-body p-3 px-4">
                        
                        {{-- MISSED PUNCH DIAGNOSIS BANNER --}}
                        @if($isMissed || $statusCode === 'missed_punch')
                        <div class="p-3 mb-3 rounded" style="background: #FFF6ED; border: 1px solid #FDCF9E; border-left: 5px solid #E04F16; border-radius: 12px;">
                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2" style="gap: 8px;">
                                <div class="font-weight-bold" style="color: #B93815; font-size: 13px;">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Missed Punch Policy Flag
                                </div>
                                @if($approvedReg)
                                    <span class="badge bg-success text-white" style="font-size: 11px; padding: 4px 10px; border-radius: 6px;">
                                        <i class="fas fa-check-circle mr-1"></i> Regularization Approved
                                    </span>
                                @elseif($pendingReg)
                                    <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 10px; border-radius: 6px;">
                                        <i class="fas fa-clock mr-1"></i> Regularization Pending Approval
                                    </span>
                                @else
                                    <span class="badge" style="background: #E04F16; color: #fff; font-size: 11px; padding: 4px 10px; border-radius: 6px;">
                                        <i class="fas fa-times-circle mr-1"></i> Regularization Required
                                    </span>
                                @endif
                            </div>

                            <div class="text-dark font-weight-bold" style="font-size: 12.5px; line-height: 1.45;">
                                <strong>Violation Trigger Reason:</strong> 
                                {{ $attendance->missed_punch_reason ?: ($attendance->status_reason ?: 'Punch out was recorded after the allowed shift cutoff window, or punch out was missing.') }}
                            </div>

                            <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap" style="font-size: 11.5px; color: #475467; border-color: rgba(224, 79, 22, 0.25) !important; gap: 8px;">
                                <div>
                                    <span><strong>Punch In:</strong> {{ $punchInStr }}</span> &bull; 
                                    <span><strong>Punch Out:</strong> {{ $punchOutStr }}</span> &bull; 
                                    <span><strong>Shift End:</strong> {{ $shiftEndTime }}</span>
                                </div>
                                <div>
                                    @if($violationsList->count() > 0)
                                        <span class="text-danger font-weight-bold"><i class="fas fa-gavel mr-1"></i> Policy Action: Converted to LWP (Unpaid)</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- PUNCH BLOCKED BANNER --}}
                        @if($isBlocked || $statusCode === 'punch_blocked')
                        <div class="p-3 mb-3 rounded" style="background: #FEF3F2; border: 1px solid #FECDCA; border-left: 5px solid #D92D20; border-radius: 12px;">
                            <div class="font-weight-bold text-danger mb-1" style="font-size: 13px;">
                                <i class="fas fa-ban mr-1"></i> Punch Blocked by System Policy
                            </div>
                            <div class="text-dark font-weight-bold" style="font-size: 12.5px;">
                                {{ $attendance->blocked_reason ?: ($attendance->auto_block_reason ?: ($attendance->block_reason ?: 'Punching blocked due to policy breach or consecutive violations.')) }}
                            </div>
                            @if($attendance->auto_blocked_at)
                            <div class="mt-1 text-muted" style="font-size: 11px;">
                                Blocked At: <strong>{{ \Carbon\Carbon::parse($attendance->auto_blocked_at)->format('d M Y, h:i A') }}</strong>
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- PUNCTUALITY STATUS & EMPLOYEE PUNCH NOTES --}}
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="p-3 border rounded h-100" style="background: #ffffff; border-radius: 12px; border-color: #EAECF0 !important;">
                                    <div class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10.5px; letter-spacing: 0.3px;">Punctuality Indicators:</div>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        @if($isLate)
                                            <span class="flag flag-late mr-1 mb-1" style="font-size: 11px; padding: 4px 10px;">
                                                <i class="fas fa-clock mr-1"></i> Late by {{ $lateMinutes > 0 ? $lateMinutes.'m' : 'mark' }}
                                            </span>
                                        @endif
                                        @if($isEarly)
                                            <span class="flag flag-early mr-1 mb-1" style="font-size: 11px; padding: 4px 10px;">
                                                <i class="fas fa-sign-out-alt mr-1"></i> Early Out {{ $earlyMinutes > 0 ? $earlyMinutes.'m' : '' }}
                                            </span>
                                        @endif
                                        @if($isMissed)
                                            <span class="flag flag-missed mr-1 mb-1" style="font-size: 11px; padding: 4px 10px;">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Missed Punch
                                            </span>
                                        @endif
                                        @if($isBlocked)
                                            <span class="flag flag-blocked mr-1 mb-1" style="font-size: 11px; padding: 4px 10px;">
                                                <i class="fas fa-ban mr-1"></i> Punch Blocked
                                            </span>
                                        @endif
                                        @if(!$isLate && !$isEarly && !$isMissed && !$isBlocked)
                                            <span class="flag flag-clear mr-1 mb-1" style="font-size: 11px; padding: 4px 10px;">
                                                <i class="fas fa-check mr-1"></i> Compliant (No Violations)
                                            </span>
                                        @endif
                                        @if($violationCount > 0)
                                            <span class="badge bg-danger text-white font-weight-bold mr-1 mb-1" style="font-size: 10.5px; padding: 4px 10px; border-radius: 999px;">
                                                Violation #{{ $violationCount }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="p-3 border rounded h-100" style="background: #ffffff; border-radius: 12px; border-color: #EAECF0 !important;">
                                    <div class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10.5px; letter-spacing: 0.3px;">Employee Punch Notes:</div>
                                    <div style="font-size: 12px; color: #344054; line-height: 1.5;">
                                        <div><strong>Punch In Note:</strong> {{ $attendance->punch_in_note ?: '-' }}</div>
                                        <div class="mt-1"><strong>Punch Out Note:</strong> {{ $attendance->punch_out_note ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 5. ⭐ ADMINISTRATIVE OVERRIDE & EDIT AUDIT --}}
                @if($isEditedByAdmin)
                <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #ffffff; border-left: 5px solid var(--orb-primary, #4B00E8) !important; border: 1px solid #EAECF0;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
                        <div class="font-weight-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-user-edit text-primary mr-2"></i> Administrative Override & Modification Audit
                        </div>
                        <span class="badge" style="background: #F4F2FF; color: #4B00E8; border: 1px solid #D9D6FE; font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 700;">
                            <i class="fas fa-pen mr-1"></i> MANUALLY MODIFIED
                        </span>
                    </div>
                    <div class="card-body p-3 px-4">
                        <div class="row" style="font-size: 12.5px;">
                            <div class="col-md-4 mb-2">
                                <span class="text-muted font-weight-bold" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">Modified By:</span>
                                <div class="font-weight-bold text-dark mt-1">
                                    <i class="fas fa-user-shield text-primary mr-1"></i> {{ $updatedBy }}
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <span class="text-muted font-weight-bold" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">Modification Timestamp:</span>
                                <div class="font-weight-bold text-dark mt-1">
                                    <i class="fas fa-clock text-muted mr-1"></i> {{ $updatedAtStr }}
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <span class="text-muted font-weight-bold" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">Modification Type:</span>
                                <div class="font-weight-bold text-dark mt-1">
                                    <span class="badge bg-light text-dark border">{{ $updateCategory }}</span>
                                </div>
                            </div>
                            
                            {{-- Reason for Manual Edit --}}
                            <div class="col-12 mt-2">
                                <div class="p-3 rounded" style="background: #F9FAFB; border: 1px solid #EAECF0; border-radius: 10px;">
                                    <div class="text-primary font-weight-bold text-uppercase" style="font-size: 10.5px; letter-spacing: 0.3px;">
                                        <i class="fas fa-comment-dots mr-1"></i> Reason for Modification / Remarks:
                                    </div>
                                    <div class="text-dark font-weight-bold mt-1" style="font-size: 13px; line-height: 1.45;">
                                        {{ $updateReason ?: 'Attendance record manually modified by administrator.' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Change Logs Timeline (From attendance_daily_status_logs) --}}
                        @if(!empty($statusLogsList) && $statusLogsList->count() > 0)
                        <div class="mt-3 pt-3 border-top">
                            <div class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10.5px;">Status Modification History:</div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" style="font-size: 12px; background: #fff;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>Changed Status</th>
                                            <th>Modified By</th>
                                            <th>Reason / Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($statusLogsList as $log)
                                        <tr>
                                            <td>{{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : '-' }}</td>
                                            <td>
                                                <span class="badge bg-light text-muted border">{{ strtoupper($log->old_status ?? 'N/A') }}</span>
                                                <i class="fas fa-arrow-right text-muted mx-1" style="font-size: 10px;"></i>
                                                <span class="badge bg-primary text-white">{{ strtoupper($log->new_status ?? 'N/A') }}</span>
                                            </td>
                                            <td class="font-weight-bold text-dark">{{ optional($log->createdBy)->name ?? 'Administrator' }}</td>
                                            <td>{{ $log->remarks ?: '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- 6. REGULARIZATION DETAILS (IF APPLIED) --}}
                @if(!empty($regList) && $regList->count() > 0)
                <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #ffffff; border: 1px solid #EAECF0;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="font-weight-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-file-signature text-info mr-2"></i> Attendance Regularization Request
                        </div>
                        @php
                            $firstReg = $regList->first();
                            $regStatusBadge = match($firstReg->status ?? '') {
                                'approved' => 'bg-success text-white',
                                'rejected' => 'bg-danger text-white',
                                default => 'bg-warning text-dark'
                            };
                        @endphp
                        <span class="badge {{ $regStatusBadge }}" style="font-size: 11px; padding: 4px 10px; border-radius: 6px;">
                            {{ strtoupper($firstReg->status ?? 'pending') }}
                        </span>
                    </div>
                    <div class="card-body p-3 px-4" style="font-size: 12.5px;">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <span class="text-muted">Regularization Type:</span>
                                <div class="font-weight-bold text-dark mt-1">{{ ucwords(str_replace('_', ' ', $firstReg->regularization_type ?? 'General Regularization')) }}</div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <span class="text-muted">Requested Timings:</span>
                                <div class="font-weight-bold text-dark mt-1">
                                    {{ $firstReg->requested_punch_in ? $firstReg->requested_punch_in->format('h:i A') : '-' }} to 
                                    {{ $firstReg->requested_punch_out ? $firstReg->requested_punch_out->format('h:i A') : '-' }}
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <span class="text-muted">Actioned By:</span>
                                <div class="font-weight-bold text-dark mt-1">{{ optional($firstReg->approvedBy)->name ?? '-' }}</div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="p-2 bg-light rounded text-dark" style="border-radius: 8px;">
                                    <strong>Employee Reason:</strong> {{ $firstReg->reason ?: '-' }}
                                    @if($firstReg->admin_remarks)
                                        <div class="mt-1 pt-1 border-top text-muted">
                                            <strong>Admin Remarks:</strong> {{ $firstReg->admin_remarks }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 7. LEAVE CONTEXT (IF LINKED TO LEAVE REQUEST) --}}
                @if($attendance->leaveRequest)
                <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #ffffff; border: 1px solid #EAECF0;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="font-weight-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-calendar-minus text-info mr-2"></i> Linked Leave Request Details
                        </div>
                        <span class="badge bg-info text-white font-weight-bold" style="font-size: 10.5px;">
                            {{ strtoupper($attendance->leaveRequest->status ?? 'APPROVED') }}
                        </span>
                    </div>
                    <div class="card-body p-3 px-4" style="font-size: 12.5px;">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <span class="text-muted">Leave Type:</span>
                                <div class="font-weight-bold text-dark mt-1">{{ optional($attendance->leaveRequest->leaveType)->name ?? 'General Leave' }}</div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <span class="text-muted">Leave Duration:</span>
                                <div class="font-weight-bold text-dark mt-1">{{ $attendance->leaveRequest->days_count ?? 1 }} Day(s) {{ $attendance->leaveRequest->is_half_day ? '('.ucwords($attendance->leaveRequest->half_day_session ?? 'Half Day').')' : '' }}</div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <span class="text-muted">Paid / Unpaid:</span>
                                <div class="font-weight-bold text-dark mt-1">
                                    <span class="badge {{ optional($attendance->leaveRequest->leaveType)->is_paid ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ optional($attendance->leaveRequest->leaveType)->is_paid ? 'Paid Leave' : 'Unpaid (LWP)' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-12 mt-1">
                                <div class="p-2 bg-light rounded text-dark" style="border-radius: 8px;">
                                    <strong>Leave Reason:</strong> {{ $attendance->leaveRequest->reason ?: 'No reason specified' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 8. WORK REPORT LOGS (IF SUBMITTED) --}}
                @php
                    $firstLog = $attendance->workLogs->first();
                @endphp
                @if($firstLog)
                <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #ffffff; border: 1px solid #EAECF0;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="font-weight-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-tasks text-primary mr-2"></i> Daily Work Report Summary
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
                <div class="text-muted" style="font-size: 11.5px;">
                    <i class="fas fa-info-circle mr-1"></i> Attendance ID: <strong>#{{ $attendance->id }}</strong> &bull; Date: <strong>{{ $attDateFormatted }}</strong>
                </div>
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
