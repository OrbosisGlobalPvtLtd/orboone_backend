@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">FnF Pending Employees</h4>
    </div>

    <!-- Card -->
    <div class="card shadow-sm">
        <div class="card-body">

            @if($employees->isEmpty())
                <div class="alert alert-info mb-0">
                    No FnF pending employees found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>Employee Code</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $index => $employee)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $employee->user->name ?? 'N/A' }}</td>
                                    <td>{{ $employee->employee_code ?? 'N/A' }}</td>
                                    <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                    <td>{{ $employee->position->name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('pages.payroll.create', $employee->id) }}"
                                           class="btn btn-sm btn-primary">
                                            Initiate FnF
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection
