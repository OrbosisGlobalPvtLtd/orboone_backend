@extends('layouts.panel', ['active' => 'dashboard'])

@php
    $meta = $dashboard['meta'] ?? [];
    $title = data_get($meta, 'title', 'Employee Dashboard');
    $userName = data_get($meta, 'user_name', 'Employee');
    $currentDate = data_get($meta, 'current_date') ?? now()->format('l, d M Y h:i A');
    $cards = $dashboard['cards'] ?? [];
    $leave = $dashboard['leave'] ?? [];
    $profiles = $dashboard['profiles'] ?? [];

    // Safe Profile & Document retrieval using data_get (supports both objects & arrays)
    $profileStatus = data_get($dashboard, 'employee.profile.profile_status') ?? (data_get($dashboard, 'employee.profile_status') ?? 'pending');
    $profileCompletion = data_get($dashboard, 'employee.profile_completion', 0);
    $isProfileCompleted = ($profileStatus === 'approved' || data_get($dashboard, 'employee.is_profile_completed') == 1) && $profileStatus !== 'rejected';
    
    // Punch variables
    $punchIn = data_get($dashboard, 'attendance_self.today.punch_in_time') ?: data_get($dashboard, 'attendance_self.today.punch_in');
    $punchOut = data_get($dashboard, 'attendance_self.today.punch_out_time') ?: data_get($dashboard, 'attendance_self.today.punch_out');
    $workMins = data_get($dashboard, 'attendance_self.today.total_work_minutes') ?: data_get($dashboard, 'attendance_self.today.gross_work_minutes');
@endphp

@section('page_title', $title)

@section('_content')
<style>
    :root {

        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .emp-dash {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        transition: all 0.3s ease;
    }

    .emp-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    .emp-hero {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        border-radius: 26px;
        padding: 24px 30px;
        margin-bottom: 24px;
        box-shadow: 0 18px 45px rgba(75, 0, 232, .15);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .emp-hero:before {
        content: "";
        position: absolute;
        right: -80px;
        top: -110px;
        width: 360px;
        height: 360px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .11);
    }

    .emp-hero-left {
        position: relative;
        z-index: 2;
    }

    .emp-hero h3 {
        font-size: 28px;
        font-weight: 950;
        margin: 0;
        line-height: 1.2;
    }

    .emp-hero p {
        font-size: 14px;
        font-weight: 650;
        margin-top: 6px;
        opacity: .92;
    }

    .emp-hero small {
        font-size: 12px;
        opacity: 0.85;
        display: block;
        margin-top: 10px;
        font-weight: 750;
    }

    .emp-hero-right {
        position: relative;
        z-index: 2;
    }

    .emp-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff !important;
        border-radius: 99px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 850;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        backdrop-filter: blur(8px);
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        padding: 20px;
        box-shadow: var(--orb-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 138px;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(16, 24, 40, .10);
    }

    .stat-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .stat-icon {
        height: 42px;
        width: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--orb-soft);
        color: var(--orb-primary);
        font-size: 18px;
    }

    .stat-info {
        margin-top: 14px;
    }

    .stat-title {
        color: var(--orb-muted);
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-value {
        font-size: 22px;
        font-weight: 950;
        color: var(--orb-text);
        margin-top: 6px;
        line-height: 1.2;
    }

    .stat-helper {
        font-size: 11.5px;
        color: var(--orb-muted);
        margin-top: 6px;
        font-weight: 650;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dash-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .orb-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .orb-card-head {
        padding: 18px 24px;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(180deg, #fff, #FAFBFF);
    }

    .orb-card-head h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 950;
        color: var(--orb-text);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .orb-card-head h5 i {
        color: var(--orb-primary);
    }

    .orb-card-body {
        padding: 20px 24px;
        flex-grow: 1;
    }

    .activity-item {
        display: flex;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid #F1F3F8;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-dot {
        height: 10px;
        width: 10px;
        border-radius: 50%;
        background: var(--orb-primary);
        margin-top: 6px;
        flex-shrink: 0;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .quick-action-btn {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 14px 10px;
        text-align: center;
        transition: all 0.2s ease;
        text-decoration: none !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .quick-action-btn:hover {
        background: var(--orb-soft);
        border-color: #D9CCFF;
        transform: translateY(-2px);
    }

    .quick-action-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #F4F2FF;
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: all 0.2s ease;
    }

    .quick-action-btn:hover .quick-action-icon {
        background: var(--orb-primary);
        color: #fff;
    }

    .quick-action-label {
        font-size: 11px;
        font-weight: 900;
        color: var(--orb-text);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    /* Empty states */
    .empty-block {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 30px 20px;
    }

    .empty-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #F8FAFC;
        color: var(--orb-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 12px;
    }

    .empty-title {
        font-size: 14px;
        font-weight: 850;
        color: var(--orb-text);
        margin-bottom: 4px;
    }

    .empty-desc {
        font-size: 12px;
        color: var(--orb-muted);
        max-width: 250px;
    }

    @media(max-width:1200px) {
        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .dash-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width:992px) {
        .emp-dash {
            padding: 18px;
        }
    }

    @media(max-width:768px) {
        .emp-dash {
            padding: 12px;
        }
        .emp-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 20px;
            border-radius: 20px;
            gap: 14px;
        }
        .emp-hero h3 {
            font-size: 22px;
        }
        .emp-hero-badge {
            width: 100%;
            justify-content: center;
        }
        .stat-grid {
            grid-template-columns: 1fr;
        }
        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="emp-dash">
    <div class="emp-container">

        @php
            $todayDate = \Carbon\Carbon::now()->toDateString();
            $empObj = \App\Models\HRMS\Employee\EmployeeM::where('user_id', auth()->id())->first();
            $canWebPunch = true;
            $todayRecord = $empObj ? \Illuminate\Support\Facades\DB::table('attendances')->where('employee_id', $empObj->id)->whereDate('attendance_date', $todayDate)->first() : null;
            $hasPunchedIn = !empty($todayRecord?->punch_in_time);
            $hasPunchedOut = !empty($todayRecord?->punch_out_time);
            $isPunchBlocked = ($todayRecord?->is_blocked || $todayRecord?->is_punch_blocked || ($todayRecord?->attendance_status ?? '') === 'punch_blocked') && !$todayRecord?->is_admin_unlocked;
        @endphp

        <!-- Hero Header -->
        <div class="emp-hero">
            <div class="emp-hero-left">
                <h3>Welcome, {{ $userName }}</h3>
                <p>Employee Self Service Dashboard</p>
                <small><i class="far fa-clock"></i> {{ $currentDate }}</small>
            </div>
            <div class="emp-hero-right">
                @if ($canWebPunch)
                    @if ($isPunchBlocked)
                        <button type="button" class="btn btn-secondary font-weight-bold px-4 py-2 shadow-none" disabled style="border-radius: 50px; font-weight: 800; font-size: 14px; cursor: not-allowed; opacity: 0.7; background: #64748b !important; color: #fff; border: none;">
                            <i class="fas fa-ban mr-2"></i> Punch Blocked
                        </button>
                    @elseif (!$hasPunchedIn)
                        <button type="button" class="btn font-weight-bold px-4 py-2 shadow" data-toggle="modal" data-target="#webPunchInModal" style="border-radius: 50px; font-weight: 800; font-size: 14px; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important; color: #fff !important; border: 1px solid rgba(255, 255, 255, 0.3);">
                            <i class="fas fa-fingerprint mr-2"></i> Punch In
                        </button>
                    @elseif (!$hasPunchedOut)
                        <button type="button" class="btn btn-danger font-weight-bold px-4 py-2 shadow" data-toggle="modal" data-target="#webPunchOutModal" style="border-radius: 50px; font-weight: 800; font-size: 14px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none;">
                            <i class="fas fa-sign-out-alt mr-2"></i> Punch Out
                        </button>
                    @else
                        <span class="badge badge-success px-4 py-2 font-weight-bold shadow-sm" style="border-radius: 50px; font-size: 14px;">
                            <i class="fas fa-check-circle mr-1"></i> Attendance Completed Today
                        </span>
                    @endif
                @else
                    <span class="emp-hero-badge">
                        <i class="fas fa-mobile-alt"></i> Mobile App Required for Attendance
                    </span>
                @endif
            </div>
        </div>

        {{-- Flash Messages & Validation Alerts --}}
        @if (session('error') || session('danger'))
            <div class="alert alert-danger border-0 shadow-lg mb-4 mt-3" style="border-radius: 18px; background: #fef2f2; border-left: 6px solid #ef4444 !important; padding: 18px 22px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle text-danger fa-2x mr-3"></i>
                    <div>
                        <h6 style="color: #991b1b; font-weight: 800; margin: 0 0 2px 0;">Punch-In / Attendance Alert</h6>
                        <p class="mb-0 text-dark font-weight-bold" style="font-size: 14px;">{{ session('error') ?? session('danger') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-lg mb-4 mt-3" style="border-radius: 18px; background: #f0fdf4; border-left: 6px solid #22c55e !important; padding: 18px 22px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle text-success fa-2x mr-3"></i>
                    <div>
                        <h6 style="color: #166534; font-weight: 800; margin: 0 0 2px 0;">Success</h6>
                        <p class="mb-0 text-dark font-weight-bold" style="font-size: 14px;">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger border-0 shadow-lg mb-4 mt-3" style="border-radius: 18px; background: #fef2f2; border-left: 6px solid #ef4444 !important; padding: 18px 22px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-danger fa-2x mr-3"></i>
                    <div>
                        <h6 style="color: #991b1b; font-weight: 800; margin: 0 0 2px 0;">Punch-In Form Errors</h6>
                        <ul class="mb-0 text-dark font-weight-bold pl-3" style="font-size: 14px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Punch Blocked Warning Banner (Displayed right below Hero Header) --}}
        @if ($isPunchBlocked)
            <div class="alert alert-danger border-0 shadow-lg mb-4 mt-4" style="border-radius: 20px; background: #fef2f2; border-left: 6px solid #ef4444 !important; padding: 22px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-lock text-danger fa-3x mr-3"></i>
                    <div>
                        <h5 style="color: #991b1b; font-weight: 800; margin: 0 0 4px 0;">Punch In Blocked for Today</h5>
                        <p class="mb-0 text-dark font-weight-bold">Your attendance punch-in is currently blocked by HR system for today. Please contact HR Administrator to request an unlock.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Profile Verification Status Banner (Only displayed if Profile Incomplete or Pending) -->
        @if(!$isProfileCompleted && $profileStatus !== 'approved')
        <div class="orb-card mb-4" style="border-radius: 22px; border: 1px solid var(--orb-border); box-shadow: var(--orb-shadow);">
            <div class="orb-card-body d-flex align-items-center justify-content-between flex-wrap gap-3 py-3 px-4" style="padding: 16px 24px;">
                <div class="d-flex align-items-center gap-3">
                    @if($profileStatus === 'pending' && $profileCompletion < 100)
                        <div class="icon-circle" style="width:46px; height:46px; border-radius:14px; background:#FEF3C7; color:#D97706; display:flex; align-items:center; justify-content:center; font-size:18px;">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0; font-size: 14px; font-weight: 900; color: var(--orb-text);">Profile Verification: <span class="text-warning">Incomplete</span></h5>
                            <p style="margin: 2px 0 0; font-size: 12px; font-weight: 650; color: var(--orb-muted);">Please fill out your self-service profile details and upload mandatory documents to submit for verification.</p>
                        </div>
                    @elseif($profileStatus === 'submitted' || ($profileStatus === 'pending' && $profileCompletion == 100))
                        <div class="icon-circle" style="width:46px; height:46px; border-radius:14px; background:#E0F2FE; color:#0369A1; display:flex; align-items:center; justify-content:center; font-size:18px;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0; font-size: 14px; font-weight: 900; color: var(--orb-text);">Profile Verification: <span class="text-primary">Submitted for Verification</span></h5>
                            <p style="margin: 2px 0 0; font-size: 12px; font-weight: 650; color: var(--orb-muted);">Your profile details are undergoing verification. All fields are locked for editing.</p>
                        </div>
                    @elseif($profileStatus === 'rejected')
                        <div class="icon-circle" style="width:46px; height:46px; border-radius:14px; background:#FEE2E2; color:#B91C1C; display:flex; align-items:center; justify-content:center; font-size:18px;">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0; font-size: 14px; font-weight: 900; color: var(--orb-text);">Profile Verification: <span class="text-danger">Rejected / Requires Correction</span></h5>
                            <p style="margin: 2px 0 0; font-size: 12px; font-weight: 650; color: var(--orb-muted);">HR has returned your profile for correction. Click 'Correct Profile' to fix your information.</p>
                        </div>
                    @endif
                </div>
                <div>
                    @if($profileStatus === 'pending' && $profileCompletion < 100)
                        <a href="{{ route('profile.index') }}" class="btn btn-warning px-4 font-weight-bold" style="border-radius:12px; font-weight:800; font-size:12px; color:#fff; background:#D97706; border-color:#D97706; min-height:36px; display:inline-flex; align-items:center; gap:6px;">
                            <i class="fas fa-edit"></i> Complete Profile
                        </a>
                    @elseif($profileStatus === 'submitted' || ($profileStatus === 'pending' && $profileCompletion == 100))
                        <a href="{{ route('profile.index') }}" class="btn btn-primary px-4 font-weight-bold" style="border-radius:12px; font-weight:800; font-size:12px; color:#fff; background:linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); border:none; min-height:36px; display:inline-flex; align-items:center; gap:6px;">
                            <i class="fas fa-eye"></i> View Submitted Profile
                        </a>
                    @elseif($profileStatus === 'rejected')
                        <a href="{{ route('profile.index') }}" class="btn btn-danger px-4 font-weight-bold" style="border-radius:12px; font-weight:800; font-size:12px; color:#fff; background:#B91C1C; border-color:#B91C1C; min-height:36px; display:inline-flex; align-items:center; gap:6px;">
                            <i class="fas fa-tools"></i> Correct Profile
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Primary Status Cards Grid -->
        <div class="stat-grid">
            <!-- 1. Today Attendance Status -->
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Today Attendance</div>
                    @php
                        $dashStatus = data_get($dashboard, 'attendance_self.today_status') ?? 'Not Marked';
                        if (strtolower((string) $dashStatus) === 'lwp') {
                            $dashStatus = 'Absent';
                        }
                    @endphp
                    <div class="stat-value">{{ $dashStatus }}</div>
                    <div class="stat-helper">{{ data_get($dashboard, 'attendance_self.punch_summary') ?? 'No punches registered' }}</div>
                </div>
            </div>

            <!-- 2. Punch Timings -->
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Punch Timings</div>
                    <div class="stat-value">
                        {{ $punchIn ? \Carbon\Carbon::parse($punchIn)->format('h:i A') : '--:--' }} 
                        / 
                        {{ $punchOut ? \Carbon\Carbon::parse($punchOut)->format('h:i A') : '--:--' }}
                    </div>
                    <div class="stat-helper">Punch In / Punch Out today</div>
                </div>
            </div>

            <!-- 3. Working Hours Today -->
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon"><i class="fas fa-business-time"></i></div>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Working Hours</div>
                    <div class="stat-value">
                        {{ $workMins ? floor($workMins / 60).'h '.($workMins % 60).'m' : '0h 0m' }}
                    </div>
                    <div class="stat-helper">Gross work minutes today</div>
                </div>
            </div>

            <!-- 4. Leave Balance -->
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon"><i class="fas fa-plane-departure"></i></div>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Leave Balance</div>
                    <div class="stat-value">{{ data_get($dashboard, 'leave_self.summary') ?? (data_get($leave, 'paid_balance') ?? 0) }} Days</div>
                    <div class="stat-helper">Available remaining balances</div>
                </div>
            </div>
        </div>

        <div class="dash-grid">
            <!-- Row 1 Left: Sleek Compact Monthly Attendance Calendar Card -->
            <div class="orb-card">
                <div class="orb-card-head d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
                    <h5 style="margin: 0; font-size: 14px; font-weight: 950; color: var(--orb-text); display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-calendar-alt text-primary" style="font-size: 16px;"></i>
                        <span>Attendance Calendar</span>
                    </h5>

                    <!-- Compact Month Navigation Controls -->
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-light border shadow-sm px-2 py-1" id="cal_prev_btn" onclick="changeCalMonth(-1)" style="border-radius: 8px; font-weight: 800;">
                            <i class="fas fa-chevron-left" style="font-size: 10px;"></i>
                        </button>
                        
                        <select id="cal_month_select" class="form-control form-control-sm border shadow-sm px-1" style="border-radius: 8px; font-weight: 800; width: 88px; font-size: 11px; height: 28px;" onchange="fetchCalData()">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('M') }}
                                </option>
                            @endforeach
                        </select>

                        <select id="cal_year_select" class="form-control form-control-sm border shadow-sm px-1" style="border-radius: 8px; font-weight: 800; width: 68px; font-size: 11px; height: 28px;" onchange="fetchCalData()">
                            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>

                        <button type="button" class="btn btn-sm btn-light border shadow-sm px-2 py-1" id="cal_next_btn" onclick="changeCalMonth(1)" style="border-radius: 8px; font-weight: 800;">
                            <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                        </button>
                    </div>
                </div>

                <div class="orb-card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Calendar Grid Headers (MON TUE WED THU FRI SAT SUN) -->
                        <div class="calendar-grid-header mb-2" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center;">
                            <div class="cal-day-header font-weight-bold text-dark" style="font-size: 10.5px; letter-spacing: 0.5px; text-transform: uppercase;">MON</div>
                            <div class="cal-day-header font-weight-bold text-dark" style="font-size: 10.5px; letter-spacing: 0.5px; text-transform: uppercase;">TUE</div>
                            <div class="cal-day-header font-weight-bold text-dark" style="font-size: 10.5px; letter-spacing: 0.5px; text-transform: uppercase;">WED</div>
                            <div class="cal-day-header font-weight-bold text-dark" style="font-size: 10.5px; letter-spacing: 0.5px; text-transform: uppercase;">THU</div>
                            <div class="cal-day-header font-weight-bold text-dark" style="font-size: 10.5px; letter-spacing: 0.5px; text-transform: uppercase;">FRI</div>
                            <div class="cal-day-header font-weight-bold text-secondary" style="font-size: 10.5px; letter-spacing: 0.5px; text-transform: uppercase;">SAT</div>
                            <div class="cal-day-header font-weight-bold text-danger" style="font-size: 10.5px; letter-spacing: 0.5px; text-transform: uppercase;">SUN</div>
                        </div>

                        <!-- Compact Dynamic Days Grid Container -->
                        <div id="attendance_calendar_grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; min-height: 180px;">
                            <!-- Rendered dynamically by JavaScript -->
                        </div>
                    </div>

                    <!-- Shifted Bottom Summary Badges Bar -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mt-3 pt-2" style="border-top: 1px solid #F1F5F9; font-size: 10.5px;">
                        <span class="px-2 py-1 rounded-pill" style="background:#DCFCE7; color:#15803D; font-weight:800; display:inline-flex; align-items:center; gap:4px;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#16A34A;"></span> Present: <strong id="cal_count_present">0</strong>
                        </span>
                        <span class="px-2 py-1 rounded-pill" style="background:#FEE2E2; color:#B91C1C; font-weight:800; display:inline-flex; align-items:center; gap:4px;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#EF4444;"></span> Absent: <strong id="cal_count_absent">0</strong>
                        </span>
                        <span class="px-2 py-1 rounded-pill" style="background:#FEF3C7; color:#B45309; font-weight:800; display:inline-flex; align-items:center; gap:4px;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#F59E0B;"></span> Half Day: <strong id="cal_count_half_day">0</strong>
                        </span>
                        <span class="px-2 py-1 rounded-pill" style="background:#DBEAFE; color:#1D4ED8; font-weight:800; display:inline-flex; align-items:center; gap:4px;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#3B82F6;"></span> Leave: <strong id="cal_count_leave">0</strong>
                        </span>
                        <span class="px-2 py-1 rounded-pill" style="background:#F1F5F9; color:#475467; font-weight:800; display:inline-flex; align-items:center; gap:4px;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#64748B;"></span> Holiday/Off: <strong id="cal_count_holiday_off">0</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Row 1 Right: Quick Actions -->
            <div class="orb-card">
                <div class="orb-card-head">
                    <h5><i class="fas fa-bolt text-primary"></i> Quick Actions</h5>
                </div>
                <div class="orb-card-body p-3">
                    <div class="quick-actions-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <a href="{{ Route::has('leave-requests.create') ? route('leave-requests.create') : '#' }}" class="quick-action-btn">
                            <div class="quick-action-icon"><i class="fas fa-plane-departure text-primary"></i></div>
                            <div class="quick-action-label">Apply Leave</div>
                        </a>
                        
                        <a href="{{ Route::has('hrms.attendance.my-wfh.index') ? route('hrms.attendance.my-wfh.index') : (Route::has('hrms.attendance.wfh.index') ? route('hrms.attendance.wfh.index') : '#') }}" class="quick-action-btn">
                            <div class="quick-action-icon"><i class="fas fa-laptop-house text-info"></i></div>
                            <div class="quick-action-label">WFH Apply</div>
                        </a>

                        <a href="{{ Route::has('hrms.attendance.my-holiday-work.index') ? route('hrms.attendance.my-holiday-work.index') : (Route::has('hrms.attendance.work-reports') ? route('hrms.attendance.work-reports') : '#') }}" class="quick-action-btn">
                            <div class="quick-action-icon"><i class="fas fa-briefcase text-warning"></i></div>
                            <div class="quick-action-label">Work Request</div>
                        </a>

                        <a href="{{ Route::has('hrms.attendance.regularizations.index') ? route('hrms.attendance.regularizations.index') : '#' }}" class="quick-action-btn">
                            <div class="quick-action-icon"><i class="fas fa-user-clock text-danger"></i></div>
                            <div class="quick-action-label">Regularization</div>
                        </a>

                        <a href="{{ Route::has('attendances.monthly-report') ? route('attendances.monthly-report') : (Route::has('hrms.attendance.monthly_summary.index') ? route('hrms.attendance.monthly_summary.index') : '#') }}" class="quick-action-btn">
                            <div class="quick-action-icon"><i class="fas fa-chart-bar" style="color:#6366F1;"></i></div>
                            <div class="quick-action-label">Monthly Report</div>
                        </a>

                        <a href="{{ Route::has('hrms.attendance.my') ? route('hrms.attendance.my') : '#' }}" class="quick-action-btn">
                            <div class="quick-action-icon"><i class="fas fa-calendar-check text-success"></i></div>
                            <div class="quick-action-label">Attendance</div>
                        </a>

                        <a href="{{ Route::has('enterprise-payroll.self.payslips') ? route('enterprise-payroll.self.payslips') : '#' }}" class="quick-action-btn">
                            <div class="quick-action-icon"><i class="fas fa-file-invoice-dollar" style="color:#7C3AED;"></i></div>
                            <div class="quick-action-label">Payslips</div>
                        </a>

                        <a href="{{ Route::has('hrms.leave.balances.index') ? route('hrms.leave.balances.index') : '#' }}" class="quick-action-btn">
                            <div class="quick-action-icon"><i class="fas fa-balance-scale text-secondary"></i></div>
                            <div class="quick-action-label">Balances</div>
                        </a>

                        <a href="{{ Route::has('hrms.documents.self.index') ? route('hrms.documents.self.index') : '#' }}" class="quick-action-btn">
                            <div class="quick-action-icon"><i class="fas fa-folder-open text-primary"></i></div>
                            <div class="quick-action-label">Documents</div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Row 2 Left: Latest Announcements -->
            <div class="orb-card">
                <div class="orb-card-head">
                    <h5><i class="fas fa-bullhorn"></i> Latest Announcements</h5>
                    <a href="{{ Route::has('employee.announcements.index') ? route('employee.announcements.index') : '#' }}" class="btn btn-sm btn-outline-primary" style="border-radius:10px; font-weight:800; font-size:11.5px; padding:4px 10px;">View All</a>
                </div>
                <div class="orb-card-body">
                    @php
                        $announcements = data_get($dashboard, 'announcements') ?? (data_get($dashboard, 'latest_announcements') ?? []);
                    @endphp
                    @forelse($announcements as $announcement)
                    <div class="activity-item">
                        <span class="activity-dot"></span>
                        <div>
                            <strong style="color:var(--orb-text); font-size:13.5px;">{{ data_get($announcement, 'title', 'Notice') }}</strong><br>
                            <small class="text-muted" style="font-size:12px; margin-top:2px; display:block;">{{ data_get($announcement, 'description', '') }}</small>
                            <small class="text-primary font-weight-bold" style="font-size:11px; margin-top:4px; display:block;">
                                {{ data_get($announcement, 'created_at') ? \Carbon\Carbon::parse(data_get($announcement, 'created_at'))->diffForHumans() : '-' }}
                            </small>
                        </div>
                    </div>
                    @empty
                    <div class="empty-block">
                        <div class="empty-icon"><i class="fas fa-bullhorn"></i></div>
                        <div class="empty-title">No announcements</div>
                        <div class="empty-desc">There are no recent announcements published by the management.</div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Row 2 Right: My Payslips -->
            <div class="orb-card">
                <div class="orb-card-head">
                    <h5><i class="fas fa-file-invoice-dollar"></i> My Payslips</h5>
                    <a href="{{ Route::has('enterprise-payroll.self.payslips') ? route('enterprise-payroll.self.payslips') : '#' }}" class="btn btn-sm btn-outline-primary" style="border-radius:10px; font-weight:800; font-size:11.5px; padding:4px 10px;">View All</a>
                </div>
                <div class="orb-card-body d-flex flex-column justify-content-center align-items-center text-center py-4">
                    @php
                        $latestPayslip = data_get($dashboard, 'latest_payslip');
                    @endphp
                    @if(isset($latestPayslip) && data_get($latestPayslip, 'label') !== '-')
                        <div class="mb-3" style="width:58px; height:58px; border-radius:16px; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; font-size:24px;">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <h6 class="font-weight-bold mb-1" style="font-size:15px; color:var(--orb-text);">{{ data_get($latestPayslip, 'label') }} Payslip</h6>
                        <p class="text-muted small px-3 mb-3">{{ data_get($latestPayslip, 'subtitle') }}</p>
                        <a href="{{ Route::has('enterprise-payroll.payslips.download') && data_get($latestPayslip, 'id') ? route('enterprise-payroll.payslips.download', data_get($latestPayslip, 'id')) : (Route::has('enterprise-payroll.self.payslips') ? route('enterprise-payroll.self.payslips') : '#') }}" class="btn btn-primary px-4 font-weight-bold" style="border-radius:12px; font-weight:800;">
                            <i class="fas fa-download"></i> Download Slip
                        </a>
                    @else
                        <div class="empty-block w-100">
                            <div class="empty-icon" style="color:#D0D5DD;"><i class="fas fa-file-invoice"></i></div>
                            <div class="empty-title" style="color:var(--orb-muted);">No payslips available</div>
                            <div class="empty-desc">No salary slip has been generated for your record yet.</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Row 3 Left: My Leaves -->
            <div class="orb-card">
                <div class="orb-card-head">
                    <h5><i class="fas fa-plane-departure"></i> My Leave Applications</h5>
                    <a href="{{ Route::has('leave-requests.create') ? route('leave-requests.create') : '#' }}" class="btn btn-sm btn-outline-primary" style="border-radius:10px; font-weight:800; font-size:11.5px; padding:4px 10px;">Apply Leave</a>
                </div>
                <div class="orb-card-body d-flex flex-column justify-content-center align-items-center text-center py-4">
                    <div class="row w-100 mb-3 text-center">
                        <div class="col-4">
                            <div style="font-size: 20px; font-weight: 950; color: var(--orb-primary);">
                                {{ data_get($dashboard, 'leave_self.total_remaining', 0) }}
                            </div>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.04em;">Remaining</small>
                        </div>
                        <div class="col-4" style="border-left: 1px solid #E2E8F0; border-right: 1px solid #E2E8F0;">
                            <div style="font-size: 20px; font-weight: 950; color: #16A34A;">
                                {{ data_get($dashboard, 'leave_self.balance.total_used', 0) }}
                            </div>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.04em;">Used</small>
                        </div>
                        <div class="col-4">
                            <div style="font-size: 20px; font-weight: 950; color: #D97706;">
                                {{ data_get($dashboard, 'leave_self.pending', 0) }}
                            </div>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.04em;">Pending</small>
                        </div>
                    </div>
                    <p class="text-muted small px-3 mb-3">Keep track of your leaves balance and easily apply for leaves online.</p>
                    <a href="{{ Route::has('hrms.leave.balances.index') ? route('hrms.leave.balances.index') : '#' }}" class="btn btn-light px-4 font-weight-bold" style="border-radius:12px; font-weight:800; border: 1px solid #E4E7EC;">
                        <i class="fas fa-calendar-alt"></i> Leave Balances
                    </a>
                </div>
            </div>

            <!-- Row 3 Right: Documents & Profile -->
            <div class="orb-card">
                <div class="orb-card-head">
                    <h5><i class="fas fa-id-card"></i> Documents & Profile Status</h5>
                </div>
                <div class="orb-card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small font-weight-bold text-muted uppercase">Profile Verification</span>
                            <span class="small font-weight-bold text-primary">{{ $profileCompletion }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 99px; background:#F1F5F9;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $profileCompletion }}%; border-radius:99px; background: linear-gradient(90deg, var(--orb-primary), var(--orb-secondary));" aria-valuenow="{{ $profileCompletion }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-4 text-center">
                            <div style="font-size: 18px; font-weight: 950; color: #D97706;">
                                {{ data_get($dashboard, 'documents_self.pending', 0) }}
                            </div>
                            <small class="text-muted font-weight-bold" style="font-size: 9px; text-transform: uppercase;">Pending</small>
                        </div>
                        <div class="col-4 text-center" style="border-left:1px solid #EEF2F6; border-right:1px solid #EEF2F6;">
                            <div style="font-size: 18px; font-weight: 950; color: #16A34A;">
                                {{ data_get($dashboard, 'documents_self.verified', 0) }}
                            </div>
                            <small class="text-muted font-weight-bold" style="font-size: 9px; text-transform: uppercase;">Verified</small>
                        </div>
                        <div class="col-4 text-center">
                            <div style="font-size: 18px; font-weight: 950; color: #DC2626;">
                                {{ data_get($dashboard, 'documents_self.rejected', 0) }}
                            </div>
                            <small class="text-muted font-weight-bold" style="font-size: 9px; text-transform: uppercase;">Rejected</small>
                        </div>
                    </div>

                    <a href="{{ Route::has('hrms.documents.self.index') ? route('hrms.documents.self.index') : '#' }}" class="btn btn-light w-100 font-weight-bold" style="border-radius:14px; font-weight:800; border: 1px solid #E4E7EC; height: 43px; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                        <i class="fas fa-folder-open"></i> Upload & Manage Documents
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Floating Bottom-Right Fixed Web Punch Button Overlay --}}
@if ($canWebPunch)
    <div style="position: fixed; bottom: 32px; right: 32px; z-index: 9999;">
        @if ($isPunchBlocked)
            <button type="button" class="btn btn-secondary font-weight-bold px-4 py-3 shadow d-flex align-items-center" disabled style="border-radius: 50px; font-size: 15px; font-weight: 900; cursor: not-allowed; opacity: 0.75; background: #64748b !important; color: #fff; border: 2px solid #ffffff;">
                <i class="fas fa-ban fa-lg mr-2"></i> PUNCH BLOCKED
            </button>
        @elseif (!$hasPunchedIn)
            <button type="button" class="btn font-weight-bold px-4 py-3 shadow-lg d-flex align-items-center" data-toggle="modal" data-target="#webPunchInModal" style="border-radius: 50px; font-size: 15px; font-weight: 900; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important; color: #fff !important; border: 2px solid #ffffff; box-shadow: 0 12px 30px rgba(75, 0, 232, 0.4) !important;">
                <i class="fas fa-fingerprint fa-lg mr-2"></i> PUNCH IN
            </button>
        @elseif (!$hasPunchedOut)
            <button type="button" class="btn btn-danger font-weight-bold px-4 py-3 shadow-lg d-flex align-items-center" data-toggle="modal" data-target="#webPunchOutModal" style="border-radius: 50px; font-size: 15px; font-weight: 900; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: 2px solid #ffffff; box-shadow: 0 12px 30px rgba(239, 68, 68, 0.45) !important;">
                <i class="fas fa-sign-out-alt fa-lg mr-2"></i> PUNCH OUT
            </button>
        @endif
    </div>
@endif

{{-- Include Punch In & Punch Out Modals and Scripts --}}
@include('dashboard.partials.employee-dashboard', ['only_modals' => true])

<script>
function fetchCalData() {
    var m = $('#cal_month_select').val();
    var y = $('#cal_year_select').val();
    
    $('#attendance_calendar_grid').html('<div class="w-100 text-center py-5" style="grid-column: span 7;"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i><p class="text-muted font-weight-bold" style="font-size:13px;">Loading attendance calendar...</p></div>');

    $.ajax({
        url: "{{ route('dashboard.attendance_calendar') }}",
        type: "GET",
        data: { month: m, year: y },
        success: function(res) {
            if (res && res.status) {
                renderCalendarGrid(res);
            }
        },
        error: function() {
            $('#attendance_calendar_grid').html('<div class="w-100 text-center py-5 text-danger" style="grid-column: span 7;"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p class="font-weight-bold" style="font-size:13px;">Failed to load calendar data.</p></div>');
        }
    });
}

function renderCalendarGrid(data) {
    var s = data.summary || {};
    var totalPresent = (s.present || 0) + (s.late || 0);
    var totalHolidayOff = (s.holiday || 0) + (s.week_off || 0);

    $('#cal_count_present').text(totalPresent);
    $('#cal_count_absent').text(s.absent || 0);
    $('#cal_count_half_day').text(s.half_day || 0);
    $('#cal_count_leave').text(s.leave || 0);
    $('#cal_count_holiday_off').text(totalHolidayOff);

    var gridHtml = '';
    var firstDay = data.first_day_of_week;
    
    for (var i = 0; i < firstDay; i++) {
        gridHtml += '<div class="cal-day-cell empty-cell" style="background: #FAFAFA; border: 1px dashed #E2E8F0; border-radius: 8px; min-height: 42px;"></div>';
    }

    var days = data.days || {};
    Object.keys(days).forEach(function(dateKey) {
        var item = days[dateKey];
        var isToday = item.is_today;
        
        var todayBorder = isToday ? 'border: 2px solid #4B00E8 !important; box-shadow: 0 0 8px rgba(75, 0, 232, 0.35);' : 'border: 1px solid #E7EAF3;';
        
        var displayLabel = item.label || '';
        if (item.status === 'holiday') {
            displayLabel = displayLabel.replace(/^Holiday:\s*/i, '');
        } else if (item.status === 'week_off') {
            displayLabel = 'Week Off';
        }

        var tooltip = item.date + ': ' + displayLabel + (item.punch_in ? ' (In: ' + item.punch_in + ' - Out: ' + (item.punch_out || '--') + ')' : '');
        
        gridHtml += '<div class="cal-day-cell p-1 position-relative text-center" title="' + tooltip + '" style="background: ' + item.bg + '; border-radius: 10px; min-height: 44px; display: flex; flex-direction: column; align-items: center; justify-content: center; ' + todayBorder + ' transition: all 0.2s ease; cursor: pointer;">';
        
        gridHtml += '<div style="font-size: 11.5px; font-weight: 900; color: #1E293B; line-height: 1.1;">' + item.day + '</div>';
        
        gridHtml += '<div style="font-size: 7.5px; font-weight: 900; color: ' + item.color + '; text-transform: uppercase; margin-top: 3px; line-height: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; width: 100%; padding: 0 2px;" title="' + displayLabel + '">' + displayLabel + '</div>';

        gridHtml += '</div>';
    });

    $('#attendance_calendar_grid').html(gridHtml);
}

function changeCalMonth(delta) {
    var curM = parseInt($('#cal_month_select').val());
    var curY = parseInt($('#cal_year_select').val());
    
    curM += delta;
    if (curM > 12) {
        curM = 1;
        curY++;
    } else if (curM < 1) {
        curM = 12;
        curY--;
    }
    
    $('#cal_month_select').val(curM);
    $('#cal_year_select').val(curY);
    fetchCalData();
}

function resetCalToToday() {
    var d = new Date();
    $('#cal_month_select').val(d.getMonth() + 1);
    $('#cal_year_select').val(d.getFullYear());
    fetchCalData();
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined') {
        fetchCalData();
    }
});
</script>
@endsection
