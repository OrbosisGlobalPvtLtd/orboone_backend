@extends('layouts.panel')

@section('_head')
<style>
    .orb-title{font-size:26px;font-weight:800;margin:0}.orb-muted{color:#667085}.orb-card{background:#fff;border:1px solid #E7EAF3;border-radius:8px;box-shadow:0 14px 35px rgba(16,24,40,.07)}.orb-card-body{padding:22px}.orb-btn{background:#4B00E8;color:#fff;border:0;border-radius:8px;padding:10px 14px;font-weight:700}.orb-btn:hover{background:#8600EE;color:#fff}
</style>
@endsection

@section('_content')
<div>
    <div class="mb-3">
        <h1 class="orb-title">Apply Leave</h1>
        <div class="orb-muted">Requests remain pending until HR/admin approval.</div>
    </div>

    @include('hrms.leave.shared.flash')

    <div class="orb-card">
        <div class="orb-card-body">
            <form method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Leave Type</label>
                        <select name="leave_type_id" class="form-control" required>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3"><label>Start Date</label><input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control" required></div>
                    <div class="col-md-4 mb-3"><label>End Date</label><input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control" required></div>
                    <div class="col-md-4 mb-3"><label>Half Day</label><select name="is_half_day" class="form-control"><option value="0">No</option><option value="1">Yes</option></select></div>
                    <div class="col-md-4 mb-3"><label>Half Day Type</label><select name="half_day_type" class="form-control"><option value="">Full day</option><option value="first_half">First half</option><option value="second_half">Second half</option></select></div>
                    <div class="col-md-4 mb-3"><label>Attachment</label><input type="file" name="attachment" class="form-control"></div>
                    <div class="col-12 mb-3"><label>Reason</label><textarea name="reason" rows="4" class="form-control" required>{{ old('reason') }}</textarea></div>
                </div>
                <button class="orb-btn" type="submit">Submit Request</button>
                <a href="{{ route('leave-requests.index') }}" class="btn btn-light border ml-2">Back</a>
            </form>
        </div>
    </div>
</div>
@endsection
