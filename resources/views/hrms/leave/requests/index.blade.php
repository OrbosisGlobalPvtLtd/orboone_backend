@extends('layouts.panel')

@section('page_title', 'My Leave Requests')

@section('_head')
@include('settings.partials.styles')

<style>
    .leave-page-container {
        max-width: 1380px;
        margin: 0 auto;
    }

    /* Requested Premium DataTable Toolbar CSS overrides */
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
    }

    .dataTables_length select {
        width: auto !important;
        min-width: 64px !important;
        height: 34px !important;
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
        color: var(--set-primary) !important;
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

    /* Status Pills */
    .orb-pill {
        border-radius: 999px;
        padding: 5px 12px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .orb-pill.pending {
        background: #FFF7E6;
        color: #B54708;
    }
    
    .orb-pill.approved {
        background: #ECFDF3;
        color: #027A48;
    }
    
    .orb-pill.rejected, .orb-pill.cancelled {
        background: #FEF3F2;
        color: #B42318;
    }

    /* Filters Layout Styles */
    .mobile-filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 2fr auto;
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
                <h1 class="set-title">My Leave Requests</h1>
                <p class="set-subtitle">Track your applied leaves, splits, approval states, and remaining allocations.</p>
            </div>
            
            <!-- Glassmorphic Apply Leave Pill Trigger -->
            <div>
                <a href="{{ route('leave-requests.create') }}" class="set-btn" style="background: rgba(255, 255, 255, 0.2) !important; color: #fff !important; border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: none;">
                    <i class="fas fa-plus-circle"></i> Apply Leave
                </a>
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        <!-- Leaves Allocations Metric Grid -->
        <div class="row mb-4">
            @foreach([
                'Total Remaining' => [
                    'val' => $allocation->total_remaining ?? 0,
                    'icon' => 'fa-calendar-alt',
                    'color' => '#4B00E8'
                ],
                'Paid Leaves' => [
                    'val' => $allocation->paid_remaining ?? 0,
                    'icon' => 'fa-star',
                    'color' => '#10B981'
                ],
                'Sick Leaves' => [
                    'val' => $allocation->sick_remaining ?? 0,
                    'icon' => 'fa-heartbeat',
                    'color' => '#EF4444'
                ],
                'Comp Off' => [
                    'val' => $allocation->comp_off_remaining ?? 0,
                    'icon' => 'fa-clock',
                    'color' => '#F59E0B'
                ],
                'LWP Used' => [
                    'val' => $allocation->lwp_used ?? 0,
                    'icon' => 'fa-user-clock',
                    'color' => '#667085'
                ]
            ] as $label => $meta)
                <div class="col-6 col-lg mb-3">
                    <div class="set-card mb-0" style="border-radius: 20px;">
                        <div class="set-card-body p-4 d-flex align-items-center" style="gap: 16px;">
                            <div class="set-icon-box" style="background: rgba(75, 0, 232, 0.05); color: {{ $meta['color'] }}; flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px;">
                                <i class="fas {{ $meta['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="small font-weight-bold text-uppercase" style="font-size: 10px; color: var(--set-muted); letter-spacing: 0.05em;">{{ $label }}</div>
                                <div class="h3 mb-0 font-weight-black mt-1" style="color: var(--set-text); font-size: 20px;">{{ number_format((float) $meta['val'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- History Card -->
        <div class="set-card">
            <div class="set-card-header">
                <div class="set-head-left">
                    <div class="set-icon-box"><i class="fas fa-plane-departure"></i></div>
                    <div>
                        <h5 class="set-card-title">Leave Request History</h5>
                        <p class="set-card-subtitle">Review active requests, splits, reason logs, and processing states.</p>
                    </div>
                </div>
                
                <div>
                    <a href="{{ route('leave-requests.create') }}" class="set-btn" style="background: linear-gradient(135deg, var(--set-primary), var(--set-secondary)) !important; color: #fff !important; height: 38px; border-radius: 11px; padding: 0 16px; font-weight: 850; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-plus-circle"></i> Apply Leave
                    </a>
                </div>
            </div>

            <!-- Attached real-time automatic filters in responsive grid -->
            <div style="border-bottom: 1px solid var(--set-border); background: #FAF9FE; padding: 20px 24px;">
                <div class="mobile-filter-grid">
                    <div class="mobile-filter-group">
                        <label>Leave Type</label>
                        <select id="filterLeaveType" class="mobile-filter-control" onchange="applyLeaveFilters()">
                            <option value="">All Types</option>
                        </select>
                    </div>
                    <div class="mobile-filter-group">
                        <label>Status</label>
                        <select id="filterStatus" class="mobile-filter-control" onchange="applyLeaveFilters()">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mobile-filter-group">
                        <label>Search Reason / Date</label>
                        <input type="text" id="filterSearch" class="mobile-filter-control" placeholder="Search reason notes..." onkeyup="applyLeaveFilters()">
                    </div>
                    <div class="mobile-filter-group">
                        <button type="button" class="mobile-filter-reset" onclick="resetLeaveFilters()">
                            <i class="fas fa-undo"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="set-card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="set-table js-custom-leaves-table" id="employeeLeavesTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Leave Type</th>
                                <th>Requested Dates</th>
                                <th>Duration</th>
                                <th>Allocation Split</th>
                                <th>Approval Status</th>
                                <th>Reason / Note</th>
                                <th width="120" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($requests->count())
                                @foreach($requests as $request)
                                    <tr class="leave-data-row">
                                        <td>{{ $request->id }}</td>
                                        <td class="leave-type-cell"><span style="font-weight: 800; color: var(--set-text);">{{ optional($request->leaveType)->name }}</span></td>
                                        <td class="leave-dates-cell"><span style="font-weight: 700;">{{ optional($request->start_date)->format('d M Y') }} - {{ optional($request->end_date)->format('d M Y') }}</span></td>
                                        <td><span style="font-weight: 800;">{{ $request->deducted_days }} Days</span></td>
                                        <td class="small text-muted" style="font-family: monospace;">P {{ $request->paid_days }} / S {{ $request->sick_days }} / C {{ $request->comp_off_days }} / LWP {{ $request->lwp_days }}</td>
                                        <td class="leave-status-cell">
                                            <span class="orb-pill {{ $request->status }}"><i class="fas fa-circle mr-1" style="font-size: 8px;"></i> {{ ucfirst($request->status) }}</span>
                                        </td>
                                        <td class="leave-reason-cell"><span class="text-muted">{{ \Illuminate\Support\Str::limit($request->reason, 65) }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-end">
                                                @if(in_array($request->status, ['pending','approved'], true))
                                                    <form method="POST" action="{{ route('leave-requests.cancel', $request->id) }}" class="m-0" onsubmit="return confirm('Cancel this leave request?')">
                                                        @csrf
                                                        <button class="set-btn set-btn-danger btn-sm" type="submit" style="min-height: 32px; border-radius: 8px; font-size: 11px; padding: 4px 12px;">
                                                            <i class="fas fa-times-circle"></i> Cancel
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
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
        // Initialize DataTable regardless of row count so toolbar is always rendered!
        if (window.jQuery && $.fn.DataTable && $('#employeeLeavesTable').length) {
            
            // Safe Buttons check to fallback gracefully if the extension fails to load
            var hasButtons = typeof $.fn.dataTable.Buttons !== 'undefined';
            var domLayout = hasButtons 
                ? '<"leave-dt-toolbar"<"leave-dt-left"l><"leave-dt-right"B>>rt<"leave-table-footer"ip>'
                : '<"leave-dt-toolbar"<"leave-dt-left"l>>rt<"leave-table-footer"ip>';

            if (!hasButtons) {
                console.warn('DataTables Buttons extension not loaded');
            }

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

        // Dynamically extract unique leave types from table records to populate the select filter
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
