@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-play-circle"></i> Enterprise Payroll</div>
            <h1>Payroll Runs</h1>
            <p>Preview, generate, approve, lock and reopen monthly enterprise payroll.</p>
        </div>
    </div>

    @include('hrms.enterprise-payroll.partials.flash')

    @if(auth()->user() && auth()->user()->hasPermission('enterprise_payroll_run.generate'))
        <div class="ep-card">
            <div class="ep-card-body ep-form">
                <form method="POST" action="{{ route('enterprise-payroll.runs.preview') }}" class="row align-items-end">
                    @csrf
                    <div class="col-md-3 mb-3">
                        <label>Month</label>
                        <select name="month" class="form-control" required>
                            @for($i=1; $i<=12; $i++)
                                <option value="{{ $i }}" {{ now('Asia/Kolkata')->month == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 mb-3"><label>Year</label><input type="number" min="2020" name="year" value="{{ now('Asia/Kolkata')->year }}" class="form-control" required></div>
                    <div class="col-md-3 mb-3"><button class="ep-btn ep-btn-gradient w-100"><i class="fas fa-search"></i> Preview Payroll</button></div>
                </form>
            </div>
        </div>
    @endif

    <div class="ep-card">
        <!-- Table Card Header -->
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-money-check-alt"></i></div>
                <div>
                    <h5 class="ep-table-title">Payroll Runs</h5>
                    <p class="ep-table-subtitle">Monitor and approve processed enterprise monthly payroll runs.</p>
                </div>
            </div>
            <div class="ep-hero-actions">
                <!-- Keep actions on the right side if required in future -->
            </div>
        </div>

        <!-- Attached Filters -->
        <div class="ep-card-filters">
            <form method="GET" action="{{ route('enterprise-payroll.runs.index') }}" class="row align-items-end ep-form" id="filterForm">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Month</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        <option value="">All Months</option>
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="{{ request('year') }}" onkeyup="if(event.keyCode === 13) this.form.submit()" placeholder="Year">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked</option>
                    </select>
                </div>
                <div class="col-md-3 text-right">
                    <a href="{{ route('enterprise-payroll.runs.index') }}" class="ep-btn ep-btn-light w-100"><i class="fas fa-sync-alt"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="ep-card-body p-0">
            <div class="ep-table-wrap">
                <table class="table ep-table js-orb-datatable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Employees</th>
                            <th class="text-right">Gross</th>
                            <th class="text-right">Deductions</th>
                            <th class="text-right">Net</th>
                            <th>Processed At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($runs) && count($runs) > 0)
                            @foreach($runs as $run)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::create()->month($run->month)->format('F') }}</td>
                                    <td>{{ $run->year }}</td>
                                    <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $run->status])</td>
                                    <td>{{ $run->total_employees }}</td>
                                    <td class="text-right">₹{{ number_format((float) $run->total_gross, 2) }}</td>
                                    <td class="text-right">₹{{ number_format((float) $run->total_deductions, 2) }}</td>
                                    <td class="text-right font-weight-bold text-primary">₹{{ number_format((float) $run->total_net, 2) }}</td>
                                    <td>{{ $run->processed_at ? $run->processed_at->format('d M Y h:i A') : '-' }}</td>
                                    <td><a class="ep-btn ep-btn-light" style="height: 30px; padding: 0 10px;" href="{{ route('enterprise-payroll.runs.show', $run) }}"><i class="fas fa-eye text-primary"></i> View</a></td>
                                </tr>
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
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    emptyTable: 'No payroll runs found.',
                    zeroRecords: 'No matching records found.'
                },
                dom: '<"crud-dt-toolbar"<"crud-dt-left"l><"crud-dt-right"B>>rt<"orb-table-footer"ip>',
                buttons: [
                    { extend: 'csvHtml5', text: '<i class="fas fa-file-csv text-muted"></i> CSV', className: 'crud-export-btn' },
                    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel text-success"></i> Excel', className: 'crud-export-btn' },
                    { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf text-danger"></i> PDF', className: 'crud-export-btn' },
                    { extend: 'print', text: '<i class="fas fa-print text-primary"></i> Print', className: 'crud-export-btn' }
                ]
            });
        });
    }
</script>
@endsection
