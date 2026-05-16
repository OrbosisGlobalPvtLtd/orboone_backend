@extends('layouts.panel')

@section('_head')
<style>.orb-title{font-size:26px;font-weight:800;margin:0}.orb-muted{color:#667085}.orb-card{background:#fff;border:1px solid #E7EAF3;border-radius:8px;box-shadow:0 14px 35px rgba(16,24,40,.07)}.orb-card-body{padding:18px}.orb-btn{background:#4B00E8;color:#fff;border:0;border-radius:8px;padding:8px 12px;font-weight:700}.orb-table th{font-size:12px;color:#667085;text-transform:uppercase;border-top:0}</style>
@endsection

@section('_content')
<div>
    <div class="d-flex justify-content-between align-items-center mb-3"><div><h1 class="orb-title">Leave Types</h1><div class="orb-muted">Configure paid, sick, LWP, and comp-off categories.</div></div><button class="orb-btn" data-toggle="modal" data-target="#typeModal">Add Type</button></div>
    @include('hrms.leave.shared.flash')
    <div class="orb-card"><div class="orb-card-body table-responsive"><table class="table orb-table js-datatable"><thead><tr><th>Name</th><th>Code</th><th>Flags</th><th>Limits</th><th>Active</th></tr></thead><tbody>
    @foreach($types as $type)<tr><td>{{ $type->name }}</td><td>{{ $type->code }}</td><td class="small">@if($type->is_paid) Paid @endif @if($type->is_sick) Sick @endif @if($type->is_lwp) LWP @endif @if($type->is_comp_off) Comp Off @endif</td><td>{{ $type->max_days_per_month ?? '-' }} / {{ $type->max_days_per_request ?? '-' }}</td><td>{{ $type->is_active ? 'Yes' : 'No' }}</td></tr>@endforeach
    </tbody></table></div></div>
    <div class="modal fade" id="typeModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('hrms.leave.types.store') }}">@csrf<div class="modal-header"><h5 class="modal-title">Leave Type</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row"><div class="col-md-6 mb-2"><input class="form-control" name="name" placeholder="Name" required></div><div class="col-md-6 mb-2"><input class="form-control" name="code" placeholder="code" required></div><div class="col-md-4 mb-2"><input class="form-control" name="max_days_per_month" placeholder="Monthly limit"></div><div class="col-md-4 mb-2"><input class="form-control" name="max_days_per_request" placeholder="Request limit"></div><div class="col-md-4 mb-2"><input class="form-control" name="color" placeholder="#4B00E8"></div><div class="col-12">@foreach(['is_paid'=>'Paid','is_sick'=>'Sick','is_lwp'=>'LWP','is_comp_off'=>'Comp Off','requires_attachment'=>'Attachment','allow_half_day'=>'Half Day','applicable_after_confirmation'=>'After Confirmation','is_active'=>'Active'] as $field=>$label)<label class="mr-3"><input type="checkbox" name="{{ $field }}" value="1" {{ in_array($field, ['allow_half_day','is_active'], true) ? 'checked' : '' }}> {{ $label }}</label>@endforeach</div></div></div><div class="modal-footer"><button class="orb-btn">Save</button></div></form></div></div></div>
</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
@endsection
