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
document.addEventListener('DOMContentLoaded', function () {
    if (typeof initSearchableSelects === 'function') {
        initSearchableSelects();
    }

    if (window.jQuery) {
        if ($.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }

        // Toggle Custom Date fields based on Month dropdown selection
        $(document).on('change', '#filter_month', function () {
            const val = $(this).val();
            if (val === 'custom') {
                $('.js-custom-date-field').show();
            } else {
                $('.js-custom-date-field').hide();
                $('.js-custom-date-field input').val('');
            }
        });
    }

    function initRegularizationForm(form) {
        if (!form) return;
        const typeSelect = form.querySelector('select[name="request_type"]');
        const inGroup = form.querySelector('.js-in-group');
        const outGroup = form.querySelector('.js-out-group');
        const statusGroup = form.querySelector('.js-status-group');
        
        const inInput = form.querySelector('input[name="requested_punch_in"]');
        const outInput = form.querySelector('input[name="requested_punch_out"]');
        const statusSelect = form.querySelector('select[name="requested_status"]');
        
        if (!typeSelect) return;

        const reqTypeGroup = form.querySelector('.js-req-type-group');
        const hasEmployeeSelect = form.querySelector('select[name="employee_id"]') !== null;

        const updateFields = () => {
            const type = typeSelect.value;
            
            if (inGroup) inGroup.style.display = 'none';
            if (outGroup) outGroup.style.display = 'none';
            if (statusGroup) statusGroup.style.display = 'none';
            
            if (inInput) inInput.removeAttribute('required');
            if (outInput) outInput.removeAttribute('required');
            if (statusSelect) statusSelect.removeAttribute('required');
            
            const hasBothPunches = (type === 'regular_attendance' || type === 'wrong_punch_time' || type === 'attendance_correction');
            if (reqTypeGroup) {
                if (hasBothPunches && hasEmployeeSelect) {
                    reqTypeGroup.classList.add('grid-col-full');
                } else {
                    reqTypeGroup.classList.remove('grid-col-full');
                }
            }

            if (type === 'missed_punch_in') {
                if (inGroup) inGroup.style.display = '';
                if (inInput) inInput.setAttribute('required', 'required');
            } else if (type === 'missed_punch_out') {
                if (outGroup) outGroup.style.display = '';
                if (outInput) outInput.setAttribute('required', 'required');
            } else if (type === 'unlock_attendance') {
                if (inGroup) inGroup.style.display = 'none';
                if (outGroup) outGroup.style.display = 'none';
                if (inInput) inInput.removeAttribute('required');
                if (outInput) outInput.removeAttribute('required');
            } else if (type === 'regular_attendance' || type === 'wrong_punch_time') {
                if (inGroup) inGroup.style.display = '';
                if (outGroup) outGroup.style.display = '';
                if (inInput) inInput.setAttribute('required', 'required');
                if (outInput) outInput.setAttribute('required', 'required');
            } else if (type === 'attendance_correction') {
                if (inGroup) inGroup.style.display = '';
                if (outGroup) outGroup.style.display = '';
            } else if (type === 'other') {
                if (statusGroup) statusGroup.style.display = '';
                if (statusSelect) statusSelect.setAttribute('required', 'required');
            }
        };

        typeSelect.addEventListener('change', updateFields);
        updateFields();

        form.addEventListener('submit', function(e) {
            const type = typeSelect.value;
            const reasonTextarea = form.querySelector('textarea[name="reason"]');
            
            if (type === 'other' && statusSelect && reasonTextarea) {
                const originalReason = reasonTextarea.value;
                if (!originalReason.startsWith('[Attendance Status Correction:')) {
                    reasonTextarea.value = `[Attendance Status Correction: ${statusSelect.value}] ${originalReason}`;
                }
            }
        });
    }

    document.querySelectorAll('.js-regularization-form').forEach(function(form) {
        initRegularizationForm(form);
    });

    const createModal = document.getElementById('createModal');
    if (createModal) {
        const createForm = createModal.querySelector('form');
        const dateInput = createForm ? createForm.querySelector('input[name="attendance_date"]') : null;
        const empSelect = createForm ? (createForm.querySelector('select[name="employee_id"]') || createForm.querySelector('input[name="employee_id"]')) : null;
        const typeSelect = createForm ? createForm.querySelector('select[name="request_type"]') : null;
        const alertBox = document.getElementById('js-regularization-alert');
        const submitBtn = createForm ? createForm.querySelector('button[type="submit"]') : null;

        function fetchAvailableOptions() {
            if (!dateInput || !typeSelect) return;
            const dateVal = dateInput.value;
            const empVal = empSelect ? empSelect.value : '';

            if (empSelect && empSelect.tagName === 'SELECT' && !empVal) {
                typeSelect.innerHTML = '<option value="">Select Employee First</option>';
                typeSelect.disabled = true;
                if (submitBtn) submitBtn.disabled = true;
                if (alertBox) {
                    alertBox.className = 'alert alert-info py-2 px-3 mb-3 small font-weight-bold';
                    alertBox.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Please select an employee to view regularization options.';
                    alertBox.classList.remove('d-none');
                }
                return;
            }

            if (!dateVal) {
                typeSelect.innerHTML = '<option value="">Select Date First</option>';
                typeSelect.disabled = true;
                if (submitBtn) submitBtn.disabled = true;
                if (alertBox) {
                    alertBox.className = 'alert alert-info py-2 px-3 mb-3 small font-weight-bold';
                    alertBox.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Please select an attendance date to view regularization options.';
                    alertBox.classList.remove('d-none');
                }
                return;
            }

            typeSelect.innerHTML = '<option value="">Loading options...</option>';
            typeSelect.disabled = true;
            if (alertBox) alertBox.classList.add('d-none');
            if (submitBtn) submitBtn.disabled = true;

            let optionsUrl = `{{ route('hrms.attendance.regularizations.options') }}?date=${encodeURIComponent(dateVal)}`;
            if (empVal) {
                optionsUrl += `&employee_id=${encodeURIComponent(empVal)}`;
            }

            fetch(optionsUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                const isSuccess = data.success !== false && data.can_regularize !== false;
                const options = data.available_options || [];

                if (isSuccess && options.length > 0) {
                    let html = options.length > 1 ? '<option value="">Select Type</option>' : '';
                    options.forEach(function(opt) {
                        const optVal = opt.value || opt.id;
                        html += `<option value="${optVal}">${opt.label}</option>`;
                    });
                    typeSelect.innerHTML = html;
                    typeSelect.disabled = false;
                    if (submitBtn) submitBtn.disabled = false;

                    if (data.default_option) {
                        typeSelect.value = data.default_option;
                    } else if (data.is_blocked || options.some(function(o) { return (o.value || o.id) === 'unlock_attendance'; })) {
                        typeSelect.value = 'unlock_attendance';
                    } else if (options.length === 1) {
                        typeSelect.value = options[0].value || options[0].id;
                    }

                    if (alertBox) {
                        if (data.is_blocked) {
                            alertBox.className = 'alert alert-warning py-2 px-3 mb-3 small font-weight-bold';
                            alertBox.innerHTML = `<i class="fas fa-user-lock mr-1 text-danger"></i> Attendance Status for ${dateVal}: <strong>Punch Blocked</strong>. 'Unlock Attendance' option has been auto-selected.`;
                        } else {
                            alertBox.className = 'alert alert-info py-2 px-3 mb-3 small font-weight-bold';
                            alertBox.innerHTML = `<i class="fas fa-info-circle mr-1"></i> Attendance Status for ${dateVal}: <strong>${data.attendance_status || 'Absent'}</strong>`;
                        }
                        alertBox.classList.remove('d-none');
                    }
                } else {
                    typeSelect.innerHTML = '<option value="">No options available</option>';
                    typeSelect.disabled = true;
                    if (submitBtn) submitBtn.disabled = true;

                    const msg = data.message || 'Regularization is not allowed for this date.';
                    if (alertBox) {
                        alertBox.className = 'alert alert-warning py-2 px-3 mb-3 small font-weight-bold';
                        alertBox.innerHTML = `<i class="fas fa-exclamation-triangle mr-1"></i> ${msg}`;
                        alertBox.classList.remove('d-none');
                    }
                }
                typeSelect.dispatchEvent(new Event('change'));
            })
            .catch(function(err) {
                console.error('Error fetching regularization options:', err);
                typeSelect.innerHTML = '<option value="">Error loading options</option>';
                typeSelect.disabled = true;
                if (submitBtn) submitBtn.disabled = true;
                if (alertBox) {
                    alertBox.className = 'alert alert-danger py-2 px-3 mb-3 small font-weight-bold';
                    alertBox.innerHTML = `<i class="fas fa-times-circle mr-1"></i> Failed to connect to server. Please try again.`;
                    alertBox.classList.remove('d-none');
                }
            });
        }

        if (dateInput) {
            dateInput.addEventListener('change', fetchAvailableOptions);
        }
        if (empSelect && empSelect.tagName === 'SELECT') {
            empSelect.addEventListener('change', fetchAvailableOptions);
        }

        $(createModal).on('shown.bs.modal', function () {
            if (dateInput && dateInput.value) {
                fetchAvailableOptions();
            }
        });
    }

    const exportFormatBody = function(data, row, column, node) {
        let rawText = $(node).text().replace(/\s+/g, ' ').trim();
        rawText = rawText.replace(/[\u{1F300}-\u{1F9FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]|🔴|🔓/gu, '').trim();

        if (column === 1) {
            let name = $(node).find('.att-emp-name').text().trim();
            let dept = $(node).find('.att-emp-code').text().trim();
            return dept ? name + ' (' + dept + ')' : name;
        }
        return rawText;
    };

    if (window.jQuery && $.fn.DataTable) {
        $.fn.dataTable.ext.errMode = 'none';

        if ($.fn.DataTable.isDataTable('#regularizationDataTable')) {
            $('#regularizationDataTable').DataTable().destroy();
        }

        const table = $('#regularizationDataTable').DataTable({
            destroy: true,
            ordering: false,
            responsive: false,
            autoWidth: false,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
            info: false,
            searching: false,
            dom: 'rt',
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv text-success"></i> CSV',
                    className: 'leave-export-btn',
                    title: '{{ branding_name() }} Attendance Regularizations',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: { body: exportFormatBody }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel text-success"></i> Excel',
                    className: 'leave-export-btn',
                    title: '{{ branding_name() }} Attendance Regularizations',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: { body: exportFormatBody }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf text-danger"></i> PDF',
                    className: 'leave-export-btn',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: '',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: { body: exportFormatBody }
                    },
                    customize: function(doc) {
                        doc.pageMargins = [20, 25, 20, 25];
                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader.fontSize = 8.5;
                        doc.styles.tableHeader.bold = true;
                        doc.styles.tableHeader.fillColor = '#1E293B';
                        doc.styles.tableHeader.color = '#FFFFFF';
                        doc.styles.tableHeader.alignment = 'left';

                        doc.content.unshift({
                            text: '{{ branding_name() }} - Attendance Regularizations',
                            fontSize: 14,
                            bold: true,
                            color: '#1E293B',
                            alignment: 'center',
                            margin: [0, 0, 0, 12]
                        });

                        let tableNode = doc.content.find(c => c.table);
                        if (tableNode && tableNode.table && tableNode.table.body && tableNode.table.body.length > 0) {
                            let colCount = tableNode.table.body[0].length;
                            if (colCount === 9) {
                                tableNode.table.widths = ['4%', '18%', '9%', '12%', '10%', '10%', '17%', '9%', '11%'];
                            } else {
                                tableNode.table.widths = Array(colCount).fill('*');
                            }
                            tableNode.layout = {
                                hLineWidth: function(i, node) { return (i === 0 || i === node.table.body.length) ? 1.2 : 0.5; },
                                vLineWidth: function() { return 0; },
                                hLineColor: function() { return '#CBD5E1'; },
                                paddingLeft: function() { return 4; },
                                paddingRight: function() { return 4; },
                                paddingTop: function() { return 5; },
                                paddingBottom: function() { return 5; },
                                fillColor: function(rowIndex) {
                                    return (rowIndex === 0) ? '#1E293B' : (rowIndex % 2 === 0 ? '#F8FAFC' : null);
                                }
                            };
                        }
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print text-primary"></i> Print',
                    className: 'leave-export-btn',
                    title: '',
                    exportOptions: {
                        columns: ':not(.no-export)',
                        format: { body: exportFormatBody }
                    },
                    customize: function(win) {
                        $(win.document.body).css('font-family', 'Inter, system-ui, -apple-system, sans-serif').css('padding', '20px');
                        $(win.document.body).prepend(
                            '<div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1E293B; padding-bottom: 12px;">' +
                                '<h2 style="margin: 0; color: #1E293B; font-weight: 800; font-size: 20px;">{{ branding_name() }}</h2>' +
                                '<h4 style="margin: 4px 0 0; color: #475569; font-weight: 600; font-size: 14px;">Attendance Regularizations Report</h4>' +
                                '<p style="margin: 4px 0 0; color: #94A3B8; font-size: 11px;">Report Generated: ' + new Date().toLocaleString() + '</p>' +
                            '</div>'
                        );
                    }
                }
            ],
            language: {
                emptyTable: 'No regularization records found.',
                zeroRecords: 'No regularization records found.'
            }
        });

        // Attach buttons to custom toolbar
        if ($('#recordsExportButtons').length) {
            $('#recordsExportButtons').empty();
            table.buttons().container().appendTo('#recordsExportButtons');
        }

        // Custom entries per page length select linking to backend pagination
        var currentPerPage = "{{ request('per_page', 25) }}";
        var lengthSelect = $(
            '<div class="dataTables_length d-flex align-items-center gap-2">' +
                '<label class="mb-0 font-weight-bold text-muted mr-2" style="font-size:13px;">Show</label>' +
                '<select class="table-per-page-select" style="width:75px;">' +
                    '<option value="10"' + (currentPerPage == 10 ? ' selected' : '') + '>10</option>' +
                    '<option value="25"' + (currentPerPage == 25 ? ' selected' : '') + '>25</option>' +
                    '<option value="50"' + (currentPerPage == 50 ? ' selected' : '') + '>50</option>' +
                    '<option value="100"' + (currentPerPage == 100 ? ' selected' : '') + '>100</option>' +
                    '<option value="all"' + ((currentPerPage == 'all' || currentPerPage == -1) ? ' selected' : '') + '>All</option>' +
                '</select>' +
                '<label class="mb-0 font-weight-bold text-muted ml-2" style="font-size:13px;">entries</label>' +
            '</div>'
        );

        $('#recordsLengthBox').empty().append(lengthSelect);

        if ($.fn.select2) {
            var $perPageSelect = lengthSelect.find('select');
            $perPageSelect.select2({
                minimumResultsForSearch: Infinity,
                width: '75px',
                dropdownCssClass: 'select2-dropdown-per-page',
                containerCssClass: 'select2-container--per-page'
            });

            $perPageSelect.on('change', function() {
                var val = $(this).val();
                var url = new URL(window.location.href);
                if (val === '-1') val = 'all';
                url.searchParams.set('per_page', val);
                url.searchParams.delete('page');
                window.location.href = url.toString();
            });
        } else {
            lengthSelect.find('select').on('change', function() {
                var val = $(this).val();
                var url = new URL(window.location.href);
                if (val === '-1') val = 'all';
                url.searchParams.set('per_page', val);
                url.searchParams.delete('page');
                window.location.href = url.toString();
            });
        }

        setTimeout(function() {
            $('#regularizationDataTable').DataTable().columns.adjust();
        }, 250);
    }
});
</script>
