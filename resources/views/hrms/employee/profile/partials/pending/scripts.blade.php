<script>
    $(document).ready(function() {
        let selectedApproveForm = null;
        let selectedRejectForm = null;

        const approveModal = document.getElementById('approveModal');
        const rejectModal = document.getElementById('rejectModal');

        function resetApproveToggles() {
            $('.profile-approve-toggle').prop('checked', false);
        }

        function closeApprove() {
            selectedApproveForm = null;
            if (approveModal) approveModal.style.display = 'none';
            resetApproveToggles();
        }

        function closeReject() {
            selectedRejectForm = null;
            if (rejectModal) rejectModal.style.display = 'none';
            const reasonBox = document.getElementById('rejectReasonBox');
            if (reasonBox) reasonBox.value = '';
        }

        // Clean column formatting for Print and PDF
        function formatPendingExportColumn(data, row, column, node, targetType) {
            if (!data) return '-';
            let $el = $('<div>').html(data);

            // Strip action buttons, avatars, scripts, styles
            $el.find('.hrms-emp-avatar, .hrms-emp-avatar-fallback, .actions, .action-btn, .complete-switch, form, script, style').remove();

            // Column 0: S.No
            if (column === 0) {
                return (row + 1).toString();
            }

            // Column 1: Employee Name & Email
            if (column === 1) {
                let name = $el.find('.emp-name').text().trim();
                let email = $el.find('.emp-email').text().trim();
                if (!name) name = $el.text().trim();

                if (targetType === 'print') {
                    let html = '<div style="font-weight:700; color:#0F172A; font-size:12px; line-height:1.3;">' + name + '</div>';
                    if (email && email !== '-') {
                        html += '<div style="color:#64748B; font-size:10.5px; margin-top:2px;">' + email + '</div>';
                    }
                    return html;
                } else if (targetType === 'pdf') {
                    let lines = [name];
                    if (email && email !== '-') lines.push(email);
                    return lines.join('\n');
                } else {
                    return name + (email && email !== '-' ? ' (' + email + ')' : '');
                }
            }

            // Column 2: Code
            if (column === 2) {
                return $el.find('.code-badge').text().trim() || $el.text().trim() || '-';
            }

            // Column 5: Status badge
            if (column === 5) {
                return $el.find('.status-badge').text().trim() || $el.text().trim() || '-';
            }

            if (targetType === 'print') {
                return $el.html().trim() || $el.text().replace(/\s+/g, ' ').trim() || '-';
            }

            return $el.text().replace(/\s+/g, ' ').trim() || '-';
        }

        // Export helper for CSV and Excel tabular data
        function buildPendingExportData(data) {
            data.header = [
                'S.No',
                'Employee Code',
                'Employee Name',
                'Email',
                'Department',
                'Designation',
                'Profile Status',
                'Updated At'
            ];

            let api = $('#pendingProfilesTable').DataTable();
            let rowsData = api.rows({ search: 'applied' }).data().toArray();
            let startIdx = (api.page.info && api.page.info().start) ? api.page.info().start : 0;

            let newBody = [];
            for (let i = 0; i < rowsData.length; i++) {
                let row = rowsData[i];
                let sNo = (startIdx + i + 1).toString();
                let code = row.raw_code || $('<div>').html(row.code || '').text().trim() || '-';
                let name = row.raw_name || '';
                let email = row.raw_email || '';

                if (!name) {
                    let $emp = $('<div>').html(row.employee || '');
                    name = $emp.find('.emp-name').text().trim() || '-';
                    email = $emp.find('.emp-email').text().trim() || '-';
                }

                let dept = row.raw_department || $('<div>').html(row.department || '-').text().trim();
                let desig = row.raw_designation || $('<div>').html(row.designation || '-').text().trim();
                let status = row.raw_status || $('<div>').html(row.status || '-').text().trim();
                let updated = row.raw_updated || $('<div>').html(row.updated || '-').text().trim();

                newBody.push([
                    sNo,
                    code,
                    name,
                    email,
                    dept,
                    desig,
                    status,
                    updated
                ]);
            }

            data.body = newBody;
        }

        // Filter State (applied only on Search button click)
        let appliedFilters = {
            search: '',
            department: '',
            status: ''
        };

        // Initialize DataTable
        let table = $('#pendingProfilesTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            ajax: {
                url: "{{ route('hrms.employees.pending_profiles') }}",
                type: "GET",
                data: function(d) {
                    d.ajax_table = 1;
                    d.department = appliedFilters.department;
                    d.status = appliedFilters.status;
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            },
            columns: [
                {
                    data: 's_no',
                    name: 's_no',
                    orderable: false,
                    searchable: false,
                    className: 'text-center font-weight-bold text-muted',
                    width: '45px'
                },
                {
                    data: 'employee',
                    name: 'users.name',
                    defaultContent: '-'
                },
                {
                    data: 'code',
                    name: 'employee_code',
                    defaultContent: '-'
                },
                {
                    data: 'department',
                    name: 'departments.name',
                    defaultContent: '-'
                },
                {
                    data: 'designation',
                    name: 'designations.name',
                    defaultContent: '-'
                },
                {
                    data: 'status',
                    name: 'profile_status',
                    defaultContent: '-'
                },
                {
                    data: 'approve',
                    name: 'approve',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    width: '110px'
                },
                {
                    data: 'updated',
                    name: 'updated_at',
                    width: '120px'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    width: '110px'
                }
            ],
            order: [
                [0, 'desc']
            ],
            dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    title: 'Pending_Profiles_Report',
                    className: 'buttons-csv d-none',
                    customizeData: function(data) {
                        buildPendingExportData(data);
                    }
                },
                {
                    extend: 'excelHtml5',
                    title: 'Pending_Profiles_Report',
                    className: 'buttons-excel d-none',
                    customizeData: function(data) {
                        buildPendingExportData(data);
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Pending Profiles Report',
                    className: 'buttons-pdf d-none',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 7],
                        format: {
                            body: function(data, row, column, node) {
                                return formatPendingExportColumn(data, row, column, node, 'pdf');
                            }
                        }
                    },
                    customize: function(doc) {
                        doc.pageOrientation = 'landscape';
                        doc.pageSize = 'A4';
                        doc.pageMargins = [18, 22, 18, 22];

                        doc.content[0] = {
                            text: 'PENDING PROFILES REVIEW REPORT',
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

                        // 7 columns: S.No (5%), Employee (25%), Code (12%), Department (18%), Designation (18%), Status (11%), Updated (11%)
                        doc.content[1].table.widths = ['5%', '25%', '12%', '18%', '18%', '11%', '11%'];

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
                                    if (j === 0 || j === 2 || j === 5 || j === 6) {
                                        cell.alignment = 'center';
                                    }
                                } else {
                                    if (i % 2 === 0) {
                                        cell.fillColor = '#F8FAFC';
                                    }
                                    cell.fontSize = 8;
                                    if (j === 0 || j === 2 || j === 5 || j === 6) {
                                        cell.alignment = 'center';
                                    }
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
                        columns: [0, 1, 2, 3, 4, 5, 7],
                        format: {
                            body: function(data, row, column, node) {
                                return formatPendingExportColumn(data, row, column, node, 'print');
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
                                '<h1 style="font-size:20px; font-weight:800; color:#0F172A; text-transform:uppercase; margin:0 0 6px 0; letter-spacing:0.5px;">Pending Profiles Report</h1>' +
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
            language: {
                processing: '<strong>Loading profiles...</strong>',
                emptyTable: 'No pending, submitted or rejected profile found',
                zeroRecords: 'No matching profile found'
            },
            drawCallback: function(settings) {
                $('.dataTables_info').appendTo('#pendingProfilesInfoBox');
                $('.dataTables_paginate').appendTo('#pendingProfilesPaginationBox');
            },
            initComplete: function() {
                $('.dataTables_length').appendTo('#pendingProfilesLengthBox');
                $('.dataTables_info').appendTo('#pendingProfilesInfoBox');
                $('.dataTables_paginate').appendTo('#pendingProfilesPaginationBox');
            }
        });

        // Bind Reusable Export Buttons Component to DataTables Export
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

        // Filter Actions (Execute query only when Search is clicked)
        $('#btnPendingFilterSubmit').on('click', function(e) {
            e.preventDefault();
            appliedFilters.search = ($('#filterSearch').val() || '').trim();
            appliedFilters.department = $('#filterDepartment').val() || '';
            appliedFilters.status = $('#filterStatus').val() || '';

            table.search(appliedFilters.search);
            table.page('first').draw('page');
        });

        $('#filterSearch').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#btnPendingFilterSubmit').click();
            }
        });

        // Reset Filter (Clear fields, reset state, and reload default unfiltered data)
        $('#resetFilter').on('click', function() {
            $('#filterSearch').val('');
            $('#filterDepartment').val('').trigger('change.select2');
            $('#filterStatus').val('').trigger('change.select2');

            appliedFilters = {
                search: '',
                department: '',
                status: ''
            };

            table.search('');
            table.page('first').draw('page');
        });

        // Dynamic event delegation for approve toggles
        $(document).on('change', '.profile-approve-toggle', function() {
            if (this.checked) {
                selectedApproveForm = document.getElementById(this.getAttribute('data-form-id'));
                if (approveModal) approveModal.style.display = 'flex';
            }
        });

        // Dynamic event delegation for reject buttons
        $(document).on('click', '.reject-profile-btn', function() {
            selectedRejectForm = document.getElementById(this.getAttribute('data-form-id'));
            if (rejectModal) {
                rejectModal.style.display = 'flex';
                setTimeout(() => {
                    const reasonBox = document.getElementById('rejectReasonBox');
                    if (reasonBox) reasonBox.focus();
                }, 100);
            }
        });

        const cancelApproveBtn = document.getElementById('cancelApprove');
        if (cancelApproveBtn) {
            cancelApproveBtn.addEventListener('click', closeApprove);
        }

        const confirmApproveBtn = document.getElementById('confirmApprove');
        if (confirmApproveBtn) {
            confirmApproveBtn.addEventListener('click', function() {
                if (selectedApproveForm) selectedApproveForm.submit();
            });
        }

        const cancelRejectBtn = document.getElementById('cancelReject');
        if (cancelRejectBtn) {
            cancelRejectBtn.addEventListener('click', closeReject);
        }

        const confirmRejectBtn = document.getElementById('confirmReject');
        if (confirmRejectBtn) {
            confirmRejectBtn.addEventListener('click', function() {
                if (!selectedRejectForm) return;

                const reason = (document.getElementById('rejectReasonBox').value || '').trim() || 'Profile rejected by HR';
                const inputEl = selectedRejectForm.querySelector('.reject-reason-input');
                if (inputEl) inputEl.value = reason;
                selectedRejectForm.submit();
            });
        }

        if (approveModal) {
            approveModal.addEventListener('click', function(e) {
                if (e.target === approveModal) closeApprove();
            });
        }

        if (rejectModal) {
            rejectModal.addEventListener('click', function(e) {
                if (e.target === rejectModal) closeReject();
            });
        }
    });
</script>
