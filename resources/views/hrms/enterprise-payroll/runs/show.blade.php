@php
    $payableDays = $run->payrolls->sum('payable_days');
    $lwpDays = $run->payrolls->sum('lwp_days');
    $attendanceDed = $run->payrolls->sum('attendance_deduction');
    $payslipsGen = \App\Models\HRMS\EnterprisePayroll\EnterprisePayslipM::whereIn('payroll_id', $run->payrolls->pluck('id'))->count();
@endphp
@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
<style>
    .ep-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .ep-summary-card {
        background: #fff;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid var(--ep-border);
        box-shadow: 0 4px 12px rgba(16, 24, 40, .02);
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .ep-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(16, 24, 40, .05);
    }
    
    .ep-summary-card.accent-purple { border-bottom: 3px solid #6366f1; }
    .ep-summary-card.accent-green { border-bottom: 3px solid #10b981; }
    .ep-summary-card.accent-blue { border-bottom: 3px solid #3b82f6; }
    .ep-summary-card.accent-red { border-bottom: 3px solid #ef4444; }
    .ep-summary-card.accent-orange { border-bottom: 3px solid #f59e0b; }
    
    .ep-card-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        font-size: 20px;
        flex-shrink: 0;
    }
    
    .accent-purple .ep-card-icon-box { background-color: rgba(99, 102, 241, 0.08); color: #6366f1; }
    .accent-green .ep-card-icon-box { background-color: rgba(16, 185, 129, 0.08); color: #10b981; }
    .accent-blue .ep-card-icon-box { background-color: rgba(59, 130, 246, 0.08); color: #3b82f6; }
    .accent-red .ep-card-icon-box { background-color: rgba(239, 68, 68, 0.08); color: #ef4444; }
    .accent-orange .ep-card-icon-box { background-color: rgba(245, 158, 11, 0.08); color: #f59e0b; }
    
    .ep-card-content {
        flex: 1;
        min-width: 0;
    }
    
    .ep-summary-lbl {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .ep-summary-val {
        font-size: 22px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 2px;
        line-height: 1.2;
    }
    
    .ep-card-footer-lbl {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        display: flex;
        align-items: center;
        margin-top: 4px;
    }
    
    .ep-card-footer-lbl i {
        font-size: 10px;
        margin-right: 4px;
    }
    
    .accent-purple .ep-card-footer-lbl i, .accent-purple .ep-card-footer-lbl span { color: #6366f1; }
    .accent-green .ep-card-footer-lbl i, .accent-green .ep-card-footer-lbl span { color: #10b981; }
    .accent-blue .ep-card-footer-lbl i, .accent-blue .ep-card-footer-lbl span { color: #3b82f6; }
    .accent-red .ep-card-footer-lbl i, .accent-red .ep-card-footer-lbl span { color: #ef4444; }
    .accent-orange .ep-card-footer-lbl i, .accent-orange .ep-card-footer-lbl span { color: #f59e0b; }

    .ep-emp-card { background: #fff; border-radius: 16px; border: 1px solid var(--ep-border); padding: 24px; box-shadow: 0 4px 12px rgba(16, 24, 40, .03); margin-bottom: 24px; }
    .ep-emp-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--ep-border); padding-bottom: 16px; margin-bottom: 16px; }
    .ep-emp-name { font-size: 20px; font-weight: 900; color: var(--ep-primary); }
    .ep-emp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px; }
    .ep-emp-stat { background: var(--ep-bg); padding: 12px; border-radius: 8px; text-align: center; }
</style>
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero align-items-center">
        <div>
            <div class="ep-kicker"><i class="fas fa-file-invoice-dollar"></i> Payroll Run Details</div>
            <h1>Payroll Run - {{ \Carbon\Carbon::createFromDate($run->year, $run->month, 1)->format('F Y') }}</h1>
            <p>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $run->status]) | Employees: {{ $run->total_employees }} | Net: ₹{{ number_format((float) $run->total_net, 2) }}</p>
        </div>
        <div class="ep-hero-actions">
            @if($run->status !== 'locked' && auth()->user() && auth()->user()->hasPermission('enterprise_payroll_run.approve'))
                <form method="POST" action="{{ route('enterprise-payroll.runs.approve', $run) }}">@csrf<button class="ep-btn ep-btn-success"><i class="fas fa-check"></i> Approve</button></form>
            @endif
            @if($run->status !== 'locked' && auth()->user() && auth()->user()->hasPermission('enterprise_payroll_run.lock'))
                <form method="POST" action="{{ route('enterprise-payroll.runs.lock', $run) }}">@csrf<button class="ep-btn ep-btn-danger"><i class="fas fa-lock"></i> Lock</button></form>
            @endif
            @if($run->status === 'locked' && auth()->user() && auth()->user()->hasPermission('enterprise_payroll_run.reopen'))
                <form method="POST" action="{{ route('enterprise-payroll.runs.reopen', $run) }}">@csrf<button class="ep-btn ep-btn-warning"><i class="fas fa-unlock"></i> Reopen</button></form>
            @endif
            @if(auth()->user() && auth()->user()->hasPermission('enterprise_payslip.generate'))
                <form method="POST" action="{{ route('enterprise-payroll.runs.payslips.generate', $run) }}">@csrf<button class="ep-btn ep-btn-primary"><i class="fas fa-file-pdf"></i> Generate Payslips</button></form>
            @endif
            <a href="{{ route('enterprise-payroll.runs.index') }}" class="ep-btn ep-btn-light"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @include('hrms.enterprise-payroll.partials.flash')

    <div class="ep-summary-grid">
        <!-- Employees Processed -->
        <div class="ep-summary-card accent-purple">
            <div class="ep-card-icon-box">
                <i class="fas fa-users"></i>
            </div>
            <div class="ep-card-content">
                <div class="ep-summary-lbl">Employees Processed</div>
                <div class="ep-summary-val">{{ $run->total_employees }}</div>
                <div class="ep-card-footer-lbl">
                    <i class="fas fa-user-check"></i>
                    <span>Active Employees</span>
                </div>
            </div>
        </div>

        <!-- Gross Payroll -->
        <div class="ep-summary-card accent-green">
            <div class="ep-card-icon-box">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="ep-card-content">
                <div class="ep-summary-lbl">Gross Payroll</div>
                <div class="ep-summary-val">₹{{ number_format((float) $run->total_gross, 2) }}</div>
                <div class="ep-card-footer-lbl">
                    <i class="fas fa-arrow-up"></i>
                    <span>Total payout</span>
                </div>
            </div>
        </div>

        <!-- Total Deductions -->
        <div class="ep-summary-card accent-red">
            <div class="ep-card-icon-box">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div class="ep-card-content">
                <div class="ep-summary-lbl">Total Deductions</div>
                <div class="ep-summary-val">₹{{ number_format((float) $run->total_deductions, 2) }}</div>
                <div class="ep-card-footer-lbl">
                    <i class="fas fa-minus-circle"></i>
                    <span>Tax & PF</span>
                </div>
            </div>
        </div>

        <!-- Net Payroll -->
        <div class="ep-summary-card accent-blue">
            <div class="ep-card-icon-box">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="ep-card-content">
                <div class="ep-summary-lbl">Net Payroll</div>
                <div class="ep-summary-val text-success">₹{{ number_format((float) $run->total_net, 2) }}</div>
                <div class="ep-card-footer-lbl">
                    <i class="fas fa-university"></i>
                    <span>Bank transfer</span>
                </div>
            </div>
        </div>

        <!-- Payable Days -->
        <div class="ep-summary-card accent-purple">
            <div class="ep-card-icon-box">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="ep-card-content">
                <div class="ep-summary-lbl">Payable Days</div>
                <div class="ep-summary-val">{{ $payableDays }}</div>
                <div class="ep-card-footer-lbl">
                    <i class="fas fa-calendar-check"></i>
                    <span>Total payable</span>
                </div>
            </div>
        </div>

        <!-- LWP Days -->
        <div class="ep-summary-card accent-red">
            <div class="ep-card-icon-box">
                <i class="fas fa-user-minus"></i>
            </div>
            <div class="ep-card-content">
                <div class="ep-summary-lbl">LWP Days</div>
                <div class="ep-summary-val">{{ $lwpDays }}</div>
                <div class="ep-card-footer-lbl">
                    <i class="fas fa-calendar-times"></i>
                    <span>Leave impact</span>
                </div>
            </div>
        </div>

        <!-- Attendance Deduction -->
        <div class="ep-summary-card accent-orange">
            <div class="ep-card-icon-box">
                <i class="fas fa-clock"></i>
            </div>
            <div class="ep-card-content">
                <div class="ep-summary-lbl">Attendance Deduction</div>
                <div class="ep-summary-val">₹{{ number_format((float) $attendanceDed, 2) }}</div>
                <div class="ep-card-footer-lbl">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Late/Absent cut</span>
                </div>
            </div>
        </div>

        <!-- Payslips Generated -->
        <div class="ep-summary-card accent-purple">
            <div class="ep-card-icon-box">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="ep-card-content">
                <div class="ep-summary-lbl">Payslips Generated</div>
                <div class="ep-summary-val">{{ $payslipsGen }}</div>
                <div class="ep-card-footer-lbl">
                    <i class="fas fa-envelope-open-text"></i>
                    <span>Shared with staff</span>
                </div>
            </div>
        </div>
    </div>

    @if($run->payrolls->count() == 1)
        @php $p = $run->payrolls->first(); @endphp
        <div class="ep-emp-card">
            <div class="ep-emp-header">
                <div class="ep-emp-name"><i class="fas fa-user-circle mr-2"></i> {{ optional($p->employee)->display_name }} ({{ optional($p->employee)->employee_code }})</div>
                <div>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $p->status])</div>
            </div>
            <div class="ep-emp-grid">
                <div class="ep-emp-stat"><div class="ep-summary-val text-success">₹{{ number_format((float) $p->net_salary, 2) }}</div><div class="ep-summary-lbl">Net Salary</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">₹{{ number_format((float) $p->gross_salary, 2) }}</div><div class="ep-summary-lbl">Gross Salary</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val text-danger">₹{{ number_format((float) $p->total_deductions, 2) }}</div><div class="ep-summary-lbl">Deductions</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">{{ $p->payable_days }}</div><div class="ep-summary-lbl">Payable Days</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">{{ $p->present_days }}</div><div class="ep-summary-lbl">Present</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">{{ $p->paid_leave_days }}</div><div class="ep-summary-lbl">Paid Leave</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">{{ $p->sick_leave_days }}</div><div class="ep-summary-lbl">Sick Leave</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val">{{ $p->comp_off_days }}</div><div class="ep-summary-lbl">Comp Off</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val text-warning">{{ $p->half_days }}</div><div class="ep-summary-lbl">Half Day</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val text-danger">{{ $p->lwp_days }}</div><div class="ep-summary-lbl">LWP</div></div>
                <div class="ep-emp-stat"><div class="ep-summary-val text-danger">{{ $p->absent_days }}</div><div class="ep-summary-lbl">Absent</div></div>
            </div>
        </div>
    @endif

    <!-- Payslip Preview & Distribution Section -->
    <div class="ep-card">
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h5 class="ep-table-title">Payslip Preview & Distribution</h5>
                    <p class="ep-table-subtitle">Preview, regenerate, download and email employee payslips generated from this payroll run.</p>
                </div>
            </div>
            <div class="ep-hero-actions">
                <!-- No additional actions needed -->
            </div>
        </div>

        <div class="ep-card-body p-0">
            <div class="ep-table-wrap">
                <table class="table ep-table js-orb-datatable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Employee</th>
                            <th class="text-right">Net Salary</th>
                            <th class="text-right">Gross</th>
                            <th class="text-right">Deductions</th>
                            <th>Payable Days</th>
                            <th>Payslip Status</th>
                            <th>Generated At</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($run->payrolls as $payroll)
                        @php $payslip = $payroll->payslip; @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ optional($payroll->employee)->display_name }}</div>
                                <small class="text-muted">{{ optional($payroll->employee)->employee_code }}</small>
                            </td>
                            <td class="text-right font-weight-bold text-success">₹{{ number_format((float) $payroll->net_salary, 2) }}</td>
                            <td class="text-right">₹{{ number_format((float) $payroll->gross_salary, 2) }}</td>
                            <td class="text-right text-danger">₹{{ number_format((float) $payroll->total_deductions, 2) }}</td>
                            <td>{{ $payroll->payable_days }}</td>
                            <td>
                                @if($payslip)
                                    <span class="badge px-2 py-1" style="font-size: 11px; font-weight: 700; border-radius: 4px; background-color: #10B981; color: #fff;">Generated</span>
                                @else
                                    <span class="badge px-2 py-1" style="font-size: 11px; font-weight: 700; border-radius: 4px; background-color: #F59E0B; color: #fff;">Pending</span>
                                @endif
                            </td>
                            <td>{{ $payslip && $payslip->generated_at ? $payslip->generated_at->format('d M Y h:i A') : '-' }}</td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" type="button" id="dropdownMenu-{{ $payroll->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border: none; background: none; box-shadow: none;">
                                        <i class="fas fa-ellipsis-v" style="font-size: 16px;"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow border-0" aria-labelledby="dropdownMenu-{{ $payroll->id }}" style="border-radius: 8px; min-width: 180px; padding: 6px 0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.02) !important;">
                                        <!-- View Preview (Always Active) -->
                                        <a class="dropdown-item" href="{{ route('enterprise-payroll.payslips.preview', $payroll) }}" target="_blank" style="font-size: 13px; font-weight: 500; padding: 8px 16px; color: var(--orb-primary);">
                                            <i class="fas fa-eye mr-2 text-primary"></i> View Preview
                                        </a>

                                        @if($payslip)
                                            <!-- View PDF -->
                                            <a class="dropdown-item" href="{{ route('enterprise-payroll.payslips.view', $payslip) }}" target="_blank" style="font-size: 13px; font-weight: 500; padding: 8px 16px;">
                                                <i class="fas fa-file-pdf mr-2 text-danger"></i> View PDF
                                            </a>

                                            <!-- Download PDF -->
                                            <a class="dropdown-item" href="{{ route('enterprise-payroll.payslips.download', $payslip) }}" style="font-size: 13px; font-weight: 500; padding: 8px 16px;">
                                                <i class="fas fa-download mr-2 text-success"></i> Download PDF
                                            </a>

                                            @if(auth()->user() && auth()->user()->hasPermission('enterprise_payslip.generate'))
                                                <div class="dropdown-divider" style="margin: 4px 0; border-top: 1px solid var(--ep-border);"></div>

                                                <!-- Regenerate PDF -->
                                                <form action="{{ route('enterprise-payroll.payslips.regenerate', $payslip) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to regenerate this payslip PDF without recalculating the salary?')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-warning border-0 bg-transparent text-left w-100" style="font-size: 13px; font-weight: 500; padding: 8px 16px; outline: none; box-shadow: none; display: block;">
                                                        <i class="fas fa-sync-alt mr-2"></i> Regenerate PDF
                                                    </button>
                                                </form>

                                                <!-- Email Payslip -->
                                                <form action="{{ route('enterprise-payroll.payslips.email', $payslip) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to email this payslip to the employee?')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-info border-0 bg-transparent text-left w-100" style="font-size: 13px; font-weight: 500; padding: 8px 16px; outline: none; box-shadow: none; display: block;">
                                                        <i class="fas fa-paper-plane mr-2"></i> Email Payslip
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            @if(auth()->user() && auth()->user()->hasPermission('enterprise_payslip.generate'))
                                                <div class="dropdown-divider" style="margin: 4px 0; border-top: 1px solid var(--ep-border);"></div>

                                                <!-- Generate PDF -->
                                                <form action="{{ route('enterprise-payroll.payrolls.generate-payslip', $payroll) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success border-0 bg-transparent text-left w-100" style="font-size: 13px; font-weight: 500; padding: 8px 16px; outline: none; box-shadow: none; display: block;">
                                                        <i class="fas fa-file-invoice mr-2"></i> Generate PDF
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Disabled Pending Actions -->
                                            <div class="dropdown-divider" style="margin: 4px 0; border-top: 1px solid var(--ep-border);"></div>
                                            <span class="dropdown-item disabled text-muted" style="font-size: 13px; font-weight: 500; padding: 8px 16px; opacity: 0.5; pointer-events: none;">
                                                <i class="fas fa-file-pdf mr-2"></i> View PDF (Pending)
                                            </span>
                                            <span class="dropdown-item disabled text-muted" style="font-size: 13px; font-weight: 500; padding: 8px 16px; opacity: 0.5; pointer-events: none;">
                                                <i class="fas fa-download mr-2"></i> Download (Pending)
                                            </span>
                                            <span class="dropdown-item disabled text-muted" style="font-size: 13px; font-weight: 500; padding: 8px 16px; opacity: 0.5; pointer-events: none;">
                                                <i class="fas fa-paper-plane mr-2"></i> Email (Pending)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Calculation Breakdown Collapsible Card -->
    <div class="ep-card mt-4">
        <div class="ep-table-header" style="border-bottom: 0;">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-calculator text-muted"></i></div>
                <div>
                    <h5 class="ep-table-title text-muted">Calculation Breakdown</h5>
                    <p class="ep-table-subtitle">Detailed attendance and leave metrics used for salary calculation.</p>
                </div>
            </div>
            <div class="ep-hero-actions">
                <button class="ep-btn ep-btn-light" type="button" data-toggle="collapse" data-target="#calculationBreakdown" aria-expanded="false" aria-controls="calculationBreakdown" id="toggleBreakdownBtn" style="height: 36px; padding: 0 16px; font-size: 13px; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; border: 1px solid var(--ep-border);">
                    <span class="btn-text">Show Details</span> <i class="fas fa-chevron-down ml-2" id="toggleChevron"></i>
                </button>
            </div>
        </div>

        <div class="collapse" id="calculationBreakdown">
            <div class="ep-card-body p-0 border-top">
                <div class="ep-table-wrap">
                    <table class="table ep-table js-orb-datatable">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Employee</th>
                                <th>Payable</th>
                                <th>Present</th>
                                <th>Paid Leave</th>
                                <th>Sick</th>
                                <th>Comp Off</th>
                                <th>Half Day</th>
                                <th>LWP</th>
                                <th>Absent</th>
                                <th class="text-right">Gross</th>
                                <th class="text-right">Deductions</th>
                                <th class="text-right">Net</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($run->payrolls as $payroll)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ optional($payroll->employee)->display_name }}</td>
                                <td>{{ $payroll->payable_days }}</td>
                                <td>{{ $payroll->present_days }}</td>
                                <td>{{ $payroll->paid_leave_days }}</td>
                                <td>{{ $payroll->sick_leave_days }}</td>
                                <td>{{ $payroll->comp_off_days }}</td>
                                <td>{{ $payroll->half_days }}</td>
                                <td>{{ $payroll->lwp_days }}</td>
                                <td>{{ $payroll->absent_days }}</td>
                                <td class="text-right">₹{{ number_format((float) $payroll->gross_salary, 2) }}</td>
                                <td class="text-right text-danger">₹{{ number_format((float) $payroll->total_deductions, 2) }}</td>
                                <td class="text-right font-weight-bold text-primary">₹{{ number_format((float) $payroll->net_salary, 2) }}</td>
                                <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $payroll->status])</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
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
                    emptyTable: 'No payroll records found.',
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

    if (window.jQuery) {
        $('#calculationBreakdown').on('show.bs.collapse', function () {
            $('#toggleBreakdownBtn .btn-text').text('Hide Details');
            $('#toggleChevron').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }).on('hide.bs.collapse', function () {
            $('#toggleBreakdownBtn .btn-text').text('Show Details');
            $('#toggleChevron').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        });
    }
</script>
@endsection
