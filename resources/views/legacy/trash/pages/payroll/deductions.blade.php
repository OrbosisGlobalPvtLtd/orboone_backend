@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container">
    <h2>My Statutory Deductions</h2>

    <table class="table table-bordered mt-3">
        <thead>
        <tr>
            <th>Month</th>
            <th>PF</th>
            <th>ESI</th>
            <th>PT</th>
            <th>TDS</th>
        </tr>
        </thead>
        <tbody>
        @forelse($payrolls as $p)
            <tr>
                <td>{{ $p->month }}</td>
                <td>{{ $p->pf_deduction ?? 0 }}</td>
                <td>{{ $p->esi_deduction ?? 0 }}</td>
                <td>{{ $p->pt_deduction ?? 0 }}</td>
                <td>{{ $p->tds_deduction ?? 0 }}</td>
            </tr>
        @empty
            <tr><td colspan="5">No payroll data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
