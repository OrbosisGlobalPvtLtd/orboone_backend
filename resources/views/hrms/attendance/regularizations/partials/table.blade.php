<!-- UNIFIED DATATABLES TOOLBAR -->
<div class="leave-dt-toolbar">
    <div class="leave-dt-left">
        <div id="recordsLengthBox"></div>
    </div>
    <div class="leave-dt-right">
        <div id="recordsExportButtons"></div>
    </div>
</div>

<div class="att-table-wrap">
    <table class="att-table table" id="regularizationDataTable">
        <thead>
            <tr>
                <th style="width: 55px;" class="text-center">S.No.</th>
                <th>Employee</th>
                <th>Date</th>
                <th>Request Type</th>
                <th class="text-center">Current Timing</th>
                <th class="text-center">Requested Timing</th>
                <th>Reason</th>
                <th class="text-center">Status</th>
                <th>Submitted At</th>
                <th class="text-right no-export" style="width: 130px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $photoUrl = resolveEmployeePassportPhoto($row->employee_id);
                    $initials = resolveEmployeeInitials($row->employee_id);
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
                        $currentTimingDisplay = $currentStatusText;
                        preg_match('/\[Attendance Status Correction:\s*([^\]]+)\]/', $row->reason, $matches);
                        $requestedTimingDisplay = $matches[1] ?? 'Correction';
                    } else {
                        if ($currentInText && $currentOutText) {
                            $currentTimingDisplay = "{$currentInText} - {$currentOutText}";
                        } elseif ($currentInText) {
                            $currentTimingDisplay = "In: {$currentInText}";
                        } elseif ($currentOutText) {
                            $currentTimingDisplay = "Out: {$currentOutText}";
                        } else {
                            $currentTimingDisplay = 'N/A';
                        }

                        if ($requestedInText && $requestedOutText) {
                            $requestedTimingDisplay = "{$requestedInText} - {$requestedOutText}";
                        } elseif ($requestedInText) {
                            $requestedTimingDisplay = "In: {$requestedInText}";
                        } elseif ($requestedOutText) {
                            $requestedTimingDisplay = "Out: {$requestedOutText}";
                        } else {
                            $requestedTimingDisplay = 'N/A';
                        }
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
                    <td class="text-center font-weight-bold text-muted">{{ $sNo }}</td>
                    <td>
                        <div class="att-emp">
                            <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                @if($photoUrl)
                                <img
                                    src="{{ $photoUrl }}"
                                    alt="{{ $empName }}"
                                    class="hrms-emp-avatar-img"
                                    onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');">
                                <span class="hrms-emp-avatar-fallback is-hidden">
                                    {{ $initials }}
                                </span>
                                @else
                                <span class="hrms-emp-avatar-fallback is-visible">
                                    {{ $initials }}
                                </span>
                                @endif
                            </span>
                            <div style="min-width: 0;">
                                <div class="att-emp-name" title="{{ $empName }}">
                                    {{ $empName }}
                                </div>
                                <div class="att-emp-code" title="{{ $empCode }}">
                                    {{ $empCode }}{{ $deptName ? " • {$deptName}" : '' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><strong>{{ \Carbon\Carbon::parse($rowDate)->format('d M Y') }}</strong></td>
                    <td>
                        <span class="orb-badge orb-badge-primary">
                            {{ $typeLabels[$row->request_type] ?? ucfirst(str_replace('_', ' ', $row->request_type)) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="text-muted font-weight-bold" style="font-size: 12px;">{{ $currentTimingDisplay }}</span>
                    </td>
                    <td class="text-center">
                        <span class="text-primary font-weight-bold" style="font-size: 12px;">{{ $requestedTimingDisplay }}</span>
                    </td>
                    <td>
                        <div class="text-truncate" style="max-width: 220px; font-size: 12px; font-weight: 600;" title="{{ $cleanReason }}">
                            <span>{{ $cleanReason }}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="orb-badge {{ $badge }}">
                            {{ ucfirst((string) $row->status) }}
                        </span>
                    </td>
                    <td class="small" style="white-space: nowrap;">
                        {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}
                    </td>
                    <td class="text-right no-export">
                        <div class="d-flex align-items-center justify-content-end" style="gap: 6px;">
                            <button type="button" class="att-action-btn att-action-view" data-toggle="modal" data-target="#viewModal{{ $row->id }}" title="View Details">
                                <i class="fas fa-eye"></i> View
                            </button>

                            @if($row->status === 'pending' && (!empty($canApproveThisRow) || !empty($canRejectThisRow) || !empty($canEdit) || !empty($canDelete)))
                            <div class="dropdown">
                                <button class="orb-action-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" style="border-radius: 12px; padding: 6px;">
                                    @if(!empty($canApproveThisRow))
                                        <button type="button" class="dropdown-item py-2 font-weight-bold text-success" data-toggle="modal" data-target="#approveModal{{ $row->id }}" style="border-radius: 8px;">
                                            <i class="fas fa-check-circle mr-2"></i> Approve Request
                                        </button>
                                    @endif
                                    @if(!empty($canRejectThisRow))
                                        <button type="button" class="dropdown-item py-2 font-weight-bold text-danger" data-toggle="modal" data-target="#rejectModal{{ $row->id }}" style="border-radius: 8px;">
                                            <i class="fas fa-times-circle mr-2"></i> Reject Request
                                        </button>
                                    @endif
                                    @if(!empty($canEdit))
                                        <button type="button" class="dropdown-item py-2 font-weight-bold text-primary" data-toggle="modal" data-target="#editModal{{ $row->id }}" style="border-radius: 8px;">
                                            <i class="fas fa-edit mr-2"></i> Edit Request
                                        </button>
                                    @endif
                                    @if(!empty($canDelete))
                                        <form method="POST" action="{{ route($deleteRoute, $row->id) }}" onsubmit="return confirm('Delete this record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item py-2 font-weight-bold text-danger" type="submit" style="border-radius: 8px;">
                                                <i class="fas fa-trash-alt mr-2"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($rows, 'links'))
    <div class="border-top bg-white" style="border-bottom-left-radius:18px; border-bottom-right-radius:18px;">
        {{ $rows->appends(request()->query())->links('vendor.pagination.orbo') }}
    </div>
@endif
