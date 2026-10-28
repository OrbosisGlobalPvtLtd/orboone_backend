        <!-- Employees Not Marked Attendance Today Table -->
        @if($unmarkedRows->count() > 0)
        <div class="orb-table-card att-unmarked-card mb-4" style="border-color: #FECACA; background: radial-gradient(circle at top right, rgba(239, 68, 68, .08), transparent 28%), #fff;">
            <div class="orb-table-toolbar justify-content-between align-items-center flex-wrap gap-3" style="background: linear-gradient(135deg, #FEF2F2, #fff); padding: 16px 18px; border-bottom: 1px solid #FEE2E2;">
                <div>
                    <h5 class="att-section-title text-danger mb-1">
                        <i class="fas fa-user-slash text-danger mr-2"></i> Employees Not Marked Attendance Today
                    </h5>
                    <div class="att-section-subtitle text-muted">
                        List of active employees who have not punched in / marked attendance today ({{ \Carbon\Carbon::parse($today ?? now())->format('d M Y') }}).
                    </div>
                </div>
                <div class="att-block-summary d-flex align-items-center gap-2">
                    <span class="att-mini-stat" style="border-color: #FCA5A5; color: #991B1B; background: #FEF2F2;">
                        <i class="fas fa-exclamation-circle mr-1"></i> Total Not Marked: {{ method_exists($unmarkedEmployees, 'total') ? $unmarkedEmployees->total() : count($unmarkedEmployees ?? []) }}
                    </span>
                </div>
            </div>
            <div class="orb-table-tools-bar eo-toolbar">
                <div id="unmarkedLengthBox" class="orb-table-length-box eo-toolbar-left"></div>
                <div id="unmarkedExportButtons" class="orb-table-export-buttons eo-toolbar-right"></div>
            </div>
            <div class="orb-table-wrap table-responsive">
                <table class="att-table table mb-0" id="unmarkedAttendanceTable" style="width:100% !important;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">S.No</th>
                                    <th>Employee</th>
                                    <th>Department / Designation</th>
                                    <th>Email / Contact</th>
                                    <th class="text-center">Today's Status</th>
                                    <th class="no-export text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unmarkedRows as $emp)
                                @php
                                    $todayAtt = $emp->today_attendance ?? null;
                                    $empUser = $emp->user;
                                    $empName = $empUser->name ?? $emp->display_name ?? 'Employee';
                                    $empCode = $emp->employee_code ?? 'N/A';
                                    $deptName = optional($emp->department)->name ?? 'N/A';
                                    $desigName = optional($emp->designation)->name ?? 'N/A';
                                    $email = $empUser->email ?? '-';
                                    $phone = optional($emp->user)->phone ?? $emp->phone ?? $emp->mobile_number ?? optional($emp->profile)->emergency_contact_number ?? '-';
                                    
                                    $statusBadgeClass = 'badge-danger';
                                    $statusText = 'Not Marked';
                                    
                                    if ($todayAtt) {
                                        if ($todayAtt->is_blocked || $todayAtt->is_punch_blocked || $todayAtt->attendance_status === 'punch_blocked') {
                                            $statusBadgeClass = 'badge-blocked';
                                            $statusText = 'Punch Blocked';
                                        } elseif (in_array($todayAtt->attendance_status, ['absent', 'lwp'], true)) {
                                            $statusBadgeClass = 'badge-danger';
                                            $statusText = 'Marked Absent';
                                        } elseif ($todayAtt->attendance_status === 'leave') {
                                            $statusBadgeClass = 'badge-info';
                                            $statusText = 'On Leave';
                                        } elseif ($todayAtt->attendance_status === 'holiday') {
                                            $statusBadgeClass = 'badge-secondary';
                                            $statusText = 'Holiday';
                                        } elseif ($todayAtt->attendance_status === 'week_off') {
                                            $statusBadgeClass = 'badge-secondary';
                                            $statusText = 'Week Off';
                                        }
                                    }
                                @endphp
                                <tr class="att-row" data-emp-code="{{ $empCode }}" data-emp-name="{{ $empName }}" data-department="{{ $deptName }}" data-designation="{{ $desigName }}" data-email="{{ $email }}" data-phone="{{ $phone !== '-' ? $phone : '-' }}" data-status="{{ $statusText }}">
                                    <td class="text-center"><span class="font-weight-bold text-muted" style="font-size:12px;">{{ ($unmarkedEmployees instanceof \Illuminate\Pagination\AbstractPaginator ? ($unmarkedEmployees->currentPage() - 1) * $unmarkedEmployees->perPage() : 0) + $loop->iteration }}</span></td>
                                    <td>
                                        <div class="att-emp">
                                            @php
                                            $passportPhotoUrl = resolveEmployeePassportPhoto($emp);
                                            $employeeInitial = resolveEmployeeInitials($emp);
                                            @endphp
                                            <div class="att-avatar mr-2">
                                                @if($passportPhotoUrl)
                                                <img
                                                    src="{{ $passportPhotoUrl }}"
                                                    alt="{{ $empName }}"
                                                    onerror="this.style.setProperty('display', 'none', 'important'); this.nextElementSibling.style.setProperty('display', 'inline-flex', 'important');">
                                                <span style="display: none !important;">{{ $employeeInitial }}</span>
                                                @else
                                                <span>{{ $employeeInitial }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="att-emp-name" title="{{ $empName }}">{{ $empName }}</div>
                                                <div class="att-emp-code">{{ $empCode }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $deptName }}</div>
                                        <div class="text-muted" style="font-size: 11px; margin-top: 2px;">{{ $desigName }}</div>
                                    </td>
                                    <td>
                                        <div class="att-small font-weight-bold text-dark">{{ $email }}</div>
                                        @if($phone && $phone !== '-')
                                        <div class="text-muted att-phone" style="font-size: 11px; margin-top: 2px;"><i class="fas fa-phone-alt mr-1"></i> {{ $phone }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="att-badge {{ $statusBadgeClass }} px-2.5 py-1" style="border-radius: 8px; font-size: 11px; font-weight: 800;">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('attendances.daily', ['employee_id' => $emp->id]) }}" 
                                           class="btn btn-sm btn-light border text-primary font-weight-bold shadow-sm" 
                                           style="border-radius: 10px; font-size: 11px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 5px;"
                                           title="View Attendance Records">
                                            <i class="fas fa-history"></i> Records
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
            </div>
            @if($unmarkedEmployees instanceof \Illuminate\Pagination\AbstractPaginator)
                {{ $unmarkedEmployees->links('vendor.pagination.orbo') }}
            @endif
        </div>
        @endif

