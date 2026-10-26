@extends('layouts.panel', ['active' => 'leave-approvals.index'])

@section('page_title', 'Leave Request #' . $lr->id . ' Details')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#8600EE' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.lr-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.lr-container {
    max-width: 100%;
    margin: 0 auto;
}

/* Signature Header Banner */
.lr-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 14px 34px rgba(75, 0, 232, 0.18);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.lr-hero h3 {
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 6px 0;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 10px;
}

.lr-hero p {
    font-size: 13.5px;
    opacity: 0.92;
    margin: 0;
}

/* Metric Summary Cards Grid */
.lr-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.lr-stat-card {
    background: var(--orb-card);
    border-radius: 16px;
    padding: 18px 20px;
    border: 1px solid var(--orb-border);
    box-shadow: var(--orb-shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.lr-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 35px rgba(15, 23, 42, 0.12);
}

.lr-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.lr-stat-val {
    font-size: 22px;
    font-weight: 800;
    color: var(--orb-text);
    line-height: 1.1;
}

.lr-stat-lbl {
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748B;
    margin-top: 4px;
}

/* Section Card */
.lr-card {
    background: var(--orb-card);
    border-radius: 18px;
    border: 1px solid var(--orb-border);
    box-shadow: var(--orb-shadow);
    margin-bottom: 24px;
    overflow: hidden;
}

.lr-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    background: #FFFFFF;
}

.lr-card-header h5 {
    font-size: 16px;
    font-weight: 800;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.lr-card-body {
    padding: 24px;
}

/* Applicant Info Tile Grid */
.lr-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
}

.lr-info-item {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 14px 16px;
}

.lr-info-item .label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748B;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.lr-info-item .value {
    font-size: 13.5px;
    font-weight: 700;
    color: #0F172A;
}

/* Table Design */
.lr-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.lr-table th {
    background: #F8FAFC;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #475569;
    border-bottom: 2px solid #E2E8F0;
    padding: 13px 16px;
    white-space: nowrap;
}

.lr-table td {
    padding: 14px 16px;
    font-size: 13px;
    border-bottom: 1px solid #F1F5F9;
    vertical-align: middle;
}

.lr-table tbody tr:hover {
    background: #FAF5FF;
}

/* Adjustment Highlight Pill */
.adjustment-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #FFFBEB;
    color: #B45309;
    border: 1px solid #FDE68A;
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 11.5px;
    font-weight: 700;
    line-height: 1.35;
}
</style>
@endsection

@section('_content')
@php
    $startFormatted = !empty($lr->start_date) ? \Carbon\Carbon::parse($lr->start_date)->format('d M Y') : '—';
    $endFormatted = !empty($lr->end_date) ? \Carbon\Carbon::parse($lr->end_date)->format('d M Y') : '—';
    $isSingleDay = ($lr->start_date === $lr->end_date);
    $status = strtolower($lr->status ?? 'pending');
@endphp

<div class="lr-page">
    <div class="lr-container">

        <!-- Top Breadcrumb & Actions Bar -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" style="gap: 12px;">
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ url('/leave-allocations?year='.date('Y', strtotime($lr->start_date ?? 'now')).'&tab=transactions') }}" class="btn btn-sm btn-white border shadow-sm font-weight-bold" style="border-radius: 10px; padding: 7px 14px; color:#475569; background:#fff;">
                    <i class="fas fa-arrow-left mr-1.5 text-primary"></i> Back to Transactions
                </a>
                <a href="{{ url('/leave-approvals') }}" class="btn btn-sm btn-light border font-weight-bold" style="border-radius: 10px; padding: 7px 14px; color:#64748B;">
                    <i class="fas fa-tasks mr-1.5"></i> All Approvals
                </a>
            </div>
            <div>
                <span class="text-muted small font-weight-bold">
                    System Time: {{ \Carbon\Carbon::now('Asia/Kolkata')->format('d M Y, h:i A') }} (IST)
                </span>
            </div>
        </div>

        <!-- Signature Hero Banner -->
        <div class="lr-hero">
            <div>
                <h3>
                    <i class="fas fa-calendar-check"></i>
                    Leave Application #LR-{{ str_pad($lr->id, 4, '0', STR_PAD_LEFT) }}
                    @if($status === 'approved')
                        <span class="badge px-3 py-1 font-weight-bold" style="background: rgba(255,255,255,0.25); border:1px solid rgba(255,255,255,0.4); font-size:12px; border-radius:20px;">
                            Approved
                        </span>
                    @elseif($status === 'rejected')
                        <span class="badge px-3 py-1 font-weight-bold" style="background: #FEE2E2; color:#DC2626; font-size:12px; border-radius:20px;">
                             Rejected
                        </span>
                    @elseif($status === 'void')
                        <span class="badge px-3 py-1 font-weight-bold" style="background: #F1F5F9; color:#475569; font-size:12px; border-radius:20px;">
                             Null & Void
                        </span>
                    @else
                        <span class="badge px-3 py-1 font-weight-bold" style="background: #FEF3C7; color:#B45309; font-size:12px; border-radius:20px;">
                             Pending Approval
                        </span>
                    @endif
                </h3>
                <p>
                    Submitted by <strong>{{ $lr->display_name }}</strong> ({{ $lr->employee_code }}) on {{ !empty($lr->created_at) ? \Carbon\Carbon::parse($lr->created_at)->format('d M Y, h:i A') : '—' }}
                </p>
            </div>
            <div class="d-flex align-items-center" style="gap: 10px;">
                <span class="badge px-3 py-2 text-white font-weight-bold" style="background: rgba(0,0,0,0.22); border-radius:10px; font-size:13px;">
                    <i class="far fa-calendar-alt mr-1"></i>
                    {{ $isSingleDay ? $startFormatted : $startFormatted . ' to ' . $endFormatted }}
                </span>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <div class="lr-stats-grid">
            <div class="lr-stat-card">
                <div class="lr-stat-icon" style="background: #EEF2FF; color: #4B00E8;">
                    <i class="fas fa-calculator"></i>
                </div>
                <div>
                    <div class="lr-stat-val">{{ (float)($lr->deducted_days ?? $lr->requested_days ?? 0) }}</div>
                    <div class="lr-stat-lbl">Total Days Deducted</div>
                </div>
            </div>

            <div class="lr-stat-card">
                <div class="lr-stat-icon" style="background: #ECFDF5; color: #10B981;">
                    <i class="fas fa-check-double"></i>
                </div>
                <div>
                    <div class="lr-stat-val text-success">{{ (float)($lr->paid_days ?? 0) }}</div>
                    <div class="lr-stat-lbl">Paid Leave (PL)</div>
                </div>
            </div>

            <div class="lr-stat-card">
                <div class="lr-stat-icon" style="background: #EFF6FF; color: #2563EB;">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div>
                    <div class="lr-stat-val text-primary">{{ (float)($lr->sick_days ?? 0) }}</div>
                    <div class="lr-stat-lbl">Sick Leave (SL)</div>
                </div>
            </div>

            <div class="lr-stat-card">
                <div class="lr-stat-icon" style="background: {{ (float)($lr->lwp_days ?? 0) > 0 ? '#FEF2F2' : '#F8FAFC' }}; color: {{ (float)($lr->lwp_days ?? 0) > 0 ? '#DC2626' : '#94A3B8' }};">
                    <i class="fas fa-ban"></i>
                </div>
                <div>
                    <div class="lr-stat-val {{ (float)($lr->lwp_days ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">{{ (float)($lr->lwp_days ?? 0) }}</div>
                    <div class="lr-stat-lbl">Loss of Pay (LWP)</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Applicant & Application Details -->
            <div class="col-lg-4 mb-4">
                <!-- Applicant Card -->
                <div class="lr-card mb-4">
                    <div class="lr-card-header">
                        <h5><i class="fas fa-user-circle text-primary"></i> Applicant Profile</h5>
                    </div>
                    <div class="lr-card-body">
                        <div class="d-flex align-items-center mb-3" style="gap: 14px;">
                            <div style="width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%); color: #FFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; box-shadow: 0 4px 12px rgba(75,0,232,0.3);">
                                {{ strtoupper(substr($lr->display_name ?? 'E', 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="font-weight-bold text-dark mb-0.5" style="font-size: 16px;">{{ $lr->display_name }}</h6>
                                <div class="badge badge-light border text-muted font-weight-bold" style="font-size: 11px;">
                                    {{ $lr->employee_code }}
                                </div>
                            </div>
                        </div>

                        <div class="lr-info-grid" style="grid-template-columns: 1fr;">
                            <div class="lr-info-item">
                                <div class="label">Department</div>
                                <div class="value">{{ $lr->department_name ?? '—' }}</div>
                            </div>
                            <div class="lr-info-item">
                                <div class="label">Designation</div>
                                <div class="value">{{ $lr->designation_name ?? '—' }}</div>
                            </div>
                            <div class="lr-info-item">
                                <div class="label">Reporting Manager</div>
                                <div class="value">{{ $lr->reporting_manager_name ?? 'Not Assigned' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Summary Card -->
                <div class="lr-card">
                    <div class="lr-card-header">
                        <h5><i class="fas fa-file-alt text-primary"></i> Request Details</h5>
                    </div>
                    <div class="lr-card-body">
                        <div class="lr-info-grid" style="grid-template-columns: 1fr; gap: 10px;">
                            <div class="lr-info-item">
                                <div class="label">Applied Leave Type</div>
                                <div class="value">
                                    <span class="badge px-2.5 py-1" style="background: #EEF2FF; color: #4B00E8; border: 1px solid #C7D2FE; font-size: 12px; border-radius: 6px;">
                                        {{ $lr->leave_type_name ?? 'Paid Leave' }}
                                    </span>
                                </div>
                            </div>

                            @if(!empty($lr->is_half_day))
                            <div class="lr-info-item">
                                <div class="label">Leave Session</div>
                                <div class="value">
                                    <span class="badge badge-info px-2.5 py-1 font-weight-bold" style="font-size: 11.5px; border-radius: 6px;">
                                        <i class="far fa-clock mr-1"></i> Half Day ({{ ($lr->half_day_type ?? '') === 'second_half' ? 'Second Half - Afternoon Session' : 'First Half - Morning Session' }})
                                    </span>
                                </div>
                            </div>
                            @endif

                            @if(!empty($lr->sandwich_applied))
                            <div class="lr-info-item">
                                <div class="label">Sandwich Policy</div>
                                <div class="value">
                                    <span class="badge badge-warning px-2.5 py-1 font-weight-bold text-dark" style="background:#FEF3C7; border:1px solid #FCD34D; font-size: 11.5px; border-radius: 6px;">
                                        <i class="fas fa-layer-group mr-1"></i> Sandwich Policy Applied
                                    </span>
                                </div>
                            </div>
                            @endif

                            @if(!empty($lr->attachment_path))
                            <div class="lr-info-item">
                                <div class="label">Attached Document</div>
                                <div class="value">
                                    <a href="{{ route('leave-requests.attachment', $lr->id) }}" target="_blank" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 6px; padding: 4px 10px; font-size: 11.5px;">
                                        <i class="fas fa-paperclip mr-1"></i> View Supporting Document
                                    </a>
                                </div>
                            </div>
                            @endif

                            <div class="lr-info-item">
                                <div class="label">Reason / Notes</div>
                                <div class="value font-italic" style="color: #334155; line-height: 1.45;">
                                    "{{ $lr->reason ?: 'No notes provided by employee' }}"
                                </div>
                            </div>

                            <div class="lr-info-item">
                                <div class="label">Approval Trail</div>
                                <div class="value" style="font-size: 12.5px;">
                                    @if($lr->manager_approved_by)
                                        <div class="mb-1 text-success">
                                            <i class="fas fa-check-circle mr-1"></i> Manager Approved: <strong>{{ $lr->manager_approver_name }}</strong>
                                            @if($lr->manager_approved_at)
                                                <span class="text-muted small">({{ \Carbon\Carbon::parse($lr->manager_approved_at)->format('d M Y') }})</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if($lr->hr_approved_by)
                                        <div class="text-success">
                                            <i class="fas fa-check-double mr-1"></i> HR Approved: <strong>{{ $lr->hr_approver_name }}</strong>
                                            @if($lr->hr_approved_at)
                                                <span class="text-muted small">({{ \Carbon\Carbon::parse($lr->hr_approved_at)->format('d M Y') }})</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if(!$lr->manager_approved_by && !$lr->hr_approved_by)
                                        <span class="text-muted">— Awaiting review</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Individual Dates Breakdown & Transactions -->
            <div class="col-lg-8">
                <!-- Daily Breakdown Table -->
                <div class="lr-card mb-4">
                    <div class="lr-card-header">
                        <h5>
                            <i class="fas fa-calendar-day text-primary"></i>
                            Daily Leave Breakdown ({{ count($dates) }} {{ \Illuminate\Support\Str::plural('Day', count($dates)) }})
                        </h5>
                        <span class="text-muted small font-weight-bold">
                            Chronological dates deduction & policy adjustments
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table lr-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day Type</th>
                                    <th class="text-center">PL Days</th>
                                    <th class="text-center">SL Days</th>
                                    <th class="text-center">LWP Days</th>
                                    <th>Remarks / Adjustment Rule</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dates as $d)
                                    @php
                                        $dCarbon = \Carbon\Carbon::parse($d->leave_date);
                                        $isAdjusted = !empty($d->remarks) && stripos($d->remarks, 'adjusted') !== false;
                                    @endphp
                                    <tr style="{{ $isAdjusted ? 'background: #FFFDF5;' : '' }}">
                                        <td>
                                            <div class="font-weight-bold text-dark" style="font-size: 13.5px;">
                                                {{ $dCarbon->format('d M Y') }}
                                            </div>
                                            <div class="text-muted small font-weight-bold">
                                                {{ $d->day_name ?: $dCarbon->format('l') }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($d->is_sandwich_day)
                                                <span class="badge px-2 py-0.5" style="background:#FEF3C7; color:#92400E; border:1px solid #FDE68A; font-size:10.5px; border-radius:6px;">
                                                    Sandwich Day
                                                </span>
                                            @elseif($d->is_weekoff)
                                                <span class="badge px-2 py-0.5" style="background:#F1F5F9; color:#475569; font-size:10.5px; border-radius:6px;">
                                                    Weekoff
                                                </span>
                                            @elseif($d->is_holiday)
                                                <span class="badge px-2 py-0.5" style="background:#FEF2F2; color:#DC2626; font-size:10.5px; border-radius:6px;">
                                                    Holiday
                                                </span>
                                            @else
                                                <span class="badge px-2 py-0.5" style="background:#F8FAFC; color:#64748B; border:1px solid #E2E8F0; font-size:10.5px; border-radius:6px;">
                                                    Working Day
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if((float)$d->paid_day > 0)
                                                <span class="badge font-weight-bold px-2 py-1" style="background:#ECFDF5; color:#15803D; border:1px solid #86EFAC; font-size:12px; border-radius:6px;">
                                                    {{ (float)$d->paid_day }} PL
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if((float)$d->sick_day > 0)
                                                <span class="badge font-weight-bold px-2 py-1" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #93C5FD; font-size:12px; border-radius:6px;">
                                                    {{ (float)$d->sick_day }} SL
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if((float)$d->lwp_day > 0)
                                                <span class="badge font-weight-bold px-2 py-1" style="background:#FEF2F2; color:#B91C1C; border:1px solid #FCA5A5; font-size:12px; border-radius:6px;">
                                                    {{ (float)$d->lwp_day }} LWP
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($d->remarks))
                                                <div class="adjustment-pill" title="{{ $d->remarks }}">
                                                    <i class="fas fa-info-circle text-warning"></i>
                                                    <span>{{ $d->remarks }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted small">— Normal Deduction</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No date rows recorded for this leave request.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Ledger Transactions Generated by This Request -->
                <div class="lr-card">
                    <div class="lr-card-header">
                        <h5>
                            <i class="fas fa-receipt text-primary"></i>
                            Ledger Balance Transactions ({{ count($transactions) }})
                        </h5>
                        <span class="text-muted small font-weight-bold">
                            Live audit debits recorded on employee passbook
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table lr-table">
                            <thead>
                                <tr>
                                    <th>Tx ID</th>
                                    <th>Date</th>
                                    <th>Bucket</th>
                                    <th>Type</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Balance After</th>
                                    <th>Reason / Audit Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $tx)
                                    @php
                                        $txType = $tx->transaction_type ?? '';
                                        $isDebit = ($txType === 'debit' || $txType === 'leave_debit' || (float)$tx->amount < 0);
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold text-muted">#{{ $tx->id }}</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-dark">
                                                {{ !empty($tx->effective_date) ? \Carbon\Carbon::parse($tx->effective_date)->format('d M Y') : '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($tx->bucket === 'PL')
                                                <span class="badge font-weight-bold px-2 py-1" style="background:#F4F2FF; color:#4B00E8; border:1px solid #DDD6FE; border-radius:6px;">
                                                    PL (Paid)
                                                </span>
                                            @elseif($tx->bucket === 'SL')
                                                <span class="badge font-weight-bold px-2 py-1" style="background:#EFF6FF; color:#2563EB; border:1px solid #BFDBFE; border-radius:6px;">
                                                    SL (Sick)
                                                </span>
                                            @else
                                                <span class="badge font-weight-bold px-2 py-1" style="background:#F1F5F9; color:#475569; border-radius:6px;">
                                                    {{ $tx->bucket }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($isDebit)
                                                <span class="badge px-2 py-1" style="background:#FFF1F2; color:#E11D48; border:1px solid #FECDD3; font-weight:700; border-radius:6px;">
                                                    Leave Debit
                                                </span>
                                            @else
                                                <span class="badge px-2 py-1" style="background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; font-weight:700; border-radius:6px;">
                                                    {{ ucwords(str_replace('_', ' ', $txType ?: 'Credit')) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold {{ $isDebit ? 'text-danger' : 'text-success' }}" style="font-size: 14px;">
                                            {{ $isDebit && (float)$tx->amount > 0 ? '-' : '' }}{{ number_format((float)$tx->amount, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold text-dark" style="font-size: 14px;">
                                            {{ number_format((float)$tx->balance_after, 2) }}
                                        </td>
                                        <td>
                                            <span class="text-muted small font-weight-600" style="line-height: 1.35; display: inline-block; max-width: 320px;">
                                                {{ $tx->reason ?: '—' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No ledger balance transactions recorded for this request.
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
</div>
@endsection
