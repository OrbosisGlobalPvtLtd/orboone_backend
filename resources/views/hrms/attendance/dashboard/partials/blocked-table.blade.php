        @if($blockedRows->count() > 0)
        <div class="orb-table-card att-block-card" style="border-color: #FED7AA;">
            <div class="orb-table-toolbar justify-content-between align-items-center">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-user-lock"></i> Punch-In Blocked Employees</h5>
                    <div class="att-section-subtitle">Employees auto-blocked after 11:15 AM because they did not punch in.</div>
                </div>
                <div class="att-block-summary">
                    <span class="att-mini-stat">Total: {{ $blockedRows->count() }}</span>
                    <span class="att-mini-stat">Pending Unlock: {{ $blockedRows->where('is_admin_unlocked', false)->count() }}</span>
                </div>
            </div>
            <div class="orb-table-tools-bar eo-toolbar">
                <div id="blockedLengthBox" class="orb-table-length-box eo-toolbar-left"></div>
                <div id="blockedExportButtons" class="orb-table-export-buttons eo-toolbar-right"></div>
            </div>
            <div class="orb-table-wrap table-responsive">
                <table class="att-table att-block-table table mb-0" id="blockedAttendanceTable" style="width:100% !important;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">S.No</th>
                                    <th>Employee</th>
                                    <th>Department / Designation</th>
                                    <th>Date</th>
                                    {{-- <th>Auto Blocked At</th> --}}
                                    <th>Block Reason</th>
                                    <th class="text-center">Regularization Request</th>
                                    <th class="text-center">Status</th>
                                    <th class="no-export text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($blockedRows as $blocked)
                                @php
                                $blockedDate = $blocked->attendance_date ? \Carbon\Carbon::parse($blocked->attendance_date)->format('d M Y') : '-';
                                $rawDate = $blocked->attendance_date ? \Carbon\Carbon::parse($blocked->attendance_date)->toDateString() : null;
                                $autoBlockedAt = $blocked->auto_blocked_at ? \Carbon\Carbon::parse($blocked->auto_blocked_at)->format('d M Y h:i A') : '-';
                                $isUnlocked = (bool) ($blocked->is_admin_unlocked ?? false);
                                $blockedTypeCode = optional($blocked->attendanceType)->code ?: ($blocked->attendance_status ?: 'default');
                                $blockedStatusLabel = $blockedTypeCode === 'punch_blocked' ? 'Punch Blocked' : (optional($blocked->attendanceType)->name ?? ucwords(str_replace('_', ' ', $blockedTypeCode)));

                                $regReq = $blocked->regularization_request ?? null;
                                if (!$regReq && $blocked->employee_id && $rawDate) {
                                    $regReq = \Illuminate\Support\Facades\DB::table('attendance_regularizations')
                                        ->where('employee_id', $blocked->employee_id)
                                        ->whereNull('deleted_at')
                                        ->where(function ($q) use ($rawDate, $blocked) {
                                            if (is_numeric($blocked->id)) {
                                                $q->where('attendance_id', $blocked->id);
                                            }
                                            $q->orWhereDate('requested_punch_in', $rawDate)
                                              ->orWhereDate('requested_punch_out', $rawDate)
                                              ->orWhereDate('created_at', $rawDate);
                                        })
                                        ->latest('id')
                                        ->first();
                                }
                                $hasReg = !empty($regReq);
                                $regStatus = $hasReg ? ($regReq->status ?? 'pending') : null;
                                @endphp
                                <tr class="att-row" data-emp-code="{{ optional($blocked->employee)->employee_code ?? 'N/A' }}" data-emp-name="{{ optional($blocked->user)->name ?? 'Employee' }}" data-department="{{ optional(optional($blocked->employee)->department)->name ?? 'N/A' }}" data-designation="{{ optional(optional($blocked->employee)->designation)->name ?? 'N/A' }}" data-date="{{ $blockedDate }}" data-reason="{{ $blocked->block_reason ?? $blocked->auto_block_reason ?? $blocked->blocked_reason ?? 'Auto blocked after 11:15 AM because employee did not punch in.' }}" data-reg="{{ $hasReg ? ('YES (' . ucwords(str_replace('_', ' ', $regStatus)) . ')') : 'NO' }}" data-status="{{ $blockedStatusLabel }} ({{ $isUnlocked ? 'Unlocked' : 'Pending Unlock' }})">
                                    <td class="text-center"><span class="font-weight-bold text-muted" style="font-size:12px;">{{ ($blockedAttendances instanceof \Illuminate\Pagination\AbstractPaginator ? ($blockedAttendances->currentPage() - 1) * $blockedAttendances->perPage() : 0) + $loop->iteration }}</span></td>
                                    <td>
                                        <div class="att-emp">
                                            @php
                                            $passportPhotoUrl = resolveEmployeePassportPhoto($blocked);
                                            $blockedName = optional($blocked->user)->name ?? 'Employee';
                                            $employeeInitial = resolveEmployeeInitials($blocked);
                                            @endphp
                                            <div class="att-avatar mr-2">
                                                @if($passportPhotoUrl)
                                                <img
                                                    src="{{ $passportPhotoUrl }}"
                                                    alt="{{ $blockedName }}"
                                                    onerror="this.style.setProperty('display', 'none', 'important'); this.nextElementSibling.style.setProperty('display', 'inline-flex', 'important');">
                                                <span style="display: none !important;">{{ $employeeInitial }}</span>
                                                @else
                                                <span>{{ $employeeInitial }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="att-emp-name" title="{{ optional($blocked->user)->name ?? 'N/A' }}">{{ optional($blocked->user)->name ?? 'N/A' }}</div>
                                                <div class="att-emp-code">{{ optional($blocked->employee)->employee_code ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ optional(optional($blocked->employee)->department)->name ?? 'N/A' }}</div>
                                        <div class="text-muted" style="font-size: 11px; margin-top: 2px;">{{ optional(optional($blocked->employee)->designation)->name ?? 'N/A' }}</div>
                                    </td>
                                    <td><span class="att-time">{{ $blockedDate }}</span></td>
                                    {{-- <td><span class="att-time">{{ $autoBlockedAt }}</span></td> --}}
                                    <td>
                                        <div class="att-small" style="max-width:220px;">{{ $blocked->block_reason ?? $blocked->auto_block_reason ?? $blocked->blocked_reason ?? 'Auto blocked after 11:15 AM because employee did not punch in.' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($hasReg)
                                            @php
                                                $badgeStyle = match($regStatus) {
                                                    'approved' => 'background: #DEF7EC; color: #03543F; border: 1px solid #BCF0DA;',
                                                    'rejected' => 'background: #FDE8E8; color: #9B1C1C; border: 1px solid #FBD5D5;',
                                                    'cancelled' => 'background: #F3F4F6; color: #374151; border: 1px solid #E5E7EB;',
                                                    default => 'background: #FEF08A; color: #713F12; border: 1px solid #FDE047;'
                                                };
                                            @endphp
                                            <span class="badge px-2.5 py-1" style="{{ $badgeStyle }} font-size: 11px; font-weight: 800; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-check-circle"></i> YES
                                            </span>
                                            <div class="text-capitalize text-muted font-weight-bold mt-1" style="font-size: 10px;">
                                                {{ ucwords(str_replace('_', ' ', $regStatus)) }}
                                            </div>
                                        @else
                                            <span class="badge px-2.5 py-1" style="background: #F3F4F6; color: #6B7280; border: 1px solid #E5E7EB; font-size: 11px; font-weight: 800; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-times-circle"></i> NO
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="att-badge badge-{{ $blockedTypeCode }}">{{ $blockedStatusLabel }}</span>
                                        @if($isUnlocked)
                                        <span class="flag-badge flag-unlock mt-1">Unlocked</span>
                                        @else
                                        <span class="flag-badge flag-block mt-1">Pending Unlock</span>
                                        @endif
                                        @if($blocked->unlock_type)
                                        <div class="att-small mt-1">{{ ucwords(str_replace('_', ' ', $blocked->unlock_type)) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($hasReg)
                                            <a href="{{ route('hrms.attendance.regularizations.index', ['employee_id' => $blocked->employee_id, 'from' => $rawDate, 'to' => $rawDate]) }}" 
                                               class="btn btn-sm btn-primary font-weight-bold shadow-sm" 
                                               style="border-radius: 10px; font-size: 11px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 5px;"
                                               title="View Regularization Request">
                                                <i class="fas fa-external-link-alt"></i> View Request
                                            </a>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-sm btn-light text-muted font-weight-bold border" 
                                                    style="border-radius: 10px; font-size: 11px; padding: 6px 12px; cursor: not-allowed; opacity: 0.7;" 
                                                    disabled 
                                                    title="No regularization request submitted by employee yet">
                                                <i class="fas fa-lock"></i> No Request
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
            </div>
            @if($blockedAttendances instanceof \Illuminate\Pagination\AbstractPaginator)
                {{ $blockedAttendances->links('vendor.pagination.orbo') }}
            @endif
        </div>
        @endif

