@php
    $canManage = auth()->user()->hasPermission('enterprise_payroll.manage') || auth()->user()->isSuperAdmin();
    
    // Quick Stats
    $activeStructures = \App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureM::where('status', 'active')->count();
    $runsThisMonth = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM::where('month', now('Asia/Kolkata')->month)->where('year', now('Asia/Kolkata')->year)->count();
    $totalGross = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM::sum('total_gross') ?? 0;
    $totalNet = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM::sum('total_net') ?? 0;
    $totalDeductions = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM::sum('total_deductions') ?? 0;
    $pendingReimbursements = \App\Models\HRMS\EnterprisePayroll\EnterpriseReimbursementM::where('status', 'pending')->count();
    $approvedBonus = \App\Models\HRMS\EnterprisePayroll\EnterpriseBonusIncentiveM::where('status', 'approved')->count();
    $payslipsGenerated = \App\Models\HRMS\EnterprisePayroll\EnterprisePayslipM::count();
    $employeesProcessed = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM::sum('total_employees') ?? 0;
    $payrollPendingApproval = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM::where('status', 'processed')->count();
    $lwpDeductions = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('lwp_deduction') ?? 0;
    $attendanceImpact = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('attendance_deduction') ?? 0;

    // Charts data
    $monthlyTrend = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM::selectRaw('month, year, sum(total_gross) as gross, sum(total_net) as net')
        ->groupBy('month', 'year')->orderBy('year', 'desc')->orderBy('month', 'desc')->limit(6)->get()->reverse()->values();
    $trendCategories = $monthlyTrend->map(function($m) { return \Carbon\Carbon::create()->month($m->month)->format('M').' '.$m->year; })->toJson();
    $trendGross = $monthlyTrend->pluck('gross')->toJson();
    $trendNet = $monthlyTrend->pluck('net')->toJson();

    // 2. Salary Component Breakdown
    $basicSum = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('basic_salary') ?? 0;
    $hraSum = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('hra') ?? 0;
    $specialSum = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('special_allowance') ?? 0;
    
    // 3. Payroll Deduction Analysis
    $ptSum = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('professional_tax') ?? 0;
    $tdsSum = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('tds') ?? 0;
    $otherDedSum = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('other_deduction') ?? 0;

    // 4. Attendance Impact
    $lwpDays = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('lwp_days') ?? 0;
    $absentDays = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('absent_days') ?? 0;
    $halfDays = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM::sum('half_days') ?? 0;

    $statusCounts = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
    $pendingApprovals = $statusCounts['processed'] ?? 0;

    $highestSalaryEmp = \App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureM::where('status', 'active')->orderBy('monthly_ctc', 'desc')->first();
    $lowestSalaryEmp = \App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureM::where('status', 'active')->orderBy('monthly_ctc', 'asc')->first();

    $timelineActivities = collect();
    $runs = \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM::latest('updated_at')->take(5)->get();
    foreach($runs as $r) {
        $timelineActivities->push([
            'title' => 'Payroll Run ' . ucfirst($r->status),
            'desc' => \Carbon\Carbon::create()->month($r->month)->format('M') . ' ' . $r->year . ' Payroll updated.',
            'time' => $r->updated_at->diffForHumans(),
            'icon' => 'fas fa-play-circle',
            'color' => 'primary'
        ]);
    }
@endphp
@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
<style>
    .ep-dash-card { background: #fff; border-radius: 16px; border: 1px solid var(--ep-border); padding: 20px; box-shadow: 0 4px 12px rgba(16, 24, 40, .03); margin-bottom: 20px; height: 100%; transition: transform 0.2s; }
    .ep-dash-card:hover { transform: translateY(-3px); box-shadow: var(--ep-shadow); }
    .ep-icon-box { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 16px; }
    .ep-icon-primary { background: var(--ep-soft); color: var(--ep-primary); }
    .ep-icon-success { background: #ECFDF3; color: #027A48; }
    .ep-icon-warning { background: #FFFAEB; color: #B54708; }
    .ep-icon-danger { background: #FEF3F2; color: #B42318; }
    .ep-icon-info { background: #F0F9FF; color: #026AA2; }
    .ep-dash-val { font-size: 24px; font-weight: 900; color: var(--ep-text); margin-bottom: 4px; }
    .ep-dash-label { font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--ep-muted); letter-spacing: 0.5px; }
    .ep-widget-title { font-size: 15px; font-weight: 800; color: var(--ep-text); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .ep-timeline-item { position: relative; padding-left: 24px; margin-bottom: 20px; border-left: 2px solid var(--ep-border); }
    .ep-timeline-item:last-child { border-left: 0; padding-bottom: 0; margin-bottom: 0; }
    .ep-timeline-icon { position: absolute; left: -10px; top: 0; width: 18px; height: 18px; border-radius: 50%; background: #fff; border: 4px solid var(--ep-primary); }
    .ep-timeline-time { font-size: 11px; color: var(--ep-muted); font-weight: 700; margin-bottom: 4px; }
    .ep-timeline-title { font-size: 13px; font-weight: 800; color: var(--ep-text); margin-bottom: 2px; }
    .ep-timeline-desc { font-size: 12px; color: var(--ep-muted); margin: 0; }
    .ep-action-btn { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 12px; background: #fff; border: 1px solid var(--ep-border); color: var(--ep-text); font-weight: 700; font-size: 13px; margin-bottom: 12px; text-decoration: none; transition: 0.2s; }
    .ep-action-btn:hover { background: var(--ep-soft); color: var(--ep-primary); border-color: rgba(75,0,232,0.2); text-decoration: none; }
    .ep-snapshot-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--ep-border); }
    .ep-snapshot-row:last-child { border-bottom: none; }
</style>
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero align-items-center">
        <div>
            <div class="ep-kicker"><i class="fas fa-satellite-dish"></i> Enterprise Command Center</div>
            <h1>Enterprise Payroll Dashboard</h1>
            <p>Monthly payroll command center with salary runs, approvals, claims, payslips, and statutory insights.</p>
        </div>
        <div class="ep-hero-actions">
            @if($canManage)
            <a href="{{ route('enterprise-payroll.runs.index') }}" class="ep-btn ep-btn-light"><i class="fas fa-play"></i> Run Payroll</a>
            <a href="{{ route('enterprise-payroll.payslips.index') }}" class="ep-btn ep-btn-light"><i class="fas fa-file-invoice"></i> Payslips</a>
            @endif
            <a href="{{ route('enterprise-payroll.reports.index') }}" class="ep-btn ep-btn-light"><i class="fas fa-chart-pie"></i> View Reports</a>
        </div>
    </div>

    <!-- METRICS GRID (8 compact cards) -->
    <div class="row ep-metrics-grid">
        <!-- Card 1: Active Salary Structures -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="ep-metric-card border-bottom-primary">
                <div class="ep-metric-icon ep-icon-primary"><i class="fas fa-users"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Active Structures</div>
                    <div class="ep-metric-value">{{ number_format($activeStructures) }}</div>
                    <div class="ep-metric-trend text-primary"><i class="fas fa-user-check"></i> Active Employees</div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Gross Payroll -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="ep-metric-card border-bottom-success">
                <div class="ep-metric-icon ep-icon-success"><i class="fas fa-rupee-sign"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Total Gross Payroll</div>
                    <div class="ep-metric-value">₹{{ number_format((float) $totalGross, 2) }}</div>
                    <div class="ep-metric-trend text-success"><i class="fas fa-arrow-up"></i> Total payout</div>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Net Payroll -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="ep-metric-card border-bottom-info">
                <div class="ep-metric-icon ep-icon-info"><i class="fas fa-wallet"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Total Net Payroll</div>
                    <div class="ep-metric-value">₹{{ number_format((float) $totalNet, 2) }}</div>
                    <div class="ep-metric-trend text-info"><i class="fas fa-university"></i> Bank transfer</div>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Deductions -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="ep-metric-card border-bottom-danger">
                <div class="ep-metric-icon ep-icon-danger"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Total Deductions</div>
                    <div class="ep-metric-value">₹{{ number_format((float) $totalDeductions, 2) }}</div>
                    <div class="ep-metric-trend text-danger"><i class="fas fa-minus-circle"></i> Tax & PF</div>
                </div>
            </div>
        </div>

        <!-- Card 5: Pending Approval -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="ep-metric-card border-bottom-warning">
                <div class="ep-metric-icon ep-icon-warning"><i class="fas fa-clock"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Pending Approval</div>
                    <div class="ep-metric-value">{{ number_format($payrollPendingApproval) }}</div>
                    <div class="ep-metric-trend text-warning"><i class="fas fa-hourglass-half"></i> Runs awaiting review</div>
                </div>
            </div>
        </div>

        <!-- Card 6: Payslips Generated -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="ep-metric-card border-bottom-primary">
                <div class="ep-metric-icon ep-icon-primary"><i class="fas fa-file-signature"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Payslips Generated</div>
                    <div class="ep-metric-value">{{ number_format($payslipsGenerated) }}</div>
                    <div class="ep-metric-trend text-primary"><i class="fas fa-envelope-open-text"></i> Shared with staff</div>
                </div>
            </div>
        </div>

        <!-- Card 7: Approved Bonus/Incentive -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="ep-metric-card border-bottom-success">
                <div class="ep-metric-icon ep-icon-success"><i class="fas fa-gift"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">Approved Bonus</div>
                    <div class="ep-metric-value">{{ number_format($approvedBonus) }}</div>
                    <div class="ep-metric-trend text-success"><i class="fas fa-award"></i> Month incentive</div>
                </div>
            </div>
        </div>

        <!-- Card 8: LWP Deductions -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-12 mb-3">
            <div class="ep-metric-card border-bottom-danger">
                <div class="ep-metric-icon ep-icon-danger"><i class="fas fa-user-minus"></i></div>
                <div class="ep-metric-content">
                    <div class="ep-metric-label">LWP Deductions</div>
                    <div class="ep-metric-value">₹{{ number_format((float) $lwpDeductions, 2) }}</div>
                    <div class="ep-metric-trend text-danger"><i class="fas fa-calendar-minus"></i> Leave impact</div>
                </div>
            </div>
        </div>
    </div>

    <!-- CHARTS SECTION -->
    <div class="row mb-3">
        <div class="col-md-8 mb-4">
            <div class="ep-dash-card-premium">
                <div class="ep-dash-header-premium">
                    <div class="ep-icon-box"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <h5 class="ep-dash-title-premium">Monthly Payroll Trend</h5>
                        <p class="ep-dash-subtitle-premium">Comparison between monthly gross pay and net disbursements.</p>
                    </div>
                </div>
                <div id="payrollTrendChart"></div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="ep-dash-card-premium">
                <div class="ep-dash-header-premium">
                    <div class="ep-icon-box"><i class="fas fa-chart-pie"></i></div>
                    <div>
                        <h5 class="ep-dash-title-premium">Salary Breakdown</h5>
                        <p class="ep-dash-subtitle-premium">Allocation across core salary components.</p>
                    </div>
                </div>
                <div id="salaryBreakdownChart"></div>
            </div>
        </div>
    </div>

    <!-- WIDGETS SECTION -->
    <div class="row mb-4">
        <!-- Timeline -->
        <div class="col-md-4 mb-4">
            <div class="ep-dash-card-premium">
                <div class="ep-dash-header-premium">
                    <div class="ep-icon-box"><i class="fas fa-history"></i></div>
                    <div>
                        <h5 class="ep-dash-title-premium">Recent Payroll Activity</h5>
                        <p class="ep-dash-subtitle-premium">Real-time status updates and operations.</p>
                    </div>
                </div>
                <div class="p-1">
                    @forelse($timelineActivities as $ta)
                    <div class="ep-timeline-item">
                        <div class="ep-timeline-icon"></div>
                        <div class="ep-timeline-time">{{ $ta['time'] }}</div>
                        <div class="ep-timeline-title">{{ $ta['title'] }}</div>
                        <div class="ep-timeline-desc">{{ $ta['desc'] }}</div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">No recent activity.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Employee Snapshot -->
        <div class="col-md-4 mb-4">
            <div class="ep-dash-card-premium">
                <div class="ep-dash-header-premium">
                    <div class="ep-icon-box"><i class="fas fa-id-card"></i></div>
                    <div>
                        <h5 class="ep-dash-title-premium">Payroll Snapshot</h5>
                        <p class="ep-dash-subtitle-premium">Quick analytical overview of structures & claims.</p>
                    </div>
                </div>
                <div class="ep-snapshot-row">
                    <span class="text-muted font-weight-bold">Highest CTC</span>
                    <span class="font-weight-bold">₹{{ $highestSalaryEmp ? number_format((float) $highestSalaryEmp->monthly_ctc, 2) : '0.00' }}</span>
                </div>
                <div class="ep-snapshot-row">
                    <span class="text-muted font-weight-bold">Lowest CTC</span>
                    <span class="font-weight-bold">₹{{ $lowestSalaryEmp ? number_format((float) $lowestSalaryEmp->monthly_ctc, 2) : '0.00' }}</span>
                </div>
                <div class="ep-snapshot-row">
                    <span class="text-muted font-weight-bold">Runs This Month</span>
                    <span class="font-weight-bold">{{ $runsThisMonth }}</span>
                </div>
                <div class="ep-snapshot-row">
                    <span class="text-muted font-weight-bold">Pending Reimb.</span>
                    <span class="font-weight-bold text-warning">{{ $pendingReimbursements }}</span>
                </div>
                <div class="ep-snapshot-row">
                    <span class="text-muted font-weight-bold">Avg Attendance Ded.</span>
                    <span class="font-weight-bold text-danger">₹{{ number_format((float) $attendanceImpact, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4 mb-4">
            <div class="ep-dash-card-premium">
                <div class="ep-dash-header-premium">
                    <div class="ep-icon-box"><i class="fas fa-bolt"></i></div>
                    <div>
                        <h5 class="ep-dash-title-premium">Quick Actions</h5>
                        <p class="ep-dash-subtitle-premium">Jump directly to core modules.</p>
                    </div>
                </div>
                @if($canManage)
                <a href="{{ route('enterprise-payroll.salary-structures.index') }}" class="ep-action-btn">
                    <span><i class="fas fa-money-check-alt mr-2 text-primary"></i> Salary Structures</span> <i class="fas fa-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('enterprise-payroll.bonus-incentives.index') }}" class="ep-action-btn">
                    <span><i class="fas fa-gift mr-2 text-success"></i> Add Bonus/Incentive</span> <i class="fas fa-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('enterprise-payroll.fnf.index') }}" class="ep-action-btn">
                    <span><i class="fas fa-handshake mr-2 text-warning"></i> FNF Settlement</span> <i class="fas fa-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('enterprise-payroll.reimbursements.index') }}" class="ep-action-btn">
                    <span><i class="fas fa-receipt mr-2 text-info"></i> Reimbursements</span> <i class="fas fa-chevron-right text-muted"></i>
                </a>
                @endif
                <a href="{{ route('enterprise-payroll.reports.index') }}" class="ep-action-btn">
                    <span><i class="fas fa-chart-bar mr-2 text-secondary"></i> All Reports</span> <i class="fas fa-chevron-right text-muted"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Latest Runs Table -->
    <div class="ep-card">
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-table"></i></div>
                <div>
                    <h5 class="ep-table-title">Latest Payroll Runs</h5>
                    <p class="ep-table-subtitle">Summary and status of recent monthly payroll disbursements.</p>
                </div>
            </div>
        </div>
        <div class="ep-card-body p-0">
            <div class="ep-table-wrap">
                <table class="table ep-table js-orb-datatable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Payroll Month</th>
                            <th>Employees</th>
                            <th class="text-right">Gross Payroll</th>
                            <th class="text-right">Total Deduction</th>
                            <th class="text-right">Net Payroll</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($latestRuns) && $latestRuns->count() > 0)
                            @foreach($latestRuns as $run)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::create()->month($run->month)->format('F') }} {{ $run->year }}</td>
                                    <td>{{ $run->total_employees }}</td>
                                    <td class="text-right">₹{{ number_format((float) $run->total_gross, 2) }}</td>
                                    <td class="text-right text-danger">₹{{ number_format((float) $run->total_deductions, 2) }}</td>
                                    <td class="text-right font-weight-bold text-primary">₹{{ number_format((float) $run->total_net, 2) }}</td>
                                    <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $run->status])</td>
                                    <td><a class="ep-btn ep-btn-light" style="height: 30px; padding: 0 8px;" href="{{ route('enterprise-payroll.runs.show', $run) }}"><i class="fas fa-eye"></i></a></td>
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    if (window.jQuery && $.fn.DataTable) {
        $('.js-orb-datatable').each(function() {
            var $table = $(this);
            $table.DataTable({
                pageLength: 10,
                order: [],
                searching: false,
                lengthChange: true,
                autoWidth: false,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    emptyTable: 'No payroll runs yet.',
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

    document.addEventListener("DOMContentLoaded", function() {
        // Trend Chart
        var trendOptions = {
            series: [{
                name: 'Gross Payroll',
                data: {!! $trendGross !!}
            }, {
                name: 'Net Payroll',
                data: {!! $trendNet !!}
            }],
            chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
            colors: ['#4B00E8', '#027A48'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: {!! $trendCategories !!}, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { formatter: function (val) { return "₹" + (val/1000).toFixed(0) + "k" } } },
            legend: { position: 'top', horizontalAlign: 'right' }
        };
        new ApexCharts(document.querySelector("#payrollTrendChart"), trendOptions).render();

        // Breakdown Chart
        var breakdownOptions = {
            series: [{{ $basicSum }}, {{ $hraSum }}, {{ $specialSum }}, {{ $otherDedSum }}],
            labels: ['Basic', 'HRA', 'Special', 'Other'],
            chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
            colors: ['#4B00E8', '#8600EE', '#027A48', '#B54708'],
            plotOptions: { donut: { size: '65%' } },
            dataLabels: { enabled: false },
            legend: { position: 'bottom' },
            tooltip: { y: { formatter: function (val) { return "₹" + val.toLocaleString() } } }
        };
        new ApexCharts(document.querySelector("#salaryBreakdownChart"), breakdownOptions).render();
    });
</script>
@endsection
