@foreach($rows as $row)
    @php
        $photoUrl = resolveEmployeePassportPhoto($row->employee_id);
        $initials = resolveEmployeeInitials($row->employee_id);
        $deptName = $row->department_name ?? 'N/A';
        $desigName = $row->designation_name ?? 'N/A';
        $currentStatusText = $row->current_attendance_type_name ?? ($row->current_attendance_status ? ucwords(str_replace('_', ' ', $row->current_attendance_status)) : 'N/A');
        $shiftName = 'Regular Shift';
        $isOwnRow = $ownEmpId && ((int)$row->employee_id === (int)$ownEmpId);
        $canApproveThisRow = $canApproveGlobal && !$isOwnRow;
        $canRejectThisRow = $canRejectGlobal && !$isOwnRow;
    @endphp

    {{-- View Details Modal --}}
    @include('hrms.attendance.regularizations.partials.modals.view')

    {{-- Approve Request Modal --}}
    @include('hrms.attendance.regularizations.partials.modals.approve')

    {{-- Reject Request Modal --}}
    @include('hrms.attendance.regularizations.partials.modals.reject')

    {{-- Edit Request Modal --}}
    @include('hrms.attendance.regularizations.partials.modals.edit')
@endforeach
