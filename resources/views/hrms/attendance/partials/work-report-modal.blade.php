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
                        <div class="d-flex align-items-center" style="gap: 8px;">
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
                    <!-- MODE A: PROJECT-BASED WORK REPORT (NEW FORMAT) -->
                    <div id="modal-section-projects" class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; overflow: hidden; background: #fff;">
                        <div class="card-body p-4">
                            <!-- Header with Title & Today's Work Status Pill -->
                            <div class="d-flex align-items-center justify-content-between flex-wrap pb-3 mb-3 border-bottom" style="gap: 12px; border-color: #F1F5F9 !important;">
                                <div class="d-flex align-items-center" style="gap: 12px;">
                                    <span style="width: 36px; height: 36px; border-radius: 10px; background: rgba(75, 0, 232, 0.08); color: var(--orb-primary, #4B00E8); display: inline-flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                                        <i class="fas fa-project-diagram"></i>
                                    </span>
                                    <div>
                                        <span style="font-size: 10px; font-weight: 800; color: var(--orb-primary, #4B00E8); letter-spacing: 0.08em; text-transform: uppercase; display: block; margin-bottom: 4px;">Project & Module Wise Report</span>
                                        <h5 id="modal-report-title" style="margin: 0; font-weight: 800; color: #0F172A; font-size: 16px; line-height: 1.35;">Work Report Submitted</h5>
                                    </div>
                                </div>
                                <div id="modal-today-status-badge">
                                    <!-- Dynamic Today's Work Status Pill -->
                                </div>
                            </div>

                            <!-- Executive Statistics Badges -->
                            <div class="d-flex align-items-center flex-wrap mb-4" style="gap: 10px;" id="modal-overview-stats">
                                <span class="d-inline-flex align-items-center px-3 py-1.5 rounded-lg border text-dark" style="border-radius: 8px; font-size: 12px; font-weight: 700; background: #F8FAFC; border-color: #E2E8F0 !important;">
                                    <i class="fas fa-folder text-primary mr-2" style="font-size: 12.5px;"></i> <span id="modal-stat-projects">1 Project</span>
                                </span>
                                <span class="d-inline-flex align-items-center px-3 py-1.5 rounded-lg border text-dark" style="border-radius: 8px; font-size: 12px; font-weight: 700; background: #F8FAFC; border-color: #E2E8F0 !important;">
                                    <i class="fas fa-tasks text-info mr-2" style="font-size: 12.5px;"></i> <span id="modal-stat-tasks">0 Tasks</span>
                                </span>
                                <span class="d-inline-flex align-items-center px-3 py-1.5 rounded-lg border text-success" style="border-radius: 8px; font-size: 12px; font-weight: 700; background: #F0FDF4; border-color: #BBF7D0 !important;">
                                    <i class="fas fa-check-circle mr-2" style="font-size: 12.5px;"></i> <span id="modal-stat-completed">0 Completed</span>
                                </span>
                            </div>

                            <!-- Project-wise Structured Container (Rendered ONCE) -->
                            <div id="modal-projects-cards-list" class="d-flex flex-column" style="gap: 16px;">
                                <!-- Dynamically generated project & module cards -->
                            </div>
                        </div>
                    </div>

                    <!-- MODE B: LEGACY WORK REPORT (OLD FORMAT) -->
                    <div id="modal-section-legacy" class="d-none">
                        <!-- Summary / Description Card -->
                        <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; overflow: hidden; background: #fff;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap pb-2 mb-2.5 border-bottom" style="gap: 8px;">
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span style="width: 28px; height: 28px; border-radius: 8px; background: rgba(75, 0, 232, 0.08); color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 13px;">
                                            <i class="fas fa-file-alt"></i>
                                        </span>
                                        <div>
                                            <span style="font-size: 9.5px; font-weight: 800; color: var(--orb-primary); letter-spacing: 0.08em; text-transform: uppercase; display: block;">Work Summary & Description</span>
                                            <h5 id="modal-legacy-title" style="margin: 0; font-weight: 800; color: #101828; font-size: 14px;">Work Report Submitted</h5>
                                        </div>
                                    </div>
                                    <div id="modal-legacy-status-badge"></div>
                                </div>
                                <div id="modal-legacy-desc-content" style="font-size: 12.5px; line-height: 1.6; color: #334155; margin: 0; padding: 12px 14px; background: #F8FAFC; border-radius: 10px; border-left: 3.5px solid var(--orb-primary, #4B00E8);">
                                    -
                                </div>
                            </div>
                        </div>

                        <!-- Task Checklist Card (For Legacy Reports) -->
                        <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; overflow: hidden; background: #fff;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-2 mb-2.5">
                                    <span style="width: 24px; height: 24px; border-radius: 6px; background: #F4F2FF; color: var(--orb-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">
                                        <i class="fas fa-tasks"></i>
                                    </span>
                                    <span style="font-size: 9.5px; font-weight: 800; color: var(--orb-primary); letter-spacing: 0.08em; text-transform: uppercase;">Task Checklist</span>
                                </div>
                                <div id="modal-legacy-tasks-list" class="d-flex flex-column gap-1.5">
                                    <!-- Populated dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: TEST STATUS & ISSUES -->
                    <!-- SECTION 3: ISSUES & BLOCKERS -->
                    <div class="card border-0 mb-3 shadow-sm" style="border-radius: 16px; background: #fff;">
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

<<script>
    function parseAndOpenWorkReport(btnElement) {
        if (!btnElement) return;
        const rawLog = btnElement.getAttribute('data-work-log');

        try {
            let log = null;

            if (rawLog) {
                try {
                    log = typeof rawLog === 'object' ? rawLog : JSON.parse(rawLog);
                } catch (e) {
                    console.warn('[WorkReportPayload Parse Warning]', e);
                }
            }

            if (!log) {
                // Fallback to legacy attributes if present
                const legacyProject = btnElement.getAttribute('data-project');
                const legacyTasks = btnElement.getAttribute('data-tasks');
                const legacyDesc = btnElement.getAttribute('data-desc');
                const legacyStatus = btnElement.getAttribute('data-status');
                const legacyIssues = btnElement.getAttribute('data-issues');
                const legacyNotes = btnElement.getAttribute('data-notes');

                if (legacyProject || legacyTasks || legacyDesc || legacyStatus) {
                    let parsedTasks = [];
                    try { if (legacyTasks) parsedTasks = JSON.parse(legacyTasks); } catch(err) {}
                    let parsedIssues = [];
                    try { if (legacyIssues) parsedIssues = JSON.parse(legacyIssues); } catch(err) {}

                    log = {
                        title: legacyProject || 'Work Report Submitted',
                        description: legacyDesc || '',
                        status: legacyStatus || 'Completed',
                        requirements: Array.isArray(parsedTasks) ? parsedTasks : [],
                        issues: Array.isArray(parsedIssues) ? parsedIssues : (legacyIssues ? [legacyIssues] : []),
                        notes: legacyNotes || ''
                    };
                }
            }

            if (!log) {
                showModalFallback("No report data associated.");
                return;
            }

            // Hide fallback, show content
            const fallbackEl = document.getElementById('modal-fallback-alert');
            const containerEl = document.getElementById('modal-content-container');
            if (fallbackEl) fallbackEl.classList.add('d-none');
            if (containerEl) containerEl.classList.remove('d-none');

            // Populate Employee Info
            const empNameEl = document.getElementById('modal-emp-name');
            if (empNameEl) empNameEl.innerText = log.employee_name || 'Employee';

            const avatarBox = document.getElementById('modal-emp-avatar');
            const initial = (log.employee_initial || (log.employee_name || 'E').substring(0, 1)).toUpperCase();

            if (avatarBox) {
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
            }

            const codeEl = document.getElementById('modal-emp-code');
            if (codeEl) codeEl.innerText = log.employee_code || 'N/A';

            const deptEl = document.getElementById('modal-emp-dept');
            if (deptEl) deptEl.innerText = log.department || 'Staff';

            const desigEl = document.getElementById('modal-emp-desig');
            if (desigEl) desigEl.innerText = log.designation || 'Member';

            // Populate Date / Shift / Status
            const dateEl = document.getElementById('modal-work-date');
            if (dateEl) dateEl.innerText = log.work_date || '-';

            const shiftEl = document.getElementById('modal-shift-name');
            if (shiftEl) shiftEl.innerText = log.shift_name || 'Default Shift';

            // Status Badge
            const statusBadge = document.getElementById('modal-attendance-badge');
            if (statusBadge) {
                const status = (log.attendance_status || 'present').toLowerCase();
                statusBadge.innerText = status.includes('absent') && log.is_lwp ? '🔴 ABSENT' : status.replace('_', ' ');
                statusBadge.className = 'badge-premium-pill';
                if (status === 'present') {
                    statusBadge.style.background = '#DCFCE7';
                    statusBadge.style.color = '#15803D';
                } else if (status === 'absent' || status === 'lwp' || status.includes('absent')) {
                    statusBadge.style.background = '#FEE2E2';
                    statusBadge.style.color = '#B91C1C';
                } else if (status === 'half_day') {
                    statusBadge.style.background = '#FEF3C7';
                    statusBadge.style.color = '#B45309';
                } else {
                    statusBadge.style.background = '#F3F4F6';
                    statusBadge.style.color = '#374151';
                }
            }

            // Mode & Submitted Badge
            const modeEl = document.getElementById('modal-mode-badge');
            if (modeEl) modeEl.innerText = (log.work_mode || 'WFO').toUpperCase();

            const submittedEl = document.getElementById('modal-submitted-badge');
            if (submittedEl) submittedEl.innerText = log.submitted_time || '-';

            // Today's Work Status Badge
            const statusContainer = document.getElementById('modal-today-status-badge');
            const rawStatus = (log.today_work_status || log.status || 'completed').toLowerCase();
            if (statusContainer) {
                let statusBg = '#EFF6FF';
                let statusColor = '#1D4ED8';
                let statusBorder = '#3B82F6';
                let statusIcon = 'fas fa-spinner fa-spin';
                let statusLabel = 'In Progress';

                if (rawStatus === 'completed' || rawStatus === 'done') {
                    statusBg = '#DCFCE7';
                    statusColor = '#15803D';
                    statusBorder = '#16A34A';
                    statusIcon = 'fas fa-check-circle';
                    statusLabel = 'Completed';
                } else if (rawStatus === 'testing') {
                    statusBg = '#FDF4FF';
                    statusColor = '#7E22CE';
                    statusBorder = '#A855F7';
                    statusIcon = 'fas fa-vial';
                    statusLabel = 'Testing';
                } else if (rawStatus === 'blocked') {
                    statusBg = '#FEF2F2';
                    statusColor = '#B91C1C';
                    statusBorder = '#EF4444';
                    statusIcon = 'fas fa-ban';
                    statusLabel = 'Blocked';
                } else {
                    statusLabel = rawStatus.replace('_', ' ').toUpperCase();
                }

                statusContainer.innerHTML = `
                    <span style="background: ${statusBg}; color: ${statusColor}; border: 1.5px solid ${statusBorder}; border-radius: 20px; padding: 3px 11px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="${statusIcon}"></i> ${statusLabel}
                    </span>
                `;
            }

            // Calculate projects & tasks statistics
            const projectsList = Array.isArray(log.projects) ? log.projects : [];
            let totalTasks = 0;
            let completedTasks = 0;

            if (projectsList.length > 0) {
                projectsList.forEach(p => {
                    const tasks = Array.isArray(p.tasks) ? p.tasks : [];
                    tasks.forEach(t => {
                        totalTasks++;
                        const isDone = typeof t === 'object' && t !== null 
                            ? (t.is_completed === true || t.is_completed === 1 || t.is_completed === '1' || t.completed === true || t.completed === 1 || t.completed === '1')
                            : true;
                        if (isDone) completedTasks++;
                    });
                });
            } else {
                const reqs = Array.isArray(log.requirements) ? log.requirements : (Array.isArray(log.tasks) ? log.tasks : []);
                totalTasks = reqs.length;
                reqs.forEach(t => {
                    const isDone = typeof t === 'object' && t !== null
                        ? (t.done === true || t.done === 1 || t.is_completed === true || t.is_completed === 1 || t.completed === true || t.completed === 1)
                        : true;
                    if (isDone) completedTasks++;
                });
            }

            const pendingTasks = totalTasks - completedTasks;
            const statProjectsEl = document.getElementById('modal-stat-projects');
            if (statProjectsEl) {
                statProjectsEl.innerText = `${projectsList.length || 1} ${projectsList.length === 1 ? 'Project' : 'Projects'}`;
            }
            const statTasksEl = document.getElementById('modal-stat-tasks');
            if (statTasksEl) {
                statTasksEl.innerText = `${totalTasks} ${totalTasks === 1 ? 'Task' : 'Tasks'} Total`;
            }
            const statCompletedEl = document.getElementById('modal-stat-completed');
            if (statCompletedEl) {
                statCompletedEl.innerText = `${completedTasks} Completed${pendingTasks > 0 ? ', ' + pendingTasks + ' Pending' : ''}`;
            }

            // Section toggling: Mode A (Project-based) vs Mode B (Legacy)
            const secProjects = document.getElementById('modal-section-projects');
            const secLegacy = document.getElementById('modal-section-legacy');
            const titleEl = document.getElementById('modal-report-title');

            if (projectsList.length > 0) {
                // Show Mode A (Projects), Hide Mode B (Legacy)
                if (secProjects) secProjects.classList.remove('d-none');
                if (secLegacy) secLegacy.classList.add('d-none');

                // Update Project mode Title
                if (titleEl) {
                    if (projectsList.length > 1) {
                        titleEl.innerText = `Daily Work Report (${projectsList.length} Projects)`;
                    } else {
                        titleEl.innerText = `${projectsList[0].project_name || projectsList[0].name || 'Project Work'}`;
                    }
                }

                // Render Project Cards (ONCE ONLY)
                const projCardsContainer = document.getElementById('modal-projects-cards-list');
                if (projCardsContainer) {
                    projCardsContainer.innerHTML = '';
                    projectsList.forEach(proj => {
                        const pName = proj.project_name || proj.name || 'Project';
                        const pTasks = Array.isArray(proj.tasks) ? proj.tasks : [];
                        let pDoneCount = 0;
                        let tasksHtml = '';

                        pTasks.forEach(task => {
                            let isDone = true;
                            let taskName = 'Task';

                            if (typeof task === 'string') {
                                taskName = task;
                                isDone = true;
                            } else if (typeof task === 'object' && task !== null) {
                                taskName = task.task_name || task.task || task.description || task.title || 'Task';
                                if (task.is_completed !== undefined) {
                                    isDone = (task.is_completed === true || task.is_completed === 1 || task.is_completed === '1' || task.is_completed === 'true');
                                } else if (task.completed !== undefined) {
                                    isDone = (task.completed === true || task.completed === 1 || task.completed === '1' || task.completed === 'true');
                                } else if (task.done !== undefined) {
                                    isDone = (task.done === true || task.done === 1 || task.done === '1' || task.done === 'true');
                                } else {
                                    const st = (task.status || 'completed').toLowerCase();
                                    isDone = (st === 'completed' || st === 'done' || st === 'success');
                                }
                            }

                            if (isDone) pDoneCount++;

                            tasksHtml += `
                                <div class="d-flex justify-content-between align-items-center p-2.5 rounded" style="border-radius: 9px; background: ${isDone ? '#F0FDF4' : '#FFFBEB'}; border: 1px solid ${isDone ? '#BBF7D0' : '#FDE68A'}; gap: 14px;">
                                    <div style="font-size: 12.5px; font-weight: 600; color: ${isDone ? '#15803D' : '#92400E'}; display: flex; align-items: flex-start; gap: 10px; line-height: 1.5; flex: 1;">
                                        <i class="${isDone ? 'fas fa-check-circle text-success' : 'far fa-clock text-warning'}" style="font-size: 14px; margin-top: 3px; flex-shrink: 0;"></i>
                                        <span style="word-break: break-word;">${escapeHtml(taskName)}</span>
                                    </div>
                                    <span style="font-size: 9px; font-weight: 800; padding: 3px 9px; border-radius: 50px; flex-shrink: 0; letter-spacing: 0.04em; ${isDone ? 'background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0;' : 'background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A;'}">
                                        ${isDone ? 'COMPLETED' : 'PENDING'}
                                    </span>
                                </div>
                            `;
                        });

                        const pCard = document.createElement('div');
                        pCard.className = 'project-module-card p-3.5 rounded-lg border bg-white shadow-xs';
                        pCard.style.cssText = 'border-radius: 14px; border: 1.5px solid #E2E8F0 !important; border-left: 4.5px solid var(--orb-primary, #4B00E8) !important;';
                        pCard.innerHTML = `
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: #F1F5F9 !important;">
                                <div class="d-flex align-items-center" style="gap: 10px;">
                                    <span style="width: 32px; height: 32px; border-radius: 8px; background: #EEF2FF; color: var(--orb-primary, #4B00E8); display: inline-flex; align-items: center; justify-content: center; font-size: 14px;">
                                        <i class="fas fa-folder-open"></i>
                                    </span>
                                    <strong style="font-size: 14.5px; color: #0F172A; font-weight: 800;">${escapeHtml(pName)}</strong>
                                </div>
                                <span class="badge border" style="background: #F8FAFC; color: #475467; font-size: 11.5px; font-weight: 700; border-radius: 6px; padding: 4px 10px; border-color: #E2E8F0 !important;">
                                    ${pTasks.length} ${pTasks.length === 1 ? 'task' : 'tasks'} &bull; <span class="${pDoneCount === pTasks.length ? 'text-success' : 'text-primary'} font-weight-bold">${pDoneCount}/${pTasks.length} Done</span>
                                </span>
                            </div>
                            <div class="d-flex flex-column" style="gap: 8px;">
                                ${tasksHtml || '<div class="text-muted small italic p-2">No tasks listed under this project</div>'}
                            </div>
                        `;
                        projCardsContainer.appendChild(pCard);
                    });
                }
            } else {
                // Show Mode B (Legacy), Hide Mode A (Projects)
                if (secProjects) secProjects.classList.add('d-none');
                if (secLegacy) secLegacy.classList.remove('d-none');

                // Legacy Title
                const legTitleEl = document.getElementById('modal-legacy-title');
                if (legTitleEl) legTitleEl.innerText = log.title || 'Work Report Submitted';

                // Legacy Status Pill
                const legStatusBadge = document.getElementById('modal-legacy-status-badge');
                if (legStatusBadge && statusContainer) {
                    legStatusBadge.innerHTML = statusContainer.innerHTML;
                }

                // Legacy Description
                const legDescEl = document.getElementById('modal-legacy-desc-content');
                let descText = log.description || log.work_summary || '';
                if (descText.includes('☑') || descText.includes('☐')) {
                    const lines = descText.split('\n');
                    const cleanLines = lines.filter(l => !l.includes('☑') && !l.includes('☐') && !l.trim().startsWith('Project:'));
                    descText = cleanLines.join(' ').trim();
                }
                if (legDescEl) legDescEl.innerText = descText || 'Work report submitted.';

                // Legacy Tasks Checklist
                const legTasksList = document.getElementById('modal-legacy-tasks-list');
                if (legTasksList) {
                    legTasksList.innerHTML = '';
                    const items = log.requirements || log.tasks || [];
                    if (Array.isArray(items) && items.length > 0) {
                        items.forEach(item => {
                            let taskText = '';
                            let isCompleted = true;

                            if (typeof item === 'string') {
                                taskText = item;
                                isCompleted = true;
                            } else if (typeof item === 'object' && item !== null) {
                                taskText = item.text || item.task || item.task_name || item.title || item.description || 'Task';
                                if (item.done !== undefined) {
                                    isCompleted = (item.done === true || item.done === 'true' || item.done === 1 || item.done === '1');
                                } else if (item.is_completed !== undefined) {
                                    isCompleted = (item.is_completed === true || item.is_completed === 'true' || item.is_completed === 1 || item.is_completed === '1');
                                } else if (item.completed !== undefined) {
                                    isCompleted = (item.completed === true || item.completed === 'true' || item.completed === 1 || item.completed === '1');
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
                            row.className = 'd-flex justify-content-between align-items-center gap-3 mb-1.5';

                            row.innerHTML = `
                                <div style="font-size: 12.5px; font-weight: 700; color: ${isCompleted ? '#166534' : '#475467'}; display: flex; align-items: center;">
                                    <i class="${isCompleted ? 'fas fa-check-square text-success' : 'far fa-square text-warning'} mr-2" style="font-size: 13px;"></i>
                                    <span>${escapeHtml(taskText)}</span>
                                </div>
                                <span style="font-size: 8.5px; font-weight: 800; padding: 2px 6px; border-radius: 50px; ${isCompleted ? 'background: #DCFCE7; color: #166534;' : 'background: #F3F4F6; color: #6B7280;'}">
                                    ${isCompleted ? 'COMPLETED' : 'PENDING'}
                                </span>
                            `;
                            legTasksList.appendChild(row);
                        });
                    } else {
                        legTasksList.innerHTML = `
                            <div class="text-center py-2.5 border rounded-lg bg-white text-muted" style="border-radius: 10px; font-style: italic; font-size: 11.5px;">
                                <i class="fas fa-info-circle mr-1"></i> No checklist items.
                            </div>
                        `;
                    }
                }
            }

            // Issues & Blockers (Array / String / Null safety)
            const issuesArea = document.getElementById('modal-issues-area');
            if (issuesArea) {
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
                            <i class="fas fa-exclamation-circle mr-1.5"></i> <strong>Blocker:</strong> ${escapeHtml(issuesText)}
                        </div>
                    `;
                } else {
                    issuesArea.innerHTML = `
                        <div style="background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; border-radius: 10px; padding: 8px; font-size: 11.5px; font-weight: 800; text-align: center; border: 1px dashed #166534;">
                            <i class="fas fa-check-circle mr-2"></i> NO ISSUES REPORTED
                        </div>
                    `;
                }
            }

            // Developer Notes
            const notesWrapper = document.getElementById('modal-notes-wrapper');
            const notesTextEl = document.getElementById('modal-notes-text');
            if (notesWrapper && notesTextEl) {
                if (log.notes && log.notes !== 'null' && String(log.notes).trim().length > 0) {
                    notesWrapper.classList.remove('d-none');
                    notesTextEl.innerText = log.notes;
                } else {
                    notesWrapper.classList.add('d-none');
                }
            }

            // Save active log references for printing
            activeModalWorkLogId = log.id || log.work_log_id || null;
            activeModalWorkLogData = log;

            const printBtn = document.getElementById('modal-print-btn');
            if (printBtn) {
                printBtn.style.display = activeModalWorkLogId ? 'inline-flex' : 'none';
            }

            // Show modal reliably across Bootstrap versions
            showSharedModal();

        } catch (err) {
            console.error('[parseAndOpenWorkReport Error]', err);
            showModalFallback("Unable to load structured report.");
        }
    }

    let activeModalWorkLogId = null;
    let activeModalWorkLogData = null;

    function printModalWorkReport() {
        if (activeModalWorkLogId) {
            const printUrl = "{{ route('hrms.attendance.work-reports.single.print', ['id' => ':id']) }}".replace(':id', activeModalWorkLogId);
            window.open(printUrl, '_blank');
        }
    }

    function showModalFallback(message) {
        activeModalWorkLogId = null;
        activeModalWorkLogData = null;
        const printBtn = document.getElementById('modal-print-btn');
        if (printBtn) {
            printBtn.style.display = 'none';
        }

        const nameEl = document.getElementById('modal-emp-name');
        if (nameEl) nameEl.innerText = 'Work Report';

        const avatarBox = document.getElementById('modal-emp-avatar');
        if (avatarBox) {
            avatarBox.innerHTML = '';
            avatarBox.innerText = 'R';
        }

        const codeEl = document.getElementById('modal-emp-code');
        if (codeEl) codeEl.innerText = '-';

        const deptEl = document.getElementById('modal-emp-dept');
        if (deptEl) deptEl.innerText = '-';

        const desigEl = document.getElementById('modal-emp-desig');
        if (desigEl) desigEl.innerText = '-';

        const dateEl = document.getElementById('modal-work-date');
        if (dateEl) dateEl.innerText = '-';

        const shiftEl = document.getElementById('modal-shift-name');
        if (shiftEl) shiftEl.innerText = '-';

        const fallbackEl = document.getElementById('modal-fallback-alert');
        if (fallbackEl) fallbackEl.classList.remove('d-none');

        const fallbackMsgEl = document.getElementById('fallback-message');
        if (fallbackMsgEl) fallbackMsgEl.innerText = message;

        const containerEl = document.getElementById('modal-content-container');
        if (containerEl) containerEl.classList.add('d-none');

        showSharedModal();
    }

    function showSharedModal() {
        const modalEl = document.getElementById('sharedWorkReportModal');
        if (!modalEl) return;

        if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
            $('#sharedWorkReportModal').modal('show');
        } else if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
            const inst = bootstrap.Modal.getOrCreateInstance ? bootstrap.Modal.getOrCreateInstance(modalEl) : new bootstrap.Modal(modalEl);
            inst.show();
        }
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