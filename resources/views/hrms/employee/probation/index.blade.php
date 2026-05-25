@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Probation / Internship')

@section('_content')
    <style>
        :root {
            --orb-primary: #4B00E8;
            --orb-secondary: #8600EE;
            --orb-bg: #F6F7FB;
            --orb-border: #E7EAF3;
            --orb-text: #101828;
            --orb-muted: #667085;
            --orb-soft: #F4F2FF;
            --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
        }

        .eo-page {
            min-height: calc(100vh - 90px);
            padding: 16px 10px 30px;
            background: var(--orb-bg);
        }

        .eo-container {
            max-width: 1320px;
            margin: 0 auto;
        }

        .eo-header,
        .eo-card {
            background: #fff;
            border: 1px solid var(--orb-border);
            border-radius: 20px;
            box-shadow: var(--orb-shadow);
        }

        .eo-header {
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
        }

        .eo-title {
            margin: 0;
            color: var(--orb-text);
            font-size: 24px;
            font-weight: 900;
        }

        .eo-subtitle {
            margin: 4px 0 0;
            color: var(--orb-muted);
            font-size: 13px;
            font-weight: 600;
        }

        .eo-card {
            overflow: hidden;
        }

        .eo-filter-inside {
            padding: 14px 16px;
            border-bottom: 1px solid var(--orb-border);
            background: #FCFCFD;
        }

        .eo-filter-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr 1fr auto;
            gap: 10px;
            align-items: end;
        }

        .eo-field label {
            display: block;
            margin: 0 0 6px;
            color: var(--orb-muted);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .eo-control {
            width: 100%;
            height: 42px;
            border-radius: 12px !important;
            border: 1px solid var(--orb-border) !important;
            background: #F9FAFB !important;
            color: var(--orb-text) !important;
            font-size: 13px;
            font-weight: 700;
            padding: 8px 12px;
            outline: none;
        }

        .eo-control:focus {
            border-color: rgba(75, 0, 232, .45) !important;
            background: #fff !important;
            box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
        }

        .eo-btn {
            min-height: 42px;
            border-radius: 12px;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            text-decoration: none !important;
            cursor: pointer;
            white-space: nowrap;
        }

        .eo-btn-light {
            background: #fff;
            color: var(--orb-text);
            border-color: var(--orb-border);
        }

        .eo-table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .eo-table {
            min-width: 1120px;
            margin-bottom: 0 !important;
        }

        .eo-table th {
            background: #F8FAFC;
            color: #667085;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .45px;
            border-bottom: 1px solid var(--orb-border);
            white-space: nowrap;
            padding: 12px 14px;
        }

        .eo-table td {
            vertical-align: middle;
            color: var(--orb-text);
            font-size: 13px;
            font-weight: 650;
            border-bottom: 1px solid #F1F3F8;
            padding: 12px 14px;
        }

        .eo-table tbody tr:hover {
            background: #FCFAFF;
        }

        .eo-table th:nth-child(1),
        .eo-table td:nth-child(1) {
            width: 130px;
        }

        .eo-table th:nth-child(2),
        .eo-table td:nth-child(2) {
            width: 180px;
        }

        .eo-table th:nth-child(3),
        .eo-table td:nth-child(3) {
            width: 150px;
        }

        .eo-table th:nth-child(4),
        .eo-table td:nth-child(4) {
            width: 150px;
        }

        .eo-table th:nth-child(5),
        .eo-table td:nth-child(5) {
            width: 130px;
        }

        .eo-table th:nth-child(6),
        .eo-table td:nth-child(6) {
            width: 120px;
        }

        .eo-table th:nth-child(7),
        .eo-table td:nth-child(7) {
            width: 120px;
        }

        .eo-table th:nth-child(8),
        .eo-table td:nth-child(8) {
            width: 120px;
        }

        .eo-table th:nth-child(9),
        .eo-table td:nth-child(9) {
            width: 105px;
        }

        .eo-code {
            display: inline-flex;
            padding: 6px 9px;
            border-radius: 10px;
            background: #F4F2FF;
            color: var(--orb-primary);
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .eo-name {
            font-weight: 900;
            color: var(--orb-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 170px;
        }

        .eo-muted-text {
            font-size: 12px;
            color: var(--orb-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 145px;
        }

        .eo-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            white-space: nowrap;
            background: #F2F4F7;
            color: #667085;
        }

        .eo-pill-active {
            background: rgba(18, 183, 106, .10);
            color: #12B76A;
        }

        .eo-pill-warning {
            background: rgba(247, 144, 9, .12);
            color: #F79009;
        }

        .eo-pill-purple {
            background: rgba(75, 0, 232, .08);
            color: #4B00E8;
        }

        .eo-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            white-space: nowrap;
        }

        .eo-icon-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--orb-border);
            border-radius: 11px;
            background: #fff;
            color: #667085;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: .18s ease;
            text-decoration: none !important;
        }

        .eo-icon-btn:hover {
            color: #fff;
            background: var(--orb-primary);
            border-color: var(--orb-primary);
        }

        .eo-more {
            position: relative;
        }

        .eo-more-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--orb-border);
            border-radius: 11px;
            background: #F8FAFC;
            color: #667085;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .eo-more-menu {
            position: absolute;
            right: 0;
            top: 40px;
            min-width: 250px;
            background: #fff;
            border: 1px solid var(--orb-border);
            border-radius: 16px;
            box-shadow: 0 18px 45px rgba(16, 24, 40, .16);
            padding: 8px;
            z-index: 30;
            display: none;
        }

        .eo-more.open .eo-more-menu {
            display: block;
        }

        .eo-menu-item {
            width: 100%;
            min-height: 38px;
            border: 0;
            background: #fff;
            border-radius: 11px;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--orb-text);
            font-size: 12px;
            font-weight: 850;
            text-align: left;
            cursor: pointer;
        }

        .eo-menu-item:hover {
            background: #F4F2FF;
            color: var(--orb-primary);
        }

        .eo-menu-form {
            margin: 0;
        }

        .eo-menu-form-box {
            padding: 8px;
            border-radius: 12px;
            background: #F8FAFC;
            margin-top: 6px;
        }

        .eo-menu-form-box label {
            font-size: 10px;
            font-weight: 900;
            color: var(--orb-muted);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .eo-date {
            height: 34px;
            border-radius: 10px;
            border: 1px solid var(--orb-border);
            padding: 5px 8px;
            font-size: 12px;
            font-weight: 700;
            color: var(--orb-text);
            width: 100%;
            margin-bottom: 6px;
            background: #fff;
        }

        .eo-empty {
            text-align: center;
            color: #667085;
            font-weight: 800;
            padding: 24px;
        }

        @media(max-width:1100px) {
            .eo-filter-grid {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        @media(max-width:991px) {
            .eo-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .eo-filter-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media(max-width:576px) {
            .eo-page {
                padding: 12px 8px 22px;
            }

            .eo-header {
                border-radius: 16px;
                padding: 14px;
            }

            .eo-title {
                font-size: 21px;
            }

            .eo-subtitle {
                font-size: 12px;
            }

            .eo-filter-grid {
                grid-template-columns: 1fr;
            }

            .eo-btn {
                width: 100%;
            }

            .eo-filter-inside {
                padding: 12px;
            }
        }
    </style>

    <div class="eo-page">
        <div class="eo-container">

            <div class="eo-header">
                <div>
                    <h1 class="eo-title">Probation / Internship</h1>
                    <p class="eo-subtitle">Track probation employees and active internships.</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <div class="eo-card">
                <div class="eo-filter-inside">
                    <div class="eo-filter-grid">
                        <div class="eo-field">
                            <label>Search</label>
                            <input type="text" id="filterSearch" class="eo-control" placeholder="Search employee...">
                        </div>

                        <div class="eo-field">
                            <label>Department</label>
                            <select id="filterDepartment" class="eo-control">
                                <option value="">All Departments</option>
                                @foreach ($departments ?? [] as $dept)
                                    <option value="{{ strtolower($dept->name ?? '') }}">{{ $dept->name ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="eo-field">
                            <label>Status</label>
                            <select id="filterStatus" class="eo-control">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="extended">Extended</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div class="eo-field">
                            <label>Employment Type</label>
                            <select id="filterEmploymentType" class="eo-control">
                                <option value="">All Type</option>
                                <option value="probation">Probation</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>

                        <div class="eo-field">
                            <label>&nbsp;</label>
                            <button type="button" id="resetFilter" class="eo-btn eo-btn-light">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div class="eo-table-wrap">
                    <table class="table table-hover eo-table" id="probationInternshipTable">
                        <thead>
                            <tr>
                                <th>Emp Code</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($employees as $employee)
                                @php
                                    $stage = strtolower($employee->employee_stage ?? '');
                                    $type = strtolower($employee->employment_type ?? '');
                                    $isIntern = $stage === 'internship' || ($stage === '' && $type === 'intern');
                                    $displayType = $isIntern ? 'Internship' : 'Probation';

                                    $startDate = $isIntern
                                        ? $employee->internship_start_date
                                        : ($employee->probation_start_date ?:
                                        $employee->joining_date);

                                    $endDate = $isIntern
                                        ? ($employee->internship_extended_to ?:
                                        $employee->internship_end_date)
                                        : $employee->probation_end_date;

                                    $status = $isIntern
                                        ? ($employee->internship_extended_to
                                            ? 'extended'
                                            : 'active')
                                        : ($employee->probation_status ?:
                                        'pending');

                                    $statusClass = in_array($status, ['completed', 'active'], true)
                                        ? 'eo-pill-active'
                                        : ($status === 'extended'
                                            ? 'eo-pill-warning'
                                            : 'eo-pill-purple');
                                @endphp

                                <tr data-search="{{ strtolower(($employee->name ?? '') . ' ' . ($employee->employee_code ?? '') . ' ' . ($employee->department_name ?? '') . ' ' . ($employee->designation_name ?? '') . ' ' . $displayType . ' ' . $status) }}"
                                    data-department="{{ strtolower($employee->department_name ?? '') }}"
                                    data-status="{{ strtolower($status) }}"
                                    data-employment-type="{{ $isIntern ? 'intern' : 'probation' }}">
                                    <td><span class="eo-code">{{ $employee->employee_code ?? 'EMP-' . $employee->id }}</span>
                                    </td>

                                    <td>
                                        <div class="eo-name" title="{{ $employee->name ?? '-' }}">
                                            {{ $employee->name ?? '-' }}</div>
                                    </td>

                                    <td>
                                        <div class="eo-muted-text" title="{{ $employee->department_name ?? '-' }}">
                                            {{ $employee->department_name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="eo-muted-text" title="{{ $employee->designation_name ?? '-' }}">
                                            {{ $employee->designation_name ?? '-' }}</div>
                                    </td>

                                    <td><span class="eo-pill eo-pill-purple">{{ $displayType }}</span></td>

                                    <td>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : '-' }}</td>
                                    <td>{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : '-' }}</td>

                                    <td>
                                        <span
                                            class="eo-pill {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                    </td>

                                    <td>
                                        <div class="eo-actions">
                                            @if (Route::has('hrms.employees.show'))
                                                <a href="{{ route('hrms.employees.show', $employee->id) }}"
                                                    class="eo-icon-btn" title="View Employee">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif

                                            @if (Route::has('hrms.employees.edit'))
                                                <a href="{{ route('hrms.employees.edit', $employee->id) }}"
                                                    class="eo-icon-btn" title="Edit Employee">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            <div class="eo-more">
                                                <button type="button" class="eo-more-btn" title="More Actions">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>

                                                <div class="eo-more-menu">
                                                    @if (!$isIntern && $status !== 'completed' && Route::has('hrms.employees.probation.mark_permanent'))
                                                        <form
                                                            action="{{ route('hrms.employees.probation.mark_permanent', $employee->id) }}"
                                                            method="POST" class="eo-menu-form">
                                                            @csrf
                                                            <button type="submit" class="eo-menu-item"
                                                                onclick="return confirm('Mark this employee as permanent?')">
                                                                <i class="fas fa-user-check"></i> Mark Permanent
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if ($isIntern && Route::has('hrms.employees.internship.extend'))
                                                        <form
                                                            action="{{ route('hrms.employees.internship.extend', $employee->id) }}"
                                                            method="POST" class="eo-menu-form eo-menu-form-box">
                                                            @csrf
                                                            <label>Extend Internship</label>
                                                            <input type="date" name="internship_extended_to"
                                                                class="eo-date"
                                                                value="{{ $employee->internship_extended_to ?: $employee->internship_end_date }}"
                                                                required>
                                                            <button type="submit" class="eo-menu-item">
                                                                <i class="fas fa-calendar-plus"></i> Extend
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if ($isIntern && Route::has('hrms.employees.internship.complete'))
                                                        <form
                                                            action="{{ route('hrms.employees.internship.complete', $employee->id) }}"
                                                            method="POST" class="eo-menu-form eo-menu-form-box">
                                                            @csrf
                                                            <label>Complete Internship</label>
                                                            <select name="next_stage" class="eo-date" required>
                                                                <option value="probation">Move to Probation</option>
                                                                <option value="permanent">Move Permanent</option>
                                                            </select>
                                                            <input type="number" name="actual_salary" class="eo-date"
                                                                min="0" step="1" placeholder="Salary">
                                                            <button type="submit" class="eo-menu-item"
                                                                onclick="return confirm('Complete this internship and update lifecycle stage?')">
                                                                <i class="fas fa-check-circle"></i> Complete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="serverEmptyRow">
                                    <td colspan="9" class="eo-empty">No probation or internship records found.</td>
                                </tr>
                            @endforelse

                            <tr id="filterEmptyRow" style="display:none;">
                                <td colspan="9" class="eo-empty">No matching probation or internship record found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('filterSearch');
            const departmentFilter = document.getElementById('filterDepartment');
            const statusFilter = document.getElementById('filterStatus');
            const employmentTypeFilter = document.getElementById('filterEmploymentType');
            const resetBtn = document.getElementById('resetFilter');
            const rows = document.querySelectorAll('#probationInternshipTable tbody tr[data-search]');
            const filterEmptyRow = document.getElementById('filterEmptyRow');

            function applyFilters() {
                const search = (searchInput.value || '').toLowerCase().trim();
                const department = (departmentFilter.value || '').toLowerCase().trim();
                const status = (statusFilter.value || '').toLowerCase().trim();
                const employmentType = (employmentTypeFilter.value || '').toLowerCase().trim();

                let visibleCount = 0;

                rows.forEach(function(row) {
                    const matchSearch = !search || (row.dataset.search || '').includes(search);
                    const matchDepartment = !department || (row.dataset.department || '') === department;
                    const matchStatus = !status || (row.dataset.status || '') === status;
                    const matchEmploymentType = !employmentType || (row.dataset.employmentType || '') ===
                        employmentType;

                    const show = matchSearch && matchDepartment && matchStatus && matchEmploymentType;
                    row.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });

                if (filterEmptyRow) {
                    filterEmptyRow.style.display = rows.length > 0 && visibleCount === 0 ? '' : 'none';
                }
            }

            searchInput.addEventListener('keyup', applyFilters);
            departmentFilter.addEventListener('change', applyFilters);
            statusFilter.addEventListener('change', applyFilters);
            employmentTypeFilter.addEventListener('change', applyFilters);

            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                departmentFilter.value = '';
                statusFilter.value = '';
                employmentTypeFilter.value = '';
                applyFilters();
            });

            document.querySelectorAll('.eo-more-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();

                    document.querySelectorAll('.eo-more').forEach(function(box) {
                        if (box !== btn.closest('.eo-more')) {
                            box.classList.remove('open');
                        }
                    });

                    btn.closest('.eo-more').classList.toggle('open');
                });
            });

            document.addEventListener('click', function() {
                document.querySelectorAll('.eo-more').forEach(function(box) {
                    box.classList.remove('open');
                });
            });

            document.querySelectorAll('.eo-more-menu').forEach(function(menu) {
                menu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
        });
    </script>
@endsection