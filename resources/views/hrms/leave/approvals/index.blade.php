@extends('layouts.panel')

@section('_head')
<style>
    .orb-title{font-size:26px;font-weight:800;margin:0}.orb-muted{color:#667085}.orb-card{background:#fff;border:1px solid #E7EAF3;border-radius:8px;box-shadow:0 14px 35px rgba(16,24,40,.07)}.orb-card-body{padding:18px}.orb-btn{background:#4B00E8;color:#fff;border:0;border-radius:8px;padding:8px 12px;font-weight:700}.orb-table th{font-size:12px;color:#667085;text-transform:uppercase;border-top:0}.orb-pill{border-radius:999px;padding:5px 10px;font-size:12px;font-weight:700}.pending{background:#FFF7E6;color:#B54708}.approved{background:#ECFDF3;color:#027A48}.rejected,.cancelled{background:#FEF3F2;color:#B42318}
</style>
@endsection

@section('_content')
<div>
    <h1 class="orb-title">Leave Approvals</h1>
    <div class="orb-muted mb-3">Approval deducts balances, logs changes, syncs attendance, and records LWP payroll impacts.</div>
    @include('hrms.leave.shared.flash')
    <div class="orb-card mb-3"><div class="orb-card-body">
        <form class="row">
            <div class="col-md-3 mb-2"><select name="status" class="form-control"><option value="">All Status</option>@foreach(['pending','approved','rejected','cancelled'] as $status)<option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="employee_id" class="form-control"><option value="">All Employees</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->user_name ?? $employee->display_name }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="leave_type_id" class="form-control"><option value="">All Types</option>@foreach($leaveTypes as $type)<option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><button class="orb-btn" type="submit">Filter</button></div>
        </form>
    </div></div>
    <div class="orb-card"><div class="orb-card-body table-responsive">
        <table class="table orb-table js-datatable">
            <thead><tr><th>#</th><th>Employee</th><th>Type</th><th>Period</th><th>Days</th><th>Split</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($requests as $request)
                <tr>
                    <td>{{ $request->id }}</td>
                    <td>{{ optional($request->employee)->display_name }}</td>
                    <td>{{ optional($request->leaveType)->name }}</td>
                    <td>{{ optional($request->start_date)->format('d M') }} - {{ optional($request->end_date)->format('d M Y') }}</td>
                    <td>{{ $request->deducted_days }}</td>
                    <td class="small">P {{ $request->paid_days }} / S {{ $request->sick_days }} / C {{ $request->comp_off_days }} / LWP {{ $request->lwp_days }}</td>
                    <td><span class="orb-pill {{ $request->status }}">{{ ucfirst($request->status) }}</span></td>
                    <td>
                        @if($request->status === 'pending')
                            <form method="POST" action="{{ route('leave-approvals.approve', $request->id) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                            <form method="POST" action="{{ route('leave-approvals.reject', $request->id) }}" class="d-inline">@csrf<input type="hidden" name="reason" value="Rejected from approval list"><button class="btn btn-sm btn-danger">Reject</button></form>
                        @else
                            <span class="orb-muted small">Processed</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center orb-muted py-4">No leave requests found.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ method_exists($requests, 'links') ? $requests->links() : '' }}
    </div></div>
</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
@endsection
