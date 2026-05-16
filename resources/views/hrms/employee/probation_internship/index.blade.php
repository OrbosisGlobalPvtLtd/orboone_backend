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

    .eo-field label,
    .eo-action-body label {
        display: block;
        margin: 0 0 6px;
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .eo-control,
    .eo-date {
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

    .eo-control:focus,
    .eo-date:focus {
        border-color: rgba(75, 0, 232, .45) !important;
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    .eo-readonly-date {
        background: #F8FAFC !important;
        color: #344054 !important;
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
        min-width: 1160px;
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

    .eo-emp-cell {
        min-width: 150px;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .eo-name {
        font-weight: 900;
        color: var(--orb-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }

    .eo-code-under {
        display: inline-flex;
        width: max-content;
        padding: 5px 8px;
        border-radius: 9px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .eo-table th:nth-child(5),
    .eo-table td:nth-child(5) {
        min-width: 118px;
    }

    .eo-table th:nth-child(6),
    .eo-table td:nth-child(6) {
        min-width: 155px;
        width: 155px;
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

    .eo-pill-danger {
        background: rgba(240, 68, 56, .10);
        color: #F04438;
    }

    .eo-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        white-space: nowrap;
    }

    .eo-icon-btn,
    .eo-more-btn {
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
        cursor: pointer;
    }

    .eo-icon-btn:hover,
    .eo-more-btn:hover {
        color: #fff;
        background: var(--orb-primary);
        border-color: var(--orb-primary);
    }

    .eo-empty {
        text-align: center;
        color: #667085;
        font-weight: 800;
        padding: 24px;
    }

    .eo-highlight-row {
        background: #FFF7ED !important;
        box-shadow: inset 4px 0 0 #F79009;
    }

    .modal-backdrop {
        z-index: 1240 !important;
        background: #0F172A !important;
    }

    .modal-backdrop.show {
        opacity: .58 !important;
    }

    .modal {
        z-index: 1250 !important;
    }

    .eo-life-modal .modal-dialog {
        max-width: 780px;
    }

    .eo-modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
    }

    .eo-modal-header {
        padding: 18px 22px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        border-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .eo-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 950;
    }

    .eo-modal-subtitle {
        margin-top: 4px;
        font-size: 12px;
        color: rgba(255, 255, 255, .78);
        font-weight: 700;
    }

    .eo-modal-close {
        color: #fff;
        opacity: 1;
        text-shadow: none;
        outline: none;
    }

    .eo-modal-body {
        padding: 18px;
        background: #fff;
        max-height: 78vh;
        overflow-y: auto;
    }

    .eo-action-card {
        border: 1px solid #EEF1F6;
        border-radius: 18px;
        background: #fff;
        overflow: hidden;
        margin-bottom: 14px;
    }

    .eo-action-card:last-child {
        margin-bottom: 0;
    }

    .eo-action-card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px 14px;
        background: #F8FAFC;
        border-bottom: 1px solid #EEF1F6;
    }

    .eo-action-icon {
        width: 36px;
        height: 36px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F4F2FF;
        color: var(--orb-primary);
        flex: 0 0 auto;
    }

    .eo-action-title {
        font-size: 13px;
        font-weight: 950;
        color: var(--orb-text);
        line-height: 1.2;
    }

    .eo-action-sub {
        font-size: 11px;
        font-weight: 750;
        color: var(--orb-muted);
        margin-top: 3px;
        line-height: 1.35;
    }

    .eo-action-body {
        padding: 16px;
        background: #fff;
    }

    .eo-info-note {
        padding: 10px 12px;
        border-radius: 13px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 12px;
        font-weight: 800;
        line-height: 1.45;
    }

    .eo-menu-submit {
        width: 100%;
        min-height: 42px;
        border: 0;
        border-radius: 13px;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    }

    .eo-menu-submit-success {
        background: #16A34A;
    }

    .eo-menu-submit-warning {
        background: #F79009;
    }

    .eo-menu-submit-danger {
        background: #DC2626;
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

        .eo-life-modal .modal-dialog {
            margin: 12px;
        }

        .eo-modal-body {
            padding: 14px;
        }
    }
</style>

<div class="eo-page">
    <div class="eo-container">

        <div class="eo-header">
            <div>
                <h1 class="eo-title">Probation / Internship</h1>
                <p class="eo-subtitle">Track active probation, internships, extensions, conversion and permanent status.
                </p>
            </div>
        </div>

        @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
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
                            <option value="extended">Extended</option>
                            <option value="completed">Completed</option>
                            <option value="converted_to_probation">Converted To Probation</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <div class="eo-field">
                        <label>Stage</label>
                        <select id="filterEmploymentType" class="eo-control">
                            <option value="">All Stage</option>
                            <option value="probation">Probation</option>
                            <option value="intern">Internship</option>
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
                            <th>Name</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Stage</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Salary Type</th>
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

                        $effectiveDate = $endDate
                        ? \Carbon\Carbon::parse($endDate)->copy()->addDay()
                        : \Carbon\Carbon::today();

                        $status = $isIntern
                        ? ($employee->internship_status ?:
                        ($employee->internship_extended_to
                        ? 'extended'
                        : 'active'))
                        : ($employee->probation_status ?:
                        'pending');

                        $statusClass = match ($status) {
                        'active', 'completed', 'converted_to_probation' => 'eo-pill-active',
                        'extended' => 'eo-pill-warning',
                        'exited' => 'eo-pill-danger',
                        default => 'eo-pill-purple',
                        };

                        $salaryType = $isIntern
                        ? ((int) ($employee->is_paid_intern ?? 0) === 1
                        ? 'Paid / Stipend'
                        : 'Unpaid')
                        : 'Salary';
                        @endphp

                        <tr id="employee-row-{{ $employee->id }}" data-employee-id="{{ $employee->id }}"
                            data-search="{{ strtolower(($employee->name ?? '') . ' ' . ($employee->employee_code ?? '') . ' ' . ($employee->department_name ?? '') . ' ' . ($employee->designation_name ?? '') . ' ' . $displayType . ' ' . $status) }}"
                            data-department="{{ strtolower($employee->department_name ?? '') }}"
                            data-status="{{ strtolower($status) }}"
                            data-employment-type="{{ $isIntern ? 'intern' : 'probation' }}">

                            <td>
                                <div class="eo-emp-cell">
                                    <div class="eo-name" title="{{ $employee->name ?? '-' }}">
                                        {{ $employee->name ?? '-' }}
                                    </div>
                                    <span class="eo-code-under">
                                        {{ $employee->employee_code ?? 'EMP-' . $employee->id }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="eo-muted-text" title="{{ $employee->department_name ?? '-' }}">
                                    {{ $employee->department_name ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="eo-muted-text" title="{{ $employee->designation_name ?? '-' }}">
                                    {{ $employee->designation_name ?? '-' }}
                                </div>
                            </td>
                            <td><span class="eo-pill eo-pill-purple">{{ $displayType }}</span></td>
                            <td>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : '-' }}</td>
                            <td>{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : '-' }}</td>
                            <td><span
                                    class="eo-pill {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            </td>
                            <td>{{ $salaryType }}</td>

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

                                    <button type="button" class="eo-more-btn" title="Lifecycle Actions"
                                        data-toggle="modal"
                                        data-target="#employeeLifecycleModal{{ $employee->id }}">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
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

        @foreach ($employees as $employee)
        @php
        $stage = strtolower($employee->employee_stage ?? '');
        $type = strtolower($employee->employment_type ?? '');
        $isIntern = $stage === 'internship' || ($stage === '' && $type === 'intern');
        $displayType = $isIntern ? 'Internship' : 'Probation';

        $endDate = $isIntern
        ? ($employee->internship_extended_to ?:
        $employee->internship_end_date)
        : $employee->probation_end_date;

        $effectiveDate = $endDate
        ? \Carbon\Carbon::parse($endDate)->copy()->addDay()
        : \Carbon\Carbon::today();

        $status = $isIntern
        ? ($employee->internship_status ?:
        ($employee->internship_extended_to
        ? 'extended'
        : 'active'))
        : ($employee->probation_status ?:
        'pending');
        @endphp

        <div class="modal fade eo-life-modal" id="employeeLifecycleModal{{ $employee->id }}" tabindex="-1"
            role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content eo-modal-content">
                    <div class="eo-modal-header">
                        <div>
                            <h5 class="eo-modal-title">{{ $employee->name ?? 'Employee' }}</h5>
                            <div class="eo-modal-subtitle">
                                {{ $employee->employee_code ?? 'EMP-' . $employee->id }} · {{ $displayType }} ·
                                Effective actions from {{ $effectiveDate->format('d M Y') }}
                            </div>
                        </div>

                        <button type="button" class="close eo-modal-close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="eo-modal-body">
                        @if (
                        !$isIntern &&
                        !in_array($status, ['completed', 'confirmed'], true) &&
                        Route::has('hrms.employees.probation.mark_permanent'))
                        <div class="eo-action-card">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon"><i class="fas fa-user-check"></i></div>
                                <div>
                                    <div class="eo-action-title">Mark Permanent</div>
                                    <div class="eo-action-sub">Permanent date and salary will apply after
                                        probation end date.</div>
                                </div>
                            </div>

                            <form
                                action="{{ route('hrms.employees.probation.mark_permanent', $employee->id) }}"
                                method="POST">
                                @csrf
                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Permanent Effective Date</label>
                                            <input type="text" class="eo-date eo-readonly-date"
                                                value="{{ $effectiveDate->format('d M Y') }}" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Permanent Salary</label>
                                            <input type="number" name="actual_salary" class="eo-date"
                                                min="0" step="1"
                                                placeholder="Optional salary update">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label>Salary Reason</label>
                                            <input type="text" name="salary_change_reason" class="eo-date"
                                                placeholder="Permanent salary update">
                                        </div>
                                    </div>

                                    <div class="eo-info-note mb-3">
                                        If admin marks early, permanent status and salary will still start from
                                        {{ $effectiveDate->format('d M Y') }}.
                                    </div>

                                    <button type="submit" class="eo-menu-submit"
                                        onclick="return confirm('Mark this employee as permanent? Effective date will be after probation end date.')">
                                        <i class="fas fa-user-check"></i> Mark Permanent
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        @if ($isIntern && Route::has('hrms.employees.internship.extend'))
                        <div class="eo-action-card">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon"><i class="fas fa-calendar-plus"></i></div>
                                <div>
                                    <div class="eo-action-title">Extend Internship</div>
                                    <div class="eo-action-sub">Extend internship and optionally update stipend.
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('hrms.employees.internship.extend', $employee->id) }}"
                                method="POST">
                                @csrf
                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Current End Date</label>
                                            <input type="text" class="eo-date eo-readonly-date"
                                                value="{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : '-' }}"
                                                readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Extend Internship To</label>
                                            <input type="date" name="internship_extended_to"
                                                class="eo-date"
                                                min="{{ $endDate ? \Carbon\Carbon::parse($endDate)->copy()->addDay()->toDateString() : now()->toDateString() }}"
                                                required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>New Stipend / Salary</label>
                                            <input type="number" name="actual_salary" class="eo-date"
                                                min="0" step="1"
                                                placeholder="Leave blank if no change">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Extension Reason</label>
                                            <input type="text" name="reason" class="eo-date"
                                                placeholder="Reason for extension">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label>Salary Reason</label>
                                            <input type="text" name="salary_change_reason" class="eo-date"
                                                placeholder="Stipend update reason">
                                        </div>
                                    </div>

                                    <button type="submit" class="eo-menu-submit eo-menu-submit-warning"
                                        onclick="return confirm('Extend this internship?')">
                                        <i class="fas fa-calendar-plus"></i> Extend Internship
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        @if ($isIntern && Route::has('hrms.employees.internship.complete'))
                        <div class="eo-action-card">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon"><i class="fas fa-check-circle"></i></div>
                                <div>
                                    <div class="eo-action-title">Complete / Convert Internship</div>
                                    <div class="eo-action-sub">Mark completed, move to probation, or move
                                        permanent.</div>
                                </div>
                            </div>

                            <form action="{{ route('hrms.employees.internship.complete', $employee->id) }}"
                                method="POST">
                                @csrf
                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Effective Date</label>
                                            <input type="text" class="eo-date eo-readonly-date"
                                                value="{{ $effectiveDate->format('d M Y') }}" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Action</label>
                                            <select name="next_stage" class="eo-date" required>
                                                <option value="completed">Only Mark Internship Completed
                                                </option>
                                                <option value="probation">Complete & Move to Probation</option>
                                                <!-- <option value="permanent">Complete & Move Permanent</option> -->
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Salary / Stipend</label>
                                            <input type="number" name="actual_salary" class="eo-date"
                                                min="0" step="1"
                                                placeholder="Required only if moving stage">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Salary Reason</label>
                                            <input type="text" name="salary_change_reason" class="eo-date"
                                                placeholder="Internship completion salary">
                                        </div>
                                    </div>

                                    <div class="eo-info-note mb-3">
                                        If admin applies early, lifecycle and salary will still be effective
                                        from {{ $effectiveDate->format('d M Y') }}.
                                    </div>

                                    <button type="submit" class="eo-menu-submit eo-menu-submit-success"
                                        onclick="return confirm('Apply internship completion action? Effective date will be after internship end date.')">
                                        <i class="fas fa-check-circle"></i> Apply Action
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        @if ($isIntern && Route::has('hrms.employees.exit.mark'))
                        <div class="eo-action-card mb-0">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon" style="background:#FEE2E2;color:#DC2626;">
                                    <i class="fas fa-user-times"></i>
                                </div>
                                <div>
                                    <div class="eo-action-title">Internship Exit</div>
                                    <div class="eo-action-sub">Use only if intern will not continue after
                                        internship.</div>
                                </div>
                            </div>

                            <form action="{{ route('hrms.employees.exit.mark', $employee->id) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="employment_status" value="inactive">

                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Internship Exit Date</label>
                                            <input type="date" name="relieving_date" class="eo-date"
                                                value="{{ $effectiveDate->toDateString() }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Exit Reason</label>
                                            <input type="text" name="reason" class="eo-date"
                                                placeholder="Internship completed exit">
                                        </div>
                                    </div>

                                    <button type="submit" class="eo-menu-submit eo-menu-submit-danger"
                                        onclick="return confirm('Exit this intern?')">
                                        <i class="fas fa-user-times"></i> Internship Exit
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach

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

        const urlParams = new URLSearchParams(window.location.search);
        const highlightEmployeeId = urlParams.get('highlight_employee');

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

        function highlightEmployeeFromNotification() {
            if (!highlightEmployeeId) return;

            searchInput.value = '';
            departmentFilter.value = '';
            statusFilter.value = '';
            employmentTypeFilter.value = '';

            applyFilters();

            const row = document.getElementById('employee-row-' + highlightEmployeeId);

            if (row) {
                row.classList.add('eo-highlight-row');

                setTimeout(function() {
                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });
                }, 250);

                setTimeout(function() {
                    row.classList.remove('eo-highlight-row');
                }, 8000);
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

        highlightEmployeeFromNotification();
    });
</script>
@endsection