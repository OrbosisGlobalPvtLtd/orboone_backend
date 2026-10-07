@props([
    'table' => null,
    'csvUrl' => null,
    'excelUrl' => null,
    'pdfUrl' => null,
    'printUrl' => null,
    'buttons' => ['csv', 'excel', 'pdf', 'print'],
    'class' => '',
])

<div class="orbo-export-group {{ $class }}" data-export-group @if($table) data-target-table="{{ $table }}" @endif>
    @if(in_array('csv', $buttons))
        @if($csvUrl)
            <a href="{{ $csvUrl }}" class="orbo-export-btn btn-export-csv" title="Export CSV">
                <i class="fas fa-file-csv icon-csv"></i>
                <span>CSV</span>
            </a>
        @else
            <button type="button" class="orbo-export-btn btn-export-csv" data-export="csv" title="Export CSV">
                <i class="fas fa-file-csv icon-csv"></i>
                <span>CSV</span>
            </button>
        @endif
    @endif

    @if(in_array('excel', $buttons))
        @if($excelUrl)
            <a href="{{ $excelUrl }}" class="orbo-export-btn btn-export-excel" title="Export Excel">
                <i class="fas fa-file-excel icon-excel"></i>
                <span>Excel</span>
            </a>
        @else
            <button type="button" class="orbo-export-btn btn-export-excel" data-export="excel" title="Export Excel">
                <i class="fas fa-file-excel icon-excel"></i>
                <span>Excel</span>
            </button>
        @endif
    @endif

    @if(in_array('pdf', $buttons))
        @if($pdfUrl)
            <a href="{{ $pdfUrl }}" class="orbo-export-btn btn-export-pdf" title="Export PDF">
                <i class="fas fa-file-pdf icon-pdf"></i>
                <span>PDF</span>
            </a>
        @else
            <button type="button" class="orbo-export-btn btn-export-pdf" data-export="pdf" title="Export PDF">
                <i class="fas fa-file-pdf icon-pdf"></i>
                <span>PDF</span>
            </button>
        @endif
    @endif

    @if(in_array('print', $buttons))
        @if($printUrl)
            <a href="{{ $printUrl }}" class="orbo-export-btn btn-export-print" title="Print" target="_blank">
                <i class="fas fa-print icon-print"></i>
                <span>Print</span>
            </a>
        @else
            <button type="button" class="orbo-export-btn btn-export-print" data-export="print" title="Print">
                <i class="fas fa-print icon-print"></i>
                <span>Print</span>
            </button>
        @endif
    @endif
</div>
