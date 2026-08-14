@php
$employee = $dashboard['employee'] ?? ($employee ?? null);
$cards = $dashboard['cards'] ?? [];
$actions = $dashboard['quick_actions'] ?? [];
$attendance = $dashboard['attendance_self'] ?? [];
$month = $attendance['month'] ?? [];
$announcements = $dashboard['latest_announcements'] ?? collect();
$recentAttendance = $attendance['recent'] ?? collect();
$todayRecord = $attendance['today'] ?? ($attendanceRecord ?? null);
$hasPunchedIn = !empty($todayRecord->punch_in_time);
$hasPunchedOut = !empty($todayRecord->punch_out_time);
$existingWorkMode = strtolower($todayRecord->work_mode ?? 'wfo');
@endphp

@if (empty($only_modals))
<div class="dash-page">
    <div class="dash-wrap">
        <div class="dash-hero">
            <div class="dash-hero-inner">
                <div>
                    <div class="dash-eyebrow">Employee Self Service</div>
                    <h1 class="dash-title">My Dashboard</h1>
                    <p class="dash-subtitle">Welcome back, {{ $dashboard['user_name'] ?? auth()->user()->name }}. Your
                        personal HRMS summary is ready.</p>
                </div>
                <div class="dash-hero-metrics">
                    <div class="dash-mini">
                        <span>Employee Code</span>
                        <strong>{{ $employee->employee_code ?? '-' }}</strong>
                    </div>
                    <div class="dash-mini">
                        <span>Profile</span>
                        <strong>{{ $employee->profile_completion ?? 0 }}%</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-grid">
            @foreach ($cards as $card)
            <div class="dash-card">
                <div class="dash-card-top">
                    <div>
                        <div class="dash-card-label">{{ $card['label'] }}</div>
                        <div class="dash-card-value">{{ $card['value'] }}</div>
                    </div>
                    <div class="dash-icon"><i class="{{ $card['icon'] }}"></i></div>
                </div>
                <div class="dash-card-sub">{{ $card['subtitle'] }}</div>
            </div>
            @endforeach
        </div>

        @if (!empty($actions))
        <div class="dash-section">
            <h2 class="dash-section-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="dash-actions">
                @php $hasDashActions = false; @endphp
                @foreach ($actions as $action)
                @php
                $url = $action['url'] ?? '';
                $title = trim($action['title'] ?? $action['label'] ?? '');
                @endphp

                @if($url && $url !== '#' && $title !== '')
                @php $hasDashActions = true; @endphp
                <a href="{{ $url }}" class="dash-action">
                    <div class="dash-action-icon"><i class="{{ $action['icon'] ?? 'fas fa-link' }}"></i></div>
                    <strong>{{ $title }}</strong>
                    <span>Open {{ strtolower($title) }}</span>
                </a>
                @endif
                @endforeach

                @if(!$hasDashActions)
                <div class="dash-empty" style="grid-column: 1 / -1; padding: 20px; border: 1px dashed #e2e8f0; border-radius: 12px; text-align: center; color: #64748b; font-weight: 600;">
                    No quick actions available
                </div>
                @endif
            </div>
        </div>
        @endif

        <div class="dash-section dash-two">
            <div>
                <h2 class="dash-section-title"><i class="fas fa-fingerprint"></i> Today's Attendance</h2>
                <div class="dash-panel">
                    <div class="dash-stat-list">
                        <div class="dash-stat">
                            <span>Status</span>
                            @php
                            $partStatus = $attendance['today_status'] ?? 'Not Marked';
                            if (strtolower((string) $partStatus) === 'lwp') {
                            $partStatus = 'Absent';
                            }
                            @endphp
                            <strong>{{ $partStatus }}</strong>
                        </div>
                        <div class="dash-stat"><span>Punch</span><strong
                                style="font-size:16px;">{{ $attendance['punch_summary'] ?? '-- to --' }}</strong></div>
                        <div class="dash-stat"><span>Work
                                Mode</span><strong>{{ strtoupper($attendance['today']->work_mode ?? '-') }}</strong>
                        </div>
                        <div class="dash-stat">
                            <span>Shift Left</span>
                            <strong>
                                @if (($attendance['remaining_shift_minutes'] ?? null) !== null)
                                {{ $attendance['remaining_shift_minutes'] }}m
                                @else
                                -
                                @endif
                            </strong>
                        </div>
                    </div>

                    @php
                    $canWebPunch = optional($employee)->canUseWebAttendance();
                    @endphp

                    @if (!$canWebPunch)
                    <div class="alert alert-warning border-0 shadow-sm mt-3 mb-0" style="border-radius: 12px; background: #fff8e6; border-left: 4px solid #f59e0b !important;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle text-warning fa-2x mr-3"></i>
                            <div>
                                <strong style="color: #854d0e; font-size: 14px;">Web Attendance Disabled</strong>
                                <p class="mb-0 text-muted small">Web Attendance is disabled for your account. Please contact HR Administrator.</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mt-3 text-center">
                        @if (!$hasPunchedIn)
                        <button type="button" class="btn btn-success btn-block font-weight-bold py-2 shadow-sm" data-toggle="modal" data-target="#webPunchInModal">
                            <i class="fas fa-sign-in-alt mr-2"></i> Punch In (Web)
                        </button>
                        @elseif (!$hasPunchedOut)
                        <button type="button" class="btn btn-danger btn-block font-weight-bold py-2 shadow-sm" data-toggle="modal" data-target="#webPunchOutModal">
                            <i class="fas fa-sign-out-alt mr-2"></i> Punch Out & Daily Work Report
                        </button>
                        @else
                        <div class="badge badge-success p-2 w-100 font-weight-bold" style="font-size: 14px;">
                            <i class="fas fa-check-circle mr-1"></i> Attendance Completed Today
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <div>
                <h2 class="dash-section-title"><i class="fas fa-calendar-alt"></i> This Month</h2>
                <div class="dash-panel">
                    <div class="dash-stat-list">
                        <div class="dash-stat"><span>Present</span><strong>{{ $month['present'] ?? 0 }}</strong></div>
                        <div class="dash-stat"><span>Absent</span><strong>{{ $month['absent'] ?? 0 }}</strong></div>
                        <div class="dash-stat"><span>Half Day</span><strong>{{ $month['half_day'] ?? 0 }}</strong>
                        </div>
                        <div class="dash-stat"><span>Late</span><strong>{{ $month['late'] ?? 0 }}</strong></div>
                    </div>
                </div>

                @php
                $violationSummary = app(\App\Services\HRMS\Attendance\AttendanceS::class)->getEmployeeViolationSummary($employee);
                @endphp
                <div class="dash-panel mt-3">
                    <h2 class="dash-section-title" style="font-size: 15px; font-weight: 700;"><i class="fas fa-exclamation-triangle text-warning"></i> Violation Cycles (Unconsumed)</h2>
                    <div class="dash-stat-list">
                        <div class="dash-stat">
                            <span>Attendance Discipline</span>
                            <strong>{{ $violationSummary['discipline']['count'] }} / {{ $violationSummary['discipline']['limit'] }}</strong>
                            <small class="text-muted d-block" style="font-size: 11px; margin-top: 2px;">Late: {{ $violationSummary['discipline']['late'] }} | Early: {{ $violationSummary['discipline']['early'] }}</small>
                        </div>
                        <div class="dash-stat">
                            <span>Missed Punch</span>
                            <strong>{{ $violationSummary['missed_punch']['count'] }} / {{ $violationSummary['missed_punch']['limit'] }}</strong>
                            <small class="text-muted d-block" style="font-size: 11px; margin-top: 2px;">Allowed: {{ $violationSummary['missed_punch']['allowed'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-section dash-three">
            <div class="dash-panel">
                <h2 class="dash-section-title"><i class="fas fa-plane-departure"></i> Leave Balance</h2>
                <div class="dash-stat-list">
                    <div class="dash-stat"><span>Paid</span><strong>{{ $dashboard['leave_self']['paid_remaining'] ?? 0 }}</strong>
                    </div>
                    <div class="dash-stat"><span>Sick</span><strong>{{ $dashboard['leave_self']['sick_remaining'] ?? 0 }}</strong>
                    </div>
                    <div class="dash-stat"><span>Comp Off</span><strong>{{ $dashboard['leave_self']['comp_off_remaining'] ?? 0 }}</strong>
                    </div>
                    <div class="dash-stat">
                        <span>Pending</span><strong>{{ $dashboard['leave_self']['pending'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>

            <div class="dash-panel">
                <h2 class="dash-section-title"><i class="fas fa-folder-open"></i> My Documents</h2>
                <div class="dash-stat-list">
                    <div class="dash-stat">
                        <span>Pending</span><strong>{{ $dashboard['documents_self']['pending'] ?? 0 }}</strong>
                    </div>
                    <div class="dash-stat">
                        <span>Verified</span><strong>{{ $dashboard['documents_self']['verified'] ?? 0 }}</strong>
                    </div>
                    <div class="dash-stat">
                        <span>Rejected</span><strong>{{ $dashboard['documents_self']['rejected'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>

            <div class="dash-panel">
                <h2 class="dash-section-title"><i class="fas fa-user-tie"></i> Reporting</h2>
                <p class="mb-2"><span
                        class="dash-pill blue">{{ $employee->manager_name ?? 'No manager assigned' }}</span></p>
                <p class="mb-0 text-muted">{{ $employee->department_name ?? 'Department not assigned' }} @if (!empty($employee->designation_name))
                    / {{ $employee->designation_name }}
                    @endif
                </p>
            </div>
        </div>

        <div class="dash-section dash-two">
            <div>
                <h2 class="dash-section-title"><i class="fas fa-history"></i> Recent Attendance</h2>
                <div class="dash-panel table-responsive">
                    @if (count($recentAttendance))
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Punch In</th>
                                <th>Punch Out</th>
                                <th>Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentAttendance as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->attendance_date)->format('d M Y') }}</td>
                                <td><span
                                        class="dash-pill green">{{ $row->type_name ?? ucfirst(str_replace('_', ' ', $row->type_code ?? 'Marked')) }}</span>
                                </td>
                                <td>{{ $row->punch_in_time ?? '-' }}</td>
                                <td>{{ $row->punch_out_time ?? '-' }}</td>
                                <td>{{ round(($row->total_work_minutes ?? 0) / 60, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="dash-empty">No attendance records yet.</div>
                    @endif
                </div>
            </div>

            <div>
                <h2 class="dash-section-title"><i class="fas fa-bullhorn"></i> Latest Announcements</h2>
                <div class="dash-panel">
                    @if (count($announcements))
                    @foreach ($announcements as $announcement)
                    <div class="mb-3 pb-3" style="border-bottom:1px solid #F1F3F8;">
                        <strong>{{ $announcement->title }}</strong>
                        <p class="mb-1 text-muted">
                            {{ \Illuminate\Support\Str::limit($announcement->description, 90) }}
                        </p>
                        <span
                            class="dash-pill">{{ $announcement->created_at ? \Carbon\Carbon::parse($announcement->created_at)->diffForHumans() : '-' }}</span>
                    </div>
                    @endforeach
                    @else
                    <div class="dash-empty">No announcements yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Web Punch In Modal --}}
<div class="modal fade" id="webPunchInModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 450px; margin: 1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
            <form method="POST" action="{{ route('attendances.clock-in') }}" id="webPunchInForm">
                @csrf
                <input type="hidden" name="latitude" id="punch_in_lat">
                <input type="hidden" name="longitude" id="punch_in_lng">
                <input type="hidden" name="browser" id="punch_in_browser">
                <input type="hidden" name="os" id="punch_in_os">
                <input type="hidden" name="gps_status" id="punch_in_gps">

                <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important; border: none;">
                    <h5 class="modal-title font-weight-bold m-0"><i class="fas fa-fingerprint mr-2"></i> Punch In</h5>
                    <button type="button" class="close text-white opacity-10" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    @if (session('error') || session('danger'))
                    <div class="alert alert-danger border-0 mb-3" style="border-radius: 12px; background: #fef2f2; border-left: 4px solid #ef4444 !important; font-size: 13px; font-weight: 700; color: #991b1b;">
                        <i class="fas fa-exclamation-circle text-danger mr-1"></i> {{ session('error') ?? session('danger') }}
                    </div>
                    @endif
                    @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger border-0 mb-3" style="border-radius: 12px; background: #fef2f2; border-left: 4px solid #ef4444 !important; font-size: 13px; font-weight: 700; color: #991b1b;">
                        <i class="fas fa-exclamation-triangle text-danger mr-1"></i> {{ $errors->first() }}
                    </div>
                    @endif

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted">Work Mode</label>
                        <select name="work_mode" id="web_work_mode_select" onchange="handleWorkModeChange(this.value)" class="form-control" style="border-radius: 12px; height: 44px;">
                            <option value="wfo">Working From Office (WFO)</option>
                            <option value="wfh">Working From Home (WFH)</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted">Punch In Note (Optional)</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Add optional remarks..." style="border-radius: 12px;"></textarea>
                    </div>
                    <div class="p-3 mb-3 border rounded-3" id="locationStatusIn" style="border-radius: 14px; background: #f8fafc !important; border: 1px solid #e2e8f0 !important; font-size: 13px; font-weight: 600;">
                        <i class="fas fa-location-arrow text-primary mr-1"></i> Location verification will trigger on Punch In.
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-0">
                    <button type="button" class="btn btn-light font-weight-bold px-3" data-dismiss="modal" style="border-radius: 12px;">Cancel</button>
                    <button type="submit" class="btn font-weight-bold px-4" style="border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important; color: #fff !important; border: none; height: 42px;"><i class="fas fa-check mr-1"></i> Confirm Punch In</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Web Punch Out & Daily Work Report Modal (Mobile App Flow Alignment) --}}
<div class="modal fade" id="webPunchOutModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width: 720px; margin: 1.5rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden; background: #f8fafc;">
            <form method="POST" action="{{ route('attendances.clock-out') }}" id="webPunchOutForm">
                @csrf
                <input type="hidden" name="work_mode" id="punch_out_work_mode" value="{{ $existingWorkMode }}">
                <input type="hidden" name="latitude" id="punch_out_lat">
                <input type="hidden" name="longitude" id="punch_out_lng">
                <input type="hidden" name="browser" id="punch_out_browser">
                <input type="hidden" name="os" id="punch_out_os">
                <input type="hidden" name="gps_status" id="punch_out_gps">

                {{-- Modal Header --}}
                <div class="modal-header p-4 text-white position-relative" style="background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%) !important; border: none;">
                    <div class="d-flex align-items-center">
                        <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(255,255,255,0.2); border-radius: 16px; backdrop-filter: blur(8px);">
                            <i class="fas fa-fingerprint fa-2x text-white"></i>
                        </div>
                        <div>
                            <h4 class="font-weight-bold mb-1 text-white" style="font-size: 20px; letter-spacing: -0.3px;">Daily Task Update</h4>
                            <p class="mb-0 text-white-50 small font-weight-semibold">Work Mode: {{ strtoupper($existingWorkMode) }} | Punch out based on assigned shift policy</p>
                        </div>
                    </div>
                    <button type="button" class="close text-white opacity-10 position-absolute" data-dismiss="modal" style="top: 20px; right: 24px; font-size: 28px; text-shadow: none;"><span>&times;</span></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4" style="max-height: 72vh; overflow-y: auto;">

                    {{-- 1. Task / Module Name --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                <i class="fas fa-terminal"></i>
                            </div>
                            <div>
                                <label class="font-weight-bold text-dark mb-0 d-block" style="font-size: 14px;">Task / Module Name <span class="text-danger">*</span></label>
                                <span class="text-muted small">Specify the active project, task or module name</span>
                            </div>
                        </div>
                        <input type="text" name="task_name" class="form-control font-weight-medium" required placeholder="Example: Attendance Punch Out Flow" style="border-radius: 12px; border: 1.5px solid #cbd5e1; font-size: 14px; padding: 10px 14px;">
                    </div>

                    {{-- 2. Today Work Description --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div>
                                <label class="font-weight-bold text-dark mb-0 d-block" style="font-size: 14px;">Today Work Description <span class="text-danger">*</span></label>
                                <span class="text-muted small">Describe the main updates and accomplishments</span>
                            </div>
                        </div>
                        <textarea name="today_work_description" class="form-control font-weight-medium" rows="3" required placeholder="Write what you worked on today..." style="border-radius: 12px; border: 1.5px solid #cbd5e1; font-size: 14px; padding: 10px 14px;"></textarea>
                    </div>

                    {{-- 3. Current Status --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div>
                                <label class="font-weight-bold text-dark mb-0 d-block" style="font-size: 14px;">Current Status <span class="text-danger">*</span></label>
                                <span class="text-muted small">Select the current progress state</span>
                            </div>
                        </div>
                        <input type="hidden" name="current_status" id="web_current_status" value="Progress">
                        <div class="row no-gutters mt-2" id="statusPillGroup">
                            <div class="col-3 pr-1">
                                <button type="button" class="btn btn-block py-2 px-2 status-pill-btn active" data-status="Progress" onclick="selectStatusPill('Progress')" style="border-radius: 12px; font-weight: 800; font-size: 13px; border: 2px solid #3b82f6; background: #eff6ff; color: #2563eb;">
                                    <i class="far fa-clock mr-1"></i> Progress
                                </button>
                            </div>
                            <div class="col-3 px-1">
                                <button type="button" class="btn btn-block py-2 px-2 status-pill-btn" data-status="Testing" onclick="selectStatusPill('Testing')" style="border-radius: 12px; font-weight: 700; font-size: 13px; border: 1.5px solid #e2e8f0; background: #fff; color: #475569;">
                                    <i class="fas fa-flask mr-1"></i> Testing
                                </button>
                            </div>
                            <div class="col-3 px-1">
                                <button type="button" class="btn btn-block py-2 px-2 status-pill-btn" data-status="Done" onclick="selectStatusPill('Done')" style="border-radius: 12px; font-weight: 700; font-size: 13px; border: 1.5px solid #e2e8f0; background: #fff; color: #475569;">
                                    <i class="far fa-check-circle mr-1"></i> Done
                                </button>
                            </div>
                            <div class="col-3 pl-1">
                                <button type="button" class="btn btn-block py-2 px-2 status-pill-btn" data-status="Blocked" onclick="selectStatusPill('Blocked')" style="border-radius: 12px; font-weight: 700; font-size: 13px; border: 1.5px solid #e2e8f0; background: #fff; color: #475569;">
                                    <i class="fas fa-ban mr-1"></i> Blocked
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Requirement Checklist --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div>
                                    <label class="font-weight-bold text-dark mb-0 d-block" style="font-size: 14px;">Requirement Checklist <span class="text-danger">*</span></label>
                                    <span class="text-muted small">Track individual tasks and project requirements</span>
                                </div>
                            </div>
                            <span class="badge px-3 py-2 font-weight-bold" id="reqCompletedCounter" style="border-radius: 20px; background: #f3e8ff; color: #7c3aed; font-size: 12px;">Completed 0 / 1</span>
                        </div>

                        <div id="requirementListContainer" class="mt-2">
                            <div class="d-flex align-items-center mb-2 req-item-row" id="reqRow_0">
                                <div class="custom-control custom-checkbox mr-2">
                                    <input type="checkbox" class="custom-control-input req-checkbox" id="reqCheck_0" onchange="updateReqCounter()">
                                    <label class="custom-control-label" for="reqCheck_0"></label>
                                </div>
                                <input type="text" name="requirements[]" class="form-control req-text-input" placeholder="Requirement / completed point..." style="border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 13px;" oninput="updateReqCounter()">
                                <button type="button" class="btn btn-link text-danger ml-2 p-0" onclick="removeReqRow(0)" style="font-size: 18px; text-decoration: none;">&times;</button>
                            </div>
                        </div>

                        <button type="button" class="btn btn-link font-weight-bold p-0 mt-1 d-inline-flex align-items-center" onclick="addRequirementRow()" style="color: #7c3aed; font-size: 13px; text-decoration: none;">
                            <i class="fas fa-plus mr-1"></i> Add Requirement
                        </button>
                    </div>

                    {{-- 5. Test Status (Optional - Multi-select Checkboxes) --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div>
                                <label class="font-weight-bold text-dark mb-0 d-block" style="font-size: 14px;">Test Status</label>
                                <span class="text-muted small">Verify if changes are tested and/or completed (Select all that apply)</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="custom-control custom-checkbox mr-4">
                                <input type="checkbox" id="testStatusTested" name="test_status[]" value="Tested" class="custom-control-input">
                                <label class="custom-control-label font-weight-bold text-dark" for="testStatusTested" style="font-size: 13px; cursor: pointer;">Tested</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" id="testStatusCompleted" name="test_status[]" value="Completed" class="custom-control-input">
                                <label class="custom-control-label font-weight-bold text-dark" for="testStatusCompleted" style="font-size: 13px; cursor: pointer;">Completed</label>
                            </div>
                        </div>
                    </div>

                    {{-- 6. Issues / Blockers --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                <i class="fas fa-bug"></i>
                            </div>
                            <div>
                                <label class="font-weight-bold text-dark mb-0 d-block" style="font-size: 14px;">Issues / Blockers</label>
                                <span class="text-muted small">Optional: list system bugs or blockers (one per line)</span>
                            </div>
                        </div>
                        <textarea name="issues_blockers" class="form-control" rows="2" placeholder="Optional: Write issues one per line..." style="border-radius: 12px; border: 1.5px solid #cbd5e1; font-size: 13px; padding: 10px 14px;"></textarea>
                    </div>

                    {{-- 7. Additional Notes --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                <i class="fas fa-comment-alt"></i>
                            </div>
                            <div>
                                <label class="font-weight-bold text-dark mb-0 d-block" style="font-size: 14px;">Additional Notes</label>
                                <span class="text-muted small">Optional: add any comments or remarks</span>
                            </div>
                        </div>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes..." style="border-radius: 12px; border: 1.5px solid #cbd5e1; font-size: 13px; padding: 10px 14px;"></textarea>
                    </div>

                    <div class="p-3 mb-1 border" id="locationStatusOut" style="border-radius: 14px; background: #f8fafc !important; border: 1px solid #e2e8f0 !important; font-size: 13px; font-weight: 600;">
                        @if ($existingWorkMode === 'wfo')
                            <i class="fas fa-location-arrow text-danger mr-1"></i> Location verification will trigger on Punch Out (WFO).
                        @else
                            <i class="fas fa-home text-info mr-1"></i> Work From Home (WFH) active. No location verification required.
                        @endif
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer p-3 border-0 bg-white shadow-lg" style="border-top: 1px solid #f1f5f9 !important;">
                    <button type="button" class="btn btn-light font-weight-bold px-4" data-dismiss="modal" style="border-radius: 12px; height: 44px;">Cancel</button>
                    <button type="submit" class="btn text-white font-weight-bold px-5 shadow-lg" style="border-radius: 12px; height: 44px; background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%) !important; border: none; font-size: 15px;">
                        <i class="fas fa-paper-plane mr-2"></i> Submit & Punch Out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let reqCount = 1;

    const statusThemes = {
        'Progress': {
            bg: '#eff6ff',
            color: '#2563eb',
            border: '2px solid #3b82f6'
        },
        'Testing': {
            bg: '#f3e8ff',
            color: '#7c3aed',
            border: '2px solid #8b5cf6'
        },
        'Done': {
            bg: '#dcfce7',
            color: '#15803d',
            border: '2px solid #22c55e'
        },
        'Blocked': {
            bg: '#fee2e2',
            color: '#b91c1c',
            border: '2px solid #ef4444'
        }
    };

    function selectStatusPill(status) {
        const hiddenInput = document.getElementById('web_current_status');
        if (hiddenInput) hiddenInput.value = status;
        document.querySelectorAll('.status-pill-btn').forEach(btn => {
            const s = btn.getAttribute('data-status');
            if (s === status) {
                const theme = statusThemes[s] || {
                    bg: '#f3e8ff',
                    color: '#7c3aed',
                    border: '2px solid #7c3aed'
                };
                btn.style.background = theme.bg;
                btn.style.color = theme.color;
                btn.style.border = theme.border;
                btn.style.fontWeight = '800';
                btn.classList.add('active');
            } else {
                btn.style.background = '#ffffff';
                btn.style.color = '#475569';
                btn.style.border = '1.5px solid #e2e8f0';
                btn.style.fontWeight = '700';
                btn.classList.remove('active');
            }
        });
    }

    function updateReqCounter() {
        const rows = document.querySelectorAll('.req-item-row');
        let completed = 0;
        rows.forEach(row => {
            const chk = row.querySelector('.req-checkbox');
            const text = row.querySelector('.req-text-input');
            if (chk && chk.checked && text && text.value.trim() !== '') {
                completed++;
            }
        });
        const counterEl = document.getElementById('reqCompletedCounter');
        if (counterEl) {
            counterEl.innerText = `Completed ${completed} / ${rows.length}`;
        }
    }

    function addRequirementRow() {
        const container = document.getElementById('requirementListContainer');
        if (!container) return;
        const rowId = reqCount++;
        const div = document.createElement('div');
        div.className = 'd-flex align-items-center mb-2 req-item-row';
        div.id = `reqRow_${rowId}`;
        div.innerHTML = `
        <div class="custom-control custom-checkbox mr-2">
            <input type="checkbox" class="custom-control-input req-checkbox" id="reqCheck_${rowId}" onchange="updateReqCounter()">
            <label class="custom-control-label" for="reqCheck_${rowId}"></label>
        </div>
        <input type="text" name="requirements[]" class="form-control req-text-input" placeholder="Requirement / completed point..." style="border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 13px;" oninput="updateReqCounter()">
        <button type="button" class="btn btn-link text-danger ml-2 p-0" onclick="removeReqRow(${rowId})" style="font-size: 18px; text-decoration: none;">&times;</button>
    `;
        container.appendChild(div);
        updateReqCounter();
    }

    function removeReqRow(id) {
        const row = document.getElementById(`reqRow_${id}`);
        if (row) {
            row.remove();
            updateReqCounter();
        }
    }

    function detectBrowserOS() {
        const ua = navigator.userAgent;
        let browser = "Unknown Browser";
        let os = "Unknown OS";

        if (ua.indexOf("Win") !== -1) os = "Windows";
        else if (ua.indexOf("Mac") !== -1) os = "MacOS";
        else if (ua.indexOf("Linux") !== -1) os = "Linux";
        else if (ua.indexOf("Android") !== -1) os = "Android";
        else if (ua.indexOf("like Mac") !== -1) os = "iOS";

        if (ua.indexOf("Chrome") !== -1) browser = "Chrome";
        else if (ua.indexOf("Safari") !== -1) browser = "Safari";
        else if (ua.indexOf("Firefox") !== -1) browser = "Firefox";
        else if (ua.indexOf("Edge") !== -1) browser = "Edge";

        return {
            browser: browser,
            os: os
        };
    }

    function handleWorkModeChange(mode) {
        const statusEl = document.getElementById('locationStatusIn');
        if (!statusEl) return;
        if (mode === 'wfo') {
            statusEl.innerHTML = '<i class="fas fa-building text-primary mr-1"></i> Work From Office (WFO) Selected. GPS location will be verified on Punch In.';
        } else {
            statusEl.innerHTML = '<i class="fas fa-home text-info mr-1"></i> Work From Home (WFH) Selected. No Office GPS validation required.';
        }
    }

    function requestGPSLocation(latId, lngId, browserId, osId, gpsId, statusId, callback) {
        const info = detectBrowserOS();
        const browserEl = document.getElementById(browserId);
        const osEl = document.getElementById(osId);
        const statusEl = document.getElementById(statusId);

        if (browserEl) browserEl.value = info.browser;
        if (osEl) osEl.value = info.os;

        if (!navigator.geolocation) {
            const gpsEl = document.getElementById(gpsId);
            if (gpsEl) gpsEl.value = 'Unsupported';
            if (statusEl) statusEl.innerHTML = '<span class="text-danger font-weight-bold"><i class="fas fa-times-circle mr-1"></i> Your browser does not support Location Services.</span>';
            if (callback) callback(false);
            return;
        }

        if (statusEl) statusEl.innerHTML = '<i class="fas fa-spinner fa-spin text-primary mr-1"></i> Getting your location... Please click Allow in browser popup.';

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                const latEl = document.getElementById(latId);
                const lngEl = document.getElementById(lngId);
                const gpsEl = document.getElementById(gpsId);
                if (latEl) latEl.value = pos.coords.latitude;
                if (lngEl) lngEl.value = pos.coords.longitude;
                if (gpsEl) gpsEl.value = 'Granted';
                if (statusEl) statusEl.innerHTML = '<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Location verified.</span>';
                if (callback) callback(true);
            },
            function(err) {
                const gpsEl = document.getElementById(gpsId);
                let msg = '<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Location error. Please try again.</span>';

                if (err.code === 1) { // PERMISSION_DENIED
                    if (gpsEl) gpsEl.value = 'Denied';
                    msg = '<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> <strong>Location Permission Required:</strong> Location access has been denied. Please enable location permission for this website from your browser settings and try again.</span>';
                } else if (err.code === 2 || err.code === 3) { // POSITION_UNAVAILABLE or TIMEOUT
                    if (gpsEl) gpsEl.value = 'Disabled';
                    msg = '<span class="text-warning font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> <strong>GPS Disabled:</strong> Please enable Location Services on your device.</span>';
                }

                if (statusEl) statusEl.innerHTML = msg;
                if (callback) callback(false);
            }, {
                enableHighAccuracy: true,
                timeout: 12000,
                maximumAge: 0
            }
        );
    }

    document.addEventListener('DOMContentLoaded', function() {
        const punchInForm = document.getElementById('webPunchInForm');
        if (punchInForm) {
            punchInForm.addEventListener('submit', function(e) {
                const selectEl = document.getElementById('web_work_mode_select');
                const mode = selectEl ? selectEl.value : 'wfo';

                // WFH Flow: Skip browser location check, submit directly to backend WFH validation
                if (mode === 'wfh') {
                    return true;
                }

                const latVal = document.getElementById('punch_in_lat') ? document.getElementById('punch_in_lat').value : '';
                const lngVal = document.getElementById('punch_in_lng') ? document.getElementById('punch_in_lng').value : '';

                // If coordinates already captured, allow form submit
                if (latVal && lngVal && parseFloat(latVal) !== 0 && parseFloat(lngVal) !== 0) {
                    return true;
                }

                // WFO Flow: Intercept form submit, trigger native browser location permission popup automatically
                e.preventDefault();

                const submitBtn = punchInForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Verifying Location...';
                }

                requestGPSLocation('punch_in_lat', 'punch_in_lng', 'punch_in_browser', 'punch_in_os', 'punch_in_gps', 'locationStatusIn', function(success) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Confirm Punch In';
                    }
                    if (success) {
                        punchInForm.submit();
                    }
                });
            });
        }

        const punchOutForm = document.getElementById('webPunchOutForm');
        if (punchOutForm) {
            punchOutForm.addEventListener('submit', function(e) {
                const modeInput = document.getElementById('punch_out_work_mode');
                const mode = modeInput ? modeInput.value : 'wfo';

                // WFH Flow: Skip location check, submit directly to backend
                if (mode === 'wfh') {
                    return true;
                }

                const latVal = document.getElementById('punch_out_lat') ? document.getElementById('punch_out_lat').value : '';
                const lngVal = document.getElementById('punch_out_lng') ? document.getElementById('punch_out_lng').value : '';

                // If coordinates already captured, allow form submit
                if (latVal && lngVal && parseFloat(latVal) !== 0 && parseFloat(lngVal) !== 0) {
                    return true;
                }

                // WFO Flow: Intercept form submit, trigger native browser location permission popup automatically
                e.preventDefault();

                const submitBtn = punchOutForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Verifying Location...';
                }

                requestGPSLocation('punch_out_lat', 'punch_out_lng', 'punch_out_browser', 'punch_out_os', 'punch_out_gps', 'locationStatusOut', function(success) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Submit & Punch Out';
                    }
                    if (success) {
                        punchOutForm.submit();
                    }
                });
            });
        }

        if (window.jQuery) {
            $('#webPunchInModal').on('shown.bs.modal', function() {
                const selectEl = document.getElementById('web_work_mode_select');
                const mode = selectEl ? selectEl.value : 'wfo';
                handleWorkModeChange(mode);
            });

            $('#webPunchOutModal').on('shown.bs.modal', function() {
                const modeInput = document.getElementById('punch_out_work_mode');
                const mode = modeInput ? modeInput.value : 'wfo';
                const statusEl = document.getElementById('locationStatusOut');
                const latVal = document.getElementById('punch_out_lat') ? document.getElementById('punch_out_lat').value : '';
                const lngVal = document.getElementById('punch_out_lng') ? document.getElementById('punch_out_lng').value : '';

                if (mode === 'wfo' && (!latVal || !lngVal || parseFloat(latVal) === 0 || parseFloat(lngVal) === 0)) {
                    requestGPSLocation('punch_out_lat', 'punch_out_lng', 'punch_out_browser', 'punch_out_os', 'punch_out_gps', 'locationStatusOut', null);
                } else if (mode !== 'wfo' && statusEl) {
                    statusEl.innerHTML = '<i class="fas fa-home text-info mr-1"></i> Work From Home (WFH) Active. No Office GPS validation required.';
                }
            });

            @if(session('error') || session('danger') || (isset($errors) && $errors->any()))
                @if(!empty($hasPunchedIn) && empty($hasPunchedOut))
                    $('#webPunchOutModal').modal('show');
                @else
                    $('#webPunchInModal').modal('show');
                @endif
            @endif
        }
    });
</script>