@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container">
    <h2>Employee Claims</h2>

    @if(auth()->user()->hasPermission('payroll.claims.manage') || auth()->user()->hasPermission('payroll.generate.process'))
        <form method="GET" class="form-inline mb-3">
            <label class="mr-2">Payroll Month</label>
            <input type="number" min="1" max="12" name="month" value="{{ request('month', now()->month) }}" class="form-control mr-2" style="width:100px">
            <label class="mr-2">Year</label>
            <input type="number" min="2020" max="2099" name="year" value="{{ request('year', now()->year) }}" class="form-control mr-2" style="width:120px">
            <button class="btn btn-outline-primary btn-sm">Use Period</button>
        </form>
    @endif

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Employee</th>
            <th>Category</th>
            <th>Amount</th>
            <th>Payroll Period</th>
            <th>Status</th>
            <th>Bill</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($claims as $c)
            <tr>
                <td>{{ $c->employee->display_name ?? 'N/A' }}</td>
                <td>{{ $c->category }}</td>
                <td>Rs {{ number_format($c->amount, 2) }}</td>
                <td>
                    @if($c->payroll_month && $c->payroll_year)
                        {{ str_pad($c->payroll_month, 2, '0', STR_PAD_LEFT) }}/{{ $c->payroll_year }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ ucfirst($c->status) }}</td>
                <td>
                    @if($c->file)
                        <a href="{{ route('hrms.documents.file', ['path' => $c->file]) }}" target="_blank">View</a>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($c->status == 'pending')
                        <form method="POST" action="{{ route('pages.payroll.claims.approve', $c->id) }}" class="d-inline-block">
                            @csrf
                            <input type="hidden" name="payroll_month" value="{{ request('month', now()->month) }}">
                            <input type="hidden" name="payroll_year" value="{{ request('year', now()->year) }}">
                            <button class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('pages.payroll.claims.reject', $c->id) }}" class="d-inline-block">
                            @csrf
                            <button class="btn btn-sm btn-danger">Reject</button>
                        </form>
                    @else
                        <span class="text-muted">Processed</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7">No claims.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
