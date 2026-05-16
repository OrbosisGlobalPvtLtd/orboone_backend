@extends('layouts.panel')

@section('_head')
<style>.orb-title{font-size:26px;font-weight:800;margin:0}.orb-card{background:#fff;border:1px solid #E7EAF3;border-radius:8px;box-shadow:0 14px 35px rgba(16,24,40,.07)}.orb-card-body{padding:18px}.orb-btn{background:#4B00E8;color:#fff;border:0;border-radius:8px;padding:8px 12px;font-weight:700}.orb-table th{font-size:12px;color:#667085;text-transform:uppercase;border-top:0}</style>
@endsection

@section('_content')
<div><div class="d-flex justify-content-between mb-3"><h1 class="orb-title">Holidays</h1><button class="orb-btn" data-toggle="modal" data-target="#holidayModal">Add Holiday</button></div>@include('hrms.leave.shared.flash')<div class="orb-card"><div class="orb-card-body table-responsive"><table class="table orb-table js-datatable"><thead><tr><th>Date</th><th>Title</th><th>Type</th><th>Override</th><th></th></tr></thead><tbody>@foreach($holidays as $holiday)<tr><td>{{ optional($holiday->holiday_date)->format('d M Y') }}</td><td>{{ $holiday->title }}</td><td>{{ $holiday->holiday_type }}</td><td>{{ $holiday->is_working_day_override ? 'Working' : 'Off' }}</td><td><form method="POST" action="{{ route('hrms.holidays.destroy', $holiday->id) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-light border">Delete</button></form></td></tr>@endforeach</tbody></table>{{ method_exists($holidays, 'links') ? $holidays->links() : '' }}</div></div><div class="modal fade" id="holidayModal"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="{{ route('hrms.holidays.store') }}">@csrf<div class="modal-header"><h5 class="modal-title">Holiday</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><input name="title" class="form-control mb-2" placeholder="Title" required><input type="date" name="holiday_date" class="form-control mb-2" required><input name="holiday_type" class="form-control mb-2" value="company"><label><input type="checkbox" name="is_working_day_override" value="1"> Working day override</label><br><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div><div class="modal-footer"><button class="orb-btn">Save</button></div></form></div></div></div></div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
@endsection
