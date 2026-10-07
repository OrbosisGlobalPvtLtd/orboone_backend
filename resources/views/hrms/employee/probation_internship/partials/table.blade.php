@php
    $departmentOptions = ($departments ?? collect())->mapWithKeys(function($d) {
        $val = strtolower($d->name ?? '');
        return [$val => $d->name ?? '-'];
    })->toArray();
@endphp

<div class="orb-table-card">
    {{-- 1. Card Header --}}
    <div class="orb-table-head d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="orb-table-title-wrap">
            <span class="orb-table-icon"><i class="fas fa-history"></i></span>
            <div>
                <h3>Probation & Internship List</h3>
                <p>Manage employee lifecycle stages, probation timelines, internships and conversion status.</p>
            </div>
        </div>
    </div>

    {{-- 2. Filters Toolbar under Table Header --}}
    <div class="orb-table-tools border-bottom">
        <div class="eo-filter-grid">
            <div class="eo-field">
                <label>Search</label>
                <input type="text" id="filterSearch" class="eo-control" style="height: 38px !important;" placeholder="Search employee...">
            </div>

            <x-form.select
                id="filterDepartment"
                name="department"
                label="Department"
                :options="$departmentOptions"
                placeholder="All Departments"
                :searchable="true"
                wrapper-class="eo-field mb-0"
                class="eo-control"
            />

            <x-form.select
                id="filterStatus"
                name="status"
                label="Status"
                :options="[
                    'active' => 'Active',
                    'scheduled_probation' => 'Scheduled Probation',
                    'scheduled_permanent' => 'Scheduled Permanent',
                    'ongoing' => 'Ongoing',
                    'extended' => 'Extended',
                    'completed' => 'Completed',
                    'pending' => 'Pending'
                ]"
                placeholder="All Status"
                :searchable="true"
                wrapper-class="eo-field mb-0"
                class="eo-control"
            />

            <x-form.select
                id="filterEmploymentType"
                name="stage"
                label="Stage"
                :options="[
                    'probation' => 'Probation',
                    'intern' => 'Internship'
                ]"
                placeholder="All Stage"
                :searchable="true"
                wrapper-class="eo-field mb-0"
                class="eo-control"
            />

            <div class="eo-field eo-filter-actions-col">
                <label class="d-none d-sm-block">&nbsp;</label>
                <div class="eo-filter-actions-wrap">
                    <x-ui.button
                        type="button"
                        id="btnProbationFilterSubmit"
                        variant="search"
                        icon="fas fa-search mr-1"
                        title="Search / Apply Filter"
                        class="orbo-button-flex"
                    >
                        Search
                    </x-ui.button>
                    <x-ui.button
                        type="button"
                        id="resetFilter"
                        variant="reset"
                        icon="fas fa-undo mr-1"
                        title="Reset Filters"
                        class="orbo-button-flex"
                    >
                        Reset
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. DataTable Toolbar (Length left, Reusable Export Buttons Component right) --}}
    <div class="orb-table-tools-bar">
        <div id="probationLengthBox" class="orb-table-length-box"></div>
        <div id="probationExportButtons" class="orb-table-export-buttons">
            <x-ui.export-buttons table="probationInternshipTable" />
        </div>
    </div>

    {{-- 4. Main Table (Responsive Horizontal Scroll) --}}
    <div class="orb-table-wrap">
        <table class="table eo-table" id="probationInternshipTable">
            <thead>
                <tr>
                    <th style="width: 45px;" class="text-center">#</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Stage</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Salary Type</th>
                    <th class="text-right" style="width: 100px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                @php
                    $stage = strtolower($employee->employee_stage ?? '');
                    $type = strtolower($employee->employment_type ?? '');
                    $isIntern = $stage === 'internship' || ($stage === '' && $type === 'intern');
                    $displayType = $isIntern ? 'Internship' : 'Probation';

                    $startDate = $isIntern
                        ? $employee->internship_start_date
                        : ($employee->probation_start_date ?: $employee->joining_date);

                    $endDate = $isIntern
                        ? ($employee->internship_extended_to ?: $employee->internship_end_date)
                        : $employee->probation_end_date;

                    if (!$isIntern && !$endDate && $startDate) {
                        $durationType = $employee->probation_duration_type ?? 'months';
                        $durationValue = (int) ($employee->probation_duration_value ?? $employee->probation_months ?? 3);
                        $calc = app(\App\Services\HRMS\Employee\EmployeeLifecycleService::class)->calculateProbationDates($startDate, $durationType, $durationValue);
                        $endDate = $calc['probation_end_date'];
                    }

                    $effectiveDate = $endDate
                        ? \Carbon\Carbon::parse($endDate)->copy()->addDay()
                        : \Carbon\Carbon::today()->addDay();

                    $status = $isIntern
                        ? ($employee->internship_status ?: ($employee->internship_extended_to ? 'extended' : 'active'))
                        : ($employee->probation_status ?: 'pending');

                    $statusClass = match ($status) {
                        'active', 'completed' => 'eo-pill-active',
                        'scheduled_probation', 'scheduled_permanent' => 'eo-pill-purple',
                        'ongoing' => 'eo-pill-purple',
                        'extended' => 'eo-pill-warning',
                        'exited' => 'eo-pill-danger',
                        default => 'eo-pill-purple',
                    };

                    $salaryType = $isIntern
                        ? ((int) ($employee->is_paid_intern ?? 0) === 1 ? 'Paid / Stipend' : 'Unpaid')
                        : 'Salary';
                @endphp

                <tr id="employee-row-{{ $employee->id }}" data-employee-id="{{ $employee->id }}"
                    data-search="{{ strtolower(($employee->name ?? '') . ' ' . ($employee->employee_code ?? '') . ' ' . ($employee->department_name ?? '') . ' ' . ($employee->designation_name ?? '') . ' ' . $displayType . ' ' . $status) }}"
                    data-department="{{ strtolower($employee->department_name ?? '') }}"
                    data-status="{{ strtolower($status) }}"
                    data-employment-type="{{ $isIntern ? 'intern' : 'probation' }}">

                    <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
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
                    <td>
                        {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : '-' }}
                        @if ($isIntern && $status === 'scheduled_probation' && !empty($employee->probation_start_date))
                            <div style="font-size: 11px; color: #6366F1; font-weight: 600; margin-top: 2px;">
                                Probation: {{ \Carbon\Carbon::parse($employee->probation_start_date)->format('d M Y') }}
                            </div>
                        @endif
                    </td>
                    <td><span class="eo-pill {{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $status)) }}</span></td>
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
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 5. Table Footer (Info & Pagination) --}}
    <div class="eo-table-footer orb-pagination-wrapper">
        <div id="probationInfoBox" class="orb-pagination-info"></div>
        <div id="probationPaginationBox"></div>
    </div>
</div>
