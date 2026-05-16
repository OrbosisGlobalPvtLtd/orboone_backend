<script>
    if (window.jQuery && $.fn.DataTable) {
        $('.js-datatable').DataTable({
            pageLength: 25,
            dom: 'Bfrtip',
            buttons: ['excel', 'csv', 'print']
        });
    }
</script>
