@extends('layouts.panel')

@section('_head')
<style>.orb-title{font-size:26px;font-weight:800;margin:0}.orb-muted{color:#667085}.orb-card{background:#fff;border:1px solid #E7EAF3;border-radius:8px;box-shadow:0 14px 35px rgba(16,24,40,.07)}.orb-card-body{padding:18px}.orb-btn{background:#4B00E8;color:#fff;border:0;border-radius:8px;padding:8px 12px;font-weight:700}.orb-table th{font-size:12px;color:#667085;text-transform:uppercase;border-top:0}</style>
@endsection

@section('_content')
<div>
    <h1 class="orb-title">Leave Balance</h1>
    <div class="orb-muted mb-3">Employee-wise earned, used, remaining, and LWP balances.</div>
    <div class="orb-card mb-3"><div class="orb-card-body"><form class="row"><div class="col-md-3"><input name="year" class="form-control" value="{{ $year }}"></div><div class="col-md-5"><select name="employee_id" class="form-control"><option value="">All Employees</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->user_name ?? $employee->display_name }}</option>@endforeach</select></div><div class="col-md-2"><button class="orb-btn">Filter</button></div></form></div></div>
    <div class="orb-card"><div class="orb-card-body table-responsive"><table class="table orb-table js-datatable"><thead><tr><th>Employee</th><th>Year</th><th>Total</th><th>Paid</th><th>Sick</th><th>Comp Off</th><th>LWP</th></tr></thead><tbody>
    @foreach($balances as $balance)<tr><td>{{ optional($balance->employee)->display_name }}</td><td>{{ $balance->year }}</td><td>{{ $balance->total_remaining }} / {{ $balance->total_allocated }}</td><td>{{ $balance->paid_remaining }}</td><td>{{ $balance->sick_remaining }}</td><td>{{ $balance->comp_off_remaining }}</td><td>{{ $balance->lwp_used }}</td></tr>@endforeach
    </tbody></table>{{ method_exists($balances, 'links') ? $balances->links() : '' }}</div></div>
</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
@endsection
