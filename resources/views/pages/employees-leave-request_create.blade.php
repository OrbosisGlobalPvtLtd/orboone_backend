@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'leave-request'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1 font-weight-bold text-dark">Apply for Leave</h2>
                <p class="text-muted">Submit your leave application based on your eligible quota.</p>
            </div>
            <a href="{{ route('employees-leave-request') }}" class="btn btn-outline-secondary shadow-sm px-4">
                <i class="fas fa-arrow-left mr-2"></i> My Requests
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Application Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-edit mr-2"></i>Leave Application Form</h5>
                </div>
                <div class="card-body py-4">
                    <form action="{{ route('employees-leave-request.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                        <div class="form-group mb-4">
                            <label for="leave_type" class="font-weight-bold text-dark">Leave Type</label>
                            <select name="leave_type" id="leave_type" class="form-control custom-select @error('leave_type') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Select Leave Type --</option>
                                <option value="Paid Leave" {{ old('leave_type') == 'Paid Leave' ? 'selected' : '' }}>Paid Leave (PL)</option>
                                <option value="Sick Leave" {{ old('leave_type') == 'Sick Leave' ? 'selected' : '' }}>Sick Leave (SL)</option>
                                <option value="Casual Leave" {{ old('leave_type') == 'Casual Leave' ? 'selected' : '' }}>Casual Leave (CL)</option>
                                <option value="Work From Home" {{ old('leave_type') == 'Work From Home' ? 'selected' : '' }}>Work From Home (WFH)</option>
                                <option value="Unpaid Leave" {{ old('leave_type') == 'Unpaid Leave' ? 'selected' : '' }}>Unpaid Leave</option>
                            </select>
                            @error('leave_type')
                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="from" class="font-weight-bold text-dark">From Date</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="far fa-calendar-alt text-primary"></i></span>
                                    </div>
                                    <input type="date" name="from" id="from" class="form-control border-left-0 @error('from') is-invalid @enderror" value="{{ old('from') }}" required>
                                </div>
                                @error('from')
                                    <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="to" class="font-weight-bold text-dark">To Date</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="far fa-calendar-check text-success"></i></span>
                                    </div>
                                    <input type="date" name="to" id="to" class="form-control border-left-0 @error('to') is-invalid @enderror" value="{{ old('to') }}" required>
                                </div>
                                @error('to')
                                    <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="message" class="font-weight-bold text-dark">Reason / Message</label>
                            <textarea name="message" id="message" rows="4" class="form-control @error('message') is-invalid @enderror" placeholder="Provide a detailed reason for your leave..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm font-weight-bold">
                                <i class="fas fa-paper-plane mr-2"></i> Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Balance Info -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-primary"><i class="fas fa-chart-pie mr-2"></i>Leave Balances ({{ Carbon\Carbon::now()->year }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <thead class="bg-light text-muted small font-weight-bold">
                                <tr>
                                    <th class="pl-4">Type</th>
                                    <th class="text-center">Quota</th>
                                    <th class="text-center">Used</th>
                                    <th class="pr-4 text-right">Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employeeLeaves as $leave)
                                    <tr class="border-bottom">
                                        <td class="pl-4 py-3">
                                            <span class="font-weight-bold text-dark">{{ $leave->leave_type }}</span>
                                        </td>
                                        <td class="text-center py-3">
                                            <span class="badge badge-pill badge-light border">{{ $leave->leaves_quota == 999 ? '∞' : $leave->leaves_quota }}</span>
                                        </td>
                                        <td class="text-center py-3">
                                            <span class="text-danger font-weight-bold">{{ $leave->used_leaves }}</span>
                                        </td>
                                        <td class="pr-4 text-right py-3">
                                            <span class="text-success font-weight-bold">{{ $leave->leaves_quota == 999 ? '∞' : ($leave->leaves_quota - $leave->used_leaves) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-3">
                    <div class="small text-muted mb-2">
                        <i class="fas fa-info-circle text-primary mr-1"></i> <strong>Pro-rata Rules:</strong>
                    </div>
                    <ul class="list-unstyled small text-muted mb-0 pl-3">
                        <li class="mb-1">• PL: 2.0 days credited per month (24 total)</li>
                        <li class="mb-1">• SL: 1.0 day credited per month (12 total)</li>
                        <li class="mb-1">• CL: 1.0 day credited per month (12 total)</li>
                        <li class="mb-1">• Effective: Jan 1 - Dec 31</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card { border-radius: 15px; overflow: hidden; }
    .btn-lg { border-radius: 12px; }
    .form-control, .input-group-text { border-radius: 10px; }
    .table thead th { letter-spacing: 0.5px; text-transform: uppercase; }
</style>
@endpush
@endsection
