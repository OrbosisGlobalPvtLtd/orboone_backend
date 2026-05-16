@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'leave-approval'])

@section('_content')
<style>
    :root { --primary-orb: #1560ab; }
    .custom-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .btn-orb { background: var(--primary-orb); color: #fff; border-radius: 50px; padding: 10px 25px; font-weight: 600; transition: all 0.3s; }
    .btn-orb:hover { background: #0d4a8a; transform: translateY(-2px); color: #fff; }
    .table-orb thead th { background: #f8f9fc; color: var(--primary-orb); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; border: none; padding: 15px; }
    .table-orb tbody td { vertical-align: middle; padding: 15px; border-bottom: 1px solid #f1f4f8; }
    .status-badge { font-weight: 700; font-size: 0.7rem; border-radius: 50px; padding: 6px 12px; }
    .badge-success-soft { background: #e6f7e6; color: #1cc88a; }
    .badge-warning-soft { background: #fff9e6; color: #f6c23e; }
    .badge-danger-soft { background: #ffe6e6; color: #e74a3b; }
    .avatar-circle { border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
</style>

<div class="container-fluid py-4 px-4">
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-6">
            <h4 class="font-weight-bold text-dark mb-1">Leave Approval Hub</h4>
            <p class="text-muted small mb-0">Review and process workforce time-off applications</p>
        </div>
        <div class="col-12 col-md-6 text-md-right mt-3 mt-md-0">
            <a href="{{ route('employees-leave-request.summary') }}" class="btn btn-orb mr-2">
                <i class="fas fa-chart-pie mr-2"></i> Leave Summary
            </a>
            <a href="{{ route('employees-leave-request.print') }}" class="btn btn-light" style="border-radius: 50px;" target="_blank">
                <i class="fas fa-print mr-2"></i> Print All
            </a>
        </div>
    </div>

    <div id="ajax-alert" style="display: none;" class="alert alert-success border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle mr-2"></i> <span id="alert-message"></span>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>

    <div class="card custom-card">
        <div class="card-header bg-white border-bottom-0 py-3">
            <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-clock mr-2"></i> Pending & Recent Applications</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-orb mb-0">
                    <thead>
                        <tr>
                            <th>Staff Member</th>
                            <th class="text-center">Category</th>
                            <th class="text-center">Period/Duration</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="leave-requests-table">
                        @php $currentEmployeeId = null; @endphp
                        @forelse ($employeeLeaveRequests as $leaveReq)
                            @if ($currentEmployeeId !== $leaveReq->employee_id)
                                <tr class="bg-light">
                                    <td colspan="5" class="py-2 px-4">
                                        <div class="d-flex align-items-center">
                                            @php
                                                $empPhoto = $leaveReq->employee->employeeDetail->photo ?? 'profile.png';
                                                $empFinalUrl = (strpos($empPhoto, 'http') === 0) ? $empPhoto : asset('storage/' . $empPhoto);
                                            @endphp
                                            <img src="{{ $empFinalUrl }}" 
                                                 onerror="this.src='{{ asset('images/profile.png') }}'; this.onerror=null;"
                                                 class="avatar-circle mr-3" 
                                                 style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                            <div>
                                                <span class="font-weight-bold text-dark small">{{ optional($leaveReq->employee)->display_name }}</span>
                                                <span class="badge badge-light ml-2 text-muted" style="font-size: 0.65rem;">ID: {{ $leaveReq->employee->employee_id }}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @php $currentEmployeeId = $leaveReq->employee_id; @endphp
                            @endif
                            <tr id="row-{{ $leaveReq->id }}">
               
                                <td class="pl-5 border-left" style="border-left-width: 4px !important; border-left-color: var(--primary-orb) !important;">
                                    <span class="text-muted extra-small font-weight-bold text-uppercase">Request #{{ $leaveReq->id }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-light border text-primary small font-weight-bold">
                                        {{ $leaveReq->leave_type ?? 'General' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="small font-weight-bold text-dark">{{ \Carbon\Carbon::parse($leaveReq->from)->format('d M') }} - {{ \Carbon\Carbon::parse($leaveReq->to)->format('d M, Y') }}</div>
                                    @php
                                        $f = \Carbon\Carbon::parse($leaveReq->from);
                                        $t = \Carbon\Carbon::parse($leaveReq->to);
                                        $d = $f->diffInDays($t) + 1;
                                    @endphp
                                    <small class="text-muted font-weight-bold">{{ $d }} Working Days</small>
                                </td>
                                <td class="status-cell text-center">
                                    @php
                                        $status = strtoupper($leaveReq->status);
                                        $badgeClass = 'warning';
                                        if($status == 'APPROVED' || $status == 'ACCEPTED') $badgeClass = 'success';
                                        elseif($status == 'REJECTED') $badgeClass = 'danger';
                                    @endphp
                                    <span class="status-badge badge-{{ $badgeClass }}-soft text-{{ $badgeClass }}">
                                        <i class="fas fa-{{ $badgeClass == 'success' ? 'check-circle' : ($badgeClass == 'danger' ? 'times-circle' : 'hourglass-half') }} mr-1"></i>
                                        {{ $leaveReq->status }}
                                    </span>
                                </td>
                                <td class="text-right action-cell">
                                    <div class="btn-group">
                                        <a href="{{ route('employees-leave-request.show', $leaveReq->id) }}" class="btn btn-sm btn-light text-primary mr-1" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($status == 'PENDING' || $status == 'WAITING_FOR_APPROVAL')
                                            <button type="button" class="btn btn-sm btn-success mr-1 approve-btn shadow-sm" data-id="{{ $leaveReq->id }}" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger reject-btn shadow-sm" data-id="{{ $leaveReq->id }}" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No pending leave applications found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $employeeLeaveRequests->links() }}
        </div>
    </div>
</div>
    <!-- Reject Reason Modal -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-times-circle mr-2"></i> Reject Application</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="reject-id">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-dark text-uppercase">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea id="reject-comment" class="form-control border-light shadow-none" rows="4" placeholder="Briefly explain why this leave request is being declined..." style="border-radius: 10px; background: #f8f9fc;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal" style="border-radius: 50px;">Cancel</button>
                    <button type="button" id="confirm-reject" class="btn btn-danger px-4" style="border-radius: 50px; font-weight: 700;">Confirm Rejection</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Approve AJAX
    $('.approve-btn').click(function() {
        const id = $(this).data('id');
        
        if (confirm('Are you sure you want to approve this leave request?')) {
            $.ajax({
                url: `/employees-leave-request/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    type: 'accept'
                },
                success: function(response) {
                    if (response.success) {
                        $('#ajax-alert').fadeIn().find('#alert-message').text(response.message);
                        const row = $(`#row-${id}`);
                        row.find('.status-cell').html(`
                            <span class="status-badge badge-success-soft text-success">
                                <i class="fas fa-check-circle mr-1"></i>
                                Approved
                            </span>
                        `);
                        row.find('.action-cell .approve-btn, .action-cell .reject-btn').remove();
                        setTimeout(() => $('#ajax-alert').fadeOut(), 3000);
                    }
                }
            });
        }
    });

    // Reject Modal Trigger
    $('.reject-btn').click(function() {
        const id = $(this).data('id');
        $('#reject-id').val(id);
        $('#reject-comment').val('');
        $('#rejectReasonModal').modal('show');
    });

    // Confirm Reject AJAX
    $('#confirm-reject').click(function() {
        const id = $('#reject-id').val();
        const comment = $('#reject-comment').val();
        
        if (!comment) {
            alert('Please provide a reason for rejection.');
            return;
        }

        $.ajax({
            url: `/employees-leave-request/${id}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE',
                comment: comment
            },
            success: function(response) {
                if (response.success) {
                    $('#rejectReasonModal').modal('hide');
                    $('#ajax-alert').fadeIn().find('#alert-message').text(response.message);
                    const row = $(`#row-${id}`);
                    row.find('.status-cell').html(`
                        <span class="status-badge badge-danger-soft text-danger">
                            <i class="fas fa-times-circle mr-1"></i>
                            Rejected
                        </span>
                    `);
                    row.find('.action-cell .approve-btn, .action-cell .reject-btn').remove();
                    setTimeout(() => $('#ajax-alert').fadeOut(), 3000);
                }
            }
        });
    });
});
</script>
@endpush
@endsection
