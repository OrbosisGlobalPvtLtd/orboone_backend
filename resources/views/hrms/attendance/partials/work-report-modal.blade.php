<!-- Shared Premium Work Report Details Modal -->
<div class="modal fade" id="sharedWorkReportModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content orb-modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 50px rgba(0,0,0,0.12); overflow: hidden; background: #fff;">

            <!-- Sticky Top Report Header (Compact Enterprise Style) -->
            <div class="modal-header d-block p-0 border-0" style="position: sticky; top: 0; z-index: 10;">
                <div style="background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)); padding: 14px 20px; color: #fff; position: relative;">
                    <!-- Close button -->
                    <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close" style="position: absolute; right: 16px; top: 12px; outline: none; border: none; background: transparent; font-size: 24px; font-weight: 300; transition: all 0.2s; cursor: pointer; line-height: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>

                    <!-- Main Header Row: Left Employee Info, Right Status Badge -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap pr-5" style="gap: 14px;">
                        <div class="d-flex align-items-center" style="gap: 14px;">
                            <div id="modal-emp-avatar" style="width: 40px; height: 40px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); color: #fff; font-size: 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden; flex-shrink: 0;">
                                -
                            </div>
                            <div>
                                <h4 id="modal-emp-name" style="margin: 0; font-weight: 800; font-size: 15px; letter-spacing: -0.01em; color: #fff; line-height: normal;">Employee Name</h4>
                                <div style="font-size: 11px; color: rgba(255, 255, 255, 0.85); font-weight: 600; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 4px;">
                                    <span id="modal-emp-code">EMP-000</span>
                                    <span style="opacity: 0.5;">&bull;</span>
                                    <span id="modal-emp-dept">Department</span>
                                    <span style="opacity: 0.5;">&bull;</span>
                                    <span id="modal-emp-desig">Designation</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <span id="modal-attendance-badge" class="badge-premium-pill" style="font-size: 9px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; padding: 4px 10px; border-radius: 6px;">
                                Present
                            </span>
                        </div>
                    </div>

                    <!-- Mini Metadata Row -->
                    <div class="mt-2 pt-2 border-top border-white-50" style="border-top: 1px solid rgba(255, 255, 255, 0.15) !important;">
                        <div style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.95); display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span><i class="far fa-calendar-alt mr-1"></i> <span id="modal-work-date">-</span></span>
                            <span style="opacity: 0.5;">|</span>
                            <span><i class="far fa-clock mr-1"></i> Shift: <span id="modal-shift-name">-</span></span>
                            <span style="opacity: 0.5;">|</span>
                            <span><i class="fas fa-laptop-house mr-1"></i> Mode: <span id="modal-mode-badge" style="text-transform: uppercase;">-</span></span>
                            <span style="opacity: 0.5;">|</span>
                            <span><i class="far fa-check-circle mr-1"></i> Submitted: <span id="modal-submitted-badge">-</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scrollable Body (Enterprise Activity Scroll) -->
            <div class="modal-body p-3" style="max-height: calc(75vh - 120px); overflow-y: auto; background: #F8FAFC;">

                <!-- Graceful Fallback Alert -->
                <div id="modal-fallback-alert" class="alert alert-danger border-0 shadow-sm d-none mb-3" style="border-radius: 12px; font-size: 13px;">
                    <i class="fas fa-exclamation-triangle mr-2"></i> <span id="fallback-message">Unable to load structured report.</span>
                </div>

                <div id="modal-content-container">
                    <!-- SECTION 1: OVERVIEW CARD (Daily Progress Note) -->
                    <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; overflow: hidden; background: #fff;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span style="width: 24px; height: 24px; border-radius: 6px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">
                                    <i class="fas fa-file-invoice"></i>
                                </span>
                                <span style="font-size: 9.5px; font-weight: 800; color: var(--orb-primary); letter-spacing: 0.08em; text-transform: uppercase;">Report Overview</span>
                            </div>
                            <h5 id="modal-report-title" style="margin: 0 0 8px 0; font-weight: 800; color: #101828; font-size: 14px;">Work Report Submitted</h5>

                            <p id="modal-report-desc" style="font-size: 12.5px; line-height: 1.5; color: #475467; margin: 0; padding: 10px 14px; background: #F8FAFC; border-radius: 10px; border-left: 3px solid var(--orb-secondary);">
                                -
                            </p>
                        </div>
                    </div>

                    <!-- SECTION 2: TASK CHECKLIST -->
                    <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; overflow: hidden; background: #fff;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2.5">
                                <span style="width: 24px; height: 24px; border-radius: 6px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">
                                    <i class="fas fa-tasks"></i>
                                </span>
                                <span style="font-size: 9.5px; font-weight: 800; color: var(--orb-primary); letter-spacing: 0.08em; text-transform: uppercase;">Task Checklist</span>
                            </div>
                            <div id="modal-tasks-list" class="d-flex flex-column gap-1.5">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: TEST STATUS & ISSUES -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 h-100 shadow-sm" style="border-radius: 16px; background: #fff;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2.5">
                                        <span style="width: 24px; height: 24px; border-radius: 6px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">
                                            <i class="fas fa-vial"></i>
                                        </span>
                                        <span style="font-size: 9.5px; font-weight: 800; color: var(--orb-primary); letter-spacing: 0.08em; text-transform: uppercase;">Test Verification</span>
                                    </div>
                                    <div id="modal-test-status-area">
                                        <!-- Dynamic badge -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card border-0 h-100 shadow-sm" style="border-radius: 16px; background: #fff;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2.5">
                                        <span style="width: 24px; height: 24px; border-radius: 6px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </span>
                                        <span style="font-size: 9.5px; font-weight: 800; color: var(--orb-primary); letter-spacing: 0.08em; text-transform: uppercase;">Issues & Blockers</span>
                                    </div>
                                    <div id="modal-issues-area">
                                        <!-- Dynamic card -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: NOTES -->
                    <div id="modal-notes-wrapper" class="card border-0 mb-2 shadow-sm d-none" style="border-radius: 16px; overflow: hidden; background: #fff;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span style="width: 24px; height: 24px; border-radius: 6px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">
                                    <i class="fas fa-sticky-note"></i>
                                </span>
                                <span style="font-size: 9.5px; font-weight: 800; color: var(--orb-primary); letter-spacing: 0.08em; text-transform: uppercase;">Developer Notes</span>
                            </div>
                            <div id="modal-notes-text" class="p-2.5 rounded-lg" style="background: #FFFBEB; border: 1px solid #FEF3C7; color: #B45309; font-size: 12px; line-height: 1.4; border-radius: 10px;">
                                -
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function parseAndOpenWorkReport(btnElement) {
        const rawLog = btnElement.getAttribute('data-work-log');

        // Debug payload temporarily
        console.log('[WorkReportPayload]', rawLog);

        try {
            if (!rawLog) {
                showModalFallback("No report data associated.");
                return;
            }

            let log = null;
            try {
                log = JSON.parse(rawLog);
            } catch (e) {
                showModalFallback("Unable to load structured report.");
                return;
            }

            if (!log) {
                showModalFallback("Unable to load structured report.");
                return;
            }

            // Hide fallback, show content
            document.getElementById('modal-fallback-alert').classList.add('d-none');
            document.getElementById('modal-content-container').classList.remove('d-none');

            // Populate Employee Info
            document.getElementById('modal-emp-name').innerText = log.employee_name || 'Employee';
            const avatarBox = document.getElementById('modal-emp-avatar');
            const initial = (log.employee_initial || (log.employee_name || 'E').substring(0, 1)).toUpperCase();

            if (log.passport_photo_url) {
                avatarBox.innerHTML = `
                    <img src="${escapeHtml(log.passport_photo_url)}"
                         alt="${escapeHtml(log.employee_name || 'Employee')}"
                         style="width:100%;height:100%;object-fit:cover;border-radius:inherit;display:block;"
                         onerror="this.style.display='none'; this.parentElement.innerHTML='${initial}';">
                `;
            } else {
                avatarBox.innerHTML = '';
                avatarBox.innerText = initial;
            }
            document.getElementById('modal-emp-code').innerText = log.employee_code || 'N/A';
            document.getElementById('modal-emp-dept').innerText = log.department || 'Staff';
            document.getElementById('modal-emp-desig').innerText = log.designation || 'Member';

            // Populate Date / Shift / Status
            document.getElementById('modal-work-date').innerText = log.work_date || '-';
            document.getElementById('modal-shift-name').innerText = log.shift_name || 'Default Shift';

            // Status Badge
            const statusBadge = document.getElementById('modal-attendance-badge');
            const status = (log.attendance_status || 'present').toLowerCase();
            statusBadge.innerText = status.replace('_', ' ');
            statusBadge.className = 'badge-premium-pill';
            if (status === 'present') {
                statusBadge.style.background = '#DCFCE7';
                statusBadge.style.color = '#15803D';
            } else if (status === 'absent' || status === 'lwp') {
                statusBadge.style.background = '#FEE2E2';
                statusBadge.style.color = '#B91C1C';
            } else if (status === 'half_day') {
                statusBadge.style.background = '#FEF3C7';
                statusBadge.style.color = '#B45309';
            } else {
                statusBadge.style.background = '#F3F4F6';
                statusBadge.style.color = '#374151';
            }

            // Mode & Submitted Badge
            document.getElementById('modal-mode-badge').innerText = (log.work_mode || 'WFO').toUpperCase();
            document.getElementById('modal-submitted-badge').innerText = log.submitted_time || '-';

            // Title & Description
            document.getElementById('modal-report-title').innerText = log.title || 'Work Report Submitted';
            document.getElementById('modal-report-desc').innerText = log.description || 'No summary description provided.';

            // Requirements / Tasks Checklist
            const tasksList = document.getElementById('modal-tasks-list');
            tasksList.innerHTML = '';

            // Normalize requirements/tasks
            const items = log.requirements || log.tasks || [];
            if (Array.isArray(items) && items.length > 0) {
                items.forEach(item => {
                    let taskText = '';
                    let isCompleted = true;

                    if (typeof item === 'string') {
                        taskText = item;
                        isCompleted = true;
                    } else if (typeof item === 'object' && item !== null) {
                        taskText = item.text || item.task || item.title || item.description || 'Task';
                        if (item.done !== undefined) {
                            isCompleted = (item.done === true || item.done === 'true');
                        } else {
                            const tStatus = (item.status || 'completed').toLowerCase();
                            isCompleted = (tStatus === 'completed' || tStatus === 'done' || tStatus === 'success');
                        }
                    }

                    const row = document.createElement('div');
                    row.style.background = isCompleted ? '#F0FDF4' : '#F9FAFB';
                    row.style.border = isCompleted ? '1px solid #BBF7D0' : '1px solid #E5E7EB';
                    row.style.borderRadius = '10px';
                    row.style.padding = '8px 12px';
                    row.className = 'd-flex justify-content-between align-items-center gap-3';

                    row.innerHTML = `
                        <div style="font-size: 12.5px; font-weight: 700; color: ${isCompleted ? '#166534' : '#475467'};">
                            ${isCompleted ? '☑' : '☐'} ${taskText}
                        </div>
                        <span style="font-size: 8.5px; font-weight: 800; padding: 2px 6px; border-radius: 50px; ${isCompleted ? 'background: #DCFCE7; color: #166534;' : 'background: #F3F4F6; color: #6B7280;'}">
                            ${isCompleted ? 'COMPLETED' : 'PENDING'}
                        </span>
                    `;
                    tasksList.appendChild(row);
                });
            } else {
                tasksList.innerHTML = `
                    <div class="text-center py-2.5 border rounded-lg bg-white text-muted" style="border-radius: 10px; font-style: italic; font-size: 11.5px;">
                        <i class="fas fa-info-circle mr-1"></i> No checklist items.
                    </div>
                `;
            }

            // Test Verification Status
            const testArea = document.getElementById('modal-test-status-area');
            let tested = false;
            let completed = false;

            if (log.test_status && typeof log.test_status === 'object') {
                tested = log.test_status.tested === true || log.test_status.tested === 'true';
                completed = log.test_status.completed === true || log.test_status.completed === 'true';
            } else if (log.tested !== undefined) {
                tested = log.tested === true || log.tested === 'tested' || log.tested === 'Completed';
                completed = log.tested === true || log.tested === 'tested' || log.tested === 'Completed';
            }

            const testedBg = tested ? '#E0F2FE' : '#FEF2F2';
            const testedColor = tested ? '#0369A1' : '#B91C1C';
            const testedText = tested ? 'Tested ✅' : 'Untested ❌';

            const completedBg = completed ? '#DCFCE7' : '#FEF2F2';
            const completedColor = completed ? '#15803D' : '#B91C1C';
            const completedText = completed ? 'Completed ✅' : 'Incomplete ❌';

            testArea.innerHTML = `
                <div class="d-flex flex-column gap-1.5">
                    <div style="background: ${testedBg}; color: ${testedColor}; border-radius: 10px; padding: 8px; font-size: 11.5px; font-weight: 800; text-align: center; border: 1px dashed ${testedColor};">
                        <i class="fas fa-vial mr-2"></i> ${testedText}
                    </div>
                    <div style="background: ${completedBg}; color: ${completedColor}; border-radius: 10px; padding: 8px; font-size: 11.5px; font-weight: 800; text-align: center; border: 1px dashed ${completedColor};">
                        <i class="fas fa-check-circle mr-2"></i> ${completedText}
                    </div>
                </div>
            `;

            // Issues & Blockers (Array / String / Null safety)
            const issuesArea = document.getElementById('modal-issues-area');
            const issues = Array.isArray(log.issues) ? log.issues : (log.issues ? [log.issues] : []);

            let hasIssues = false;
            let issuesText = '';

            const realIssues = issues.filter(item => {
                if (typeof item !== 'string') return true;
                const val = item.trim().toLowerCase();
                return val !== 'no issues' && val !== 'none' && val.length > 0;
            });

            if (realIssues.length > 0) {
                hasIssues = true;
                issuesText = realIssues.join(', ');
            }

            if (hasIssues) {
                issuesArea.innerHTML = `
                    <div style="background: #FFF5F5; border: 1px solid #FEB2B2; color: #C53030; border-radius: 10px; padding: 8px 12px; font-size: 11.5px; line-height: 1.4; border-left: 3px solid #E53E3E !important;">
                        <i class="fas fa-exclamation-circle mr-1.5"></i> <strong>Blocker:</strong> ${issuesText}
                    </div>
                `;
            } else {
                issuesArea.innerHTML = `
                    <div style="background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; border-radius: 10px; padding: 8px; font-size: 11.5px; font-weight: 800; text-align: center; border: 1px dashed #166534;">
                        <i class="fas fa-check-circle mr-2"></i> NO ISSUES REPORTED
                    </div>
                `;
            }

            // Developer Notes
            const notesWrapper = document.getElementById('modal-notes-wrapper');
            if (log.notes && log.notes !== 'null' && log.notes.trim().length > 0) {
                notesWrapper.classList.remove('d-none');
                document.getElementById('modal-notes-text').innerText = log.notes;
            } else {
                notesWrapper.classList.add('d-none');
            }

            // Show modal
            $('#sharedWorkReportModal').modal('show');

        } catch (err) {
            console.error('[parseAndOpenWorkReport Error]', err);
            showModalFallback("Unable to load structured report.");
        }
    }

    function showModalFallback(message) {
        document.getElementById('modal-emp-name').innerText = 'Work Report';
        const avatarBox = document.getElementById('modal-emp-avatar');
        avatarBox.innerHTML = '';
        avatarBox.innerText = 'R';
        document.getElementById('modal-emp-code').innerText = '-';
        document.getElementById('modal-emp-dept').innerText = '-';
        document.getElementById('modal-emp-desig').innerText = '-';
        document.getElementById('modal-work-date').innerText = '-';
        document.getElementById('modal-shift-name').innerText = '-';

        document.getElementById('modal-fallback-alert').classList.remove('d-none');
        document.getElementById('fallback-message').innerText = message;
        document.getElementById('modal-content-container').classList.add('d-none');

        $('#sharedWorkReportModal').modal('show');
    }

    function escapeHtml(value) {
        if (!value) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>