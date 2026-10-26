<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.select2) {
            $('#filter_employee_select, .filter-control-sm.select2-searchable').select2({
                width: '100%'
            });

            $('#grant_comp_off_employee').select2({
                dropdownParent: $('#grantCompOffModal'),
                width: '100%'
            });

            $('#compOffPerPageSelect').select2({
                minimumResultsForSearch: Infinity,
                width: '75px',
                dropdownCssClass: 'select2-dropdown-per-page',
                containerCssClass: 'select2-container--per-page'
            }).on('change', function() {
                const val = $(this).val();
                let url = updateQueryStringParameter(window.location.href, 'per_page', val);
                url = url.replace(/([?&])page=\d+/gi, '').replace(/\?&/, '?').replace(/&&/, '&');
                if (url.endsWith('?') || url.endsWith('&')) {
                    url = url.slice(0, -1);
                }
                window.location.href = url;
            });
        }

        if (window.jQuery) {
            $('#grantCompOffModal').on('shown.bs.modal', function () {
                if (window.initOrboDatePickers) {
                    window.initOrboDatePickers(this);
                }
                $('#grant_comp_off_employee').select2({
                    dropdownParent: $('#grantCompOffModal'),
                    width: '100%'
                });
            });

            $('#editCompOffModal').on('shown.bs.modal', function () {
                if (window.initOrboDatePickers) {
                    window.initOrboDatePickers(this);
                }
                const pendingW = $(this).data('pendingWorkedDate');
                const pendingE = $(this).data('pendingExpiryDate');
                if (pendingW) {
                    const wInput = document.getElementById('edit_worked_date');
                    if (wInput && wInput._flatpickr) {
                        wInput._flatpickr.setDate(pendingW, true);
                    }
                }
                if (pendingE) {
                    const eInput = document.getElementById('edit_expiry_date');
                    if (eInput && eInput._flatpickr) {
                        eInput._flatpickr.setDate(pendingE, true);
                    }
                }
                if ($.fn.select2) {
                    $('#edit_status').select2({
                        dropdownParent: $('#editCompOffModal'),
                        width: '100%',
                        minimumResultsForSearch: Infinity
                    });
                }
            });

            // Edit Comp-Off button handler
            $(document).on('click', '.btn-edit-comp-off', function (e) {
                e.preventDefault();
                const btn = $(this);
                const id = btn.data('id');
                const employee = btn.data('employee') || 'Employee';
                const code = btn.data('code') || '';
                const workedDate = btn.data('worked-date') || '';
                const earnedDays = btn.data('earned-days') || '1.0';
                const expiryDate = btn.data('expiry-date') || '';
                let status = (btn.data('status') || 'earned').toLowerCase();
                if (status === 'available') status = 'earned';
                const remarks = btn.data('remarks') || '';
                const updateUrl = btn.data('update-url') || '';

                const initial = employee ? employee.trim().charAt(0).toUpperCase() : 'E';
                $('#edit_employee_avatar').text(initial);
                $('#edit_employee_name').text(employee);
                $('#edit_employee_code').text(code || 'EMP');

                $('#edit_comp_off_id').val(id);
                $('#edit_earned_days').val(earnedDays);
                $('#edit_status').val(status).trigger('change');
                $('#edit_remarks').val(remarks);
                $('#editCompOffForm').attr('action', updateUrl);

                // Store and set date values on flatpickr / inputs
                $('#editCompOffModal').data('pendingWorkedDate', workedDate);
                $('#editCompOffModal').data('pendingExpiryDate', expiryDate);

                const wInput = document.getElementById('edit_worked_date');
                if (wInput) {
                    if (wInput._flatpickr) {
                        wInput._flatpickr.setDate(workedDate, true);
                    } else {
                        wInput.value = workedDate;
                    }
                }

                const eInput = document.getElementById('edit_expiry_date');
                if (eInput) {
                    if (eInput._flatpickr) {
                        eInput._flatpickr.setDate(expiryDate, true);
                    } else {
                        eInput.value = expiryDate;
                    }
                }

                $('#editCompOffModal').modal('show');
            });
        }

        // Tab state hash handling
        const currentHash = window.location.hash;
        if (currentHash === '#transactions' || currentHash === '#tab-transactions') {
            if (window.jQuery) {
                $('#transactions-tab').tab('show');
            }
        }

        if (window.jQuery) {
            const defaultCardTitle = $('#compOffCardTitle').text().trim() || 'Compensatory Off Records';
            const defaultCardSubtitle = $('#compOffCardSubtitle').text().trim() || 'Track employee earned, used, expired compensatory-off balances and expiry schedule.';

            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                const targetId = $(e.target).attr('href');
                if (history.replaceState) {
                    history.replaceState(null, null, targetId.replace('tab-', ''));
                }
                if (targetId === '#tab-transactions') {
                    $('#compOffCardTitle').text('Comp-Off Transactions');
                    $('#compOffCardSubtitle').text('Read-only audit ledger of compensatory-off credits, debits, reversals and expiry movements.');
                    $('#compOffCardIcon').removeClass('fa-calendar-check').addClass('fa-history');
                } else {
                    $('#compOffCardTitle').text(defaultCardTitle);
                    $('#compOffCardSubtitle').text(defaultCardSubtitle);
                    $('#compOffCardIcon').removeClass('fa-history').addClass('fa-calendar-check');
                }
            });
        }
    });

    function exportTable(format, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        }

        const activePane = document.querySelector('#compOffTabContent .tab-pane.active');
        const table = (activePane && activePane.querySelector('table')) ? activePane.querySelector('table') : document.getElementById('compOffTable');
        if (!table) return;

        const isTxTab = activePane && activePane.id === 'tab-transactions';
        const filePrefix = isTxTab ? 'compensatory_off_transactions_' : 'compensatory_off_records_';

        if (format === 'csv' || format === 'excel') {
            let csv = [];
            const rows = table.querySelectorAll('tr');
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll('th, td');
                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                    text = text.replace(/"/g, '""');
                    row.push('"' + text + '"');
                }
                csv.push(row.join(','));
            }
            const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
            const link = document.createElement('a');
            link.setAttribute('href', csvContent);
            link.setAttribute('download', filePrefix + new Date().toISOString().slice(0, 10) + '.' + (format === 'excel' ? 'xls' : 'csv'));
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else if (format === 'pdf') {
            const pMake = window.pdfMake || (typeof pdfMake !== 'undefined' ? pdfMake : null);
            if (!pMake) {
                alert('PDF library is loading, please try again in a moment.');
                return;
            }

            if (typeof window.vfsFonts !== 'undefined' && window.vfsFonts.pdfMake) {
                pMake.vfs = window.vfsFonts.pdfMake.vfs;
            } else if (typeof window.pdfMake_vfs !== 'undefined') {
                pMake.vfs = window.pdfMake_vfs;
            }

            const headers = [];
            const thCols = table.querySelectorAll('thead tr th');
            for (let j = 0; j < thCols.length; j++) {
                headers.push({
                    text: thCols[j].innerText.replace(/\s+/g, ' ').trim(),
                    bold: true,
                    fillColor: '#4B00E8',
                    color: '#FFFFFF',
                    fontSize: 8.5
                });
            }

            const bodyRows = [headers];
            const trRows = table.querySelectorAll('tbody tr');

            trRows.forEach(function(tr, rowIndex) {
                const tdCols = tr.querySelectorAll('td');
                if (tdCols.length === 1 && tr.querySelector('.empty-state')) {
                    return;
                }
                if (tdCols.length > 0) {
                    const rowData = [];
                    for (let j = 0; j < tdCols.length; j++) {
                        let cellText = tdCols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                        rowData.push({
                            text: cellText || '-',
                            fontSize: 8,
                            fillColor: rowIndex % 2 === 0 ? '#FFFFFF' : '#F8FAFC',
                            color: '#1E293B'
                        });
                    }
                    bodyRows.push(rowData);
                }
            });

            const colWidths = headers.map(function(_, idx) {
                return idx === 0 ? '6%' : '*';
            });

            const docDefinition = {
                pageOrientation: 'portrait',
                pageSize: 'A4',
                pageMargins: [20, 24, 20, 24],
                content: [
                    {
                        text: 'COMPENSATORY OFF RECORDS',
                        fontSize: 14,
                        bold: true,
                        alignment: 'center',
                        color: '#4B00E8',
                        margin: [0, 0, 0, 4]
                    },
                    {
                        text: 'Generated on: ' + new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString(),
                        fontSize: 8.5,
                        alignment: 'center',
                        color: '#64748B',
                        margin: [0, 0, 0, 12]
                    },
                    {
                        table: {
                            headerRows: 1,
                            widths: colWidths,
                            body: bodyRows
                        },
                        layout: {
                            hLineWidth: function() { return 0.5; },
                            vLineWidth: function() { return 0.5; },
                            hLineColor: function() { return '#E2E8F0'; },
                            vLineColor: function() { return '#E2E8F0'; },
                            paddingTop: function() { return 6; },
                            paddingBottom: function() { return 6; },
                            paddingLeft: function() { return 6; },
                            paddingRight: function() { return 6; }
                        }
                    }
                ]
            };

            pMake.createPdf(docDefinition).download('compensatory_off_records_' + new Date().toISOString().slice(0, 10) + '.pdf');
        } else if (format === 'print') {
            window.print();
        }
    }

    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return uri + separator + key + "=" + value;
        }
    }
</script>
