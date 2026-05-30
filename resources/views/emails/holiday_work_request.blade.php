@php
$status = strtolower($status ?? 'submitted');
$statusLabel = $status === 'approved' ? 'Approved' : ($status === 'rejected' ? 'Rejected' : 'Submitted');
@endphp

@extends('emails.layouts.enterprise')

@section('content')
<span class="badge">Holiday Work Request - {{ $statusLabel }}</span>
<h2 style="margin:0 0 8px;">{{ $subject ?? 'Work Request Update' }}</h2>
<table class="meta">
    <tr><td class="label">Employee Name</td><td class="value">{{ $employee_name }}</td></tr>
    <tr><td class="label">Employee Code</td><td class="value">{{ $employee_code }}</td></tr>
    <tr><td class="label">Department</td><td class="value">{{ $department }}</td></tr>
    <tr><td class="label">Work Date</td><td class="value">{{ $worked_date }}</td></tr>
    <tr><td class="label">Work Type</td><td class="value">{{ $work_type }}</td></tr>
    <tr><td class="label">Work Mode</td><td class="value">{{ $work_mode }}</td></tr>
    <tr><td class="label">Reason</td><td class="value">{{ $reason }}</td></tr>
    @if(!empty($rejection_reason))
    <tr><td class="label">Rejection Reason</td><td class="value">{{ $rejection_reason }}</td></tr>
    @endif
    @if(!empty($reviewer_name))
    <tr><td class="label">Reviewed By</td><td class="value">{{ $reviewer_name }}</td></tr>
    @endif
</table>
@if(!empty($action_url))
<div class="btn-wrap"><a class="btn" href="{{ $action_url }}">Open in HRMS</a></div>
@endif
@endsection

