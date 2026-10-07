@push('scripts')
<script>
    function selectEmployeeForShift(empId) {
        document.getElementById('assignEmployeeSelect').value = empId;
    }

    function handleShiftTemplateSelect(context, selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        let panel;
        if (context === 'assign') {
            panel = document.getElementById('assignFlexibleSection');
        } else if (context.startsWith('edit_')) {
            const id = context.replace('edit_', '');
            panel = document.getElementById('editFlexibleSection' + id);
        }

        if (!panel) return;

        const shiftType = selectedOption.getAttribute('data-shift-type') || '';
        const punchFrom = selectedOption.getAttribute('data-punch-allowed') || '';
        const shiftStart = selectedOption.getAttribute('data-shift-start') || '';
        const lateAfter = selectedOption.getAttribute('data-late-after') || '';
        const blockAfter = selectedOption.getAttribute('data-block-after') || '';
        const halfDayAfter = selectedOption.getAttribute('data-half-day-after') || '';
        const shiftEnd = selectedOption.getAttribute('data-shift-end') || '';
        const reqMins = selectedOption.getAttribute('data-req-mins') || '480';
        const lunchMins = selectedOption.getAttribute('data-lunch-mins') || '60';

        const clockTimeFields = panel.querySelectorAll('.clock-time-field');
        const punchInput = panel.querySelector('input[name="punch_allowed_from"]');
        const startInput = panel.querySelector('input[name="shift_start_time"]');
        const lateInput = panel.querySelector('input[name="late_after_time"]');
        const blockInput = panel.querySelector('input[name="block_after_time"]');
        const halfDayInput = panel.querySelector('input[name="half_day_after_time"]');
        const endInput = panel.querySelector('input[name="shift_end_time"]');
        const reqInput = panel.querySelector('input[name="required_work_minutes"]');
        const lunchInput = panel.querySelector('input[name="lunch_minutes"]');

        if (shiftType === 'dynamic_hours') {
            clockTimeFields.forEach(f => {
                f.style.display = 'none';
            });
            if (punchInput) punchInput.value = '';
            if (startInput) startInput.value = '';
            if (lateInput) lateInput.value = '';
            if (blockInput) blockInput.value = '';
            if (halfDayInput) halfDayInput.value = '';
            if (endInput) endInput.value = '';
        } else {
            clockTimeFields.forEach(f => {
                f.style.display = '';
            });
            if (punchInput && punchFrom) punchInput.value = punchFrom;
            if (startInput && shiftStart) startInput.value = shiftStart;
            if (lateInput && lateAfter) lateInput.value = lateAfter;
            if (blockInput && blockAfter) blockInput.value = blockAfter;
            if (halfDayInput && halfDayAfter) halfDayInput.value = halfDayAfter;
            if (endInput && shiftEnd) endInput.value = shiftEnd;
        }

        if (reqInput && reqMins) reqInput.value = reqMins;
        if (lunchInput && lunchMins !== null && lunchMins !== undefined) lunchInput.value = lunchMins;

        updateAllTimeDisplaysInScope(panel);
    }

    function formatTimeTo12Hour(timeStr) {
        if (!timeStr) return '--:--';
        const parts = timeStr.split(':');
        if (parts.length < 2) return '--:--';
        let hours = Number(parts[0]);
        const minutes = Number(parts[1]);
        if (isNaN(hours) || isNaN(minutes)) return '--:--';

        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        const strMinutes = String(minutes).padStart(2, '0');
        const strHours = String(hours).padStart(2, '0');
        return `${strHours}:${strMinutes} ${ampm}`;
    }

    function addMinutesToTime(timeStr, minutesToAdd) {
        if (!timeStr) return '';
        const parts = timeStr.split(':');
        if (parts.length < 2) return '';
        const hours = Number(parts[0]);
        const minutes = Number(parts[1]);
        const date = new Date();
        date.setHours(hours);
        date.setMinutes(minutes + minutesToAdd);
        date.setSeconds(0);

        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    }

    function updateAllTimeDisplaysInScope(scopeEl) {
        if (!scopeEl) return;
        const inputs = scopeEl.querySelectorAll('input[type="time"]');
        inputs.forEach(input => {
            const overlay = input.parentNode.querySelector('.time-display-val');
            if (overlay) {
                overlay.textContent = formatTimeTo12Hour(input.value);
            }
        });
    }

    function autoCalculateTimingsForScope(scopeEl) {
        if (!scopeEl) return;
        const shiftStartInput   = scopeEl.querySelector('input[name="shift_start_time"]');
        const lateAfterInput    = scopeEl.querySelector('input[name="late_after_time"]');
        const halfDayAfterInput = scopeEl.querySelector('input[name="half_day_after_time"]');
        const shiftEndInput     = scopeEl.querySelector('input[name="shift_end_time"]');
        const reqMinutesInput   = scopeEl.querySelector('input[name="required_work_minutes"]');
        const lunchMinutesInput = scopeEl.querySelector('input[name="lunch_minutes"]');
        const punchAllowedInput = scopeEl.querySelector('input[name="punch_allowed_from"]');
        const blockedPunchInput = scopeEl.querySelector('input[name="block_after_time"]');

        if (!shiftStartInput) return;

        const startTime = shiftStartInput.value;
        if (!startTime) return;

        // 1. Late After: Start + 65 mins
        if (lateAfterInput) {
            lateAfterInput.value = addMinutesToTime(startTime, 65);
        }

        // 2. Half Day After: Start + 240 mins (4 hours)
        if (halfDayAfterInput) {
            halfDayAfterInput.value = addMinutesToTime(startTime, 240);
        }

        // 3. Punch Allowed: Start - 60 mins (1 hour before)
        if (punchAllowedInput) {
            punchAllowedInput.value = addMinutesToTime(startTime, -60);
        }

        // 4. Shift End Time: Start + Required Minutes + Lunch Minutes
        const reqMin = Number(reqMinutesInput?.value || 0);
        const lunchMin = Number(lunchMinutesInput?.value || 0);
        if (shiftEndInput && (reqMin > 0 || lunchMin > 0)) {
            shiftEndInput.value = addMinutesToTime(startTime, reqMin + lunchMin);
        }

        // 5. Blocked Punch: Start + 75 mins
        if (blockedPunchInput) {
            blockedPunchInput.value = addMinutesToTime(startTime, 75);
        }

        updateAllTimeDisplaysInScope(scopeEl);
    }

    function syncAllShiftTemplateVisibilities() {
        document.querySelectorAll('#assignShiftSelect, select[id^="editShiftSelect"]').forEach(selectEl => {
            const selectedOption = selectEl.options[selectEl.selectedIndex];
            if (!selectedOption) return;

            let panel;
            if (selectEl.id === 'assignShiftSelect') {
                panel = document.getElementById('assignFlexibleSection');
            } else if (selectEl.id && selectEl.id.startsWith('editShiftSelect')) {
                const id = selectEl.id.replace('editShiftSelect', '');
                panel = document.getElementById('editFlexibleSection' + id);
            }
            if (!panel) return;

            const shiftType = selectedOption.getAttribute('data-shift-type') || '';
            const clockTimeFields = panel.querySelectorAll('.clock-time-field');
            if (shiftType === 'dynamic_hours') {
                clockTimeFields.forEach(f => f.style.display = 'none');
            } else {
                clockTimeFields.forEach(f => f.style.display = '');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize template visibility states
        syncAllShiftTemplateVisibilities();

        // Listen for any modal opened via Bootstrap to re-sync
        if (window.jQuery) {
            window.jQuery('.modal').on('show.bs.modal shown.bs.modal', function() {
                syncAllShiftTemplateVisibilities();
            });
        }

        // Initialize time overlays for all card sections
        document.querySelectorAll('.card').forEach(panel => {
            updateAllTimeDisplaysInScope(panel);

            // Bind input/change listeners for time displays & auto-calculation
            const timeInputs = panel.querySelectorAll('input[type="time"]');
            timeInputs.forEach(input => {
                input.addEventListener('input', function() {
                    updateAllTimeDisplaysInScope(panel);
                });
                input.addEventListener('change', function() {
                    updateAllTimeDisplaysInScope(panel);
                });
            });

            // Bind auto calculation on shift_start_time, required_work_minutes, lunch_minutes
            const startInput = panel.querySelector('input[name="shift_start_time"]');
            const reqInput   = panel.querySelector('input[name="required_work_minutes"]');
            const lunchInput = panel.querySelector('input[name="lunch_minutes"]');

            if (startInput) {
                startInput.addEventListener('input', () => autoCalculateTimingsForScope(panel));
                startInput.addEventListener('change', () => autoCalculateTimingsForScope(panel));
            }
            if (reqInput) {
                reqInput.addEventListener('input', () => autoCalculateTimingsForScope(panel));
                reqInput.addEventListener('change', () => autoCalculateTimingsForScope(panel));
            }
            if (lunchInput) {
                lunchInput.addEventListener('input', () => autoCalculateTimingsForScope(panel));
                lunchInput.addEventListener('change', () => autoCalculateTimingsForScope(panel));
            }
        });

        // Submit filter form on length dropdown change
        $(document).on('change select2:select', '#shiftLengthSelect', function() {
            const val = $(this).val();
            $('#shiftPerPageInput').val(val);
            $('#shiftFilterForm').submit();
        });

        // Shift Assignment Table Export Handler (CSV, Excel, PDF, Print)
        let isExporting = false;
        $(document).off('click', '#shiftAssignmentExportButtons [data-export]').on('click', '#shiftAssignmentExportButtons [data-export]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            if (isExporting) return;
            isExporting = true;
            setTimeout(() => { isExporting = false; }, 800);

            const exportType = $(this).attr('data-export');
            const table = document.getElementById('shiftAssignmentTable');
            if (!table) return;

            if (exportType === 'print') {
                const printWindow = window.open('', '', 'height=700,width=950');
                printWindow.document.write('<html><head><title>Shift Assignments</title>');
                printWindow.document.write('<style>body{font-family:sans-serif;padding:20px;} table{width:100%;border-collapse:collapse;margin-top:14px;font-size:12px;} th,td{border:1px solid #ddd;padding:8px 10px;text-align:left;} th{background:#f8f9fa;font-weight:bold;} .badge{background:transparent!important;border:none!important;padding:0!important;font-weight:600;} .hrms-emp-avatar{display:none;}</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write('<h2 style="margin:0 0 4px 0;color:#101828;">Shift Assignments List</h2>');
                printWindow.document.write('<p style="margin:0 0 16px 0;color:#667085;font-size:13px;">Generated on ' + new Date().toLocaleDateString() + '</p>');
                
                const cloneTable = table.cloneNode(true);
                cloneTable.querySelectorAll('tr').forEach(tr => {
                    if (tr.children.length > 1) {
                        tr.removeChild(tr.lastElementChild); // Remove action column
                    }
                });
                printWindow.document.write(cloneTable.outerHTML);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => { printWindow.print(); printWindow.close(); }, 350);
            } else if (exportType === 'csv' || exportType === 'excel') {
                let csv = [];
                const rows = table.querySelectorAll('tr');
                for (let i = 0; i < rows.length; i++) {
                    let row = [], cols = rows[i].querySelectorAll('td, th');
                    for (let j = 0; j < cols.length - 1; j++) { // Omit actions
                        let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                        text = text.replace(/"/g, '""');
                        row.push('"' + text + '"');
                    }
                    if (row.length > 0) csv.push(row.join(','));
                }
                const mimeType = exportType === 'csv' ? 'text/csv;charset=utf-8;' : 'application/vnd.ms-excel;charset=utf-8;';
                const blob = new Blob(["\uFEFF" + csv.join('\n')], { type: mimeType });
                const downloadLink = document.createElement('a');
                downloadLink.download = 'shift_assignments_' + new Date().toISOString().slice(0, 10) + (exportType === 'csv' ? '.csv' : '.xls');
                downloadLink.href = window.URL.createObjectURL(blob);
                downloadLink.style.display = 'none';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                setTimeout(() => {
                    if (downloadLink.parentNode) {
                        downloadLink.parentNode.removeChild(downloadLink);
                    }
                    window.URL.revokeObjectURL(downloadLink.href);
                }, 200);
            } else if (exportType === 'pdf') {
                if (typeof pdfMake === 'undefined') {
                    window.print();
                    return;
                }

                const headers = [];
                const thCols = table.querySelectorAll('thead tr th');
                for (let j = 0; j < thCols.length - 1; j++) { // omit Actions
                    headers.push({
                        text: thCols[j].innerText.replace(/\s+/g, ' ').trim(),
                        bold: true,
                        fillColor: '#F1F5F9',
                        color: '#0F172A',
                        fontSize: 8.5
                    });
                }

                const bodyRows = [headers];
                const trRows = table.querySelectorAll('tbody tr');

                trRows.forEach((tr, rowIndex) => {
                    const tdCols = tr.querySelectorAll('td');
                    if (tdCols.length > 1) {
                        const rowData = [];
                        for (let j = 0; j < tdCols.length - 1; j++) { // omit Actions
                            let cellText = tdCols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                            rowData.push({
                                text: cellText || '-',
                                fontSize: 7.5,
                                fillColor: rowIndex % 2 === 0 ? '#FFFFFF' : '#F8FAFC',
                                color: '#1E293B'
                            });
                        }
                        bodyRows.push(rowData);
                    }
                });

                const docDefinition = {
                    pageOrientation: 'landscape',
                    pageSize: 'A4',
                    pageMargins: [14, 18, 14, 18],
                    content: [
                        {
                            text: 'EMPLOYEE SHIFT ASSIGNMENT REPORT',
                            fontSize: 13,
                            bold: true,
                            alignment: 'center',
                            color: '#0F172A',
                            margin: [0, 0, 0, 4]
                        },
                        {
                            text: 'Generated on: ' + new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString(),
                            fontSize: 8.5,
                            alignment: 'center',
                            color: '#64748B',
                            margin: [0, 0, 0, 10]
                        },
                        {
                            table: {
                                headerRows: 1,
                                widths: ['4%', '15%', '13%', '8%', '9%', '8%', '8%', '8%', '7%', '9%', '9%', '7%'],
                                body: bodyRows
                            },
                            layout: {
                                hLineWidth: function() { return 0.5; },
                                vLineWidth: function() { return 0.5; },
                                hLineColor: function() { return '#E2E8F0'; },
                                vLineColor: function() { return '#E2E8F0'; },
                                paddingTop: function() { return 4; },
                                paddingBottom: function() { return 4; },
                                paddingLeft: function() { return 4; },
                                paddingRight: function() { return 4; }
                            }
                        }
                    ]
                };

                const fileName = 'shift_assignments_' + new Date().toISOString().slice(0, 10) + '.pdf';
                pdfMake.createPdf(docDefinition).download(fileName);
            }
        });
    });
</script>
@endpush
