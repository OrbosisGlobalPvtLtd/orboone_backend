<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('filterSearch');
        const departmentFilter = document.getElementById('filterDepartment');
        const statusFilter = document.getElementById('filterStatus');
        const exitTypeFilter = document.getElementById('filterExitType');
        const assetStatusFilter = document.getElementById('filterAssetStatus');
        const fnfStatusFilter = document.getElementById('filterFnfStatus');
        const resetBtn = document.getElementById('resetFilter');

        const exportFormat = {
            body: function (data, row, column, node) {
                // S. No. column (column index 0)
                if (column === 0) {
                    return data.trim();
                }
                // For Employee column (column index 1)
                if (column === 1) {
                    const code = $(node).find('.eo-code-under').text().trim();
                    const name = $(node).find('.eo-name').text().trim();
                    const email = $(node).find('.eo-muted-text').text().trim();
                    return name + '\n' + code + '\n' + email;
                }
                // Strip HTML tags, replace multiple spaces and trim
                return data ? data.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() : '';
            }
        };

        // Initialize DataTable with custom styling and export features
        const table = $('#exitEmployeesTable').DataTable({
            dom: "<'d-none'lB><'row'<'col-12'tr>><'d-none'i p>",
            pageLength: 10,
            ordering: true,
            order: [], // Server-side default order preserved
            columnDefs: [{
                    orderable: false,
                    targets: [13]
                } // Actions column not sortable
            ],
            language: {
                emptyTable: "No exit employees found."
            },
            buttons: [{
                    extend: 'csv',
                    className: 'buttons-csv d-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        format: exportFormat
                    }
                },
                {
                    extend: 'excel',
                    className: 'buttons-excel d-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        format: exportFormat
                    }
                },
                {
                    extend: 'pdf',
                    className: 'buttons-pdf d-none',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        format: exportFormat
                    },
                    customize: function(doc) {
                        doc.content[1].table.widths = [20, 95, 60, 60, 45, 65, 45, 45, 40, 40, 40, 40, 50];
                        doc.defaultStyle.fontSize = 7;
                        doc.styles.tableHeader.fontSize = 7;
                        doc.styles.tableHeader.fillColor = '#4F46E5';
                        doc.styles.tableHeader.color = '#ffffff';
                    }
                },
                {
                    extend: 'print',
                    className: 'buttons-print d-none',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        format: exportFormat
                    },
                    customize: function(win) {
                        $(win.document.body).css('font-size', '10px');
                        $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                    }
                }
            ],
            drawCallback: function(settings) {
                $('.dataTables_info').appendTo('#exitInfoBox');
                $('.dataTables_paginate').appendTo('#exitPaginationBox');
            },
            initComplete: function() {
                $('.dataTables_length').appendTo('#exitLengthBox');
                $('.dataTables_info').appendTo('#exitInfoBox');
                $('.dataTables_paginate').appendTo('#exitPaginationBox');

                if ($.fn.select2) {
                    $('#exitLengthBox .dataTables_length select').select2({
                        minimumResultsForSearch: -1,
                        width: 'auto',
                        dropdownCssClass: 'orb-length-dropdown'
                    });
                }
            }
        });

        // DataTable filtering logic
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const row = table.row(dataIndex).node();
                if (!row) return true;

                const search = (searchInput.value || '').toLowerCase().trim();
                const department = (departmentFilter.value || '').toLowerCase().trim();
                const status = (statusFilter.value || '').toLowerCase().trim();
                const exitType = (exitTypeFilter.value || '').toLowerCase().trim();
                const assetStatus = (assetStatusFilter.value || '').toLowerCase().trim();
                const fnfStatus = (fnfStatusFilter.value || '').toLowerCase().trim();

                const matchSearch = !search || (row.getAttribute('data-search') || '').includes(search);
                const matchDepartment = !department || (row.getAttribute('data-department') || '') === department;
                const matchStatus = !status || (row.getAttribute('data-status') || '') === status;
                const matchExitType = !exitType || (row.getAttribute('data-exit-type') || '') === exitType;
                const matchAsset = !assetStatus || (row.getAttribute('data-asset') || '') === assetStatus;
                const matchFnf = !fnfStatus || (row.getAttribute('data-fnf') || '') === fnfStatus;

                return matchSearch && matchDepartment && matchStatus && matchExitType && matchAsset && matchFnf;
            }
        );

        function applyFilters() {
            table.draw();
        }

        // Dynamic notice period date recalculation in Exit Init forms
        document.querySelectorAll('.eo-exit-init-form').forEach(function(form) {
            const exitType = form.querySelector('.eo-exit-type');
            const resignationDate = form.querySelector('.eo-resignation-date');
            const terminationDate = form.querySelector('.eo-termination-date');
            const lastWorkingDay = form.querySelector('.eo-last-working-day');
            const noticeDays = form.querySelector('.eo-notice-days');
            const noticeWaived = form.querySelector('.eo-notice-waived');
            const immediateExit = form.querySelector('.eo-immediate-exit');

            const toYmd = function(dateObj) {
                const y = dateObj.getFullYear();
                const m = String(dateObj.getMonth() + 1).padStart(2, '0');
                const d = String(dateObj.getDate()).padStart(2, '0');
                return y + '-' + m + '-' + d;
            };

            const recalc = function() {
                if (!exitType || !lastWorkingDay) return;

                const type = String(exitType.value || '').toLowerCase();
                const waived = !!(noticeWaived && noticeWaived.checked);
                const immediate = !!(immediateExit && immediateExit.checked);
                const notice = Math.max(0, parseInt((noticeDays && noticeDays.value) ? noticeDays.value : '15', 10) || 0);

                if (type === 'termination' || type === 'absconding' || immediate) {
                    if (terminationDate && terminationDate.value) {
                        lastWorkingDay.value = terminationDate.value;
                    }
                    return;
                }

                if (waived) {
                    if (resignationDate && resignationDate.value) {
                        lastWorkingDay.value = resignationDate.value;
                    }
                    return;
                }

                if (resignationDate && resignationDate.value) {
                    const base = new Date(resignationDate.value + 'T00:00:00');
                    if (!isNaN(base.getTime())) {
                        base.setDate(base.getDate() + (Math.max(1, notice) - 1));
                        lastWorkingDay.value = toYmd(base);
                    }
                }
            };

            [exitType, resignationDate, terminationDate, noticeDays, noticeWaived, immediateExit].forEach(function(el) {
                if (!el) return;
                el.addEventListener('change', recalc);
                el.addEventListener('input', recalc);
            });

            recalc();
        });

        // Bind custom premium export buttons to DataTable triggers
        $(document).on('click', '.js-export-csv, [data-export="csv"]', function(e) {
            e.preventDefault();
            table.button('.buttons-csv').trigger();
        });
        $(document).on('click', '.js-export-excel, [data-export="excel"]', function(e) {
            e.preventDefault();
            table.button('.buttons-excel').trigger();
        });
        $(document).on('click', '.js-export-pdf, [data-export="pdf"]', function(e) {
            e.preventDefault();
            table.button('.buttons-pdf').trigger();
        });
        $(document).on('click', '.js-export-print, [data-export="print"]', function(e) {
            e.preventDefault();
            table.button('.buttons-print').trigger();
        });

        // Initialize Select2 on Filter Dropdowns
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-searchable').select2({
                width: '100%',
                minimumResultsForSearch: 0
            }).on('change', function() {
                applyFilters();
            });
        } else {
            $('.select2-searchable').on('change', function() {
                applyFilters();
            });
        }

        const submitBtn = document.getElementById('btnExitFilterSubmit');
        if (submitBtn) {
            submitBtn.addEventListener('click', applyFilters);
        }

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });

        // Reset Filters action
        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            departmentFilter.value = '';
            statusFilter.value = '';
            exitTypeFilter.value = '';
            assetStatusFilter.value = '';
            fnfStatusFilter.value = '';
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2-searchable').val('').trigger('change.select2');
            }
            applyFilters();
        });

        // Toast Notification Helper
        function showExitToast(message, type = 'success') {
            let $toast = $('#ajaxExitToast');
            if (!$toast.length) {
                $toast = $(`
                    <div id="ajaxExitToast" style="position: fixed; top: 24px; right: 24px; z-index: 99999; display: none; min-width: 280px; padding: 12px 20px; border-radius: 12px; font-weight: 700; font-size: 13px; color: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.18); transition: all 0.3s ease;">
                        <span class="toast-text"></span>
                    </div>
                `);
                $('body').append($toast);
            }
            const bgColor = (type === 'success' || type === 'approved') ? '#10B981' : (type === 'rejected' || type === 'danger' ? '#EF4444' : '#F59E0B');
            $toast.css('background-color', bgColor);
            $toast.find('.toast-text').html((type === 'success' || type === 'approved' ? '<i class="fas fa-check-circle mr-2"></i>' : '<i class="fas fa-exclamation-circle mr-2"></i>') + message);
            $toast.stop(true, true).fadeIn(200).delay(2500).fadeOut(400);
        }

        // Update Main Table Row Pills & Modal Top Pills Live
        function updateMainTableRowPills(employeeId, process) {
            if (!employeeId || !process) return;

            const getPillClass = function(val) {
                val = (val || 'pending').toLowerCase();
                if (['completed', 'issued', 'not_required', 'cleared', 'approved', 'paid'].includes(val)) return 'eo-pill-success';
                if (['processing', 'clearance_pending', 'generated', 'sent', 'ready_for_final_approval', 'reviewed'].includes(val)) return 'eo-pill-info';
                if (['lost', 'damaged', 'rejected', 'cancelled', 'absconded', 'terminated'].includes(val)) return 'eo-pill-danger';
                return 'eo-pill-warning';
            };

            const formatLabel = function(val) {
                if (!val) return 'Pending';
                const v = String(val).toLowerCase();
                if (v === 'discontinued') return 'Discontinuation';
                if (v === 'absconding') return 'Absconding';
                if (v === 'contract_end') return 'End of Contract';
                if (v === 'internship_completed') return 'Completion of Internship';
                if (v === 'deceased') return 'Death';
                return val.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            };

            const updateCell = function(selector, value) {
                const $el = $(selector);
                if ($el.length && value !== undefined && value !== null) {
                    if ($el.hasClass('eo-pill')) {
                        $el.removeClass('eo-pill-success eo-pill-info eo-pill-danger eo-pill-warning')
                            .addClass(getPillClass(value))
                            .text(formatLabel(value));
                    } else {
                        $el.find('.eo-pill')
                            .removeClass('eo-pill-success eo-pill-info eo-pill-danger eo-pill-warning')
                            .addClass(getPillClass(value))
                            .text(formatLabel(value));
                    }
                }
            };

            if (process.exit_type) updateCell('.js-tbl-cell-exit-type-' + employeeId, process.exit_type);
            const assetVal = process.asset_handover_status || process.asset_status;
            if (assetVal !== undefined) {
                updateCell('.js-tbl-cell-asset-' + employeeId, assetVal);
                updateCell('.js-top-pill-asset-' + employeeId, assetVal);
            }
            if (process.fnf_status !== undefined) {
                updateCell('.js-tbl-cell-fnf-' + employeeId, process.fnf_status);
                updateCell('.js-top-pill-fnf-' + employeeId, process.fnf_status);
            }
            if (process.document_status !== undefined) {
                updateCell('.js-tbl-cell-document-' + employeeId, process.document_status);
                updateCell('.js-top-pill-document-' + employeeId, process.document_status);
            }
            if (process.handover_status !== undefined) {
                updateCell('.js-tbl-cell-handover-' + employeeId, process.handover_status);
                updateCell('.js-top-pill-handover-' + employeeId, process.handover_status);
            }
            if (process.experience_letter_status !== undefined) updateCell('.js-tbl-cell-experience-' + employeeId, process.experience_letter_status);
            if (process.relieving_letter_status !== undefined) updateCell('.js-tbl-cell-relieving-' + employeeId, process.relieving_letter_status);

            const exitStatusVal = process.status || process.exit_status;
            if (exitStatusVal !== undefined) updateCell('.js-tbl-cell-exit-flow-' + employeeId, exitStatusVal);
            if (process.final_status !== undefined) updateCell('.js-tbl-cell-final-' + employeeId, process.final_status);
        }

        // Auto Save Department Clearance via AJAX
        function saveDeptClearanceAjax(form) {
            const $form = $(form);
            const deptKey = $form.data('dept');
            const employeeId = $form.data('employee-id');
            const $card = $form.closest('.js-dept-card').length ? $form.closest('.js-dept-card') : $form.closest('.card');
            const $modal = $form.closest('.modal');
            const $submitBtn = $form.find('button[type="submit"]');
            const originalBtnHtml = $submitBtn.length ? $submitBtn.html() : '';

            if ($submitBtn.length) {
                $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
            }

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if ($submitBtn.length) {
                        $submitBtn.prop('disabled', false).html(originalBtnHtml);
                    }

                    if (response.success) {
                        const status = response.status || 'pending';
                        const statusLabel = response.status_label || (status.charAt(0).toUpperCase() + status.slice(1));

                        let borderColor = '#F59E0B';
                        let badgeClass = 'badge-warning';
                        let iconClass = 'fas fa-clock text-warning mr-2 js-dept-icon js-dept-icon-' + deptKey + ' js-dept-icon-' + deptKey + '-' + employeeId;

                        if (status === 'approved') {
                            borderColor = '#10B981';
                            badgeClass = 'badge-success';
                            iconClass = 'fas fa-check-circle text-success mr-2 js-dept-icon js-dept-icon-' + deptKey + ' js-dept-icon-' + deptKey + '-' + employeeId;
                        } else if (status === 'rejected') {
                            borderColor = '#EF4444';
                            badgeClass = 'badge-danger';
                            iconClass = 'fas fa-times-circle text-danger mr-2 js-dept-icon js-dept-icon-' + deptKey + ' js-dept-icon-' + deptKey + '-' + employeeId;
                        }

                        // 1. Update left border color
                        $card.css('border-left', '4px solid ' + borderColor);

                        // 2. Update status badge
                        const $badge = $card.find('.js-dept-badge');
                        if ($badge.length) {
                            $badge.removeClass('badge-warning badge-success badge-danger')
                                  .addClass(badgeClass)
                                  .text(statusLabel);
                        }

                        // 3. Update icon
                        const $icon = $card.find('.js-dept-icon');
                        if ($icon.length) {
                            $icon.attr('class', iconClass);
                        }

                        // 4. Update approved by text
                        const $approvedByContainer = $card.find('.js-dept-approved-by');
                        if ($approvedByContainer.length) {
                            if (response.approved_by && response.approved_at) {
                                $approvedByContainer.html('<span class="text-muted small mr-2 d-none d-md-inline" style="font-size: 11.5px;">by <strong class="text-dark">' + response.approved_by + '</strong> on ' + response.approved_at + '</span>');
                            } else {
                                $approvedByContainer.html('');
                            }
                        }

                        // 5. Update main table row & modal pills
                        if (response.process) {
                            updateMainTableRowPills(employeeId, response.process);
                        }

                        showExitToast(response.message, status === 'rejected' ? 'danger' : 'success');

                        // 6. Dynamic Update Complete Exit Button block
                        if (response.all_mandatory_approved !== undefined) {
                            const $completeBtnContainer = $modal.find('.js-complete-exit-btn-container');
                            if ($completeBtnContainer.length) {
                                if (response.all_mandatory_approved) {
                                    $completeBtnContainer.html(`
                                        <button type="submit" class="btn btn-success em-btn-success px-3 py-2" style="height:36px; min-height:36px; font-size:12px; border-radius:50px; font-weight:800; border:none;" onclick="return confirm('Complete exit and disable login?')">
                                            <i class="fas fa-user-check mr-1"></i> Complete Exit
                                        </button>
                                    `);
                                } else {
                                    $completeBtnContainer.html(`
                                        <button type="button" class="btn btn-success em-btn-success px-3 py-2" style="height:36px; min-height:36px; font-size:12px; border-radius:50px; font-weight:800; border:none; opacity: 0.5; cursor: not-allowed;" disabled title="Clearances are pending approval">
                                            <i class="fas fa-ban mr-1"></i> Complete Exit (Blocked)
                                        </button>
                                        <span class="text-danger small mt-1 d-block w-100"><i class="fas fa-exclamation-triangle mr-1"></i> All mandatory clearances (HR, Manager, IT, Admin, Finance, Assets) must be approved.</span>
                                    `);
                                }
                            }
                        }
                    } else {
                        showExitToast(response.message || 'Failed to update clearance.', 'danger');
                    }
                },
                error: function(xhr) {
                    if ($submitBtn.length) {
                        $submitBtn.prop('disabled', false).html(originalBtnHtml);
                    }
                    let errMsg = 'Error updating clearance.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    showExitToast(errMsg, 'danger');
                }
            });
        }

        // Trigger AJAX auto-save on checklist item toggle
        $(document).on('change', '.js-dept-clearance-form input[type="checkbox"]', function() {
            saveDeptClearanceAjax(this.form);
        });

        // Trigger AJAX auto-save on status select dropdown change
        $(document).on('change', '.js-dept-clearance-form select[name="status"]', function() {
            saveDeptClearanceAjax(this.form);
        });

        // Trigger AJAX auto-save on remarks change
        $(document).on('change', '.js-dept-clearance-form input[name="remarks"]', function() {
            saveDeptClearanceAjax(this.form);
        });

        // Auto Save Overall Exit Clearance Status via AJAX
        function saveOverallClearanceAjax(form) {
            const $form = $(form);
            const employeeId = $form.data('employee-id');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success && response.process) {
                        const proc = response.process;
                        const getPillClass = function(val) {
                            val = (val || 'pending').toLowerCase();
                            if (['completed', 'issued', 'not_required', 'cleared', 'approved', 'paid'].includes(val)) return 'eo-pill-success';
                            if (['processing', 'clearance_pending', 'generated', 'sent', 'ready_for_final_approval', 'reviewed'].includes(val)) return 'eo-pill-info';
                            if (['lost', 'damaged', 'rejected', 'cancelled', 'absconded', 'terminated'].includes(val)) return 'eo-pill-danger';
                            return 'eo-pill-warning';
                        };

                        const formatLabel = function(val) {
                            if (!val) return 'Pending';
                            return val.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        };

                        if (proc.asset_status) {
                            $form.find('select[name="asset_status"]').val(proc.asset_status.toLowerCase());
                            const $assetPill = $('.js-top-pill-asset-' + employeeId);
                            $assetPill.removeClass('eo-pill-success eo-pill-info eo-pill-danger eo-pill-warning')
                                .addClass(getPillClass(proc.asset_status))
                                .text(formatLabel(proc.asset_status));
                        }
                        if (proc.fnf_status) {
                            $form.find('select[name="fnf_status"]').val(proc.fnf_status.toLowerCase());
                            const $fnfPill = $('.js-top-pill-fnf-' + employeeId);
                            $fnfPill.removeClass('eo-pill-success eo-pill-info eo-pill-danger eo-pill-warning')
                                .addClass(getPillClass(proc.fnf_status))
                                .text(formatLabel(proc.fnf_status));
                        }
                        if (proc.document_status) {
                            $form.find('select[name="document_status"]').val(proc.document_status.toLowerCase());
                            const $docPill = $('.js-top-pill-document-' + employeeId);
                            $docPill.removeClass('eo-pill-success eo-pill-info eo-pill-danger eo-pill-warning')
                                .addClass(getPillClass(proc.document_status))
                                .text(formatLabel(proc.document_status));
                        }
                        if (proc.handover_status) {
                            $form.find('select[name="handover_status"]').val(proc.handover_status.toLowerCase());
                            const $handoverPill = $('.js-top-pill-handover-' + employeeId);
                            $handoverPill.removeClass('eo-pill-success eo-pill-info eo-pill-danger eo-pill-warning')
                                .addClass(getPillClass(proc.handover_status))
                                .text(formatLabel(proc.handover_status));
                        }

                        updateMainTableRowPills(employeeId, proc);

                        showExitToast(response.message || 'Exit clearance status updated.', 'success');
                    } else {
                        showExitToast(response.message || 'Failed to update clearance.', 'danger');
                    }
                },
                error: function(xhr) {
                    let errMsg = 'Error updating clearance.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    showExitToast(errMsg, 'danger');
                }
            });
        }

        $(document).on('change', '.js-overall-clearance-form select', function() {
            saveOverallClearanceAjax(this.form);
        });

        $(document).on('change', '.js-overall-clearance-form input[name="remarks"]', function() {
            saveOverallClearanceAjax(this.form);
        });

        $(document).on('submit', '.js-overall-clearance-form', function(e) {
            e.preventDefault();
            saveOverallClearanceAjax(this);
        });
    });
</script>
