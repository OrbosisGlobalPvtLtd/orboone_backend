<div class="modal fade" id="viewModal{{ $attendance->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header" style="background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);">
                <div>
                    <h5 class="modal-title text-white"><i class="fas fa-eye mr-2"></i> Attendance Record Details</h5>
                    <p class="orb-modal-subtitle text-white-50 mb-0">Read-only view of employee attendance record.</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body orb-modal-body p-4">
                @php
                $isUnlocked = (bool) ($attendance->is_admin_unlocked || $attendance->unlocked_at || ($attendance->attendance_status ?? '') === 'unlocked');
                $attDateStr = $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->toDateString() : null;
                $todayStr = \Carbon\Carbon::now('Asia/Kolkata')->toDateString();
                $isPastDate = $attDateStr && $attDateStr < $todayStr;

                $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                $rawStatus = strtolower($attendance->attendance_status ?? '');
                if (empty($rawStatus) || $rawStatus === 'default') {
                    $rawStatus = $typeCode;
                }

                if (!$isUnlocked && $isPastDate) {
                    $statusCode = 'absent';
                    $statusLabel = '🔴 ABSENT';
                } elseif (empty($rawStatus) || $rawStatus === 'unlocked' || $rawStatus === 'present' || ($isUnlocked && empty($attendance->attendance_status))) {
                    $statusCode = 'present';
                    $statusLabel = 'Present';
                } elseif ($rawStatus === 'absent' || $rawStatus === 'lwp') {
                    $statusCode = 'absent';
                    $statusLabel = '🔴 ABSENT';
                } elseif ($rawStatus === 'half_day') {
                    $statusCode = 'half_day';
                    $statusLabel = 'Half Day';
                } elseif ($rawStatus === 'missed_punch') {
                    $statusCode = 'missed_punch';
                    $statusLabel = 'Missed Punch';
                } elseif ($rawStatus === 'leave') {
                    $statusCode = 'leave';
                    $statusLabel = 'Leave';
                } elseif ($rawStatus === 'holiday') {
                    $statusCode = 'holiday';
                    $statusLabel = 'Holiday';
                } elseif ($rawStatus === 'week_off') {
                    $statusCode = 'week_off';
                    $statusLabel = 'Week Off';
                } elseif ($rawStatus === 'punch_blocked') {
                    $statusCode = 'punch_blocked';
                    $statusLabel = 'Punch Blocked';
                } else {
                    $statusCode = $rawStatus;
                    $statusLabel = optional($attendance->attendanceType)->name ?? ucwords(str_replace('_', ' ', $rawStatus));
                }
                $attDate = $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y (l)') : '-';

                $displayReason = $attendance->block_reason ?? $attendance->auto_block_reason ?? $attendance->blocked_reason;
                if (empty($displayReason)) {
                    if ($isUnlocked) {
                        $displayReason = 'Unlocked by HR';
                    } elseif ($statusCode === 'missed_punch' || $attendance->missed_punch) {
                        $displayReason = 'Missed Punch (Out Time Pending)';
                    } else {
                        $displayReason = 'Punch-in blocked after cutoff time';
                    }
                }
                @endphp

                <!-- Employee & Date Overview -->
                <div class="card border-0 bg-light mb-3" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center">
                                    @php
                                    $passportPhotoUrl = resolveEmployeePassportPhoto($attendance->employee ?? $attendance);
                                    $employeeName = optional($attendance->user)->name ?? 'Employee';
                                    $employeeInitial = resolveEmployeeInitials($attendance->employee ?? $attendance);
                                    @endphp
                                    <span class="hrms-emp-avatar hrms-emp-avatar-md mr-3">
                                        @if($passportPhotoUrl)
                                        <img src="{{ $passportPhotoUrl }}" alt="{{ $employeeName }}" class="hrms-emp-avatar-img">
                                        @else
                                        <span class="hrms-emp-avatar-fallback is-visible">{{ $employeeInitial }}</span>
                                        @endif
                                    </span>
                                    <div>
                                        <h6 class="mb-1 font-weight-bold" style="font-size: 15px; color: #1E293B;">
                                            {{ optional($attendance->user)->name ?? 'N/A' }}
                                        </h6>
                                        <div class="small text-muted">
                                            Code: <strong>{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</strong>
                                            | Dept: <strong>{{ optional(optional($attendance->employee)->department)->name ?? 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 text-md-right mt-2 mt-md-0">
                                <div class="small text-muted">Attendance Date</div>
                                <div class="font-weight-bold" style="font-size: 14px; color: #334155;">{{ $attDate }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Details Grid -->
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded h-100" style="background: #F8FAFC; border-radius: 10px;">
                            <div class="small text-muted font-weight-bold text-uppercase mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Attendance Status</div>
                            <div>
                                <span class="att-badge badge-{{ $statusCode }}" style="font-size: 12px; padding: 6px 12px;">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded h-100" style="background: #F8FAFC; border-radius: 10px;">
                            <div class="small text-muted font-weight-bold text-uppercase mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Blocked Status</div>
                            <div>
                                <span class="att-badge badge-{{ $isUnlocked ? 'unlocked' : 'punch_blocked' }}" style="font-size: 12px; padding: 6px 12px;">
                                    {{ $isUnlocked ? '🔓 UNLOCKED' : 'PUNCH BLOCKED' }}
                                </span>
                                @if($isUnlocked && $attendance->unlocked_at)
                                <div class="small text-muted mt-2">
                                    <i class="fas fa-check-circle text-success"></i> Unlocked {{ \Carbon\Carbon::parse($attendance->unlocked_at)->format('d M Y, h:i A') }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded h-100" style="background: #F8FAFC; border-radius: 10px;">
                            <div class="small text-muted font-weight-bold text-uppercase mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Punch In Time</div>
                            <div class="font-weight-bold" style="font-size: 14px; color: #1E293B;">
                                {{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : 'Not Punched' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded h-100" style="background: #F8FAFC; border-radius: 10px;">
                            <div class="small text-muted font-weight-bold text-uppercase mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Punch Out Time</div>
                            <div class="font-weight-bold" style="font-size: 14px; color: #1E293B;">
                                {{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : 'Not Punched' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="p-3 border rounded" style="background: #F8FAFC; border-radius: 10px;">
                            <div class="small text-muted font-weight-bold text-uppercase mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Block / Approval Reason</div>
                            <div class="{{ $isUnlocked ? 'text-success' : 'text-danger' }} font-weight-bold" style="font-size: 13px;">
                                {{ $displayReason }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer orb-modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
