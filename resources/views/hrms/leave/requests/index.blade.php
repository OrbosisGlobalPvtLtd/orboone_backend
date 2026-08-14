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
                <h1 style="font-size: 26px; font-weight: 900; color: #fff;">My Leave Requests</h1>
                <p style="font-size: 13px; color: rgba(255,255,255,0.85); margin-bottom: 0;">Track your applied leaves, quota splits, approval states, and remaining allocations.</p>
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
            $isConfirmed = $employee->is_permanent;
            
            $monthlyQuotaInfo = app(\App\Services\HRMS\Leave\MonthlyLeaveQuotaService::class)->getMonthlyQuota($employee);

            $paidRemaining = $isConfirmed ? ($allocation->paid_remaining ?? 0) : 0.0;
            $paidUsed = $isConfirmed ? ($allocation->paid_used ?? 0) : 0.0;
            $sickRemaining = $isConfirmed ? ($allocation->sick_remaining ?? 0) : 0.0;
            $compRemaining = $isConfirmed ? ($allocation->comp_off_remaining ?? 0) : 0.0;
            $lwpUsed = (float) ($allocation->lwp_used ?? 0);

            $alreadyUsedThisMonth = $isConfirmed ? (float) $monthlyQuotaInfo['current_month_used'] : 0.0;
            $remainingThisMonth = $isConfirmed ? (float) $monthlyQuotaInfo['total_monthly_remaining_paid'] : 0.0;
            $paidAvailableThisMonth = $isConfirmed ? (float) ($monthlyQuotaInfo['monthly_limit'] + $monthlyQuotaInfo['carry_forward_available']) : 0.0;

            // Apply November/December rule to dynamic balances
            $currentMonth = Carbon\Carbon::now('Asia/Kolkata')->month;
            if (in_array((int) $currentMonth, [11, 12], true) && ($allocation->total_remaining ?? 0) > 10.0) {
                $paidRemaining = round($paidRemaining * 0.5, 2);
                $sickRemaining = round($sickRemaining * 0.5, 2);
                $compRemaining = round($compRemaining * 0.5, 2);
                $remainingThisMonth = round($remainingThisMonth * 0.5, 2);
            }

            // Requests Counts
            $pendingCount = DB::table('leave_requests')->where('employee_id', $employee->id)->where('status', 'pending')->count();
            $approvedCount = DB::table('leave_requests')->where('employee_id', $employee->id)->where('status', 'approved')->count();
            $rejectedCount = DB::table('leave_requests')->where('employee_id', $employee->id)->where('status', 'rejected')->count();
            $emergencyUsed = DB::table('leave_requests')->where('employee_id', $employee->id)->where('status', 'approved')->where('emergency_leave', 1)->count();
        @endphp

        <!-- 1. PRIMARY QUOTA CARDS (4-Column Layout) -->
        <div class="row mb-4">
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
                        <div class="metric-lbl" title="Total Paid Leave balance available for this month (including Carry Forward)">Monthly Paid Leave Balance</div>
                        <div class="metric-val">{{ number_format((float) $remainingThisMonth, 2) }}</div>
                    </div>
                </div>
            </div>
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
                        <h5 class="ep-table-title">Leave Request History</h5>
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
                <div class="filter-grid">
                    <div>
                        <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Leave Type</label>
                        <select id="filterLeaveType" class="filter-control" onchange="applyLeaveFilters()">
                            <option value="">All Types</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Status</label>
                        <select id="filterStatus" class="filter-control" onchange="applyLeaveFilters()">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-weight-bold text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;">Search Reason / Date</label>
                        <input type="text" id="filterSearch" class="filter-control" placeholder="Search reason notes..." onkeyup="applyLeaveFilters()">
                    </div>
                    <div>
                        <button type="button" class="filter-btn-reset" onclick="resetLeaveFilters()">
                            <i class="fas fa-undo mr-1"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="ep-card-body p-0">
                <div class="ep-table-wrap">
                    <table class="table ep-table js-custom-leaves-table" id="employeeLeavesTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Leave Type</th>
                                <th>Requested Dates</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Reason / Note</th>
                                <th class="text-right pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                                <tr class="leave-data-row">
                                    <td><strong>{{ $loop->iteration + ($requests->currentPage() - 1) * $requests->perPage() }}</strong></td>
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
                                            @elseif($request->status === 'approved')
                                                <form method="POST" action="{{ route('leave-requests.cancel', $request->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this approved leave request?');">
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
                                    <td colspan="7" class="text-center py-5 text-muted">
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
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && $.fn.DataTable && $('#employeeLeavesTable').length) {
            var hasButtons = typeof $.fn.dataTable.Buttons !== 'undefined';
            var domLayout = hasButtons 
                ? '<"leave-dt-toolbar"<"leave-dt-left"l><"leave-dt-right"B>>rt<"ep-table-footer"ip>'
                : '<"leave-dt-toolbar"<"leave-dt-left"l>>rt<"ep-table-footer"ip>';

            $('.js-custom-leaves-table').DataTable({
                pageLength: 25,
                responsive: false,
                language: {
                    emptyTable: 'No records found',
                    zeroRecords: 'No matching records found'
                },
                dom: domLayout,
                buttons: [
                    { extend: 'excelHtml5', text: 'Excel', className: 'leave-export-btn' },
                    { extend: 'csvHtml5', text: 'CSV', className: 'leave-export-btn' },
                    { extend: 'pdfHtml5', text: 'PDF', className: 'leave-export-btn' },
                    { extend: 'print', text: 'Print', className: 'leave-export-btn' }
                ]
            });
        }

        var typeSelect = document.getElementById('filterLeaveType');
        var types = new Set();
        document.querySelectorAll('#employeeLeavesTable tbody tr.leave-data-row').forEach(function(row) {
            var typeCell = row.querySelector('.leave-type-cell');
            if (typeCell) {
                var text = typeCell.textContent.trim();
                if (text) {
                    types.add(text);
                }
            }
        });
        
        types.forEach(function(tp) {
            var opt = document.createElement('option');
            opt.value = tp;
            opt.textContent = tp;
            typeSelect.appendChild(opt);
        });

        // View Details Modal Handler
        document.querySelectorAll('.js-view-details').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var row = JSON.parse(this.getAttribute('data-row') || '{}');
                var fmt = function(v) { return v ? String(v) : '-'; };
                
                var startDateStr = row.start_date ? new Date(row.start_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                var endDateStr = row.end_date ? new Date(row.end_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                var appliedOnStr = row.created_at ? new Date(row.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';

                document.getElementById('det_leave_type').textContent = (row.leave_type ? row.leave_type.name : 'Leave');
                document.getElementById('det_deducted_days').textContent = (row.deducted_days || '0') + ' Days';
                document.getElementById('det_date_range').textContent = startDateStr + ' - ' + endDateStr;
                document.getElementById('det_applied_on').textContent = appliedOnStr;
                document.getElementById('det_breakdown').textContent = 'Paid: ' + (row.paid_days || 0) + ' | Sick: ' + (row.sick_days || 0) + ' | Comp-Off: ' + (row.comp_off_days || 0) + ' | LWP: ' + (row.lwp_days || 0);
                document.getElementById('det_reason').textContent = fmt(row.reason);
                document.getElementById('det_status').textContent = fmt(row.status).toUpperCase();
                document.getElementById('det_emergency').textContent = row.emergency_leave ? 'YES' : 'NO';

                var rejRow = document.getElementById('det_rejection_row');
                if (row.rejection_reason) {
                    rejRow.style.display = 'block';
                    document.getElementById('det_rejection_note').textContent = row.rejection_reason;
                } else {
                    rejRow.style.display = 'none';
                }

                $('#leaveDetailsModal').modal('show');
            });
        });

        // Edit Request Modal Handler
        document.querySelectorAll('.js-edit-request').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var row = JSON.parse(this.getAttribute('data-row') || '{}');
                var updateUrl = "{{ route('leave-requests.update', ':id') }}".replace(':id', row.id);
                document.getElementById('editLeaveForm').setAttribute('action', updateUrl);
                document.getElementById('edit_leave_type_id').value = row.leave_type_id || '';
                
                var cleanStartDate = (row.start_date || '').toString().split('T')[0].split(' ')[0];
                var cleanEndDate = (row.end_date || '').toString().split('T')[0].split(' ')[0];

                document.getElementById('edit_start_date').value = cleanStartDate;
                document.getElementById('edit_end_date').value = cleanEndDate;
                document.getElementById('edit_leave_reason').value = row.reason || '';
                document.getElementById('edit_is_half_day').checked = !!row.is_half_day;
                document.getElementById('edit_emergency_leave').checked = !!row.emergency_leave;

                $('#editLeaveModal').modal('show');
            });
        });
    });

    function applyLeaveFilters() {
        var typeVal = document.getElementById('filterLeaveType').value.toLowerCase().trim();
        var statusVal = document.getElementById('filterStatus').value.toLowerCase().trim();
        var searchVal = document.getElementById('filterSearch').value.toLowerCase().trim();

        document.querySelectorAll('#employeeLeavesTable tbody tr.leave-data-row').forEach(function(row) {
            var typeCell = row.querySelector('.leave-type-cell');
            var statusCell = row.querySelector('.leave-status-cell');
            var reasonCell = row.querySelector('.leave-reason-cell');
            var datesCell = row.querySelector('.leave-dates-cell');

            if (!typeCell) return;

            var typeText = typeCell.textContent.toLowerCase();
            var statusText = statusCell ? statusCell.textContent.trim().toLowerCase() : '';
            var searchText = (reasonCell ? reasonCell.textContent.toLowerCase() : '') + ' ' + (datesCell ? datesCell.textContent.toLowerCase() : '');

            var matchesType = !typeVal || typeText.includes(typeVal);
            var matchesStatus = !statusVal || statusText.includes(statusVal);
            var matchesSearch = !searchVal || searchText.includes(searchVal);

            if (matchesType && matchesStatus && matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function resetLeaveFilters() {
        document.getElementById('filterLeaveType').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterSearch').value = '';
        
        document.querySelectorAll('#employeeLeavesTable tbody tr.leave-data-row').forEach(function(row) {
            row.style.display = '';
        });
    }
</script>
@endsection
