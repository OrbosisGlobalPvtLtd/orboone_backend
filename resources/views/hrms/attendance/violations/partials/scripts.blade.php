<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 on Filter dropdowns
        if (window.jQuery && $.fn.select2) {
            $('.select2-searchable').select2({
                width: '100%',
                dropdownAutoWidth: true
            });
        }

        const monthSelect = document.getElementById('monthFilterSelect');
        const fromWrap = document.getElementById('customFromWrap');
        const toWrap = document.getElementById('customToWrap');
        const fromInput = document.getElementById('fromDateInput');
        const toInput = document.getElementById('toDateInput');
        
        // Month select auto toggle for custom date range
        if (monthSelect) {
            $(monthSelect).on('change select2:select', function() {
                const val = $(this).val();
                if (val === 'custom') {
                    if (fromWrap) fromWrap.style.display = 'block';
                    if (toWrap) toWrap.style.display = 'block';
                } else {
                    if (fromWrap) fromWrap.style.display = 'none';
                    if (toWrap) toWrap.style.display = 'none';
                    if (fromInput) fromInput.value = '';
                    if (toInput) toInput.value = '';
                }
            });
        }

        // Per page selector handler (Server-side Pagination reload)
        $('#recordsPerPageSelect').on('change', function() {
            const newPerPage = $(this).val();
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', newPerPage);
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        });

        // Initialize DataTable with Export Buttons (Client-side DataTables paging disabled so Laravel Server-side Pagination works seamlessly)
        if (window.jQuery && $.fn.DataTable) {
            $.fn.dataTable.ext.errMode = 'none';

            if ($.fn.DataTable.isDataTable('#violationsDataTable')) {
                $('#violationsDataTable').DataTable().destroy();
            }

            const hasEmptyRow = $('#violationsDataTable tbody tr td[colspan]').length > 0;
            const dataRowCount = $('#violationsDataTable tbody tr').length;

            if (!hasEmptyRow && dataRowCount > 0) {
                const dt = $('#violationsDataTable').DataTable({
                    destroy: true,
                    paging: false,
                    searching: false,
                    info: false,
                    lengthChange: false,
                    responsive: false,
                    autoWidth: false,
                    order: [],
                    dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
                    buttons: [
                        {
                            extend: 'csvHtml5',
                            className: 'buttons-csv d-none',
                            title: 'Attendance Violations Audit Log',
                            exportOptions: {
                                columns: ':not(.no-export)',
                                format: {
                                    body: function(data, row, column, node) {
                                        return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                    }
                                }
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            className: 'buttons-excel d-none',
                            title: 'Attendance Violations Audit Log',
                            sheetName: 'Violations Audit',
                            exportOptions: {
                                columns: ':not(.no-export)',
                                format: {
                                    body: function(data, row, column, node) {
                                        return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                    }
                                }
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            className: 'buttons-pdf d-none',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            title: 'Attendance Violations Audit Log',
                            exportOptions: {
                                columns: ':not(.no-export)',
                                format: {
                                    body: function(data, row, column, node) {
                                        return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                    }
                                }
                            }
                        },
                        {
                            extend: 'print',
                            className: 'buttons-print d-none',
                            title: 'Attendance Violations Audit Log',
                            exportOptions: {
                                columns: ':not(.no-export)',
                                format: {
                                    body: function(data, row, column, node) {
                                        return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                    }
                                }
                            }
                        }
                    ]
                });
            }
        }

        // Side Drawer Logic
        const drawer = document.getElementById('auditDrawer');
        const backdrop = document.getElementById('auditDrawerBackdrop');
        const closeBtn = document.getElementById('closeDrawerBtn');
        const drawerBody = document.getElementById('auditDrawerBody');

        function openDrawer(employeeId) {
            backdrop.style.display = 'block';
            drawer.classList.add('open');
            drawerBody.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-circle-notch fa-spin fa-2x mb-3 text-primary"></i>
                    <div>Loading employee audit payload...</div>
                </div>
            `;

            const urlParams = new URLSearchParams(window.location.search);
            const month = urlParams.get('month') || '{{ $filters['selected_month'] ?? $filters['current_month'] }}';
            const fromDate = urlParams.get('from_date') || urlParams.get('from') || '';
            const toDate = urlParams.get('to_date') || urlParams.get('to') || '';

            let query = [];
            if (month) query.push(`month=${encodeURIComponent(month)}`);
            if (fromDate) query.push(`from_date=${encodeURIComponent(fromDate)}`);
            if (toDate) query.push(`to_date=${encodeURIComponent(toDate)}`);
            const qs = query.length > 0 ? '?' + query.join('&') : '';

            fetch(`{{ url('hrms/attendance/violations/employee-audit') }}/${employeeId}${qs}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        drawerBody.innerHTML = `<div class="alert alert-danger">${data.error || 'Failed to load employee audit.'}</div>`;
                        return;
                    }
                    renderDrawerContent(data);
                })
                .catch(err => {
                    console.error(err);
                    drawerBody.innerHTML = `<div class="alert alert-danger">Unable to load employee audit profile.</div>`;
                });
        }

        function closeDrawer() {
            drawer.classList.remove('open');
            backdrop.style.display = 'none';
        }

        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if (backdrop) backdrop.addEventListener('click', closeDrawer);

        $(document).on('click', '.js-open-emp-drawer', function() {
            const empId = $(this).attr('data-emp-id');
            if (empId) openDrawer(empId);
        });

        function renderDrawerContent(data) {
            const emp = data.employee;
            const pol = data.policy;
            const ctr = data.counters;

            let timelineHtml = '';
            if (data.timeline && data.timeline.length > 0) {
                data.timeline.forEach(item => {
                    timelineHtml += `
                    <div class="timeline-card">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-weight-bold text-dark" style="font-size: 13px;">${item.type}</span>
                            <span class="orb-badge ${item.badge_class}">${item.penalty_status}</span>
                        </div>
                        <div class="text-muted small mb-1"><i class="fas fa-calendar-day mr-1"></i> ${item.date} ${item.minutes > 0 ? '&bull; ' + item.minutes + ' mins' : ''}</div>
                        <div class="small text-dark font-weight-bold">${item.remarks}</div>
                    </div>
                `;
                });
            } else {
                timelineHtml = '<div class="text-muted small py-3 text-center bg-light rounded-lg border">No violation history records found for this period.</div>';
            }

            let html = `
                <!-- Employee Profile Card -->
                <div class="d-flex align-items-center p-3 mb-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px;">
                    ${emp.photo_url ? `<img src="${emp.photo_url}" style="width:48px; height:48px; border-radius:50%; object-fit:cover;">` : `<div class="d-inline-flex align-items-center justify-content-center font-weight-bold text-primary" style="width:48px; height:48px; border-radius:50%; background:var(--orb-soft); font-size:16px;">${emp.initials}</div>`}
                    <div class="ml-3">
                        <h6 class="font-weight-bold text-dark mb-0">${emp.name}</h6>
                        <div class="text-muted small"><code>${emp.code}</code> &bull; ${emp.designation}</div>
                        <div class="badge badge-light border mt-1">${emp.department}</div>
                    </div>
                </div>

                <!-- Active Cycle Counters -->
                <div class="row mx-0 mb-3">
                    <div class="col-6 pl-0 pr-1">
                        <div class="p-3 text-center" style="background: #FFF7ED; border: 1px solid #FFEDD5; border-radius: 14px;">
                            <div class="small font-weight-bold text-muted text-uppercase">Discipline Cycle</div>
                            <div class="h4 font-weight-900 text-warning mb-0 mt-1">${ctr.discipline}</div>
                        </div>
                    </div>
                    <div class="col-6 pr-0 pl-1">
                        <div class="p-3 text-center" style="background: #FEF2F2; border: 1px solid #FEE2E2; border-radius: 14px;">
                            <div class="small font-weight-bold text-muted text-uppercase">Missed Punch Cycle</div>
                            <div class="h4 font-weight-900 text-danger mb-0 mt-1">${ctr.missed_punch}</div>
                        </div>
                    </div>
                </div>

                <!-- Policy Rules -->
                <div class="p-3 mb-4" style="background: #F1F5F9; border-radius: 14px; font-size: 12px;">
                    <div class="font-weight-bold text-dark mb-1"><i class="fas fa-gavel mr-1 text-primary"></i> ${pol.name} (${pol.shift_type})</div>
                    <div class="text-muted">Shift Timing: <strong>${pol.shift_start} - ${pol.shift_end}</strong></div>
                    <div class="text-muted">Discipline Limit: <strong>${pol.discipline_limit}</strong> &bull; Missed Limit: <strong>${pol.missed_limit}</strong></div>
                </div>

                <!-- Timeline -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-history mr-1 text-primary"></i> Violation Audit Timeline</h6>
                    ${data.period_label ? `<span class="badge badge-primary badge-pill font-weight-bold" style="font-size: 11px;">${data.period_label}</span>` : ''}
                </div>
                <div class="timeline-list">${timelineHtml}</div>
            `;

            drawerBody.innerHTML = html;
        }

        function formatMinutesToHours(mins) {
            mins = parseInt(mins) || 0;
            if (mins <= 0) return '0m';
            const h = Math.floor(mins / 60);
            const m = mins % 60;
            if (h > 0 && m > 0) return `${h}h ${m}m`;
            if (h > 0) return `${h}h`;
            return `${m}m`;
        }

        // Attendance Audit Modal Logic
        $(document).on('click', '.js-open-att-modal', function() {
            const attId = $(this).attr('data-att-id');
            if (!attId) return;

            $('#attendanceAuditModal').modal('show');
            const modalBody = document.getElementById('attendanceAuditModalBody');
            modalBody.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-circle-notch fa-spin fa-2x mb-3 text-primary"></i>
                    <div class="font-weight-bold" style="font-size: 13px;">Loading attendance details...</div>
                </div>
            `;

            fetch(`{{ url('hrms/attendance/violations/attendance-audit') }}/${attId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        modalBody.innerHTML = `<div class="alert alert-danger border-0 rounded-lg shadow-sm">${data.message || 'Unable to load attendance details.'}</div>`;
                        return;
                    }

                    const att = data.attendance;
                    const initials = (att.employee_name || 'E').split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();

                    let statusBg = '#EFF6FF', statusColor = '#1D4ED8', statusBorder = '#DBEAFE', statusIcon = 'fa-check-circle';
                    const lowerStatus = (att.status || '').toLowerCase();
                    if (lowerStatus.includes('present')) {
                        statusBg = '#ECFDF5'; statusColor = '#047857'; statusBorder = '#A7F3D0'; statusIcon = 'fa-check-circle';
                    } else if (lowerStatus.includes('absent')) {
                        statusBg = '#FEF2F2'; statusColor = '#B91C1C'; statusBorder = '#FECACA'; statusIcon = 'fa-times-circle';
                    } else if (lowerStatus.includes('half')) {
                        statusBg = '#FFFBEB'; statusColor = '#B45309'; statusBorder = '#FDE68A'; statusIcon = 'fa-adjust';
                    } else if (lowerStatus.includes('leave') || lowerStatus.includes('lwp')) {
                        statusBg = '#F5F3FF'; statusColor = '#6D28D9'; statusBorder = '#DDD6FE'; statusIcon = 'fa-calendar-minus';
                    }

                    modalBody.innerHTML = `
                        <!-- Section 1: Employee & Attendance Date -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2 pb-1 border-bottom" style="border-color: #E2E8F0 !important;">
                                <i class="fas fa-user-circle text-primary mr-2" style="font-size: 11px;"></i>
                                <span class="font-weight-bold text-uppercase" style="font-size: 11px; color: var(--orb-primary, #4B00E8); letter-spacing: 0.5px;">Employee & Attendance Date</span>
                            </div>
                            <div class="row no-gutters" style="margin: 0 -4px;">
                                <!-- Employee -->
                                <div class="col-6 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Employee</span>
                                        <div class="font-weight-bold text-dark text-truncate" style="font-size: 12.5px;" title="${att.employee_name} (${att.employee_code})">
                                            ${att.employee_name} <span class="text-muted small">(${att.employee_code})</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date -->
                                <div class="col-6 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Attendance Date</span>
                                        <div class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                            <i class="far fa-calendar-alt text-muted mr-1"></i> ${att.date}
                                        </div>
                                    </div>
                                </div>

                                <!-- Department -->
                                <div class="col-6 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Department</span>
                                        <div class="font-weight-bold text-dark text-truncate" style="font-size: 12.5px;">
                                            ${att.department || 'N/A'}
                                        </div>
                                    </div>
                                </div>

                                <!-- Designation -->
                                <div class="col-6 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Designation</span>
                                        <div class="font-weight-bold text-dark text-truncate" style="font-size: 12.5px;">
                                            ${att.designation || 'Employee'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Punch & Shift Timings -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2 pb-1 border-bottom" style="border-color: #E2E8F0 !important;">
                                <i class="fas fa-clock text-primary mr-2" style="font-size: 11px;"></i>
                                <span class="font-weight-bold text-uppercase" style="font-size: 11px; color: var(--orb-primary, #4B00E8); letter-spacing: 0.5px;">Punch Records & Shift Target</span>
                            </div>
                            <div class="row no-gutters" style="margin: 0 -4px;">
                                <!-- Punch In -->
                                <div class="col-4 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 54px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Punch In</span>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="font-weight-bold text-dark" style="font-size: 13px;">${att.punch_in}</span>
                                            <span class="badge" style="background: ${att.punch_in !== 'N/A' ? '#ECFDF5' : '#F1F5F9'}; color: ${att.punch_in !== 'N/A' ? '#065F46' : '#64748B'}; font-size: 9.5px; font-weight: 800; padding: 2px 6px; border-radius: 4px;">
                                                ${att.punch_in !== 'N/A' ? 'IN' : 'N/A'}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Punch Out -->
                                <div class="col-4 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 54px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Punch Out</span>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="font-weight-bold text-dark" style="font-size: 13px;">${att.punch_out}</span>
                                            <span class="badge" style="background: ${att.punch_out !== 'N/A' ? '#EEF2FF' : '#FEF2F2'}; color: ${att.punch_out !== 'N/A' ? '#4338CA' : '#991B1B'}; font-size: 9.5px; font-weight: 800; padding: 2px 6px; border-radius: 4px;">
                                                ${att.punch_out !== 'N/A' ? 'OUT' : 'PENDING'}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Target Out -->
                                <div class="col-4 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 54px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Target Out</span>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="font-weight-bold text-dark" style="font-size: 13px;">${att.target_punch_out}</span>
                                            <span class="badge" style="background: #FDF2F8; color: #9D174D; font-size: 9.5px; font-weight: 800; padding: 2px 6px; border-radius: 4px;">
                                                TARGET
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Work Duration & Time Variance -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2 pb-1 border-bottom" style="border-color: #E2E8F0 !important;">
                                <i class="fas fa-chart-line text-primary mr-2" style="font-size: 11px;"></i>
                                <span class="font-weight-bold text-uppercase" style="font-size: 11px; color: var(--orb-primary, #4B00E8); letter-spacing: 0.5px;">Work Duration & Time Variance</span>
                            </div>
                            <div class="row no-gutters" style="margin: 0 -4px;">
                                <!-- Total Work -->
                                <div class="col-4 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 54px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Total Work</span>
                                        <div class="font-weight-bold text-primary mt-1" style="font-size: 13px;">
                                            ${att.total_work_minutes} mins <span class="text-muted font-weight-normal small">(${formatMinutesToHours(att.total_work_minutes)})</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Late In -->
                                <div class="col-4 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 54px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Late Login</span>
                                        <div class="font-weight-bold ${att.late_minutes > 0 ? 'text-danger' : 'text-dark'} mt-1" style="font-size: 13px;">
                                            ${att.late_minutes} mins
                                        </div>
                                    </div>
                                </div>

                                <!-- Early Out -->
                                <div class="col-4 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 54px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Early Logout</span>
                                        <div class="font-weight-bold ${att.early_out_minutes > 0 ? 'text-warning' : 'text-dark'} mt-1" style="font-size: 13px;">
                                            ${att.early_out_minutes} mins
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Policy & Attendance Status -->
                        <div>
                            <div class="d-flex align-items-center mb-2 pb-1 border-bottom" style="border-color: #E2E8F0 !important;">
                                <i class="fas fa-shield-alt text-primary mr-2" style="font-size: 11px;"></i>
                                <span class="font-weight-bold text-uppercase" style="font-size: 11px; color: var(--orb-primary, #4B00E8); letter-spacing: 0.5px;">Policy & Compliance Summary</span>
                            </div>
                            <div class="row no-gutters" style="margin: 0 -4px;">
                                <!-- Status -->
                                <div class="col-6 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Attendance Status</span>
                                        <div class="mt-1">
                                            <span class="badge" style="background: ${statusBg}; color: ${statusColor}; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 4px; border: 1px solid ${statusBorder};">
                                                <i class="fas ${statusIcon} mr-1"></i> ${att.status.toUpperCase()}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Policy Applied -->
                                <div class="col-6 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-height: 52px;">
                                        <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Policy Applied</span>
                                        <div class="font-weight-bold text-dark text-truncate mt-1" style="font-size: 12.5px;">
                                            <i class="fas fa-gavel text-muted mr-1"></i> ${att.policy_name}
                                        </div>
                                    </div>
                                </div>

                                <!-- Half Day Reason (if triggered) -->
                                ${att.is_half_day ? `
                                <div class="col-12 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #FFFBEB; border: 1px solid #FDE68A;">
                                        <span class="text-warning d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">Half Day Deduction Applied</span>
                                        <div class="text-dark font-weight-bold" style="font-size: 12px;">
                                            <i class="fas fa-exclamation-circle text-warning mr-1"></i> ${att.half_day_reason || 'Working hours below shift threshold.'}
                                        </div>
                                    </div>
                                </div>
                                ` : ''}

                                <!-- LWP Reason (if triggered) -->
                                ${att.is_lwp ? `
                                <div class="col-12 p-1">
                                    <div class="p-2 px-3 rounded" style="background: #FEF2F2; border: 1px solid #FECACA;">
                                        <span class="text-danger d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">LWP (Loss of Pay) Deducted</span>
                                        <div class="text-danger font-weight-bold" style="font-size: 12px;">
                                            <i class="fas fa-ban text-danger mr-1"></i> ${att.lwp_reason || 'Discipline violation cycle threshold reached.'}
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                })
                .catch(err => {
                    console.error(err);
                    modalBody.innerHTML = `<div class="alert alert-danger border-0 rounded-lg shadow-sm">Error fetching attendance audit details.</div>`;
                });
        });
    });
</script>
