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
    // View Switcher Function
    function switchWorkReportView(mode) {
        if (mode === 'table') {
            $('#tableViewArea').show();
            $('#cardsViewArea').hide();
            $('#btnTableView').addClass('active');
            $('#btnCardsView').removeClass('active');
        } else {
            $('#tableViewArea').hide();
            $('#cardsViewArea').show();
            $('#btnCardsView').addClass('active');
            $('#btnTableView').removeClass('active');
        }
    }

    // Open Slide-Out Timeline Drawer
    function openEmployeeTimelineDrawer(empSummary) {
        $('#drawerEmpName').text(empSummary.user_name);
        $('#drawerEmpMeta').text(`${empSummary.employee_code} • ${empSummary.department}`);
        $('#drawerTotalCount').text(`${empSummary.total_reports} Logs`);

        if (empSummary.passport_photo_url) {
            $('#drawerAvatar').html(`<img src="${empSummary.passport_photo_url}" style="width:100%;height:100%;object-fit:cover;">`);
        } else {
            $('#drawerAvatar').html(`<span>${empSummary.employee_initial}</span>`);
        }

        let html = '';
        if (empSummary.logs && empSummary.logs.length > 0) {
            empSummary.logs.forEach(function(log) {
                const workDate = log.work_date ? new Date(log.work_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                const gross = (log.attendance && log.attendance.gross_duration) ? log.attendance.gross_duration : '-';
                const mode = (log.attendance && log.attendance.work_mode) ? log.attendance.work_mode.toUpperCase() : 'WFO';
                const modeBadge = mode === 'WFH' ? 'badge-wfh' : 'badge-wfo';
                const summaryText = log.work_summary || 'Work report submitted.';

                html += `
                    <div class="timeline-item">
                        <div class="timeline-card">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="font-weight-bold text-dark" style="font-size:14px;"><i class="fas fa-calendar-day text-primary mr-1"></i> ${workDate}</span>
                                <span class="badge-premium-pill ${modeBadge}">${mode}</span>
                            </div>
                            <div class="text-muted font-weight-bold mb-2" style="font-size:12px;">
                                <i class="fas fa-clock mr-1"></i> Gross Work: <span class="text-dark">${gross}</span>
                            </div>
                            <div class="p-2 bg-light rounded text-dark font-weight-bold mb-3" style="font-size:12.5px;">
                                ${summaryText}
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            html = `<div class="text-center py-4 text-muted font-weight-bold">No daily work logs found for this employee.</div>`;
        }

        $('#drawerTimelineList').html(html);
        $('#drawerOverlay').addClass('active');
        $('#employeeTimelineDrawer').addClass('active');
    }

    function closeEmployeeTimelineDrawer() {
        $('#drawerOverlay').removeClass('active');
        $('#employeeTimelineDrawer').removeClass('active');
    }

    $(document).ready(function() {
        // Initialize Select2 on Filter dropdowns
        if (window.jQuery && $.fn.select2) {
            $('.select2-searchable').select2({
                width: '100%',
                dropdownAutoWidth: true
            });
        }

        // Global length select initialization
        if (typeof window.initGlobalDataTableLengthSelects === 'function') {
            window.initGlobalDataTableLengthSelects();
        }

        // Toggle Custom Date Fields based on Month Filter
        $('#monthFilterSelect').on('change select2:select', function() {
            var val = $(this).val();
            if (val === 'custom') {
                $('#customFromWrap').slideDown(150);
                $('#customToWrap').slideDown(150);
                $('#filterDate').val('');
            } else {
                $('#customFromWrap').slideUp(150);
                $('#customToWrap').slideUp(150);
                $('#fromDateInput, #filterFromDate').val('');
                $('#toDateInput, #filterToDate').val('');
            }
        });

        function extractYearMonth(dateStr) {
            if (!dateStr) return null;
            dateStr = dateStr.trim();
            var parts = dateStr.split(/[-/]/);
            if (parts.length === 3) {
                if (parts[0].length === 4) {
                    // YYYY-MM-DD
                    return parts[0] + '-' + parts[1].padStart(2, '0');
                } else if (parts[2].length === 4) {
                    // DD-MM-YYYY
                    return parts[2] + '-' + parts[1].padStart(2, '0');
                }
            }
            return null;
        }

        // Mutual reset and auto-sync Month when single Date is chosen
        $('#filterDate').on('change input', function() {
            var dateVal = $(this).val();
            if (dateVal) {
                $('#filterFromDate, #filterToDate, #fromDateInput, #toDateInput').val('');
                $('#customFromWrap, #customToWrap').slideUp(150);
                
                var ym = extractYearMonth(dateVal);
                if (ym) {
                    if ($('#monthFilterSelect option[value="' + ym + '"]').length > 0) {
                        $('#monthFilterSelect').val(ym).trigger('change.select2');
                    }
                }
            }
        });

        $('#filterFromDate, #filterToDate').on('change input', function() {
            if ($(this).val()) {
                $('#filterDate').val('');
            }
        });

        // Instant Server-Side Records Per Page Change (Matching standard URL redirect)
        $('#recordsPerPageSelect').on('change', function() {
            const newPerPage = $(this).val();
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', newPerPage);
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        });

        // Initialize DataTable with Export Buttons for workReportsTable
        if (window.jQuery && $.fn.DataTable) {
            $.fn.dataTable.ext.errMode = 'none';

            if ($.fn.DataTable.isDataTable('#workReportsTable')) {
                $('#workReportsTable').DataTable().destroy();
            }

            const hasEmptyRow = $('#workReportsTable tbody tr td[colspan]').length > 0;
            const dataRowCount = $('#workReportsTable tbody tr').length;

            if (!hasEmptyRow && dataRowCount > 0) {
                const dt = $('#workReportsTable').DataTable({
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
                            title: 'Daily Work Reports',
                            exportOptions: {
                                columns: ':not(.no-export)',
                                format: {
                                    body: function(data, row, column, node) {
                                        if (node && node.hasAttribute('data-export')) {
                                            return node.getAttribute('data-export');
                                        }
                                        return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                    }
                                }
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            className: 'buttons-excel d-none',
                            title: 'Daily Work Reports',
                            sheetName: 'Work Reports',
                            exportOptions: {
                                columns: ':not(.no-export)',
                                format: {
                                    body: function(data, row, column, node) {
                                        if (node && node.hasAttribute('data-export')) {
                                            return node.getAttribute('data-export');
                                        }
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
                            title: 'Daily Work Reports',
                            exportOptions: {
                                columns: ':not(.no-export)',
                                format: {
                                    body: function(data, row, column, node) {
                                        if (node && node.hasAttribute('data-export')) {
                                            return node.getAttribute('data-export');
                                        }
                                        return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                    }
                                }
                            }
                        },
                        {
                            extend: 'print',
                            className: 'buttons-print d-none',
                            title: 'Daily Work Reports',
                            exportOptions: {
                                columns: ':not(.no-export)',
                                format: {
                                    body: function(data, row, column, node) {
                                        if (node && node.hasAttribute('data-export')) {
                                            return node.getAttribute('data-export');
                                        }
                                        return typeof data === 'string' ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : data;
                                    }
                                }
                            }
                        }
                    ]
                });

                // Attach export buttons trigger
                $('#workReportsExportButtons [data-export-type]').on('click', function(e) {
                    e.preventDefault();
                    const exportType = $(this).data('export-type');
                    if (exportType === 'excel') dt.button('.buttons-excel').trigger();
                    else if (exportType === 'csv') dt.button('.buttons-csv').trigger();
                    else if (exportType === 'pdf') dt.button('.buttons-pdf').trigger();
                    else if (exportType === 'print') dt.button('.buttons-print').trigger();
                });
            }
        }
    });
</script>

