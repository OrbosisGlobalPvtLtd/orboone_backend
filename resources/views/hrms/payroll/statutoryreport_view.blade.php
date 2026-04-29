@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container-fluid py-4">

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h5 class="mb-0">
                Statutory Report – {{ $month }}
            </h5>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Gross Salary</th>
                        <th>PT Deduction</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($payrolls as $key => $payroll)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $payroll->employee_id }}</td>
                            <td>{{ $payroll->employee->name ?? '-' }}</td>
                            <td>₹ {{ number_format($payroll->gross_salary, 2) }}</td>
                            <td>₹ {{ number_format($payroll->pt_deduction, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No payroll data found for this month
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($payrolls->count())
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4" class="text-end">Total PT</th>
                        <th>₹ {{ number_format($pt_total, 2) }}</th>
                    </tr>
                </tfoot>
                @endif

            </table>

        </div>
    </div>

</div>
@endsection
