@php
    $todayRecord = $attendanceRecord ?? ($attendance['today'] ?? ($dashboard['attendance_self']['today'] ?? null));
    $hasPunchedIn = !empty($hasPunchedIn) || !empty($todayRecord->punch_in_time);
    $hasPunchedOut = !empty($hasPunchedOut) || !empty($todayRecord->punch_out_time);
    $existingWorkMode = strtolower($todayRecord->work_mode ?? ($workMode ?? 'wfo'));
    $isPunchBlocked = !empty($isPunchBlocked);
@endphp

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
                        
                        // If record is actually blocked but no errorMsg set yet, provide helpful default text
                        if (!$errorMsg && !empty($isPunchBlocked)) {
                            $errorMsg = 'Your attendance punch-in is blocked for today because the shift cutoff window closed or an attendance policy violation occurred.';
                        }

                        $errLower = strtolower($errorMsg ?? '');

                        // Contextual error categorizations
                        $isWfhReq = str_contains($errLower, 'work from home') || str_contains($errLower, 'wfh');
                        $isLocationErr = str_contains($errLower, 'premises') || str_contains($errLower, 'geofence') || str_contains($errLower, 'location') || str_contains($errLower, 'gps') || str_contains($errLower, 'outside');
                        $isAlreadyPunched = str_contains($errLower, 'already recorded') || str_contains($errLower, 'already marked') || str_contains($errLower, 'already punched');
                        $isHolidayWeekoff = str_contains($errLower, 'leave, holiday, or week off') || str_contains($errLower, 'holiday') || str_contains($errLower, 'week off') || str_contains($errLower, 'work request');
                        $isOnLeave = str_contains($errLower, 'disabled during approved leave') || str_contains($errLower, 'approved leave');
                        $isWebDisabled = str_contains($errLower, 'web attendance is disabled') || str_contains($errLower, 'mobile attendance is disabled') || str_contains($errLower, 'mobile app required');
                        $isEligibilityErr = str_contains($errLower, 'not eligible') || str_contains($errLower, 'profile pending') || str_contains($errLower, 'complete your profile') || str_contains($errLower, 'employment has ended');
                        $isEarlyLogin = !$isWfhReq && !$isLocationErr && (
                            str_contains($errLower, 'too early') || 
                            str_contains($errLower, 'early') || 
                            str_contains($errLower, 'allowed from') ||
                            str_contains($errLower, 'window opens')
                        );
                        $isRealBlocked = !empty($isPunchBlocked) || (
                            !$isWfhReq && !$isLocationErr && !$isHolidayWeekoff && !$isOnLeave && !$isEarlyLogin && (
                                str_contains($errLower, 'closed') || 
                                str_contains($errLower, 'blocked') || 
                                str_contains($errLower, 'cutoff') || 
                                str_contains($errLower, 'punch_blocked')
                            )
                        );

                        // Resolve Card Header, Badge, Icon, Colors, and Next Steps strictly based on cause
                        if ($isEarlyLogin) {
                            $cardTitle = 'Attendance Window Unavailable';
                            $cardBadge = 'EARLY PUNCH';
                            $badgeStyle = 'background: #f59e0b; color: #ffffff;';
                            $cardIcon = 'fa-clock';
                            $iconBg = '#f59e0b';
                            $cardBg = 'linear-gradient(135deg, #fffbe6 0%, #fff7ed 100%)';
                            $cardBorder = '#fde047';
                            $titleColor = '#b45309';
                            $nextSteps = [
                                'Please wait until your shift punch-in window opens.',
                                'Contact HR if your assigned shift timings have changed.'
                            ];
                        } elseif ($isWfhReq) {
                            $cardTitle = 'Work From Home Approval Required';
                            $cardBadge = 'WFH REQUIRED';
                            $badgeStyle = 'background: #0284c7; color: #ffffff;';
                            $cardIcon = 'fa-laptop-house';
                            $iconBg = '#0284c7';
                            $cardBg = 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)';
                            $cardBorder = '#7dd3fc';
                            $titleColor = '#0369a1';
                            $nextSteps = [
                                'Apply for Work From Home (WFH) for today\'s date and get approval before punching in.',
                                'If you are working from office today, select Working From Office (WFO) to punch in.',
                                'Contact your HR Manager or Administrator if you need assistance.'
                            ];
                        } elseif ($isLocationErr) {
                            $cardTitle = 'Office Location Mismatch';
                            $cardBadge = 'OUTSIDE PREMISES';
                            $badgeStyle = 'background: #d97706; color: #ffffff;';
                            $cardIcon = 'fa-map-marker-alt';
                            $iconBg = '#d97706';
                            $cardBg = 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)';
                            $cardBorder = '#fcd34d';
                            $titleColor = '#b45309';
                            $nextSteps = [
                                'Ensure your device GPS / Location services are enabled and allowed for this browser.',
                                'Ensure you are connected to the official Office Wi-Fi network.',
                                'If working remotely today, apply for Work From Home (WFH).'
                            ];
                        } elseif ($isAlreadyPunched) {
                            $cardTitle = 'Attendance Already Recorded';
                            $cardBadge = 'ALREADY MARKED';
                            $badgeStyle = 'background: #16a34a; color: #ffffff;';
                            $cardIcon = 'fa-check-circle';
                            $iconBg = '#16a34a';
                            $cardBg = 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)';
                            $cardBorder = '#86efac';
                            $titleColor = '#15803d';
                            $nextSteps = [
                                'Your punch-in for today has already been registered.',
                                'You can punch out when your workday finishes.'
                            ];
                        } elseif ($isHolidayWeekoff) {
                            $cardTitle = 'Holiday / Week-off Restriction';
                            $cardBadge = 'WORK REQUEST REQUIRED';
                            $badgeStyle = 'background: #7c3aed; color: #ffffff;';
                            $cardIcon = 'fa-calendar-day';
                            $iconBg = '#7c3aed';
                            $cardBg = 'linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%)';
                            $cardBorder = '#d8b4fe';
                            $titleColor = '#6d28d9';
                            $nextSteps = [
                                'Today is scheduled as a Holiday or Week-off.',
                                'To punch and earn comp-off, apply for a Holiday / Week-off Work Request.'
                            ];
                        } elseif ($isOnLeave) {
                            $cardTitle = 'Attendance Disabled (On Leave)';
                            $cardBadge = 'ON LEAVE';
                            $badgeStyle = 'background: #0284c7; color: #ffffff;';
                            $cardIcon = 'fa-plane-departure';
                            $iconBg = '#0284c7';
                            $cardBg = 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)';
                            $cardBorder = '#7dd3fc';
                            $titleColor = '#0369a1';
                            $nextSteps = [
                                'You have an approved full-day leave recorded for today.',
                                'If you need to attend office, please cancel your leave request first.'
                            ];
                        } elseif ($isWebDisabled) {
                            $cardTitle = 'Web Attendance Restricted';
                            $cardBadge = 'MOBILE APP REQUIRED';
                            $badgeStyle = 'background: #475569; color: #ffffff;';
                            $cardIcon = 'fa-mobile-alt';
                            $iconBg = '#475569';
                            $cardBg = 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)';
                            $cardBorder = '#cbd5e1';
                            $titleColor = '#334155';
                            $nextSteps = [
                                'Web attendance is not enabled for your account.',
                                'Please mark attendance using the OrboOne Mobile App, or contact HR.'
                            ];
                        } elseif ($isEligibilityErr) {
                            $cardTitle = 'Attendance Eligibility Notice';
                            $cardBadge = 'ACTION REQUIRED';
                            $badgeStyle = 'background: #e11d48; color: #ffffff;';
                            $cardIcon = 'fa-id-card';
                            $iconBg = '#e11d48';
                            $cardBg = 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)';
                            $cardBorder = '#fecdd3';
                            $titleColor = '#be123c';
                            $nextSteps = [
                                'Complete your employee profile details and submit for verification.',
                                'Contact HR Operations if you need attendance eligibility activation.'
                            ];
                        } elseif ($isRealBlocked) {
                            // STRICTLY ONLY WHEN ATTENDANCE IS GENUINELY BLOCKED
                            $cardTitle = 'Attendance Punch Blocked';
                            $cardBadge = 'PUNCH BLOCKED';
                            $badgeStyle = 'background: #dc2626; color: #ffffff;';
                            $cardIcon = 'fa-user-lock';
                            $iconBg = '#dc2626';
                            $cardBg = 'linear-gradient(135deg, #fff5f5 0%, #ffeef0 100%)';
                            $cardBorder = '#fca5a5';
                            $titleColor = '#991b1b';
                            $nextSteps = [
                                'Your punch-in has been blocked because the shift cutoff window closed or an attendance policy violation occurred.',
                                'Submit an Attendance Regularization request to unlock.',
                                'Contact your HR Administrator if you require assistance.'
                            ];
                        } else {
                            // GENERAL OR UNKNOWN ATTENDANCE ISSUE (NEVER CALL THIS BLOCKED)
                            $cardTitle = 'Unable to Mark Attendance';
                            $cardBadge = 'ATTENDANCE NOTICE';
                            $badgeStyle = 'background: #ea580c; color: #ffffff;';
                            $cardIcon = 'fa-exclamation-triangle';
                            $iconBg = '#ea580c';
                            $cardBg = 'linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%)';
                            $cardBorder = '#fed7aa';
                            $titleColor = '#c2410c';
                            $nextSteps = [
                                'Please review the notice details and try again.',
                                'If the problem persists, contact your HR Administrator or IT Support.'
                            ];
                        }
                    @endphp

                    @if ($errorMsg)
                        <div class="card border-0 mb-3 shadow-xs" style="border-radius: 18px; background: {{ $cardBg }}; border: 1.5px solid {{ $cardBorder }} !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start mb-2">
                                    <div class="mr-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: {{ $iconBg }}; color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
                                        <i class="fas {{ $cardIcon }} fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                            <h6 class="font-weight-bold m-0" style="font-size: 15px; color: {{ $titleColor }};">
                                                {{ $cardTitle }}
                                            </h6>
                                            <span class="badge px-2 py-1" style="border-radius: 6px; font-size: 10px; font-weight: 800; {{ $badgeStyle }}">
                                                {{ $cardBadge }}
                                            </span>
                                        </div>
                                        <p class="font-weight-bold mb-0 mt-1" style="font-size: 13px; line-height: 1.4; color: #1e293b;">
                                            {{ $errorMsg }}
                                        </p>
                                    </div>
                                </div>

                                <div class="p-3 mt-2" style="background: rgba(255, 255, 255, 0.9); border-radius: 12px; border-left: 4px solid {{ $iconBg }}; font-size: 12px; color: #334155; line-height: 1.5;">
                                    <p class="font-weight-bold mb-1" style="font-size: 12px; color: {{ $titleColor }};">
                                        <i class="fas fa-info-circle mr-1"></i> Next Steps & Required Actions:
                                    </p>
                                    <ul class="pl-3 mb-0" style="font-weight: 600; font-size: 12px; color: #475569;">
                                        @foreach ($nextSteps as $step)
                                            <li class="mb-1">{{ $step }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mt-3 gap-2 flex-wrap">
                                    @if ($isWfhReq)
                                        @if(Route::has('hrms.attendance.my-wfh.index'))
                                            <a href="{{ route('hrms.attendance.my-wfh.index') }}" class="btn btn-primary btn-sm font-weight-bold text-white shadow-xs px-3" style="border-radius: 10px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border: none; font-size: 12px; padding: 7px 14px;">
                                                <i class="fas fa-laptop-house mr-1"></i> Apply Work From Home
                                            </a>
                                        @elseif(Route::has('hrms.attendance.wfh.index'))
                                            <a href="{{ route('hrms.attendance.wfh.index') }}" class="btn btn-primary btn-sm font-weight-bold text-white shadow-xs px-3" style="border-radius: 10px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border: none; font-size: 12px; padding: 7px 14px;">
                                                <i class="fas fa-laptop-house mr-1"></i> Apply Work From Home
                                            </a>
                                        @endif
                                    @elseif ($isHolidayWeekoff && Route::has('hrms.attendance.holiday_work.index'))
                                        <a href="{{ route('hrms.attendance.holiday_work.index') }}" class="btn btn-primary btn-sm font-weight-bold text-white shadow-xs px-3" style="border-radius: 10px; background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); border: none; font-size: 12px; padding: 7px 14px;">
                                            <i class="fas fa-calendar-plus mr-1"></i> Apply Holiday Work Request
                                        </a>
                                    @elseif ($isRealBlocked && Route::has('hrms.attendance.regularizations.index'))
                                        <a href="{{ route('hrms.attendance.regularizations.index') }}" class="btn btn-danger btn-sm font-weight-bold text-white shadow-xs px-3" style="border-radius: 10px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none; font-size: 12px; padding: 7px 14px;">
                                            <i class="fas fa-file-signature mr-1"></i> Apply Regularization
                                        </a>
                                    @endif

                                    <button type="button" onclick="$('#webPunchInFormInputs').slideToggle(); $('#webPunchInSubmitBtn').toggle();" class="btn btn-outline-secondary btn-sm font-weight-bold px-3 ml-auto" style="border-radius: 10px; font-size: 11.5px; padding: 6px 14px;">
                                        <i class="fas fa-redo mr-1"></i> Try Again / View Form
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div id="webPunchInFormInputs" style="{{ $errorMsg ? 'display: none;' : '' }}">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-muted">Work Mode</label>
                            <select name="work_mode" id="web_work_mode_select" onchange="handleWorkModeChange(this.value)" class="form-control select2-searchable" style="border-radius: 12px; height: 44px; width: 100%;">
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
                <div class="modal-header p-4 text-white position-relative" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important; border: none;">
                    <div class="d-flex align-items-center w-100" style="padding-right: 30px;">
                        <div class="modal-header-icon mr-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px; background: rgba(255,255,255,0.2); border-radius: 16px; backdrop-filter: blur(8px);">
                            <i class="fas fa-sign-out-alt fa-2x text-white"></i>
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <h4 class="font-weight-bold mb-1 text-white modal-title-text" style="font-size: 20px; letter-spacing: -0.3px;">Daily Task Update</h4>
                            <p class="mb-0 text-white-50 small font-weight-semibold modal-subtitle-text">Work Mode: {{ strtoupper($existingWorkMode) }} | Punch out based on assigned shift policy</p>
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
                        textarea {
                            resize: none !important;
                        }

                        /* Task Status Toggle Buttons (Completed / Pending) */
                        .task-status-toggle-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                            padding: 3px 9px;
                            font-size: 11px;
                            font-weight: 800;
                            border-radius: 6px;
                            border: 1px solid #e2e8f0;
                            background: #fff;
                            color: #64748b;
                            cursor: pointer;
                            transition: all 0.15s ease;
                            line-height: 1.2;
                            user-select: none;
                        }
                        .task-status-toggle-btn:hover {
                            border-color: #cbd5e1;
                        }
                        .task-status-toggle-btn.task-status-completed {
                            border-color: rgba(22, 163, 74, 0.35);
                            background: rgba(22, 163, 74, 0.04);
                            color: #16a34a;
                        }
                        .task-status-toggle-btn.task-status-completed:hover {
                            background: rgba(22, 163, 74, 0.08);
                        }
                        .task-status-toggle-btn.task-status-completed.active {
                            background: rgba(22, 163, 74, 0.14) !important;
                            border-color: #16a34a !important;
                            color: #15803d !important;
                            box-shadow: 0 0 0 1px #16a34a inset;
                        }
                        .task-status-toggle-btn.task-status-pending {
                            border-color: rgba(234, 179, 8, 0.35);
                            background: rgba(234, 179, 8, 0.04);
                            color: #b45309;
                        }
                        .task-status-toggle-btn.task-status-pending:hover {
                            background: rgba(234, 179, 8, 0.08);
                        }
                        .task-status-toggle-btn.task-status-pending.active {
                            background: rgba(234, 179, 8, 0.14) !important;
                            border-color: #eab308 !important;
                            color: #92400e !important;
                            box-shadow: 0 0 0 1px #eab308 inset;
                        }
                        .task-status-toggle-btn i.task-status-icon {
                            font-size: 11.5px;
                            line-height: 1;
                        }

                        /* Responsive status grid */
                        .today-status-grid {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 8px;
                            width: 100%;
                        }

                        .today-status-pill-btn {
                            width: 100%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 6px;
                            padding: 9px 8px !important;
                            font-size: 13px !important;
                            font-weight: 700;
                            border-radius: 12px;
                            border: 1.5px solid #e2e8f0;
                            background: #fff;
                            color: #475569;
                            white-space: nowrap;
                            transition: all 0.18s ease;
                            text-align: center;
                        }
                        .today-status-pill-btn i {
                            font-size: 13px;
                            flex-shrink: 0;
                        }

                        @media (max-width: 768px) {
                            #webPunchOutModal .modal-dialog,
                            #webPunchInModal .modal-dialog {
                                margin: 0.6rem auto !important;
                                max-width: calc(100% - 1.2rem) !important;
                            }
                            #webPunchOutModal .modal-content,
                            #webPunchInModal .modal-content {
                                border-radius: 20px !important;
                            }
                            .today-status-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                                gap: 8px !important;
                            }
                        }

                        @media (max-width: 576px) {
                            #webPunchOutModal .modal-dialog,
                            #webPunchInModal .modal-dialog {
                                margin: 0.4rem auto !important;
                                max-width: calc(100% - 0.8rem) !important;
                            }
                            #webPunchOutModal .modal-header,
                            #webPunchInModal .modal-header {
                                padding: 14px 16px !important;
                            }
                            #webPunchOutModal .modal-header .modal-header-icon {
                                width: 40px !important;
                                height: 40px !important;
                                border-radius: 12px !important;
                                margin-right: 10px !important;
                            }
                            #webPunchOutModal .modal-header .modal-header-icon i {
                                font-size: 1.2rem !important;
                            }
                            #webPunchOutModal .modal-header .modal-title-text {
                                font-size: 16px !important;
                                margin-bottom: 2px !important;
                            }
                            #webPunchOutModal .modal-header .modal-subtitle-text {
                                font-size: 10.5px !important;
                                line-height: 1.3 !important;
                            }
                            #webPunchOutModal .modal-header .close {
                                top: 12px !important;
                                right: 14px !important;
                                font-size: 24px !important;
                            }
                            #webPunchOutModal .modal-body,
                            #webPunchInModal .modal-body {
                                padding: 12px !important;
                                max-height: 74vh !important;
                            }
                            #webPunchOutModal .orb-card-section {
                                padding: 12px !important;
                                border-radius: 14px !important;
                                margin-bottom: 10px !important;
                            }
                            #webPunchOutModal .project-block-card {
                                padding: 10px !important;
                                border-radius: 12px !important;
                            }
                            .today-status-pill-btn {
                                font-size: 12px !important;
                                padding: 8px 6px !important;
                            }
                            #webPunchOutModal .modal-footer,
                            #webPunchInModal .modal-footer {
                                padding: 12px 14px !important;
                                flex-direction: column-reverse !important;
                                gap: 8px !important;
                            }
                            #webPunchOutModal .modal-footer .punch-modal-cancel-btn,
                            #webPunchOutModal .modal-footer .punch-modal-submit-btn,
                            #webPunchInModal .modal-footer .btn {
                                width: 100% !important;
                                margin: 0 !important;
                            }
                        }

                        /* Project select & custom input layout */
                        .project-input-group {
                            display: flex !important;
                            flex-direction: row !important;
                            align-items: center !important;
                            gap: 8px !important;
                            width: 100% !important;
                        }
                        .project-input-group .select2-container {
                            flex: 1 1 0% !important;
                            min-width: 0 !important;
                            width: 100% !important;
                        }
                        .project-input-group .custom-project-input {
                            flex: 1 1 0% !important;
                            min-width: 0 !important;
                        }
                        @media (max-width: 576px) {
                            .project-input-group {
                                flex-direction: column !important;
                            }
                            .project-input-group .select2-container,
                            .project-input-group .custom-project-input {
                                width: 100% !important;
                                flex: none !important;
                            }
                        }
                    </style>

                    <template id="projectSelectOptionsTpl">
                        <option value="">-- Select Project --</option>
                        @foreach($assignedProjects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                        <option value="custom">Custom</option>
                    </template>

                    {{-- Today's Work Section --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(75, 0, 232, 0.08); border-radius: 10px; color: var(--orb-primary, #4B00E8); font-weight: 800;">
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
                                    <div class="project-input-group d-flex align-items-center flex-column flex-sm-row" style="gap: 8px;">
                                        <select name="projects[0][project_id]" class="form-control form-control-sm project-select select2-searchable" onchange="toggleCustomProjectInput(0)" required style="border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; width: 100%;">
                                            <option value="">-- Select Project --</option>
                                            @foreach($assignedProjects as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                            <option value="custom">Custom</option>
                                        </select>
                                        <input type="text" name="projects[0][custom_project_name]" id="customProjectInput_0" class="form-control form-control-sm custom-project-input" placeholder="Enter project or module name..." style="display: none; border-radius: 8px; border: 1.5px solid #cbd5e1; width: 100%;">
                                    </div>
                                </div>

                                <!-- Tasks Container for Project Block 0 -->
                                <div class="tasks-container mt-2" id="tasksContainer_0">
                                    <div class="tasks-header-wrap d-flex align-items-center justify-content-between w-100 mb-2">
                                        <label class="small font-weight-bold text-dark mb-0">Tasks / Work Items <span class="text-danger">*</span></label>
                                        <div class="d-flex align-items-center ml-auto" style="gap: 6px;">
                                            <button type="button" class="btn btn-xs task-status-toggle-btn task-status-completed" id="btnMarkCompleted_0" onclick="setAllTasksStatus(0, true)" title="Mark all tasks as Completed">
                                                <i class="far fa-square task-status-icon mr-1" id="iconCompleted_0" style="font-size: 11.5px;"></i>
                                                <span>Completed</span>
                                            </button>
                                            <button type="button" class="btn btn-xs task-status-toggle-btn task-status-pending active" id="btnMarkPending_0" onclick="setAllTasksStatus(0, false)" title="Mark all tasks as Pending">
                                                <i class="fas fa-check-square task-status-icon mr-1" id="iconPending_0" style="font-size: 11.5px;"></i>
                                                <span>Pending</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Quick Multi-line Task Input Box -->
                                    <div id="quickTaskBox_0" class="mb-2 p-2 bg-white border rounded shadow-xs" style="display: none; border-color: #cbd5e1 !important;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <label class="small font-weight-bold text-muted mb-0" style="font-size: 11.5px;">
                                                <i class="fas fa-list-ul mr-1 text-primary"></i> Paste / type tasks description (one per line):
                                            </label>
                                            <button type="button" class="btn btn-xs text-muted p-0" onclick="toggleQuickTaskBox(0)" style="font-size: 15px; line-height: 1;" title="Close">&times;</button>
                                        </div>
                                        <textarea id="quickTaskInput_0" class="form-control form-control-sm mb-2" rows="3" placeholder="Enter tasks (one per line)...&#10;Task 1: Designed homepage layout&#10;Task 2: Fixed API authentication bug" style="font-size: 12.5px; border-radius: 8px; border: 1.5px solid #cbd5e1; resize: vertical !important; min-height: 80px; line-height: 1.5; padding: 8px 10px;"></textarea>
                                        <div class="d-flex justify-content-end" style="gap: 6px;">
                                            <button type="button" class="btn btn-xs btn-light border py-1 px-2" onclick="toggleQuickTaskBox(0)" style="font-size: 11.5px; border-radius: 6px;">Cancel</button>
                                            <button type="button" class="btn btn-xs btn-primary py-1 px-2 font-weight-bold" onclick="processQuickTasks(0)" style="font-size: 11.5px; border-radius: 6px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important; border: none; color: #fff;">
                                                <i class="fas fa-plus mr-1"></i> Add Tasks
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Task Rows List -->
                                    <div id="taskRowsList_0">
                                        <div class="d-flex align-items-center mb-2 task-row" id="taskRow_0_0">
                                            <input type="hidden" name="projects[0][tasks][0][is_completed]" value="0">
                                            <input type="checkbox" name="projects[0][tasks][0][is_completed]" value="1" class="task-checkbox-custom task-checkbox" id="taskCheck_0_0" onchange="syncSelectAllCheckbox(0)" title="Unticked = Pending, Ticked = Completed">
                                            <input type="text" name="projects[0][tasks][0][task_name]" class="form-control form-control-sm task-name-input" placeholder="Task description..." required style="border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 12.5px; height: 36px;">
                                            <button type="button" class="btn btn-link text-danger ml-2 p-0 remove-task-btn" onclick="removeTaskRow(this)" style="font-size: 18px; text-decoration: none; display: none;" title="Remove Task">&times;</button>
                                        </div>
                                    </div>

                                    <div class="align-items-center justify-content-between mt-2 pt-2 border-top" id="tasksFooterRow_0" style="display: flex;">
                                        <button type="button" class="btn btn-sm btn-link p-0 font-weight-bold add-task-btn" id="addTaskBtn_0" onclick="addTaskRow(0)" style="color: var(--orb-primary, #4B00E8) !important; text-decoration: none; font-size: 12.5px;">
                                            <i class="fas fa-plus mr-1"></i> Add Task
                                        </button>
                                        <button type="button" class="btn btn-xs font-weight-bold quick-add-tasks-btn" onclick="toggleQuickTaskBox(0)" style="border-radius: 8px; font-size: 11.5px; padding: 4px 10px; color: var(--orb-primary, #4B00E8); border: 1px solid rgba(75, 0, 232, 0.35); background: rgba(75, 0, 232, 0.05); text-decoration: none;">
                                            <i class="fas fa-bolt mr-1" style="color: #eab308;"></i> Quick Add Tasks
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold mt-1" onclick="addProjectBlock()" style="border-radius: 10px; color: var(--orb-primary, #4B00E8); border-color: var(--orb-primary, #4B00E8);">
                            <i class="fas fa-folder-plus mr-1"></i> Add Project
                        </button>
                    </div>

                    {{-- Single Overall Today's Work Status (After all projects, before blockers) --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="mr-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(75, 0, 232, 0.08); border-radius: 10px; color: var(--orb-primary, #4B00E8); font-weight: 800;">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div>
                                <label class="font-weight-bold text-dark mb-0 d-block" style="font-size: 14px;">Today's Work Status <span class="text-danger">*</span></label>
                                <span class="text-muted small">Overall status of the employee's work performed today</span>
                            </div>
                        </div>
                        <input type="hidden" name="today_work_status" id="web_today_work_status" value="in_progress">
                        <div class="today-status-grid mt-2" id="todayStatusPillGroup">
                            <button type="button" class="btn today-status-pill-btn active" data-status="in_progress" onclick="selectTodayStatusPill('in_progress')" style="border: 2px solid #3b82f6; background: #eff6ff; color: #2563eb;">
                                <i class="far fa-clock mr-1"></i> In Progress
                            </button>
                            <button type="button" class="btn today-status-pill-btn" data-status="testing" onclick="selectTodayStatusPill('testing')">
                                <i class="fas fa-flask mr-1"></i> Testing
                            </button>
                            <button type="button" class="btn today-status-pill-btn" data-status="completed" onclick="selectTodayStatusPill('completed')">
                                <i class="far fa-check-circle mr-1"></i> Completed
                            </button>
                            <button type="button" class="btn today-status-pill-btn" data-status="blocked" onclick="selectTodayStatusPill('blocked')">
                                <i class="fas fa-ban mr-1"></i> Blocked
                            </button>
                        </div>
                    </div>

                    {{-- Issues / Blockers --}}
                    <div class="orb-card-section mb-3 p-3 bg-white border shadow-xs" style="border-radius: 18px; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleCollapsibleSection('issuesBlockersBox', 'issuesToggleIcon')" style="user-select: none;">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(75, 0, 232, 0.08); border-radius: 10px; color: var(--orb-primary, #4B00E8); font-weight: 800;">
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
                                <div class="mr-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(75, 0, 232, 0.08); border-radius: 10px; color: var(--orb-primary, #4B00E8); font-weight: 800;">
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
                <div class="modal-footer p-3 border-0 bg-white shadow-lg d-flex align-items-center justify-content-between flex-wrap" style="border-top: 1px solid #f1f5f9 !important; gap: 8px;">
                    <button type="button" class="btn btn-light font-weight-bold px-4 punch-modal-cancel-btn" data-dismiss="modal" style="border-radius: 12px; height: 44px; min-width: 90px;">Cancel</button>
                    <button type="submit" class="btn text-white font-weight-bold px-4 shadow-lg punch-modal-submit-btn" style="border-radius: 12px; height: 44px; background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important; border: none; font-size: 14px; flex: 1; min-width: 180px; box-shadow: 0 8px 20px rgba(239, 68, 68, 0.35) !important;">
                        <i class="fas fa-sign-out-alt mr-2"></i> Submit & Punch Out
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

    function setAllTasksStatus(projIdx, isCompleted) {
        const container = document.getElementById(`taskRowsList_${projIdx}`);
        if (!container) return;

        const checkboxes = container.querySelectorAll('.task-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = isCompleted;
        });

        syncTaskStatusPills(projIdx);
    }

    function syncTaskStatusPills(projIdx) {
        const container = document.getElementById(`taskRowsList_${projIdx}`);
        const completedBtn = document.getElementById(`btnMarkCompleted_${projIdx}`);
        const pendingBtn = document.getElementById(`btnMarkPending_${projIdx}`);
        const iconCompleted = document.getElementById(`iconCompleted_${projIdx}`);
        const iconPending = document.getElementById(`iconPending_${projIdx}`);
        if (!container) return;

        const checkboxes = container.querySelectorAll('.task-checkbox');
        if (checkboxes.length === 0) {
            if (completedBtn) completedBtn.classList.remove('active');
            if (pendingBtn) pendingBtn.classList.remove('active');
            if (iconCompleted) iconCompleted.className = 'far fa-square task-status-icon mr-1';
            if (iconPending) iconPending.className = 'far fa-square task-status-icon mr-1';
            return;
        }

        let allCompleted = true;
        let allPending = true;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                allPending = false;
            } else {
                allCompleted = false;
            }
        });

        if (completedBtn) {
            if (allCompleted) {
                completedBtn.classList.add('active');
            } else {
                completedBtn.classList.remove('active');
            }
        }
        if (iconCompleted) {
            iconCompleted.className = (allCompleted ? 'fas fa-check-square' : 'far fa-square') + ' task-status-icon mr-1';
        }

        if (pendingBtn) {
            if (allPending) {
                pendingBtn.classList.add('active');
            } else {
                pendingBtn.classList.remove('active');
            }
        }
        if (iconPending) {
            iconPending.className = (allPending ? 'fas fa-check-square' : 'far fa-square') + ' task-status-icon mr-1';
        }
    }

    function syncSelectAllCheckbox(projIdx) {
        syncTaskStatusPills(projIdx);
    }

    function toggleSelectAllTasks(projIdx, isChecked) {
        setAllTasksStatus(projIdx, isChecked);
    }

    function toggleCustomProjectInput(projIdx) {
        const card = document.getElementById(`projectBlock_${projIdx}`);
        const selectEl = card ? card.querySelector('select.project-select') : document.querySelector(`select[name="projects[${projIdx}][project_id]"]`);
        const customInput = document.getElementById(`customProjectInput_${projIdx}`) || (card ? card.querySelector('.custom-project-input') : null);
        if (!selectEl || !customInput) return;

        if (selectEl.value === 'custom') {
            customInput.style.display = 'block';
            customInput.required = true;
            setTimeout(() => customInput.focus(), 60);
        } else {
            customInput.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    }

    function toggleQuickTaskBox(projIdx) {
        const box = document.getElementById(`quickTaskBox_${projIdx}`);
        const rowsList = document.getElementById(`taskRowsList_${projIdx}`);
        const footerRow = document.getElementById(`tasksFooterRow_${projIdx}`);
        if (!box) return;

        if (box.style.display === 'none' || !box.style.display) {
            // Opening Quick Add Box
            const input = document.getElementById(`quickTaskInput_${projIdx}`);
            if (input && rowsList) {
                const existingInputs = rowsList.querySelectorAll('.task-name-input');
                const existingTexts = [];
                existingInputs.forEach(inp => {
                    if (inp.value && inp.value.trim()) {
                        existingTexts.push(inp.value.trim());
                    }
                });
                if (existingTexts.length > 0 && !input.value.trim()) {
                    input.value = existingTexts.join('\n');
                }
            }

            box.style.display = 'block';
            if (rowsList) rowsList.style.display = 'none';
            if (footerRow) footerRow.style.display = 'none';
            if (input) input.focus();
        } else {
            // Closing Quick Add Box
            box.style.display = 'none';
            if (rowsList) rowsList.style.display = 'block';
            if (footerRow) footerRow.style.display = 'flex';
        }
    }

    function processQuickTasks(projIdx) {
        const input = document.getElementById(`quickTaskInput_${projIdx}`);
        const listContainer = document.getElementById(`taskRowsList_${projIdx}`);
        const footerRow = document.getElementById(`tasksFooterRow_${projIdx}`);
        const box = document.getElementById(`quickTaskBox_${projIdx}`);
        if (!input || !listContainer) return;

        const text = input.value.trim();
        if (!text) {
            toggleQuickTaskBox(projIdx);
            return;
        }

        const lines = text.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
        if (lines.length === 0) {
            toggleQuickTaskBox(projIdx);
            return;
        }

        // Rebuild rows from the quick add input lines
        listContainer.innerHTML = '';
        taskIdxCounter[projIdx] = 0;

        lines.forEach((line) => {
            const tIdx = taskIdxCounter[projIdx]++;
            const taskDiv = document.createElement('div');
            taskDiv.className = 'd-flex align-items-center mb-2 task-row';
            taskDiv.id = `taskRow_${projIdx}_${tIdx}`;
            taskDiv.innerHTML = `
                <input type="hidden" name="projects[${projIdx}][tasks][${tIdx}][is_completed]" value="0">
                <input type="checkbox" name="projects[${projIdx}][tasks][${tIdx}][is_completed]" value="1" class="task-checkbox-custom task-checkbox" id="taskCheck_${projIdx}_${tIdx}" onchange="syncSelectAllCheckbox(${projIdx})" title="Unticked = Pending, Ticked = Completed">
                <input type="text" name="projects[${projIdx}][tasks][${tIdx}][task_name]" class="form-control form-control-sm task-name-input" value="${line.replace(/"/g, '&quot;')}" required style="border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 12.5px; height: 36px;">
                <button type="button" class="btn btn-link text-danger ml-2 p-0 remove-task-btn" onclick="removeTaskRow(this)" style="font-size: 18px; text-decoration: none;" title="Remove Task">&times;</button>
            `;
            listContainer.appendChild(taskDiv);
        });

        updateTaskRemoveButtonsInContainer(listContainer);
        syncSelectAllCheckbox(projIdx);

        // Hide quick box, show individual rows and footer
        if (box) box.style.display = 'none';
        if (listContainer) listContainer.style.display = 'block';
        if (footerRow) footerRow.style.display = 'flex';
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
            <input type="text" name="projects[${projIdx}][tasks][${tIdx}][task_name]" class="form-control form-control-sm task-name-input" placeholder="Task description..." required style="border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 12.5px; height: 36px;">
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

    const todayStatusThemes = {
        'in_progress': { bg: '#eff6ff', color: '#2563eb', border: '2px solid #3b82f6' },
        'testing': { bg: '#fdf4ff', color: '#a855f7', border: '2px solid #a855f7' },
        'completed': { bg: '#f0fdf4', color: '#16a34a', border: '2px solid #16a34a' },
        'blocked': { bg: '#fef2f2', color: '#ef4444', border: '2px solid #ef4444' }
    };

    function addProjectBlock() {
        const container = document.getElementById('projectBlocksContainer');
        if (!container) return;

        const pIdx = projectBlockIdx++;
        taskIdxCounter[pIdx] = 1;

        const tpl = document.getElementById('projectSelectOptionsTpl');
        const optionsHtml = tpl ? tpl.innerHTML.trim() : '<option value="">-- Select Project --</option><option value="custom">Custom</option>';

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
                <div class="project-input-group d-flex align-items-center flex-column flex-sm-row" style="gap: 8px;">
                    <select name="projects[${pIdx}][project_id]" class="form-control form-control-sm project-select select2-searchable" onchange="toggleCustomProjectInput(${pIdx})" required style="border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600; width: 100%;">
                        ${optionsHtml}
                    </select>
                    <input type="text" name="projects[${pIdx}][custom_project_name]" id="customProjectInput_${pIdx}" class="form-control form-control-sm custom-project-input" placeholder="Enter project or module name..." style="display: none; border-radius: 8px; border: 1.5px solid #cbd5e1; width: 100%;">
                </div>
            </div>

            <div class="tasks-container mt-2" id="tasksContainer_${pIdx}">
                <div class="tasks-header-wrap d-flex align-items-center justify-content-between w-100 mb-2">
                    <label class="small font-weight-bold text-dark mb-0">Tasks / Work Items <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center ml-auto" style="gap: 6px;">
                        <button type="button" class="btn btn-xs task-status-toggle-btn task-status-completed" id="btnMarkCompleted_${pIdx}" onclick="setAllTasksStatus(${pIdx}, true)" title="Mark all tasks as Completed">
                            <i class="far fa-square task-status-icon mr-1" id="iconCompleted_${pIdx}" style="font-size: 11.5px;"></i>
                            <span>Completed</span>
                        </button>
                        <button type="button" class="btn btn-xs task-status-toggle-btn task-status-pending active" id="btnMarkPending_${pIdx}" onclick="setAllTasksStatus(${pIdx}, false)" title="Mark all tasks as Pending">
                            <i class="fas fa-check-square task-status-icon mr-1" id="iconPending_${pIdx}" style="font-size: 11.5px;"></i>
                            <span>Pending</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Multi-line Task Input Box -->
                <div id="quickTaskBox_${pIdx}" class="mb-2 p-2 bg-white border rounded shadow-xs" style="display: none; border-color: #cbd5e1 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="small font-weight-bold text-muted mb-0" style="font-size: 11.5px;">
                            <i class="fas fa-list-ul mr-1 text-primary"></i> Paste / type tasks description (one per line):
                        </label>
                        <button type="button" class="btn btn-xs text-muted p-0" onclick="toggleQuickTaskBox(${pIdx})" style="font-size: 15px; line-height: 1;" title="Close">&times;</button>
                    </div>
                    <textarea id="quickTaskInput_${pIdx}" class="form-control form-control-sm mb-2" rows="3" placeholder="Enter tasks (one per line)...&#10;Task 1: Designed homepage layout&#10;Task 2: Fixed API authentication bug" style="font-size: 12.5px; border-radius: 8px; border: 1.5px solid #cbd5e1; resize: vertical !important; min-height: 80px; line-height: 1.5; padding: 8px 10px;"></textarea>
                    <div class="d-flex justify-content-end" style="gap: 6px;">
                        <button type="button" class="btn btn-xs btn-light border py-1 px-2" onclick="toggleQuickTaskBox(${pIdx})" style="font-size: 11.5px; border-radius: 6px;">Cancel</button>
                        <button type="button" class="btn btn-xs btn-primary py-1 px-2 font-weight-bold" onclick="processQuickTasks(${pIdx})" style="font-size: 11.5px; border-radius: 6px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important; border: none; color: #fff;">
                            <i class="fas fa-plus mr-1"></i> Add Tasks
                        </button>
                    </div>
                </div>

                <div id="taskRowsList_${pIdx}">
                    <div class="d-flex align-items-center mb-2 task-row" id="taskRow_${pIdx}_0">
                        <input type="hidden" name="projects[${pIdx}][tasks][0][is_completed]" value="0">
                        <input type="checkbox" name="projects[${pIdx}][tasks][0][is_completed]" value="1" class="task-checkbox-custom task-checkbox" id="taskCheck_${pIdx}_0" onchange="syncSelectAllCheckbox(${pIdx})" title="Unticked = Pending, Ticked = Completed">
                        <input type="text" name="projects[${pIdx}][tasks][0][task_name]" class="form-control form-control-sm task-name-input" placeholder="Task description..." required style="border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 12.5px; height: 36px;">
                        <button type="button" class="btn btn-link text-danger ml-2 p-0 remove-task-btn" onclick="removeTaskRow(this)" style="font-size: 18px; text-decoration: none; display: none;" title="Remove Task">&times;</button>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top" id="tasksFooterRow_${pIdx}">
                    <button type="button" class="btn btn-sm btn-link p-0 font-weight-bold add-task-btn" id="addTaskBtn_${pIdx}" onclick="addTaskRow(${pIdx})" style="color: var(--orb-primary, #4B00E8) !important; text-decoration: none; font-size: 12.5px;">
                        <i class="fas fa-plus mr-1"></i> Add Task
                    </button>
                    <button type="button" class="btn btn-xs font-weight-bold quick-add-tasks-btn" onclick="toggleQuickTaskBox(${pIdx})" style="border-radius: 8px; font-size: 11.5px; padding: 4px 10px; color: var(--orb-primary, #4B00E8); border: 1px solid rgba(75, 0, 232, 0.35); background: rgba(75, 0, 232, 0.05); text-decoration: none;">
                        <i class="fas fa-bolt mr-1" style="color: #eab308;"></i> Quick Add Tasks
                    </button>
                </div>
            </div>
        `;

        container.appendChild(pDiv);
        syncTaskStatusPills(pIdx);

        // Bind direct change & select2 listeners for custom project toggle
        if (window.jQuery) {
            const $newSelect = $(pDiv).find('select.project-select');
            $newSelect.on('change select2:select select2:clear', function() {
                const val = $(this).val();
                const $custInp = $(pDiv).find('.custom-project-input');
                if (val === 'custom') {
                    $custInp.show().css('display', 'block').prop('required', true);
                    setTimeout(function() { $custInp.focus(); }, 60);
                } else {
                    $custInp.hide().css('display', 'none').prop('required', false).val('');
                }
            });
        }

        if (window.initSearchableSelects) {
            window.initSearchableSelects(pDiv);
        }
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
            $(document).on('change select2:select select2:clear', 'select.project-select', function() {
                const $select = $(this);
                const $card = $select.closest('.project-block-card');
                const $customInput = $card.find('.custom-project-input');
                const val = $select.val();

                if (val === 'custom') {
                    $customInput.show().css('display', 'block').prop('required', true);
                    setTimeout(function() { $customInput.focus(); }, 60);
                } else {
                    $customInput.hide().css('display', 'none').prop('required', false).val('');
                }
            });

            syncTaskStatusPills(0);

            $('#webPunchInModal').on('shown.bs.modal', function() {
                if (window.initSearchableSelects) {
                    window.initSearchableSelects(this);
                }
                const selectEl = document.getElementById('web_work_mode_select');
                const mode = selectEl ? selectEl.value : 'wfo';
                handleWorkModeChange(mode);
            });

            $('#webPunchOutModal').on('shown.bs.modal', function() {
                if (window.initSearchableSelects) {
                    window.initSearchableSelects(this);
                }
                syncTaskStatusPills(0);
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