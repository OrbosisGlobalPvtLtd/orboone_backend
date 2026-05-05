@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'payroll_index'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-1">Salary Management</h2>
            <p class="text-muted">Define and assign salary structures to employees.</p>
        </div>
        <div class="col-md-6 text-md-right">
            @if(!isset($employees))
                <a href="{{ route('pages.payroll.create') }}" class="btn btn-primary shadow-sm px-4">
                    <i class="fas fa-plus-circle mr-1"></i> Create Salary Structure
                </a>
                <a href="{{ route('pages.payroll.assign') }}" class="btn btn-outline-primary shadow-sm px-4 ml-2">
                    <i class="fas fa-user-tag mr-1"></i> Assign to Employees
                </a>
            @else
                <a href="{{ route('pages.payroll.index') }}" class="btn btn-outline-secondary shadow-sm px-4">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Structures
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(isset($employees))
        <!-- Assign Structures View -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">Assign Salary Structures</h5>
            </div>
            <div class="card-body p-0">
                <form method="POST" action="{{ route('pages.payroll.assign.save') }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light text-uppercase small font-weight-bold text-muted">
                                <tr>
                                    <th class="px-4">Employee</th>
                                    <th>Current Structure</th>
                                    <th class="px-4">Assign New Structure</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                    <tr>
                                        <td class="px-4">
                                            <span class="font-weight-bold d-block text-dark">{{ $employee->name }}</span>
                                            <small class="text-muted">ID: {{ $employee->id }} | {{ $employee->department->name ?? 'No Dept' }}</small>
                                        </td>
                                        <td>
                                            @if($employee->salaryStructure)
                                                <span class="badge badge-info-soft text-info px-3 py-2">
                                                    {{ $employee->salaryStructure->name }}
                                                </span>
                                            @else
                                                <span class="text-muted italic">Not Assigned</span>
                                            @endif
                                        </td>
                                        <td class="px-4">
                                            <select name="assignments[{{ $employee->id }}]" class="form-control custom-select">
                                                <option value="">-- No Structure --</option>
                                                @foreach($structures as $s)
                                                    <option value="{{ $s->id }}" {{ ($employee->salary_structure_id == $s->id) ? 'selected' : '' }}>
                                                        {{ $s->name }} (₹{{ number_format($s->basic_salary, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white border-0 py-4 text-right">
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                            <i class="fas fa-save mr-2"></i> Save Assignments
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <!-- Structures List View -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">Defined Salary Structures</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-uppercase small font-weight-bold text-muted">
                            <tr>
                                <th class="px-4">Structure Name</th>
                                <th class="text-right">Basic Salary</th>
                                <th class="text-center">HRA %</th>
                                <th class="text-right">Allowance</th>
                                <th class="text-right">PT</th>
                                <th class="text-center">Employees</th>
                                <th class="text-right px-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($structures as $s)
                                <tr>
                                    <td class="px-4 font-weight-bold text-dark">{{ $s->name }}</td>
                                    <td class="text-right">₹{{ number_format($s->basic_salary, 2) }}</td>
                                    <td class="text-center">{{ $s->hra_percent }}%</td>
                                    <td class="text-right">₹{{ number_format($s->allowance, 2) }}</td>
                                    <td class="text-right">₹{{ number_format($s->pt_amount, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-pill badge-primary px-3">{{ $s->employees_count }}</span>
                                    </td>
                                    <td class="text-right px-4">
                                        <a href="{{ route('pages.payroll.edit', $s->id) }}" class="btn btn-sm btn-outline-primary px-3">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-file-invoice-dollar fa-3x text-light mb-3 d-block"></i>
                                        <p class="text-muted">No salary structures defined yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .card { border-radius: 12px; }
    .table thead th { border-top: 0; letter-spacing: 0.5px; }
    .badge-info-soft { background-color: rgba(23, 162, 184, 0.1); }
    .custom-select { border-radius: 8px; }
    .btn-primary, .btn-success { border-radius: 10px; font-weight: 700; }
</style>
@endpush
@endsection
