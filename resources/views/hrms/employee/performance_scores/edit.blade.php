@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'performance'])

@section('_content')
@include('hrms.employee.partials.styles')

<div class="premium-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-lg-8 col-md-12 text-center text-lg-left mb-3 mb-lg-0">
                <h2 class="font-weight-bold mb-2" style="font-size: 2.2rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                    <i class="fas fa-edit mr-2"></i> Modify Assessment
                </h2>
                <p class="mb-0" style="font-weight: 500; font-size: 1.1rem; opacity: 0.9;">
                    Refining performance highlights and professional evaluation
                </p>
            </div>
            <div class="col-lg-4 col-md-12 text-center text-lg-right">
                <a href="{{ route('hrms.employees.performance_scores.index') }}" class="btn btn-light rounded-pill px-4 py-2 font-weight-bold shadow-sm" style="color: var(--orb-primary);">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-2 px-md-4 py-4" style="position: relative; z-index: 10;">
    <div class="row justify-content-center">
        <div class="col-lg-10 mt-2">
            <div class="content-card shadow-lg">
                <form action="{{ route('hrms.employees.performance_scores.update', ['employeeScore' => $employeeScore->group_id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-5 align-items-center">
                        <div class="col-lg-7 mb-4 mb-lg-0">
                            <label class="form-label mb-2" style="color: #858796;">Assessing Employee (Locked)</label>
                            <div class="employee-display-card">
                                @php
                                    $empPhoto = $employeeScore->employee->employeeDetail->photo ?? null;
                                    $empPhotoUrl = asset('images/profile.png');
                                    if($empPhoto) {
                                        $cleanEmpPath = str_replace('public/', '', $empPhoto);
                                        $empPhotoUrl = \Illuminate\Support\Str::startsWith($cleanEmpPath, ['http', 'uploads/']) ? asset($cleanEmpPath) : asset("storage/{$cleanEmpPath}");
                                    }
                                @endphp
                                <img src="{{ $empPhotoUrl }}" class="rounded-lg mr-3 shadow-sm" style="width: 55px; height: 55px; object-fit: cover; border: 2px solid white;" onerror="this.src='{{ asset('images/profile.png') }}'" alt="">
                                <div>
                                    <h5 class="mb-1 font-weight-bold text-dark">{{ $employeeScore->employee->name }}</h5>
                                    <span class="badge badge-pill" style="background: rgba(75,0,232,0.1); color: var(--orb-primary); font-weight: 700;">{{ $employeeScore->employee->employee_id }}</span>
                                </div>
                                <input type="hidden" name="employee_id" value="{{ $employeeScore->employee_id }}">
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="bg-light p-4 rounded-lg border-left shadow-sm" style="border-left-color: #ffb101 !important; border-left-width: 5px !important;">
                                <h6 class="font-weight-bold mb-2" style="color: #ffb101;"><i class="fas fa-history mr-1"></i> Revision Mode</h6>
                                <p class="small text-muted mb-0 font-weight-bold">Updating a previous assessment.<br>Original date: <span style="color: var(--orb-primary);">{{ \Carbon\Carbon::parse($employeeScore->created_at)->format('M d, Y') }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @foreach ($scores as $score)
                        <div class="col-md-6 mb-3">
                            <div class="category-card shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white p-2 rounded-lg shadow-sm mr-3" style="border: 1px solid #ebf0f6;">
                                            <i class="fas fa-pen text-warning"></i>
                                        </div>
                                        <h6 class="font-weight-bold mb-0 text-dark">{{ Str::ucfirst(Str::lower($score->scoreCategory->name)) }}</h6>
                                    </div>
                                    <span class="badge badge-pill px-3 py-1" style="background: rgba(255,177,1,0.1); color: #d6a121; font-weight: 700;">Revision</span>
                                </div>
                                <div class="form-group mb-0">
                                    <input type="hidden" name="categoryAndScore[{{ $loop->index }}][id]" value="{{ $score->scoreCategory->id }}">
                                    <input type="number" 
                                           step="0.1" 
                                           min="0" 
                                           max="10"
                                           name="categoryAndScore[{{ $loop->index }}][score]"
                                           value="{{ old("categoryAndScore.$loop->index.score", $score->score) }}"
                                           class="form-control orb-input @error("categoryAndScore.{$loop->index}.score") is-invalid @enderror"
                                           placeholder="Update Score (1.0 - 10.0)">
                                    @error("categoryAndScore.{$loop->index}.score")
                                        <div class="invalid-feedback font-weight-bold mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="text-right mt-4 pt-4 border-top">
                        <button type="submit" class="btn btn-premium px-5 shadow">
                            <i class="fas fa-sync-alt mr-2"></i> Update Assessment Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection