@php $renderedModalIds = []; @endphp
@foreach($attendanceRows as $attendance)
@php $renderedModalIds[] = $attendance->id; @endphp
@if($canManageAttendance ?? false)
@include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
@endif
@include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
@endforeach

@foreach($blockedRows as $attendance)
@if(!in_array($attendance->id, $renderedModalIds))
@if($canManageAttendance ?? false)
@include('hrms.attendance.partials.edit-modal', ['attendance' => $attendance])
@endif
@include('hrms.attendance.partials.unlock-modal', ['attendance' => $attendance])
@endif
@endforeach
