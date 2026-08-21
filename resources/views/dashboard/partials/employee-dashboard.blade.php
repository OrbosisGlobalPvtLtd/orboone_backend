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
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px; margin: 1.75rem auto;">
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
                    @php
                        $errorMsg = session('error') ?? session('danger') ?? (isset($errors) && $errors->any() ? $errors->first() : null);
                        $isEarlyLogin = $errorMsg && (
                            str_contains(strtolower($errorMsg), 'too early') || 
                            str_contains(strtolower($errorMsg), 'early') || 
                            str_contains(strtolower($errorMsg), 'window') || 
                            str_contains(strtolower($errorMsg), 'available')
                        );
                        $isBlocked = !empty($isPunchBlocked) || ($errorMsg && !$isEarlyLogin && (
                            str_contains(strtolower($errorMsg), 'closed') || 
                            str_contains(strtolower($errorMsg), 'blocked') || 
                            str_contains(strtolower($errorMsg), 'cutoff') || 
                            str_contains(strtolower($errorMsg), 'late') || 
                            str_contains(strtolower($errorMsg), 'lock') ||
                            str_contains(strtolower($errorMsg), 'failed')
                        ));
                    @endphp

                    @if ($errorMsg)
                        @if ($isEarlyLogin)
                            <div class="card border-0 mb-3 shadow-xs" style="border-radius: 18px; background: linear-gradient(135deg, #fffbe6 0%, #fff7ed 100%); border: 1.5px solid #fde047 !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="mr-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: #f59e0b; color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                                            <i class="fas fa-clock fa-lg"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="font-weight-bold m-0" style="font-size: 15px; color: #b45309;">
                                                    Attendance Window Currently Unavailable
                                                </h6>
                                                <span class="badge px-2 py-1" style="border-radius: 6px; font-size: 10px; font-weight: 800; background: #f59e0b; color: #ffffff;">
                                                    EARLY PUNCH
                                                </span>
                                            </div>
                                            <p class="font-weight-bold mb-0 mt-1" style="font-size: 13px; line-height: 1.4; color: #78350f;">
                                                {{ $errorMsg }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-end mt-3 gap-2">
                                        <button type="button" onclick="$('#webPunchInFormInputs').slideToggle(); $('#webPunchInSubmitBtn').toggle();" class="btn btn-outline-warning btn-sm font-weight-bold px-3" style="border-radius: 10px; font-size: 11.5px; padding: 6px 12px; color: #b45309; border-color: #f59e0b;">
                                            <i class="fas fa-redo mr-1"></i> Try Again / View Form
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="card border-0 mb-3 shadow-xs" style="border-radius: 18px; background: linear-gradient(135deg, #fff5f5 0%, #ffeef0 100%); border: 1.5px solid #fca5a5 !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="mr-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: #ef4444; color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                                            <i class="fas fa-user-lock fa-lg"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="font-weight-bold text-danger m-0" style="font-size: 15px;">
                                                    Attendance Punch Blocked
                                                </h6>
                                                <span class="badge badge-danger px-2 py-1" style="border-radius: 6px; font-size: 10px; font-weight: 800;">
                                                    NOT RECORDED
                                                </span>
                                            </div>
                                            <p class="text-dark font-weight-bold mb-0 mt-1" style="font-size: 13px; line-height: 1.4;">
                                                {{ $errorMsg }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="p-3 mt-2" style="background: rgba(255, 255, 255, 0.9); border-radius: 12px; border-left: 4px solid #ef4444; font-size: 12.5px; color: #334155; line-height: 1.5;">
                                        <p class="font-weight-bold mb-1 text-danger" style="font-size: 12px;">
                                            <i class="fas fa-info-circle mr-1"></i> Next Steps & Required Actions:
                                        </p>
                                        <ul class="pl-3 mb-0" style="font-weight: 600; font-size: 12px; color: #475569;">
                                            <li class="mb-1">Contact your <strong>HR Manager or Administrator</strong> to request an attendance unlock.</li>
                                            <li>Submit an <strong>Attendance Regularization</strong> request for your late entry or missed punch.</li>
                                        </ul>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mt-3 gap-2">
                                        @if(Route::has('hrms.attendance.regularizations.index'))
                                        <a href="{{ route('hrms.attendance.regularizations.index') }}" class="btn btn-warning btn-sm font-weight-bold text-white shadow-xs px-3" style="border-radius: 10px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; font-size: 12px; padding: 7px 12px;">
                                            <i class="fas fa-calendar-check mr-1"></i> Apply Regularization
                                        </a>
                                        @endif
                                        <button type="button" onclick="$('#webPunchInFormInputs').slideToggle(); $('#webPunchInSubmitBtn').toggle();" class="btn btn-outline-secondary btn-sm font-weight-bold px-3" style="border-radius: 10px; font-size: 11.5px; padding: 6px 12px;">
                                            <i class="fas fa-redo mr-1"></i> Try Again / View Form
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <div id="webPunchInFormInputs" style="{{ $errorMsg ? 'display: none;' : '' }}">
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
                </div>
                <div class="modal-footer bg-light p-3 border-0">
                    <button type="button" class="btn btn-light font-weight-bold px-3" data-dismiss="modal" style="border-radius: 12px;">Close</button>
                    <button type="submit" id="webPunchInSubmitBtn" class="btn font-weight-bold px-4" style="border-radius: 12px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important; color: #fff !important; border: none; height: 42px; {{ $errorMsg ? 'display: none;' : '' }}"><i class="fas fa-check mr-1"></i> Confirm Punch In</button>
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

                    @php
                        $assignedProjects = collect();
                        $scopeS = app(\App\Services\HRMS\ProjectManagement\ProjectAccessScopeS::class);
                        $empId = $scopeS->getOwnEmployeeId();
                        if ($empId) {
                            $projIds = $scopeS->getAccessibleProjectIds();
                            $assignedProjects = \Illuminate\Support\Facades\DB::table('projects')
                                ->whereIn('id', $projIds)
                                ->select('id', 'name')
                                ->orderBy('name')
                                ->get();
                        }
                    @endphp

                    <style>
                        /* Custom Task Checkbox Styling: Yellow border for pending (unticked), Green background & checkmark for done (ticked) */
                        .task-checkbox-custom {
                            width: 20px;
                            height: 20px;
                            cursor: pointer;
                            border-radius: 6px;
                            border: 2px solid #eab308 !important; /* Yellow border for pending */
                            background-color: #fffbeb !important; /* Light yellow tint */
                            appearance: none;
                            -webkit-appearance: none;
                            outline: none;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            transition: all 0.2s ease-in-out;
                            margin-right: 8px;
                            position: relative;
                            vertical-align: middle;
                            flex-shrink: 0;
                        }
                        .task-checkbox-custom:hover {
                            border-color: #d97706 !important;
                            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
                        }
                        .task-checkbox-custom:checked {
                            border-color: #16a34a !important; /* Green border for done */
                            background-color: #16a34a !important; /* Green background */
                            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
                        }
                        .task-checkbox-custom:checked::after {
                            content: '✓';
                            color: #ffffff;
                            font-size: 13px;
                            font-weight: 900;
                            line-height: 1;
                        }
                    </style>

                    {{-- Today's Work Section --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div>
                                    <label class="font-weight-bold text-dark mb-0 d-block" style="font-size: 15px;">Today's Work <span class="text-danger">*</span></label>
                                    <span class="text-muted small">Project-based daily work items & tasks</span>
                                </div>
                            </div>
                        </div>

                        <div id="projectBlocksContainer">
                            <!-- Project Block 0 -->
                            <div class="project-block-card p-3 mb-3 border rounded-lg bg-light" id="projectBlock_0" data-block-idx="0">
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                    <span class="font-weight-bold text-primary small project-block-header-title">
                                        <i class="fas fa-folder mr-1"></i> Project 1
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 remove-project-btn" onclick="removeProjectBlock(this)" style="border-radius: 6px; font-size: 13px; display: none;" title="Remove Project">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-dark mb-1">Project <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center">
                                        <select name="projects[0][project_id]" class="form-control form-control-sm project-select" onchange="toggleCustomProjectInput(0)" required style="border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; flex: 1;">
                                            <option value="">-- Select Project --</option>
                                            @foreach($assignedProjects as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                            <option value="custom">Custom</option>
                                        </select>
                                        <input type="text" name="projects[0][custom_project_name]" id="customProjectInput_0" class="form-control form-control-sm ml-2 custom-project-input" placeholder="Enter project or module name..." style="display: none; border-radius: 8px; border: 1.5px solid #cbd5e1; flex: 1;">
                                    </div>
                                </div>

                                <!-- Tasks Container for Project Block 0 -->
                                <div class="tasks-container mt-2" id="tasksContainer_0">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="d-flex align-items-center flex-wrap">
                                            <label class="small font-weight-bold text-dark mb-0 mr-1">Tasks / Work Items <span class="text-danger">*</span></label>
                                            <span class="badge badge-light border text-dark ml-2 d-inline-flex align-items-center" style="font-size: 10.5px; padding: 2px 6px; border-radius: 6px; background-color: #f8fafc;">
                                                <span style="display:inline-flex; align-items:center; justify-content:center; width:13px; height:13px; background:#16a34a; color:#fff; border-radius:3px; font-size:9px; font-weight:900; margin-right:4px;">✓</span> Completed
                                            </span>
                                            <span class="badge badge-light border text-dark ml-1 d-inline-flex align-items-center" style="font-size: 10.5px; padding: 2px 6px; border-radius: 6px; background-color: #f8fafc;">
                                                <span style="display:inline-flex; align-items:center; justify-content:center; width:13px; height:13px; border:1.5px solid #eab308; background:#fffbeb; border-radius:3px; margin-right:4px;"></span> Pending
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-link p-0 font-weight-bold" onclick="toggleQuickTaskBox(0)" style="color: #7c3aed; font-size: 11.5px; text-decoration: none;">
                                            <i class="fas fa-edit mr-1"></i> Quick Add Tasks
                                        </button>
                                    </div>

                                    <!-- Quick Multi-line Task Entry Box -->
                                    <div id="quickTaskBox_0" class="mb-2 p-2 border rounded bg-white shadow-xs" style="display: none; border-radius: 10px;">
                                        <div class="small font-weight-bold text-dark mb-1">
                                            <i class="fas fa-paste text-primary mr-1"></i> Enter one task per line...
                                        </div>
                                        <textarea id="quickTaskText_0" class="form-control form-control-sm mb-2" rows="3" placeholder="Attendance Module - Early Logout Fix&#10;Leave Approval UI&#10;Payroll Testing" style="border-radius: 8px; font-size: 12px; border: 1.5px solid #cbd5e1;"></textarea>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <button type="button" class="btn btn-xs font-weight-bold text-white py-1 px-3" onclick="processQuickTasks(0)" style="border-radius: 6px; font-size: 11px; background: #7c3aed; border: none;">
                                                <i class="fas fa-plus-circle mr-1"></i> Add Tasks
                                            </button>
                                            <button type="button" class="btn btn-xs btn-light font-weight-bold py-1 px-2 border" onclick="toggleQuickTaskBox(0)" style="border-radius: 6px; font-size: 11px; color: #64748b;">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Task Rows List -->
                                    <div id="taskRowsList_0">
                                        <div class="d-flex align-items-center mb-2 task-row" id="taskRow_0_0">
                                            <input type="hidden" name="projects[0][tasks][0][is_completed]" value="0">
                                            <input type="checkbox" name="projects[0][tasks][0][is_completed]" value="1" class="task-checkbox-custom task-checkbox" id="taskCheck_0_0" onchange="syncSelectAllCheckbox(0)" title="Unticked = Pending, Ticked = Completed">
                                            <input type="text" name="projects[0][tasks][0][task_name]" class="form-control form-control-sm task-name-input" placeholder="Task description..." required style="border-radius: 8px; border: 1.5px solid #cbd5e1;">
                                            <button type="button" class="btn btn-link text-danger ml-2 p-0 remove-task-btn" onclick="removeTaskRow(this)" style="font-size: 18px; text-decoration: none; display: none;" title="Remove Task">&times;</button>
                                        </div>
                                    </div>

                                    <div class="align-items-center justify-content-between mt-2 pt-1 border-top" id="tasksFooterRow_0" style="display: flex;">
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox" id="selectAllCheck_0" onchange="toggleSelectAllTasks(0, this.checked)" style="width: 17px; height: 17px; cursor: pointer; accent-color: #7c3aed; margin-right: 6px;" title="Mark All Completed">
                                            <label for="selectAllCheck_0" class="small font-weight-bold text-dark mb-0 cursor-pointer" style="font-size: 12px; user-select: none;" id="selectAllLabel_0">Mark All Completed</label>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light border font-weight-bold add-task-btn" id="addTaskBtn_0" onclick="addTaskRow(0)" style="border-radius: 8px; color: #7c3aed;">
                                            <i class="fas fa-plus mr-1"></i> Add Task
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold mt-1" onclick="addProjectBlock()" style="border-radius: 10px;">
                            <i class="fas fa-folder-plus mr-1"></i> Add Project
                        </button>
                    </div>

                    {{-- Single Overall Today's Work Status (After all projects, before blockers) --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div>
                                <label class="font-weight-bold text-dark mb-0 d-block" style="font-size: 14px;">Today's Work Status <span class="text-danger">*</span></label>
                                <span class="text-muted small">Overall status of the employee's work performed today</span>
                            </div>
                        </div>
                        <input type="hidden" name="today_work_status" id="web_today_work_status" value="in_progress">
                        <div class="row no-gutters mt-2" id="todayStatusPillGroup">
                            <div class="col-3 pr-1">
                                <button type="button" class="btn btn-block py-2 px-2 today-status-pill-btn active" data-status="in_progress" onclick="selectTodayStatusPill('in_progress')" style="border-radius: 12px; font-weight: 800; font-size: 13px; border: 2px solid #3b82f6; background: #eff6ff; color: #2563eb;">
                                    <i class="far fa-clock mr-1"></i> In Progress
                                </button>
                            </div>
                            <div class="col-3 px-1">
                                <button type="button" class="btn btn-block py-2 px-2 today-status-pill-btn" data-status="testing" onclick="selectTodayStatusPill('testing')" style="border-radius: 12px; font-weight: 700; font-size: 13px; border: 1.5px solid #e2e8f0; background: #fff; color: #475569;">
                                    <i class="fas fa-flask mr-1"></i> Testing
                                </button>
                            </div>
                            <div class="col-3 px-1">
                                <button type="button" class="btn btn-block py-2 px-2 today-status-pill-btn" data-status="completed" onclick="selectTodayStatusPill('completed')" style="border-radius: 12px; font-weight: 700; font-size: 13px; border: 1.5px solid #e2e8f0; background: #fff; color: #475569;">
                                    <i class="far fa-check-circle mr-1"></i> Completed
                                </button>
                            </div>
                            <div class="col-3 pl-1">
                                <button type="button" class="btn btn-block py-2 px-2 today-status-pill-btn" data-status="blocked" onclick="selectTodayStatusPill('blocked')" style="border-radius: 12px; font-weight: 700; font-size: 13px; border: 1.5px solid #e2e8f0; background: #fff; color: #475569;">
                                    <i class="fas fa-ban mr-1"></i> Blocked
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Issues / Blockers --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleCollapsibleSection('issuesBlockersBox', 'issuesToggleIcon')" style="user-select: none;">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                    <i class="fas fa-bug"></i>
                                </div>
                                <div>
                                    <label class="font-weight-bold text-dark mb-0 d-block cursor-pointer" style="font-size: 14px;">Issues / Blockers <span class="badge badge-light border text-muted ml-1" style="font-size: 10px; font-weight: 600;">Optional</span></label>
                                    <span class="text-muted small">Describe any issue, blocker or dependency...</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-light border text-primary mr-2" id="issuesBadgeText" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; display: none;">Added</span>
                                <i class="fas fa-chevron-down text-muted" id="issuesToggleIcon" style="transition: transform 0.2s; font-size: 14px;"></i>
                            </div>
                        </div>
                        <div id="issuesBlockersBox" class="mt-2 pt-2 border-top" style="display: none;">
                            <textarea name="issues_blockers" id="issues_blockers_input" class="form-control" rows="2" placeholder="Describe any issue, blocker or dependency..." style="border-radius: 12px; border: 1.5px solid #cbd5e1; font-size: 13px; padding: 10px 14px;" oninput="updateCollapsibleBadge('issues_blockers_input', 'issuesBadgeText')"></textarea>
                        </div>
                    </div>

                    {{-- Additional Notes --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleCollapsibleSection('additionalNotesBox', 'notesToggleIcon')" style="user-select: none;">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; border-radius: 10px; color: #7c3aed; font-weight: 800;">
                                    <i class="fas fa-comment-alt"></i>
                                </div>
                                <div>
                                    <label class="font-weight-bold text-dark mb-0 d-block cursor-pointer" style="font-size: 14px;">Additional Notes <span class="badge badge-light border text-muted ml-1" style="font-size: 10px; font-weight: 600;">Optional</span></label>
                                    <span class="text-muted small">Add any additional notes...</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-light border text-primary mr-2" id="notesBadgeText" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; display: none;">Added</span>
                                <i class="fas fa-chevron-down text-muted" id="notesToggleIcon" style="transition: transform 0.2s; font-size: 14px;"></i>
                            </div>
                        </div>
                        <div id="additionalNotesBox" class="mt-2 pt-2 border-top" style="display: none;">
                            <textarea name="remarks" id="remarks_input" class="form-control" rows="2" placeholder="Add any additional notes..." style="border-radius: 12px; border: 1.5px solid #cbd5e1; font-size: 13px; padding: 10px 14px;" oninput="updateCollapsibleBadge('remarks_input', 'notesBadgeText')"></textarea>
                        </div>
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
    let projectBlockIdx = 1;
    let taskIdxCounter = { 0: 1 };

    function toggleCollapsibleSection(boxId, iconId) {
        const box = document.getElementById(boxId);
        const icon = document.getElementById(iconId);
        if (!box) return;

        const isHidden = box.style.display === 'none';
        if (isHidden) {
            box.style.display = 'block';
            if (icon) icon.className = 'fas fa-chevron-up text-primary';
        } else {
            box.style.display = 'none';
            if (icon) icon.className = 'fas fa-chevron-down text-muted';
        }
    }

    function updateCollapsibleBadge(inputId, badgeId) {
        const input = document.getElementById(inputId);
        const badge = document.getElementById(badgeId);
        if (!input || !badge) return;
        if (input.value.trim().length > 0) {
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    function selectTodayStatusPill(status) {
        const hiddenInput = document.getElementById('web_today_work_status');
        if (hiddenInput) hiddenInput.value = status;
        document.querySelectorAll('.today-status-pill-btn').forEach(btn => {
            const s = btn.getAttribute('data-status');
            if (s === status) {
                const theme = todayStatusThemes[s] || { bg: '#f3e8ff', color: '#7c3aed', border: '2px solid #7c3aed' };
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

    function toggleSelectAllTasks(projIdx, isChecked) {
        const container = document.getElementById(`taskRowsList_${projIdx}`);
        if (!container) return;

        const checkboxes = container.querySelectorAll('.task-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = isChecked;
        });

        const label = document.getElementById(`selectAllLabel_${projIdx}`);
        if (label) {
            label.textContent = isChecked ? 'Mark All Pending' : 'Mark All Completed';
        }
    }

    function syncSelectAllCheckbox(projIdx) {
        const container = document.getElementById(`taskRowsList_${projIdx}`);
        const selectAllCb = document.getElementById(`selectAllCheck_${projIdx}`);
        const label = document.getElementById(`selectAllLabel_${projIdx}`);
        if (!container || !selectAllCb) return;

        const checkboxes = container.querySelectorAll('.task-checkbox');
        if (checkboxes.length === 0) return;

        let allChecked = true;
        checkboxes.forEach(cb => {
            if (!cb.checked) allChecked = false;
        });
        selectAllCb.checked = allChecked;
        if (label) {
            label.textContent = allChecked ? 'Mark All Pending' : 'Mark All Completed';
        }
    }

    function toggleQuickTaskBox(projIdx) {
        const box = document.getElementById(`quickTaskBox_${projIdx}`);
        const rowsList = document.getElementById(`taskRowsList_${projIdx}`);
        const footerRow = document.getElementById(`tasksFooterRow_${projIdx}`);
        const textarea = document.getElementById(`quickTaskText_${projIdx}`);
        if (!box) return;

        const isHidden = box.style.display === 'none';

        if (isHidden) {
            if (rowsList && textarea) {
                const currentTasks = [];
                rowsList.querySelectorAll('.task-name-input').forEach(input => {
                    const val = input.value.trim();
                    if (val) currentTasks.push(val);
                });
                textarea.value = currentTasks.join('\n');
            }

            box.style.display = 'block';
            if (rowsList) rowsList.style.setProperty('display', 'none', 'important');
            if (footerRow) footerRow.style.setProperty('display', 'none', 'important');
        } else {
            box.style.display = 'none';
            if (rowsList) rowsList.style.display = 'block';
            if (footerRow) footerRow.style.setProperty('display', 'flex', 'important');
        }
    }

    function processQuickTasks(projIdx) {
        const textarea = document.getElementById(`quickTaskText_${projIdx}`);
        if (!textarea) return;

        const rawText = textarea.value;
        const container = document.getElementById(`taskRowsList_${projIdx}`);
        if (!container) return;

        const existingStatusMap = {};
        container.querySelectorAll('.task-row').forEach(row => {
            const input = row.querySelector('.task-name-input');
            const check = row.querySelector('.task-checkbox');
            if (input && check) {
                const val = input.value.trim().toLowerCase();
                if (val) {
                    existingStatusMap[val] = check.checked;
                }
            }
        });

        // Filter out empty lines and prompt/instructional text
        const lines = rawText.split('\n')
            .map(l => l.trim())
            .filter(l => {
                if (l.length === 0) return false;
                const lower = l.toLowerCase();
                if (lower.includes('enter one task per line') || 
                    lower.includes('paste multiple tasks') || 
                    lower.includes('quick add tasks') || 
                    lower.includes('quick paste box')) {
                    return false;
                }
                return true;
            });

        if (lines.length === 0) {
            container.innerHTML = `
                <div class="d-flex align-items-center mb-2 task-row" id="taskRow_${projIdx}_0">
                    <input type="hidden" name="projects[${projIdx}][tasks][0][is_completed]" value="0">
                    <input type="checkbox" name="projects[${projIdx}][tasks][0][is_completed]" value="1" class="task-checkbox-custom task-checkbox" id="taskCheck_${projIdx}_0" onchange="syncSelectAllCheckbox(${projIdx})" title="Unticked = Pending, Ticked = Completed">
                    <input type="text" name="projects[${projIdx}][tasks][0][task_name]" class="form-control form-control-sm task-name-input" placeholder="Task description..." required style="border-radius: 8px; border: 1.5px solid #cbd5e1;">
                    <button type="button" class="btn btn-link text-danger ml-2 p-0 remove-task-btn" onclick="removeTaskRow(this)" style="font-size: 18px; text-decoration: none; display: none;" title="Remove Task">&times;</button>
                </div>
            `;
            taskIdxCounter[projIdx] = 1;
        } else {
            let rowsHtml = '';
            const uniqueLines = [];
            const seen = new Set();

            lines.forEach(line => {
                const lower = line.toLowerCase();
                if (!seen.has(lower)) {
                    seen.add(lower);
                    uniqueLines.push(line);
                }
            });

            uniqueLines.forEach((line, tIdx) => {
                const isCompleted = existingStatusMap[line.toLowerCase()] === true;
                const checkedAttr = isCompleted ? 'checked' : '';
                const valEscaped = line.replace(/"/g, '&quot;');

                rowsHtml += `
                    <div class="d-flex align-items-center mb-2 task-row" id="taskRow_${projIdx}_${tIdx}">
                        <input type="hidden" name="projects[${projIdx}][tasks][${tIdx}][is_completed]" value="0">
                        <input type="checkbox" name="projects[${projIdx}][tasks][${tIdx}][is_completed]" value="1" class="task-checkbox-custom task-checkbox" id="taskCheck_${projIdx}_${tIdx}" onchange="syncSelectAllCheckbox(${projIdx})" title="Unticked = Pending, Ticked = Completed" ${checkedAttr}>
                        <input type="text" name="projects[${projIdx}][tasks][${tIdx}][task_name]" class="form-control form-control-sm task-name-input" value="${valEscaped}" required style="border-radius: 8px; border: 1.5px solid #cbd5e1;">
                        <button type="button" class="btn btn-link text-danger ml-2 p-0 remove-task-btn" onclick="removeTaskRow(this)" style="font-size: 18px; text-decoration: none;" title="Remove Task">&times;</button>
                    </div>
                `;
            });

            container.innerHTML = rowsHtml;
            taskIdxCounter[projIdx] = uniqueLines.length;
        }

        updateTaskRemoveButtonsInContainer(container);
        syncSelectAllCheckbox(projIdx);

        const box = document.getElementById(`quickTaskBox_${projIdx}`);
        const footerRow = document.getElementById(`tasksFooterRow_${projIdx}`);
        if (box) box.style.display = 'none';
        if (container) container.style.display = 'block';
        if (footerRow) footerRow.style.setProperty('display', 'flex', 'important');
    }

    function toggleCustomProjectInput(projIdx) {
        const selectEl = document.querySelector(`select[name="projects[${projIdx}][project_id]"]`);
        const customInput = document.getElementById(`customProjectInput_${projIdx}`);
        if (!selectEl || !customInput) return;

        if (selectEl.value === 'custom') {
            customInput.style.display = 'block';
            customInput.required = true;
        } else {
            customInput.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    }

    function addTaskRow(projIdx) {
        const container = document.getElementById(`taskRowsList_${projIdx}`);
        if (!container) return;

        if (!taskIdxCounter[projIdx]) {
            taskIdxCounter[projIdx] = 1;
        }
        const tIdx = taskIdxCounter[projIdx]++;

        const taskDiv = document.createElement('div');
        taskDiv.className = 'd-flex align-items-center mb-2 task-row';
        taskDiv.id = `taskRow_${projIdx}_${tIdx}`;
        taskDiv.innerHTML = `
            <input type="hidden" name="projects[${projIdx}][tasks][${tIdx}][is_completed]" value="0">
            <input type="checkbox" name="projects[${projIdx}][tasks][${tIdx}][is_completed]" value="1" class="task-checkbox-custom task-checkbox" id="taskCheck_${projIdx}_${tIdx}" onchange="syncSelectAllCheckbox(${projIdx})" title="Unticked = Pending, Ticked = Completed">
            <input type="text" name="projects[${projIdx}][tasks][${tIdx}][task_name]" class="form-control form-control-sm task-name-input" placeholder="Task description..." required style="border-radius: 8px; border: 1.5px solid #cbd5e1;">
            <button type="button" class="btn btn-link text-danger ml-2 p-0 remove-task-btn" onclick="removeTaskRow(this)" style="font-size: 18px; text-decoration: none;" title="Remove Task">&times;</button>
        `;
        container.appendChild(taskDiv);
        updateTaskRemoveButtonsInContainer(container);
        syncSelectAllCheckbox(projIdx);
    }

    function removeTaskRow(btnEl) {
        const row = btnEl.closest ? btnEl.closest('.task-row') : null;
        if (row) {
            const container = row.closest('.tasks-container') || row.parentElement;
            const block = row.closest('.project-block-card');
            const projIdx = block ? block.getAttribute('data-block-idx') : 0;
            row.remove();
            if (container) {
                updateTaskRemoveButtonsInContainer(container);
            }
            syncSelectAllCheckbox(projIdx);
        }
    }

    function updateTaskRemoveButtonsInContainer(container) {
        const rows = container.querySelectorAll('.task-row');
        rows.forEach((row) => {
            const btn = row.querySelector('.remove-task-btn');
            if (btn) {
                btn.style.display = rows.length > 1 ? 'inline-block' : 'none';
            }
        });
    }

    function addProjectBlock() {
        const container = document.getElementById('projectBlocksContainer');
        if (!container) return;

        const pIdx = projectBlockIdx++;
        taskIdxCounter[pIdx] = 1;

        const firstSelect = document.querySelector('select.project-select');
        const optionsHtml = firstSelect ? firstSelect.innerHTML : '<option value="">-- Select Project --</option><option value="custom">Custom</option>';

        const pDiv = document.createElement('div');
        pDiv.className = 'project-block-card p-3 mb-3 border rounded-lg bg-light';
        pDiv.id = `projectBlock_${pIdx}`;
        pDiv.setAttribute('data-block-idx', pIdx);

        pDiv.innerHTML = `
            <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                <span class="font-weight-bold text-primary small project-block-header-title">
                    <i class="fas fa-folder mr-1"></i> Project ${pIdx + 1}
                </span>
                <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 remove-project-btn" onclick="removeProjectBlock(this)" style="border-radius: 6px; font-size: 13px;" title="Remove Project">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>

            <div class="form-group mb-2">
                <label class="small font-weight-bold text-dark mb-1">Project <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center">
                    <select name="projects[${pIdx}][project_id]" class="form-control form-control-sm project-select" onchange="toggleCustomProjectInput(${pIdx})" required style="border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; flex: 1;">
                        ${optionsHtml}
                    </select>
                    <input type="text" name="projects[${pIdx}][custom_project_name]" id="customProjectInput_${pIdx}" class="form-control form-control-sm ml-2 custom-project-input" placeholder="Enter project or module name..." style="display: none; border-radius: 8px; border: 1.5px solid #cbd5e1; flex: 1;">
                </div>
            </div>

            <div class="tasks-container mt-2" id="tasksContainer_${pIdx}">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="d-flex align-items-center flex-wrap">
                        <label class="small font-weight-bold text-dark mb-0 mr-1">Tasks / Work Items <span class="text-danger">*</span></label>
                        <span class="badge badge-light border text-dark ml-2 d-inline-flex align-items-center" style="font-size: 10.5px; padding: 2px 6px; border-radius: 6px; background-color: #f8fafc;">
                            <span style="display:inline-flex; align-items:center; justify-content:center; width:13px; height:13px; background:#16a34a; color:#fff; border-radius:3px; font-size:9px; font-weight:900; margin-right:4px;">✓</span> Completed
                        </span>
                        <span class="badge badge-light border text-dark ml-1 d-inline-flex align-items-center" style="font-size: 10.5px; padding: 2px 6px; border-radius: 6px; background-color: #f8fafc;">
                            <span style="display:inline-flex; align-items:center; justify-content:center; width:13px; height:13px; border:1.5px solid #eab308; background:#fffbeb; border-radius:3px; margin-right:4px;"></span> Pending
                        </span>
                    </div>
                    <button type="button" class="btn btn-xs btn-link p-0 font-weight-bold" onclick="toggleQuickTaskBox(${pIdx})" style="color: #7c3aed; font-size: 11.5px; text-decoration: none;">
                        <i class="fas fa-edit mr-1"></i> Quick Add Tasks
                    </button>
                </div>

                <div id="quickTaskBox_${pIdx}" class="mb-2 p-2 border rounded bg-white shadow-xs" style="display: none; border-radius: 10px;">
                    <div class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-paste text-primary mr-1"></i> Enter one task per line...
                    </div>
                    <textarea id="quickTaskText_${pIdx}" class="form-control form-control-sm mb-2" rows="3" placeholder="Attendance Module - Early Logout Fix&#10;Leave Approval UI&#10;Payroll Testing" style="border-radius: 8px; font-size: 12px; border: 1.5px solid #cbd5e1;"></textarea>
                    <div class="d-flex align-items-center justify-content-between">
                        <button type="button" class="btn btn-xs font-weight-bold text-white py-1 px-3" onclick="processQuickTasks(${pIdx})" style="border-radius: 6px; font-size: 11px; background: #7c3aed; border: none;">
                            <i class="fas fa-plus-circle mr-1"></i> Add Tasks
                        </button>
                        <button type="button" class="btn btn-xs btn-light font-weight-bold py-1 px-2 border" onclick="toggleQuickTaskBox(${pIdx})" style="border-radius: 6px; font-size: 11px; color: #64748b;">
                            Cancel
                        </button>
                    </div>
                </div>

                <div id="taskRowsList_${pIdx}">
                    <div class="d-flex align-items-center mb-2 task-row" id="taskRow_${pIdx}_0">
                        <input type="hidden" name="projects[${pIdx}][tasks][0][is_completed]" value="0">
                        <input type="checkbox" name="projects[${pIdx}][tasks][0][is_completed]" value="1" class="task-checkbox-custom task-checkbox" id="taskCheck_${pIdx}_0" onchange="syncSelectAllCheckbox(${pIdx})" title="Unticked = Pending, Ticked = Completed">
                        <input type="text" name="projects[${pIdx}][tasks][0][task_name]" class="form-control form-control-sm task-name-input" placeholder="Task description..." required style="border-radius: 8px; border: 1.5px solid #cbd5e1;">
                        <button type="button" class="btn btn-link text-danger ml-2 p-0 remove-task-btn" onclick="removeTaskRow(this)" style="font-size: 18px; text-decoration: none; display: none;" title="Remove Task">&times;</button>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top" id="tasksFooterRow_${pIdx}">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" id="selectAllCheck_${pIdx}" onchange="toggleSelectAllTasks(${pIdx}, this.checked)" style="width: 17px; height: 17px; cursor: pointer; accent-color: #7c3aed; margin-right: 6px;" title="Mark All Completed">
                        <label for="selectAllCheck_${pIdx}" class="small font-weight-bold text-dark mb-0 cursor-pointer" style="font-size: 12px; user-select: none;" id="selectAllLabel_${pIdx}">Mark All Completed</label>
                    </div>
                    <button type="button" class="btn btn-sm btn-light border font-weight-bold add-task-btn" id="addTaskBtn_${pIdx}" onclick="addTaskRow(${pIdx})" style="border-radius: 8px; color: #7c3aed;">
                        <i class="fas fa-plus mr-1"></i> Add Task
                    </button>
                </div>
            </div>
        `;

        container.appendChild(pDiv);
        updateProjectRemoveButtons();
    }

    function removeProjectBlock(btnEl) {
        const block = (btnEl && btnEl.closest) ? btnEl.closest('.project-block-card') : document.getElementById(`projectBlock_${btnEl}`);
        if (block) {
            block.remove();
            updateProjectRemoveButtons();
        }
    }

    function updateProjectRemoveButtons() {
        const blocks = document.querySelectorAll('.project-block-card');
        blocks.forEach((block, idx) => {
            const btn = block.querySelector('.remove-project-btn');
            if (btn) {
                btn.style.display = blocks.length > 1 ? 'inline-block' : 'none';
            }
            const titleEl = block.querySelector('.project-block-header-title');
            if (titleEl) {
                titleEl.innerHTML = `<i class="fas fa-folder mr-1"></i> Project ${idx + 1}`;
            }
        });
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