<div class="orb-table-tools">
    <div class="crud-dt-toolbar">
        <div class="crud-dt-left">
            <label class="mb-0 d-flex align-items-center font-weight-bold text-muted" style="font-size: 13px; gap: 8px;">
                Show
                <select name="per_page" class="form-control form-control-sm custom-select" style="width: 75px; height: 34px; font-weight: 700; border-radius: 8px; padding: 0 8px;" onchange="var u = new URL(window.location.href); u.searchParams.set('per_page', this.value); u.searchParams.set('page', '1'); window.location.href = u.toString();">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                entries
            </label>
        </div>
        <div class="crud-dt-right"></div>
    </div>
</div>

<div class="orb-table-wrap crud-table-responsive">
    <table class="table table-hover orb-table js-orb-datatable">
        <thead>
            <tr>
                <th>S.No.</th>
                <th>Employee</th>
                <th>Code</th>
                <th>Date Range</th>
                <th>Working Days</th>
                <th>Source</th>
                <th>Request Type</th>
                <th>Reason Category</th>
                <th>Quota Impact</th>
                <th>Payroll Impact</th>
                <th>Status</th>
                <th>Approved By</th>
                <th class="no-export">Action</th>
            </tr>
        </thead>
        <tbody>
            @php
                $startIdx = ($rows->currentPage() - 1) * $rows->perPage();
            @endphp
            @forelse($rows as $row)
            <tr>
                <td><strong>{{ $startIdx + $loop->iteration }}</strong></td>
                <td>
                    <div class="font-weight-bold text-dark">{{ $row->employee_display_name ?? '-' }}</div>
                </td>
                <td><span class="text-muted font-weight-bold">{{ $row->employee_code ?? '-' }}</span></td>
                <td><strong>{{ $row->date_range_label ?? \Carbon\Carbon::parse($row->request_date)->format('d M Y') }}</strong></td>
                <td><span class="orb-badge orb-badge-primary">{{ $row->working_days ?? 1 }} Days</span></td>
                <td><span class="text-muted">{{ $row->source_label ?? 'Employee Requested' }}</span></td>
                <td>{{ ucwords(str_replace('_', ' ', $row->request_type)) }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $row->reason_category)) }}</td>
                <td>
                    <span class="orb-badge {{ $row->counts_in_monthly_quota ? 'orb-badge-warning' : 'orb-badge-primary' }}">
                        {{ $row->counts_in_monthly_quota ? 'Counts in Quota' : 'Non-Quota' }}
                    </span>
                </td>
                <td>
                    <span class="orb-badge {{ $row->payroll_impact === 'lwp' ? 'orb-badge-danger' : 'orb-badge-success' }}">
                        {{ strtoupper($row->payroll_impact === 'lwp' ? 'LWP' : 'None') }}
                    </span>
                </td>
                <td>
                    @php
                        $st = strtolower($row->status ?? 'pending');
                        $hasMgr = ! empty($row->reporting_manager_employee_id);
                        $isAssignedMgr = (! empty($userEmpId) && ! empty($row->reporting_manager_employee_id) && (int) $userEmpId === (int) $row->reporting_manager_employee_id);
                    @endphp
                    @if($st === 'approved')
                        <span class="orb-badge orb-badge-success">
                            <i class="fas fa-check-circle mr-1"></i> Approved
                        </span>
                    @elseif($st === 'rejected' || $st === 'cancelled')
                        <span class="orb-badge orb-badge-danger">
                            <i class="fas fa-times-circle mr-1"></i> {{ ucfirst($st) }}
                        </span>
                    @elseif($st === 'manager_approved')
                        <span class="orb-badge orb-badge-info">
                            <i class="fas fa-user-check mr-1"></i> Pending HR
                        </span>
                    @elseif($st === 'pending')
                        @if($hasMgr)
                            <span class="orb-badge orb-badge-warning">
                                <i class="fas fa-hourglass-half mr-1"></i> Pending Manager
                            </span>
                        @else
                            <span class="orb-badge orb-badge-info">
                                <i class="fas fa-user-clock mr-1"></i> Pending HR
                            </span>
                        @endif
                    @else
                        <span class="orb-badge orb-badge-warning">
                            <i class="fas fa-clock mr-1"></i> Pending
                        </span>
                    @endif
                </td>
                <td><span class="text-muted">{{ $row->approved_by_label ?? '-' }}</span></td>
                <td class="no-export">
                    <div class="dropdown">
                        <button class="orb-action-btn" type="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" style="border-radius: 12px; padding: 6px;">
                            <button type="button" class="dropdown-item js-view-details py-2" data-row='@json($row)'>
                                <i class="fas fa-eye text-muted mr-2"></i> View Details
                            </button>
                            
                            @if(in_array($st, ['pending', 'manager_approved']) && ($canApprove || $canReject))
                                @if($isSuperAdmin ?? false)
                                    @if($canApprove)
                                    <button type="button" class="dropdown-item text-success js-approve font-weight-bold py-2" data-id="{{ $row->id }}" data-row='@json($row)'>
                                        <i class="fas fa-crown text-warning mr-2"></i> Super Admin Approve
                                    </button>
                                    @endif
                                    @if($canReject)
                                    <button type="button" class="dropdown-item text-danger js-reject font-weight-bold py-2" data-id="{{ $row->id }}">
                                        <i class="fas fa-times-circle mr-2"></i> Reject Request
                                    </button>
                                    @endif
                                @elseif($st === 'pending' && $isAssignedMgr)
                                    @if($canApprove)
                                    <button type="button" class="dropdown-item text-success js-approve font-weight-bold py-2" data-id="{{ $row->id }}" data-row='@json($row)'>
                                        <i class="fas fa-check-circle text-success mr-2"></i> Manager Approve
                                    </button>
                                    @endif
                                    @if($canReject)
                                    <button type="button" class="dropdown-item text-danger js-reject font-weight-bold py-2" data-id="{{ $row->id }}">
                                        <i class="fas fa-times-circle mr-2"></i> Reject Request
                                    </button>
                                    @endif
                                @elseif($isHrOrAdmin ?? false)
                                    @if($canApprove)
                                    <button type="button" class="dropdown-item text-primary js-approve font-weight-bold py-2" data-id="{{ $row->id }}" data-row='@json($row)'>
                                        <i class="fas fa-check-double text-primary mr-2"></i> HR Final Approve
                                    </button>
                                    @endif
                                    @if($canReject)
                                    <button type="button" class="dropdown-item text-danger js-reject font-weight-bold py-2" data-id="{{ $row->id }}">
                                        <i class="fas fa-times-circle mr-2"></i> Reject Request
                                    </button>
                                    @endif
                                @endif
                            @endif

                            @if(($canMarkLwp ?? false) && $row->status === 'approved' && $row->payroll_impact !== 'lwp')
                            <button type="button" class="dropdown-item text-warning js-mark-lwp py-2" data-id="{{ $row->id }}">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Mark as LWP
                            </button>
                            @endif
                            @if($canAssign ?? false)
                            <button type="button" class="dropdown-item js-open-assign py-2">
                                <i class="fas fa-plus-circle text-info mr-2"></i> Assign WFH
                            </button>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x d-block mb-3 opacity-50"></i>
                    <strong>No WFH requests found matching the criteria.</strong>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($rows, 'links'))
    {{ $rows->appends(request()->query())->links('vendor.pagination.orbo') }}
@endif
