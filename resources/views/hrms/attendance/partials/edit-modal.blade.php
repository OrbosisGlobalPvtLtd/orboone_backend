<div class="modal fade" id="editModal{{ $attendance->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('attendances.update') }}" class="modal-content">
            @csrf
            @method('PUT')

            <input type="hidden" name="id" value="{{ $attendance->id }}">

            <div class="modal-header">
                <h5 class="modal-title">Update Attendance</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <label>Status</label>
                <select name="attendance_type_id" class="form-control mb-3" required>
                    @foreach($attendanceTypes as $type)
                        <option value="{{ $type->id }}" {{ $attendance->attendance_type_id == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>

                <label>Date</label>
                <input type="date" name="attendance_date" class="form-control mb-3" value="{{ optional($attendance->attendance_date)->format('Y-m-d') }}">

                <label>Punch In</label>
                <input type="time" name="punch_in_time" class="form-control mb-3" value="{{ $attendance->punch_in_time }}">

                <label>Punch Out</label>
                <input type="time" name="punch_out_time" class="form-control mb-3" value="{{ $attendance->punch_out_time }}">

                <label>Work Mode</label>
                <select name="work_mode" class="form-control mb-3">
                    <option value="">None</option>
                    <option value="wfo" {{ $attendance->work_mode == 'wfo' ? 'selected' : '' }}>Work From Office</option>
                    <option value="wfh" {{ $attendance->work_mode == 'wfh' ? 'selected' : '' }}>Work From Home</option>
                </select>

                <label>Note</label>
                <textarea name="note" class="form-control mb-3" rows="3">{{ $attendance->punch_out_note }}</textarea>

                <label>HR Approval Note</label>
                <textarea name="hr_approval_note" class="form-control" rows="3">{{ $attendance->hr_approval_note }}</textarea>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Update Attendance</button>
            </div>
        </form>
    </div>
</div>