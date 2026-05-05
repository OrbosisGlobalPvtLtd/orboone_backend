@extends('layouts.panel')

@section('page_title', $dashboard['role_title'] ?? 'Super Admin Dashboard')

@section('_content')
    <!-- @include('dashboard.partials.styles')
    @include('dashboard.partials.role-dashboard') -->
    @php
        $cards = $dashboard['cards'] ?? [];
        $actions = $dashboard['quick_actions'] ?? [];
        $attendance = $dashboard['attendance_today'] ?? [];
        $monthly = $dashboard['charts']['monthly'] ?? ['labels' => [], 'present' => [], 'late' => []];
        $departments = $dashboard['charts']['departments'] ?? ['labels' => [], 'values' => []];
        $activities = $dashboard['recent_activities'] ?? collect();

        $present = (int) ($attendance['present'] ?? 0);
        $absent = (int) ($attendance['absent'] ?? 0);
        $late = (int) ($attendance['late'] ?? 0);
        $leave = (int) ($attendance['leave'] ?? 0);
        $halfDay = (int) ($attendance['half_day'] ?? 0);
        $weekOff = (int) ($attendance['week_off'] ?? 0);
        $pendingHr = (int) ($attendance['pending_hr'] ?? 0);
        $earlyOut = (int) ($attendance['early_out'] ?? 0);

        $totalEmployees =
            (int) (collect($cards)->firstWhere('label', 'Total Employees')['value'] ??
                (collect($cards)->first()['value'] ?? 0));
        $totalAttendance = max(1, $present + $absent + $late + $leave + $halfDay + $weekOff);
        $presentPercent = round(($present / $totalAttendance) * 100);

        $kpiCards = collect($cards)->take(4);
        $monthlyPresent = array_map('intval', $monthly['present'] ?? []);
        $monthlyLate = array_map('intval', $monthly['late'] ?? []);
        $departmentValues = array_map('intval', $departments['values'] ?? []);

        $maxMonthly = max(
            1,
            !empty($monthlyPresent) ? max($monthlyPresent) : 0,
            !empty($monthlyLate) ? max($monthlyLate) : 0,
        );
        $maxDept = max(1, !empty($departmentValues) ? max($departmentValues) : 0);
    @endphp

    <style>
        .hrdash-page {
            min-height: calc(100vh - 80px);
            background: #f7f8fc;
            padding: 22px;
            color: #111827;
        }

        .hrdash-wrap {
            max-width: 1440px;
            margin: 0 auto;
        }

        .hrdash-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
        }

        .hrdash-greeting h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -.6px;
            color: #111827;
        }

        .hrdash-greeting h1 span {
            color: #8b8fb3;
            font-weight: 600;
        }

        .hrdash-greeting p {
            margin: 5px 0 0;
            color: #98a2b3;
            font-size: 13px;
        }

        .hrdash-top-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .hrdash-search {
            width: 280px;
            height: 42px;
            background: #fff;
            border: 1px solid #edf0f7;
            border-radius: 999px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 15px;
            color: #98a2b3;
            box-shadow: 0 10px 28px rgba(17, 24, 39, .04);
        }

        .hrdash-search input {
            border: 0;
            outline: 0;
            width: 100%;
            font-size: 13px;
            background: transparent;
        }

        .hrdash-manage-btn {
            border: 0;
            background: linear-gradient(135deg, #6c7cff, #8b8cff);
            color: #fff;
            height: 42px;
            padding: 0 18px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(108, 124, 255, .25);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .hrdash-section-title {
            font-size: 15px;
            font-weight: 800;
            margin: 0 0 12px;
            color: #1f2937;
        }

        .hrdash-overview {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr)) 1.1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .hrdash-kpi,
        .hrdash-total-card,
        .hrdash-card {
            background: #fff;
            border: 1px solid #edf0f7;
            border-radius: 18px;
            box-shadow: 0 12px 32px rgba(17, 24, 39, .05);
        }

        .hrdash-kpi {
            padding: 18px;
            min-height: 115px;
            background: linear-gradient(145deg, #ffffff, #f2f3ff);
            position: relative;
            overflow: hidden;
        }

        .hrdash-kpi:after {
            content: "";
            position: absolute;
            right: -28px;
            top: -28px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(108, 92, 231, .08);
        }

        .hrdash-kpi-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }

        .hrdash-kpi-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #fff;
            display: grid;
            place-items: center;
            color: #6c63ff;
            box-shadow: 0 8px 18px rgba(17, 24, 39, .06);
        }

        .hrdash-trend {
            font-size: 11px;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 999px;
        }

        .hrdash-trend.up {
            color: #16a34a;
            background: #ecfdf3;
        }

        .hrdash-trend.down {
            color: #ef4444;
            background: #fff1f2;
        }

        .hrdash-kpi strong {
            display: block;
            font-size: 27px;
            line-height: 1;
            font-weight: 900;
            color: #111827;
            position: relative;
            z-index: 1;
        }

        .hrdash-kpi span {
            display: block;
            margin-top: 8px;
            font-size: 12px;
            color: #667085;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .hrdash-total-card {
            padding: 16px;
            display: grid;
            grid-template-columns: 1fr 105px;
            align-items: center;
            gap: 8px;
            background: #fff;
        }

        .hrdash-total-card span {
            font-size: 12px;
            color: #667085;
            font-weight: 800;
            display: block;
            margin-bottom: 6px;
        }

        .hrdash-total-card strong {
            font-size: 28px;
            font-weight: 900;
        }

        .hrdash-donut {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: conic-gradient(#7d83ff 0 {{ $presentPercent }}%, #111827 {{ $presentPercent }}% 100%);
        }

        .hrdash-donut-inner {
            width: 66px;
            height: 66px;
            border-radius: 50%;
            background: #fff;
            display: grid;
            place-items: center;
            font-weight: 900;
            font-size: 22px;
        }

        .hrdash-gender {
            display: flex;
            gap: 12px;
            margin-top: 10px;
            color: #667085;
            font-size: 12px;
        }

        .hrdash-gender b {
            width: 8px;
            height: 8px;
            display: inline-block;
            border-radius: 50%;
            margin-right: 5px;
        }

        .hrdash-main-grid {
            display: grid;
            grid-template-columns: 1.4fr .75fr .75fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .hrdash-card {
            padding: 16px;
        }

        .hrdash-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .hrdash-card-head h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
        }

        .hrdash-date-pill {
            font-size: 11px;
            color: #667085;
            background: #f7f8fc;
            border: 1px solid #edf0f7;
            padding: 6px 9px;
            border-radius: 999px;
        }

        .hrdash-table-wrap {
            overflow: auto;
        }

        .hrdash-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        .hrdash-table th {
            text-align: left;
            color: #667085;
            font-size: 11px;
            text-transform: uppercase;
            padding: 12px 10px;
            background: #f8f9fd;
            border-bottom: 1px solid #edf0f7;
        }

        .hrdash-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f2f7;
            font-size: 13px;
            color: #344054;
            vertical-align: middle;
        }

        .hrdash-emp {
            display: flex;
            align-items: center;
            gap: 9px;
            font-weight: 800;
            color: #111827;
        }

        .hrdash-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6c63ff, #e879f9);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .hrdash-btns {
            display: flex;
            gap: 7px;
        }

        .hrdash-mini-btn {
            border: 0;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 800;
        }

        .hrdash-mini-btn.accept {
            color: #16a34a;
            background: #ecfdf3;
        }

        .hrdash-mini-btn.reject {
            color: #ef4444;
            background: #fff1f2;
        }

        .hrdash-list {
            display: grid;
            gap: 12px;
        }

        .hrdash-person {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .hrdash-person-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .hrdash-person-name {
            display: block;
            font-size: 13px;
            font-weight: 900;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
        }

        .hrdash-person-code {
            display: block;
            color: #98a2b3;
            font-size: 11px;
            margin-top: 1px;
        }

        .hrdash-chip {
            font-size: 11px;
            font-weight: 900;
            padding: 6px 9px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .hrdash-chip.green {
            background: #ecfdf3;
            color: #16a34a;
        }

        .hrdash-chip.red {
            background: #fff1f2;
            color: #ef4444;
        }

        .hrdash-chip.blue {
            background: #eef4ff;
            color: #4f46e5;
        }

        .hrdash-bottom-grid {
            display: grid;
            grid-template-columns: .9fr .9fr 1.2fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .hrdash-bars {
            display: grid;
            gap: 11px;
        }

        .hrdash-bar-row {
            display: grid;
            grid-template-columns: 90px 1fr 34px;
            align-items: center;
            gap: 9px;
        }

        .hrdash-bar-row label {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            color: #667085;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hrdash-bar-track {
            height: 8px;
            border-radius: 999px;
            background: #eef0f6;
            overflow: hidden;
        }

        .hrdash-bar-fill {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #6c63ff, #8b8cff);
        }

        .hrdash-bar-fill.purple {
            background: linear-gradient(90deg, #8b5cf6, #d946ef);
        }

        .hrdash-bar-row strong {
            font-size: 12px;
            text-align: right;
        }

        .hrdash-action-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .hrdash-action {
            text-decoration: none;
            border: 1px solid #edf0f7;
            background: #f8f9fd;
            border-radius: 14px;
            padding: 12px;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hrdash-action:hover {
            background: #f1f3ff;
            color: #6c63ff;
            text-decoration: none;
        }

        .hrdash-action i {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
            display: grid;
            place-items: center;
            color: #6c63ff;
        }

        .hrdash-action span {
            font-size: 12px;
            font-weight: 900;
        }

        .hrdash-empty {
            padding: 18px;
            text-align: center;
            color: #98a2b3;
            font-size: 13px;
        }

        @media(max-width:1300px) {
            .hrdash-overview {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .hrdash-main-grid,
            .hrdash-bottom-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width:768px) {
            .hrdash-page {
                padding: 14px;
            }

            .hrdash-top {
                flex-direction: column;
                align-items: flex-start;
            }

            .hrdash-top-actions,
            .hrdash-search,
            .hrdash-manage-btn {
                width: 100%;
            }

            .hrdash-overview {
                grid-template-columns: 1fr;
            }

            .hrdash-total-card {
                grid-template-columns: 1fr;
            }

            .hrdash-action-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="hrdash-page">
        <div class="hrdash-wrap">

            <div class="hrdash-top">
                <div class="hrdash-greeting">
                    <h1>Good Morning, <span>{{ $dashboard['user_name'] ?? auth()->user()->name }}</span></h1>
                    <p>Here’s what’s happening with the HRMS today</p>
                </div>

                <div class="hrdash-top-actions">
                    <div class="hrdash-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search employee, attendance, payroll..." readonly>
                    </div>

                    @if (!empty($actions[0]['url']))
                        <a href="{{ $actions[0]['url'] }}" class="hrdash-manage-btn">
                            <i class="fas fa-users-cog"></i> Manage Employee
                        </a>
                    @endif
                </div>
            </div>

            <h2 class="hrdash-section-title">Overview</h2>

            <div class="hrdash-overview">
                @forelse($kpiCards as $index => $card)
                    <div class="hrdash-kpi">
                        <div class="hrdash-kpi-head">
                            <div class="hrdash-kpi-icon">
                                <i class="{{ $card['icon'] ?? 'fas fa-chart-pie' }}"></i>
                            </div>
                            <span class="hrdash-trend {{ $index % 2 == 0 ? 'up' : 'down' }}">
                                {{ $index % 2 == 0 ? '↑ 2.5%' : '↓ 1.5%' }}
                            </span>
                        </div>
                        <strong>{{ $card['value'] ?? 0 }}</strong>
                        <span>{{ $card['label'] ?? 'Metric' }}</span>
                    </div>
                @empty
                    <div class="hrdash-kpi">
                        <div class="hrdash-empty">No dashboard cards available.</div>
                    </div>
                @endforelse

                <div class="hrdash-total-card">
                    <div>
                        <span>Total Employees</span>
                        <strong>{{ $totalEmployees }}</strong>
                        <div class="hrdash-gender">
                            <div><b style="background:#111827"></b>Men</div>
                            <div><b style="background:#7d83ff"></b>Women</div>
                        </div>
                    </div>

                    <div class="hrdash-donut">
                        <div class="hrdash-donut-inner">{{ $totalEmployees }}</div>
                    </div>
                </div>
            </div>

            <div class="hrdash-main-grid">
                <div class="hrdash-card">
                    <div class="hrdash-card-head">
                        <h3>Leave Request</h3>
                        <span class="hrdash-date-pill">{{ now()->format('d M Y') }}</span>
                    </div>

                    <div class="hrdash-table-wrap">
                        <table class="hrdash-table">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Leave Type</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Reason</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($dashboard['leave_requests'] ?? []) as $leave)
                                    <tr>
                                        <td>{{ $leave->employee->employee_code ?? 'EMP-000' }}</td>
                                        <td>
                                            <div class="hrdash-emp">
                                                <span
                                                    class="hrdash-avatar">{{ strtoupper(substr($leave->employee->name ?? 'E', 0, 1)) }}</span>
                                                {{ $leave->employee->name ?? '-' }}
                                            </div>
                                        </td>
                                        <td>{{ $leave->type ?? '-' }}</td>
                                        <td>{{ !empty($leave->from_date) ? \Carbon\Carbon::parse($leave->from_date)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td>{{ !empty($leave->to_date) ? \Carbon\Carbon::parse($leave->to_date)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($leave->reason ?? '-', 35) }}</td>
                                        <td>
                                            <div class="hrdash-btns">
                                                <button class="hrdash-mini-btn accept" type="button">✓ Accept</button>
                                                <button class="hrdash-mini-btn reject" type="button">× Reject</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td>EMP-0001</td>
                                        <td>
                                            <div class="hrdash-emp"><span class="hrdash-avatar">A</span>Ahmed Butt</div>
                                        </td>
                                        <td>Casual Leave</td>
                                        <td>{{ now()->format('d/m/Y') }}</td>
                                        <td>{{ now()->addDay()->format('d/m/Y') }}</td>
                                        <td>Going to hospital</td>
                                        <td>
                                            <div class="hrdash-btns">
                                                <button class="hrdash-mini-btn accept" type="button">✓ Accept</button>
                                                <button class="hrdash-mini-btn reject" type="button">× Reject</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>EMP-0002</td>
                                        <td>
                                            <div class="hrdash-emp"><span class="hrdash-avatar">A</span>Ali Raza</div>
                                        </td>
                                        <td>Sick Leave</td>
                                        <td>{{ now()->format('d/m/Y') }}</td>
                                        <td>{{ now()->addDay()->format('d/m/Y') }}</td>
                                        <td>Fever</td>
                                        <td>
                                            <div class="hrdash-btns">
                                                <button class="hrdash-mini-btn accept" type="button">✓ Accept</button>
                                                <button class="hrdash-mini-btn reject" type="button">× Reject</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="hrdash-card">
                    <div class="hrdash-card-head">
                        <h3>Quick Actions</h3>
                        <span class="hrdash-date-pill">{{ count($actions) }} Items</span>
                    </div>

                    <div class="hrdash-action-grid">
                        @forelse($actions as $action)
                            <a href="{{ $action['url'] }}" class="hrdash-action">
                                <i class="{{ $action['icon'] ?? 'fas fa-arrow-right' }}"></i>
                                <span>{{ $action['label'] }}</span>
                            </a>
                        @empty
                            <div class="hrdash-empty">No quick actions</div>
                        @endforelse
                    </div>
                </div>

                <div class="hrdash-card">
                    <div class="hrdash-card-head">
                        <h3>Attendance Status</h3>
                        <span class="hrdash-date-pill">{{ now()->format('d M') }}</span>
                    </div>

                    <div class="hrdash-list">
                        <div class="hrdash-person">
                            <div class="hrdash-person-left">
                                <span class="hrdash-avatar">P</span>
                                <div><span class="hrdash-person-name">Present</span><span
                                        class="hrdash-person-code">Today</span></div>
                            </div>
                            <span class="hrdash-chip green">{{ $present }}</span>
                        </div>
                        <div class="hrdash-person">
                            <div class="hrdash-person-left">
                                <span class="hrdash-avatar">L</span>
                                <div><span class="hrdash-person-name">Late Arrival</span><span
                                        class="hrdash-person-code">Today</span></div>
                            </div>
                            <span class="hrdash-chip red">{{ $late }}</span>
                        </div>
                        <div class="hrdash-person">
                            <div class="hrdash-person-left">
                                <span class="hrdash-avatar">A</span>
                                <div><span class="hrdash-person-name">Absent</span><span
                                        class="hrdash-person-code">Today</span></div>
                            </div>
                            <span class="hrdash-chip red">{{ $absent }}</span>
                        </div>
                        <div class="hrdash-person">
                            <div class="hrdash-person-left">
                                <span class="hrdash-avatar">O</span>
                                <div><span class="hrdash-person-name">Early Out</span><span
                                        class="hrdash-person-code">Today</span></div>
                            </div>
                            <span class="hrdash-chip blue">{{ $earlyOut }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hrdash-bottom-grid">
                <div class="hrdash-card">
                    <div class="hrdash-card-head">
                        <h3>Monthly Attendance</h3>
                        <span class="hrdash-date-pill">{{ now()->format('M Y') }}</span>
                    </div>

                    <div class="hrdash-bars">
                        @forelse($monthly['labels'] ?? [] as $index => $label)
                            @if ($index % 3 === 0 || $index === count($monthly['labels']) - 1)
                                @php
                                    $presentValue = (int) ($monthly['present'][$index] ?? 0);
                                    $width = max(3, ($presentValue / $maxMonthly) * 100);
                                @endphp
                                <div class="hrdash-bar-row">
                                    <label>{{ now()->format('M') }} {{ $label }}</label>
                                    <div class="hrdash-bar-track">
                                        <span class="hrdash-bar-fill" style="width:{{ $width }}%"></span>
                                    </div>
                                    <strong>{{ $presentValue }}</strong>
                                </div>
                            @endif
                        @empty
                            <div class="hrdash-empty">No monthly data</div>
                        @endforelse
                    </div>
                </div>

                <div class="hrdash-card">
                    <div class="hrdash-card-head">
                        <h3>Department</h3>
                        <span class="hrdash-date-pill">Employees</span>
                    </div>

                    <div class="hrdash-bars">
                        @forelse($departments['labels'] ?? [] as $index => $label)
                            @php
                                $value = (int) ($departments['values'][$index] ?? 0);
                                $width = max(3, ($value / $maxDept) * 100);
                            @endphp
                            <div class="hrdash-bar-row">
                                <label>{{ $label }}</label>
                                <div class="hrdash-bar-track">
                                    <span class="hrdash-bar-fill purple" style="width:{{ $width }}%"></span>
                                </div>
                                <strong>{{ $value }}</strong>
                            </div>
                        @empty
                            <div class="hrdash-empty">No department data</div>
                        @endforelse
                    </div>
                </div>

                <div class="hrdash-card">
                    <div class="hrdash-card-head">
                        <h3>Recent Activity</h3>
                        <span class="hrdash-date-pill">{{ now()->format('d M Y') }}</span>
                    </div>

                    <div class="hrdash-list">
                        @forelse($activities as $activity)
                            <div class="hrdash-person">
                                <div class="hrdash-person-left">
                                    <span class="hrdash-avatar">
                                        <i class="{{ $activity['icon'] ?? 'fas fa-circle' }}"></i>
                                    </span>
                                    <div>
                                        <span class="hrdash-person-name">{{ $activity['title'] ?? '-' }}</span>
                                        <span class="hrdash-person-code">
                                            {{ \Illuminate\Support\Str::limit($activity['description'] ?? '', 38) }}
                                        </span>
                                    </div>
                                </div>
                                <span class="hrdash-chip blue">
                                    {{ !empty($activity['time']) ? \Carbon\Carbon::parse($activity['time'])->diffForHumans() : '-' }}
                                </span>
                            </div>
                        @empty
                            <div class="hrdash-empty">No recent activity</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
