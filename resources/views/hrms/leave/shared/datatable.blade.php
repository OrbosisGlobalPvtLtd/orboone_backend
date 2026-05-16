<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<style>
.dataTables_wrapper .dt-buttons {
    margin-bottom: 15px;
}
.dataTables_wrapper .dt-buttons .btn {
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 12px;
    margin-right: 5px;
}
.dataTables_wrapper .dataTables_length select {
    border-radius: 8px;
    border: 1px solid #E4E7EC;
    padding: 4px 8px;
}
.dataTables_wrapper .dataTables_filter input {
    border-radius: 8px;
    border: 1px solid #E4E7EC;
    padding: 6px 12px;
}
table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before, table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
    background-color: var(--orb-primary);
}
.dataTables_empty {
    padding: 40px !important;
    text-align: center !important;
    color: var(--orb-muted) !important;
    font-size: 14px;
    background: #FAFBFF !important;
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
            $('.js-datatable').DataTable({
                pageLength: 25,
                responsive: true,
                language: {
                    emptyTable: '<div class="py-4"><i class="fas fa-folder-open fa-3x mb-3 text-muted opacity-50"></i><br>No records found</div>',
                    loadingRecords: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
                },
                dom: "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend: 'excel', className: 'btn btn-light border shadow-sm' },
                    { extend: 'csv', className: 'btn btn-light border shadow-sm' },
                    { extend: 'pdf', className: 'btn btn-light border shadow-sm' },
                    { extend: 'print', className: 'btn btn-light border shadow-sm' }
                ],
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded justify-content-end mb-0');
                }
            });

            // Auto-filter for dropdowns with class .auto-filter
            $('.auto-filter').on('change', function() {
                var table = $('.js-datatable').DataTable();
                var colIdx = $(this).data('column-index');
                if (colIdx !== undefined) {
                    table.column(colIdx).search($(this).val()).draw();
                } else {
                    // if it's a form, we might submit it or use custom logic
                    $(this).closest('form').submit();
                }
            });
            
            // Reset filters
            $('.btn-reset-filters').on('click', function() {
                var table = $('.js-datatable').DataTable();
                table.search('').columns().search('').draw();
                $('.auto-filter').val('').trigger('change.select2'); // if using select2
            });
        }
    });
</script>
