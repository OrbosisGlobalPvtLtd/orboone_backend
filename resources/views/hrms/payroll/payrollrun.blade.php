@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'payroll_run'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">Run Monthly Payroll</h2>
            <p class="text-muted">Generate payroll records for all active employees based on their attendance.</p>
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

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Select Period</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pages.payroll.payrollrun.run') }}">
                        @csrf
                        <div class="form-group mb-4">
                            <label for="month" class="font-weight-bold">Target Month</label>
                            <input type="month" name="month" id="month" class="form-control form-control-lg" value="{{ date('Y-m') }}" required>
                            @error('month') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle mr-1"></i> Payroll will be calculated for all active employees for this entire month.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm">
                            <i class="fas fa-play-circle mr-2"></i> Initiate Payroll Run
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mt-4 mt-md-0">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h5 class="mb-3 text-primary">How it works?</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex align-items-start">
                            <div class="bg-primary text-white rounded-circle mr-3 d-flex align-items-center justify-content-center" style="min-width: 24px; height: 24px; font-size: 12px;">1</div>
                            <span>The system identifies all active employees with assigned salary structures.</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <div class="bg-primary text-white rounded-circle mr-3 d-flex align-items-center justify-content-center" style="min-width: 24px; height: 24px; font-size: 12px;">2</div>
                            <span>It counts the number of "Present" days in the attendance records for the selected month.</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <div class="bg-primary text-white rounded-circle mr-3 d-flex align-items-center justify-content-center" style="min-width: 24px; height: 24px; font-size: 12px;">3</div>
                            <span>Salary components (Basic, HRA, etc.) are calculated pro-rata based on paid days.</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <div class="bg-primary text-white rounded-circle mr-3 d-flex align-items-center justify-content-center" style="min-width: 24px; height: 24px; font-size: 12px;">4</div>
                            <span>You will be redirected to a preview page to review and lock the payroll.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card { border-radius: 12px; }
    .btn-primary { border-radius: 10px; font-weight: 700; letter-spacing: 0.5px; }
    .form-control-lg { border-radius: 10px; }
</style>
@endpush
@endsection
