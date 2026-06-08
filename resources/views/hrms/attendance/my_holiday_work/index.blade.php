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

    @if($errors->any())
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
                            <th>Work Time</th>
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
                                @php
                                    $workTime = '-';
                                    if ($row->notes && preg_match('/^Hours:\s*([^\n]+)/', $row->notes, $matches)) {
                                        $workTime = trim($matches[1]);
                                    }
                                @endphp
                                {{ $workTime }}
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
                                <button type="button" class="ep-btn ep-btn-light js-view-details" data-row='@json($row)' style="height:32px;padding:0 12px; font-size:12px;">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
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
        <form method="POST" action="{{ route('hrms.attendance.my-holiday-work.store') }}" class="modal-content ep-form border-0 shadow-lg">
            @csrf
            <div class="ep-modal-header">
                <h5 class="modal-title">Apply Holiday Work Request</h5>
                <p>Submit details of holiday/weekoff work for HR approval.</p>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body">
                <div class="ep-form-group">
                    <label>Work Type <span class="text-danger">*</span></label>
                    <select name="work_type" id="work_type" class="form-control" required>
                        <option value="">Select Work Type</option>
                        <option value="holiday_work" @selected(old('work_type') === 'holiday_work')>Holiday Work</option>
                        <option value="weekoff_work" @selected(old('work_type') === 'weekoff_work')>Week-Off Work</option>
                    </select>
                </div>

                <div class="ep-form-group">
                    <label>Worked Dates <span class="text-danger">*</span></label>
                    <div id="worked-dates-container">
                        @if(old('worked_dates') && is_array(old('worked_dates')))
                            @foreach(old('worked_dates') as $index => $oldDate)
                                <div class="worked-date-row d-flex align-items-center mb-2">
                                    <input type="date" name="worked_dates[]" class="form-control" required value="{{ $oldDate }}" style="flex: 1; @if($index > 0) margin-right: 8px; @endif">
                                    @if($index > 0)
                                        <button type="button" class="btn btn-outline-danger remove-date-btn" style="height: calc(1.5em + .75rem + 2px); display: flex; align-items: center; justify-content: center; padding: 0 12px;" onclick="this.closest('.worked-date-row').remove();">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="worked-date-row d-flex align-items-center mb-2">
                                <input type="date" name="worked_dates[]" class="form-control" required value="{{ date('Y-m-d') }}">
                            </div>
                        @endif
                    </div>
                    <button type="button" id="add-date-btn" class="ep-btn ep-btn-light btn-sm mt-1" style="padding: 4px 12px; font-size: 13px;">
                        <i class="fas fa-plus-circle"></i> Add Date
                    </button>
                </div>

                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="ep-form-group">
                            <label>Work Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" required value="{{ old('start_time') ?? '09:00' }}">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="ep-form-group">
                            <label>Work End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" required value="{{ old('end_time') ?? '18:00' }}">
                        </div>
                    </div>
                </div>

                <div class="ep-form-group">
                    <label>Work Mode <span class="text-danger">*</span></label>
                    <select name="work_mode" class="form-control" required>
                        <option value="wfo" @selected(old('work_mode') === 'wfo')>WFO (Work From Office)</option>
                        <option value="wfh" @selected(old('work_mode') === 'wfh')>WFH (Work From Home)</option>
                    </select>
                </div>

                <div class="ep-form-group">
                    <label>Reason / Work Summary <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="reason" rows="3" required placeholder="Describe the reason or tasks completed...">{{ old('reason') }}</textarea>
                </div>

                <div class="ep-form-group">
                    <label>Additional Notes</label>
                    <textarea class="form-control" name="notes" rows="2" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="ep-modal-footer">
                <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                <button class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-check"></i> Submit Request</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================
     VIEW DETAILS MODAL
     ================================================== -->
<div class="modal fade" id="holidayWorkDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content ep-form border-0 shadow-lg">
            <div class="ep-modal-header">
                <h5 class="modal-title">Holiday Work Request Details</h5>
                <p>View complete submission history, status, and validation logs.</p>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="ep-modal-body">
                <div class="ep-section-card mb-3">
                    <div class="ep-section-title"><i class="fas fa-info-circle"></i> Request Details</div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Holiday Date</small>
                            <strong id="hw_worked_date">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Work Mode</small>
                            <strong id="hw_work_mode">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Work Type</small>
                            <strong id="hw_work_type">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Applied On</small>
                            <strong id="hw_applied_on">-</strong>
                        </div>
                        <div class="col-12 mb-2">
                            <small class="text-muted d-block">Reason / Work Summary</small>
                            <p class="mb-0 font-weight-bold text-dark" id="hw_reason" style="white-space: pre-wrap;"></p>
                        </div>
                        <div class="col-12 mb-2">
                            <small class="text-muted d-block">Notes / Hours Log</small>
                            <p class="mb-0 font-weight-bold text-dark" id="hw_notes" style="white-space: pre-wrap;"></p>
                        </div>
                    </div>
                </div>

                <div class="ep-section-card mb-0">
                    <div class="ep-section-title"><i class="fas fa-history"></i> Verification & Status</div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Approval Status</small>
                            <strong id="hw_status">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Comp Off Status</small>
                            <strong id="hw_comp_off">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Processed By</small>
                            <strong id="hw_processed_by">-</strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Processed At</small>
                            <strong id="hw_processed_at">-</strong>
                        </div>
                        <div class="col-12 mb-2" id="hw_rejection_reason_row">
                            <small class="text-muted d-block text-danger">Rejection Reason</small>
                            <strong class="text-danger" id="hw_rejection_reason">-</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ep-modal-footer">
                <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Close</button>
            </div>
        </div>
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
