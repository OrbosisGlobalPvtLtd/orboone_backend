@extends('layouts.panel', ['active' => 'attendances'])

@section('page_title', 'Daily Attendance Report')

@section('_head')
<!-- Datatable CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endsection

@section('_content')

<div class="row">
    <div class="col-12">
        
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Daily Attendance Records</h3>
                <div class="ml-auto">
                    <a href="{{ route('attendances.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
            
            <div class="card-body bg-light border-bottom">
                <form method="GET" action="{{ route('attendances.daily') }}" id="attendanceFilterForm">
                    <div class="row">
                        <div class="col-md-2 form-group mb-0">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control select2-searchable">
                                <option value="">All Staff</option>
                                @foreach($employees as $emp)
                                <option value="{{ optional($emp->employee)->id }}"
                                    {{ request('employee_id') == optional($emp->employee)->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 form-group mb-0">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>

                        <div class="col-md-2 form-group mb-0">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>

                        <div class="col-md-2 form-group mb-0">
                            <label>Status</label>
                            <select name="attendance_type_id" class="form-control">
                                <option value="">All Status</option>
                                @foreach($attendanceTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ request('attendance_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 form-group mb-0">
                            <label>Work Mode</label>
                            <select name="work_mode" class="form-control">
                                <option value="">All</option>
                                <option value="wfo" {{ strtolower(request('work_mode')) === 'wfo' ? 'selected' : '' }}>WFO</option>
                                <option value="wfh" {{ strtolower(request('work_mode')) === 'wfh' ? 'selected' : '' }}>WFH</option>
                            </select>
                        </div>

                        <div class="col-md-2 form-group mb-0 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('attendances.daily') }}" class="btn btn-default ml-2" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover" id="attendanceRecordsTable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Shift</th>
                            <th>Punch In</th>
                            <th>Punch Out</th>
                            <th>Gross Work</th>
                            <th>Net Work</th>
                            <th>Status</th>
                            <th>Flags</th>
                            <th class="text-right pr-4 no-export">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        @php
                        $attStatus = strtolower((string) ($attendance->attendance_status ?? ''));
                        $punchInTime = $attendance->punch_in_time;
                        $isAdminUnlocked = (bool) ($attendance->is_admin_unlocked ?? false);
                        
                        if (in_array($attStatus, ['unlocked', 'awaiting_punch_in'], true) || ($isAdminUnlocked && is_null($punchInTime))) {
                            $typeCode = 'warning';
                            $statusName = 'Awaiting Punch In';
                        } elseif ($attStatus === 'punch_blocked' || ($attendance->is_punch_blocked ?? false) || ($attendance->is_blocked ?? false)) {
                            $typeCode = 'danger';
                            $statusName = 'Punch Blocked';
                        } elseif ($attStatus === 'half_day' || ($attendance->is_half_day ?? false)) {
                            $typeCode = 'info';
                            $statusName = 'Half Day';
                        } elseif ($attStatus === 'absent' || $attStatus === 'lwp' || ($attendance->is_lwp ?? false)) {
                            $typeCode = 'danger';
                            $statusName = 'ABSENT';
                        } elseif ($attStatus === 'present' || ! is_null($punchInTime)) {
                            $typeCode = 'success';
                            $statusName = 'Present';
                        } else {
                            $typeCode = 'secondary';
                            $statusName = optional($attendance->attendanceType)->name ?? 'Pending';
                        }
                        $modeCode = strtoupper($attendance->work_mode ?? '-');
                        @endphp

                        <tr>
                            <td>
                                <div><strong>{{ optional($attendance->user)->name ?? 'N/A' }}</strong></div>
                                <small class="text-muted">{{ optional($attendance->employee)->employee_code ?? 'N/A' }}</small>
                            </td>

                            <td>
                                {{ $attendance->attendance_date
                                    ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y')
                                    : '-' }}
                            </td>

                            <td>
                                <span class="badge badge-secondary">{{ $modeCode }}</span>
                            </td>

                            <td>
                                {{ optional($attendance->attendanceTime)->name ?? '-' }}
                            </td>

                            <td>
                                {{ $attendance->punch_in_time
                                    ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A')
                                    : '-' }}
                            </td>

                            <td>
                                {{ $attendance->punch_out_time
                                    ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A')
                                    : '-' }}
                            </td>

                            <td>
                                {{ $attendance->gross_duration ?? '-' }}
                            </td>

                            <td>
                                <strong>{{ $attendance->net_duration ?? '-' }}</strong>
                            </td>

                            <td>
                                <span class="badge badge-{{ $typeCode }}">
                                    {{ $statusName }}
                                </span>
                            </td>

                            <td>
                                @php
                                    $src = strtolower((string) ($attendance->attendance_source ?? 'mobile'));
                                @endphp
                                @if($src === 'web')
                                    <span class="badge badge-light border">Web</span>
                                @elseif($src === 'mobile')
                                    <span class="badge badge-light border">Mobile</span>
                                @elseif($src === 'admin')
                                    <span class="badge badge-light border">Admin</span>
                                @endif

                                @if($attendance->is_late)
                                <span class="badge badge-warning">Late</span>
                                @endif

                                @if($attendance->is_early_out)
                                <span class="badge badge-warning">Early Out</span>
                                @endif

                                @if($attendance->missed_punch)
                                <span class="badge badge-danger">Missed</span>
                                @endif
                            </td>

                            <td class="text-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        @if($canManageAttendance ?? false)
                                        <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $attendance->id }}">
                                            <i class="fas fa-edit text-primary"></i> Edit
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @foreach($attendances as $attendance)
        @if($canManageAttendance ?? false)
        @include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
        @endif
        @include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
        @endforeach

    </div>
</div>

@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(function() {
        var table = $('#attendanceRecordsTable').DataTable({
            pageLength: 25,
            ordering: true,
            responsive: false,
            autoWidth: false,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-right'B>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend: 'csvHtml5', text: '<i class="fas fa-file-csv"></i> CSV', className: 'btn btn-default btn-sm', exportOptions: { columns: ':not(.no-export)' } },
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-default btn-sm', exportOptions: { columns: ':not(.no-export)' } },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-default btn-sm', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: ':not(.no-export)' } },
                { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn btn-default btn-sm', exportOptions: { columns: ':not(.no-export)' } }
            ],
            language: {
                emptyTable: 'No records found.',
                zeroRecords: 'No matching records found.'
            }
        });

        });
    });
</script>
@endsection
