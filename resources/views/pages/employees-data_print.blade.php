@extends('layouts.print')

@section('_content')
<style>
    @media print {
        .no-print { display: none; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    }
    .print-header {
        border-bottom: 3px solid #1560ab;
        padding-bottom: 20px;
        margin-bottom: 30px;
    }
    .company-name {
        color: #1560ab;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    .report-title {
        font-weight: 700;
        color: #444;
    }
    .table-orb {
        width: 100%;
        border-collapse: collapse;
    }
    .table-orb th {
        background: #f8f9fc !important;
        color: #1560ab !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        padding: 12px;
        border: 1px solid #e1e5eb;
    }
    .table-orb td {
        padding: 10px;
        border: 1px solid #e1e5eb;
        font-size: 0.85rem;
        color: #333;
    }
    .status-badge {
        font-weight: 700;
        font-size: 0.7rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="print-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="company-name mb-0">Orbosis HRMS</h2>
            <p class="text-muted small mb-0">Employee Management Information System</p>
        </div>
        <div class="text-right">
            <h4 class="report-title mb-0">Staff Directory Report</h4>
            <p class="small text-muted mb-0">Generated: {{ now()->format('d M, Y H:i') }}</p>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table-orb">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Position / Designation</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Joining Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="font-weight-bold">{{ $employee->employee_id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->position->name ?? 'N/A' }}</td>
                    <td>{{ $employee->department->name ?? 'N/A' }}</td>
                    <td>{{ $employee->employment_type }}</td>
                    <td class="text-center font-weight-bold">{{ $employee->status }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($employee->start_of_contract)->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-5 pt-4 text-center border-top">
        <p class="small text-muted">Internal Document - Confidential Information</p>
    </div>
</div>
@endsection

@section('_script')
    <script>
      window.onload = function () {
        window.print();
      }
    </script>
@endsection