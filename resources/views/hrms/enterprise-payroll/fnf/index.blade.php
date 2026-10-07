@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-hand-holding-usd"></i> Enterprise Payroll</div>
            <h1>FNF Settlements</h1>
            <p>Draft and approve final settlement values without touching locked payroll.</p>
        </div>
        <!-- @if($canManage)<button class="ep-btn ep-btn-primary" data-toggle="modal" data-target="#fnfModal"><i class="fas fa-plus"></i> Add FNF</button>@endif -->
    </div>
    @include('hrms.enterprise-payroll.partials.flash')

    <div class="ep-card">
        <!-- Table Card Header -->
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-user-minus"></i></div>
                <div>
                    <h5 class="ep-table-title">FNF Settlements</h5>
                    <p class="ep-table-subtitle">Draft and approve final settlement values without touching locked payroll.</p>
                </div>
            </div>
            <div class="ep-hero-actions">
                @if($canManage)
                <button class="ep-btn ep-btn-gradient" data-toggle="modal" data-target="#fnfModal"><i class="fas fa-plus"></i> Add FNF</button>
                @endif
            </div>
        </div>

        <!-- Attached Filters -->
        <div class="ep-card-filters">
            <form method="GET" action="{{ route('enterprise-payroll.fnf.index') }}" class="row align-items-end ep-form" id="filterForm">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Employee</label>
                    <select name="employee_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Employees</option>
                        @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Month</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        <option value="">All Months</option>
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->format('F') }}</option>
                            @endfor
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="{{ request('year') }}" onkeyup="if(event.keyCode === 13) this.form.submit()" placeholder="Year">
                </div>
                <div class="col-md-2 text-right">
                    <a href="{{ route('enterprise-payroll.fnf.index') }}" class="ep-btn ep-btn-light w-100"><i class="fas fa-sync-alt"></i> Reset</a>
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
                            <th>Period</th>
                            <th class="text-right">Pending Salary</th>
                            <th class="text-right">Leave Encashment</th>
                            <th class="text-right">Reimbursement</th>
                            <th class="text-right">Deductions</th>
                            <th class="text-right">Final Payable</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($rows) && count($rows) > 0)
                        @foreach($rows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row->employee_display_name }}</td>
                            <td>{{ \Carbon\Carbon::create()->month($row->settlement_month)->format('F') }} {{ $row->settlement_year }}</td>
                            <td class="text-right">₹{{ number_format((float) $row->pending_salary, 2) }}</td>
                            <td class="text-right">₹{{ number_format((float) $row->leave_encashment, 2) }}</td>
                            <td class="text-right">₹{{ number_format((float) $row->reimbursement_amount, 2) }}</td>
                            <td class="text-right text-danger">₹{{ number_format((float) $row->deductions, 2) }}</td>
                            <td class="text-right font-weight-bold text-primary">₹{{ number_format((float) $row->final_payable, 2) }}</td>
                            <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $row->status])</td>
                            <td>
                                @if($canManage && $row->status === 'draft')
                                <form method="POST" action="{{ route('enterprise-payroll.fnf.approve', $row->id) }}">
                                    @csrf
                                    <button class="ep-btn ep-btn-light" style="height: 30px; padding: 0 8px;" title="Approve"><i class="fas fa-check text-success"></i></button>
                                </form>
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

    @if($canManage)
    <div class="modal fade" id="fnfModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="{{ route('enterprise-payroll.fnf.store') }}" class="modal-content ep-form border-0 shadow-lg">
                @csrf
                <div class="ep-modal-header">
                    <h5 class="modal-title">Add FNF Settlement</h5>
                    <p>Draft and approve final settlement values.</p>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="ep-modal-body">
                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-user-tie"></i> Employee & Period</div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="ep-form-group"><label>Employee</label><select name="employee_id" class="form-control" required>
                                        <option value="">Select</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->display_name }}</option>@endforeach
                                    </select></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="ep-form-group">
                                    <label>Month</label>
                                    <select name="settlement_month" class="form-control" required>
                                        @for($i=1; $i<=12; $i++)
                                            <option value="{{ $i }}" {{ now('Asia/Kolkata')->month == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->format('F') }}</option>
                                            @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="ep-form-group"><label>Year</label><input type="number" min="2020" name="settlement_year" class="form-control" value="{{ now('Asia/Kolkata')->year }}" required></div>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="ep-form-group"><label>Exit Process ID</label><input type="number" name="exit_process_id" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-coins"></i> Settlement Amounts</div>
                        <div class="row">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="ep-form-group"><label>Pending Salary</label><input type="number" step="0.01" name="pending_salary" class="form-control"></div>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="ep-form-group"><label>Leave Encashment</label><input type="number" step="0.01" name="leave_encashment" class="form-control"></div>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="ep-form-group"><label>Reimbursement</label><input type="number" step="0.01" name="reimbursement_amount" class="form-control"></div>
                            </div>
                            <div class="col-md-3">
                                <div class="ep-form-group"><label>Deductions</label><input type="number" step="0.01" name="deductions" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                    <div class="ep-section-card mb-0">
                        <div class="ep-section-title"><i class="fas fa-sticky-note"></i> Final Notes</div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="ep-form-group"><label>Remarks</label><input name="remarks" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ep-modal-footer">
                    <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                    <button class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-save"></i> Save Settlement</button>
                </div>
            </form>
        </div>
    </div>
    @endif
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
                    emptyTable: 'No FNF settlements found.',
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