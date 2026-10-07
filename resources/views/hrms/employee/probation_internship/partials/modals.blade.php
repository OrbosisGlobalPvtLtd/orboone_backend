@foreach ($employees as $employee)
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

                <button type="button" class="close eo-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="eo-modal-body">
                {{-- Mark Permanent for Probation Employees --}}
                @if (
                    !$isIntern &&
                    !in_array($status, ['completed', 'confirmed'], true) &&
                    Route::has('hrms.employees.probation.mark_permanent'))
                <div class="eo-action-card">
                    <div class="eo-action-card-head">
                        <div class="eo-action-icon"><i class="fas fa-user-check"></i></div>
                        <div>
                            <div class="eo-action-title">Mark Permanent</div>
                            <div class="eo-action-sub">Permanent date and salary will apply after probation end date.</div>
                        </div>
                    </div>

                    <form action="{{ route('hrms.employees.probation.mark_permanent', $employee->id) }}" method="POST">
                        @csrf
                        <div class="eo-action-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Permanent Effective Date</label>
                                    <x-form.date-picker
                                        name="permanent_effective_date"
                                        :value="$effectiveDate->format('Y-m-d')"
                                        class="eo-date"
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="orb-form-label">Permanent Salary</label>
                                    <input type="number" name="actual_salary" class="eo-input-control"
                                        min="0" step="1"
                                        placeholder="Optional salary update">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="orb-form-label">Salary Reason</label>
                                    <input type="text" name="salary_change_reason" class="eo-input-control"
                                        placeholder="Permanent salary update">
                                </div>
                            </div>

                            <div class="eo-info-note mb-3">
                                If admin marks early, permanent status and salary will still start from {{ $effectiveDate->format('d M Y') }}.
                            </div>

                            <button type="submit" class="eo-menu-submit"
                                onclick="return confirm('Mark this employee as permanent? Effective date will be after probation end date.')">
                                <i class="fas fa-user-check"></i> Mark Permanent
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                {{-- Internship Actions --}}
                @if ($isIntern)
                    @php
                        $hasExtend = Route::has('hrms.employees.internship.extend');
                        $hasComplete = Route::has('hrms.employees.internship.complete');
                        $hasExit = Route::has('hrms.employees.exit.mark');
                        $firstTab = $hasExtend ? 'extend' : ($hasComplete ? 'complete' : ($hasExit ? 'exit' : ''));
                    @endphp
                    
                    @if ($hasExtend || $hasComplete || $hasExit)
                    <div class="eo-action-tabs">
                        @if ($hasExtend)
                        <button type="button" class="eo-action-tab {{ $firstTab === 'extend' ? 'active' : '' }}" data-target="extend-internship-{{ $employee->id }}">
                            <i class="fas fa-calendar-plus mr-1"></i> Extend Internship
                        </button>
                        @endif
                        @if ($hasComplete)
                        <button type="button" class="eo-action-tab {{ $firstTab === 'complete' ? 'active' : '' }}" data-target="complete-internship-{{ $employee->id }}">
                            <i class="fas fa-check-circle mr-1"></i> Complete / Convert Internship
                        </button>
                        @endif
                        @if ($hasExit)
                        <button type="button" class="eo-action-tab {{ $firstTab === 'exit' ? 'active' : '' }}" data-target="exit-internship-{{ $employee->id }}">
                            <i class="fas fa-user-times mr-1"></i> Internship Exit
                        </button>
                        @endif
                    </div>
                    @endif

                    {{-- Tab 1: Extend Internship --}}
                    @if ($hasExtend)
                    <div class="eo-tab-pane {{ $firstTab === 'extend' ? 'active' : '' }}" id="extend-internship-{{ $employee->id }}">
                        <div class="eo-action-card">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon"><i class="fas fa-calendar-plus"></i></div>
                                <div>
                                    <div class="eo-action-title">Extend Internship</div>
                                    <div class="eo-action-sub">Extend internship and optionally update stipend.</div>
                                </div>
                            </div>

                            <form action="{{ route('hrms.employees.internship.extend', $employee->id) }}" method="POST">
                                @csrf
                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Current End Date</label>
                                            <input type="text" class="eo-readonly-date"
                                                value="{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : '-' }}"
                                                readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Extend Internship To <span class="text-danger">*</span></label>
                                            <x-form.date-picker
                                                name="internship_extended_to"
                                                :min="$endDate ? \Carbon\Carbon::parse($endDate)->copy()->addDay()->toDateString() : now()->toDateString()"
                                                required="true"
                                                class="eo-date"
                                            />
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">New Stipend / Salary</label>
                                            <input type="number" name="actual_salary" class="eo-input-control"
                                                min="0" step="1"
                                                placeholder="Leave blank if no change">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Extension Reason</label>
                                            <input type="text" name="reason" class="eo-input-control"
                                                placeholder="Reason for extension">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="orb-form-label">Salary Reason</label>
                                            <input type="text" name="salary_change_reason" class="eo-input-control"
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
                    </div>
                    @endif

                    {{-- Tab 2: Complete / Convert Internship --}}
                    @if ($hasComplete)
                    <div class="eo-tab-pane {{ $firstTab === 'complete' ? 'active' : '' }}" id="complete-internship-{{ $employee->id }}">
                        <div class="eo-action-card">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon"><i class="fas fa-check-circle"></i></div>
                                <div>
                                    <div class="eo-action-title">Complete / Convert Internship</div>
                                    <div class="eo-action-sub">Mark completed, move to probation, or move permanent.</div>
                                </div>
                            </div>

                            <form action="{{ route('hrms.employees.internship.complete', $employee->id) }}" method="POST">
                                @csrf
                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Effective Date</label>
                                            <input type="text" class="eo-readonly-date"
                                                value="{{ $effectiveDate->format('d M Y') }}" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Action <span class="text-danger">*</span></label>
                                            <select name="next_stage" class="eo-input-control select-next-stage select2-searchable" data-emp-id="{{ $employee->id }}" required>
                                                <option value="completed">Only Mark Internship Completed</option>
                                                <option value="probation">Complete & Move to Probation</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3 probation-duration-box-{{ $employee->id }}" style="display: none;">
                                            <label class="orb-form-label">Probation Duration</label>
                                            <select name="probation_duration_option" class="eo-input-control select-probation-option select2-searchable" data-emp-id="{{ $employee->id }}">
                                                <option value="3_months" selected>3 Months</option>
                                                <option value="6_months">6 Months</option>
                                                <option value="custom">Custom</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3 custom-probation-box-{{ $employee->id }}" style="display: none;">
                                            <label class="orb-form-label">Custom Duration</label>
                                            <div class="eo-custom-duration-group">
                                                <input type="number" name="custom_duration_value" class="eo-input-control" min="1" max="365" value="3" placeholder="e.g. 10">
                                                <select name="custom_duration_unit" class="eo-input-control select2-searchable">
                                                    <option value="months" selected>Months</option>
                                                    <option value="days">Days</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Salary / Stipend</label>
                                            <input type="number" name="actual_salary" class="eo-input-control"
                                                min="0" step="1"
                                                placeholder="Required only if moving stage">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Salary Reason</label>
                                            <input type="text" name="salary_change_reason" class="eo-input-control"
                                                placeholder="Internship completion salary">
                                        </div>
                                    </div>

                                    @php
                                        $isFutureAction = $effectiveDate->gt(\Carbon\Carbon::today('Asia/Kolkata'));
                                    @endphp
                                    @if ($isFutureAction)
                                        <div class="eo-info-note mb-3">
                                            <i class="fas fa-info-circle mr-1"></i> Since internship ends on {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : '-' }}, employee will remain in Internship stage and probation will be <strong>Scheduled</strong> starting from <strong>{{ $effectiveDate->format('d M Y') }}</strong>.
                                        </div>
                                    @else
                                        <div class="eo-info-note mb-3" style="background: #ECFDF5; border: 1px solid #10B981; color: #065F46; border-radius: 8px; padding: 10px 14px; font-size: 13px;">
                                            <i class="fas fa-check-circle mr-1"></i> Since internship has completed, employee will be <strong>Immediately Converted to Probation (Ongoing)</strong> effective from <strong>{{ $effectiveDate->format('d M Y') }}</strong>.
                                        </div>
                                    @endif

                                    <button type="submit" class="eo-menu-submit eo-menu-submit-success"
                                        onclick="return confirm('Apply internship action? Effective date: {{ $effectiveDate->format('d M Y') }}')">
                                        <i class="fas fa-check-circle"></i> Apply Action
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- Tab 3: Internship Exit --}}
                    @if ($hasExit)
                    <div class="eo-tab-pane {{ $firstTab === 'exit' ? 'active' : '' }}" id="exit-internship-{{ $employee->id }}">
                        <div class="eo-action-card mb-0">
                            <div class="eo-action-card-head">
                                <div class="eo-action-icon" style="background: #FEE2E2; color: #DC2626;">
                                    <i class="fas fa-user-times"></i>
                                </div>
                                <div>
                                    <div class="eo-action-title">Internship Exit</div>
                                    <div class="eo-action-sub">Use only if intern will not continue after internship or leaves midway.</div>
                                </div>
                            </div>

                            <form action="{{ route('hrms.employees.exit.mark', $employee->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="immediate_exit" value="1">

                                <div class="eo-action-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Exit Type <span class="text-danger">*</span></label>
                                            <select name="exit_type" class="eo-input-control select2-searchable" required>
                                                <option value="internship_completed" selected>Completion of Internship</option>
                                                <option value="internship_exit">Internship Exit / Discontinued</option>
                                                <option value="resignation">Resignation</option>
                                                <option value="mutual_separation">Mutual Separation</option>
                                                <option value="termination">Termination</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Last Working Date <span class="text-danger">*</span></label>
                                            <x-form.date-picker
                                                name="last_working_day"
                                                :value="$endDate ? \Carbon\Carbon::parse($endDate)->toDateString() : now()->toDateString()"
                                                class="eo-date"
                                            />
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Exit Reason</label>
                                            <input type="text" name="reason" class="eo-input-control"
                                                placeholder="e.g. Completed tenure / Discontinued">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="orb-form-label">Remarks / Handover Notes</label>
                                            <input type="text" name="remarks" class="eo-input-control"
                                                placeholder="Optional exit remarks">
                                        </div>
                                    </div>

                                    <button type="submit" class="eo-menu-submit eo-menu-submit-danger"
                                        onclick="return confirm('Initiate exit process for this intern?')">
                                        <i class="fas fa-user-times"></i> Initiate Internship Exit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach
