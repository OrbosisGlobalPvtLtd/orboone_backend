<div class="leave-dt-toolbar">
    <div class="leave-dt-left">
        <div id="recordsLengthBox"></div>
    </div>
    <div class="leave-dt-right">
        <div id="recordsExportButtons"></div>
    </div>
</div>

<div class="att-table-wrap">
    <table class="att-table table" id="regularizationDataTable" style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">#</th>
                <th style="min-width: 135px; max-width: 175px;">Employee</th>
                <th style="white-space: nowrap; width: 90px;">Date</th>
                <th style="white-space: nowrap; width: 125px;">Request Type</th>
                <th class="text-center" style="white-space: nowrap; width: 85px; background: #f8fafc; color: #475569; border-left: 1px solid #e2e8f0;">
                    <i class="fas fa-sign-in-alt mr-1 text-success"></i> Current In
                </th>
                <th class="text-center" style="white-space: nowrap; width: 85px; background: #f8fafc; color: #475569; border-right: 1.5px solid #cbd5e1;">
                    <i class="fas fa-sign-out-alt mr-1 text-danger"></i> Current Out
                </th>
                <th class="text-center" style="white-space: nowrap; width: 85px; background: #faf5ff; color: #6d28d9;">
                    <i class="fas fa-sign-in-alt mr-1 text-primary"></i> Req. In
                </th>
                <th class="text-center" style="white-space: nowrap; width: 85px; background: #faf5ff; color: #6d28d9; border-right: 1.5px solid #e2e8f0;">
                    <i class="fas fa-sign-out-alt mr-1 text-purple"></i> Req. Out
                </th>
                <th style="min-width: 120px; max-width: 180px;">Reason</th>
                <th class="text-center" style="white-space: nowrap; width: 80px;">Status</th>
                <th style="white-space: nowrap; width: 95px;">Submitted At</th>
                <th class="text-right no-export" style="width: 80px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $isOwnRow = $ownEmpId && ((int)$row->employee_id === (int)$ownEmpId);
                    $canApproveThisRow = $canApproveGlobal && !$isOwnRow;
                    $canRejectThisRow = $canRejectGlobal && !$isOwnRow;

                    $empName = $row->employee_display_name ?? $row->name ?? 'Employee';
                    $empCode = $row->employee_code ?? '';
                    $deptName = $row->department_name ?? '';
                    $currentStatusText = $row->current_attendance_type_name ?? ($row->current_attendance_status ? ucwords(str_replace('_', ' ', $row->current_attendance_status)) : 'N/A');

                    $type = $row->request_type;

                    $currentInText = $row->existing_punch_in ? \Carbon\Carbon::parse($row->existing_punch_in)->format('h:i A') : null;
                    $currentOutText = $row->existing_punch_out ? \Carbon\Carbon::parse($row->existing_punch_out)->format('h:i A') : null;
                    $requestedInText = $row->requested_punch_in ? \Carbon\Carbon::parse($row->requested_punch_in)->format('h:i A') : null;
                    $requestedOutText = $row->requested_punch_out ? \Carbon\Carbon::parse($row->requested_punch_out)->format('h:i A') : null;

                    if ($type === 'other' && str_contains($row->reason, '[Attendance Status Correction:')) {
                        preg_match('/\[Attendance Status Correction:\s*([^\]]+)\]/', $row->reason, $matches);
                        $requestedTimingDisplay = $matches[1] ?? 'Correction';
                    }

                    $rowDate = $row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at));
                    $cleanReason = str_replace(['[Attendance Status Correction: Present]', '[Attendance Status Correction: Half Day]', '[Attendance Status Correction: Absent]', '[Attendance Status Correction: present]', '[Attendance Status Correction: half_day]', '[Attendance Status Correction: absent]'], '', $row->reason ?? '');

                    $badge = $row->status === 'approved'
                        ? 'orb-badge-success'
                        : ($row->status === 'pending'
                            ? 'orb-badge-warning'
                            : ($row->status === 'rejected'
                                ? 'orb-badge-danger'
                                : 'orb-badge-secondary'));

                    $sNo = method_exists($rows, 'currentPage') ? (($rows->currentPage() - 1) * $rows->perPage()) + $loop->iteration : $loop->iteration;
                @endphp
                <tr>
                    {{-- S.No --}}
                    <td class="text-center font-weight-bold text-muted">{{ $sNo }}</td>

                    {{-- Employee (No Photo for Faster Loading) --}}
                    <td>
                        <div style="min-width: 0;">
                            <div class="att-emp-name text-truncate" title="{{ $empName }}" style="font-weight: 800; font-size: 13px; color: #1e293b; line-height: 1.3; max-width: 170px;">
                                {{ $empName }}
                            </div>
                            <div class="att-emp-code text-truncate" title="{{ $empCode }}" style="font-size: 11px; color: #64748b; font-weight: 700; line-height: 1.3; margin-top: 3px; max-width: 170px;">
                                <span class="badge border text-dark font-weight-bold" style="font-size: 9.5px; padding: 2px 5px; border-radius: 4px; background: #f8fafc; border-color: #e2e8f0 !important;">{{ $empCode }}</span>
                                @if($deptName)
                                    <span class="text-muted ml-1">{{ $deptName }}</span>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Date --}}
                    <td style="white-space: nowrap;">
                        <strong class="text-dark" style="font-size: 12px;">{{ \Carbon\Carbon::parse($rowDate)->format('d M Y') }}</strong>
                    </td>

                    {{-- Request Type --}}
                    <td style="white-space: nowrap;">
                        <span class="orb-badge orb-badge-primary">
                            {{ $typeLabels[$row->request_type] ?? ucfirst(str_replace('_', ' ', $row->request_type)) }}
                        </span>
                    </td>

                    {{-- Current Punch In --}}
                    <td class="text-center" style="white-space: nowrap; border-left: 1px solid #f1f5f9; background: #fafbfc;">
                        @if ($currentInText)
                            <span class="badge px-2 py-1 font-weight-bold" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 6px; font-size: 11px;">
                                <i class="fas fa-sign-in-alt mr-1"></i>{{ $currentInText }}
                            </span>
                        @else
                            <span class="text-muted font-weight-bold" style="font-size: 12px;">—</span>
                        @endif
                    </td>

                    {{-- Current Punch Out --}}
                    <td class="text-center" style="white-space: nowrap; border-right: 1px solid #cbd5e1; background: #fafbfc;">
                        @if ($currentOutText)
                            <span class="badge px-2 py-1 font-weight-bold" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 6px; font-size: 11px;">
                                <i class="fas fa-sign-out-alt mr-1"></i>{{ $currentOutText }}
                            </span>
                        @else
                            <span class="text-muted font-weight-bold" style="font-size: 12px;">—</span>
                        @endif
                    </td>

                    {{-- Requested Punch In --}}
                    <td class="text-center" style="white-space: nowrap; background: #fdfcff;">
                        @if ($requestedInText)
                            <span class="badge px-2 py-1 font-weight-bold" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 6px; font-size: 11px;">
                                <i class="fas fa-sign-in-alt mr-1"></i>{{ $requestedInText }}
                            </span>
                        @elseif ($type === 'other' && str_contains($row->reason, '[Attendance Status Correction:'))
                            <span class="badge badge-info px-2 py-1" style="font-size: 10.5px;">Status</span>
                        @else
                            <span class="text-muted font-weight-bold" style="font-size: 12px;">—</span>
                        @endif
                    </td>

                    {{-- Requested Punch Out --}}
                    <td class="text-center" style="white-space: nowrap; border-right: 1px solid #e2e8f0; background: #fdfcff;">
                        @if ($requestedOutText)
                            <span class="badge px-2 py-1 font-weight-bold" style="background: #faf5ff; color: #7c3aed; border: 1px solid #ddd6fe; border-radius: 6px; font-size: 11px;">
                                <i class="fas fa-sign-out-alt mr-1"></i>{{ $requestedOutText }}
                            </span>
                        @elseif ($type === 'other' && str_contains($row->reason, '[Attendance Status Correction:'))
                            <span class="badge badge-info px-2 py-1" style="font-size: 10.5px;">{{ $requestedTimingDisplay ?? 'Correction' }}</span>
                        @else
                            <span class="text-muted font-weight-bold" style="font-size: 12px;">—</span>
                        @endif
                    </td>

                    {{-- Reason --}}
                    <td>
                        <div class="text-truncate" style="max-width: 170px; font-size: 12px; font-weight: 600;" title="{{ $cleanReason }}">
                            <span>{{ $cleanReason }}</span>
                        </div>
                    </td>

                    {{-- Status --}}
                    <td class="text-center" style="white-space: nowrap;">
                        <span class="orb-badge {{ $badge }}">
                            {{ ucfirst((string) $row->status) }}
                        </span>
                    </td>

                    {{-- Submitted At --}}
                    <td style="white-space: nowrap;">
                        <div style="font-size: 11.5px; font-weight: 700; color: #1e293b; line-height: 1.25;">
                            {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}
                        </div>
                        <div class="text-muted font-weight-semibold" style="font-size: 10.5px; line-height: 1.25;">
                            <i class="far fa-clock mr-1" style="font-size: 9.5px;"></i>{{ \Carbon\Carbon::parse($row->created_at)->format('h:i A') }}
                        </div>
                    </td>

                    {{-- Action --}}
                    <td class="text-right no-export" style="white-space: nowrap;">
                        <div class="dropdown d-inline-block">
                            <button class="orb-action-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions" style="width: 30px; height: 30px;">
                                <i class="fas fa-ellipsis-v" style="font-size: 12px;"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" style="border-radius: 12px; padding: 6px; min-width: 175px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05) !important;">
                                <button type="button" class="dropdown-item py-2 font-weight-bold text-dark" data-toggle="modal" data-target="#viewModal{{ $row->id }}" style="border-radius: 8px; font-size: 12.5px;">
                                    <i class="fas fa-eye mr-2" style="color: var(--orb-primary, #4B00E8);"></i> View Details
                                </button>

                                @if($row->status === 'pending' && (!empty($canApproveThisRow) || !empty($canRejectThisRow) || !empty($canEdit) || !empty($canDelete)))
                                    <div class="dropdown-divider my-1"></div>
                                    @if(!empty($canApproveThisRow))
                                        <button type="button" class="dropdown-item py-2 font-weight-bold text-success" data-toggle="modal" data-target="#approveModal{{ $row->id }}" style="border-radius: 8px; font-size: 12.5px;">
                                            <i class="fas fa-check-circle mr-2"></i> Approve Request
                                        </button>
                                    @endif
                                    @if(!empty($canRejectThisRow))
                                        <button type="button" class="dropdown-item py-2 font-weight-bold text-danger" data-toggle="modal" data-target="#rejectModal{{ $row->id }}" style="border-radius: 8px; font-size: 12.5px;">
                                            <i class="fas fa-times-circle mr-2"></i> Reject Request
                                        </button>
                                    @endif
                                    @if(!empty($canEdit))
                                        <button type="button" class="dropdown-item py-2 font-weight-bold text-primary" data-toggle="modal" data-target="#editModal{{ $row->id }}" style="border-radius: 8px; font-size: 12.5px;">
                                            <i class="fas fa-edit mr-2"></i> Edit Request
                                        </button>
                                    @endif
                                    @if(!empty($canDelete))
                                        <form method="POST" action="{{ route($deleteRoute, $row->id) }}" onsubmit="return confirm('Delete this record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item py-2 font-weight-bold text-danger" type="submit" style="border-radius: 8px; font-size: 12.5px;">
                                                <i class="fas fa-trash-alt mr-2"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 text-secondary" style="opacity: 0.4;"></i>
                            <h6 class="font-weight-bold mb-1">No Regularization Requests Found</h6>
                            <p class="small text-muted mb-0">Try adjusting your filters or date range.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($rows, 'links'))
    <div style="border-bottom-left-radius: 18px; border-bottom-right-radius: 18px; overflow: hidden;">
        {{ $rows->appends(request()->query())->links('vendor.pagination.orbo') }}
    </div>
@endif
