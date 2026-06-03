@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'performance'])

@section('_content')
@include('hrms.employee.partials.styles')

<div class="premium-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-lg-8 col-md-12 text-center text-lg-left mb-3 mb-lg-0">
                <h2 class="font-weight-bold mb-2" style="font-size: 2.2rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                    <i class="fas fa-eye mr-2"></i> Performance Intelligence
                </h2>
                <p class="mb-0" style="font-weight: 500; font-size: 1.1rem; opacity: 0.9;">
                    Deep-dive into professional performance metrics and milestones
                </p>
            </div>
            <div class="col-lg-4 col-md-12 text-center text-lg-right">
                <a href="{{ route('hrms.employees.performance_scores.index') }}" class="btn btn-light rounded-pill px-4 py-2 font-weight-bold shadow-sm" style="color: var(--orb-primary);">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Listing
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-2 px-md-4 py-4" style="position: relative; z-index: 10;">
    <div class="row mt-2">
        <!-- Left Column: Profile Summary -->
        <div class="col-lg-4 mb-4">
            <div class="info-card">
                <div class="profile-section text-center">
                    @php
                        $empPhoto = $employeeScore->employee->employeeDetail->photo ?? null;
                        $empPhotoUrl = asset('images/profile.png');
                        if($empPhoto) {
                            $cleanEmpPath = str_replace('public/', '', $empPhoto);
                            $empPhotoUrl = \Illuminate\Support\Str::startsWith($cleanEmpPath, ['http', 'uploads/']) ? asset($cleanEmpPath) : asset("storage/{$cleanEmpPath}");
                        }
                    @endphp
                    <img src="{{ $empPhotoUrl }}" class="profile-avatar mb-3" onerror="this.src='{{ asset('images/profile.png') }}'" alt="">
                    <h4 class="font-weight-bold text-dark mb-1">{{ $employeeScore->employee->name }}</h4>
                    <p class="text-muted font-weight-bold">{{ $employeeScore->employee->designation->name ?? 'Staff Professional' }}</p>
                    <span class="badge badge-pill px-3 py-2" style="background: rgba(75,0,232,0.1); color: var(--orb-primary); font-weight: 700;">{{ $employeeScore->employee->employee_id }}</span>
                </div>

                <div class="row mb-4">
                    <div class="col-6 mb-4">
                        <div class="stat-label">Department</div>
                        <div class="stat-value"><i class="fas fa-building mr-1" style="color: #ffb101;"></i> {{ $employeeScore->employee->department->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="stat-label">Joining Date</div>
                        <div class="stat-value"><i class="fas fa-calendar-alt mr-1" style="color: #ffb101;"></i> {{ \Carbon\Carbon::parse($employeeScore->employee->joining_date)->format('M d, Y') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="stat-label">Assessment Date</div>
                        <div class="stat-value">
                            <i class="far fa-calendar-check mr-2" style="color: var(--orb-primary);"></i> {{ \Carbon\Carbon::parse($employeeScore->created_at)->format('l, F jS Y - h:i A') }}
                        </div>
                    </div>
                </div>

                <div class="border-top pt-4 mt-2">
                    <h6 class="stat-label mb-3">Evaluator Information</h6>
                    <div class="d-flex align-items-center bg-light p-3 rounded-lg" style="border: 1px solid #ebf0f6;">
                        @php
                            $scorerPhoto = $employeeScore->scoredBy->employeeDetail->photo ?? null;
                            $scorerPhotoUrl = asset('images/profile.png');
                            if($scorerPhoto) {
                                $cleanScorerPath = str_replace('public/', '', $scorerPhoto);
                                $scorerPhotoUrl = \Illuminate\Support\Str::startsWith($cleanScorerPath, ['http', 'uploads/']) ? asset($cleanScorerPath) : asset("storage/{$cleanScorerPath}");
                            }
                        @endphp
                        <img src="{{ $scorerPhotoUrl }}" class="mr-3 shadow-sm" style="width: 45px; height: 45px; border-radius: 12px; object-fit: cover;" onerror="this.src='{{ asset('images/profile.png') }}'" alt="">
                        <div>
                            <h6 class="mb-1 font-weight-bold text-dark">{{ $employeeScore->scoredBy->name }}</h6>
                            <span class="badge badge-pill badge-dark px-2">Verified Evaluator</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Scores & Analysis -->
        <div class="col-lg-8 mb-4">
            <div class="info-card">
                <div class="row align-items-center mb-5 border-bottom pb-4">
                    <div class="col-md-4 text-center mb-4 mb-md-0">
                        <div class="overall-badge">
                            {{ number_format($employeeScore->average_score, 1) }}
                        </div>
                        <h5 class="font-weight-bold mb-1" style="color: var(--orb-primary);">Overall Index</h5>
                        <p class="small text-muted mb-0 font-weight-bold">Out of 10.0 scale</p>
                    </div>
                    <div class="col-md-8">
                        <h4 class="font-weight-bold text-dark mb-3">Performance Index Analysis</h4>
                        @php
                            $avg = $employeeScore->average_score;
                            $status = $avg >= 8 ? 'Excellent Performance' : ($avg >= 5 ? 'Steady Competence' : 'Improvement Required');
                            $color = $avg >= 8 ? '#15a06d' : ($avg >= 5 ? '#d6a121' : '#c43224');
                            $desc = $avg >= 8 ? 'Demonstrates exceptional skill and leadership.' : ($avg >= 5 ? 'Consistently meets organizational expectations.' : 'Specific areas require active mentoring and support.');
                        @endphp
                        <div class="p-4 rounded-lg shadow-sm" style="background: #f8faff; border-left: 5px solid {{ $color }};">
                            <h5 class="font-weight-bold mb-2" style="color: {{ $color }};"><i class="fas fa-check-circle mr-2"></i> {{ $status }}</h5>
                            <p class="text-dark font-weight-bold small mb-0" style="opacity: 0.8; line-height: 1.6;">{{ $desc }} This assessment highlights both organizational strengths and development opportunities for the employee.</p>
                        </div>
                    </div>
                </div>

                <h6 class="stat-label mb-4" style="font-size: 1rem;">Detailed Metrics Breakdown</h6>
                <div class="row">
                    @foreach ($scores as $score)
                    <div class="col-md-6 mb-2">
                        <div class="score-item shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="font-weight-bold text-dark" style="font-size: 0.9rem;"><i class="fas fa-star mr-2 text-warning"></i> {{ Str::ucfirst(Str::lower($score->scoreCategory->name)) }}</span>
                                <span class="score-val">{{ number_format($score->score, 1) }}</span>
                            </div>
                            <div class="progress shadow-sm">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ ($score->score / 10) * 100 }}%" 
                                     aria-valuenow="{{ $score->score }}" aria-valuemin="0" aria-valuemax="10"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="text-right mt-5 pt-4 border-top">
                    @if (collect($accesses)->where('menu_id', 3)->first()->status == 2 && ($employeeScore->scored_by == (auth()->user()->employee ? auth()->user()->employee->id : null) || auth()->user()->isAdmin()))
                    <a href="{{ route('hrms.employees.performance_scores.edit', ['employeeScore' => $employeeScore->group_id]) }}" class="btn btn-warning btn-action mr-2 mb-2 mb-md-0 shadow-sm font-weight-bold text-dark">
                        <i class="fas fa-edit mr-2"></i> Edit Record
                    </a>
                    <form action="{{ route('hrms.employees.performance_scores.destroy', ['employeeScore' => $employeeScore->group_id]) }}" method="POST" class="d-inline mb-2 mb-md-0 m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-action mr-2 shadow-sm font-weight-bold" onclick="return confirm('Delete this assessment completely?')">
                            <i class="fas fa-trash-alt mr-2"></i> Delete
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('hrms.employees.performance_scores.export') }}" class="btn btn-dark btn-action shadow-sm font-weight-bold" target="_blank">
                        <i class="fas fa-print mr-2"></i> Print View
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection