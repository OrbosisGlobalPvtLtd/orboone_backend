@extends('layouts.admin', ['accesses' => $accesses ?? [], 'active' => 'leave-approval'])

@section('_head')
@include('hrms.leave.shared.style')
@endsection

@section('_content')
<div class="leave-page">
    <div class="leave-container">

        <div class="leave-header">
            <div>
                <h3 class="leave-title">Employee Leave Requests</h3>
                <p class="leave-subtitle">Review, approve, and manage workforce time-off applications.</p>
            </div>
            <div>
                <a href="{{ route('employees-leave-request.summary') }}" class="leave-btn leave-btn-light mr-2">
                    <i class="fas fa-chart-pie text-primary"></i> Summary
                </a>
                <a href="{{ route('employees-leave-request.print') }}" class="leave-btn leave-btn-primary" target="_blank">
                    <i class="fas fa-print"></i> Print All
                </a>
            </div>
        </div>

        <div id="ajax-alert" style="display: none;" class="alert alert-success border-0 shadow-sm mb-4" style="border-radius:14px;">
            <i class="fas fa-check-circle mr-2"></i> <span id="alert-message"></span>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>

        <div class="leave-summary-grid">
            <div class="summary-card">
                <div class="summary-icon bg-soft-warning text-warning" style="background:#ffedd5;color:#c2410c">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="summary-info">
                    <h4>{{ collect($employeeLeaveRequests->items() ?? [])->filter(function($r) { return strtoupper($r->status) == 'PENDING'; })->count() }}</h4>
                    <p>Pending Approvals</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon bg-soft-success text-success" style="background:#dcfce7;color:#166534">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="summary-info">
                    <h4>{{ collect($employeeLeaveRequests->items() ?? [])->filter(function($r) { return strtoupper($r->status) == 'APPROVED' || strtoupper($r->status) == 'ACCEPTED'; })->count() }}</h4>
                    <p>Approved Leaves</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon bg-soft-danger text-danger" style="background:#fee2e2;color:#991b1b">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="summary-info">
                    <h4>{{ collect($employeeLeaveRequests->items() ?? [])->filter(function($r) { return strtoupper($r->status) == 'REJECTED'; })->count() }}</h4>
                    <p>Rejected Leaves</p>
                </div>
            </div>
        </div>

        <div class="leave-card">
            <div class="leave-table-wrap">
                <div class="leave-table-responsive">
                    <table class="leave-table js-datatable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>From - To</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employeeLeaveRequests as $leaveReq)
                                <tr id="row-{{ $leaveReq->id }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @php
                                                $passportPhotoUrl = resolveEmployeePassportPhoto($leaveReq->employee);
                                                $employeeInitial = resolveEmployeeInitials($leaveReq->employee);
                                                $employeeName = optional($leaveReq->employee)->display_name ?? 'Employee';
                                            @endphp
                                            <span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2">
                                                @if($passportPhotoUrl)
                                                    <img
                                                        src="{{ $passportPhotoUrl }}"
                                                        alt="{{ $employeeName }}"
                                                        class="hrms-emp-avatar-img"
                                                        onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');"
                                                    >
                                                    <span class="hrms-emp-avatar-fallback is-hidden">
                                                        {{ $employeeInitial }}
                                                    </span>
                                                @else
                                                    <span class="hrms-emp-avatar-fallback is-visible">
                                                        {{ $employeeInitial }}
                                                    </span>
                                                @endif
                                            </span>
                                            <div>
                                                <div style="font-weight: 800; color: var(--orb-text);">{{ optional($leaveReq->employee)->display_name }}</div>
                                                <div class="small text-muted">{{ $leaveReq->employee->employee_id ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $leaveReq->leave_type ?? 'General' }}</strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar-alt text-muted mr-1"></i> {{ \Carbon\Carbon::parse($leaveReq->from)->format('d M') }} - {{ \Carbon\Carbon::parse($leaveReq->to)->format('d M, Y') }}
                                    </td>
                                    <td>
                                        @php
                                            $f = \Carbon\Carbon::parse($leaveReq->from);
                                            $t = \Carbon\Carbon::parse($leaveReq->to);
                                            $d = $f->diffInDays($t) + 1;
                                        @endphp
                                        <span class="font-weight-bold">{{ $d }} Days</span>
                                    </td>
                                    <td class="status-cell">
                                        @php
                                            $status = strtoupper($leaveReq->status);
                                            $badgeClass = 'badge-pending';
                                            $label = 'Pending';
                                            if($status == 'APPROVED' || $status == 'ACCEPTED') { $badgeClass = 'badge-approved'; $label = 'Approved'; }
                                            elseif($status == 'REJECTED') { $badgeClass = 'badge-rejected'; $label = 'Rejected'; }
                                        @endphp
                                        <span class="leave-badge {{ $badgeClass }}">{{ $label }}</span>
                                    </td>
                                    <td class="text-right action-cell">
                                        <div class="leave-actions">
                                            <div class="dropdown">
                                                <button class="icon-btn" type="button" data-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right leave-action-menu">
                                                    <a class="dropdown-item" href="{{ route('employees-leave-request.show', $leaveReq->id) }}">
                                                        <i class="fas fa-eye text-primary"></i> View Details
                                                    </a>
                                                    @if($status == 'PENDING' || $status == 'WAITING_FOR_APPROVAL')
                                                        <div class="dropdown-divider"></div>
                                                        <button type="button" class="dropdown-item text-success approve-btn font-weight-bold" data-id="{{ $leaveReq->id }}">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                        <button type="button" class="dropdown-item text-danger reject-btn font-weight-bold" data-id="{{ $leaveReq->id }}">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-3 bg-light border-top">
                {{ method_exists($employeeLeaveRequests, 'links') ? $employeeLeaveRequests->links() : '' }}
            </div>
        </div>

    </div>
</div>

<!-- Reject Reason Modal -->
<div class="modal fade orb-type-modal" id="rejectReasonModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content leave-modal-content">
            <div class="modal-header leave-modal-header" style="background: linear-gradient(135deg, #e11d48, #be123c);">
                <div>
                    <h5 class="leave-modal-title">Reject Application</h5>
                    <div class="leave-modal-subtitle">Provide a reason for rejection.</div>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body leave-modal-body">
                <input type="hidden" id="reject-id">
                <div class="form-floating">
                    <textarea id="reject-comment" class="form-control" placeholder="Reason" style="height: 120px;" required></textarea>
                    <label>Reason for Rejection</label>
                </div>
            </div>
            <div class="modal-footer leave-modal-footer">
                <button type="button" class="leave-btn leave-btn-light" data-dismiss="modal">Cancel</button>
                <button type="button" id="confirm-reject" class="leave-btn leave-btn-danger"><i class="fas fa-times"></i> Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
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
                    if (response.success || response.status == 'success') {
                        $('#ajax-alert').fadeIn().find('#alert-message').text(response.message || 'Approved successfully');
                        const row = $(`#row-${id}`);
                        row.find('.status-cell').html(`<span class="leave-badge badge-approved">Approved</span>`);
                        row.find('.approve-btn, .reject-btn, .dropdown-divider').remove();
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
                if (response.success || response.status == 'success') {
                    $('#rejectReasonModal').modal('hide');
                    $('#ajax-alert').fadeIn().find('#alert-message').text(response.message || 'Rejected successfully');
                    const row = $(`#row-${id}`);
                    row.find('.status-cell').html(`<span class="leave-badge badge-rejected">Rejected</span>`);
                    row.find('.approve-btn, .reject-btn, .dropdown-divider').remove();
                    setTimeout(() => $('#ajax-alert').fadeOut(), 3000);
                }
            }
        });
    });
});
</script>
@endsection
