@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'attendance'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-calendar-day"></i> Attendance & Time Tracking</div>
            <h1>My Holiday Work Requests</h1>
            <p>Request approval for working on holidays or weekoffs and track comp-off generation status.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background: #ECFDF3; color: #027A48;">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background: #FEF3F2; color: #B42318;">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background: #FEF3F2; color: #B42318;">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="ep-card">
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <h5 class="ep-table-title">My Holiday Work Request History</h5>
                    <p class="ep-table-subtitle">Apply for new requests or check status and details of previous submissions.</p>
                </div>
            </div>
            <div>
                <button type="button" class="ep-btn ep-btn-gradient" data-toggle="modal" data-target="#applyHolidayWorkModal">
                    <i class="fas fa-plus-circle"></i> Apply Work Request
                </button>
            </div>
        </div>

        <div class="ep-card-body p-0">
            <div class="ep-table-wrap">
                <table class="table ep-table">
                     <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Worked Date</th>
                            <th>Work Type</th>
                            <th>Work Mode</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Comp Off Status</th>
                            <th>Applied On</th>
                            <th class="text-right pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $row)
                        <tr>
                            <td>{{ $loop->iteration + ($requests->currentPage() - 1) * $requests->perPage() }}</td>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($row->worked_date)->format('d M Y') }}</strong>
                            </td>
                            <td>
                                {{ str_contains(strtolower($row->work_type), 'weekoff') ? 'Week-Off Work' : 'Holiday Work' }}
                            </td>
                            <td>
                                <span class="ep-badge {{ strtolower($row->work_mode) === 'wfh' ? 'ep-badge-primary' : 'ep-badge-success' }}">
                                    {{ strtoupper($row->work_mode ?? 'wfo') }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" title="{{ $row->reason }}">{{ Str::limit($row->reason, 40) }}</span>
                            </td>
                            <td>
                                @if($row->status === 'pending')
                                    <span class="ep-badge ep-badge-warning">Pending</span>
                                @elseif($row->status === 'approved')
                                    <span class="ep-badge ep-badge-success">Approved</span>
                                @elseif($row->status === 'rejected')
                                    <span class="ep-badge ep-badge-danger">Rejected</span>
                                @elseif($row->status === 'cancelled')
                                    <span class="ep-badge ep-badge-secondary">Cancelled</span>
                                @else
                                    <span class="ep-badge ep-badge-danger">{{ ucfirst($row->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($row->comp_off_generated || $row->comp_off_id)
                                    <span class="ep-badge ep-badge-success"><i class="fas fa-check-circle mr-1"></i> Generated</span>
                                @elseif($row->status === 'approved')
                                    <span class="ep-badge ep-badge-warning"><i class="fas fa-clock mr-1"></i> Pending Verification</span>
                                @elseif($row->status === 'pending')
                                    <span class="ep-badge ep-badge-secondary">Awaiting Approval</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $row->created_at ? $row->created_at->format('d M Y h:i A') : 'N/A' }}
                            </td>
                            <td class="text-right pr-4">
                                <div class="d-flex align-items-center justify-content-end" style="gap: 8px;">
                                    <button type="button" class="btn btn-sm btn-light border shadow-sm js-view-details" data-row='@json($row)' title="View Details" style="width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; padding:0;">
                                        <i class="fas fa-eye text-primary"></i>
                                    </button>

                                    @if($row->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-outline-warning shadow-sm js-edit-request" data-row='@json($row)' title="Edit Request" style="width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; padding:0;">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form method="POST" action="{{ route('hrms.attendance.my-holiday-work.cancel', $row->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" title="Cancel Request" style="width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; padding:0;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-light"></i>
                                <p class="mb-0 font-weight-bold">No holiday work requests found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($requests->hasPages())
    <div class="mt-3">
        {{ $requests->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- ==================================================
     APPLY HOLIDAY WORK REQUEST MODAL
     ================================================== -->
<div class="modal fade" id="applyHolidayWorkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('hrms.attendance.my-holiday-work.store') }}" class="modal-content ep-form border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            @csrf
            <div class="ep-modal-header" style="background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); color: #fff; padding: 20px 24px;">
                <h5 class="modal-title font-weight-bold text-white mb-1"><i class="fas fa-calendar-plus mr-2"></i> Apply Holiday Work Request</h5>
                <p class="mb-0 text-white-50" style="font-size: 13px;">Submit details of holiday/weekoff work for HR approval.</p>
                <button type="button" class="close text-white opacity-100" data-dismiss="modal" style="margin-top: -30px;"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body" style="padding: 24px;">
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="ep-form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Work Type <span class="text-danger">*</span></label>
                            <select name="work_type" id="work_type" class="form-control shadow-none" style="border-radius: 10px; height: 42px;" required>
                                <option value="">Select Work Type</option>
                                <option value="holiday_work" @selected(old('work_type') === 'holiday_work')>Holiday Work</option>
                                <option value="weekoff_work" @selected(old('work_type') === 'weekoff_work')>Week-Off Work</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="ep-form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Work Mode <span class="text-danger">*</span></label>
                            <select name="work_mode" class="form-control shadow-none" style="border-radius: 10px; height: 42px;" required>
                                <option value="wfo" @selected(old('work_mode') === 'wfo')>WFO (Work From Office)</option>
                                <option value="wfh" @selected(old('work_mode') === 'wfh')>WFH (Work From Home)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="ep-form-group mb-3">
                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Worked Dates <span class="text-danger">*</span></label>
                    <div id="worked-dates-container">
                        @if(old('worked_dates') && is_array(old('worked_dates')))
                            @foreach(old('worked_dates') as $index => $oldDate)
                                <div class="worked-date-row d-flex align-items-center mb-2">
                                    <input type="date" name="worked_dates[]" class="form-control shadow-none" required value="{{ $oldDate }}" style="flex: 1; border-radius: 10px; height: 42px; @if($index > 0) margin-right: 8px; @endif">
                                    @if($index > 0)
                                        <button type="button" class="btn btn-outline-danger remove-date-btn" style="height: 42px; width: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; padding: 0;" onclick="this.closest('.worked-date-row').remove();">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="worked-date-row d-flex align-items-center mb-2">
                                <input type="date" name="worked_dates[]" class="form-control shadow-none" style="border-radius: 10px; height: 42px;" required value="{{ date('Y-m-d') }}">
                            </div>
                        @endif
                    </div>
                    <button type="button" id="add-date-btn" class="btn btn-sm btn-light border shadow-sm font-weight-bold text-primary mt-1" style="border-radius: 8px; padding: 6px 14px; font-size: 12px;">
                        <i class="fas fa-plus-circle mr-1"></i> Add Date
                    </button>
                </div>

                <!-- Hidden default work times -->
                <input type="hidden" name="start_time" value="09:00">
                <input type="hidden" name="end_time" value="18:00">

                <div class="ep-form-group mb-3">
                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Reason / Work Summary <span class="text-danger">*</span></label>
                    <textarea class="form-control shadow-none" name="reason" rows="3" style="border-radius: 10px;" required placeholder="Describe the reason or tasks completed...">{{ old('reason') }}</textarea>
                </div>

                <div class="ep-form-group mb-0">
                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Additional Notes</label>
                    <textarea class="form-control shadow-none" name="notes" rows="2" style="border-radius: 10px;" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="ep-modal-footer bg-light" style="padding: 16px 24px; border-top: 1px solid #E5E7EB;">
                <button type="button" class="btn btn-light border font-weight-bold" data-dismiss="modal" style="border-radius: 10px; padding: 8px 18px;">Cancel</button>
                <button class="btn btn-primary font-weight-bold border-0 shadow-sm" style="border-radius: 10px; padding: 8px 22px; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);"><i class="fas fa-check mr-1"></i> Submit Request</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================
     VIEW DETAILS MODAL
     ================================================== -->
<div class="modal fade" id="holidayWorkDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content ep-form border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="ep-modal-header" style="background: linear-gradient(135deg, #1E293B 0%, #334155 100%); color: #fff; padding: 20px 24px;">
                <h5 class="modal-title font-weight-bold text-white mb-1"><i class="fas fa-info-circle mr-2"></i> Holiday Work Request Details</h5>
                <p class="mb-0 text-white-50" style="font-size: 13px;">View complete submission history, status, and validation logs.</p>
                <button type="button" class="close text-white opacity-100" data-dismiss="modal" style="margin-top: -30px;"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body" style="padding: 24px;">
                <div class="ep-section-card mb-3 p-3" style="background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0;">
                    <div class="ep-section-title font-weight-bold text-primary mb-2" style="font-size: 14px;"><i class="fas fa-file-alt mr-1"></i> Request Details</div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Holiday Date</small>
                            <strong id="hw_worked_date" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Work Mode</small>
                            <strong id="hw_work_mode" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Work Type</small>
                            <strong id="hw_work_type" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Applied On</small>
                            <strong id="hw_applied_on" class="text-dark">-</strong>
                        </div>
                        <div class="col-12 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Reason / Work Summary</small>
                            <p class="mb-0 font-weight-bold text-dark" id="hw_reason" style="white-space: pre-wrap; font-size: 13px;"></p>
                        </div>
                        <div class="col-12 mb-0">
                            <small class="text-muted d-block" style="font-size: 11px;">Notes</small>
                            <p class="mb-0 font-weight-bold text-dark" id="hw_notes" style="white-space: pre-wrap; font-size: 13px;"></p>
                        </div>
                    </div>
                </div>

                <div class="ep-section-card mb-0 p-3" style="background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0;">
                    <div class="ep-section-title font-weight-bold text-primary mb-2" style="font-size: 14px;"><i class="fas fa-history mr-1"></i> Verification & Status</div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Approval Status</small>
                            <strong id="hw_status" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Comp Off Status</small>
                            <strong id="hw_comp_off" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Processed By</small>
                            <strong id="hw_processed_by" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block" style="font-size: 11px;">Processed At</small>
                            <strong id="hw_processed_at" class="text-dark">-</strong>
                        </div>
                        <div class="col-12 mb-0" id="hw_rejection_reason_row">
                            <small class="text-muted d-block text-danger" style="font-size: 11px;">Rejection Reason</small>
                            <strong class="text-danger" id="hw_rejection_reason">-</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ep-modal-footer bg-light" style="padding: 14px 24px; border-top: 1px solid #E5E7EB;">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal" style="border-radius: 10px; padding: 8px 20px;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================================================
     EDIT HOLIDAY WORK REQUEST MODAL
     ================================================== -->
<div class="modal fade" id="editHolidayWorkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <form id="editHolidayWorkForm" method="POST" action="" class="modal-content ep-form border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            @csrf
            @method('PUT')
            <div class="ep-modal-header" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color: #fff; padding: 20px 24px;">
                <h5 class="modal-title font-weight-bold text-white mb-1"><i class="fas fa-edit mr-2"></i> Edit Holiday Work Request</h5>
                <p class="mb-0 text-white-50" style="font-size: 13px;">Update your pending work request details.</p>
                <button type="button" class="close text-white opacity-100" data-dismiss="modal" style="margin-top: -30px;"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body" style="padding: 24px;">
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="ep-form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Work Type <span class="text-danger">*</span></label>
                            <select name="work_type" id="edit_work_type" class="form-control shadow-none" style="border-radius: 10px; height: 42px;" required>
                                <option value="holiday_work">Holiday Work</option>
                                <option value="weekoff_work">Week-Off Work</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="ep-form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Work Mode <span class="text-danger">*</span></label>
                            <select name="work_mode" id="edit_work_mode" class="form-control shadow-none" style="border-radius: 10px; height: 42px;" required>
                                <option value="wfo">WFO (Work From Office)</option>
                                <option value="wfh">WFH (Work From Home)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="ep-form-group mb-3">
                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Worked Date <span class="text-danger">*</span></label>
                    <input type="date" name="worked_date" id="edit_worked_date" class="form-control shadow-none" style="border-radius: 10px; height: 42px;" required>
                </div>

                <div class="ep-form-group mb-3">
                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Reason / Work Summary <span class="text-danger">*</span></label>
                    <textarea class="form-control shadow-none" name="reason" id="edit_reason" rows="3" style="border-radius: 10px;" required placeholder="Describe the reason or tasks completed..."></textarea>
                </div>

                <div class="ep-form-group mb-0">
                    <label class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Additional Notes</label>
                    <textarea class="form-control shadow-none" name="notes" id="edit_notes" rows="2" style="border-radius: 10px;" placeholder="Any additional notes..."></textarea>
                </div>
            </div>
            <div class="ep-modal-footer bg-light" style="padding: 16px 24px; border-top: 1px solid #E5E7EB;">
                <button type="button" class="btn btn-light border font-weight-bold" data-dismiss="modal" style="border-radius: 10px; padding: 8px 18px;">Cancel</button>
                <button class="btn btn-warning text-white font-weight-bold border-0 shadow-sm" style="border-radius: 10px; padding: 8px 22px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);"><i class="fas fa-save mr-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('_script')
<script>
    (function() {
        document.querySelectorAll('.js-view-details').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var row = JSON.parse(this.getAttribute('data-row') || '{}');
                var fmt = function(v) { return v ? String(v) : '-'; };
                
                // Format Worked Date
                var workedDateStr = '-';
                if (row.worked_date) {
                    var d = new Date(row.worked_date);
                    workedDateStr = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                }

                // Format Applied On Date
                var appliedOnStr = '-';
                if (row.created_at) {
                    var d = new Date(row.created_at);
                    appliedOnStr = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + 
                                   ' ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                }

                document.getElementById('hw_worked_date').textContent = workedDateStr;
                document.getElementById('hw_work_mode').textContent = fmt(row.work_mode).toUpperCase();
                document.getElementById('hw_work_type').textContent = strContains(fmt(row.work_type), 'weekoff') ? 'Week-Off Work' : 'Holiday Work';
                document.getElementById('hw_applied_on').textContent = appliedOnStr;
                document.getElementById('hw_reason').textContent = fmt(row.reason);
                document.getElementById('hw_notes').textContent = fmt(row.notes);
                
                // Status badge
                var statusStr = fmt(row.status).toUpperCase();
                document.getElementById('hw_status').textContent = statusStr;

                // Comp off status
                var compOffStr = 'Awaiting Approval';
                if (row.comp_off_generated || row.comp_off_id) {
                    compOffStr = 'GENERATED';
                } else if (row.status === 'approved') {
                    compOffStr = 'PENDING ATTENDANCE VERIFICATION';
                } else if (row.status === 'rejected') {
                    compOffStr = 'N/A';
                }
                document.getElementById('hw_comp_off').textContent = compOffStr;

                // Processed logs
                document.getElementById('hw_processed_by').textContent = fmt(row.approved_by_user_id ? 'HR Admin (#' + row.approved_by_user_id + ')' : '-');
                
                var processedAtStr = '-';
                if (row.approved_at) {
                    var d = new Date(row.approved_at);
                    processedAtStr = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + 
                                     ' ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                }
                document.getElementById('hw_processed_at').textContent = processedAtStr;

                // Rejection reason row control
                var rejRow = document.getElementById('hw_rejection_reason_row');
                if (row.status === 'rejected' && row.rejection_reason) {
                    rejRow.style.display = 'block';
                    document.getElementById('hw_rejection_reason').textContent = row.rejection_reason;
                } else {
                    rejRow.style.display = 'none';
                }

                $('#holidayWorkDetailsModal').modal('show');
            });
        });

        document.querySelectorAll('.js-edit-request').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var row = JSON.parse(this.getAttribute('data-row') || '{}');
                var updateUrl = "{{ route('hrms.attendance.my-holiday-work.update', ':id') }}".replace(':id', row.id);
                document.getElementById('editHolidayWorkForm').setAttribute('action', updateUrl);
                document.getElementById('edit_work_type').value = row.work_type || 'holiday_work';
                
                // Parse date cleanly into YYYY-MM-DD format for HTML date input
                var rawDate = (row.worked_date || '').toString();
                var cleanDate = rawDate.split('T')[0].split(' ')[0];
                document.getElementById('edit_worked_date').value = cleanDate;

                document.getElementById('edit_work_mode').value = (row.work_mode || 'wfo').toLowerCase();
                document.getElementById('edit_reason').value = row.reason || '';
                document.getElementById('edit_notes').value = row.notes || '';
                $('#editHolidayWorkModal').modal('show');
            });
        });

        // Dynamic worked dates rows builder
        var container = document.getElementById('worked-dates-container');
        var addBtn = document.getElementById('add-date-btn');
        if (addBtn && container) {
            addBtn.addEventListener('click', function() {
                var row = document.createElement('div');
                row.className = 'worked-date-row d-flex align-items-center mb-2';
                
                var input = document.createElement('input');
                input.type = 'date';
                input.name = 'worked_dates[]';
                input.className = 'form-control';
                input.required = true;
                input.style.flex = '1';
                input.style.marginRight = '8px';
                
                var today = new Date();
                var yyyy = today.getFullYear();
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var dd = String(today.getDate()).padStart(2, '0');
                input.value = yyyy + '-' + mm + '-' + dd;
                
                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-outline-danger remove-date-btn';
                removeBtn.style.height = 'calc(1.5em + .75rem + 2px)';
                removeBtn.style.display = 'flex';
                removeBtn.style.alignItems = 'center';
                removeBtn.style.justifyContent = 'center';
                removeBtn.style.padding = '0 12px';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                
                removeBtn.addEventListener('click', function() {
                    row.remove();
                });
                
                row.appendChild(input);
                row.appendChild(removeBtn);
                container.appendChild(row);
            });
        }

        function strContains(str, search) {
            return str.toLowerCase().indexOf(search.toLowerCase()) !== -1;
        }
    })();
</script>
@endsection
