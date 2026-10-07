@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-gift"></i> Enterprise Payroll</div>
            <h1>Bonus & Incentives</h1>
            <p>Approve monthly bonus and incentive entries before payroll inclusion.</p>
        </div>
        <!-- @if($canManage)<button class="ep-btn ep-btn-primary" data-toggle="modal" data-target="#bonusModal"><i class="fas fa-plus"></i> Add Entry</button>@endif -->
    </div>
    @include('hrms.enterprise-payroll.partials.flash')

    <div class="ep-card">
        <!-- Table Card Header -->
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-gift"></i></div>
                <div>
                    <h5 class="ep-table-title">Bonus & Incentives</h5>
                    <p class="ep-table-subtitle">Approve monthly bonus and incentive entries before payroll inclusion.</p>
                </div>
            </div>
            <div class="ep-hero-actions">
                @if($canManage)
                <button class="ep-btn ep-btn-gradient" data-toggle="modal" data-target="#bonusModal"><i class="fas fa-plus"></i> Add Entry</button>
                @endif
            </div>
        </div>

        <!-- Attached Filters -->
        <div class="ep-card-filters">
            <form method="GET" action="{{ route('enterprise-payroll.bonus-incentives.index') }}" class="row align-items-end ep-form" id="filterForm">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Employee</label>
                    <select name="employee_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Employees</option>
                        @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Type</label>
                    <select name="type" class="form-control" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="bonus" {{ request('type') == 'bonus' ? 'selected' : '' }}>Bonus</option>
                        <option value="incentive" {{ request('type') == 'incentive' ? 'selected' : '' }}>Incentive</option>
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
                <div class="col-md-3 text-right">
                    <a href="{{ route('enterprise-payroll.bonus-incentives.index') }}" class="ep-btn ep-btn-light w-100"><i class="fas fa-sync-alt"></i> Reset</a>
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
                            <th>Type</th>
                            <th>Title</th>
                            <th>Target Range</th>
                            <th class="text-right">Target</th>
                            <th class="text-right">Achievement</th>
                            <th class="text-right">Amount</th>
                            <th>Period</th>
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
                            <td>{{ ucfirst($row->type) }}</td>
                            <td>{{ $row->title }}</td>
                            <td>{{ $row->target_range }}</td>
                            <td class="text-right">₹{{ number_format((float) $row->target_amount, 2) }}</td>
                            <td class="text-right">₹{{ number_format((float) $row->achievement_amount, 2) }}</td>
                            <td class="text-right font-weight-bold text-primary">₹{{ number_format((float) $row->amount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::create()->month($row->month)->format('F') }} {{ $row->year }}</td>
                            <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $row->status])</td>
                            <td>
                                @if($canManage && $row->status === 'pending')
                                <form method="POST" action="{{ route('enterprise-payroll.bonus-incentives.approve', $row->id) }}" class="d-inline">@csrf<button class="ep-btn ep-btn-light" style="height: 30px; padding: 0 8px;" title="Approve"><i class="fas fa-check text-success"></i></button></form>
                                <form method="POST" action="{{ route('enterprise-payroll.bonus-incentives.reject', $row->id) }}" class="d-inline">@csrf<button class="ep-btn ep-btn-light" style="height: 30px; padding: 0 8px;" title="Reject"><i class="fas fa-times text-danger"></i></button></form>
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
    <div class="modal fade" id="bonusModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="{{ route('enterprise-payroll.bonus-incentives.store') }}" class="modal-content ep-form border-0 shadow-lg">
                @csrf
                <div class="ep-modal-header">
                    <h5 class="modal-title">Add Bonus/Incentive</h5>
                    <p>Approve monthly bonus and incentive entries.</p>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="ep-modal-body">
                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-gift"></i> Employee & Incentive Details</div>
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                                <div class="ep-form-group"><label>Employee</label><select name="employee_id" class="form-control" required>
                                        <option value="">Select</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->display_name }}</option>@endforeach
                                    </select></div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                                <div class="ep-form-group"><label>Type</label><select name="type" class="form-control">
                                        <option value="bonus">Bonus</option>
                                        <option value="incentive">Incentive</option>
                                    </select></div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                                <div class="ep-form-group"><label>Title</label><input name="title" class="form-control" required></div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                                <div class="ep-form-group"><label>Quarterly Target</label><select name="target_range" class="form-control">@for($i=1;$i<=10;$i++)<option value="{{ $i }} Lac">{{ $i }} Lac</option>@endfor</select></div>
                            </div>
                        </div>
                    </div>
                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-coins"></i> Target & Amount</div>
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                                <div class="ep-form-group"><label>Target Amount</label><input type="number" step="0.01" name="target_amount" class="form-control"></div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                                <div class="ep-form-group"><label>Achievement Amount</label><input type="number" step="0.01" name="achievement_amount" class="form-control"></div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                                <div class="ep-form-group"><label>Approved Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                                <div class="ep-form-group">
                                    <label>Month</label>
                                    <select name="month" class="form-control" required>
                                        @for($i=1; $i<=12; $i++)
                                            <option value="{{ $i }}" {{ now('Asia/Kolkata')->month == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->format('F') }}</option>
                                            @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
                                <div class="ep-form-group"><label>Year</label><input type="number" min="2020" name="year" value="{{ now('Asia/Kolkata')->year }}" class="form-control" style="min-width: 120px;" required></div>
                            </div>
                        </div>
                    </div>
                    <div class="ep-section-card mb-0">
                        <div class="ep-section-title"><i class="fas fa-sticky-note"></i> Notes</div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="ep-form-group"><label>Remarks</label><input name="remarks" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ep-modal-footer">
                    <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                    <button class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-save"></i> Save Entry</button>
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
                    emptyTable: 'No bonus or incentive entries found.',
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