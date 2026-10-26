<!-- 3. MAIN TABLE CARD -->
<div class="ep-card">
    <div class="ep-table-header">
        <div class="ep-table-head-left">
            <div class="ep-icon-box" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #7C3AED) 100%); color: #FFFFFF; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25);"><i class="fas fa-list-alt"></i></div>
            <div>
                <h5 class="ep-table-title">{{ $isAdminOrHr ? 'Leave Requests Directory' : 'Leave Request History' }}</h5>
                <p class="ep-table-subtitle">Review active requests, splits, reason logs, and processing states.</p>
            </div>
        </div>
        
        <div>
            <a href="{{ route('leave-requests.create') }}" class="btn btn-primary font-weight-bold shadow-sm" style="border-radius: 12px; padding: 9px 20px; font-size: 13px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #7C3AED) 100%); border: none; box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25);">
                <i class="fas fa-plus-circle mr-1"></i> Apply Leave
            </a>
        </div>
    </div>

    <!-- Filters Header Bar -->
    @include('hrms.leave.requests.partials.filter-bar')

    <div class="ep-card-body p-0">
        <!-- Theme Standard Table Tools Bar: Show Entries (Left) & Export Group (Right) -->
        <div class="orb-table-tools-bar eo-toolbar">
            <div class="orb-table-length-box eo-toolbar-left">
                <label class="eo-entries-label mb-0 d-flex align-items-center font-weight-bold text-muted" style="font-size: 13px; gap: 8px;">
                    <span>Show</span>
                    <select id="leaveRequestsPerPageSelect" name="per_page" class="table-per-page-select orb-per-page-select" style="width: 75px; height: 36px; border-radius: 10px; border: 1px solid #CBD5E1; font-weight: 700; padding: 0 8px; background: #fff;">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>entries</span>
                </label>
            </div>
            <div class="orb-table-export-buttons eo-toolbar-right" data-target-table="employeeLeavesTable">
                <div class="orbo-export-group" data-export-group data-target-table="employeeLeavesTable">
                    <button type="button" class="orbo-export-btn btn-export-excel" data-export="excel" onclick="exportEmployeeLeaves('excel')" title="Export Excel">
                        <i class="fas fa-file-excel icon-excel"></i>
                        <span>Excel</span>
                    </button>
                    <button type="button" class="orbo-export-btn btn-export-csv" data-export="csv" onclick="exportEmployeeLeaves('csv')" title="Export CSV">
                        <i class="fas fa-file-csv icon-csv"></i>
                        <span>CSV</span>
                    </button>
                    <button type="button" class="orbo-export-btn btn-export-pdf" data-export="pdf" onclick="exportEmployeeLeaves('pdf')" title="Export PDF">
                        <i class="fas fa-file-pdf icon-pdf"></i>
                        <span>PDF</span>
                    </button>
                    <button type="button" class="orbo-export-btn btn-export-print" data-export="print" onclick="exportEmployeeLeaves('print')" title="Print">
                        <i class="fas fa-print icon-print"></i>
                        <span>Print</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="ep-table-wrap">
            <table class="table ep-table js-custom-leaves-table m-0" id="employeeLeavesTable" style="width: 100%;">
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
                        <th class="text-center no-export" style="width: 70px;" data-orderable="false">Actions</th>
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
                                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                    <span class="font-weight-bold text-dark">{{ optional($request->leaveType)->name ?? 'Leave' }}</span>
                                </div>
                            </td>
                            <td class="leave-dates-cell">
                                <span class="font-weight-bold text-dark">{{ optional($request->start_date)->format('d M Y') }} - {{ optional($request->end_date)->format('d M Y') }}</span>
                            </td>
                            <td>
                                <span class="ep-badge ep-badge-primary">{{ $request->deducted_days }} Days</span>
                                @if($request->is_half_day)
                                    <div class="mt-1">
                                        <span class="badge badge-pill font-weight-bold" style="background: rgba(14, 165, 233, 0.12); color: #0284C7; border: 1px solid rgba(14, 165, 233, 0.28); font-size: 10px; padding: 2px 7px;">
                                            <i class="far fa-clock mr-0.5"></i> {{ $request->half_day_type === 'second_half' ? '2nd Half' : '1st Half' }}
                                        </span>
                                    </div>
                                @endif
                                @if($request->sandwich_applied)
                                    <div class="mt-0.5">
                                        <span class="badge badge-pill font-weight-bold" style="background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D; font-size: 10px; padding: 2px 7px;" title="Sandwich Policy Applied">
                                            <i class="fas fa-layer-group mr-0.5"></i> Sandwich
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="leave-status-cell">
                                <span class="orb-pill {{ $request->status }}">
                                    <i class="fas fa-circle mr-1" style="font-size: 7px;"></i> {{ $request->status === 'void' ? 'Null & Void' : ucfirst($request->status) }}
                                </span>
                            </td>
                            <td class="leave-reason-cell">
                                <span class="text-muted" title="{{ $request->reason }}">{{ \Illuminate\Support\Str::limit($request->reason, 55) }}</span>
                            </td>
                            <td class="text-center align-middle no-export" style="width: 70px;">
                                <div class="dropdown">
                                    <button class="btn-action-dots" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-action">
                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#viewModal{{ $request->id }}">
                                            <i class="fas fa-eye text-primary"></i>
                                            <span>View Details</span>
                                        </a>

                                        @if($request->attachment_path)
                                            <a class="dropdown-item" href="{{ route('leave-requests.attachment', $request->id) }}" target="_blank">
                                                <i class="fas fa-paperclip text-info"></i>
                                                <span>View Attachment</span>
                                            </a>
                                        @endif

                                        @if($request->status === 'pending')
                                            <div class="dropdown-divider my-1"></div>
                                            <a class="dropdown-item js-edit-request text-warning" 
                                               href="javascript:void(0)" 
                                               data-id="{{ $request->id }}"
                                               data-employee-id="{{ $request->employee_id }}"
                                               data-leave-type-id="{{ $request->leave_type_id }}"
                                               data-start-date="{{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->setTimezone('Asia/Kolkata')->format('Y-m-d') : '' }}"
                                               data-end-date="{{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->setTimezone('Asia/Kolkata')->format('Y-m-d') : '' }}"
                                               data-is-half-day="{{ $request->is_half_day ? 1 : 0 }}"
                                               data-half-day-type="{{ $request->half_day_type ?: 'first_half' }}"
                                               data-emergency-leave="{{ $request->emergency_leave ? 1 : 0 }}"
                                               data-reason="{{ e($request->reason) }}"
                                               data-attachment-path="{{ $request->attachment_path ? e($request->attachment_path) : '' }}"
                                               data-attachment-url="{{ $request->attachment_path ? route('leave-requests.attachment', $request->id) : '' }}"
                                               data-row='@json($request)'>
                                                <i class="fas fa-edit text-warning"></i>
                                                <span>Edit Request</span>
                                            </a>

                                            <form id="cancel-form-{{ $request->id }}" method="POST" action="{{ route('leave-requests.cancel', $request->id) }}" style="display:none;">
                                                @csrf
                                            </form>
                                            <a class="dropdown-item text-danger font-weight-bold" href="javascript:void(0)" onclick="if(confirm('Are you sure you want to cancel this leave request?')) document.getElementById('cancel-form-{{ $request->id }}').submit();">
                                                <i class="fas fa-times-circle text-danger"></i>
                                                <span>Cancel Request</span>
                                            </a>
                                        @endif
                                    </div>
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

        <!-- Theme Vendor Pagination -->
        <div class="p-0 border-top bg-white">
            {{ $requests->appends(request()->query())->links('vendor.pagination.orbo') }}
        </div>
    </div>
</div>
