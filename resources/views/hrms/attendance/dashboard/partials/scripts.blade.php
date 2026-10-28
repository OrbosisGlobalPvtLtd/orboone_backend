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
        // Clean formatter for DataTable exports
        const cleanExportData = function(data, row, column, node) {
            if (!node) return (data || '').trim();
            const $cell = $(node);

            // 1. Employee cell: Name (Code)
            const $emp = $cell.find('.att-emp');
            if ($emp.length) {
                const name = $cell.find('.att-emp-name').text().trim();
                const code = $cell.find('.att-emp-code').text().trim();
                return code && code !== 'N/A' ? name + ' (' + code + ')' : name;
            }

            // 2. Department & Designation or Multi-line cells
            const $bold = $cell.find('.font-weight-bold, .att-strong');
            const $muted = $cell.find('.text-muted, .att-small:not(.att-strong)');
            if ($bold.length && $muted.length) {
                const bText = $bold.first().text().trim();
                const mText = $muted.first().text().trim();
                if (bText && mText && bText !== mText) {
                    return bText + ' - ' + mText;
                }
            }

            // 3. Status Badges / Flags
            const $badges = $cell.find('.att-badge, .badge, .mode-badge, .flag-badge');
            if ($badges.length) {
                const bList = [];
                $badges.each(function() {
                    const txt = $(this).text().trim();
                    if (txt && !bList.includes(txt)) bList.push(txt);
                });
                if (bList.length) return bList.join(', ');
            }

            // 4. Default: remove icons, avatars, buttons, line breaks
            const $clone = $cell.clone();
            $clone.find('.hrms-emp-avatar, .att-avatar, .leave-avatar, i, svg, button, .dropdown, script').remove();
            return $clone.text().replace(/\s+/g, ' ').trim();
        };

        // Custom Tabular Exporters for CSV, Excel, PDF & Print with separated columns
        function buildAttendanceExportData(data) {
            data.header = ['S.No', 'Employee Code', 'Employee Name', 'Department', 'Work Mode', 'Shift', 'Punch In', 'Punch Out', 'Target Out', 'Gross Hours', 'Net Hours', 'Status', 'Flags'];
            const rows = [];
            $('#attendanceDataTable tbody tr').each(function(i) {
                const $tr = $(this);
                if ($tr.find('td').length < 12 || $tr.find('.dataTables_empty').length) return;
                const sNo = $tr.find('td:eq(0)').text().trim() || (i + 1).toString();
                const empCode = $tr.attr('data-emp-code') || $tr.find('.att-emp-code').text().trim() || '-';
                const empName = $tr.attr('data-emp-name') || $tr.find('.att-emp-name').text().trim() || '-';
                const dept = $tr.attr('data-department') || $tr.find('td:eq(2)').text().trim() || '-';
                const mode = $tr.attr('data-mode') || $tr.find('td:eq(3)').text().trim() || '-';
                const shift = $tr.attr('data-shift') || $tr.find('td:eq(4)').text().trim() || '-';
                const punchIn = $tr.attr('data-punch-in') || $tr.find('td:eq(5)').text().trim() || '-';
                const punchOut = $tr.attr('data-punch-out') || $tr.find('td:eq(6)').text().trim() || '-';
                const targetOut = $tr.attr('data-target-out') || $tr.find('td:eq(7)').text().trim() || '-';
                const gross = $tr.attr('data-gross') || $tr.find('td:eq(8)').text().trim() || '-';
                const net = $tr.attr('data-net') || $tr.find('td:eq(9)').text().trim() || '-';
                const status = $tr.attr('data-status') || $tr.find('td:eq(10) .att-badge, td:eq(10) .badge').text().trim() || '-';
                const flags = $tr.attr('data-flags') || $tr.find('td:eq(11)').text().replace(/\s+/g, ' ').trim() || 'Clear';

                rows.push([sNo, empCode, empName, dept, mode, shift, punchIn, punchOut, targetOut, gross, net, status, flags]);
            });
            data.body = rows;
        }

        function buildBlockedExportData(data) {
            data.header = ['S.No', 'Employee Code', 'Employee Name', 'Department', 'Designation', 'Date', 'Block Reason', 'Regularization Request', 'Status'];
            const rows = [];
            $('#blockedAttendanceTable tbody tr').each(function(i) {
                const $tr = $(this);
                if ($tr.find('td').length < 7 || $tr.find('.dataTables_empty').length) return;
                const sNo = $tr.find('td:eq(0)').text().trim() || (i + 1).toString();
                const empCode = $tr.attr('data-emp-code') || $tr.find('.att-emp-code').text().trim() || '-';
                const empName = $tr.attr('data-emp-name') || $tr.find('.att-emp-name').text().trim() || '-';
                const dept = $tr.attr('data-department') || $tr.find('td:eq(2) .font-weight-bold').text().trim() || '-';
                const desig = $tr.attr('data-designation') || $tr.find('td:eq(2) .text-muted').text().trim() || '-';
                const date = $tr.attr('data-date') || $tr.find('td:eq(3)').text().trim() || '-';
                const reason = $tr.attr('data-reason') || $tr.find('td:eq(4)').text().replace(/\s+/g, ' ').trim() || '-';
                const reg = $tr.attr('data-reg') || $tr.find('td:eq(5)').text().replace(/\s+/g, ' ').trim() || '-';
                const status = $tr.attr('data-status') || $tr.find('td:eq(6)').text().replace(/\s+/g, ' ').trim() || '-';

                rows.push([sNo, empCode, empName, dept, desig, date, reason, reg, status]);
            });
            data.body = rows;
        }

        function buildUnmarkedExportData(data) {
            data.header = ['S.No', 'Employee Code', 'Employee Name', 'Department', 'Designation', 'Email', 'Phone', "Today's Status"];
            const rows = [];
            $('#unmarkedAttendanceTable tbody tr').each(function(i) {
                const $tr = $(this);
                if ($tr.find('td').length < 5 || $tr.find('.dataTables_empty').length) return;
                const sNo = $tr.find('td:eq(0)').text().trim() || (i + 1).toString();
                const empCode = $tr.attr('data-emp-code') || $tr.find('.att-emp-code').text().trim() || '-';
                const empName = $tr.attr('data-emp-name') || $tr.find('.att-emp-name').text().trim() || '-';
                const dept = $tr.attr('data-department') || $tr.find('td:eq(2) .font-weight-bold').text().trim() || '-';
                const desig = $tr.attr('data-designation') || $tr.find('td:eq(2) .text-muted').text().trim() || '-';
                const email = $tr.attr('data-email') || $tr.find('td:eq(3) .att-small').text().trim() || '-';
                const phone = $tr.attr('data-phone') || $tr.find('.att-phone').text().replace(/[^0-9+\s-]/g, '').trim() || '-';
                const status = $tr.attr('data-status') || $tr.find('td:eq(4) .att-badge, td:eq(4) .badge').text().trim() || 'Not Marked';

                rows.push([sNo, empCode, empName, dept, desig, email, phone, status]);
            });
            data.body = rows;
        }

        // Dedicated UTF-8 CSV Generator (Supports separated customized columns & Excel compatibility)
        function triggerCsvExport(buildDataFn, defaultFilename) {
            const data = {};
            buildDataFn(data);
            const filename = defaultFilename.endsWith('.csv') ? defaultFilename : (defaultFilename + '.csv');
            const csvRows = [];
            if (data.header && data.header.length) {
                csvRows.push(data.header.map(function(h) {
                    return '"' + (h || '').toString().replace(/"/g, '""') + '"';
                }).join(','));
            }
            if (data.body && data.body.length) {
                data.body.forEach(function(row) {
                    csvRows.push(row.map(function(cell) {
                        return '"' + (cell || '').toString().replace(/"/g, '""') + '"';
                    }).join(','));
                });
            }
            const csvContent = '\uFEFF' + csvRows.join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        // Render custom length dropdown (10, 25, 50, 100 entries) connected to server pagination
        function renderLengthSelector(containerSelector, paramName, pageParamName, currentValue) {
            const options = [10, 25, 50, 100];
            const currentNum = parseInt(currentValue, 10) || 10;
            if (!options.includes(currentNum)) {
                options.push(currentNum);
                options.sort(function(a, b) { return a - b; });
            }
            let optsHtml = '';
            options.forEach(function(opt) {
                optsHtml += '<option value="' + opt + '"' + (opt === currentNum ? ' selected' : '') + '>' + opt + '</option>';
            });

            const $lengthSelect = $(
                '<div class="dataTables_length d-flex align-items-center">' +
                    '<label class="eo-entries-label mb-0 d-flex align-items-center font-weight-bold text-muted" style="font-size:13px; gap:8px;">' +
                        '<span>Show</span>' +
                        '<select class="table-per-page-select orb-per-page-select" ' +
                                'data-param="' + paramName + '" ' +
                                'data-page-param="' + pageParamName + '" style="width: 75px;">' +
                            optsHtml +
                        '</select>' +
                        '<span>entries</span>' +
                    '</label>' +
                '</div>'
            );
            $(containerSelector).empty().append($lengthSelect);

            if ($.fn.select2) {
                const $select = $lengthSelect.find('select');
                $select.select2({
                    minimumResultsForSearch: Infinity,
                    width: '75px',
                    dropdownCssClass: 'select2-dropdown-per-page',
                    containerCssClass: 'select2-container--per-page'
                });
            }
        }

        // Global change event handler for pagination per-page selection
        $(document).on('change', '.orb-per-page-select', function() {
            const param = $(this).data('param') || 'per_page';
            const pageParam = $(this).data('page-param') || 'page';
            const val = $(this).val();
            const url = new URL(window.location.href);
            url.searchParams.set(param, val);
            if (param === 'attendance_per_page') {
                url.searchParams.set('per_page', val);
            }
            url.searchParams.delete(pageParam);
            if (pageParam === 'attendance_page') {
                url.searchParams.delete('page');
            }
            window.location.href = url.toString();
        });

        const todayTable = $('#attendanceDataTable').DataTable({
            paging: false,
            info: false,
            ordering: true,
            responsive: false,
            autoWidth: false,
            scrollX: false,
            searching: true,
            columnDefs: [
                { targets: 0, width: "50px", className: "text-center" },
                { targets: 12, orderable: false, className: "text-right" }
            ],
            dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                    className: 'btn btn-sm btn-export-csv',
                    title: 'Today Attendance Report',
                    action: function(e, dt, button, config) {
                        triggerCsvExport(buildAttendanceExportData, 'Today Attendance Report.csv');
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'btn btn-sm btn-export-excel',
                    title: 'Today Attendance Report',
                    customizeData: function(data) {
                        buildAttendanceExportData(data);
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                    className: 'btn btn-sm btn-export-pdf',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Today Attendance Report',
                    customize: function(doc) {
                        const data = {};
                        buildAttendanceExportData(data);
                        doc.content[1].table.headerRows = 1;
                        const body = [];
                        body.push(data.header.map(function(h) { return { text: h, bold: true, fillColor: '#F1F5F9', fontSize: 7 }; }));
                        data.body.forEach(function(row, i) {
                            body.push(row.map(function(cell) { return { text: cell, fontSize: 6.5, fillColor: i % 2 === 0 ? '#FFFFFF' : '#F8FAFC' }; }));
                        });
                        doc.content[1].table.body = body;
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    className: 'btn btn-sm btn-export-print',
                    title: 'Today Attendance Report',
                    customize: function(win) {
                        const data = {};
                        buildAttendanceExportData(data);
                        let tableHtml = '<table class="table table-bordered" style="width:100%; border-collapse:collapse; font-size:11px;"><thead><tr style="background:#F8FAFC;">';
                        data.header.forEach(function(h) { tableHtml += '<th style="padding:6px 8px; border:1px solid #CBD5E1;">' + h + '</th>'; });
                        tableHtml += '</tr></thead><tbody>';
                        data.body.forEach(function(row) {
                            tableHtml += '<tr>';
                            row.forEach(function(cell) { tableHtml += '<td style="padding:6px 8px; border:1px solid #E2E8F0;">' + cell + '</td>'; });
                            tableHtml += '</tr>';
                        });
                        tableHtml += '</tbody></table>';
                        $(win.document.body).find('table').replaceWith(tableHtml);
                    }
                }
            ],
            language: {
                emptyTable: 'No attendance records found for today.'
            },
            initComplete: function() {
                $('#attendanceDataTable_wrapper .dt-buttons').appendTo('#attendanceExportButtons');
            }
        });

        renderLengthSelector('#attendanceLengthBox', 'attendance_per_page', 'attendance_page', {{ $currentAttendancePerPage }});

        $('#todayAttendanceSearch').on('keyup', function(e) {
            if (e.key === 'Enter') {
                const url = new URL(window.location.href);
                if (this.value) {
                    url.searchParams.set('search', this.value);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('attendance_page');
                window.location.href = url.toString();
            } else {
                todayTable.search(this.value).draw();
            }
        });

        if ($('#blockedAttendanceTable').length) {
            $('#blockedAttendanceTable').DataTable({
                paging: false,
                info: false,
                ordering: true,
                autoWidth: false,
                scrollX: false,
                searching: false,
                columnDefs: [
                    { targets: 0, width: "50px", className: "text-center" },
                    { targets: 1, width: "22%" },
                    { targets: 2, width: "16%" },
                    { targets: 3, width: "12%" },
                    { targets: 4, width: "20%" },
                    { targets: 5, width: "10%", className: "text-center" },
                    { targets: 6, width: "10%", className: "text-center" },
                    { targets: 7, width: "10", orderable: false, className: "text-right" }
                ],
                dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
                buttons: [
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                        className: 'btn btn-sm btn-export-csv',
                        title: 'Punch-In Blocked Employees',
                        action: function(e, dt, button, config) {
                            triggerCsvExport(buildBlockedExportData, 'Punch-In Blocked Employees.csv');
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                        className: 'btn btn-sm btn-export-excel',
                        title: 'Punch-In Blocked Employees',
                        customizeData: function(data) {
                            buildBlockedExportData(data);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                        className: 'btn btn-sm btn-export-pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Punch-In Blocked Employees',
                        customize: function(doc) {
                            const data = {};
                            buildBlockedExportData(data);
                            doc.content[1].table.headerRows = 1;
                            const body = [];
                            body.push(data.header.map(function(h) { return { text: h, bold: true, fillColor: '#F1F5F9', fontSize: 7.5 }; }));
                            data.body.forEach(function(row, i) {
                                body.push(row.map(function(cell) { return { text: cell, fontSize: 7, fillColor: i % 2 === 0 ? '#FFFFFF' : '#F8FAFC' }; }));
                            });
                            doc.content[1].table.body = body;
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1"></i> Print',
                        className: 'btn btn-sm btn-export-print',
                        title: 'Punch-In Blocked Employees',
                        customize: function(win) {
                            const data = {};
                            buildBlockedExportData(data);
                            let tableHtml = '<table class="table table-bordered" style="width:100%; border-collapse:collapse; font-size:11px;"><thead><tr style="background:#F8FAFC;">';
                            data.header.forEach(function(h) { tableHtml += '<th style="padding:6px 8px; border:1px solid #CBD5E1;">' + h + '</th>'; });
                            tableHtml += '</tr></thead><tbody>';
                            data.body.forEach(function(row) {
                                tableHtml += '<tr>';
                                row.forEach(function(cell) { tableHtml += '<td style="padding:6px 8px; border:1px solid #E2E8F0;">' + cell + '</td>'; });
                                tableHtml += '</tr>';
                            });
                            tableHtml += '</tbody></table>';
                            $(win.document.body).find('table').replaceWith(tableHtml);
                        }
                    }
                ],
                language: {
                    emptyTable: 'No punch-in blocked employees today.'
                },
                initComplete: function() {
                    $('#blockedAttendanceTable_wrapper .dt-buttons').appendTo('#blockedExportButtons');
                }
            });

            renderLengthSelector('#blockedLengthBox', 'blocked_per_page', 'blocked_page', {{ $currentBlockedPerPage }});
        }

        if ($('#unmarkedAttendanceTable').length) {
            $('#unmarkedAttendanceTable').DataTable({
                paging: false,
                info: false,
                ordering: true,
                autoWidth: false,
                scrollX: false,
                searching: false,
                columnDefs: [
                    { targets: 0, width: "50px", className: "text-center" },
                    { targets: 1, width: "26%" },
                    { targets: 2, width: "22%" },
                    { targets: 3, width: "24%" },
                    { targets: 4, width: "16%", className: "text-center" },
                    { targets: 5, width: "12%", orderable: false, className: "text-right" }
                ],
                dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
                buttons: [
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                        className: 'btn btn-sm btn-export-csv',
                        title: 'Employees Not Marked Attendance Today',
                        action: function(e, dt, button, config) {
                            triggerCsvExport(buildUnmarkedExportData, 'Employees Not Marked Attendance Today.csv');
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                        className: 'btn btn-sm btn-export-excel',
                        title: 'Employees Not Marked Attendance Today',
                        customizeData: function(data) {
                            buildUnmarkedExportData(data);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                        className: 'btn btn-sm btn-export-pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Employees Not Marked Attendance Today',
                        customize: function(doc) {
                            const data = {};
                            buildUnmarkedExportData(data);
                            doc.content[1].table.headerRows = 1;
                            const body = [];
                            body.push(data.header.map(function(h) { return { text: h, bold: true, fillColor: '#F1F5F9', fontSize: 7.5 }; }));
                            data.body.forEach(function(row, i) {
                                body.push(row.map(function(cell) { return { text: cell, fontSize: 7, fillColor: i % 2 === 0 ? '#FFFFFF' : '#F8FAFC' }; }));
                            });
                            doc.content[1].table.body = body;
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1"></i> Print',
                        className: 'btn btn-sm btn-export-print',
                        title: 'Employees Not Marked Attendance Today',
                        customize: function(win) {
                            const data = {};
                            buildUnmarkedExportData(data);
                            let tableHtml = '<table class="table table-bordered" style="width:100%; border-collapse:collapse; font-size:11px;"><thead><tr style="background:#F8FAFC;">';
                            data.header.forEach(function(h) { tableHtml += '<th style="padding:6px 8px; border:1px solid #CBD5E1;">' + h + '</th>'; });
                            tableHtml += '</tr></thead><tbody>';
                            data.body.forEach(function(row) {
                                tableHtml += '<tr>';
                                row.forEach(function(cell) { tableHtml += '<td style="padding:6px 8px; border:1px solid #E2E8F0;">' + cell + '</td>'; });
                                tableHtml += '</tr>';
                            });
                            tableHtml += '</tbody></table>';
                            $(win.document.body).find('table').replaceWith(tableHtml);
                        }
                    }
                ],
                language: {
                    emptyTable: '<div class="text-center text-success py-3 font-weight-bold" style="font-size: 13px;"><i class="fas fa-check-circle mr-1"></i> Great news! All active employees have marked attendance for today.</div>'
                },
                initComplete: function() {
                    $('#unmarkedAttendanceTable_wrapper .dt-buttons').appendTo('#unmarkedExportButtons');
                }
            });

            renderLengthSelector('#unmarkedLengthBox', 'unmarked_per_page', 'unmarked_page', {{ $currentUnmarkedPerPage }});
        }
    });
</script>
