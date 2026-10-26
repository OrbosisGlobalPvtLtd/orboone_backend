<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<style>
.dataTables_wrapper .row {
    margin-left: 0 !important;
    margin-right: 0 !important;
}
.dataTables_wrapper .dt-toolbar {
    margin-bottom: 16px !important;
}
.dataTables_wrapper .dt-toolbar-actions {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
}
.dataTables_wrapper .dt-buttons {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 8px !important;
    margin-bottom: 0 !important;
    flex-wrap: wrap !important;
}
.dataTables_wrapper .dt-buttons .btn,
.dataTables_wrapper .dt-buttons .leave-light-btn {
    border: 1px solid var(--orb-border, #E7EAF3) !important;
    background: #FFFFFF !important;
    color: var(--orb-text, #101828) !important;
    border-radius: 12px !important;
    padding: 8px 14px !important;
    font-size: 12px !important;
    font-weight: 850 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    line-height: 1.4 !important;
    outline: none !important;
    margin: 0 !important;
}
.dataTables_wrapper .dt-buttons .btn:hover,
.dataTables_wrapper .dt-buttons .leave-light-btn:hover {
    background: var(--orb-soft, #F4F2FF) !important;
    color: var(--orb-primary, #4B00E8) !important;
    border-color: rgba(75, 0, 232, 0.25) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(75, 0, 232, 0.12) !important;
}
.dataTables_wrapper .dataTables_length label,
.dataTables_wrapper .dataTables_filter label {
    font-size: 13px;
    font-weight: 600;
    color: var(--orb-muted, #667085);
    margin-bottom: 0;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.dataTables_wrapper .dataTables_length select {
    border-radius: 10px;
    border: 1px solid #E4E7EC;
    padding: 5px 10px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--orb-text, #101828);
    background: #fff;
    outline: none;
}
.dataTables_wrapper .dataTables_filter input {
    border-radius: 10px;
    border: 1px solid #E4E7EC;
    padding: 6px 12px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--orb-text, #101828);
    outline: none;
    background: #fff;
    transition: all 0.2s ease;
}
.dataTables_wrapper .dataTables_filter input:focus,
.dataTables_wrapper .dataTables_length select:focus {
    border-color: var(--orb-primary, #4B00E8);
    box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08);
}
table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before, table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
    background-color: var(--orb-primary, #4B00E8);
}
.dataTables_empty {
    padding: 40px !important;
    text-align: center !important;
    color: var(--orb-muted, #667085) !important;
    font-size: 14px;
    background: #FAFBFF !important;
}

/* ==========================================================================
   Vendor Pagination Theme Integration (matches vendor/pagination/orbo.blade.php)
   ========================================================================== */
.orb-pagination-wrapper,
.dataTables_wrapper .row:last-child {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
    width: 100% !important;
    padding: 12px 18px 14px !important;
    background: #FFFFFF !important;
    border-top: 1px solid #EEF2F6 !important;
    box-sizing: border-box !important;
    margin: 0 !important;
    position: relative !important;
    z-index: 5 !important;
}

.orb-pagination-info,
.dataTables_wrapper .dataTables_info {
    font-size: 13px !important;
    font-weight: 600 !important;
    color: var(--orb-muted, #6B7280) !important;
    white-space: nowrap !important;
    padding: 0 !important;
    margin: 0 !important;
}

.dataTables_wrapper .dataTables_paginate,
.dataTables_paginate_box {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    padding: 0 !important;
    margin: 0 !important;
}

.orb-pagination,
.dataTables_paginate .pagination {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
    flex-wrap: wrap !important;
}

.orb-page-item,
.dataTables_paginate .pagination .page-item {
    margin: 0 !important;
    border: none !important;
    background: transparent !important;
}

.orb-page-link,
.dataTables_paginate .pagination .page-item .page-link,
.dataTables_paginate .paginate_button {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 34px !important;
    height: 34px !important;
    padding: 0 12px !important;
    border-radius: 9px !important;
    border: 1px solid transparent !important;
    background: transparent !important;
    color: var(--orb-primary, #4B00E8) !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.18s ease !important;
    cursor: pointer !important;
    user-select: none !important;
    box-shadow: none !important;
    outline: none !important;
}

.orb-page-link:hover:not(.orb-page-dots),
.dataTables_paginate .pagination .page-item:not(.active):not(.disabled) .page-link:hover,
.dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
    background: var(--orb-soft, #F3EDFF) !important;
    color: var(--orb-primary, #4B00E8) !important;
    border-color: transparent !important;
}

.orb-page-item.active .orb-page-link,
.dataTables_paginate .pagination .page-item.active .page-link,
.dataTables_paginate .paginate_button.current,
.dataTables_paginate .paginate_button.current:hover {
    background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)) !important;
    color: #FFFFFF !important;
    border-radius: 9px !important;
    font-weight: 800 !important;
    min-width: 34px !important;
    height: 34px !important;
    box-shadow: 0 4px 14px rgba(75, 0, 232, 0.35) !important;
    border: none !important;
    cursor: default !important;
}

.orb-page-item.disabled .orb-page-link,
.dataTables_paginate .pagination .page-item.disabled .page-link,
.dataTables_paginate .paginate_button.disabled,
.dataTables_paginate .paginate_button.disabled:hover {
    color: #94A3B8 !important;
    cursor: not-allowed !important;
    background: transparent !important;
    opacity: 0.6 !important;
    border-color: transparent !important;
    box-shadow: none !important;
}

.orb-page-dots,
.dataTables_paginate .pagination .page-item.disabled .page-link:not(.orb-page-prev):not(.orb-page-next) {
    border: none !important;
    background: transparent !important;
    min-width: 18px !important;
    padding: 0 2px !important;
    color: #94A3B8 !important;
    font-weight: 800 !important;
    letter-spacing: 1px !important;
}

@media (max-width: 576px) {
    .orb-pagination-wrapper,
    .dataTables_wrapper .row:last-child {
        flex-direction: column !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 10px 4px !important;
        text-align: center !important;
    }
    .orb-pagination-info,
    .dataTables_wrapper .dataTables_info {
        font-size: 11px !important;
    }
    .orb-pagination,
    .dataTables_paginate .pagination {
        flex-wrap: nowrap !important;
        justify-content: center !important;
        gap: 3px !important;
        max-width: 100% !important;
        overflow-x: auto !important;
    }
    .orb-page-link,
    .dataTables_paginate .pagination .page-item .page-link,
    .dataTables_paginate .paginate_button {
        min-width: 28px !important;
        height: 28px !important;
        padding: 0 6px !important;
        font-size: 11.5px !important;
        border-radius: 7px !important;
    }
    .orb-page-item.active .orb-page-link,
    .dataTables_paginate .pagination .page-item.active .page-link,
    .dataTables_paginate .paginate_button.current {
        min-width: 28px !important;
        height: 28px !important;
        border-radius: 7px !important;
    }
    .orb-page-dots {
        min-width: 14px !important;
        font-size: 11px !important;
        padding: 0 1px !important;
    }
}
</style>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.DataTable) {
            // Set error mode to throw/none to prevent ugly browser alert boxes
            $.fn.dataTable.ext.errMode = 'none';

            $('.js-datatable').each(function() {
                var $table = $(this);
                // Only initialize DataTable if the table is not empty (no td with colspan, and has rows)
                if ($table.find('tbody tr').length > 0 && $table.find('tbody td[colspan]').length === 0) {
                    $table.DataTable({
                        pageLength: 25,
                        responsive: true,
                        language: {
                            emptyTable: '<div class="py-4"><i class="fas fa-folder-open fa-3x mb-3 text-muted opacity-50"></i><br>No records found</div>',
                            loadingRecords: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
                            paginate: {
                                previous: '<span class="d-none d-sm-inline">Previous</span><span class="d-inline d-sm-none">&lsaquo;</span>',
                                next: '<span class="d-none d-sm-inline">Next</span><span class="d-inline d-sm-none">&rsaquo;</span>'
                            }
                        },
                        dom: "<'row dt-toolbar align-items-center mb-3 px-1'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 dt-toolbar-actions'B>>" +
                             "<'row'<'col-sm-12'tr>>" +
                             "<'orb-pagination-wrapper'<'orb-pagination-info'i><'dataTables_paginate_box'p>>",
                        buttons: [
                            { extend: 'csv', text: '<i class="fas fa-file-csv text-primary"></i> CSV', className: 'btn leave-light-btn' },
                            { extend: 'excel', text: '<i class="fas fa-file-excel text-success"></i> Excel', className: 'btn leave-light-btn' },
                            { extend: 'pdf', text: '<i class="fas fa-file-pdf text-danger"></i> PDF', className: 'btn leave-light-btn' },
                            { extend: 'print', text: '<i class="fas fa-print text-secondary"></i> Print', className: 'btn leave-light-btn' }
                        ],
                        drawCallback: function() {
                            var $wrapper = $(this).closest('.dataTables_wrapper');
                            $wrapper.find('.dataTables_paginate > .pagination').addClass('orb-pagination');
                            $wrapper.find('.dataTables_paginate .page-item').addClass('orb-page-item');
                            $wrapper.find('.dataTables_paginate .page-link').addClass('orb-page-link');
                            $wrapper.find('.dataTables_paginate .previous .page-link').addClass('orb-page-prev');
                            $wrapper.find('.dataTables_paginate .next .page-link').addClass('orb-page-next');
                            $wrapper.find('.dataTables_info').addClass('orb-pagination-info');
                        }
                    });
                }
            });

            // Reset filters
            $('.btn-reset-filters').on('click', function() {
                var table = $('.js-datatable').DataTable();
                if ($.fn.DataTable.isDataTable(table)) {
                    table.search('').columns().search('').draw();
                }
                $('.auto-filter').val('').trigger('change.select2'); // if using select2
            });
        }
    });
</script>
