@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'performance'])

@section('_content')
@include('hrms.employee.partials.styles')

<div class="premium-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-lg-8 col-md-12 text-center text-lg-left mb-3 mb-lg-0">
                <h2 class="font-weight-bold mb-2" style="font-size: 2.2rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                    <i class="fas fa-plus-circle mr-2"></i> New Performance Assessment
                </h2>
                <p class="mb-0" style="font-weight: 500; font-size: 1.1rem; opacity: 0.9;">
                    Evaluate employee milestones and professional growth
                </p>
            </div>
            <div class="col-lg-4 col-md-12 text-center text-lg-right">
                <a href="{{ route('hrms.employees.performance_scores.index') }}" class="btn btn-light rounded-pill px-4 py-2 font-weight-bold shadow-sm" style="color: #4b00e8;">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-2 px-md-4 py-4" style="position: relative; z-index: 10;">
    <div class="row justify-content-center">
        <div class="col-lg-10 mt-2">
            <div class="content-card shadow-lg">
                <form action="{{ route('hrms.employees.performance_scores.store') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-5 align-items-center">
                        <div class="col-lg-7 mb-4 mb-lg-0">
                            <div class="form-group mb-0">
                                <label class="form-label" for="employee_id">Target Employee</label>
                                <select id="employee_id" class="form-control orb-input @error('employee_id') is-invalid @enderror" name="employee_id" required>
                                    <option value="" disabled selected>-- Select Employee to Assess --</option>
                                    @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected': '' }}>
                                        {{ $employee->name }} ({{ $employee->employee_id }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback font-weight-bold mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="score-legend shadow-sm">
                                <h6 class="font-weight-bold mb-2" style="color: #ffb101;"><i class="fas fa-info-circle mr-1"></i> Scoring Guide</h6>
                                <p class="small text-dark mb-0 font-weight-bold" style="opacity: 0.8;">Rate categories from <strong style="color:#4b00e8;">1.0 to 10.0</strong> based on quality of work, consistency, and professional conduct.</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @foreach ($scoreCategories as $category)
                        <div class="col-md-6 mb-3">
                            <div class="category-card shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white p-2 rounded-lg shadow-sm mr-3" style="border: 1px solid #ebf0f6;">
                                            <i class="fas fa-star text-warning"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-0 text-dark">{{ Str::ucfirst(Str::lower($category->name)) }}</h6>
                                    </div>
                                    <span class="badge badge-pill px-3 py-1" style="background: rgba(75,0,232,0.1); color: #4b00e8; font-weight: 700;">Cat: {{ $loop->iteration }}</span>
                                </div>
                                <div class="form-group mb-0">
                                    <input type="hidden" name="categoryAndScore[{{ $loop->index }}][id]" value="{{ $category->id }}">
                                    <input type="number" 
                                           step="0.1" 
                                           min="0" 
                                           max="10"
                                           name="categoryAndScore[{{ $loop->index }}][score]"
                                           value="{{ old("categoryAndScore.$loop->index.score") }}"
                                           class="form-control orb-input @error("categoryAndScore.{$loop->index}.score") is-invalid @enderror"
                                           placeholder="Enter Score (1.0 - 10.0)">
                                    @error("categoryAndScore.{$loop->index}.score")
                                        <div class="invalid-feedback font-weight-bold mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="text-right mt-4 pt-4 border-top">
                        <button type="reset" class="btn btn-outline-secondary rounded-pill px-4 py-2 mr-2 font-weight-bold">Reset Form</button>
                        <button type="submit" class="btn btn-premium px-5 shadow">
                            <i class="fas fa-save mr-2"></i> Securely Save Assessment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection