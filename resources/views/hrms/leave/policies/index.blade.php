@extends('layouts.panel')

@section('_head')
<style>.orb-title{font-size:26px;font-weight:800;margin:0}.orb-muted{color:#667085}.orb-card{background:#fff;border:1px solid #E7EAF3;border-radius:8px;box-shadow:0 14px 35px rgba(16,24,40,.07)}.orb-card-body{padding:18px}.orb-btn{background:#4B00E8;color:#fff;border:0;border-radius:8px;padding:8px 12px;font-weight:700}.orb-table th{font-size:12px;color:#667085;text-transform:uppercase;border-top:0}</style>
@endsection

@section('_content')
<div>
    <div class="d-flex justify-content-between align-items-center mb-3"><div><h1 class="orb-title">Leave Policies</h1><div class="orb-muted">All leave values are read from active DB policies.</div></div><button class="orb-btn" data-toggle="modal" data-target="#policyModal">Add Policy</button></div>
    @include('hrms.leave.shared.flash')
    <div class="orb-card"><div class="orb-card-body table-responsive"><table class="table orb-table js-datatable"><thead><tr><th>Name</th><th>Annual</th><th>Monthly</th><th>Sandwich</th><th>Nov/Dec</th><th>Active</th></tr></thead><tbody>
    @foreach($policies as $policy)<tr><td>{{ $policy->policy_name }}</td><td>{{ $policy->annual_total_leaves }} total, {{ $policy->annual_paid_leaves }} paid, {{ $policy->annual_sick_leaves }} sick</td><td>{{ $policy->monthly_leave_limit }}</td><td>{{ $policy->sandwich_enabled ? 'Enabled' : 'Disabled' }}</td><td>{{ $policy->nov_dec_threshold_balance }} / {{ $policy->nov_dec_usage_percentage }}%</td><td>{{ $policy->is_active ? 'Yes' : 'No' }}</td></tr>@endforeach
    </tbody></table></div></div>
    <div class="modal fade" id="policyModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><form method="POST" action="{{ route('hrms.leave.policies.store') }}">@csrf<div class="modal-header"><h5 class="modal-title">Leave Policy</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row">@foreach(['policy_name'=>'Policy Name','annual_total_leaves'=>'Annual Total','annual_paid_leaves'=>'Annual Paid','annual_sick_leaves'=>'Annual Sick','monthly_leave_limit'=>'Monthly Limit','max_leave_at_once'=>'Max At Once','probation_leave_limit'=>'Probation Limit','internship_leave_limit'=>'Internship Limit','medical_certificate_after_days'=>'Medical Certificate After','nov_dec_threshold_balance'=>'Nov/Dec Threshold','nov_dec_usage_percentage'=>'Nov/Dec Usage %'] as $field=>$label)<div class="col-md-3 mb-2"><label>{{ $label }}</label><input class="form-control" name="{{ $field }}" value="{{ $field === 'policy_name' ? '' : 0 }}" required></div>@endforeach<div class="col-md-3 mb-2"><label>Rounding</label><select class="form-control" name="rounding_method"><option value="nearest">Nearest</option><option value="floor">Floor</option><option value="ceil">Ceil</option></select></div><div class="col-12">@foreach(['allow_monthly_balance_accumulation'=>'Monthly Accumulation','carry_forward_enabled'=>'Carry Forward','sandwich_enabled'=>'Sandwich','weekoff_included_in_sandwich'=>'Weekoff Sandwich','holiday_included_in_sandwich'=>'Holiday Sandwich','nov_dec_half_usage_enabled'=>'Nov/Dec Cap','comp_off_expiry_same_month'=>'Comp Off Same Month','is_active'=>'Active'] as $field=>$label)<label class="mr-3"><input type="checkbox" name="{{ $field }}" value="1" {{ !in_array($field, ['carry_forward_enabled'], true) ? 'checked' : '' }}> {{ $label }}</label>@endforeach</div></div></div><div class="modal-footer"><button class="orb-btn">Save</button></div></form></div></div></div>
</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
@endsection
