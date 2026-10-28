        <div class="orb-table-card">
            <div class="orb-table-toolbar justify-content-between align-items-end flex-wrap gap-3">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-calendar-day"></i> Today's Attendance</h5>
                    <div class="att-section-subtitle">Daily attendance list for today only. Full history is available in Attendance Records.</div>
                </div>
                <div class="orb-filter-group align-items-center">
                    <input type="text" id="todayAttendanceSearch" class="att-search" placeholder="Search today attendance..." value="{{ request('search') }}">
                    <a href="{{ route('attendances.index') }}" class="orb-btn-light py-2 px-3 h-auto" style="min-height: 38px !important; border-radius: 12px !important;"><i class="fas fa-sync-alt"></i> Refresh</a>
                </div>
            </div>
            <div class="orb-table-tools-bar eo-toolbar">
                <div id="attendanceLengthBox" class="orb-table-length-box eo-toolbar-left"></div>
                <div id="attendanceExportButtons" class="orb-table-export-buttons eo-toolbar-right"></div>
            </div>
            <div class="orb-table-wrap table-responsive">
                <table class="att-table table mb-0" id="attendanceDataTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">S.No</th>
                            <th>Employee</th>
                            <!-- <th>Employee Code</th> -->
                            <th>Department</th>
                            <th>Mode</th>
                            <th>Shift</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Target Out</th>
                            <th>Gross</th>
                            <th>Net</th>
                            <th>Status</th>
                            <th>Flags</th>
                            <th class="no-export text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceRows as $attendance)
                        @php
                         $rawStatus = strtolower($attendance->attendance_status ?? '');
                         if (empty($rawStatus)) {
                             $rawStatus = optional($attendance->attendanceType)->code ?? 'default';
                         }
                         if ($rawStatus === 'absent') {
                             $typeCode = 'absent';
                             $statusName = '🔴 ABSENT';
                         } elseif ($rawStatus === 'lwp') {
                             $typeCode = 'lwp';
                             $statusName = '🔴 LWP';
                         } else {
                             $statusMap = [
                                 'present' => ['present', 'Present'],
                                 'half_day' => ['half_day', 'Half Day'],
                                 'absent' => ['absent', '🔴 ABSENT'],
                                 'lwp' => ['lwp', '🔴 LWP'],
                                 'missed_punch' => ['missed_punch', 'Missed Punch'],
                                 'leave' => ['leave', 'Leave'],
                                 'holiday' => ['holiday', 'Holiday'],
                                 'week_off' => ['week_off', 'Week Off'],
                                 'punch_blocked' => ['punch_blocked', 'Punch Blocked'],
                             ];
                             $mapped = $statusMap[$rawStatus] ?? null;
                             if ($mapped) {
                                 $typeCode = $mapped[0];
                                 $statusName = $mapped[1];
                             } else {
                                 $typeCode = optional($attendance->attendanceType)->code ?? 'default';
                                 $statusName = optional($attendance->attendanceType)->name ?? 'N/A';
                                 if ($typeCode === 'lwp') {
                                     $typeCode = 'lwp';
                                     $statusName = '🔴 LWP';
                                 }
                             }
                         }
                        $modeCode = strtolower($attendance->work_mode ?? '');
                        $modeLabel = $modeCode === 'wfh' ? 'WFH' : ($modeCode === 'wfo' ? 'WFO' : '-');
                        $modeClass = in_array($modeCode, ['wfo', 'wfh']) ? $modeCode : 'default';
                        $grossMinutes = (int) ($attendance->gross_work_minutes ?? 0);
                        $netMinutes = (int) ($attendance->total_work_minutes ?? 0);
                        $grossText = $grossMinutes > 0 ? floor($grossMinutes / 60).'h '.($grossMinutes % 60).'m' : ($attendance->gross_duration ?? '-');
                        $netText = $netMinutes > 0 ? floor($netMinutes / 60).'h '.($netMinutes % 60).'m' : ($attendance->net_duration ?? '-');
                        @endphp
                        <tr class="att-row" data-emp-code="{{ optional($attendance->employee)->employee_code ?? 'N/A' }}" data-emp-name="{{ optional($attendance->user)->name ?? 'Employee' }}" data-department="{{ optional(optional($attendance->employee)->department)->name ?? 'N/A' }}" data-mode="{{ $modeLabel }}" data-shift="{{ optional($attendance->attendanceTime)->name ?? '-' }}" data-punch-in="{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}" data-punch-out="{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}" data-target-out="{{ $attendance->target_punch_out_time ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A') : '-' }}" data-gross="{{ $grossText }}" data-net="{{ $netText }}" data-status="{{ $statusName }}" data-flags="{{ $attendance->is_late ? 'Late ' . ($attendance->late_minutes ?? 0) . 'm ' : '' }}{{ $attendance->is_early_out ? 'Early ' . ($attendance->early_out_minutes ?? 0) . 'm ' : '' }}{{ $attendance->missed_punch ? 'Missed ' : '' }}{{ ($attendance->is_blocked || $attendance->is_punch_blocked) ? 'Blocked ' : '' }}{{ $attendance->is_admin_unlocked ? 'Unlocked ' : '' }}{{ (!$attendance->is_late && !$attendance->is_early_out && !$attendance->missed_punch && !$attendance->is_blocked && !$attendance->is_punch_blocked) ? 'Clear' : '' }}">
                            <td class="text-center"><span class="font-weight-bold text-muted" style="font-size:12px;">{{ ($attendances instanceof \Illuminate\Pagination\AbstractPaginator ? ($attendances->currentPage() - 1) * $attendances->perPage() : 0) + $loop->iteration }}</span></td>
                            <td>
                                <div class="att-emp">
                                    @php
                                    $passportPhotoUrl = resolveEmployeePassportPhoto($attendance);
                                    $employeeName = optional($attendance->user)->name ?? 'Employee';
                                    $employeeInitial = resolveEmployeeInitials($attendance);
                                    @endphp
                                    <div class="att-avatar mr-2">
                                        @if($passportPhotoUrl)
                                        <img
                                            src="{{ $passportPhotoUrl }}"
                                            alt="{{ $employeeName }}"
                                            onerror="this.style.setProperty('display', 'none', 'important'); this.nextElementSibling.style.setProperty('display', 'inline-flex', 'important');">
                                        <span style="display: none !important;">{{ $employeeInitial }}</span>
                                        @else
                                        <span>{{ $employeeInitial }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="att-emp-name" title="{{ optional($attendance->user)->name ?? 'N/A' }}">{{ optional($attendance->user)->name ?? 'N/A' }}</div>
                                        <div class="att-emp-code">{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <!-- <td>{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</td> -->
                            <td>
                                <div class="att-small">{{ optional(optional($attendance->employee)->department)->name ?? 'N/A' }}</div>
                            </td>
                            <td><span class="mode-badge mode-{{ $modeClass }}">{{ $modeLabel }}</span></td>
                            <td>
                                <div class="att-small">{{ optional($attendance->attendanceTime)->name ?? '-' }}</div>
                            </td>
                            <td><span class="att-time">{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '-' }}</span></td>
                            <td><span class="att-time">{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '-' }}</span></td>
                            <td><span class="att-time">{{ $attendance->target_punch_out_time ? \Carbon\Carbon::parse($attendance->target_punch_out_time)->format('h:i A') : '-' }}</span></td>
                            <td><span class="att-small att-strong">{{ $grossText }}</span></td>
                            <td><span class="att-small att-strong">{{ $netText }}</span></td>
                            <td><span class="att-badge badge-{{ $typeCode }}">{{ $statusName }}</span></td>
                            <td>
                                @if($attendance->is_late)
                                <span class="flag-badge flag-late">Late {{ $attendance->late_minutes ?? 0 }}m</span>
                                @endif
                                @if($attendance->is_early_out)
                                <span class="flag-badge flag-early">Early {{ $attendance->early_out_minutes ?? 0 }}m</span>
                                @endif
                                @if($attendance->missed_punch ?? false)
                                <span class="flag-badge flag-missed">Missed</span>
                                @endif
                                @if(($attendance->is_blocked ?? false) || ($attendance->is_punch_blocked ?? false))
                                <span class="flag-badge flag-block">Blocked</span>
                                @endif
                                @if(($attendance->is_admin_unlocked ?? false))
                                <span class="flag-badge flag-unlock">Unlocked</span>
                                @endif
                                @if(!$attendance->is_late && !$attendance->is_early_out && !($attendance->missed_punch ?? false) && !($attendance->is_blocked ?? false) && !($attendance->is_punch_blocked ?? false))
                                <span class="flag-badge flag-clear">Clear</span>
                                @endif
                            </td>
                            <td>
                                <div class="att-action-wrap dropdown">
                                    <button class="action-dot" type="button" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                    <div class="dropdown-menu dropdown-menu-right att-action-menu">
                                        @if($canManageAttendance ?? false)
                                        <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $attendance->id }}"><i class="fas fa-edit text-primary"></i> Edit</button>
                                        @endif
                                        <a href="{{ route('attendances.daily', ['employee_id' => optional($attendance->employee)->id]) }}" class="dropdown-item"><i class="fas fa-eye text-info"></i> View Records</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($attendances instanceof \Illuminate\Pagination\AbstractPaginator)
                {{ $attendances->links('vendor.pagination.orbo') }}
            @endif
        </div>
