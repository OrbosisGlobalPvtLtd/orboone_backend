@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container">
    <h2>Full & Final Settlement</h2>

    @if(!$fnf)
        <div class="alert alert-info mt-3">
            No F&F details available. If you have resigned, please wait for HR processing.
        </div>
    @else
        <p><strong>Last Working Day:</strong> {{ $fnf->last_working_day }}</p>

        <table class="table table-bordered mt-3">
            <tr><th>Pending Salary</th><td>{{ $fnf->pending_salary }}</td></tr>
            <tr><th>Leave Encashment</th><td>{{ $fnf->leave_encashment }}</td></tr>
            <tr><th>Reimbursements</th><td>{{ $fnf->reimbursements }}</td></tr>
            <tr><th>Deductions</th><td>{{ $fnf->deductions }}</td></tr>
            <tr><th>Net Payable</th><td><strong>{{ $fnf->net_payable }}</strong></td></tr>
        </table>
    @endif
</div>
@endsection
