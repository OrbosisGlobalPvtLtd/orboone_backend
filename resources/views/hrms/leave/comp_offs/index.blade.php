@extends('layouts.panel')

@section('_head')
<style>.orb-title{font-size:26px;font-weight:800;margin:0}.orb-muted{color:#667085}.orb-card{background:#fff;border:1px solid #E7EAF3;border-radius:8px;box-shadow:0 14px 35px rgba(16,24,40,.07)}.orb-card-body{padding:18px}.orb-btn{background:#4B00E8;color:#fff;border:0;border-radius:8px;padding:8px 12px;font-weight:700}.orb-table th{font-size:12px;color:#667085;text-transform:uppercase;border-top:0}</style>
@endsection

@section('_content')
<div><div class="d-flex justify-content-between mb-3"><div><h1 class="orb-title">Comp Off</h1><div class="orb-muted">Approved holiday/weekoff work creates expiring comp off balance.</div></div><form method="POST" action="{{ route('hrms.comp_offs.expire') }}">@csrf<button class="orb-btn">Expire Due</button></form></div>@include('hrms.leave.shared.flash')<div class="orb-card mb-3"><div class="orb-card-body"><h5>Pending Holiday Work</h5>@forelse($holidayWorkRequests as $request)<form method="POST" action="{{ route('hrms.comp_offs.holiday_work.approve', $request->id) }}" class="d-inline-block mr-2 mb-2">@csrf<button class="btn btn-sm btn-success">{{ optional($request->employee)->display_name }} - {{ optional($request->worked_date)->format('d M Y') }}</button></form>@empty<span class="orb-muted">No pending holiday work requests.</span>@endforelse</div></div><div class="orb-card"><div class="orb-card-body table-responsive"><table class="table orb-table js-datatable"><thead><tr><th>Employee</th><th>Worked Date</th><th>Earned</th><th>Expiry</th><th>Status</th></tr></thead><tbody>@foreach($compOffs as $compOff)<tr><td>{{ optional($compOff->employee)->display_name }}</td><td>{{ optional($compOff->worked_date)->format('d M Y') }}</td><td>{{ $compOff->earned_days }}</td><td>{{ optional($compOff->expiry_date)->format('d M Y') }}</td><td>{{ ucfirst($compOff->status) }}</td></tr>@endforeach</tbody></table>{{ method_exists($compOffs, 'links') ? $compOffs->links() : '' }}</div></div></div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
@endsection
