    @extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

    @section('_head')
    @include('hrms.enterprise-payroll.partials.styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
    @endsection

    @section('_content')
    <div class="ep-page">
        <div class="ep-hero">
            <div><div class="ep-kicker"><i class="fas fa-chart-line"></i> Report</div><h1>{{ $title }}</h1><p>Filtered for {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}.</p></div>
            <div><a href="{{ route('enterprise-payroll.reports.index') }}" class="ep-btn ep-btn-light"><i class="fas fa-arrow-left"></i> Back to Reports</a></div>
        </div>
        <div class="ep-card">
            <!-- Table Card Header -->
            <div class="ep-table-header">
                <div class="ep-table-head-left">
                    <div class="ep-icon-box"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <h5 class="ep-table-title">{{ $title }}</h5>
                        <p class="ep-table-subtitle">Dynamic report data for {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}.</p>
                    </div>
                </div>
                <div class="ep-hero-actions d-flex align-items-center gap-2">
                    <input type="text" class="form-control ep-table-search" style="width: 200px; height: 38px; border-radius: 9px;" placeholder="Search report...">
                </div>
            </div>

            <!-- Attached Filters -->
            <div class="ep-card-filters">
                <form method="GET" class="row align-items-end ep-form" id="filterForm">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label>Month</label>
                        <select name="month" class="form-control" onchange="this.form.submit()">
                            <option value="">All Months</option>
                            @for($i=1; $i<=12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label>Year</label>
                        <input type="number" name="year" min="2020" value="{{ $year }}" class="form-control" onkeyup="if(event.keyCode === 13) this.form.submit()">
                    </div>
                    <div class="col-md-3 text-right">
                        <a href="{{ url()->current() }}" class="ep-btn ep-btn-light w-100"><i class="fas fa-sync-alt"></i> Reset</a>
                    </div>
                </form>
            </div>

            <div class="ep-card-body p-0">
                <div class="ep-table-wrap">
                    <table class="table ep-table js-orb-datatable" style="width: 100%">
                        <thead>
                            @if($type === 'employee-salary')
                                <tr>
                                    <th>S.No.</th>
                                    <th>Employee</th>
                                    <th class="text-right">Annual CTC</th>
                                    <th class="text-right">Monthly CTC</th>
                                    <th class="text-right">Basic</th>
                                    <th class="text-right">HRA</th>
                                    <th class="text-right">Special</th>
                                    <th class="text-right">PT</th>
                                    <th class="text-right">TDS</th>
                                    <th>Effective From</th>
                                </tr>
                            @elseif($type === 'reimbursement')
                                <tr>
                                    <th>S.No.</th>
                                    <th>Employee</th>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Approved</th>
                                    <th>Status</th>
                                </tr>
                            @elseif($type === 'bonus-incentive')
                                <tr>
                                    <th>S.No.</th>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th class="text-right">Amount</th>
                                    <th>Status</th>
                                </tr>
                            @else
                                <tr>
                                    <th>S.No.</th>
                                    <th>Employee</th>
                                    <th>Code</th>
                                    <th>Month</th>
                                    <th>Payable</th>
                                    <th>Present</th>
                                    <th>Paid Lv</th>
                                    <th>Sick Lv</th>
                                    <th>Comp Off</th>
                                    <th>Half Day</th>
                                    <th>LWP</th>
                                    <th>Absent</th>
                                    <th class="text-right">Gross</th>
                                    <th class="text-right">Deductions</th>
                                    <th class="text-right">Net</th>
                                    <th>Status</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @if(!empty($rows) && count($rows) > 0)
                                @foreach($rows as $row)
                                    @if($type === 'employee-salary')
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ optional($row->employee)->display_name }}</td>
                                            <td class="text-right">₹{{ number_format((float) $row->annual_ctc, 2) }}</td>
                                            <td class="text-right">₹{{ number_format((float) $row->monthly_ctc, 2) }}</td>
                                            <td class="text-right">₹{{ number_format((float) $row->basic_monthly, 2) }}</td>
                                            <td class="text-right">₹{{ number_format((float) $row->hra_monthly, 2) }}</td>
                                            <td class="text-right">₹{{ number_format((float) $row->special_allowance_monthly, 2) }}</td>
                                            <td class="text-right text-danger">₹{{ number_format((float) $row->professional_tax_monthly, 2) }}</td>
                                            <td class="text-right text-danger">₹{{ number_format((float) $row->tds_monthly, 2) }}</td>
                                            <td>{{ $row->effective_from ? \Carbon\Carbon::parse($row->effective_from)->format('d M Y') : '-' }}</td>
                                        </tr>
                                    @elseif($type === 'reimbursement')
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ optional($row->employee)->display_name }}</td>
                                            <td>{{ $row->title }}</td>
                                            <td>{{ $row->claim_date ? \Carbon\Carbon::parse($row->claim_date)->format('d M Y') : '-' }}</td>
                                            <td class="text-right">₹{{ number_format((float) $row->amount, 2) }}</td>
                                            <td class="text-right">₹{{ number_format((float) $row->approved_amount, 2) }}</td>
                                            <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $row->status])</td>
                                        </tr>
                                    @elseif($type === 'bonus-incentive')
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ optional($row->employee)->display_name }}</td>
                                            <td>{{ ucfirst($row->type) }}</td>
                                            <td>{{ $row->title }}</td>
                                            <td class="text-right font-weight-bold text-primary">₹{{ number_format((float) $row->amount, 2) }}</td>
                                            <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $row->status])</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ optional($row->employee)->display_name }}</td>
                                            <td>{{ optional($row->employee)->employee_code ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::create()->month($row->month ?? $month)->format('F') }} {{ $row->year ?? $year }}</td>
                                            <td>{{ $row->payable_days ?? 0 }}</td>
                                            <td>{{ $row->present_days ?? 0 }}</td>
                                            <td>{{ $row->paid_leave_days ?? 0 }}</td>
                                            <td>{{ $row->sick_leave_days ?? 0 }}</td>
                                            <td>{{ $row->comp_off_days ?? 0 }}</td>
                                            <td>{{ $row->half_days ?? 0 }}</td>
                                            <td>{{ $row->lwp_days ?? 0 }}</td>
                                            <td>{{ $row->absent_days ?? 0 }}</td>
                                            <td class="text-right">₹{{ number_format((float) ($row->gross_salary ?? 0), 2) }}</td>
                                            <td class="text-right text-danger">₹{{ number_format((float) ($row->total_deductions ?? 0), 2) }}</td>
                                            <td class="text-right font-weight-bold text-primary">₹{{ number_format((float) ($row->net_salary ?? 0), 2) }}</td>
                                            <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $row->status ?? 'unknown'])</td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @section('_script')
    <!-- DataTables Core -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    
    <!-- DataTables Buttons Dependencies -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
    if (window.jQuery && $.fn.DataTable) {
        $(document).ready(function() {
            if (!$.fn.DataTable.isDataTable('.js-orb-datatable')) {
                var table = $('.js-orb-datatable').DataTable({
                    scrollX: true,
                    scrollCollapse: true,
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    language: {
                        emptyTable: 'No report data found for selected filters.',
                        zeroRecords: 'No matching records found.'
                    },
                    dom: '<"crud-dt-toolbar"<"crud-dt-left"l><"crud-dt-right"B>>rt<"orb-table-footer"ip>',
                    buttons: [
                        { extend: 'csvHtml5', text: '<i class="fas fa-file-csv text-muted"></i> CSV', className: 'crud-export-btn' },
                        { extend: 'excelHtml5', text: '<i class="fas fa-file-excel text-success"></i> Excel', className: 'crud-export-btn' },
                        { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf text-danger"></i> PDF', className: 'crud-export-btn', orientation: 'landscape', pageSize: 'A4' },
                        { extend: 'print', text: '<i class="fas fa-print text-primary"></i> Print', className: 'crud-export-btn' }
                    ]
                });
                $('.ep-table-search').on('keyup', function () {
                    table.search(this.value).draw();
                });
            }
        });
    }
    </script>
    @endsection
