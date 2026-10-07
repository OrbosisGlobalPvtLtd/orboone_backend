@if ($paginator->hasPages())
    <div class="orb-pagination-wrapper">
        <div class="orb-pagination-info">
            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} entries
        </div>

        <nav role="navigation" aria-label="Pagination Navigation">
            <ul class="orb-pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="orb-page-item disabled" aria-disabled="true">
                        <span class="orb-page-link orb-page-prev">Previous</span>
                    </li>
                @else
                    <li class="orb-page-item">
                        <a class="orb-page-link orb-page-prev" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="orb-page-item">
                        <a class="orb-page-link orb-page-next" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
                    </li>
                @else
                    <li class="orb-page-item disabled" aria-disabled="true">
                        <span class="orb-page-link orb-page-next">Next</span>
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

        .orb-page-item.disabled .orb-page-link {
            color: #94A3B8 !important;
            cursor: not-allowed !important;
            background: transparent !important;
            opacity: 0.6;
        }

        @media (max-width: 576px) {
            .orb-pagination-wrapper {
                flex-direction: column;
                align-items: center;
                gap: 10px;
                text-align: center;
            }
            .orb-pagination {
                justify-content: center;
            }
        }
    </style>
@endif
