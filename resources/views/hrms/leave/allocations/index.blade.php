@extends('layouts.panel')

@section('_head')
<style>
    .orb-title{font-size:26px;font-weight:800;margin:0}.orb-muted{color:#667085}.orb-card{background:#fff;border:1px solid #E7EAF3;border-radius:8px;box-shadow:0 14px 35px rgba(16,24,40,.07)}.orb-card-body{padding:18px}.orb-btn{background:#4B00E8;color:#fff;border:0;border-radius:8px;padding:8px 12px;font-weight:700}.orb-table th{font-size:12px;color:#667085;text-transform:uppercase;border-top:0}
</style>
@endsection

@section('_content')
<div>
    <h1 class="orb-title">Leave Allocation</h1>
    <div class="orb-muted mb-3">Generate policy-driven yearly allocations.</div>
    @include('hrms.leave.shared.flash')
    @if($canManageAllocations ?? false)
    <div class="orb-card mb-3"><div class="orb-card-body">
        <form method="POST" action="{{ route('leave-allocations.process') }}" class="form-inline d-inline-block mr-2">@csrf<input name="year" class="form-control mr-2" value="{{ $year }}" style="width:110px"><button class="orb-btn">Generate Year</button></form>
        <form method="POST" action="{{ route('leave-allocations.single') }}" class="form-inline d-inline-block">@csrf<input name="year" class="form-control mr-2" value="{{ $year }}" style="width:110px"><select name="employee_id" class="form-control mr-2">@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->user_name ?? $employee->display_name }}</option>@endforeach</select><button class="btn btn-light border">Generate Single</button></form>
    </div></div>
    @endif
    <div class="orb-card"><div class="orb-card-body table-responsive">
        <table class="table orb-table js-datatable"><thead><tr><th>Employee</th><th>Stage</th><th>Policy</th><th>Allocated</th><th>Used</th><th>Remaining</th><th>LWP</th></tr></thead><tbody>
        @foreach($allocations as $allocation)
            <tr><td>{{ optional($allocation->employee)->display_name }}</td><td>{{ ucfirst($allocation->employment_stage) }}</td><td>{{ optional($allocation->policy)->policy_name }}</td><td>{{ $allocation->total_allocated }}</td><td>{{ $allocation->total_used }}</td><td>{{ $allocation->total_remaining }}</td><td>{{ $allocation->lwp_used }}</td></tr>
        @endforeach
        </tbody></table>{{ method_exists($allocations, 'links') ? $allocations->links() : '' }}
    </div></div>
</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
@endsection
