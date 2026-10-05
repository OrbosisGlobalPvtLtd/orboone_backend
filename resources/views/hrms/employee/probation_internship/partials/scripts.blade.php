<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('filterSearch');
        const departmentFilter = document.getElementById('filterDepartment');
        const statusFilter = document.getElementById('filterStatus');
        const employmentTypeFilter = document.getElementById('filterEmploymentType');
        const resetBtn = document.getElementById('resetFilter');

        const urlParams = new URLSearchParams(window.location.search);
        const highlightEmployeeId = urlParams.get('highlight_employee') || urlParams.get('highlight');
        const stageFromNotification = (urlParams.get('stage') || '').toLowerCase();

        // Stored Filter State (executed ONLY when user clicks Search or Reset)
        let appliedFilters = {
            search: '',
            department: '',
            status: '',
            stage: ''
        };

        // Helper for CSV/Excel export structure
        function buildProbationExportData(data) {
            data.header = [
                'S.No',
                'Employee Code',
                'Employee Name',
                'Department',
                'Designation',
                'Stage',
                'Start Date',
                'End Date',
                'Status',
                'Salary Type'
            ];

            const api = $('#probationInternshipTable').DataTable();
            const rowsData = api.rows({ search: 'applied' }).nodes().toArray();

            let newBody = [];
            for (let i = 0; i < rowsData.length; i++) {
                const tr = $(rowsData[i]);
                const $cells = tr.children('td');
                if ($cells.length < 9) continue;

                const sNo = (i + 1).toString();
                const $nameCell = $cells.eq(1);
                const code = $nameCell.find('.eo-code-under').text().replace(/\s+/g, ' ').trim() || '-';
                const name = $nameCell.find('.eo-name').text().replace(/\s+/g, ' ').trim() || $nameCell.text().replace(/\s+/g, ' ').trim() || '-';
                
                const dept = $cells.eq(2).text().replace(/\s+/g, ' ').trim() || '-';
                const desig = $cells.eq(3).text().replace(/\s+/g, ' ').trim() || '-';
                const stage = $cells.eq(4).text().replace(/\s+/g, ' ').trim() || '-';
                const start = $cells.eq(5).text().replace(/\s+/g, ' ').trim() || '-';
                const end = $cells.eq(6).text().replace(/\s+/g, ' ').trim() || '-';
                const status = $cells.eq(7).text().replace(/\s+/g, ' ').trim() || '-';
                const salary = $cells.eq(8).text().replace(/\s+/g, ' ').trim() || '-';

                newBody.push([
                    sNo,
                    code,
                    name,
                    dept,
                    desig,
                    stage,
                    start,
                    end,
                    status,
                    salary
                ]);
            }

            data.body = newBody;
        }

        // Clean export column formatting for PDF & Print
        function formatProbationExportColumn(data, row, column, node, targetType) {
            if (!data && data !== 0) return '-';
            let $el = $('<div>').html(data);
            $el.find('.eo-actions, .eo-icon-btn, .eo-more-btn, form, script, style').remove();

            if (column === 0) {
                return (row + 1).toString();
            }

            if (column === 1) {
                let name = $el.find('.eo-name').text().trim();
                let code = $el.find('.eo-code-under').text().trim();
                if (!name) name = $el.text().trim();

                if (targetType === 'print') {
                    let html = '<div style="font-weight:700; color:#0F172A; font-size:12px; line-height:1.3;">' + name + '</div>';
                    if (code && code !== '-') {
                        html += '<div style="color:#64748B; font-size:10.5px; margin-top:2px;">' + code + '</div>';
                    }
                    return html;
                } else if (targetType === 'pdf') {
                    return name + (code && code !== '-' ? ' (' + code + ')' : '');
                } else {
                    return name + (code ? ' (' + code + ')' : '');
                }
            }

            if (targetType === 'print') {
                return $el.html().trim() || $el.text().replace(/\s+/g, ' ').trim() || '-';
            }

            return $el.text().replace(/\s+/g, ' ').trim() || '-';
        }

        // Initialize DataTable
        const table = $('#probationInternshipTable').DataTable({
            dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
            pageLength: 10,
            ordering: true,
            order: [],
            columnDefs: [
                { orderable: false, targets: [0, 9] }
            ],
            language: {
                emptyTable: "No probation or internship records found."
            },
            buttons: [
                {
                    extend: 'csvHtml5',
                    title: 'Probation_Internship_Report',
                    className: 'buttons-csv d-none',
                    customizeData: function(data) {
                        buildProbationExportData(data);
                    }
                },
                {
                    extend: 'excelHtml5',
                    title: 'Probation_Internship_Report',
                    className: 'buttons-excel d-none',
                    customizeData: function(data) {
                        buildProbationExportData(data);
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Probation / Internship Report',
                    className: 'buttons-pdf d-none',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        format: {
                            body: function(data, row, column, node) {
                                return formatProbationExportColumn(data, row, column, node, 'pdf');
                            }
                        }
                    },
                    customize: function(doc) {
                        doc.pageOrientation = 'landscape';
                        doc.pageSize = 'A4';
                        doc.pageMargins = [18, 22, 18, 22];

                        doc.content[0] = {
                            text: 'PROBATION & INTERNSHIP REPORT',
                            fontSize: 13,
                            bold: true,
                            alignment: 'center',
                            color: '#0F172A',
                            margin: [0, 0, 0, 12]
                        };

                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader = {
                            fontSize: 8.5,
                            bold: true,
                            color: '#0F172A',
                            fillColor: '#F1F5F9',
                            alignment: 'left'
                        };

                        doc.content[1].table.widths = ['5%', '18%', '13%', '13%', '10%', '10%', '10%', '10%', '11%'];

                        let body = doc.content[1].table.body;
                        for (let i = 0; i < body.length; i++) {
                            let row = body[i];
                            let isHeader = (i === 0);
                            for (let j = 0; j < row.length; j++) {
                                let cell = row[j];
                                if (isHeader) {
                                    cell.fillColor = '#F1F5F9';
                                    cell.color = '#0F172A';
                                    cell.bold = true;
                                    cell.fontSize = 8.5;
                                } else {
                                    if (i % 2 === 0) {
                                        cell.fillColor = '#F8FAFC';
                                    }
                                    cell.fontSize = 8;
                                }
                            }
                        }

                        doc.content[1].layout = {
                            hLineWidth: function() { return 0.5; },
                            vLineWidth: function() { return 0.5; },
                            hLineColor: function() { return '#E2E8F0'; },
                            vLineColor: function() { return '#E2E8F0'; },
                            paddingLeft: function() { return 6; },
                            paddingRight: function() { return 6; },
                            paddingTop: function() { return 5; },
                            paddingBottom: function() { return 5; }
                        };
                    }
                },
                {
                    extend: 'print',
                    title: '',
                    className: 'buttons-print d-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        format: {
                            body: function(data, row, column, node) {
                                return formatProbationExportColumn(data, row, column, node, 'print');
                            }
                        }
                    },
                    customize: function(win) {
                        $(win.document.body)
                            .css('font-family', "'Inter', system-ui, -apple-system, sans-serif")
                            .css('padding', '24px')
                            .css('background', '#fff')
                            .css('color', '#0F172A');

                        $(win.document.body).prepend(
                            '<div style="text-align:center; margin-bottom:20px; padding-bottom:12px; border-bottom:2px solid #E2E8F0;">' +
                                '<h1 style="font-size:20px; font-weight:800; color:#0F172A; text-transform:uppercase; margin:0 0 6px 0; letter-spacing:0.5px;">Probation & Internship Report</h1>' +
                                '<p style="font-size:12px; color:#64748B; margin:0;">Generated on ' + new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + '</p>' +
                            '</div>'
                        );

                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', '12px')
                            .css('width', '100%')
                            .css('border-collapse', 'collapse')
                            .css('margin-top', '10px');

                        $(win.document.body).find('table th')
                            .css('background-color', '#F8FAFC')
                            .css('color', '#334155')
                            .css('font-weight', '700')
                            .css('padding', '10px 14px')
                            .css('border', '1px solid #CBD5E1')
                            .css('text-transform', 'uppercase')
                            .css('font-size', '11px')
                            .css('letter-spacing', '0.5px');

                        $(win.document.body).find('table td')
                            .css('padding', '10px 14px')
                            .css('border', '1px solid #E2E8F0')
                            .css('color', '#1E293B')
                            .css('vertical-align', 'middle');

                        $(win.document.body).find('table tbody tr:nth-child(even)').css('background-color', '#F8FAFC');
                    }
                }
            ],
            drawCallback: function(settings) {
                $('.dataTables_info').appendTo('#probationInfoBox');
                $('.dataTables_paginate').appendTo('#probationPaginationBox');
            },
            initComplete: function() {
                $('.dataTables_length').appendTo('#probationLengthBox');
                $('.dataTables_info').appendTo('#probationInfoBox');
                $('.dataTables_paginate').appendTo('#probationPaginationBox');

                if ($.fn.select2) {
                    $('#probationLengthBox .dataTables_length select').select2({
                        minimumResultsForSearch: -1,
                        width: 'auto',
                        dropdownCssClass: 'orb-length-dropdown'
                    });
                }
            }
        });

        // Custom search filtering pipeline
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const row = table.row(dataIndex).node();
                if (!row) return true;

                const search = (appliedFilters.search || '').toLowerCase().trim();
                const department = (appliedFilters.department || '').toLowerCase().trim();
                const status = (appliedFilters.status || '').toLowerCase().trim();
                const employmentType = (appliedFilters.stage || '').toLowerCase().trim();

                const matchSearch = !search || (row.getAttribute('data-search') || '').includes(search);
                const matchDepartment = !department || (row.getAttribute('data-department') || '') === department;
                const matchStatus = !status || (row.getAttribute('data-status') || '') === status;
                const matchEmploymentType = !employmentType || (row.getAttribute('data-employment-type') || '') === employmentType;

                return matchSearch && matchDepartment && matchStatus && matchEmploymentType;
            }
        );

        // Bind Reusable Export Buttons Component
        $(document).on('click', '.orbo-export-group [data-export="csv"]', function(e) {
            e.preventDefault();
            table.button('.buttons-csv').trigger();
        });

        $(document).on('click', '.orbo-export-group [data-export="excel"]', function(e) {
            e.preventDefault();
            table.button('.buttons-excel').trigger();
        });

        $(document).on('click', '.orbo-export-group [data-export="pdf"]', function(e) {
            e.preventDefault();
            table.button('.buttons-pdf').trigger();
        });

        $(document).on('click', '.orbo-export-group [data-export="print"]', function(e) {
            e.preventDefault();
            table.button('.buttons-print').trigger();
        });

        // Filter Form Execution (Apply filter only on Search button click or Enter key)
        $('#btnProbationFilterSubmit').on('click', function(e) {
            e.preventDefault();
            appliedFilters.search = ($('#filterSearch').val() || '').trim().toLowerCase();
            appliedFilters.department = ($('#filterDepartment').val() || '').trim().toLowerCase();
            appliedFilters.status = ($('#filterStatus').val() || '').trim().toLowerCase();
            appliedFilters.stage = ($('#filterEmploymentType').val() || '').trim().toLowerCase();

            table.page('first').draw();
        });

        $('#filterSearch').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#btnProbationFilterSubmit').click();
            }
        });

        // Reset Filter
        $('#resetFilter').on('click', function() {
            $('#filterSearch').val('');
            $('#filterDepartment').val('').trigger('change.select2');
            $('#filterStatus').val('').trigger('change.select2');
            $('#filterEmploymentType').val('').trigger('change.select2');

            appliedFilters = {
                search: '',
                department: '',
                status: '',
                stage: ''
            };

            table.page('first').draw();
        });

        function highlightEmployeeFromNotification() {
            if (!highlightEmployeeId) return;

            $('#filterSearch').val('');
            $('#filterDepartment').val('').trigger('change.select2');
            $('#filterStatus').val('').trigger('change.select2');

            if (stageFromNotification === 'probation') {
                $('#filterEmploymentType').val('probation').trigger('change.select2');
                appliedFilters.stage = 'probation';
            } else if (stageFromNotification === 'internship') {
                $('#filterEmploymentType').val('intern').trigger('change.select2');
                appliedFilters.stage = 'intern';
            } else {
                $('#filterEmploymentType').val('').trigger('change.select2');
                appliedFilters.stage = '';
            }

            appliedFilters.search = '';
            appliedFilters.department = '';
            appliedFilters.status = '';

            table.draw();

            const row = document.getElementById('employee-row-' + highlightEmployeeId);
            if (row) {
                row.classList.add('eo-highlight-row');

                setTimeout(function() {
                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });
                }, 250);

                setTimeout(function() {
                    row.classList.remove('eo-highlight-row');
                }, 8000);
            }
        }

        highlightEmployeeFromNotification();

        // Vanilla JS Tab Switcher for Internship Action Modal (scoped to active modal)
        document.addEventListener('click', function(event) {
            const tabButton = event.target.closest('.eo-action-tab');
            if (!tabButton) return;

            const modal = tabButton.closest('.eo-life-modal') || tabButton.closest('.modal');
            if (!modal) return;

            const tabs = modal.querySelectorAll('.eo-action-tab');
            tabs.forEach(tab => tab.classList.remove('active'));

            tabButton.classList.add('active');

            const targetId = tabButton.getAttribute('data-target');
            const panes = modal.querySelectorAll('.eo-tab-pane');
            panes.forEach(pane => pane.classList.remove('active'));

            const targetPane = modal.querySelector('#' + targetId);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });

        // Toggle Probation Duration Fields on Action dropdown change (Supports native & Select2 change)
        $(document).on('change change.select2', '.select-next-stage', function() {
            const empId = $(this).attr('data-emp-id');
            const probBox = $('.probation-duration-box-' + empId);
            const customBox = $('.custom-probation-box-' + empId);
            if ($(this).val() === 'probation') {
                probBox.show();
                const optVal = probBox.find('.select-probation-option').val();
                if (optVal === 'custom') {
                    customBox.show();
                } else {
                    customBox.hide();
                }
            } else {
                probBox.hide();
                customBox.hide();
            }
        });

        $(document).on('change change.select2', '.select-probation-option', function() {
            const empId = $(this).attr('data-emp-id');
            const customBox = $('.custom-probation-box-' + empId);
            if ($(this).val() === 'custom') {
                customBox.show();
            } else {
                customBox.hide();
            }
        });

        // Initialize Flatpickr and Select2 when lifecycle modal opens
        $(document).on('shown.bs.modal', '.eo-life-modal', function() {
            const modal = $(this);
            if (typeof $.fn.select2 !== 'undefined') {
                modal.find('select.select2-searchable').each(function() {
                    if (!$(this).data('select2')) {
                        $(this).select2({
                            theme: 'bootstrap4',
                            width: '100%',
                            dropdownParent: modal
                        });
                    }
                });
            }
            if (typeof window.initOrboDatePickers === 'function') {
                window.initOrboDatePickers(this);
            }
        });
    });
</script>
