@extends('layouts.panel')

@section('page_title', 'Leave Balances')

@section('_head')
@include('settings.partials.styles')

<style>
    .leave-page-container {
        max-width: 1380px;
        margin: 0 auto;
    }

    /* Target customized DataTable controls */
    .leave-dt-toolbar {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        padding: 14px 24px !important;
        background: #fff !important;
        border-top: 1px solid #E7EAF3 !important;
        border-bottom: 1px solid #E7EAF3 !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .leave-dt-left,
    .leave-dt-right,
    .dt-buttons {
        display: flex !important;
        align-items: center !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .leave-dt-right {
        margin-left: auto !important;
        gap: 8px !important;
    }

    .dataTables_length {
        display: block !important;
    }

    .dataTables_length label {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
        white-space: nowrap !important;
        font-weight: 850 !important;
        font-size: 12px !important;
        color: var(--set-muted) !important;
    }

    .dataTables_length select {
        width: auto !important;
        min-width: 64px !important;
        height: 34px !important;
        border-radius: 8px !important;
        border: 1px solid var(--set-border) !important;
    }

    .dt-buttons .dt-button,
    .leave-export-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: auto !important;
        min-width: auto !important;
        height: 34px !important;
        padding: 0 12px !important;
        border-radius: 10px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border: 1px solid #E7EAF3 !important;
        background: #fff !important;
        color: #4B00E8 !important;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .dt-buttons .dt-button:hover,
    .leave-export-btn:hover {
        background: var(--set-primary) !important;
        color: #fff !important;
        border-color: var(--set-primary) !important;
    }

    .dataTables_info {
        font-size: 12px !important;
        font-weight: 750 !important;
        color: var(--set-muted) !important;
    }

    .leave-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-top: 1px solid var(--set-border);
        background: #FAF9FE;
    }

    .dataTables_paginate .pagination {
        margin: 0 !important;
    }

    .dataTables_paginate .paginate_button.active a,
    .dataTables_paginate .paginate_button:hover a {
        background: var(--set-primary) !important;
        border-color: var(--set-primary) !important;
        color: #fff !important;
    }

    /* Badges */
    .leave-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
        text-transform: uppercase;
        display: inline-block;
    }

    .badge-paid {
        background: #ECFDF3;
        color: #027A48;
    }

    .badge-pending {
        background: #FFF7E6;
        color: #B54708;
    }

    .badge-comp-off {
        background: #EFF8FF;
        color: #175CD3;
    }

    .badge-lwp {
        background: #FEF2F2;
        color: #EF4444;
    }

    /* Filters Grid */
    .mobile-filter-grid {
        display: grid;
        grid-template-columns: 1fr 2fr auto;
        gap: 12px;
        align-items: end;
    }

    .mobile-filter-group {
        display: flex;
        flex-direction: column;
    }

    .mobile-filter-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #667085;
        letter-spacing: .04em;
    }

    .mobile-filter-control {
        width: 100%;
        height: 40px;
        border: 1px solid #E7EAF3;
        border-radius: 12px;
        padding: 0 12px;
        background: #fff;
        font-size: 13px;
        color: #101828;
        outline: none;
        transition: all 0.2s ease;
    }

    .mobile-filter-control:focus {
        border-color: var(--set-primary);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08);
    }

    .mobile-filter-reset {
        height: 40px;
        padding: 0 16px;
        border-radius: 12px;
        border: 1px solid #E7EAF3;
        background: #fff;
        font-weight: 800;
        font-size: 12px;
        color: #475569;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .mobile-filter-reset:hover {
        background: #F1F5F9;
        color: #1E293B;
    }

    @media (max-width: 991px) {
        .mobile-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .mobile-filter-group:last-child {
            grid-column: span 2;
        }
    }

    @media (max-width: 575px) {
        .mobile-filter-grid {
            grid-template-columns: 1fr;
        }
        .mobile-filter-group:last-child {
            grid-column: span 1;
        }
    }
</style>
@endsection

@section('_content')
<div class="set-page">
    <div class="leave-page-container">
        
        <!-- Premium Purple Gradient Hero Header -->
        <div class="set-header">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-calendar-alt"></i> EMPLOYEE &bull; LEAVE MANAGEMENT
                </div>
                <h1 class="set-title">Leave Balances</h1>
                <p class="set-subtitle">Review available, used, pending, LWP and allocated leave balances across active years.</p>
            </div>
            
            <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                <a href="{{ route('leave-requests.index') }}" class="set-btn" style="background: rgba(255, 255, 255, 0.15) !important; color: #fff !important; border: 1px solid rgba(255, 255, 255, 0.25); box-shadow: none;">
                    <i class="fas fa-history"></i> Leave History
                </a>
                <a href="{{ route('leave-requests.create') }}" class="set-btn" style="background: rgba(255, 255, 255, 0.25) !important; color: #fff !important; border: 1px solid rgba(255, 255, 255, 0.35); box-shadow: none;">
                    <i class="fas fa-plus-circle"></i> Apply Leave
                </a>
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        <!-- Dynamic Summary Cards Computed From Collection -->
        @php
            $totalAllocated = $balances->sum('total_allocated');
            $totalRemaining = $balances->sum('total_remaining');
            $totalPaidRem = $balances->sum('paid_remaining');
            $totalSickRem = $balances->sum('sick_remaining');
            $totalLwpUsed = $balances->sum('lwp_used');
        @endphp

        <div class="row mb-4">
            <div class="col-6 col-lg mb-3">
                <div class="set-card mb-0" style="border-radius: 20px;">
                    <div class="set-card-body p-4 d-flex align-items-center" style="gap: 16px;">
                        <div class="set-icon-box" style="background: rgba(75, 0, 232, 0.05); color: #4B00E8; flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <div class="small font-weight-bold text-uppercase" style="font-size: 10px; color: var(--set-muted); letter-spacing: 0.05em;">Total Allocated</div>
                            <div class="h3 mb-0 font-weight-black mt-1" style="color: var(--set-text); font-size: 20px;">{{ number_format((float) $totalAllocated, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-lg mb-3">
                <div class="set-card mb-0" style="border-radius: 20px;">
                    <div class="set-card-body p-4 d-flex align-items-center" style="gap: 16px;">
                        <div class="set-icon-box" style="background: rgba(16, 185, 129, 0.05); color: #10B981; flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="small font-weight-bold text-uppercase" style="font-size: 10px; color: var(--set-muted); letter-spacing: 0.05em;">Available Balance</div>
                            <div class="h3 mb-0 font-weight-black mt-1" style="color: var(--set-text); font-size: 20px;">{{ number_format((float) $totalRemaining, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg mb-3">
                <div class="set-card mb-0" style="border-radius: 20px;">
                    <div class="set-card-body p-4 d-flex align-items-center" style="gap: 16px;">
                        <div class="set-icon-box" style="background: rgba(23, 92, 211, 0.05); color: #175CD3; flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px;">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div>
                            <div class="small font-weight-bold text-uppercase" style="font-size: 10px; color: var(--set-muted); letter-spacing: 0.05em;">Paid Remaining</div>
                            <div class="h3 mb-0 font-weight-black mt-1" style="color: var(--set-text); font-size: 20px;">{{ number_format((float) $totalPaidRem, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg mb-3">
                <div class="set-card mb-0" style="border-radius: 20px;">
                    <div class="set-card-body p-4 d-flex align-items-center" style="gap: 16px;">
                        <div class="set-icon-box" style="background: rgba(245, 158, 11, 0.05); color: #F59E0B; flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px;">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div>
                            <div class="small font-weight-bold text-uppercase" style="font-size: 10px; color: var(--set-muted); letter-spacing: 0.05em;">Sick Remaining</div>
                            <div class="h3 mb-0 font-weight-black mt-1" style="color: var(--set-text); font-size: 20px;">{{ number_format((float) $totalSickRem, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg mb-3">
                <div class="set-card mb-0" style="border-radius: 20px;">
                    <div class="set-card-body p-4 d-flex align-items-center" style="gap: 16px;">
                        <div class="set-icon-box" style="background: rgba(239, 68, 68, 0.05); color: #EF4444; flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px;">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div>
                            <div class="small font-weight-bold text-uppercase" style="font-size: 10px; color: var(--set-muted); letter-spacing: 0.05em;">LWP Used</div>
                            <div class="h3 mb-0 font-weight-black mt-1" style="color: var(--set-text); font-size: 20px;">{{ number_format((float) $totalLwpUsed, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card Wrapper -->
        <div class="set-card">
            <div class="set-card-header">
                <div class="set-head-left">
                    <div class="set-icon-box"><i class="fas fa-chart-pie"></i></div>
                    <div>
                        <h5 class="set-card-title">Employee Leave Allocations</h5>
                        <p class="set-card-subtitle">Review active quota targets, deduct counts, and LWP parameters.</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <!-- Export buttons container -->
                    <div id="leaveBalancesExportButtons"></div>
                </div>
            </div>

            <!-- Attached real-time automatic filters in responsive grid -->
            <div style="border-bottom: 1px solid var(--set-border); background: #FAF9FE; padding: 20px 24px;">
                <form class="mobile-filter-grid" method="GET" action="">
                    <div class="mobile-filter-group">
                        <label>Filter Year</label>
                        <input name="year" type="number" class="mobile-filter-control" value="{{ request('year', $year ?? date('Y')) }}" placeholder="e.g. 2026">
                    </div>
                    <div class="mobile-filter-group">
                        <label>Employee Select</label>
                        <select name="employee_id" class="mobile-filter-control">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->user_name ?? $employee->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mobile-filter-group d-flex flex-row" style="gap: 8px;">
                        <button type="submit" class="set-btn" style="height: 40px; padding: 0 16px; border-radius: 12px;">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                        <a href="{{ url()->current() }}" class="mobile-filter-reset">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="set-card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="set-table js-custom-balances-table" id="leaveBalancesTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Allocation Year</th>
                                <th>Total Available / Quota</th>
                                <th>Paid Rem.</th>
                                <th>Sick Rem.</th>
                                <th>Comp Off Rem.</th>
                                <th>LWP Used</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($balances->count())
                                @foreach($balances as $balance)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center mr-2" style="width:34px;height:34px;font-weight:900;background:rgba(75, 0, 232, 0.06); font-size:12px;">
                                                    {{ substr(optional($balance->employee)->display_name ?? 'U', 0, 1) }}
                                                </div>
                                                <span style="font-weight: 850; color: var(--set-text);">{{ optional($balance->employee)->display_name }}</span>
                                            </div>
                                        </td>
                                        <td><span style="font-weight: 800; font-family: monospace; font-size:13px; color: var(--set-muted);">{{ $balance->year }}</span></td>
                                        <td>
                                            <span class="text-success font-weight-black" style="font-size: 14px;">{{ $balance->total_remaining }}</span>
                                            <span class="small text-muted">/ {{ $balance->total_allocated }} Allocated</span>
                                        </td>
                                        <td><span class="leave-badge badge-paid"><i class="fas fa-check-circle mr-1"></i> {{ $balance->paid_remaining }} Rem.</span></td>
                                        <td><span class="leave-badge badge-pending"><i class="fas fa-heartbeat mr-1"></i> {{ $balance->sick_remaining }} Rem.</span></td>
                                        <td><span class="leave-badge badge-comp-off"><i class="fas fa-clock mr-1"></i> {{ $balance->comp_off_remaining }} Rem.</span></td>
                                        <td><span class="leave-badge badge-lwp"><i class="fas fa-user-times mr-1"></i> {{ $balance->lwp_used }} Used</span></td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                
                @if(method_exists($balances, 'links') && $balances->hasPages())
                    <div class="border-top" style="padding: 16px 24px;">
                        {{ $balances->links() }}
                    </div>
                @endif
            </div>
        </div>
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
        if (window.jQuery && $.fn.DataTable && $('#leaveBalancesTable').length) {
            
            // Safe Buttons check to fallback gracefully if the extension fails to load
            var hasButtons = typeof $.fn.dataTable.Buttons !== 'undefined';
            var domLayout = hasButtons 
                ? '<"leave-dt-toolbar"<"leave-dt-left"l><"leave-dt-right"B>>rt<"leave-table-footer"ip>'
                : '<"leave-dt-toolbar"<"leave-dt-left"l>>rt<"leave-table-footer"ip>';

            if (!hasButtons) {
                console.warn('DataTables Buttons extension not loaded');
            }

            $('.js-custom-balances-table').DataTable({
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
    });
</script>
@endsection
