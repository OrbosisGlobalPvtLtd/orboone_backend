@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'leave-approval'])

@section('_content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1 font-weight-bold text-dark">Review Leave Application</h2>
                <p class="text-muted">Detailed review of the leave request submitted by <strong>{{ optional($employeeLeaveRequest->employee)->display_name }}</strong>.</p>
            </div>
            <a href="{{ route('employees-leave-request') }}" class="btn btn-outline-secondary shadow-sm px-4">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-2"></i>Application Details</h5>
                    @php
                        $status = strtoupper($employeeLeaveRequest->status);
                        $badgeClass = 'warning';
                        if($status == 'APPROVED' || $status == 'ACCEPTED') $badgeClass = 'success';
                        elseif($status == 'REJECTED') $badgeClass = 'danger';
                    @endphp
                    <span class="badge badge-{{ $badgeClass }}-soft text-{{ $badgeClass }} px-4 py-2">
                        <i class="fas fa-{{ $badgeClass == 'success' ? 'check-circle' : ($badgeClass == 'danger' ? 'times-circle' : 'hourglass-half') }} mr-1"></i>
                        {{ $employeeLeaveRequest->status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small font-weight-bold text-uppercase mb-1 d-block">Employee</label>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle mr-2" style="width: 30px; height: 30px; border-radius: 50%; background: #f4f6f9; display: flex; align-items: center; justify-content: center; color: #4e73df; font-weight: bold; font-size: 0.8rem;">
                                    {{ strtoupper(substr(optional($employeeLeaveRequest->employee)->display_name ?? 'N', 0, 1)) }}
                                </div>
                                <span class="h6 font-weight-bold mb-0 text-dark">{{ optional($employeeLeaveRequest->employee)->display_name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small font-weight-bold text-uppercase mb-1 d-block">Leave Type</label>
                            <span class="badge badge-pill badge-light border px-3 py-2 text-primary font-weight-bold">
                                {{ $employeeLeaveRequest->leave_type ?? 'Paid Leave' }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small font-weight-bold text-uppercase mb-1 d-block">Period</label>
                            <p class="h6 font-weight-bold text-dark">
                                <i class="far fa-calendar-alt text-primary mr-2"></i>
                                {{ $employeeLeaveRequest->from }} <i class="fas fa-long-arrow-alt-right mx-2 text-muted"></i> {{ $employeeLeaveRequest->to }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small font-weight-bold text-uppercase mb-1 d-block">Duration</label>
                            <p class="h6 font-weight-bold text-dark">
                                <span class="badge badge-pill badge-light border px-3 py-2">{{ $diff + 1 }} Working Days</span>
                            </p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="text-muted small font-weight-bold text-uppercase mb-1 d-block">Employee Message</label>
                            <div class="p-3 bg-light rounded border text-dark">
                                {{ $employeeLeaveRequest->message }}
                            </div>
                        </div>
                    </div>

                    @if($employeeLeaveRequest->comment)
                        <div class="row mb-0">
                            <div class="col-12">
                                <label class="text-muted small font-weight-bold text-uppercase mb-1 d-block">Admin Remarks</label>
                                <div class="p-3 bg-soft-info rounded border border-info text-dark">
                                    {{ $employeeLeaveRequest->comment }}
                                    <div class="mt-2 small text-muted font-italic">
                                        - Processed by: {{ $employeeLeaveRequest->checkedBy->name ?? 'System Administrator' }} on {{ $employeeLeaveRequest->updated_at->format('d M, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if($status == 'PENDING' || $status == 'WAITING_FOR_APPROVAL')
                    <div class="card-footer bg-light border-0 py-4">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-danger btn-lg px-5 mr-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times-circle mr-2"></i> Reject
                            </button>
                            <button type="button" class="btn btn-success btn-lg px-5 shadow-sm font-weight-bold" data-toggle="modal" data-target="#acceptModal">
                                <i class="fas fa-check-circle mr-2"></i> Approve
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 font-weight-bold text-primary"><i class="fas fa-chart-pie mr-2"></i>Quota Status ({{ $employeeLeaveRequest->leave_type ?? 'PL' }})</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        @php
                            $year = \Carbon\Carbon::parse($employeeLeaveRequest->start_date ?? now())->year;
                            $alloc = \App\Models\HRMS\Leave\LeaveAllocationM::where('employee_id', $employeeLeaveRequest->employee_id)
                                ->where('year', $year)
                                ->first();
                            
                            $type = $employeeLeaveRequest->leave_type ?? 'PL';
                            if ($type === 'PL') {
                                $quota = $alloc->paid_allocated ?? 0;
                                $used  = $alloc->paid_used ?? 0;
                            } else {
                                $quota = $alloc->sick_allocated ?? 0;
                                $used  = $alloc->sick_used ?? 0;
                            }
                            
                            $left = max(0, $quota - $used);
                            $percent = $quota > 0 ? min(100, ($used / $quota) * 100) : 0;
                        @endphp
                        <div class="h3 font-weight-bold text-dark mb-0">{{ $left == 999 ? '∞' : $left }}</div>
                        <small class="text-muted text-uppercase font-weight-bold">Days Remaining</small>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Yearly Quota</span>
                        <span class="font-weight-bold text-dark">{{ $quota == 999 ? 'Unlimited' : $quota . ' Days' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Already Used</span>
                        <span class="font-weight-bold text-danger">{{ $used }} Days</span>
                    </div>
                    <div class="d-flex justify-content-between mb-0 small">
                        <span class="text-muted">Requested</span>
                        <span class="font-weight-bold text-primary">{{ $diff + 1 }} Days</span>
                    </div>
                    @if($quota != 999)
                        <div class="progress mt-3" style="height: 6px; border-radius: 3px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $percent }}%"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accept Modal -->
<div class="modal fade" id="acceptModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header" style="background: linear-gradient(135deg, #10B981, #059669) !important;">
                <div>
                    <h5 class="modal-title" style="color: #fff !important;">Approve Leave Request</h5>
                    <p class="orb-modal-subtitle" style="color: rgba(255,255,255,0.85) !important;">Approve the leave application details</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('employees-leave-request.update', ['employeeLeaveRequest' => $employeeLeaveRequest->id]) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="accept">
                <div class="modal-body orb-modal-body text-center">
                    <div class="orb-form-section" style="background:#fff !important; border:0 !important; margin-bottom:0 !important; padding:10px !important;">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5 class="mb-3 font-weight-bold" style="color: var(--orb-text);">Are you sure?</h5>
                        <p class="text-muted mb-4">You are about to approve <strong>{{ $diff + 1 }} days</strong> of <strong>{{ $employeeLeaveRequest->leave_type ?? 'Paid Leave' }}</strong> for {{ optional($employeeLeaveRequest->employee)->display_name }}. </p>
                        <div class="form-group text-left">
                            <label class="orb-form-label" style="text-align: left; display: block; margin-bottom: 8px;">Admin Remarks (Optional)</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="Add a comment for the employee..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer orb-modal-footer">
                    <button type="button" class="orb-btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="orb-btn-primary" style="background: linear-gradient(135deg, #10B981, #059669) !important; border-color: #059669 !important;">Confirm & Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header" style="background: linear-gradient(135deg, #EF4444, #DC2626) !important;">
                <div>
                    <h5 class="modal-title" style="color: #fff !important;">Reject Leave Request</h5>
                    <p class="orb-modal-subtitle" style="color: rgba(255,255,255,0.85) !important;">Decline the leave application with reason</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('employees-leave-request.destroy', ['employeeLeaveRequest' => $employeeLeaveRequest->id]) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body orb-modal-body text-center">
                    <div class="orb-form-section" style="background:#fff !important; border:0 !important; margin-bottom:0 !important; padding:10px !important;">
                        <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                        <h5 class="mb-3 font-weight-bold" style="color: var(--orb-text);">Decline Application?</h5>
                        <p class="text-muted mb-4">Please provide a reason for rejecting this leave request.</p>
                        <div class="form-group text-left">
                            <label class="orb-form-label" style="text-align: left; display: block; margin-bottom: 8px;">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="Explain why the leave is rejected..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer orb-modal-footer">
                    <button type="button" class="orb-btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="orb-btn-primary" style="background: linear-gradient(135deg, #EF4444, #DC2626) !important; border-color: #DC2626 !important;">Confirm & Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card { border-radius: 15px; }
    .badge-success-soft { background-color: #e6fffa; color: #2e7d32; border: 1px solid #c8e6c9; }
    .badge-warning-soft { background-color: #fffde7; color: #f9a825; border: 1px solid #fff9c4; }
    .badge-danger-soft { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    .bg-soft-info { background-color: rgba(78, 115, 223, 0.05); }
    .modal-content { border-radius: 20px; }
    .btn-lg { border-radius: 12px; }
    .avatar-circle { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
</style>
@endpush
@endsection
