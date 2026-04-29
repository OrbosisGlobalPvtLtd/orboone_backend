@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container">
    <h2>Create Salary Structure</h2>

    <form method="POST" action="{{ route('pages.payroll.salary_structure') }}" id="structure-form">
        @csrf

        <div class="mb-3">
            <label>Structure Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Basic Salary</label>
            <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ old('basic_salary') }}" required>
            @error('basic_salary') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>HRA Percent (%)</label>
            <input type="number" step="0.01" name="hra_percent" class="form-control" value="{{ old('hra_percent', 0) }}" required>
            @error('hra_percent') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Allowance</label>
            <input type="number" step="0.01" name="allowance" class="form-control" value="{{ old('allowance', 0) }}">
            @error('allowance') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Professional Tax Amount</label>
            <input type="number" step="0.01" name="pt_amount" class="form-control" value="{{ old('pt_amount', 0) }}">
            @error('pt_amount') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Effective From</label>
            <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date') }}" required>
            @error('effective_date') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">Save Structure</button>
        </div>
    </form>
</div>
@endsection
