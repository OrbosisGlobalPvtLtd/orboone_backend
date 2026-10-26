<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(function() {
    if ($.fn.select2) {
        $('.select2-searchable').each(function() {
            var placeholder = $(this).attr('data-placeholder') || $(this).find('option:first').text() || 'Select option';
            $(this).select2({
                placeholder: placeholder,
                allowClear: true,
                width: '100%'
            });
        });
    }

    var table = null;
    if ($('#employeeLeavesTable').length) {
        $.fn.dataTable.ext.errMode = 'none';

        table = $('#employeeLeavesTable').DataTable({
            paging: false,
            info: false,
            searching: false,
            ordering: true,
            dom: "<'d-none'B>rt",
            buttons: [
                { extend: 'excelHtml5', className: 'd-none buttons-excel', title: 'Leave_Requests_' + new Date().toISOString().slice(0, 10), exportOptions: { columns: ':not(.no-export)' } },
                { extend: 'csvHtml5', className: 'd-none buttons-csv', title: 'Leave_Requests_' + new Date().toISOString().slice(0, 10), exportOptions: { columns: ':not(.no-export)' } },
                { extend: 'pdfHtml5', className: 'd-none buttons-pdf', orientation: 'landscape', pageSize: 'A4', title: 'Leave Requests Report', exportOptions: { columns: ':not(.no-export)' } },
                { extend: 'print', className: 'd-none buttons-print', title: 'Leave Requests Report', exportOptions: { columns: ':not(.no-export)' } }
            ],
            language: {
                emptyTable: 'No leave requests found matching criteria.',
                zeroRecords: 'No matching records found.'
            }
        });
    }

    // Trigger export functions for the theme Orbo export buttons
    window.exportEmployeeLeaves = function(type) {
        if (table && typeof table.button === 'function') {
            var btn = table.button('.buttons-' + type);
            if (btn && btn.length) {
                btn.trigger();
                return;
            }
        }
        if (type === 'print') {
            window.print();
        }
    };

    // Server-side per_page selector handler
    $('#leaveRequestsPerPageSelect').on('change', function() {
        var perPage = $(this).val();
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });

    // Prevent Bootstrap modal from stealing focus from Flatpickr calendar popups
    if ($.fn.modal && $.fn.modal.Constructor) {
        $.fn.modal.Constructor.prototype._enforceFocus = function() {};
    }

    var currentEditData = null;
    var editPreviewDebounce = null;

    // Cache initial edit leave type options
    var originalLeaveTypeOptions = [];
    $('#edit_leave_type_id option').each(function() {
        originalLeaveTypeOptions.push({
            id: $(this).val(),
            text: $(this).text().trim(),
            code: $(this).attr('data-code') || '',
            isCompOff: parseInt($(this).attr('data-is-comp-off') || 0, 10) === 1,
            isSick: parseInt($(this).attr('data-is-sick') || 0, 10) === 1,
            isPaid: parseInt($(this).attr('data-is-paid') || 0, 10) === 1,
            isLwp: parseInt($(this).attr('data-is-lwp') || 0, 10) === 1,
            halfday: $(this).attr('data-halfday')
        });
    });

    // Dynamically filter leave types based on employee available balance
    function filterEditLeaveTypes(balances, currentLeaveTypeId) {
        var $select = $('#edit_leave_type_id');
        var selectedVal = currentLeaveTypeId || $select.val();

        var compRemaining = (balances && balances.comp_off_remaining !== undefined)
            ? parseFloat(balances.comp_off_remaining) : 0;
        var paidRemaining = (balances && balances.paid_remaining !== undefined)
            ? parseFloat(balances.paid_remaining) : 0;
        var sickRemaining = (balances && balances.sick_remaining !== undefined)
            ? parseFloat(balances.sick_remaining) : 0;

        var availableOptions = [];
        originalLeaveTypeOptions.forEach(function(opt) {
            var isCurrentSelected = (selectedVal && String(opt.id) === String(selectedVal));
            var shouldShow = true;

            if (opt.isCompOff) {
                // Comp-off option appears ONLY if there is comp-off balance (>0) or currently selected for this request
                shouldShow = (compRemaining > 0) || isCurrentSelected;
            } else if (opt.isSick) {
                // Sick leave appears if balance > 0 or currently selected
                shouldShow = (sickRemaining > 0) || isCurrentSelected;
            } else if (opt.isPaid) {
                // Paid leave appears if balance > 0 or currently selected
                shouldShow = (paidRemaining > 0) || isCurrentSelected;
            } else if (opt.isLwp) {
                // Leave without pay is always available as unpaid option
                shouldShow = true;
            }

            if (shouldShow) {
                availableOptions.push(opt);
            }
        });

        // Only rebuild DOM if the list of options actually changed
        var currentIds = $select.find('option').map(function() { return $(this).val(); }).get().join(',');
        var newIds = availableOptions.map(function(o) { return o.id; }).join(',');

        if (currentIds !== newIds) {
            $select.empty();
            availableOptions.forEach(function(opt) {
                var $opt = $('<option></option>')
                    .val(opt.id)
                    .text(opt.text)
                    .attr('data-code', opt.code)
                    .attr('data-is-comp-off', opt.isCompOff ? 1 : 0)
                    .attr('data-is-sick', opt.isSick ? 1 : 0)
                    .attr('data-is-paid', opt.isPaid ? 1 : 0)
                    .attr('data-is-lwp', opt.isLwp ? 1 : 0)
                    .attr('data-halfday', opt.halfday);

                if (selectedVal && String(opt.id) === String(selectedVal)) {
                    $opt.prop('selected', true);
                }
                $select.append($opt);
            });

            if (selectedVal) {
                $select.val(selectedVal);
            }
            if ($.fn.select2) {
                $select.trigger('change.select2');
            }
        }
    }

    // Live preview function for Edit Modal
    function triggerEditLivePreview() {
        clearTimeout(editPreviewDebounce);
        editPreviewDebounce = setTimeout(performEditLivePreview, 140);
    }

    function performEditLivePreview() {
        var leaveTypeId = $('#edit_leave_type_id').val();
        var startDate = $('#edit_start_date').val();
        var isHalfDay = $('#edit_is_half_day').is(':checked');
        var endDate = isHalfDay ? startDate : ($('#edit_end_date').val() || startDate);
        var halfDayType = $('input[name="half_day_type"]:checked', '#editLeaveForm').val() || 'first_half';
        var emergencyLeave = $('#edit_emergency_leave').is(':checked');
        var employeeId = currentEditData ? currentEditData.employee_id : null;
        var leaveRequestId = currentEditData ? currentEditData.id : null;

        if (!leaveTypeId || !startDate) {
            $('#edit_prev_cal_days').text('0');
            $('#edit_prev_work_days').text('0');
            $('#edit_prev_sand_days').text('0');
            $('#edit_prev_deducted_val').text('0.0 Days');
            return;
        }

        $('#editCalcSpinner').show();
        $('#edit_calc_error').hide();

        $.ajax({
            url: "{{ route('leave-requests.preview') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                leave_request_id: leaveRequestId,
                employee_id: employeeId,
                leave_type_id: leaveTypeId,
                start_date: startDate,
                end_date: endDate,
                is_half_day: isHalfDay ? 1 : 0,
                half_day_type: halfDayType,
                emergency_leave: emergencyLeave ? 1 : 0
            },
            success: function(res) {
                $('#editCalcSpinner').hide();
                if (res.success && res.data) {
                    var d = res.data;
                    $('#edit_prev_cal_days').text(d.requested_calendar_days || 0);
                    $('#edit_prev_work_days').text(d.working_days || 0);
                    $('#edit_prev_sand_days').text(d.sandwich_days || 0);

                    var dec = parseFloat(d.deducted_days || 0);
                    $('#edit_prev_deducted_val').text(dec.toFixed(1) + ' Days');

                    // Filter leave types dynamically based on live balance
                    if (d.current_balance) {
                        filterEditLeaveTypes(d.current_balance, $('#edit_leave_type_id').val());
                    }

                    // Sandwich Alert & Dates Breakdown
                    if (d.sandwich_applied && d.sandwich_days > 0) {
                        $('#edit_sand_card').css({ 'background': '#FEF3C7', 'border-color': '#F59E0B' });
                        $('#edit_sandwich_count_text').text(d.sandwich_days);
                        if (d.sandwich_details && d.sandwich_details.length > 0) {
                            var badges = d.sandwich_details.map(function(item) {
                                return '<span class="badge badge-warning text-dark font-weight-bold mr-1 mb-1" style="background: #FDE68A; border: 1px solid #F59E0B; font-size: 10.5px; padding: 4px 7px; border-radius: 6px;"><i class="fas fa-calendar-day mr-1"></i>' + (item.formatted_date || item.date) + ' (' + (item.type || 'Off') + ')</span>';
                            }).join('');
                            $('#edit_sandwich_dates_list').html(badges).show();
                        } else {
                            $('#edit_sandwich_dates_list').hide();
                        }
                        $('#edit_sandwich_alert').slideDown(120);
                    } else {
                        $('#edit_sand_card').css({ 'background': '#FFFBEB', 'border-color': '#FDE68A' });
                        $('#edit_sandwich_dates_list').empty().hide();
                        $('#edit_sandwich_alert').slideUp(120);
                    }

                    // Quota Split
                    $('#edit_split_paid_val').text((parseFloat(d.paid_days) || 0).toFixed(1) + ' Days');
                    $('#edit_split_sick_val').text((parseFloat(d.sick_days) || 0).toFixed(1) + ' Days');
                    $('#edit_split_comp_val').text((parseFloat(d.comp_off_days) || 0).toFixed(1) + ' Days');
                    $('#edit_split_lwp_val').text((parseFloat(d.lwp_days) || 0).toFixed(1) + ' Days');

                    // Formula
                    if (d.ledger_backed || d.pl_days !== undefined) {
                        var formula = 'PL ' + Number(d.pl_days || 0).toFixed(1) + ' + SL ' + Number(d.sl_days || 0).toFixed(1) + ' + Comp ' + Number(d.comp_off_days || 0).toFixed(1) + ' + LWP ' + Number(d.lop_days || 0).toFixed(1);
                        $('#edit_ledger_split_formula').text(formula).show();
                    } else {
                        $('#edit_ledger_split_formula').hide();
                    }

                    // Balance Impact (Current vs After Deduction)
                    if (d.current_balance && d.balance_after_split) {
                        var curBal = parseFloat(d.current_balance.total_remaining || 0).toFixed(1);
                        var afterBal = parseFloat(d.balance_after_split.total_remaining || 0).toFixed(1);
                        $('#edit_balance_impact_curr').text(curBal + ' Days');
                        $('#edit_balance_impact_after').text(afterBal + ' Days');
                        $('#edit_balance_impact_box').slideDown(120);
                    } else {
                        $('#edit_balance_impact_box').slideUp(120);
                    }

                    // Notice Warning
                    if (d.notice_warning) {
                        $('#edit_notice_text').text(d.notice_warning);
                        $('#edit_notice_alert').slideDown(120);
                    } else if (d.duplicate_warning) {
                        $('#edit_notice_text').text(d.duplicate_warning);
                        $('#edit_notice_alert').slideDown(120);
                    } else {
                        $('#edit_notice_alert').slideUp(120);
                    }

                    $('#edit_calc_error').hide();
                }
            },
            error: function(xhr) {
                $('#editCalcSpinner').hide();
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Calculation error';
                $('#edit_prev_cal_days').text('0');
                $('#edit_prev_work_days').text('0');
                $('#edit_prev_sand_days').text('0');
                $('#edit_prev_deducted_val').text('0.0 Days');
                $('#edit_sandwich_alert').slideUp(120);
                $('#edit_sandwich_dates_list').empty().hide();
                $('#edit_notice_alert').slideUp(120);
                $('#edit_balance_impact_box').slideUp(120);
                $('#edit_calc_error_text').text(msg);
                $('#edit_calc_error').slideDown(120);
            }
        });
    }

    function bindEditDatePickerHooks() {
        var startEl = document.getElementById('edit_start_date');
        var endEl = document.getElementById('edit_end_date');

        if (startEl && startEl._flatpickr && !startEl._flatpickr._editHookBound) {
            startEl._flatpickr.config.onChange.push(function(selectedDates, dateStr) {
                if ($('#edit_is_half_day').is(':checked')) {
                    var endInput = document.getElementById('edit_end_date');
                    if (endInput && endInput._flatpickr) {
                        endInput._flatpickr.setDate(dateStr, false);
                    } else if (endInput) {
                        endInput.value = dateStr;
                    }
                }
                triggerEditLivePreview();
            });
            startEl._flatpickr._editHookBound = true;
        }

        if (endEl && endEl._flatpickr && !endEl._flatpickr._editHookBound) {
            endEl._flatpickr.config.onChange.push(function(selectedDates, dateStr) {
                triggerEditLivePreview();
            });
            endEl._flatpickr._editHookBound = true;
        }
    }

    // Toggle half-day inputs in edit modal
    function syncEditHalfDayState() {
        var isHalf = $('#edit_is_half_day').is(':checked');
        var editEndEl = document.getElementById('edit_end_date');
        if (isHalf) {
            $('#edit_half_day_type_container').slideDown(150);
            $('#edit_half_day_card').css('border-color', '#4B00E8');
            var startDateVal = $('#edit_start_date').val();
            if (startDateVal && editEndEl) {
                if (editEndEl._flatpickr) {
                    editEndEl._flatpickr.setDate(startDateVal, false);
                } else {
                    editEndEl.value = startDateVal;
                }
            }
            if (editEndEl && editEndEl._flatpickr && editEndEl._flatpickr.altInput) {
                editEndEl._flatpickr.altInput.disabled = true;
                editEndEl._flatpickr.altInput.style.backgroundColor = '#F1F5F9';
                editEndEl._flatpickr.altInput.style.cursor = 'not-allowed';
            }
            $('#edit_end_date_hint').show();
        } else {
            $('#edit_half_day_type_container').slideUp(150);
            $('#edit_half_day_card').css('border-color', '#E2E8F0');
            if (editEndEl && editEndEl._flatpickr && editEndEl._flatpickr.altInput) {
                editEndEl._flatpickr.altInput.disabled = false;
                editEndEl._flatpickr.altInput.style.backgroundColor = '#fff';
                editEndEl._flatpickr.altInput.style.cursor = 'pointer';
            }
            $('#edit_end_date_hint').hide();
        }
        triggerEditLivePreview();
    }

    $('#edit_is_half_day').on('change', syncEditHalfDayState);
    $('input[name="half_day_type"]', '#editLeaveForm').on('change', triggerEditLivePreview);
    $('#edit_emergency_leave').on('change', triggerEditLivePreview);
    $('#edit_leave_type_id').on('change', triggerEditLivePreview);
    $('#edit_start_date').on('change', function() {
        if ($('#edit_is_half_day').is(':checked')) {
            var val = $(this).val();
            var editEndEl = document.getElementById('edit_end_date');
            if (editEndEl) {
                if (editEndEl._flatpickr) {
                    editEndEl._flatpickr.setDate(val, false);
                } else {
                    editEndEl.value = val;
                }
            }
        }
        triggerEditLivePreview();
    });
    $('#edit_end_date').on('change', triggerEditLivePreview);

    // Edit Request Modal Handler
    $(document).on('click', '.js-edit-request', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var id = $btn.data('id');
        var employeeId = $btn.data('employee-id');
        var startDate = $btn.data('start-date');
        var endDate = $btn.data('end-date');
        var leaveTypeId = $btn.data('leave-type-id');
        var isHalfDay = parseInt($btn.data('is-half-day'), 10) === 1;
        var halfDayType = $btn.data('half-day-type') || 'first_half';
        var emergencyLeave = parseInt($btn.data('emergency-leave'), 10) === 1;
        var reason = $btn.data('reason') || '';
        var attachmentPath = $btn.data('attachment-path');
        var attachmentUrl = $btn.data('attachment-url');

        currentEditData = {
            id: id,
            employee_id: employeeId
        };

        var updateUrl = "{{ route('leave-requests.update', ':id') }}".replace(':id', id);
        $('#editLeaveForm').attr('action', updateUrl);
        $('#edit_attachment').val('');

        // Extract balance from rowData and filter leave types
        var rowData = $btn.data('row');
        if (typeof rowData === 'string') {
            try { rowData = JSON.parse(rowData); } catch(err) { rowData = {}; }
        }
        rowData = rowData || {};

        var compRemaining = (rowData.comp_off_remaining !== undefined) ? parseFloat(rowData.comp_off_remaining) : 0;
        var paidRemaining = (rowData.paid_remaining !== undefined) ? parseFloat(rowData.paid_remaining) : 0;
        var sickRemaining = (rowData.sick_remaining !== undefined) ? parseFloat(rowData.sick_remaining) : 0;

        filterEditLeaveTypes({
            comp_off_remaining: compRemaining,
            paid_remaining: paidRemaining,
            sick_remaining: sickRemaining
        }, leaveTypeId);

        // Select leave type
        $('#edit_leave_type_id').val(leaveTypeId).trigger('change.select2');

        // Reason
        $('#edit_leave_reason').val(reason);

        // Half day
        $('#edit_is_half_day').prop('checked', isHalfDay);
        if (halfDayType === 'second_half') {
            $('#edit_session_second').prop('checked', true);
        } else {
            $('#edit_session_first').prop('checked', true);
        }

        // Emergency leave
        $('#edit_emergency_leave').prop('checked', emergencyLeave);

        // Attachment
        if (attachmentPath && attachmentUrl) {
            $('#edit_current_attachment_link').attr('href', attachmentUrl);
            var fileName = attachmentPath.toString().split('/').pop();
            $('#edit_existing_attachment_name').text(fileName || 'Attached Document');
            $('#edit_existing_attachment_container').show();
        } else {
            $('#edit_existing_attachment_container').hide();
        }

        // Ensure datepickers are initialized
        if (typeof window.initOrboDatePickers === 'function') {
            window.initOrboDatePickers(document.getElementById('editLeaveModal'));
        }

        var startInput = document.getElementById('edit_start_date');
        if (startInput) {
            if (startInput._flatpickr) {
                startInput._flatpickr.setDate(startDate, false);
            } else {
                startInput.value = startDate;
            }
        }

        var endInput = document.getElementById('edit_end_date');
        if (endInput) {
            if (endInput._flatpickr) {
                endInput._flatpickr.setDate(endDate, false);
            } else {
                endInput.value = endDate;
            }
        }

        bindEditDatePickerHooks();
        syncEditHalfDayState();

        $('#editLeaveModal').modal('show');
    });

    $('#editLeaveModal').on('shown.bs.modal', function () {
        if (typeof window.initOrboDatePickers === 'function') {
            window.initOrboDatePickers(this);
        }
        bindEditDatePickerHooks();
        var s = document.getElementById('edit_start_date');
        if (s && s._flatpickr) s._flatpickr.redraw();
        var e = document.getElementById('edit_end_date');
        if (e && e._flatpickr) e._flatpickr.redraw();

        triggerEditLivePreview();
    });
});
</script>
