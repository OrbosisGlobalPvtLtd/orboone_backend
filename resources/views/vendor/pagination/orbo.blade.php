@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $total = method_exists($paginator, 'total') ? $paginator->total() : $paginator->count();
    $firstItem = method_exists($paginator, 'firstItem') ? $paginator->firstItem() : 1;
    $lastItem = method_exists($paginator, 'lastItem') ? $paginator->lastItem() : $paginator->count();

    // Smart windowing: compact windowing (e.g. 1 2 3 ... 7)
    $pages = [];
    if ($lastPage <= 5) {
        for ($i = 1; $i <= $lastPage; $i++) {
            $pages[] = $i;
        }
    } else {
        if ($currentPage <= 2) {
            $pages = [1, 2, 3, '...', $lastPage];
        } elseif ($currentPage >= $lastPage - 1) {
            $pages = [1, '...', $lastPage - 2, $lastPage - 1, $lastPage];
        } else {
            $pages = [1, '...', $currentPage, '...', $lastPage];
        }
    }
@endphp

<div class="orb-pagination-wrapper">
    <div class="orb-pagination-info">
        @if(method_exists($paginator, 'total'))
            Showing {{ $paginator->firstItem() ?? ($paginator->count() > 0 ? 1 : 0) }} to {{ $paginator->lastItem() ?? $paginator->count() }} of {{ $paginator->total() ?? $paginator->count() }} entries
        @else
            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? $paginator->count() }} entries
        @endif
    </div>

    <nav role="navigation" aria-label="Pagination Navigation">
        <ul class="orb-pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="orb-page-item disabled" aria-disabled="true">
                    <span class="orb-page-link orb-page-prev">
                        <span class="d-none d-sm-inline">Previous</span>
                        <span class="d-inline d-sm-none">&lsaquo;</span>
                    </span>
                </li>
            @else
                <li class="orb-page-item">
                    <a class="orb-page-link orb-page-prev" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <span class="d-none d-sm-inline">Previous</span>
                        <span class="d-inline d-sm-none">&lsaquo;</span>
                    </a>
                </li>
            @endif

            {{-- Smart Page Numbers with Dots --}}
            @foreach ($pages as $p)
                @if ($p === '...')
                    <li class="orb-page-item disabled" aria-disabled="true">
                        <span class="orb-page-link orb-page-dots">&hellip;</span>
                    </li>
                @elseif ($p == $currentPage)
                    <li class="orb-page-item active" aria-current="page">
                        <span class="orb-page-link">{{ $p }}</span>
                    </li>
                @else
                    <li class="orb-page-item">
                        <a class="orb-page-link" href="{{ $paginator->url($p) }}">{{ $p }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="orb-page-item">
                    <a class="orb-page-link orb-page-next" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <span class="d-none d-sm-inline">Next</span>
                        <span class="d-inline d-sm-none">&rsaquo;</span>
                    </a>
                </li>
            @else
                <li class="orb-page-item disabled" aria-disabled="true">
                    <span class="orb-page-link orb-page-next">
                        <span class="d-none d-sm-inline">Next</span>
                        <span class="d-inline d-sm-none">&rsaquo;</span>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
</div>

<style>
    .orb-pagination-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        width: 100%;
        padding: 12px 18px 14px;
        background: #FFFFFF;
        border-top: 1px solid #EEF2F6;
        box-sizing: border-box;
        position: relative;
        z-index: 5;
    }

    .orb-pagination-info {
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-muted, #6B7280);
        white-space: nowrap;
    }

    .orb-pagination {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        list-style: none;
        padding: 0;
        margin: 0;
        flex-wrap: wrap;
    }

    .orb-page-item {
        margin: 0;
    }

    .orb-page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 12px;
        border-radius: 9px;
        border: 1px solid transparent;
        background: transparent;
        color: var(--orb-primary, #4B00E8);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none !important;
        transition: all 0.18s ease;
        cursor: pointer;
        user-select: none;
    }

    .orb-page-link:hover:not(.orb-page-dots) {
        background: var(--orb-soft, #F3EDFF);
        color: var(--orb-primary, #4B00E8);
    }

    .orb-page-item.active .orb-page-link {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)) !important;
        color: #FFFFFF !important;
        border-radius: 9px !important;
        font-weight: 800 !important;
        min-width: 34px;
        height: 34px;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.35) !important;
        border: none !important;
    }

    .orb-page-item.disabled .orb-page-link {
        color: #94A3B8 !important;
        cursor: not-allowed !important;
        background: transparent !important;
        opacity: 0.6;
    }

    .orb-page-dots {
        border: none !important;
        background: transparent !important;
        min-width: 18px;
        padding: 0 2px;
        color: #94A3B8;
        font-weight: 800;
        letter-spacing: 1px;
    }

    @media (max-width: 576px) {
        .orb-pagination-wrapper {
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 10px 4px;
            text-align: center;
        }
        .orb-pagination-info {
            font-size: 11px;
        }
        .orb-pagination {
            flex-wrap: nowrap;
            justify-content: center;
            gap: 3px;
            max-width: 100%;
            overflow-x: auto;
        }
        .orb-page-link {
            min-width: 28px;
            height: 28px;
            padding: 0 6px;
            font-size: 11.5px;
            border-radius: 7px;
        }
        .orb-page-item.active .orb-page-link {
            min-width: 28px;
            height: 28px;
            border-radius: 7px !important;
        }
        .orb-page-dots {
            min-width: 14px;
            font-size: 11px;
            padding: 0 1px;
        }
    }
</style>
