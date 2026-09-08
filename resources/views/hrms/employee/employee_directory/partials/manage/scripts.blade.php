<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toast Alert System Helper
        function showToast(message, type = 'success') {
            let container = document.getElementById('orb-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'orb-toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = `orb-toast orb-toast-${type}`;

            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            toast.innerHTML = `
                <div class="orb-toast-content">
                    <span class="orb-toast-icon"><i class="fas ${icon}"></i></span>
                    <span class="orb-toast-text">${message}</span>
                </div>
                <button class="orb-toast-close"><i class="fas fa-times"></i></button>
            `;

            container.appendChild(toast);

            // Close event
            toast.querySelector('.orb-toast-close').addEventListener('click', function() {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            });

            // Auto close
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 4000);
        }

        // Keep track of original values for Cancel restoration
        const originalValues = {};

        function getCardInputs(cardId) {
            const card = document.getElementById(cardId);
            return card ? card.querySelectorAll('input, select, textarea') : [];
        }

        function toggleEmploymentSections() {
            const employmentTypeSelect = document.getElementById('employment_type');
            const employeeStageSelect = document.getElementById('employee_stage');
            const employeeStageDisplay = document.getElementById('employee_stage_display');

            const type = employmentTypeSelect ? employmentTypeSelect.value : '';
            const currentStage = employeeStageSelect ? employeeStageSelect.value : '';

            let stage = currentStage || (
                type === 'intern' ? 'internship' :
                (type === 'freelancer' ? 'freelance' :
                    (type === 'contract' ? 'contract' : 'probation'))
            );

            if (employmentTypeSelect && document.body.classList.contains('edit-mode')) {
                stage = type === 'intern' ? 'internship' :
                    (type === 'freelancer' ? 'freelance' :
                        (type === 'contract' ? 'contract' : 'probation'));

                if (employeeStageSelect) {
                    employeeStageSelect.value = stage;
                }
            }

            if (employeeStageDisplay) {
                employeeStageDisplay.value = stage ?
                    stage.replace(/_/g, ' ').replace(/\b\w/g, function(char) {
                        return char.toUpperCase();
                    }) :
                    'Auto';
            }

            document.querySelectorAll('.internship-section').forEach(function(el) {
                el.style.display = stage === 'internship' ? 'block' : 'none';
            });

            document.querySelectorAll('.probation-section').forEach(function(el) {
                el.style.display = stage === 'internship' || stage === 'contract' || stage === 'freelance' ?
                    'none' :
                    'block';
            });

            document.querySelectorAll('.contract-section').forEach(function(el) {
                el.style.display = stage === 'contract' || stage === 'freelance' ? 'block' : 'none';
            });
        }

        function loadDesignations(departmentId, selectedId = '') {
            const designationSelect = document.getElementById('designation_id');
            if (!designationSelect) return;

            if (!departmentId) {
                designationSelect.innerHTML = '<option value="">Select Designation</option>';
                return;
            }

            designationSelect.innerHTML = '<option value="">Loading...</option>';

            fetch("{{ url('/hrms/employees/get-designations') }}/" + departmentId)
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    designationSelect.innerHTML = '<option value="">Select Designation</option>';

                    data.forEach(function(item) {
                        const selected = String(selectedId) === String(item.id) ? 'selected' : '';
                        designationSelect.innerHTML += '<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>';
                    });
                })
                .catch(function() {
                    designationSelect.innerHTML = '<option value="">Unable to load designations</option>';
                });
        }

        // Rebind and reinitialize all event listeners
        function rebindAllListeners() {
            // 1. Section EDIT buttons
            document.querySelectorAll('.edit-sec-btn').forEach(btn => {
                // Remove existing listener to prevent duplicate binding
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.edit-sec-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section');
                    const card = document.getElementById(sectionId);
                    if (!card) return;

                    card.classList.add('is-editing');

                    getCardInputs(sectionId).forEach(el => {
                        const name = el.name || el.id;
                        if (name) {
                            if (el.type === 'file') {
                                originalValues[name] = '';
                            } else {
                                originalValues[name] = el.value;
                            }
                        }

                        // Enable fields except readonly system fields
                        if (el.type !== 'file' && !el.classList.contains('em-control-readonly') && el.name !== 'derived_employee_stage' && el.id !== 'employee_stage_display') {
                            el.removeAttribute('readonly');
                            el.removeAttribute('disabled');
                        } else if (el.type === 'file') {
                            el.removeAttribute('disabled');
                        }
                    });

                    this.style.display = 'none';
                    card.querySelector('.cancel-sec-btn').style.display = 'inline-flex';
                    card.querySelector('.save-sec-btn').style.display = 'inline-flex';

                    if (sectionId === 'cardA') {
                        document.body.classList.add('edit-mode');
                        toggleEmploymentSections();
                    }
                });
            });

            // 2. Section CANCEL buttons
            document.querySelectorAll('.cancel-sec-btn').forEach(btn => {
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.cancel-sec-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section');
                    const card = document.getElementById(sectionId);
                    if (!card) return;

                    card.classList.remove('is-editing');

                    getCardInputs(sectionId).forEach(el => {
                        const name = el.name || el.id;
                        if (name && originalValues[name] !== undefined) {
                            el.value = originalValues[name];
                        }

                        // Restore readonly/disabled
                        if (el.type !== 'file' && el.name !== 'name' && el.name !== 'email' && el.name !== 'phone' && !el.classList.contains('editable') && !el.classList.contains('editable-select')) {
                            // Keep unchanged
                        } else {
                            if (el.tagName === 'SELECT' || el.type === 'file') {
                                el.setAttribute('disabled', 'disabled');
                            } else {
                                el.setAttribute('readonly', 'readonly');
                            }
                        }
                    });

                    card.querySelector('.edit-sec-btn').style.display = 'inline-flex';
                    this.style.display = 'none';
                    card.querySelector('.save-sec-btn').style.display = 'none';

                    if (sectionId === 'cardA') {
                        document.body.classList.remove('edit-mode');
                        toggleEmploymentSections();
                    }
                });
            });

            // 3. Section SAVE buttons
            document.querySelectorAll('.save-sec-btn').forEach(btn => {
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.save-sec-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section');
                    const card = document.getElementById(sectionId);
                    const saveBtn = this;
                    const cancelBtn = card.querySelector('.cancel-sec-btn');
                    const form = document.getElementById('employeeManageForm');

                    const originalHtml = saveBtn.innerHTML;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
                    saveBtn.setAttribute('disabled', 'disabled');
                    cancelBtn.setAttribute('disabled', 'disabled');

                    // Temporarily enable all fields in the entire form so FormData captures all required fields
                    const disabledElements = [];
                    form.querySelectorAll('[disabled]').forEach(el => {
                        disabledElements.push(el);
                        el.removeAttribute('disabled');
                    });

                    const formData = new FormData(form);

                    // Restore disabled state immediately
                    disabledElements.forEach(el => {
                        el.setAttribute('disabled', 'disabled');
                    });

                    fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(htmlText => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(htmlText, 'text/html');

                            const hasError = doc.querySelector('.alert-danger');
                            if (hasError) {
                                const errorMsg = hasError.textContent.trim();
                                showToast(errorMsg || 'Validation failed. Please check inputs.', 'error');

                                saveBtn.innerHTML = originalHtml;
                                saveBtn.removeAttribute('disabled');
                                cancelBtn.removeAttribute('disabled');
                                return;
                            }

                            // Success! Update DOM sections dynamically
                            const updateSection = (id) => {
                                const oldSec = document.getElementById(id);
                                const newSec = doc.getElementById(id);
                                if (oldSec && newSec) {
                                    oldSec.innerHTML = newSec.innerHTML;
                                }
                            };

                            updateSection('cardA');
                            updateSection('cardB');

                            const oldHeader = document.querySelector('.ev-header');
                            const newHeader = doc.querySelector('.ev-header');
                            if (oldHeader && newHeader) {
                                oldHeader.innerHTML = newHeader.innerHTML;
                            }

                            document.body.classList.remove('edit-mode');
                            document.querySelectorAll('.em-card').forEach(c => c.classList.remove('is-editing'));

                            rebindAllListeners();

                            showToast(sectionId === 'cardA' ? 'Employee details updated successfully!' : 'Profile details updated successfully!', 'success');
                        })
                        .catch(error => {
                            console.error('Save error:', error);
                            showToast('An error occurred while saving. Please try again.', 'error');

                            saveBtn.innerHTML = originalHtml;
                            saveBtn.removeAttribute('disabled');
                            cancelBtn.removeAttribute('disabled');
                        });
                });
            });

            // 4. Asynchronous Document Re-upload flow
            document.querySelectorAll('.js-ajax-doc-upload').forEach(input => {
                input.replaceWith(input.cloneNode(true));
            });

            document.querySelectorAll('.js-ajax-doc-upload').forEach(input => {
                input.addEventListener('change', function() {
                    const fileInput = this;
                    const file = fileInput.files[0];
                    if (!file) return;

                    const label = fileInput.closest('.btn-doc-reupload');
                    const labelSpan = label ? label.querySelector('span') : null;
                    const labelIcon = label ? label.querySelector('.fas') : null;
                    const docTitle = label ? label.getAttribute('data-doc-title') : 'Document';

                    const originalText = labelSpan ? labelSpan.textContent : 'Reupload';
                    if (labelSpan) labelSpan.textContent = 'Uploading...';
                    if (labelIcon) {
                        labelIcon.className = 'fas fa-spinner fa-spin mr-1';
                    }
                    fileInput.setAttribute('disabled', 'disabled');

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch(fileInput.getAttribute('data-action'), {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Upload failed');
                            }
                            return response.text();
                        })
                        .then(htmlText => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(htmlText, 'text/html');

                            const newCardC = doc.getElementById('documentCard');
                            if (newCardC) {
                                document.getElementById('documentCard').innerHTML = newCardC.innerHTML;
                            }

                            rebindAllListeners();
                            showToast(`${docTitle} uploaded successfully!`, 'success');
                        })
                        .catch(error => {
                            console.error('Upload error:', error);
                            showToast(`Failed to upload ${docTitle}. Please try again.`, 'error');

                            if (labelSpan) labelSpan.textContent = originalText;
                            if (labelIcon) {
                                labelIcon.className = 'fas fa-cloud-upload-alt mr-1';
                            }
                            fileInput.removeAttribute('disabled');
                        });
                });
            });

            // 4a. Asynchronous Document Verification
            document.querySelectorAll('.js-ajax-doc-verify').forEach(btn => {
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.js-ajax-doc-verify').forEach(btn => {
                btn.addEventListener('click', function() {
                    const actionUrl = this.getAttribute('data-action');
                    const docTitle = this.getAttribute('data-doc-title') || 'Document';

                    if (!confirm(`Are you sure you want to verify and lock ${docTitle}?`)) return;

                    const verifyBtn = this;
                    const originalHtml = verifyBtn.innerHTML;
                    verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Verifying...';
                    verifyBtn.setAttribute('disabled', 'disabled');

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch(actionUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Verification failed');
                            }
                            return response.text();
                        })
                        .then(htmlText => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(htmlText, 'text/html');

                            const newCardC = doc.getElementById('documentCard');
                            if (newCardC) {
                                document.getElementById('documentCard').innerHTML = newCardC.innerHTML;
                            }

                            rebindAllListeners();
                            showToast(`${docTitle} verified successfully!`, 'success');
                        })
                        .catch(error => {
                            console.error('Verify error:', error);
                            showToast(`Failed to verify ${docTitle}. Please try again.`, 'error');
                            verifyBtn.innerHTML = originalHtml;
                            verifyBtn.removeAttribute('disabled');
                        });
                });
            });

            // 4b. Asynchronous Document Rejection
            document.querySelectorAll('.js-ajax-doc-reject').forEach(btn => {
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.js-ajax-doc-reject').forEach(btn => {
                btn.addEventListener('click', function() {
                    const actionUrl = this.getAttribute('data-action');
                    const docTitle = this.getAttribute('data-doc-title') || 'Document';

                    const reason = prompt(`Enter rejection reason for ${docTitle}:`);
                    if (reason === null) return; // cancelled
                    if (!reason.trim()) {
                        showToast('Rejection reason is required!', 'error');
                        return;
                    }

                    const rejectBtn = this;
                    const originalHtml = rejectBtn.innerHTML;
                    rejectBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Rejecting...';
                    rejectBtn.setAttribute('disabled', 'disabled');

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('rejection_reason', reason);

                    fetch(actionUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Rejection failed');
                            }
                            return response.text();
                        })
                        .then(htmlText => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(htmlText, 'text/html');

                            const newCardC = doc.getElementById('documentCard');
                            if (newCardC) {
                                document.getElementById('documentCard').innerHTML = newCardC.innerHTML;
                            }

                            rebindAllListeners();
                            showToast(`${docTitle} rejected successfully!`, 'success');
                        })
                        .catch(error => {
                            console.error('Reject error:', error);
                            showToast(`Failed to reject ${docTitle}. Please try again.`, 'error');
                            rejectBtn.innerHTML = originalHtml;
                            rejectBtn.removeAttribute('disabled');
                        });
                });
            });

            // 5. Department Designation dropdown cascade binding
            const departmentSelect = document.getElementById('department_id');
            if (departmentSelect) {
                // Clean and bind
                const newDept = departmentSelect.cloneNode(true);
                departmentSelect.replaceWith(newDept);
                newDept.addEventListener('change', function() {
                    loadDesignations(this.value);
                });
            }

            // 6. Employment type stage toggle binding
            const employmentTypeSelect = document.getElementById('employment_type');
            if (employmentTypeSelect) {
                const newEmpType = employmentTypeSelect.cloneNode(true);
                employmentTypeSelect.replaceWith(newEmpType);
                newEmpType.addEventListener('change', function() {
                    toggleEmploymentSections();
                });
            }

            // 6b. Work Schedule Type custom timings toggle
            const workScheduleSelect = document.getElementById('work_schedule_type');
            if (workScheduleSelect) {
                const newSchedule = workScheduleSelect.cloneNode(true);
                workScheduleSelect.replaceWith(newSchedule);
                newSchedule.addEventListener('change', function() {
                    handleScheduleChange(false);
                });
            }

            // 6c. Timing calculation and helper bindings
            const timeFields = ['punch_allowed_from', 'shift_start_time', 'late_after_time', 'half_day_after_time', 'block_after_time', 'shift_end_time'];
            timeFields.forEach(field => {
                const el = document.getElementById(field);
                if (el) {
                    const newEl = el.cloneNode(true);
                    el.replaceWith(newEl);
                    newEl.addEventListener('input', updateAllTimeDisplays);
                    newEl.addEventListener('change', updateAllTimeDisplays);

                    if (field === 'shift_start_time') {
                        newEl.addEventListener('input', autoCalculateTimings);
                        newEl.addEventListener('change', autoCalculateTimings);
                    }
                }
            });

            // 6d. Probation calculation and helper bindings
            const probationControls = ['joining_date', 'manage_probation_start_date', 'manage_probation_duration_option', 'manage_custom_duration_value', 'manage_custom_duration_unit'];
            probationControls.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    const newEl = el.cloneNode(true);
                    el.replaceWith(newEl);
                    newEl.addEventListener('change', calculateManageProbation);
                    newEl.addEventListener('input', calculateManageProbation);
                }
            });

            const reqMinEl = document.getElementById('required_work_minutes');
            const lunchMinEl = document.getElementById('lunch_minutes');

            if (reqMinEl) {
                const newReqMin = reqMinEl.cloneNode(true);
                reqMinEl.replaceWith(newReqMin);
                newReqMin.addEventListener('input', autoCalculateTimings);
                newReqMin.addEventListener('change', autoCalculateTimings);
            }
            if (lunchMinEl) {
                const newLunchMin = lunchMinEl.cloneNode(true);
                lunchMinEl.replaceWith(newLunchMin);
                newLunchMin.addEventListener('input', autoCalculateTimings);
                newLunchMin.addEventListener('change', autoCalculateTimings);
            }

            // 7. Toggle experience fields initially
            const manageExpSelect = document.getElementById('manage_experience_type');
            if (manageExpSelect) {
                toggleManageExperienceFields(manageExpSelect.value);
            }
        }

        const shiftDefaultTimings = {
            @foreach($attendanceTimes as $shiftItem)
            '{{ $shiftItem->code }}': {
                'punch_allowed_from': '{{ $shiftItem->punch_allowed_from ? \Carbon\Carbon::parse($shiftItem->punch_allowed_from)->format('H:i') : '' }}',
                'shift_start_time': '{{ $shiftItem->shift_start_time ? \Carbon\Carbon::parse($shiftItem->shift_start_time)->format('H:i') : '' }}',
                'late_after_time': '{{ $shiftItem->late_after_time ? \Carbon\Carbon::parse($shiftItem->late_after_time)->format('H:i') : '' }}',
                'half_day_after_time': '{{ $shiftItem->half_day_after_time ? \Carbon\Carbon::parse($shiftItem->half_day_after_time)->format('H:i') : '' }}',
                'block_after_time': '{{ $shiftItem->block_after_time ? \Carbon\Carbon::parse($shiftItem->block_after_time)->format('H:i') : '' }}',
                'shift_end_time': '{{ $shiftItem->shift_end_time ? \Carbon\Carbon::parse($shiftItem->shift_end_time)->format('H:i') : '' }}',
                'required_work_minutes': '{{ $shiftItem->required_work_minutes ?? '' }}',
                'lunch_minutes': '{{ $shiftItem->lunch_break_minutes ?? '' }}'
            },
            @endforeach
        };

        function formatTimeTo12Hour(timeStr) {
            if (!timeStr) return '--:--';
            const [hoursStr, minutesStr] = timeStr.split(':');
            let hours = Number(hoursStr);
            const minutes = Number(minutesStr);
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
                    const overlay = input.parentNode.querySelector('.time-display-val');
                    if (overlay) {
                        overlay.textContent = formatTimeTo12Hour(input.value);
                    }
                }
            });
        }
        window.updateAllTimeDisplays = updateAllTimeDisplays;

        function addMinutesToTime(timeStr, minutesToAdd) {
            if (!timeStr) return '';
            const [hours, minutes] = timeStr.split(':').map(Number);
            const date = new Date();
            date.setHours(hours);
            date.setMinutes(minutes + minutesToAdd);
            date.setSeconds(0);

            const h = String(date.getHours()).padStart(2, '0');
            const m = String(date.getMinutes()).padStart(2, '0');
            return `${h}:${m}`;
        }

        function autoCalculateTimings() {
            const shiftStartInput = document.getElementById('shift_start_time');
            const lateAfterInput = document.getElementById('late_after_time');
            const halfDayAfterInput = document.getElementById('half_day_after_time');
            const shiftEndInput = document.getElementById('shift_end_time');
            const reqMinutesInput = document.getElementById('required_work_minutes');
            const lunchMinutesInput = document.getElementById('lunch_minutes');
            const punchAllowedInput = document.getElementById('punch_allowed_from');
            const blockedPunchInput = document.getElementById('block_after_time');

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

            // 3. Punch Allowed From: Start - 60 mins (1 hour before)
            if (punchAllowedInput) {
                punchAllowedInput.value = addMinutesToTime(startTime, -60);
            }

            // 4. Shift End Time: Start + Required Minutes + Lunch Minutes
            const reqMin = Number(reqMinutesInput?.value || 0);
            const lunchMin = Number(lunchMinutesInput?.value || 0);
            let calculatedEnd = '';
            if (shiftEndInput && (reqMin > 0 || lunchMin > 0)) {
                calculatedEnd = addMinutesToTime(startTime, reqMin + lunchMin);
                shiftEndInput.value = calculatedEnd;
            }

            // 5. Blocked Punch: Start + 75 mins
            if (blockedPunchInput) {
                blockedPunchInput.value = addMinutesToTime(startTime, 75);
            }

            updateAllTimeDisplays();
        }
        window.autoCalculateTimings = autoCalculateTimings;

        function calculateManageProbation() {
            const joiningDateInput = document.getElementById('joining_date');
            const startInput = document.getElementById('manage_probation_start_date');
            const optionSelect = document.getElementById('manage_probation_duration_option');
            const customValueInput = document.getElementById('manage_custom_duration_value');
            const customUnitSelect = document.getElementById('manage_custom_duration_unit');
            const customBox = document.getElementById('manage_custom_probation_box');
            const endInput = document.getElementById('manage_probation_end_date');
            const confirmationInput = document.getElementById('manage_confirmation_date');

            if (!optionSelect) return;

            const option = optionSelect.value;
            if (customBox) {
                if (option === 'custom') {
                    customBox.classList.remove('em-hidden');
                    customBox.style.display = '';
                } else {
                    customBox.classList.add('em-hidden');
                    customBox.style.display = 'none';
                }
            }

            let startDateStr = startInput ? startInput.value : '';
            if (!startDateStr && joiningDateInput) {
                startDateStr = joiningDateInput.value;
            }
            if (!startDateStr) return;

            const parts = startDateStr.split('-');
            if (parts.length !== 3) return;
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            const start = new Date(y, m, d);
            if (isNaN(start.getTime())) return;

            let type = 'months';
            let val = 3;

            if (option === '6_months') {
                type = 'months';
                val = 6;
            } else if (option === 'custom') {
                type = customUnitSelect && customUnitSelect.value === 'days' ? 'days' : 'months';
                val = parseInt(customValueInput ? customValueInput.value : '1', 10) || 1;
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

            const formatDateStr = (dateObj) => {
                const year = dateObj.getFullYear();
                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                const day = String(dateObj.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            if (endInput) {
                endInput.value = formatDateStr(endDate);
            }
            if (confirmationInput) {
                confirmationInput.value = formatDateStr(permDate);
            }
        }
        window.calculateManageProbation = calculateManageProbation;

        function toggleFlexibleTimingFields() {
            const workScheduleSelect = document.getElementById('work_schedule_type');
            const flexibleShiftTimingsBox = document.getElementById('flexible_shift_timings_box');
            if (!workScheduleSelect || !flexibleShiftTimingsBox) return;

            if (workScheduleSelect.value === 'flexible_part_time') {
                flexibleShiftTimingsBox.style.display = 'block';
                flexibleShiftTimingsBox.querySelectorAll('input').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            } else {
                flexibleShiftTimingsBox.style.display = 'none';
                flexibleShiftTimingsBox.querySelectorAll('input').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        }
        window.toggleFlexibleTimingFields = toggleFlexibleTimingFields;

        function handleScheduleChange(isInit = false) {
            const workScheduleSelect = document.getElementById('work_schedule_type');
            if (!workScheduleSelect) return;
            const code = workScheduleSelect.value;
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
        window.handleScheduleChange = handleScheduleChange;

        function toggleManageExperienceFields(value) {
            const container = document.getElementById('manage_total_experience_container');
            const input = document.getElementById('manage_total_experience');
            if (value === 'fresher') {
                if (container) container.style.display = 'none';
                if (input) {
                    input.removeAttribute('required');
                    input.value = '0';
                }
            } else {
                if (container) container.style.display = 'block';
                if (input) {
                    input.setAttribute('required', 'required');
                    if (input.value === '0') input.value = '';
                }
            }
        }
        window.toggleManageExperienceFields = toggleManageExperienceFields;

        // Initial setup
        rebindAllListeners();
        toggleEmploymentSections();
        handleScheduleChange(true);

        // Handle Laravel validation redirect fallbacks (if any non-ajax errors exist)
        @if($errors->any())
        const cardA = document.getElementById('cardA');
        if (cardA) {
            const editBtn = cardA.querySelector('.edit-sec-btn');
            if (editBtn) editBtn.click();
        }
        @endif
    });
</script>
