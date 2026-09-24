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
    // Tooltips activation
    if (window.jQuery && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
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

        const updateFields = () => {
            const type = typeSelect.value;
            
            // Hide all conditional groups first
            if (inGroup) inGroup.style.display = 'none';
            if (outGroup) outGroup.style.display = 'none';
            if (statusGroup) statusGroup.style.display = 'none';
            
            if (inInput) inInput.removeAttribute('required');
            if (outInput) outInput.removeAttribute('required');
            if (statusSelect) statusSelect.removeAttribute('required');
            
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

        // Handle prepending dynamic status to reason before submit
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

    // Dynamic Attendance Options fetcher for Create Modal
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

                    // Auto-select Unlock Attendance if blocked, or default option
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

        // Also fetch on modal show if date is pre-filled
        $(createModal).on('shown.bs.modal', function () {
            if (dateInput && dateInput.value) {
                fetchAvailableOptions();
            }
        });
    }

    // DataTable initialization
    if (window.jQuery && $.fn.DataTable) {
        if ($.fn.DataTable.isDataTable('#regularizationDataTable')) {
            $('#regularizationDataTable').DataTable().destroy();
        }

        $('#regularizationDataTable').DataTable({
            destroy: true,
            paging: false,
            searching: false,
            lengthChange: false,
            info: false,
            responsive: false,
            autoWidth: false,
            order: [],
            scrollX: false,
            dom: "<'row mx-0 px-2 py-3 align-items-center'<'col-sm-12 col-md-6 px-0'l><'col-sm-12 col-md-6 px-0 text-right'B>><'table-responsive-wrap'rt>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-light border',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-light border',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-light border',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: '{{ branding_name() }} Attendance Regularizations',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-light border',
                    title: '{{ branding_name() }} Attendance Regularizations',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                }
            ],
            language: {
                emptyTable: 'No regularization records found.'
            }
        });

        setTimeout(function() {
            $('#regularizationDataTable').DataTable().columns.adjust();
        }, 250);
    }
});
</script>
