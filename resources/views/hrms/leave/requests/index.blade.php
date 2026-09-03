@extends('layouts.panel')

@section('page_title', 'My Leave Requests')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
@include('hrms.enterprise-payroll.partials.styles')

<style>
    body, .ep-page, .set-page, button, input, select, textarea, table {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
    }

    .leave-page-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Metric Summary Pills & Cards */
    .metric-card-primary {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .metric-card-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(75, 0, 232, 0.08);
        border-color: #CBD5E1;
    }

    .metric-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .metric-val {
        font-size: 24px;
        font-weight: 900;
        color: #0F172A;
        line-height: 1.1;
        letter-spacing: -0.02em;
    }

    .metric-lbl {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748B;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    /* Counter Pills Row */
    .counter-pill-item {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .counter-pill-lbl {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .counter-pill-num {
        font-size: 15px;
        font-weight: 900;
        padding: 3px 10px;
        border-radius: 8px;
    }

    /* Status Badges */
    .orb-pill {
        border-radius: 30px;
        padding: 5px 12px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        letter-spacing: 0.03em;
    }

    .orb-pill.pending {
        background: #FEF3C7;
        color: #B45309;
    }

    .orb-pill.approved {
        background: #D1FAE5;
        color: #047857;
    }

    .orb-pill.rejected {
        background: #FEE2E2;
        color: #B91C1C;
    }

    .orb-pill.cancelled {
        background: #F1F5F9;
        color: #475569;
    }

    .orb-pill.expired {
        background: #F3E8FF;
        color: #6B21A8;
    }

    /* Filter Controls */
    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 2fr auto;
        gap: 14px;
        align-items: flex-end;
    }

    .filter-control {
        width: 100%;
        height: 42px;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 0 14px;
        background: #FFFFFF;
        font-size: 13px;
        font-weight: 600;
        color: #0F172A;
        outline: none;
        transition: all 0.2s ease;
    }

    .filter-control:focus {
        border-color: #4F46E5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .filter-btn-reset {
        height: 42px;
        padding: 0 18px;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        font-weight: 800;
        font-size: 12px;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .filter-btn-reset:hover {
        background: #F8FAFC;
        color: #0F172A;
        border-color: #CBD5E1;
    }

    /* DataTable toolbar styling */
    .leave-dt-toolbar {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        padding: 14px 24px !important;
        background: #FFFFFF !important;
        border-top: 1px solid #E2E8F0 !important;
        border-bottom: 1px solid #E2E8F0 !important;
    }

    .dt-buttons .dt-button,
    .leave-export-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 34px !important;
        padding: 0 14px !important;
        border-radius: 10px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border: 1px solid #E2E8F0 !important;
        background: #FFFFFF !important;
        color: #4F46E5 !important;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .dt-buttons .dt-button:hover,
    .leave-export-btn:hover {
        background: #4F46E5 !important;
        color: #FFFFFF !important;
        border-color: #4F46E5 !important;
    }
</style>
@endsection

@section('_content')
<div class="ep-page">
    <div class="leave-page-container">
        
        <!-- Premium Purple Gradient Hero Header -->
        <div class="ep-hero" style="background: linear-gradient(135deg, #4B00E8 0%, #7C3AED 100%);">
            <div>
                <div class="ep-kicker"><i class="fas fa-plane-departure"></i> LEAVE MANAGEMENT</div>
                <h1 style="font-size: 26px; font-weight: 900; color: #fff;">{{ $isAdminOrHr ? 'Leave Requests Directory' : 'My Leave Requests' }}</h1>
                <p style="font-size: 13px; color: rgba(255,255,255,0.85); margin-bottom: 0;">{{ $isAdminOrHr ? 'Review employee leave applications, quota splits, approval states, and remaining allocations.' : 'Track your applied leaves, quota splits, approval states, and remaining allocations.' }}</p>
            </div>
            
            <div>
                <a href="{{ route('leave-requests.create') }}" class="btn font-weight-bold" style="background: rgba(255, 255, 255, 0.2); color: #fff; border: 1px solid rgba(255, 255, 255, 0.35); border-radius: 12px; padding: 10px 20px; font-size: 13px; backdrop-filter: blur(6px);">
                    <i class="fas fa-plus-circle mr-1"></i> Apply Leave
                </a>
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        <!-- Leaves Calculation Metadata -->
        @php
            $isConfirmed = $employee ? (bool)$employee->is_permanent : true;
            
            $monthlyQuotaInfo = $employee ? app(\App\Services\HRMS\Leave\MonthlyLeaveQuotaService::class)->getMonthlyQuota($employee) : [
                'current_month_used' => 0,
                'total_monthly_remaining_paid' => 0,
                'monthly_limit' => 0,
                'carry_forward_available' => 0,
            ];

            $paidRemaining = $isConfirmed && $allocation ? (float)($allocation->paid_remaining ?? 0) : 0.0;
            $paidUsed = $isConfirmed && $allocation ? (float)($allocation->paid_used ?? 0) : 0.0;
            $sickRemaining = $isConfirmed && $allocation ? (float)($allocation->sick_remaining ?? 0) : 0.0;
            $compRemaining = $isConfirmed && $allocation ? (float)($allocation->comp_off_remaining ?? 0) : 0.0;
            $lwpUsed = $allocation ? (float) ($allocation->lwp_used ?? 0) : 0.0;

            $alreadyUsedThisMonth = (float) ($monthlyQuotaInfo['current_month_used'] ?? 0);
            $remainingThisMonth = (float) ($monthlyQuotaInfo['total_monthly_remaining_paid'] ?? 0);
            $paidAvailableThisMonth = (float) (($monthlyQuotaInfo['monthly_limit'] ?? 0) + ($monthlyQuotaInfo['carry_forward_available'] ?? 0));

            // Apply November/December rule to dynamic balances
            $currentMonth = Carbon\Carbon::now('Asia/Kolkata')->month;
            if ($allocation && in_array((int) $currentMonth, [11, 12], true) && ($allocation->total_remaining ?? 0) > 10.0) {
                $paidRemaining = round($paidRemaining * 0.5, 2);
                $sickRemaining = round($sickRemaining * 0.5, 2);
                $compRemaining = round($compRemaining * 0.5, 2);
                $remainingThisMonth = round($remainingThisMonth * 0.5, 2);
            }

            // Requests Counts
            $countQuery = DB::table('leave_requests');
            if (!$isAdminOrHr && $employee) {
                $countQuery->where('employee_id', $employee->id);
            } elseif ($isAdminOrHr && request('employee_id')) {
                $countQuery->where('employee_id', request('employee_id'));
            }
            $pendingCount = (clone $countQuery)->where('status', 'pending')->count();
            $approvedCount = (clone $countQuery)->where('status', 'approved')->count();
            $rejectedCount = (clone $countQuery)->where('status', 'rejected')->count();
            $emergencyUsed = (clone $countQuery)->where('status', 'approved')->where('emergency_leave', 1)->count();
            $totalCount = (clone $countQuery)->count();
        @endphp

        <!-- 1. PRIMARY QUOTA CARDS (4-Column Layout) -->
        <div class="row mb-4">
            @if($employee)
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="metric-card-primary">
                    <div class="metric-icon-box" style="background: #ECFDF5; color: #10B981;">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <div class="metric-lbl" title="Annual remaining Paid Leaves">Paid Remaining</div>
                        <div class="metric-val">{{ number_format((float) $paidRemaining, 2) }}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="metric-card-primary">
                    <div class="metric-icon-box" style="background: #FEF2F2; color: #EF4444;">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div>
                        <div class="metric-lbl" title="Annual remaining Sick Leaves">Sick Remaining</div>
                        <div class="metric-val">{{ number_format((float) $sickRemaining, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="metric-card-primary">
                    <div class="metric-icon-box" style="background: #F5F3FF; color: #8B5CF6;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="metric-lbl" title="Available Comp-Off balance">Comp-Off Balance</div>
                        <div class="metric-val">{{ number_format((float) $compRemaining, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="metric-card-primary">
                    <div class="metric-icon-box" style="background: #E0F2FE; color: #0284C7;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="metric-lbl" title="Total Paid Leave balance available for this month (including Carry Forward)">Monthly Paid Balance</div>
                        <div class="metric-val">{{ number_format((float) $remainingThisMonth, 2) }}</div>
                    </div>
                </div>
            </div>
            @else
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="metric-card-primary">
                    <div class="metric-icon-box" style="background: #EEF2FF; color: #4F46E5;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="metric-lbl">Total Requests</div>
                        <div class="metric-val">{{ $totalCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="metric-card-primary">
                    <div class="metric-icon-box" style="background: #FEF3C7; color: #D97706;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="metric-lbl">Pending Review</div>
                        <div class="metric-val">{{ $pendingCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="metric-card-primary">
                    <div class="metric-icon-box" style="background: #ECFDF5; color: #059669;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <div class="metric-lbl">Approved Leaves</div>
                        <div class="metric-val">{{ $approvedCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="metric-card-primary">
                    <div class="metric-icon-box" style="background: #FEE2E2; color: #DC2626;">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div>
                        <div class="metric-lbl">Rejected Leaves</div>
                        <div class="metric-val">{{ $rejectedCount }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- 2. SECONDARY STATS COUNTER ROW -->
        <div class="row mb-4">
            <div class="col-6 col-md-3 col-lg-2 mb-2">
                <div class="counter-pill-item">
                    <span class="counter-pill-lbl"><i class="fas fa-check-circle text-indigo"></i> Paid Used</span>
                    <span class="counter-pill-num" style="background: #EEF2FF; color: #4F46E5;">{{ number_format((float) $paidUsed, 1) }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2 mb-2">
                <div class="counter-pill-item">
                    <span class="counter-pill-lbl"><i class="fas fa-hourglass-half text-amber"></i> Month Used</span>
                    <span class="counter-pill-num" style="background: #FEF3C7; color: #D97706;">{{ number_format((float) $alreadyUsedThisMonth, 1) }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2 mb-2">
                <div class="counter-pill-item">
                    <span class="counter-pill-lbl"><i class="fas fa-user-clock text-slate"></i> LWP Days</span>
                    <span class="counter-pill-num" style="background: #F1F5F9; color: #475569;">{{ number_format((float) $lwpUsed, 1) }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2 mb-2">
                <div class="counter-pill-item">
                    <span class="counter-pill-lbl"><i class="fas fa-spinner text-warning"></i> Pending</span>
                    <span class="counter-pill-num" style="background: #FEF3C7; color: #B45309;">{{ $pendingCount }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2 mb-2">
                <div class="counter-pill-item">
                    <span class="counter-pill-lbl"><i class="fas fa-thumbs-up text-success"></i> Approved</span>
                    <span class="counter-pill-num" style="background: #D1FAE5; color: #047857;">{{ $approvedCount }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2 mb-2">
                <div class="counter-pill-item">
                    <span class="counter-pill-lbl"><i class="fas fa-exclamation-triangle text-pink"></i> Emergency</span>
                    <span class="counter-pill-num" style="background: #FCE7F3; color: #DB2777;">{{ $emergencyUsed }}</span>
                </div>
            </div>
        </div>

        <!-- 3. MAIN TABLE CARD -->
        <div class="ep-card">
            <div class="ep-table-header">
                <div class="ep-table-head-left">
                    <div class="ep-icon-box"><i class="fas fa-list-alt"></i></div>
                    <div>
                        <h5 class="ep-table-title">{{ $isAdminOrHr ? 'Leave Requests Directory' : 'Leave Request History' }}</h5>
                        <p class="ep-table-subtitle">Review active requests, splits, reason logs, and processing states.</p>
                    </div>
                </div>
                
                <div>
                    <a href="{{ route('leave-requests.create') }}" class="btn btn-primary font-weight-bold shadow-sm" style="border-radius: 12px; padding: 8px 18px; font-size: 13px; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); border: none;">
                        <i class="fas fa-plus-circle mr-1"></i> Apply Leave
                    </a>
                </div>
            </div>

            <!-- Filters Header Bar -->
            <div style="border-bottom: 1px solid #E2E8F0; background: #F8FAFC; padding: 18px 24px;">
                <form method="GET" action="{{ route('leave-requests.index') }}" id="leaveFilterForm">
                    <div class="filter-grid" style="grid-template-columns: {{ $isAdminOrHr ? '1.5fr 1.1fr 1fr 1.8fr 0.9fr auto' : '1.2fr 1fr 2fr 0.9fr auto' }};">
                        @if($isAdminOrHr && $employees->isNotEmpty())
                        <div>
                            <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Employee</label>
                            <select name="employee_id" id="filterEmployee" class="filter-control select2-searchable">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ (string)request('employee_id') === (string)$emp->id ? 'selected' : '' }}>
                                        {{ $emp->display_name }} ({{ $emp->employee_code ?? 'EMP' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Leave Type</label>
                            <select name="leave_type_id" id="filterLeaveType" class="filter-control">
                                <option value="">All Types</option>
                                @foreach($leaveTypes as $lt)
                                    <option value="{{ $lt->id }}" {{ (string)request('leave_type_id') === (string)$lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Status</label>
                            <select name="status" id="filterStatus" class="filter-control">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>

                        <div>
                            <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Search Keyword</label>
                            <input type="text" name="search" id="filterSearch" value="{{ request('search') }}" class="filter-control" placeholder="Search reason notes, employee, code...">
                        </div>

                        <div>
                            <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Per Page</label>
                            <select name="per_page" id="filterPerPage" class="filter-control font-weight-bold">
                                <option value="10" {{ (int)request('per_page', 25) === 10 ? 'selected' : '' }}>10 rows</option>
                                <option value="25" {{ (int)request('per_page', 25) === 25 ? 'selected' : '' }}>25 rows</option>
                                <option value="50" {{ (int)request('per_page', 25) === 50 ? 'selected' : '' }}>50 rows</option>
                                <option value="100" {{ (int)request('per_page', 25) === 100 ? 'selected' : '' }}>100 rows</option>
                                <option value="250" {{ (int)request('per_page', 25) === 250 ? 'selected' : '' }}>250 rows</option>
                                <option value="-1" {{ (int)request('per_page', 25) === -1 ? 'selected' : '' }}>All rows</option>
                            </select>
                        </div>

                        <div class="d-flex align-items-end" style="gap: 8px;">
                            <button type="submit" class="btn text-white font-weight-bold shadow-sm" style="height: 42px; border-radius: 12px; background: var(--orb-primary); border: none; padding: 0 18px; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('leave-requests.index') }}" class="filter-btn-reset" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="ep-card-body p-0">
                <div class="ep-table-wrap">
                    <table class="table ep-table js-custom-leaves-table" id="employeeLeavesTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">S.No.</th>
                                @if($isAdminOrHr)
                                    <th style="min-width: 180px;">Employee</th>
                                @endif
                                <th style="min-width: 130px;">Leave Type</th>
                                <th style="min-width: 170px;">Requested Dates</th>
                                <th style="min-width: 90px;">Duration</th>
                                <th style="min-width: 100px;">Status</th>
                                <th style="min-width: 250px;">Reason / Note</th>
                                <th class="text-right pr-4 no-export" style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                                <tr class="leave-data-row">
                                    <td><strong>{{ $loop->iteration + ($requests->currentPage() - 1) * $requests->perPage() }}</strong></td>
                                    @if($isAdminOrHr)
                                        <td>
                                            <div class="d-flex align-items-center" style="gap: 10px;">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 34px; height: 34px; font-size: 11px; background: linear-gradient(135deg, #4B00E8, #7C3AED); flex-shrink: 0;">
                                                    {{ strtoupper(substr(optional($request->employee)->display_name ?? 'E', 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold text-dark" style="font-size: 12.5px;">{{ optional($request->employee)->display_name ?? 'Unknown' }}</div>
                                                    <small class="text-muted" style="font-size: 10.5px;">{{ optional($request->employee)->employee_code ?? 'EMP' }} &bull; {{ optional(optional($request->employee)->department)->name ?? 'General' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                    <td class="leave-type-cell">
                                        <span class="font-weight-bold text-dark">{{ optional($request->leaveType)->name ?? 'Leave' }}</span>
                                    </td>
                                    <td class="leave-dates-cell">
                                        <span class="font-weight-bold text-dark">{{ optional($request->start_date)->format('d M Y') }} - {{ optional($request->end_date)->format('d M Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="ep-badge ep-badge-primary">{{ $request->deducted_days }} Days</span>
                                    </td>
                                    <td class="leave-status-cell">
                                        <span class="orb-pill {{ $request->status }}">
                                            <i class="fas fa-circle mr-1" style="font-size: 7px;"></i> {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td class="leave-reason-cell">
                                        <span class="text-muted" title="{{ $request->reason }}">{{ \Illuminate\Support\Str::limit($request->reason, 55) }}</span>
                                    </td>
                                    <td class="text-right pr-4">
                                        <div class="d-flex align-items-center justify-content-end" style="gap: 8px;">
                                            <button type="button" class="btn btn-sm btn-light border shadow-sm js-view-details" data-row='@json($request)' title="View Details" style="width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; padding:0;">
                                                <i class="fas fa-eye text-primary"></i>
                                            </button>

                                             @if($request->status === 'pending')
                                                <button type="button" class="btn btn-sm btn-outline-warning shadow-sm js-edit-request" data-row='@json($request)' title="Edit Request" style="width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; padding:0;">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <form method="POST" action="{{ route('leave-requests.cancel', $request->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" title="Cancel Request" style="width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; padding:0;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdminOrHr ? '8' : '7' }}" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fa-3x mb-3 text-light"></i>
                                        <p class="mb-0 font-weight-bold">No leave requests found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ==================================================
     VIEW DETAILS MODAL
     ================================================== -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content ep-form border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="ep-modal-header" style="background: linear-gradient(135deg, #1E293B 0%, #334155 100%); color: #fff; padding: 20px 24px;">
                <h5 class="modal-title font-weight-bold text-white mb-1"><i class="fas fa-info-circle mr-2"></i> Leave Request Details</h5>
                <p class="mb-0 text-white-50" style="font-size: 13px;">View complete leave application breakdown and status logs.</p>
                <button type="button" class="close text-white opacity-100" data-dismiss="modal" style="margin-top: -30px;"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body" style="padding: 24px;">
                <div class="ep-section-card mb-3 p-3" style="background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0;">
                    <div class="ep-section-title font-weight-bold text-primary mb-2" style="font-size: 14px;"><i class="fas fa-file-alt mr-1"></i> Leave Information</div>
                    <div class="row">
                        @if($isAdminOrHr)
                        <div class="col-12 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Employee</small>
                            <strong id="det_employee_name" class="text-dark">-</strong>
                        </div>
                        @endif
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Leave Type</small>
                            <strong id="det_leave_type" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Deducted Days</small>
                            <strong id="det_deducted_days" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Date Range</small>
                            <strong id="det_date_range" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Applied On</small>
                            <strong id="det_applied_on" class="text-dark">-</strong>
                        </div>
                        <div class="col-12 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Quota Breakdown</small>
                            <strong id="det_breakdown" class="text-dark" style="font-family: monospace;">-</strong>
                        </div>
                        <div class="col-12 mb-0">
                            <small class="text-muted d-block" style="font-size: 11px;">Reason / Note</small>
                            <p class="mb-0 font-weight-bold text-dark" id="det_reason" style="white-space: pre-wrap; font-size: 13px;"></p>
                        </div>
                    </div>
                </div>

                <div class="ep-section-card mb-0 p-3" style="background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0;">
                    <div class="ep-section-title font-weight-bold text-primary mb-2" style="font-size: 14px;"><i class="fas fa-history mr-1"></i> Approval & Status</div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Current Status</small>
                            <strong id="det_status" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Emergency Leave</small>
                            <strong id="det_emergency" class="text-dark">-</strong>
                        </div>
                        <div class="col-12 mb-0" id="det_rejection_row" style="display: none;">
                            <small class="text-muted d-block text-danger" style="font-size: 11px;">Rejection / Response Note</small>
                            <strong class="text-danger" id="det_rejection_note">-</strong>
                            <div id="det_rejected_by" class="text-muted small mt-1" style="font-size: 11px; display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ep-modal-footer bg-light" style="padding: 14px 24px; border-top: 1px solid #E5E7EB;">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal" style="border-radius: 10px; padding: 8px 20px;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================================================
     EDIT LEAVE REQUEST MODAL
     ================================================== -->
<div class="modal fade" id="editLeaveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <form id="editLeaveForm" method="POST" action="" class="modal-content ep-form border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            @csrf
            @method('PUT')
            <div class="ep-modal-header" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color: #fff; padding: 20px 24px;">
                <h5 class="modal-title font-weight-bold text-white mb-1"><i class="fas fa-edit mr-2"></i> Edit Leave Request</h5>
                <p class="mb-0 text-white-50" style="font-size: 13px;">Update your pending leave request details.</p>
                <button type="button" class="close text-white opacity-100" data-dismiss="modal" style="margin-top: -30px;"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body" style="padding: 24px;">
                <div class="ep-form-group mb-3">
                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Leave Type <span class="text-danger">*</span></label>
                    <select name="leave_type_id" id="edit_leave_type_id" class="form-control shadow-none" style="border-radius: 10px; height: 42px;" required>
                        @php
                            $typesList = $leaveTypes ?? \App\Models\HRMS\Leave\LeaveTypeM::where('is_active', true)->get();
                        @endphp
                        @foreach($typesList as $lt)
                            <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="ep-form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="edit_start_date" class="form-control shadow-none" style="border-radius: 10px; height: 42px;" required>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="ep-form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="edit_end_date" class="form-control shadow-none" style="border-radius: 10px; height: 42px;" required>
                        </div>
                    </div>
                </div>

                <div class="ep-form-group mb-3">
                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Reason / Notes <span class="text-danger">*</span></label>
                    <textarea class="form-control shadow-none" name="reason" id="edit_leave_reason" rows="3" style="border-radius: 10px;" required placeholder="Describe the reason for your leave..."></textarea>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="custom-control custom-checkbox mr-4">
                        <input type="checkbox" class="custom-control-input" id="edit_is_half_day" name="is_half_day" value="1">
                        <label class="custom-control-label font-weight-bold text-dark" for="edit_is_half_day" style="font-size: 13px;">Half Day Leave</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="edit_emergency_leave" name="emergency_leave" value="1">
                        <label class="custom-control-label font-weight-bold text-dark" for="edit_emergency_leave" style="font-size: 13px;">Emergency Leave</label>
                    </div>
                </div>
            </div>
            <div class="ep-modal-footer bg-light" style="padding: 16px 24px; border-top: 1px solid #E5E7EB;">
                <button type="button" class="btn btn-light border font-weight-bold" data-dismiss="modal" style="border-radius: 10px; padding: 8px 18px;">Cancel</button>
                <button class="btn btn-warning text-white font-weight-bold border-0 shadow-sm" style="border-radius: 10px; padding: 8px 22px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);"><i class="fas fa-save mr-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(function() {
    if ($.fn.select2) {
        $('.select2-searchable').select2({
            placeholder: "Select employee...",
            allowClear: true,
            width: '100%'
        });
    }

    var table = null;
    if ($('#employeeLeavesTable').length) {
        $.fn.dataTable.ext.errMode = 'none';

        var initialPageLen = parseInt($('#filterPerPage').val()) || 25;
        table = $('#employeeLeavesTable').DataTable({
            pageLength: initialPageLen,
            lengthMenu: [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
            ordering: true,
            searching: true,
            paging: true,
            info: true,
            dom: '<"leave-dt-toolbar"<"leave-dt-left"l><"leave-dt-right"B>>rt<"ep-table-footer d-flex justify-content-between align-items-center p-3 border-top bg-white"ip>',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="far fa-file-excel text-success mr-1"></i> Excel', className: 'leave-export-btn', exportOptions: { columns: ':not(.no-export)' } },
                { extend: 'csvHtml5', text: '<i class="fas fa-file-csv text-primary mr-1"></i> CSV', className: 'leave-export-btn', exportOptions: { columns: ':not(.no-export)' } },
                { extend: 'pdfHtml5', text: '<i class="far fa-file-pdf text-danger mr-1"></i> PDF', className: 'leave-export-btn', orientation: 'landscape', exportOptions: { columns: ':not(.no-export)' } },
                { extend: 'print', text: '<i class="fas fa-print text-dark mr-1"></i> Print', className: 'leave-export-btn', exportOptions: { columns: ':not(.no-export)' } }
            ],
            language: {
                emptyTable: 'No leave requests found matching criteria.',
                zeroRecords: 'No matching records found.',
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            }
        });

        $('#filterPerPage').on('change', function() {
            var len = parseInt($(this).val());
            if (table) {
                table.page.len(len).draw();
            }
        });
    }

    // View Details Modal Handler
    $('.js-view-details').on('click', function() {
        var row = JSON.parse($(this).attr('data-row') || '{}');
        var fmt = function(v) { return v ? String(v) : '-'; };
        
        var startDateStr = row.start_date ? new Date(row.start_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
        var endDateStr = row.end_date ? new Date(row.end_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
        var appliedOnStr = row.created_at ? new Date(row.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';

        var empName = row.employee ? (row.employee.name + (row.employee.employee_code ? ' (' + row.employee.employee_code + ')' : '')) : '-';
        $('#det_employee_name').text(empName);

        $('#det_leave_type').text(row.leave_type ? row.leave_type.name : 'Leave');
        $('#det_deducted_days').text((row.deducted_days || '0') + ' Days');
        $('#det_date_range').text(startDateStr + ' - ' + endDateStr);
        $('#det_applied_on').text(appliedOnStr);
        $('#det_breakdown').text('Paid: ' + (row.paid_days || 0) + ' | Sick: ' + (row.sick_days || 0) + ' | Comp-Off: ' + (row.comp_off_days || 0) + ' | LWP: ' + (row.lwp_days || 0));
        $('#det_reason').text(fmt(row.reason));
        $('#det_status').text(fmt(row.status).toUpperCase());
        $('#det_emergency').text(row.emergency_leave ? 'YES' : 'NO');

        if (row.rejection_reason) {
            $('#det_rejection_row').show();
            $('#det_rejection_note').text(row.rejection_reason);
            if (row.approver && row.approver.name) {
                var rejectedByText = 'Rejected by ' + row.approver.name;
                if (row.approved_at) {
                    var rejDate = new Date(row.approved_at);
                    rejectedByText += ' \u2022 ' + rejDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                }
                $('#det_rejected_by').text(rejectedByText).show();
            } else {
                $('#det_rejected_by').hide();
            }
        } else {
            $('#det_rejection_row').hide();
            $('#det_rejected_by').hide();
        }

        $('#leaveDetailsModal').modal('show');
    });

    // Edit Request Modal Handler
    $('.js-edit-request').on('click', function() {
        var row = JSON.parse($(this).attr('data-row') || '{}');
        var updateUrl = "{{ route('leave-requests.update', ':id') }}".replace(':id', row.id);
        $('#editLeaveForm').attr('action', updateUrl);
        $('#edit_leave_type_id').val(row.leave_type_id || '');
        
        var cleanStartDate = (row.start_date || '').toString().split('T')[0].split(' ')[0];
        var cleanEndDate = (row.end_date || '').toString().split('T')[0].split(' ')[0];

        $('#edit_start_date').val(cleanStartDate);
        $('#edit_end_date').val(cleanEndDate);
        $('#edit_leave_reason').val(row.reason || '');
        $('#edit_is_half_day').prop('checked', !!row.is_half_day);
        $('#edit_emergency_leave').prop('checked', !!row.emergency_leave);

        $('#editLeaveModal').modal('show');
    });
});
</script>
@endsection
