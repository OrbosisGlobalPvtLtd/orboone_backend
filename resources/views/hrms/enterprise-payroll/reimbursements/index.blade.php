@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-receipt"></i> Enterprise Payroll</div>
            <h1>{{ $self ? 'My Reimbursements' : 'Reimbursements' }}</h1>
            <p>Submit, approve, reject and include approved reimbursements in payroll.</p>
        </div>
        <!-- <button class="ep-btn ep-btn-primary" data-toggle="modal" data-target="#reimbursementModal"><i class="fas fa-plus"></i> Add Claim</button> -->
    </div>
    @include('hrms.enterprise-payroll.partials.flash')

    <div class="ep-card">
        <!-- Table Card Header -->
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-receipt"></i></div>
                <div>
                    <h5 class="ep-table-title">{{ $self ? 'My Reimbursements' : 'Reimbursements' }}</h5>
                    <p class="ep-table-subtitle">Submit, approve, reject and include approved reimbursements in payroll.</p>
                </div>
            </div>
            <div class="ep-hero-actions">
                <button class="ep-btn ep-btn-gradient" data-toggle="modal" data-target="#reimbursementModal"><i class="fas fa-plus"></i> Add Claim</button>
            </div>
        </div>

        <!-- Attached Filters -->
        <div class="ep-card-filters">
            <form method="GET" action="{{ $self ? route('enterprise-payroll.self.reimbursements') : route('enterprise-payroll.reimbursements.index') }}" class="row align-items-end ep-form" id="filterForm">
                @if(!$self)
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Employee</label>
                    <select name="employee_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Employees</option>
                        @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Date From</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Date To</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-2 text-right">
                    <a href="{{ $self ? route('enterprise-payroll.self.reimbursements') : route('enterprise-payroll.reimbursements.index') }}" class="ep-btn ep-btn-light w-100"><i class="fas fa-sync-alt"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="ep-card-body p-0">
            <div class="ep-table-wrap">
                <table class="table ep-table js-orb-datatable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Employee</th>
                            <th>Title</th>
                            <th>Claim Date</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Approved</th>
                            <th>Status</th>
                            <th>Attachment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($rows) && count($rows) > 0)
                        @foreach($rows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row->employee_display_name }}</td>
                            <td>{{ $row->title }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->claim_date)->format('d M Y') }}</td>
                            <td class="text-right">₹{{ number_format((float) $row->amount, 2) }}</td>
                            <td class="text-right">₹{{ number_format((float) $row->approved_amount, 2) }}</td>
                            <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $row->status])</td>
                            <td>@if($row->attachment_path)<a class="btn btn-sm btn-link font-weight-bold" href="{{ route('hrms.documents.file', ['path' => $row->attachment_path]) }}" target="_blank"><i class="fas fa-paperclip"></i> Preview</a>@else - @endif</td>
                            <td>
                                @if($canManage && $row->status === 'pending')
                                <form method="POST" action="{{ route('enterprise-payroll.reimbursements.approve', $row->id) }}" class="d-inline">@csrf<input type="hidden" name="approved_amount" value="{{ $row->amount }}"><button class="ep-btn ep-btn-light" style="height: 30px; padding: 0 8px;" title="Approve"><i class="fas fa-check text-success"></i></button></form>
                                <form method="POST" action="{{ route('enterprise-payroll.reimbursements.reject', $row->id) }}" class="d-inline">@csrf<button class="ep-btn ep-btn-light" style="height: 30px; padding: 0 8px;" title="Reject"><i class="fas fa-times text-danger"></i></button></form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reimbursementModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" enctype="multipart/form-data" action="{{ route('enterprise-payroll.reimbursements.store') }}" class="modal-content ep-form border-0 shadow-lg">
                @csrf
                <div class="ep-modal-header">
                    <h5 class="modal-title">Add Reimbursement</h5>
                    <p>Submit and manage employee reimbursement claims.</p>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="ep-modal-body">
                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-receipt"></i> Claim Details</div>
                        <div class="row">
                            @if($canManage)<div class="col-md-6 mb-3">
                                <div class="ep-form-group"><label>Employee</label><select name="employee_id" class="form-control" required>
                                        <option value="">Select</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->display_name }}</option>@endforeach
                                    </select></div>
                            </div>@endif
                            <div class="col-md-{{ $canManage ? '6' : '12' }} mb-3">
                                <div class="ep-form-group"><label>Title</label><input name="title" class="form-control" required></div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="ep-form-group"><label>Claim Date</label><input type="date" name="claim_date" value="{{ now('Asia/Kolkata')->toDateString() }}" class="form-control" required></div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="ep-form-group"><label>Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
                            </div>
                            @if($canManage)<div class="col-md-4">
                                <div class="ep-form-group"><label>Approved Amount</label><input type="number" step="0.01" name="approved_amount" class="form-control"></div>
                            </div>@endif
                        </div>
                    </div>
                    <div class="ep-section-card mb-0">
                        <div class="ep-section-title"><i class="fas fa-paperclip"></i> Attachment & Notes</div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="ep-form-group"><label>Attachment</label><input type="file" name="attachment" class="form-control p-1"></div>
                            </div>
                            <div class="col-md-12">
                                <div class="ep-form-group"><label>Remarks</label><input name="remarks" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ep-modal-footer">
                    <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                    <button class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-save"></i> Save Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script>
    if (window.jQuery && $.fn.DataTable) {
        $('.js-orb-datatable').each(function() {
            var $table = $(this);
            $table.DataTable({
                pageLength: 25,
                order: [],
                searching: false,
                lengthChange: true,
                autoWidth: false,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                language: {
                    emptyTable: 'No reimbursement claims found.',
                    zeroRecords: 'No matching records found.'
                },
                dom: '<"crud-dt-toolbar"<"crud-dt-left"l><"crud-dt-right"B>>rt<"orb-table-footer"ip>',
                buttons: [{
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv text-muted"></i> CSV',
                        className: 'crud-export-btn'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel text-success"></i> Excel',
                        className: 'crud-export-btn'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                        className: 'crud-export-btn'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print text-primary"></i> Print',
                        className: 'crud-export-btn'
                    }
                ]
            });
        });
    }
</script>
@endsection
