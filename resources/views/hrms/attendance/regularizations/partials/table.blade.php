<div class="att-table-wrap">
    <div class="att-table-responsive">
        <table class="att-table table table-hover js-orb-datatable" id="regularizationDataTable" style="width:100% !important; min-width: 1350px;">
            <thead>
                <tr>
                    <th rowspan="2" style="vertical-align: middle !important; width: 50px; padding: 4px 6px !important;">S.No.</th>
                    <th rowspan="2" style="vertical-align: middle !important; width: 220px; padding: 4px 6px !important;">Employee</th>
                    <th rowspan="2" style="vertical-align: middle !important; width: 90px; padding: 4px 6px !important;">Code</th>
                    <th rowspan="2" style="vertical-align: middle !important; width: 95px; padding: 4px 6px !important;">Date</th>
                    <th rowspan="2" style="vertical-align: middle !important; width: 140px; padding: 4px 6px !important;">Request Type</th>
                    <th colspan="4" class="text-center no-sort" style="background: rgba(75, 0, 232, 0.08) !important; color: #4B00E8 !important; font-weight: 800; font-size: 11px; border-bottom: 1.5px solid var(--orb-primary); letter-spacing: 0.5px; padding: 3px 6px !important;">PUNCH TIME</th>
                    <th rowspan="2" style="vertical-align: middle !important; width: 160px; padding: 4px 6px !important;">Reason</th>
                    <th rowspan="2" style="vertical-align: middle !important; width: 100px; padding: 4px 6px !important;">Status</th>
                    <th rowspan="2" style="vertical-align: middle !important; width: 120px; padding: 4px 6px !important;">Submitted At</th>
                    <th rowspan="2" style="vertical-align: middle !important; width: 80px; padding: 4px 6px !important;" class="text-right no-export">Action</th>
                </tr>
                <tr>
                    <th style="width: 95px; background: rgba(75, 0, 232, 0.03) !important; color: #4B00E8 !important; font-size: 10px; font-weight: 800; padding: 3px 6px !important;" class="text-center">Current In</th>
                    <th style="width: 95px; background: rgba(75, 0, 232, 0.03) !important; color: #4B00E8 !important; font-size: 10px; font-weight: 800; padding: 3px 6px !important;" class="text-center">Current Out</th>
                    <th style="width: 95px; background: rgba(75, 0, 232, 0.06) !important; color: #4B00E8 !important; font-size: 10px; font-weight: 800; padding: 3px 6px !important;" class="text-center">Req. In</th>
                    <th style="width: 95px; background: rgba(75, 0, 232, 0.06) !important; color: #4B00E8 !important; font-size: 10px; font-weight: 800; padding: 3px 6px !important;" class="text-center">Req. Out</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    @php
                        $photoUrl = resolveEmployeePassportPhoto($row->employee_id);
                        $initials = resolveEmployeeInitials($row->employee_id);
                        $isOwnRow = $ownEmpId && ((int)$row->employee_id === (int)$ownEmpId);
                        $canApproveThisRow = $canApproveGlobal && !$isOwnRow;
                        $canRejectThisRow = $canRejectGlobal && !$isOwnRow;

                        $deptName = $row->department_name ?? 'N/A';
                        $desigName = $row->designation_name ?? 'N/A';
                        $currentStatusText = $row->current_attendance_type_name ?? ($row->current_attendance_status ? ucwords(str_replace('_', ' ', $row->current_attendance_status)) : 'N/A');
                        
                        $type = $row->request_type;
                        
                        $currentInText = $row->existing_punch_in ? \Carbon\Carbon::parse($row->existing_punch_in)->format('h:i A') : 'N/A';
                        $currentOutText = $row->existing_punch_out ? \Carbon\Carbon::parse($row->existing_punch_out)->format('h:i A') : 'N/A';
                        $requestedInText = $row->requested_punch_in ? \Carbon\Carbon::parse($row->requested_punch_in)->format('h:i A') : 'N/A';
                        $requestedOutText = $row->requested_punch_out ? \Carbon\Carbon::parse($row->requested_punch_out)->format('h:i A') : 'N/A';
                        
                        if ($type === 'other' && str_contains($row->reason, '[Attendance Status Correction:')) {
                            $currentInText = $currentStatusText;
                            preg_match('/\[Attendance Status Correction:\s*([^\]]+)\]/', $row->reason, $matches);
                            $requestedInText = $matches[1] ?? 'Correction';
                        }
                    @endphp
                    <tr>
                        <td style="white-space: nowrap;"><strong>{{ method_exists($rows, 'currentPage') ? (($rows->currentPage() - 1) * $rows->perPage()) + $loop->iteration : $loop->iteration }}</strong></td>
                        <td style="white-space: nowrap;">
                            <div class="d-flex align-items-center">
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}" class="avatar-table rounded-circle" alt="">
                                @else
                                    <div class="avatar-table rounded-circle d-inline-flex align-items-center justify-content-center" style="background: var(--orb-soft); color: var(--orb-primary); font-weight: 900; font-size: 14px;">
                                        {{ $initials }}
                                    </div>
                                @endif
                                <div class="ml-3">
                                    <div class="font-weight-bold text-dark">{{ $row->employee_display_name ?? $row->name }}</div>
                                    <div class="text-muted small">{{ $deptName }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="white-space: nowrap;"><code>{{ $row->employee_code }}</code></td>
                        <td style="white-space: nowrap;">
                            @php
                                $rowDate = $row->mapped_attendance_date ?: ($row->requested_punch_in ?: ($row->requested_punch_out ?: $row->created_at));
                            @endphp
                            <span class="font-weight-bold">{{ \Carbon\Carbon::parse($rowDate)->format('d M Y') }}</span>
                        </td>
                        <td style="white-space: nowrap;">
                            <span class="orb-badge orb-badge-primary">
                                {{ $typeLabels[$row->request_type] ?? ucfirst(str_replace('_', ' ', $row->request_type)) }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;" class="text-center"><span class="font-weight-bold">{{ $currentInText }}</span></td>
                        <td style="white-space: nowrap;" class="text-center"><span class="font-weight-bold">{{ $currentOutText }}</span></td>
                        <td style="white-space: nowrap;" class="text-center"><span class="text-primary font-weight-bold">{{ $requestedInText }}</span></td>
                        <td style="white-space: nowrap;" class="text-center"><span class="text-primary font-weight-bold">{{ $requestedOutText }}</span></td>
                        <td style="max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <span title="{{ $row->reason }}" data-toggle="tooltip" style="cursor: help;">
                                {{ Str::limit(str_replace(['[Attendance Status Correction: Present]', '[Attendance Status Correction: Half Day]', '[Attendance Status Correction: Absent]', '[Attendance Status Correction: present]', '[Attendance Status Correction: half_day]', '[Attendance Status Correction: absent]'], '', $row->reason), 35) }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            @php
                                $badge = $row->status === 'approved'
                                    ? 'orb-badge-success'
                                    : ($row->status === 'pending'
                                        ? 'orb-badge-warning'
                                        : ($row->status === 'rejected'
                                            ? 'orb-badge-danger'
                                            : 'orb-badge-secondary'));
                            @endphp
                            <span class="orb-badge {{ $badge }}">
                                {{ ucfirst((string) $row->status) }}
                            </span>
                        </td>
                        <td class="small">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y h:i A') }}</td>
                        <td class="text-right no-export">
                            <div class="dropdown">
                                <button class="orb-action-btn" type="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <button type="button" class="dropdown-item" data-toggle="modal" data-target="#viewModal{{ $row->id }}">
                                        <i class="fas fa-eye mr-2 text-info"></i> View Details
                                    </button>

                                    @if($row->status === 'pending')
                                        @if(!empty($canApproveThisRow))
                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#approveModal{{ $row->id }}">
                                                <i class="fas fa-check mr-2 text-success"></i> Approve
                                            </button>
                                        @endif
                                        @if(!empty($canRejectThisRow))
                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#rejectModal{{ $row->id }}">
                                                <i class="fas fa-times mr-2 text-danger"></i> Reject
                                            </button>
                                        @endif
                                        @if(!empty($canEdit))
                                            <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $row->id }}">
                                                <i class="fas fa-edit mr-2 text-primary"></i> Edit
                                            </button>
                                        @endif
                                    @endif

                                    @if(!empty($canDelete) && $row->status === 'pending')
                                        <form method="POST" action="{{ route($deleteRoute, $row->id) }}" onsubmit="return confirm('Delete this record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item text-danger" type="submit">
                                                <i class="fas fa-trash mr-2"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($rows, 'links'))
    <div class="mt-3 px-3 pb-3">
        {{ $rows->appends(request()->query())->links('vendor.pagination.orbo') }}
    </div>
@endif
