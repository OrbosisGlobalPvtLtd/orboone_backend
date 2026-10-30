@php
    $modalId = $modalId ?? 'createModal';
    $isAdmin = $isAdmin ?? false;
    $action = $action ?? ($isAdmin ? route('hrms.attendance.holiday_work.store') : route('hrms.attendance.my-holiday-work.store'));
    $modalTitle = $modalTitle ?? ($isAdmin ? 'Add Holiday Work Requests' : 'Apply Holiday Work Request');
    $currentEmp = $currentEmployee ?? (auth()->check() ? auth()->user()->employee : null);
    $currentEmpName = $currentEmp 
        ? (($currentEmp->display_name ?? auth()->user()->name ?? 'N/A') . (!empty($currentEmp->employee_code) ? ' (' . $currentEmp->employee_code . ')' : ''))
        : (auth()->user()->name ?? 'Current Employee');
    $currentEmpId = $currentEmp->id ?? null;
    $employeeList = $employees ?? [];
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <form method="POST" action="{{ $action }}" class="modal-content orb-modal">
            @csrf

            {{-- Exact Orbo Theme Header --}}
            <div class="orb-modal-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title font-weight-bold text-white mb-1">{{ $modalTitle }}</h5>
                    <p class="orb-modal-subtitle text-white-50 mb-0" style="font-size: 13px;">Changes are saved immediately after validation.</p>
                </div>
                <button type="button" class="orb-modal-close-btn" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.22); display: inline-flex; align-items: center; justify-content: center; border: 0; color: #ffffff !important; font-size: 14px; opacity: 1; outline: none; cursor: pointer; padding: 0; margin: 0; line-height: 1; transition: background 0.2s ease, transform 0.15s ease;" onmouseover="this.style.background='rgba(255,255,255,0.38)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(255,255,255,0.22)'; this.style.transform='scale(1)';">
                    <i class="fas fa-times" style="color: #ffffff; font-size: 14px; line-height: 1;"></i>
                </button>
            </div>

            <div class="modal-body orb-modal-body">
                <div class="orb-form-section">
                    <div class="orb-form-section-title">
                        <i class="fas fa-edit"></i> FORM DETAILS
                    </div>

                    <div class="row">
                        @if($isAdmin)
                            {{-- EMPLOYEE (Admin Only) --}}
                            <div class="col-md-6 col-12 mb-3">
                                <label class="orb-form-label">Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" id="{{ $modalId }}_employee_id" class="form-control select2-searchable" required style="border-radius: 12px; height: 42px;">
                                    <option value="">Select Employee</option>
                                    @foreach($employeeList as $empId => $empName)
                                        <option value="{{ $empId }}" @selected(old('employee_id') == $empId)>{{ $empName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- STATUS (Admin Only) --}}
                            <div class="col-md-6 col-12 mb-3">
                                <label class="orb-form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="{{ $modalId }}_status" class="form-control select2-searchable" style="border-radius: 12px; height: 42px;" required>
                                    <option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option>
                                    <option value="approved" @selected(old('status') === 'approved')>Approved</option>
                                    <option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
                                    <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
                                </select>
                            </div>
                        @else
                            {{-- Employee Panel: Hidden logged-in employee ID --}}
                            <input type="hidden" name="employee_id" value="{{ $currentEmpId }}">
                        @endif

                        {{-- WORK TYPE --}}
                        <div class="col-md-6 col-12 mb-3">
                            <label class="orb-form-label">Work Type <span class="text-danger">*</span></label>
                            <select name="work_type" id="{{ $modalId }}_work_type" class="form-control select2-searchable" style="border-radius: 12px; height: 42px;" required>
                                <option value="">Select Work Type</option>
                                <option value="holiday_work" @selected(old('work_type') === 'holiday_work')>Holiday Work</option>
                                <option value="weekoff_work" @selected(old('work_type') === 'weekoff_work')>Week-Off Work</option>
                            </select>
                        </div>

                        {{-- WORK MODE --}}
                        <div class="col-md-6 col-12 mb-3">
                            <label class="orb-form-label">Work Mode <span class="text-danger">*</span></label>
                            <select name="work_mode" id="{{ $modalId }}_work_mode" class="form-control select2-searchable" style="border-radius: 12px; height: 42px;" required>
                                <option value="wfo" @selected(old('work_mode', 'wfo') === 'wfo')>WFO (Work From Office)</option>
                                <option value="wfh" @selected(old('work_mode') === 'wfh')>WFH (Work From Home)</option>
                            </select>
                        </div>

                        {{-- WORKED DATES (2-Grid Responsive with Custom Theme Date Picker) --}}
                        <div class="col-12 mb-3">
                            <label class="orb-form-label">Worked Dates <span class="text-danger">*</span></label>
                            <div id="{{ $modalId }}_worked_dates_container">
                                @if(old('worked_dates') && is_array(old('worked_dates')))
                                    @foreach(old('worked_dates') as $index => $oldDate)
                                        <div class="worked-date-row d-flex align-items-center">
                                            <div class="worked-date-input-wrap">
                                                <x-form.date-picker 
                                                    name="worked_dates[]" 
                                                    :id="$modalId . '_worked_date_' . $index"
                                                    :value="$oldDate" 
                                                    required 
                                                    placeholder="dd-mm-yyyy"
                                                    class="shadow-none"
                                                />
                                            </div>
                                            <button type="button" class="btn btn-outline-danger remove-date-btn" title="Remove date" onclick="this.closest('.worked-date-row').remove();">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="worked-date-row d-flex align-items-center">
                                        <div class="worked-date-input-wrap">
                                            <x-form.date-picker 
                                                name="worked_dates[]" 
                                                :id="$modalId . '_worked_date_0'"
                                                :value="date('Y-m-d')" 
                                                required 
                                                placeholder="dd-mm-yyyy"
                                                class="shadow-none"
                                            />
                                        </div>
                                        <button type="button" class="btn btn-outline-danger remove-date-btn" title="Remove date" onclick="this.closest('.worked-date-row').remove();">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" id="{{ $modalId }}_add_date_btn" class="btn btn-sm btn-light border shadow-sm font-weight-bold text-primary mt-2" style="border-radius: 8px; padding: 6px 14px; font-size: 12px;">
                                <i class="fas fa-plus-circle mr-1"></i> Add Date
                            </button>
                        </div>

                        {{-- Hidden default work times for employee --}}
                        <input type="hidden" name="start_time" value="09:00">
                        <input type="hidden" name="end_time" value="18:00">

                        {{-- REASON --}}
                        <div class="col-12 mb-3">
                            <label class="orb-form-label">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Describe the reason or tasks completed..." style="border-radius: 12px;">{{ old('reason') }}</textarea>
                        </div>

                        {{-- ADDITIONAL NOTES --}}
                        <div class="col-12 mb-0">
                            <label class="orb-form-label">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes..." style="border-radius: 12px;">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Exact Orbo Theme Footer --}}
            <div class="modal-footer orb-modal-footer d-flex align-items-center justify-content-end">
                <button type="button" class="orb-btn-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="orb-btn-primary"><i class="fas fa-check-double"></i> Submit</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Scoped Orbo Theme styling for {{ $modalId }} */
#{{ $modalId }} .modal-dialog {
    max-width: 740px !important;
    margin: 1.75rem auto !important;
}
#{{ $modalId }} .modal-content.orb-modal {
    border: 0 !important;
    border-radius: 24px !important;
    overflow: hidden !important;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .28) !important;
    max-height: calc(100vh - 3.5rem) !important;
    display: flex !important;
    flex-direction: column !important;
}
#{{ $modalId }} .orb-modal-header {
    background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
    border: 0 !important;
    color: #fff !important;
    padding: 20px 24px !important;
    flex-shrink: 0 !important;
}
#{{ $modalId }} .orb-modal-header .modal-title {
    font-weight: 900 !important;
    color: #fff !important;
    font-size: 1.2rem !important;
}
#{{ $modalId }} .orb-modal-subtitle {
    color: rgba(255, 255, 255, 0.85) !important;
    font-size: 13px !important;
}
#{{ $modalId }} .orb-modal-close-btn {
    width: 32px !important;
    height: 32px !important;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.22) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 0 !important;
    color: #ffffff !important;
    font-size: 14px !important;
    outline: none !important;
    cursor: pointer !important;
    transition: background 0.2s ease, transform 0.15s ease !important;
}
#{{ $modalId }} .orb-modal-close-btn:hover {
    background: rgba(255, 255, 255, 0.38) !important;
    transform: scale(1.05) !important;
}
#{{ $modalId }} .modal-body.orb-modal-body {
    background: #fff !important;
    padding: 24px !important;
    overflow-y: auto !important;
    max-height: calc(100vh - 3.5rem - 140px) !important;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
#{{ $modalId }} .orb-form-section-title {
    font-size: 12px !important;
    font-weight: 900 !important;
    text-transform: uppercase !important;
    color: var(--orb-primary, #4B00E8) !important;
    letter-spacing: 0.06em !important;
    margin-bottom: 16px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
#{{ $modalId }} .orb-form-label {
    font-size: 11px !important;
    font-weight: 800 !important;
    color: #475467 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    margin-bottom: 6px !important;
    display: block !important;
}
#{{ $modalId }} .form-control,
#{{ $modalId }} select.form-control {
    border-radius: 12px !important;
    height: 42px !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    color: var(--orb-text, #101828) !important;
}
#{{ $modalId }} .form-control:focus,
#{{ $modalId }} select.form-control:focus {
    border-color: var(--orb-primary, #4B00E8) !important;
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1) !important;
    outline: none !important;
}
#{{ $modalId }} textarea.form-control {
    height: auto !important;
}
#{{ $modalId }} .modal-footer.orb-modal-footer {
    background: #F8FAFC !important;
    border-top: 1px solid #EEF2F6 !important;
    padding: 16px 24px !important;
    flex-shrink: 0 !important;
}
#{{ $modalId }} .orb-btn-light {
    background: #fff !important;
    color: var(--orb-text, #101828) !important;
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    border-radius: 12px !important;
    height: 42px !important;
    padding: 0 20px !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
}
#{{ $modalId }} .orb-btn-light:hover {
    background: var(--orb-soft, #F4F2FF) !important;
    color: var(--orb-primary, #4B00E8) !important;
}
#{{ $modalId }} .orb-btn-primary {
    background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 12px !important;
    height: 42px !important;
    padding: 0 24px !important;
    font-weight: 800 !important;
    font-size: 13px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    box-shadow: 0 10px 25px rgba(75, 0, 232, 0.22) !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
}
#{{ $modalId }} .orb-btn-primary:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 12px 28px rgba(75, 0, 232, 0.32) !important;
}

/* 2-Grid Responsive Dates */
#{{ $modalId }}_worked_dates_container {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 12px 14px !important;
    max-height: 240px !important;
    overflow-y: auto !important;
    padding: 3px 4px 3px 1px !important;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
#{{ $modalId }}_worked_dates_container .worked-date-row {
    display: flex !important;
    align-items: center !important;
    width: 100% !important;
    margin-bottom: 0 !important;
}
#{{ $modalId }}_worked_dates_container .worked-date-row:only-child {
    grid-column: 1 / -1 !important;
}
#{{ $modalId }}_worked_dates_container .worked-date-input-wrap {
    flex: 1 !important;
    min-width: 0 !important;
    margin-right: 8px !important;
}
#{{ $modalId }}_worked_dates_container .worked-date-row:only-child .worked-date-input-wrap {
    margin-right: 0 !important;
}
#{{ $modalId }}_worked_dates_container .worked-date-row:only-child .remove-date-btn {
    display: none !important;
}
#{{ $modalId }} .remove-date-btn {
    height: 42px !important;
    width: 42px !important;
    min-width: 42px !important;
    flex-shrink: 0 !important;
    border-radius: 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
}

/* Date picker calendar SVG icon */
#{{ $modalId }} .orbo-date-picker,
#{{ $modalId }} .orbo-date-picker-display,
#{{ $modalId }} input[data-date-picker] {
    height: 42px !important;
    border-radius: 12px !important;
    padding-left: 12px !important;
    padding-right: 38px !important;
    background-color: #ffffff !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236366F1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 14px center !important;
    background-size: 16px 16px !important;
    cursor: pointer !important;
}
#{{ $modalId }} .orbo-date-picker-display:focus {
    border-color: var(--orb-primary, #4B00E8) !important;
    box-shadow: 0 0 0 3px rgba(75, 0, 232, .1) !important;
    outline: none !important;
}

/* Thin Scrollbars */
#{{ $modalId }} .modal-body::-webkit-scrollbar,
#{{ $modalId }}_worked_dates_container::-webkit-scrollbar {
    width: 6px;
}
#{{ $modalId }} .modal-body::-webkit-scrollbar-track,
#{{ $modalId }}_worked_dates_container::-webkit-scrollbar-track {
    background: transparent;
}
#{{ $modalId }} .modal-body::-webkit-scrollbar-thumb,
#{{ $modalId }}_worked_dates_container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
#{{ $modalId }} .modal-body::-webkit-scrollbar-thumb:hover,
#{{ $modalId }}_worked_dates_container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Mobile responsive collapse */
@media (max-width: 640px) {
    #{{ $modalId }} .modal-dialog {
        width: calc(100% - 20px) !important;
        margin: 10px auto !important;
        max-width: none !important;
    }
    #{{ $modalId }}_worked_dates_container {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
        max-height: 200px !important;
    }
}
</style>

<script>
(function() {
    var modalId = '{{ $modalId }}';
    var container = document.getElementById(modalId + '_worked_dates_container');
    var addBtn = document.getElementById(modalId + '_add_date_btn');
    if (addBtn && container) {
        var rowCounter = container.querySelectorAll('.worked-date-row').length;
        addBtn.addEventListener('click', function() {
            rowCounter++;
            var row = document.createElement('div');
            row.className = 'worked-date-row d-flex align-items-center';
            
            var inputWrap = document.createElement('div');
            inputWrap.className = 'worked-date-input-wrap';

            var input = document.createElement('input');
            input.type = 'text';
            input.name = 'worked_dates[]';
            input.id = modalId + '_worked_date_' + rowCounter;
            input.className = 'orbo-date-picker form-control shadow-none';
            input.setAttribute('data-date-picker', '');
            input.setAttribute('autocomplete', 'off');
            input.placeholder = 'dd-mm-yyyy';
            input.style.borderRadius = '12px';
            input.style.height = '42px';
            input.required = true;
            
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            input.value = yyyy + '-' + mm + '-' + dd;
            
            inputWrap.appendChild(input);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline-danger remove-date-btn';
            removeBtn.title = 'Remove date';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            
            removeBtn.addEventListener('click', function() {
                row.remove();
            });
            
            row.appendChild(inputWrap);
            row.appendChild(removeBtn);
            container.appendChild(row);

            if (window.initOrboDatePickers) {
                window.initOrboDatePickers(inputWrap);
            }
        });
    }

    // Modal show listener to ensure datepickers & select2 are initialized
    $('#' + modalId).on('shown.bs.modal', function() {
        if (window.initOrboDatePickers) {
            window.initOrboDatePickers(this);
        }
        if ($.fn.select2) {
            $(this).find('.select2-searchable').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        dropdownParent: $('#' + modalId),
                        width: '100%'
                    });
                }
            });
        }
    });
})();
</script>
