@php
    $shiftDefaultTimings = [];
    if (isset($attendanceTimes)) {
        foreach ($attendanceTimes as $shiftItem) {
            $shiftDefaultTimings[$shiftItem->code] = [
                'punch_allowed_from' => $shiftItem->punch_allowed_from ? \Carbon\Carbon::parse($shiftItem->punch_allowed_from)->format('H:i') : '',
                'shift_start_time' => $shiftItem->shift_start_time ? \Carbon\Carbon::parse($shiftItem->shift_start_time)->format('H:i') : '',
                'late_after_time' => $shiftItem->late_after_time ? \Carbon\Carbon::parse($shiftItem->late_after_time)->format('H:i') : '',
                'half_day_after_time' => $shiftItem->half_day_after_time ? \Carbon\Carbon::parse($shiftItem->half_day_after_time)->format('H:i') : '',
                'block_after_time' => $shiftItem->block_after_time ? \Carbon\Carbon::parse($shiftItem->block_after_time)->format('H:i') : '',
                'shift_end_time' => $shiftItem->shift_end_time ? \Carbon\Carbon::parse($shiftItem->shift_end_time)->format('H:i') : '',
                'required_work_minutes' => $shiftItem->required_work_minutes ?? '',
                'lunch_minutes' => $shiftItem->lunch_break_minutes ?? '',
            ];
        }
    }
@endphp

<script type="application/json" id="shift-default-timings-data">
    @json($shiftDefaultTimings)
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const shiftDataElement = document.getElementById('shift-default-timings-data');
        const shiftDefaultTimings = shiftDataElement ? JSON.parse(shiftDataElement.textContent || '{}') : {};

        const elements = {
            form: document.getElementById('employeeOnboardingForm'),
            department: document.getElementById('department_id'),
            designation: document.getElementById('designation_id'),
            employmentType: document.getElementById('employment_type'),
            workScheduleType: document.getElementById('work_schedule_type'),
            employeeStage: document.getElementById('employee_stage'),
            employeeStageDisplay: document.getElementById('employee_stage_display'),
            joiningDate: document.getElementById('joining_date'),
            probationOption: document.getElementById('probation_duration_option'),
            probationMonths: document.getElementById('probation_months'),
            customProbationBox: document.querySelector('.custom-probation-box'),
            customValue: document.getElementById('custom_duration_value'),
            customUnit: document.getElementById('custom_duration_unit'),
            probationStartDisplay: document.getElementById('probation_start_date_display'),
            probationEndDisplay: document.getElementById('probation_end_date_display'),
            permanentDisplay: document.getElementById('permanent_effective_date_display'),
            internBox: document.getElementById('intern_box'),
            contractBox: document.getElementById('contract_box'),
            internshipStart: document.getElementById('internship_start_date'),
            internshipEnd: document.getElementById('internship_end_date'),
            internshipDurationMonths: document.getElementById('internship_duration_months'),
            durationDisplay: document.getElementById('internship_duration_display'),
            paidIntern: document.getElementById('is_paid_intern'),
            salary: document.getElementById('actual_salary'),
            salaryLabel: document.getElementById('salary_label'),
            salaryNote: document.getElementById('salary_note'),
            salaryEffectiveFrom: document.getElementById('salary_effective_from'),
            salaryEffectiveNote: document.getElementById('salary_effective_note'),
            salaryReason: document.getElementById('salary_change_reason'),
            flexibleShiftBox: document.getElementById('flexible_shift_timings_box'),
            shiftStart: document.getElementById('shift_start_time'),
            lateAfter: document.getElementById('late_after_time'),
            halfDayAfter: document.getElementById('half_day_after_time'),
            blockAfter: document.getElementById('block_after_time'),
            shiftEnd: document.getElementById('shift_end_time'),
            requiredWorkMinutes: document.getElementById('required_work_minutes'),
            lunchMinutes: document.getElementById('lunch_minutes'),
            punchAllowedFrom: document.getElementById('punch_allowed_from'),
        };

        let salaryEffectiveTouched = false;

        function formatDateDDMMYYYY(date) {
            if (!date || isNaN(date.getTime())) return '';
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}-${month}-${year}`;
        }

        function formatInputDate(date) {
            if (!date || isNaN(date.getTime())) return '';
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${date.getFullYear()}-${month}-${day}`;
        }

        function addMonths(dateString, months) {
            if (!dateString) return null;
            const parts = dateString.split('-');
            if (parts.length !== 3) return null;
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            const date = new Date(y, m, d);
            if (isNaN(date.getTime())) return null;

            const originalDay = date.getDate();
            date.setMonth(date.getMonth() + Number(months));
            if (date.getDate() !== originalDay) {
                date.setDate(0);
            }
            date.setDate(date.getDate() - 1);
            return date;
        }

        function diffDaysInclusive(startValue, endValue) {
            if (!startValue || !endValue) return '';
            const startParts = startValue.split('-');
            const endParts = endValue.split('-');
            if (startParts.length !== 3 || endParts.length !== 3) return '';

            const start = new Date(parseInt(startParts[0], 10), parseInt(startParts[1], 10) - 1, parseInt(startParts[2], 10));
            const end = new Date(parseInt(endParts[0], 10), parseInt(endParts[1], 10) - 1, parseInt(endParts[2], 10));

            if (isNaN(start.getTime()) || isNaN(end.getTime())) return '';

            const diff = end - start;
            if (diff < 0) return 'Invalid date range';

            return Math.floor(diff / (1000 * 60 * 60 * 24)) + 1;
        }

        function filterDesignations() {
            if (!elements.department || !elements.designation) return;
            const deptId = elements.department.value;

            Array.from(elements.designation.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }
                option.hidden = option.getAttribute('data-department-id') !== deptId;
            });

            const selected = elements.designation.options[elements.designation.selectedIndex];
            if (selected && selected.hidden) {
                elements.designation.value = '';
            }
        }

        const stageLabels = {
            internship: 'Internship',
            probation: 'Probation',
            permanent: 'Permanent',
            freelance: 'Freelance',
            contract: 'Contract'
        };

        function defaultStageForType() {
            if (!elements.employmentType || !elements.employmentType.value) return '';
            const val = elements.employmentType.value;
            if (val === 'intern') return 'internship';
            if (val === 'freelancer') return 'freelance';
            if (val === 'contract') return 'contract';
            return 'probation';
        }

        function currentStage() {
            let stage = defaultStageForType();
            if (elements.employeeStage) elements.employeeStage.value = stage;
            if (elements.employeeStageDisplay) elements.employeeStageDisplay.value = stageLabels[stage] || 'Auto';
            return stage;
        }

        function calculateClientProbation(startDateStr, option, customValue, customUnit) {
            if (!startDateStr) return null;
            const parts = startDateStr.split('-');
            if (parts.length !== 3) return null;
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            const start = new Date(y, m, d);
            if (isNaN(start.getTime())) return null;

            let type = 'months';
            let val = 3;

            if (option === '6_months') {
                type = 'months';
                val = 6;
            } else if (option === 'custom') {
                type = customUnit === 'days' ? 'days' : 'months';
                val = parseInt(customValue, 10) || 1;
                if (val < 1) val = 1;
            } else {
                type = 'months';
                val = 3;
            }

            let endDate;
            if (type === 'days') {
                endDate = new Date(y, m, d + (val - 1));
            } else {
                const lastDayOfStartMonth = new Date(y, m + 1, 0).getDate();
                const isLast = (d === lastDayOfStartMonth);

                if (isLast) {
                    endDate = new Date(y, m + val + 1, 0);
                } else {
                    const maxDaysInTarget = new Date(y, m + val + 1, 0).getDate();
                    const targetDay = Math.min(d, maxDaysInTarget);
                    const temp = new Date(y, m + val, targetDay);
                    temp.setDate(temp.getDate() - 1);
                    endDate = temp;
                }
            }

            const permDate = new Date(endDate);
            permDate.setDate(permDate.getDate() + 1);

            return {
                start: start,
                end: endDate,
                permanent: permDate
            };
        }

        function updateProbation() {
            const stage = currentStage();
            const option = elements.probationOption ? elements.probationOption.value : '3_months';
            const customVal = elements.customValue ? elements.customValue.value : 10;
            const customUnit = elements.customUnit ? elements.customUnit.value : 'days';

            if (elements.customProbationBox) {
                if (option === 'custom') {
                    elements.customProbationBox.classList.remove('eo-hidden');
                } else {
                    elements.customProbationBox.classList.add('eo-hidden');
                }
            }

            if (elements.probationMonths) {
                if (option === '6_months') {
                    elements.probationMonths.value = 6;
                } else if (option === 'custom' && customUnit === 'months') {
                    elements.probationMonths.value = parseInt(customVal, 10) || 1;
                } else {
                    elements.probationMonths.value = 3;
                }
            }

            if (!elements.joiningDate || !elements.joiningDate.value || (stage !== 'probation' && stage !== 'permanent')) {
                if (elements.probationStartDisplay) elements.probationStartDisplay.value = '';
                if (elements.probationEndDisplay) elements.probationEndDisplay.value = '';
                if (elements.permanentDisplay) elements.permanentDisplay.value = '';
                return;
            }

            const res = calculateClientProbation(elements.joiningDate.value, option, customVal, customUnit);
            if (res) {
                if (elements.probationStartDisplay) elements.probationStartDisplay.value = formatDateDDMMYYYY(res.start);
                if (elements.probationEndDisplay) elements.probationEndDisplay.value = formatDateDDMMYYYY(res.end);
                if (elements.permanentDisplay) elements.permanentDisplay.value = formatDateDDMMYYYY(res.permanent);
            }
        }

        function updateInternshipEndDate() {
            if (!elements.internshipDurationMonths || !elements.internshipStart) return;
            const duration = elements.internshipDurationMonths.value;

            if (!elements.internshipStart.value) {
                if (elements.durationDisplay) elements.durationDisplay.value = '';
                return;
            }

            if (duration && duration !== 'custom' && elements.internshipEnd) {
                const endDate = addMonths(elements.internshipStart.value, duration);
                if (endDate) {
                    elements.internshipEnd.value = formatInputDate(endDate);
                    elements.internshipEnd.setAttribute('readonly', 'readonly');
                }
            } else if (elements.internshipEnd) {
                elements.internshipEnd.removeAttribute('readonly');
            }

            updateInternshipDuration();
        }

        function updateInternshipDuration() {
            if (!elements.internshipStart || !elements.internshipEnd || !elements.durationDisplay) return;
            const days = diffDaysInclusive(elements.internshipStart.value, elements.internshipEnd.value);

            if (!days) {
                elements.durationDisplay.value = '';
                return;
            }

            if (days === 'Invalid date range') {
                elements.durationDisplay.value = days;
                return;
            }

            elements.durationDisplay.value = days + ' days';
        }

        function disableSalaryEffectiveForUnpaidIntern() {
            if (!elements.salaryEffectiveFrom) return;
            elements.salaryEffectiveFrom.value = '';
            elements.salaryEffectiveFrom.setAttribute('readonly', 'readonly');
            elements.salaryEffectiveFrom.classList.add('disabled-soft');
            if (elements.salaryEffectiveNote) elements.salaryEffectiveNote.innerText = 'Salary effective date is not required for an unpaid internship.';
        }

        function enableSalaryEffective() {
            if (!elements.salaryEffectiveFrom) return;
            elements.salaryEffectiveFrom.removeAttribute('readonly');
            elements.salaryEffectiveFrom.classList.remove('disabled-soft');
            if (elements.salaryEffectiveNote) elements.salaryEffectiveNote.innerText = 'Salary history effective date.';
        }

        function updateSalary() {
            const stage = currentStage();
            if (!elements.salary) return;

            if (stage === 'internship') {
                if (elements.salaryLabel) elements.salaryLabel.innerHTML = 'Stipend / Salary <span class="required">*</span>';
                if (elements.salaryReason) elements.salaryReason.placeholder = 'Initial internship stipend';

                if (elements.paidIntern && elements.paidIntern.value === '0') {
                    elements.salary.value = 0;
                    elements.salary.setAttribute('readonly', 'readonly');
                    elements.salary.classList.add('disabled-soft');
                    if (elements.salaryNote) elements.salaryNote.innerText = 'Unpaid internship selected, salary locked at 0.';
                    disableSalaryEffectiveForUnpaidIntern();
                    return;
                }

                elements.salary.removeAttribute('readonly');
                elements.salary.classList.remove('disabled-soft');
                enableSalaryEffective();

                if (!salaryEffectiveTouched && elements.internshipStart && elements.internshipStart.value && elements.salaryEffectiveFrom) {
                    elements.salaryEffectiveFrom.value = elements.internshipStart.value;
                }

                if (elements.salaryNote) elements.salaryNote.innerText = 'Please enter the stipend amount for a paid intern.';
                return;
            }

            if (elements.salaryLabel) elements.salaryLabel.innerHTML = 'Actual Salary (Monthly CTC) <span class="required">*</span>';
            elements.salary.removeAttribute('readonly');
            elements.salary.classList.remove('disabled-soft');
            enableSalaryEffective();
            if (elements.salaryReason) elements.salaryReason.placeholder = 'Initial salary';

            if (!salaryEffectiveTouched && elements.joiningDate && elements.joiningDate.value && elements.salaryEffectiveFrom) {
                elements.salaryEffectiveFrom.value = elements.joiningDate.value;
            }

            if (elements.salaryNote) elements.salaryNote.innerText = "Annual CTC is calculated automatically by the system.";
        }

        function updateEmploymentFields() {
            const stage = currentStage();

            if (stage === 'internship') {
                if (elements.internBox) elements.internBox.style.display = 'block';
                if (elements.contractBox) elements.contractBox.style.display = 'none';
                document.querySelectorAll('.joining-box,.probation-box').forEach(el => el.classList.add('eo-hidden'));
            } else if (stage === 'contract' || stage === 'freelance') {
                if (elements.internBox) elements.internBox.style.display = 'none';
                if (elements.contractBox) elements.contractBox.style.display = 'block';
                document.querySelectorAll('.joining-box').forEach(el => el.classList.remove('eo-hidden'));
                document.querySelectorAll('.probation-box').forEach(el => el.classList.add('eo-hidden'));
            } else {
                if (elements.internBox) elements.internBox.style.display = 'none';
                if (elements.contractBox) elements.contractBox.style.display = 'none';
                document.querySelectorAll('.joining-box,.probation-box').forEach(el => el.classList.remove('eo-hidden'));
                updateProbation();
            }

            updateInternshipEndDate();
            updateSalary();
        }

        function toggleFlexibleTimingFields() {
            if (!elements.workScheduleType || !elements.flexibleShiftBox) return;
            if (elements.workScheduleType.value === 'flexible_part_time') {
                elements.flexibleShiftBox.style.display = 'block';
                elements.flexibleShiftBox.querySelectorAll('input').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            } else {
                elements.flexibleShiftBox.style.display = 'none';
                elements.flexibleShiftBox.querySelectorAll('input').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        }

        function formatTimeTo12Hour(timeStr) {
            if (!timeStr) return '--:--';
            const parts = timeStr.split(':');
            if (parts.length < 2) return '--:--';
            let hours = Number(parts[0]);
            const minutes = Number(parts[1]);
            if (isNaN(hours) || isNaN(minutes)) return '--:--';

            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            const strMinutes = String(minutes).padStart(2, '0');
            const strHours = String(hours).padStart(2, '0');
            return `${strHours}:${strMinutes} ${ampm}`;
        }

        function updateAllTimeDisplays() {
            const timeFields = ['punch_allowed_from', 'shift_start_time', 'late_after_time', 'half_day_after_time', 'block_after_time', 'shift_end_time'];
            timeFields.forEach(field => {
                const input = document.getElementById(field);
                if (input) {
                    const overlay = input.parentNode ? input.parentNode.querySelector('.time-display-val') : null;
                    if (overlay) {
                        overlay.textContent = formatTimeTo12Hour(input.value);
                    }
                }
            });
        }

        function handleScheduleChange(isInit = false) {
            if (!elements.workScheduleType) return;
            const code = elements.workScheduleType.value;
            const defaults = shiftDefaultTimings[code];
            if (defaults) {
                const fields = ['punch_allowed_from', 'shift_start_time', 'late_after_time', 'half_day_after_time', 'block_after_time', 'shift_end_time', 'required_work_minutes', 'lunch_minutes'];
                fields.forEach(field => {
                    const el = document.getElementById(field);
                    if (el) {
                        if (!isInit || !el.value) {
                            el.value = defaults[field];
                        }
                    }
                });
            }
            toggleFlexibleTimingFields();
            updateAllTimeDisplays();
        }

        function addMinutesToTime(timeStr, minutesToAdd) {
            if (!timeStr) return '';
            const parts = timeStr.split(':');
            if (parts.length < 2) return '';
            const hours = Number(parts[0]);
            const minutes = Number(parts[1]);
            if (isNaN(hours) || isNaN(minutes)) return '';

            const date = new Date();
            date.setHours(hours);
            date.setMinutes(minutes + minutesToAdd);
            date.setSeconds(0);

            const h = String(date.getHours()).padStart(2, '0');
            const m = String(date.getMinutes()).padStart(2, '0');
            return `${h}:${m}`;
        }

        function autoCalculateTimings() {
            if (!elements.shiftStart) return;
            const startTime = elements.shiftStart.value;
            if (!startTime) return;

            if (elements.lateAfter) elements.lateAfter.value = addMinutesToTime(startTime, 65);
            if (elements.halfDayAfter) elements.halfDayAfter.value = addMinutesToTime(startTime, 240);
            if (elements.punchAllowedFrom) elements.punchAllowedFrom.value = addMinutesToTime(startTime, -60);

            const reqMin = Number(elements.requiredWorkMinutes ? elements.requiredWorkMinutes.value : 0);
            const lunchMin = Number(elements.lunchMinutes ? elements.lunchMinutes.value : 0);
            if (elements.shiftEnd && (reqMin > 0 || lunchMin > 0)) {
                elements.shiftEnd.value = addMinutesToTime(startTime, reqMin + lunchMin);
            }

            if (elements.blockAfter) elements.blockAfter.value = addMinutesToTime(startTime, 75);

            updateAllTimeDisplays();
        }

        function bindDoubleSubmitGuard() {
            if (!elements.form) return;
            let isSubmitting = false;
            elements.form.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                if (!elements.form.checkValidity()) {
                    return;
                }
                isSubmitting = true;
                const submitBtns = elements.form.querySelectorAll('button[type="submit"]');
                submitBtns.forEach(btn => {
                    btn.style.opacity = '0.7';
                    btn.style.pointerEvents = 'none';
                });
            });
        }

        function bindEvents() {
            if (elements.salaryEffectiveFrom) {
                elements.salaryEffectiveFrom.addEventListener('change', function() {
                    salaryEffectiveTouched = true;
                });
            }

            if (elements.department) {
                elements.department.addEventListener('change', filterDesignations);
            }

            if (elements.employmentType) {
                elements.employmentType.addEventListener('change', function() {
                    salaryEffectiveTouched = false;
                    if (elements.salaryEffectiveFrom) elements.salaryEffectiveFrom.value = '';
                    updateEmploymentFields();

                    const workScheduleMap = {
                        'full_time': 'general_shift',
                        'part_time': 'part_time_shift',
                        'intern': 'general_shift',
                        'contract': 'general_shift',
                        'consultant': 'general_shift',
                        'trainee': 'general_shift'
                    };
                    if (elements.workScheduleType) {
                        const schedule = workScheduleMap[elements.employmentType.value];
                        if (schedule) {
                            const hasOption = Array.from(elements.workScheduleType.options).some(opt => opt.value === schedule);
                            if (hasOption) {
                                elements.workScheduleType.value = schedule;
                            } else {
                                elements.workScheduleType.value = '';
                            }
                        } else if (!elements.employmentType.value) {
                            elements.workScheduleType.value = '';
                        }
                        handleScheduleChange(false);
                    }
                });
            }

            if (elements.joiningDate) {
                elements.joiningDate.addEventListener('change', function() {
                    if (currentStage() !== 'internship') {
                        salaryEffectiveTouched = false;
                    }
                    updateProbation();
                    updateSalary();
                });
            }

            if (elements.probationOption) elements.probationOption.addEventListener('change', updateProbation);
            if (elements.customValue) {
                elements.customValue.addEventListener('input', updateProbation);
                elements.customValue.addEventListener('change', updateProbation);
            }
            if (elements.customUnit) elements.customUnit.addEventListener('change', updateProbation);
            if (elements.probationMonths) elements.probationMonths.addEventListener('change', updateProbation);

            if (elements.internshipStart) {
                elements.internshipStart.addEventListener('change', function() {
                    if (currentStage() === 'internship' && elements.paidIntern && elements.paidIntern.value !== '0') {
                        salaryEffectiveTouched = false;
                    }
                    updateInternshipEndDate();
                    updateSalary();
                });
            }

            if (elements.internshipDurationMonths) elements.internshipDurationMonths.addEventListener('change', updateInternshipEndDate);
            if (elements.internshipEnd) elements.internshipEnd.addEventListener('change', updateInternshipDuration);

            if (elements.paidIntern) {
                elements.paidIntern.addEventListener('change', function() {
                    salaryEffectiveTouched = false;
                    updateSalary();
                });
            }

            if (elements.workScheduleType) {
                elements.workScheduleType.addEventListener('change', function() {
                    handleScheduleChange(false);
                });
            }

            if (elements.shiftStart) {
                elements.shiftStart.addEventListener('input', autoCalculateTimings);
                elements.shiftStart.addEventListener('change', autoCalculateTimings);
            }
            if (elements.requiredWorkMinutes) {
                elements.requiredWorkMinutes.addEventListener('input', autoCalculateTimings);
                elements.requiredWorkMinutes.addEventListener('change', autoCalculateTimings);
            }
            if (elements.lunchMinutes) {
                elements.lunchMinutes.addEventListener('input', autoCalculateTimings);
                elements.lunchMinutes.addEventListener('change', autoCalculateTimings);
            }

            const timeFields = ['punch_allowed_from', 'shift_start_time', 'late_after_time', 'half_day_after_time', 'block_after_time', 'shift_end_time'];
            timeFields.forEach(field => {
                const el = document.getElementById(field);
                if (el) {
                    el.addEventListener('input', updateAllTimeDisplays);
                    el.addEventListener('change', updateAllTimeDisplays);
                }
            });

            bindDoubleSubmitGuard();
        }

        // Run Lifecycle Initialization
        filterDesignations();
        updateEmploymentFields();
        handleScheduleChange(true);
        bindEvents();
    });
</script>
