@push('scripts')
<script>
    function selectEmployeeForShift(empId) {
        document.getElementById('assignEmployeeSelect').value = empId;
    }

    function handleShiftTemplateSelect(context, selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        let panel;
        if (context === 'assign') {
            panel = document.getElementById('assignFlexibleSection');
        } else if (context.startsWith('edit_')) {
            const id = context.replace('edit_', '');
            panel = document.getElementById('editFlexibleSection' + id);
        }

        if (!panel) return;

        const shiftType = selectedOption.getAttribute('data-shift-type') || '';
        const punchFrom = selectedOption.getAttribute('data-punch-allowed') || '';
        const shiftStart = selectedOption.getAttribute('data-shift-start') || '';
        const lateAfter = selectedOption.getAttribute('data-late-after') || '';
        const blockAfter = selectedOption.getAttribute('data-block-after') || '';
        const halfDayAfter = selectedOption.getAttribute('data-half-day-after') || '';
        const shiftEnd = selectedOption.getAttribute('data-shift-end') || '';
        const reqMins = selectedOption.getAttribute('data-req-mins') || '480';
        const lunchMins = selectedOption.getAttribute('data-lunch-mins') || '60';

        const punchInput = panel.querySelector('input[name="punch_allowed_from"]');
        const startInput = panel.querySelector('input[name="shift_start_time"]');
        const lateInput = panel.querySelector('input[name="late_after_time"]');
        const blockInput = panel.querySelector('input[name="block_after_time"]');
        const halfDayInput = panel.querySelector('input[name="half_day_after_time"]');
        const endInput = panel.querySelector('input[name="shift_end_time"]');
        const reqInput = panel.querySelector('input[name="required_work_minutes"]');
        const lunchInput = panel.querySelector('input[name="lunch_minutes"]');

        if (shiftType === 'dynamic_hours') {
            if (punchInput) punchInput.value = '';
            if (startInput) startInput.value = '';
            if (lateInput) lateInput.value = '';
            if (blockInput) blockInput.value = '';
            if (halfDayInput) halfDayInput.value = '';
            if (endInput) endInput.value = '';
        } else {
            if (punchInput) punchInput.value = punchFrom;
            if (startInput) startInput.value = shiftStart;
            if (lateInput) lateInput.value = lateAfter;
            if (blockInput) blockInput.value = blockAfter;
            if (halfDayInput) halfDayInput.value = halfDayAfter;
            if (endInput) endInput.value = shiftEnd;
        }

        if (reqInput) reqInput.value = reqMins;
        if (lunchInput) lunchInput.value = lunchMins;

        updateAllTimeDisplaysInScope(panel);
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

    function addMinutesToTime(timeStr, minutesToAdd) {
        if (!timeStr) return '';
        const parts = timeStr.split(':');
        if (parts.length < 2) return '';
        const hours = Number(parts[0]);
        const minutes = Number(parts[1]);
        const date = new Date();
        date.setHours(hours);
        date.setMinutes(minutes + minutesToAdd);
        date.setSeconds(0);

        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    }

    function updateAllTimeDisplaysInScope(scopeEl) {
        if (!scopeEl) return;
        const inputs = scopeEl.querySelectorAll('input[type="time"]');
        inputs.forEach(input => {
            const overlay = input.parentNode.querySelector('.time-display-val');
            if (overlay) {
                overlay.textContent = formatTimeTo12Hour(input.value);
            }
        });
    }

    function autoCalculateTimingsForScope(scopeEl) {
        if (!scopeEl) return;
        const shiftStartInput   = scopeEl.querySelector('input[name="shift_start_time"]');
        const lateAfterInput    = scopeEl.querySelector('input[name="late_after_time"]');
        const halfDayAfterInput = scopeEl.querySelector('input[name="half_day_after_time"]');
        const shiftEndInput     = scopeEl.querySelector('input[name="shift_end_time"]');
        const reqMinutesInput   = scopeEl.querySelector('input[name="required_work_minutes"]');
        const lunchMinutesInput = scopeEl.querySelector('input[name="lunch_minutes"]');
        const punchAllowedInput = scopeEl.querySelector('input[name="punch_allowed_from"]');
        const blockedPunchInput = scopeEl.querySelector('input[name="block_after_time"]');

        if (!shiftStartInput) return;

        const startTime = shiftStartInput.value;
        if (!startTime) return;

        // 1. Late After: Start + 65 mins
        if (lateAfterInput) {
            lateAfterInput.value = addMinutesToTime(startTime, 65);
        }

        // 2. Half Day After: Start + 240 mins (4 hours)
        if (halfDayAfterInput) {
            halfDayAfterInput.value = addMinutesToTime(startTime, 240);
        }

        // 3. Punch Allowed: Start - 60 mins (1 hour before)
        if (punchAllowedInput) {
            punchAllowedInput.value = addMinutesToTime(startTime, -60);
        }

        // 4. Shift End Time: Start + Required Minutes + Lunch Minutes
        const reqMin = Number(reqMinutesInput?.value || 0);
        const lunchMin = Number(lunchMinutesInput?.value || 0);
        if (shiftEndInput && (reqMin > 0 || lunchMin > 0)) {
            shiftEndInput.value = addMinutesToTime(startTime, reqMin + lunchMin);
        }

        // 5. Blocked Punch: Start + 75 mins
        if (blockedPunchInput) {
            blockedPunchInput.value = addMinutesToTime(startTime, 75);
        }

        updateAllTimeDisplaysInScope(scopeEl);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize time overlays for all card sections
        document.querySelectorAll('.card').forEach(panel => {
            updateAllTimeDisplaysInScope(panel);

            // Bind input/change listeners for time displays & auto-calculation
            const timeInputs = panel.querySelectorAll('input[type="time"]');
            timeInputs.forEach(input => {
                input.addEventListener('input', function() {
                    updateAllTimeDisplaysInScope(panel);
                });
                input.addEventListener('change', function() {
                    updateAllTimeDisplaysInScope(panel);
                });
            });

            // Bind auto calculation on shift_start_time, required_work_minutes, lunch_minutes
            const startInput = panel.querySelector('input[name="shift_start_time"]');
            const reqInput   = panel.querySelector('input[name="required_work_minutes"]');
            const lunchInput = panel.querySelector('input[name="lunch_minutes"]');

            if (startInput) {
                startInput.addEventListener('input', () => autoCalculateTimingsForScope(panel));
                startInput.addEventListener('change', () => autoCalculateTimingsForScope(panel));
            }
            if (reqInput) {
                reqInput.addEventListener('input', () => autoCalculateTimingsForScope(panel));
                reqInput.addEventListener('change', () => autoCalculateTimingsForScope(panel));
            }
            if (lunchInput) {
                lunchInput.addEventListener('input', () => autoCalculateTimingsForScope(panel));
                lunchInput.addEventListener('change', () => autoCalculateTimingsForScope(panel));
            }
        });
    });
</script>
@endpush
