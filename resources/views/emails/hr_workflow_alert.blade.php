@extends('emails.layouts.enterprise')

@section('content')
<span class="badge">HR Workflow</span>
<h2 style="margin:0 0 8px;">{{ $workflowTitle }}</h2>
<table class="meta">
    @foreach($details as $label => $value)
    <tr>
        <td class="label">{{ $label }}</td>
        <td class="value">{{ $value }}</td>
    </tr>
    @endforeach
</table>
@if(!empty($actionUrl))
<div class="btn-wrap"><a class="btn" href="{{ $actionUrl }}">Review Request</a></div>
@endif
@endsection

