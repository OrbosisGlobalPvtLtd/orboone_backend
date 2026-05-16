@extends('layouts.panel')

@section('_head')
<style>
    .orb-title{font-size:26px;font-weight:800;margin:0}.orb-muted{color:#667085}.orb-card{background:#fff;border:1px solid #E7EAF3;border-radius:8px;box-shadow:0 14px 35px rgba(16,24,40,.07)}.orb-card-body{padding:18px}.orb-table th{font-size:12px;color:#667085;text-transform:uppercase;border-top:0}.orb-stat{font-size:30px;font-weight:800;color:#4B00E8}
</style>
@endsection

@section('_content')
<div>
    <h1 class="orb-title">Leave Dashboard</h1>
    <div class="orb-muted mb-3">Operational view of leave volume and payroll-sensitive leave.</div>
    <div class="row mb-3">
        @foreach($stats as $label => $value)
            <div class="col-md-3 mb-3"><div class="orb-card"><div class="orb-card-body"><div class="orb-muted">{{ ucwords(str_replace('_', ' ', $label)) }}</div><div class="orb-stat">{{ $value }}</div></div></div></div>
        @endforeach
    </div>
    <div class="orb-card"><div class="orb-card-body table-responsive">
        <table class="table orb-table js-datatable"><thead><tr><th>#</th><th>Employee</th><th>Type</th><th>Dates</th><th>Status</th><th>LWP</th></tr></thead><tbody>
        @foreach($recentRequests as $request)
            <tr><td>{{ $request->id }}</td><td>{{ optional($request->employee)->display_name }}</td><td>{{ optional($request->leaveType)->name }}</td><td>{{ optional($request->start_date)->format('d M') }} - {{ optional($request->end_date)->format('d M Y') }}</td><td>{{ ucfirst($request->status) }}</td><td>{{ $request->lwp_days }}</td></tr>
        @endforeach
        </tbody></table>
    </div></div>
</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
@endsection
