@if($canManageAllocations ?? false)
<div class="leave-card">
    <div class="leave-card-head">
        <div class="leave-card-title-wrap">
            <div class="leave-card-icon">
                <i class="fas fa-magic"></i>
            </div>
            <div>
                <h5 class="leave-card-title">Generate Leave Allocation</h5>
                <div class="leave-card-subtitle">
                    Generate allocation for full year or for a selected employee only.
                </div>
            </div>
        </div>
    </div>

    <div class="leave-card-body">
        <div class="leave-action-grid">

            <div class="leave-action-box">
                <div class="leave-action-title">Generate Yearly Allocation</div>
                <div class="leave-action-subtitle">Process leave allocation for all eligible employees.</div>

                <form method="POST" action="{{ route('leave-allocations.process') }}">
                    @csrf
                    <div class="leave-form-row">
                        <input name="year"
                            class="leave-control leave-year-input"
                            value="{{ $year }}"
                            placeholder="Year">

                        <button class="leave-btn" type="submit">
                            <i class="fas fa-play"></i>
                            Generate Year
                        </button>
                    </div>
                </form>
            </div>

            <div class="leave-action-box">
                <div class="leave-action-title">Generate Single Employee</div>
                <div class="leave-action-subtitle">Run allocation for one employee without affecting others.</div>

                <form method="POST" action="{{ route('leave-allocations.single') }}">
                    @csrf
                    <div class="leave-form-row">
                        <input name="year"
                            class="leave-control leave-year-input"
                            value="{{ $year }}"
                            placeholder="Year">

                        <div class="leave-employee-select-wrap">
                            <select name="employee_id" class="leave-control leave-employee-select select2-searchable" id="singleEmployeeSelect" style="width: 100%;">
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->user_name ?? $employee->display_name }} ({{ $employee->employee_code ?? 'EMP' }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <button class="leave-btn" type="submit">
                            <i class="fas fa-user-check"></i>
                            Generate Single
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
