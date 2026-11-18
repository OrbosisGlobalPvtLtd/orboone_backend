<style>
    :root {
        --ep-primary: var(--orb-primary, #4B00E8);
        --ep-secondary: var(--orb-secondary, #FF5252);
        --ep-bg: #F6F7FB;
        --ep-card: #FFFFFF;
        --ep-border: #E7EAF3;
        --ep-text: #101828;
        --ep-muted: #667085;
        --ep-soft: #F4F2FF;
        --ep-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        background: var(--ep-bg) !important;
        overflow-x: hidden !important;
    }

    .ep-page {
        padding: 24px !important;
        background: #F6F7FB !important;
        min-height: calc(100vh - 90px) !important;
        box-sizing: border-box !important;
    }

    @media (max-width: 991px) {
        .ep-page {
            padding: 18px !important;
        }
    }

    @media (max-width: 575px) {
        .ep-page {
            padding: 12px !important;
        }
    }

    /* PREMIUM HERO GRADIENT CARD */
    .ep-hero {
        background: linear-gradient(135deg, var(--ep-primary), var(--ep-secondary)) !important;
        border-radius: 26px !important;
        color: #fff !important;
        padding: 24px 28px !important;
        margin-bottom: 24px !important;
        box-shadow: 0 20px 45px rgba(75, 0, 232, .22) !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 16px !important;
        flex-wrap: wrap !important;
        border: none !important;
    }

    .ep-hero h1 {
        color: #fff !important;
        font-size: 28px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        letter-spacing: -0.02em !important;
    }

    .ep-hero p {
        color: rgba(255, 255, 255, .82) !important;
        margin: 6px 0 0 !important;
        font-size: 13px !important;
        font-weight: 500 !important;
    }

    .ep-kicker {
        display: inline-flex !important;
        gap: 8px !important;
        align-items: center !important;
        background: rgba(255, 255, 255, .14) !important;
        padding: 6px 12px !important;
        border-radius: 999px !important;
        font-size: 11px !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        margin-bottom: 10px !important;
        letter-spacing: 0.05em !important;
    }

    /* PREMIUM CARD DEFINITIONS */
    .ep-card {
        background: #fff !important;
        border: 1px solid var(--ep-border) !important;
        border-radius: 22px !important;
        box-shadow: var(--ep-shadow) !important;
        margin-bottom: 24px !important;
        overflow: visible !important;
    }

    .ep-card-body {
        padding: 24px !important;
    }

    /* SUMMARY STATS GRID */
    .ep-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 18px !important;
        margin-bottom: 24px !important;
    }

    @media (max-width: 991px) {
        .ep-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 767px) {
        .ep-grid {
            grid-template-columns: 1fr !important;
        }
        .ep-hero h1 {
            font-size: 22px !important;
        }
    }

    .ep-stat {
        background: #fff !important;
        border: 1px solid var(--ep-border) !important;
        border-radius: 22px !important;
        padding: 20px 24px !important;
        box-shadow: 0 10px 30px rgba(16, 24, 40, .03) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .ep-stat:hover {
        transform: translateY(-2px) !important;
        box-shadow: var(--ep-shadow) !important;
    }

    .ep-stat-label {
        color: var(--ep-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        margin-bottom: 8px !important;
        letter-spacing: 0.05em !important;
    }

    .ep-stat-value {
        color: var(--ep-text) !important;
        font-size: 28px !important;
        font-weight: 900 !important;
        letter-spacing: -0.02em !important;
        margin-top: auto !important;
    }

    /* COMPACT BUTTON STYLING */
    .ep-btn {
        border: 0 !important;
        border-radius: 9px !important;
        height: 38px !important;
        padding: 0 16px !important;
        font-size: 13px !important;
        font-weight: 850 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
        white-space: nowrap !important;
        text-decoration: none !important;
    }

    .ep-btn-primary {
        background: #fff !important;
        color: var(--ep-primary) !important;
        border: 1px solid var(--ep-border) !important;
    }

    .ep-btn-primary:hover {
        background: var(--ep-soft) !important;
        color: var(--ep-primary) !important;
        border-color: rgba(75, 0, 232, 0.22) !important;
    }

    .ep-btn-gradient {
        background: linear-gradient(135deg, var(--ep-primary), var(--ep-secondary)) !important;
        color: #fff !important;
    }

    .ep-btn-gradient:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 8px 20px rgba(75, 0, 232, 0.22) !important;
    }

    .ep-btn-light {
        background: #fff !important;
        color: var(--ep-text) !important;
        border: 1px solid var(--ep-border) !important;
    }

    .ep-btn-light:hover {
        background: #F9FAFB !important;
    }

    /* RESPONSIVE SCROLLABLE TABLES */
    .ep-table-wrap {
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        background: #fff !important;
        margin: 0 !important;
    }

    /* When DataTable is active, outer wrapper must NOT scroll so toolbar and pagination stay 100% fixed card width */
    .ep-table-wrap:has(.dataTables_wrapper),
    .ep-table-wrap:has(.ep-table-scroll),
    .ep-table-wrap.ep-table-dt-active {
        overflow-x: visible !important;
        overflow-y: visible !important;
        overflow: visible !important;
    }

    /* Isolated scroll area wrapping ONLY the table element - horizontal scrollbar stays strictly under the rows */
    .ep-table-scroll {
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        background: #fff !important;
        margin: 0 !important;
    }

    .ep-table {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    .ep-table th {
        background: #F9FAFB !important;
        color: #475467 !important;
        font-size: 11px !important;
        text-transform: uppercase !important;
        font-weight: 900 !important;
        letter-spacing: 0.05em !important;
        border-bottom: 1px solid var(--ep-border) !important;
        padding: 14px 16px !important;
        white-space: nowrap !important;
    }

    .ep-table td {
        font-size: 13px !important;
        font-weight: 650 !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
        border-bottom: 1px solid #F2F4F7 !important;
        padding: 14px 16px !important;
    }

    .ep-table tr:last-child td {
        border-bottom: none !important;
    }

    /* STATUS PILL BADGES */
    .ep-badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 999px !important;
        padding: 5px 12px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.03em !important;
        white-space: nowrap !important;
    }

    .ep-badge-success, .ep-badge-approved, .ep-badge-paid {
        background: #ECFDF3 !important;
        color: #027A48 !important;
        border: 1px solid #ABEFC6 !important;
    }

    .ep-badge-warning, .ep-badge-pending, .ep-badge-draft {
        background: #FFFAEB !important;
        color: #B54708 !important;
        border: 1px solid #FEDF89 !important;
    }

    .ep-badge-danger, .ep-badge-rejected, .ep-badge-cancelled {
        background: #FEF3F2 !important;
        color: #B42318 !important;
        border: 1px solid #FECDCA !important;
    }

    .ep-badge-primary, .ep-badge-generated, .ep-badge-processed {
        background: var(--ep-soft) !important;
        color: var(--ep-primary) !important;
        border: 1px solid rgba(75, 0, 232, .12) !important;
    }

    /* FORMS & INPUT ALIGNMENTS */
    .ep-form label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--ep-muted) !important;
        margin-bottom: 6px !important;
        display: block !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    .ep-form .form-control, .ep-form .custom-select {
        display: block !important;
        width: 100% !important;
        height: 40px !important;
        padding: 8px 14px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        line-height: 1.5 !important;
        color: var(--ep-text) !important;
        background-color: #ffffff !important;
        border: 1px solid var(--ep-border) !important;
        border-radius: 12px !important;
        box-shadow: none !important;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out !important;
        outline: none !important;
    }

    .ep-form .form-control:focus, .ep-form .custom-select:focus {
        border-color: var(--ep-primary) !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1) !important;
    }

    /* MODALS & POPUPS */
    .modal {
        z-index: 30000 !important;
        overflow-y: hidden !important;
        overflow-x: hidden !important;
    }

    .modal-backdrop {
        z-index: 29990 !important;
    }

    .modal-dialog {
        max-height: calc(100vh - 40px) !important;
        margin: 20px auto !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .modal-dialog.modal-dialog-centered,
    .modal-dialog-centered {
        min-height: calc(100% - 40px) !important;
        margin-top: auto !important;
        margin-bottom: auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .modal-dialog-scrollable {
        max-height: calc(100vh - 40px) !important;
        display: flex !important;
        flex-direction: column !important;
    }
    .modal-dialog-scrollable.modal-dialog-centered {
        margin-top: auto !important;
        margin-bottom: auto !important;
    }
    .modal-dialog .modal-content,
    .modal-dialog-scrollable .modal-content,
    .ep-modal-content-scrollable {
        max-height: calc(100vh - 50px) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        border-radius: 24px !important;
        width: 100% !important;
    }

    .ep-modal-header {
        background: linear-gradient(135deg, var(--ep-primary), var(--ep-secondary)) !important;
        color: #fff !important;
        padding: 20px 26px !important;
        border-radius: 24px 24px 0 0 !important;
        border: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-shrink: 0 !important;
        position: relative !important;
        z-index: 10 !important;
    }

    .ep-modal-header .modal-title {
        font-weight: 900 !important;
        font-size: 1.25rem !important;
        color: #fff !important;
        margin: 0 !important;
        letter-spacing: -0.01em !important;
    }

    .ep-modal-header p {
        font-size: 13px !important;
        color: rgba(255, 255, 255, 0.8) !important;
        margin-top: 4px !important;
        margin-bottom: 0 !important;
        font-weight: 500 !important;
    }

    .ep-modal-header .close {
        color: #fff !important;
        opacity: 1 !important;
        text-shadow: none !important;
        font-size: 24px !important;
        font-weight: 300 !important;
        margin: 0 !important;
        outline: none !important;
        border: 0 !important;
        background: transparent !important;
    }

    .ep-modal-body,
    .modal-dialog-scrollable .ep-modal-body,
    .ep-modal-content-scrollable .ep-modal-body {
        background: #F6F7FB !important;
        padding: 24px 26px !important;
        border-radius: 0 !important;
        flex: 1 1 auto !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        max-height: calc(100vh - 190px) !important;
        overscroll-behavior: contain !important;
    }

    .ep-section-card {
        background: #fff !important;
        border-radius: 20px !important;
        padding: 20px !important;
        margin-bottom: 16px !important;
        border: 1px solid var(--ep-border) !important;
        box-shadow: none !important;
    }

    .ep-section-title {
        font-size: 13px !important;
        font-weight: 850 !important;
        text-transform: uppercase !important;
        color: var(--ep-text) !important;
        margin-bottom: 16px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        letter-spacing: 0.05em !important;
        border-bottom: 1px solid var(--ep-border) !important;
        padding-bottom: 8px !important;
    }

    .ep-section-title i {
        font-size: 14px !important;
        color: var(--ep-primary) !important;
    }

    .ep-modal-footer,
    .modal-dialog-scrollable .ep-modal-footer,
    .ep-modal-content-scrollable .ep-modal-footer {
        background: #fff !important;
        border-top: 1px solid var(--ep-border) !important;
        padding: 16px 26px !important;
        border-radius: 0 0 24px 24px !important;
        flex-shrink: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 10px !important;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05) !important;
        position: relative !important;
        z-index: 10 !important;
    }

    .ep-modal-body::-webkit-scrollbar {
        width: 6px;
    }
    .ep-modal-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .ep-modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .ep-modal-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* COMPACT FILTERS & SEARCH ROW */
    .ep-filter-card {
        background: #fff !important;
        padding: 18px !important;
        border-radius: 20px !important;
        margin-bottom: 24px !important;
        border: 1px solid var(--ep-border) !important;
        box-shadow: var(--ep-shadow) !important;
    }

    .ep-hero-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 12px !important;
        flex-wrap: wrap !important;
    }

    .ep-hero-actions .ep-btn {
        min-height: 40px !important;
        padding: 0 18px !important;
        border-radius: 12px !important;
        font-weight: 800 !important;
        white-space: nowrap !important;
    }

    @media (max-width: 768px) {
        .ep-hero-actions {
            justify-content: flex-start !important;
            margin-top: 16px !important;
            width: 100% !important;
        }
    }

    .ep-action-group {
        display: flex !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
    }

    .ep-table-search {
        max-width: 250px !important;
        border-radius: 10px !important;
    }

    .ep-export-btn {
        background: #fff !important;
        border: 1px solid var(--ep-border) !important;
        color: var(--ep-text) !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        border-radius: 8px !important;
        margin-left: 5px !important;
        padding: 8px 14px !important;
    }

    .ep-export-btn:hover {
        background: var(--ep-soft) !important;
        color: var(--ep-primary) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
    }

    .ep-empty-state {
        text-align: center !important;
        padding: 40px 20px !important;
        background: #fff !important;
        border-radius: 20px !important;
        border: 1px dashed var(--ep-border) !important;
        margin: 20px 0 !important;
    }

    .ep-empty-state i {
        color: var(--ep-muted) !important;
        margin-bottom: 15px !important;
    }

    /* TABLE HEADERS & ICONS */
    .ep-table-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 20px 24px !important;
        border-bottom: 1px solid var(--ep-border) !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }

    .ep-table-head-left {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
    }

    .ep-table-title {
        font-size: 18px !important;
        font-weight: 900 !important;
        color: var(--ep-text) !important;
        margin: 0 !important;
        letter-spacing: -0.01em !important;
    }

    .ep-table-subtitle {
        font-size: 13px !important;
        color: var(--ep-muted) !important;
        margin: 4px 0 0 0 !important;
    }

    .ep-icon-box {
        width: 46px !important;
        height: 46px !important;
        border-radius: 50% !important; /* Soft purple circular icon */
        background: var(--ep-soft) !important;
        color: var(--ep-primary) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
    }

    /* ATTACHED FILTERS ROW */
    .ep-card-filters {
        background: #F8FAFC !important;
        padding: 16px 20px !important;
        border-bottom: 1px solid var(--ep-border) !important;
        box-sizing: border-box !important;
    }

    .ep-card-filters label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--ep-muted) !important;
        margin-bottom: 6px !important;
        display: block !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    .ep-card-filters .form-control, .ep-card-filters select {
        height: 38px !important;
        padding: 6px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        border: 1px solid var(--ep-border) !important;
        border-radius: 9px !important;
        outline: none !important;
    }

    .ep-card-filters .form-control:focus, .ep-card-filters select:focus {
        border-color: var(--ep-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    /* DATATABLE CUSTOM TOOLBAR OVERLAY FOR ENTERPRISE PAYROLL */
    .crud-dt-toolbar {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        flex-wrap: nowrap !important;
        margin: 0 !important;
        width: 100% !important;
        padding: 10px 18px !important;
        background: #fff !important;
        border-bottom: 1px solid var(--ep-border) !important;
        box-sizing: border-box !important;
        position: sticky !important;
        left: 0 !important;
        z-index: 4 !important;
    }

    .crud-dt-left {
        display: inline-flex !important;
        align-items: center !important;
        width: auto !important;
        max-width: none !important;
        flex: 0 0 auto !important;
        padding: 0 !important;
        gap: 7px !important;
    }

    .crud-dt-right {
        display: inline-flex !important;
        align-items: center !important;
        width: auto !important;
        max-width: none !important;
        flex: 0 0 auto !important;
        padding: 0 !important;
        margin-left: auto !important;
        justify-content: flex-end !important;
        gap: 6px !important;
    }

    .crud-export-btn {
        width: auto !important;
        min-width: auto !important;
        max-width: none !important;
        height: 32px !important;
        min-height: 32px !important;
        padding: 0 10px !important;
        border-radius: 9px !important;
        border: 1px solid var(--ep-border) !important;
        background: #fff !important;
        color: var(--ep-text) !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        line-height: 30px !important;
        box-shadow: none !important;
        margin: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        white-space: nowrap !important;
        transition: all .2s ease !important;
        cursor: pointer !important;
    }

    .crud-export-btn:hover {
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, .22) !important;
    }

    .dataTables_wrapper .dataTables_length {
        display: flex !important;
        align-items: center !important;
        margin: 0 !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        color: var(--ep-muted) !important;
        white-space: nowrap !important;
    }

    .dataTables_wrapper .dataTables_length select {
        width: auto !important;
        min-width: 64px !important;
        height: 32px !important;
        border: 1px solid var(--ep-border) !important;
        border-radius: 9px !important;
        padding: 2px 24px 2px 8px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        background: #fff !important;
        color: var(--ep-text) !important;
        margin: 0 2px !important;
        outline: none !important;
    }

    .dataTables_wrapper {
        position: relative !important;
        clear: both !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .dataTables_wrapper .dataTables_filter {
        display: none !important;
    }

    /* ==========================================================================
       ORBO PAGINATION THEME (VENDOR & DATATABLES) FOR ENTERPRISE PAYROLL
       ========================================================================== */
    .orb-pagination-wrapper,
    .orb-table-footer,
    .dataTables_wrapper .row:last-child {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: nowrap !important;
        gap: 12px !important;
        width: 100% !important;
        min-height: 58px !important;
        height: auto !important;
        max-height: none !important;
        padding: 12px 18px 14px !important;
        background: #FFFFFF !important;
        border-top: 1px solid var(--ep-border, #EEF2F6) !important;
        margin: 0 !important;
        box-sizing: border-box !important;
        position: relative !important;
        z-index: 5 !important;
        overflow-y: hidden !important;
        overflow-x: auto !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    .orb-pagination-wrapper::-webkit-scrollbar,
    .orb-table-footer::-webkit-scrollbar,
    .dataTables_wrapper .row:last-child::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .dataTables_wrapper .row:last-child > [class*="col-"] {
        padding: 0 !important;
        width: auto !important;
        max-width: none !important;
        flex: 0 0 auto !important;
    }

    .orb-pagination-info,
    .dataTables_wrapper .dataTables_info {
        padding: 0 !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--ep-muted, #6B7280) !important;
        white-space: nowrap !important;
        margin: 0 !important;
        flex-shrink: 0 !important;
        line-height: normal !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        flex-shrink: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        overflow-y: hidden !important;
        overflow-x: auto !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    .dataTables_wrapper .dataTables_paginate::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .orb-pagination,
    .dataTables_wrapper .dataTables_paginate ul.pagination {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        flex-wrap: nowrap !important;
        border: none !important;
        height: 36px !important;
        max-height: 36px !important;
        overflow-y: hidden !important;
        overflow-x: auto !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    .orb-pagination::-webkit-scrollbar,
    .dataTables_wrapper .dataTables_paginate ul.pagination::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* Reset double padding & borders inherited from global stylesheets */
    .ep-page .dataTables_wrapper .dataTables_paginate .paginate_button,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.paginate_button,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item,
    .orb-page-item {
        margin: 0 2px !important;
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 34px !important;
        min-height: 34px !important;
        max-height: 34px !important;
        line-height: normal !important;
        overflow: visible !important;
        box-sizing: border-box !important;
    }

    .ep-page .dataTables_wrapper .dataTables_paginate li.paginate_button:hover,
    .ep-page .dataTables_wrapper .dataTables_paginate li.paginate_button:active,
    .ep-page .dataTables_wrapper .dataTables_paginate li.paginate_button:focus,
    .ep-page .dataTables_wrapper .dataTables_paginate li.paginate_button.current {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }

    .orb-page-link,
    .dataTables_wrapper .dataTables_paginate a.paginate_button,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item .page-link,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item a.page-link,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item span.page-link {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 34px !important;
        height: 34px !important;
        line-height: 32px !important;
        padding: 0 12px !important;
        border-radius: 9px !important;
        border: 1px solid transparent !important;
        background: transparent !important;
        color: var(--ep-primary, #4B00E8) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        transition: all 0.18s ease !important;
        cursor: pointer !important;
        user-select: none !important;
        box-shadow: none !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
        white-space: nowrap !important;
    }

    .orb-page-link:hover:not(.orb-page-dots),
    .dataTables_wrapper .dataTables_paginate a.paginate_button:hover:not(.disabled):not(.current),
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item:not(.active):not(.disabled) .page-link:hover,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item:not(.active):not(.disabled) a.page-link:hover {
        background: var(--ep-soft, #F3EDFF) !important;
        color: var(--ep-primary, #4B00E8) !important;
        border-color: transparent !important;
    }

    .orb-page-item.active .orb-page-link,
    .dataTables_wrapper .dataTables_paginate a.paginate_button.current,
    .dataTables_wrapper .dataTables_paginate a.paginate_button.current:hover,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item.active .page-link,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item.active a.page-link,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item.active span.page-link {
        background: linear-gradient(135deg, var(--ep-primary, #4B00E8), var(--ep-secondary, #FF5252)) !important;
        color: #FFFFFF !important;
        border-radius: 9px !important;
        font-weight: 800 !important;
        min-width: 34px !important;
        height: 34px !important;
        line-height: 34px !important;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.35) !important;
        border: none !important;
    }

    .orb-page-item.disabled .orb-page-link,
    .dataTables_wrapper .dataTables_paginate a.paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate a.paginate_button.disabled:hover,
    .dataTables_wrapper .dataTables_paginate a.paginate_button.disabled:active,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item.disabled .page-link,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item.disabled a.page-link,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item.disabled span.page-link {
        color: #94A3B8 !important;
        cursor: not-allowed !important;
        background: transparent !important;
        opacity: 0.6 !important;
        border: none !important;
        box-shadow: none !important;
    }

    .orb-page-dots,
    .dataTables_wrapper .dataTables_paginate .ellipsis,
    .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item.disabled span.page-link.orb-page-dots {
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
        .orb-table-footer,
        .dataTables_wrapper .row:last-child {
            flex-direction: column !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 10px 4px !important;
            text-align: center !important;
            overflow-y: hidden !important;
            overflow-x: auto !important;
        }
        .orb-pagination-info,
        .dataTables_wrapper .dataTables_info {
            font-size: 11px !important;
            white-space: nowrap !important;
        }
        .dataTables_wrapper .dataTables_paginate,
        .orb-pagination,
        .dataTables_wrapper .dataTables_paginate ul.pagination {
            flex-wrap: nowrap !important;
            justify-content: center !important;
            gap: 3px !important;
            max-width: 100% !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .orb-page-link,
        .dataTables_wrapper .dataTables_paginate a.paginate_button,
        .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item .page-link {
            min-width: 28px !important;
            height: 28px !important;
            line-height: 26px !important;
            padding: 0 6px !important;
            font-size: 11.5px !important;
            border-radius: 7px !important;
        }
        .orb-page-item.active .orb-page-link,
        .dataTables_wrapper .dataTables_paginate a.paginate_button.current,
        .dataTables_wrapper .dataTables_paginate ul.pagination li.page-item.active .page-link {
            min-width: 28px !important;
            height: 28px !important;
            line-height: 28px !important;
            border-radius: 7px !important;
        }
    }

    /* ==========================================================================
       ORBO CUSTOM THEME DATE PICKER INPUTS & CALENDAR POPUP
       ========================================================================== */
    .orbo-date-picker,
    .orbo-date-picker-display,
    .flatpickr-input.orbo-date-picker-display,
    input[data-date-picker] {
        height: 40px !important;
        border-radius: 10px !important;
        border: 1px solid var(--ep-border, #CBD5E1) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        padding: 0 36px 0 12px !important;
        background-color: #ffffff !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236366F1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 14px center !important;
        background-size: 16px 16px !important;
        color: var(--ep-text, #101828) !important;
        cursor: pointer !important;
        box-sizing: border-box !important;
        transition: all 0.2s ease !important;
        width: 100% !important;
    }

    .orbo-date-picker:focus,
    .orbo-date-picker-display:focus,
    .flatpickr-input.orbo-date-picker-display:focus,
    input[data-date-picker]:focus {
        border-color: var(--ep-primary, #4B00E8) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
        outline: none !important;
    }

    /* Flatpickr Calendar Container with Orbo Theme */
    .flatpickr-calendar.orbo-date-picker-calendar {
        border: 1px solid var(--ep-border, #E7EAF3) !important;
        border-radius: 16px !important;
        box-shadow: 0 16px 40px rgba(16, 24, 40, 0.14) !important;
        overflow: hidden !important;
        background: #ffffff !important;
        font-family: inherit !important;
        box-sizing: border-box !important;
        z-index: 999999 !important;
    }

    .flatpickr-calendar.orbo-date-picker-calendar .flatpickr-day.selected,
    .flatpickr-calendar.orbo-date-picker-calendar .flatpickr-day.startRange,
    .flatpickr-calendar.orbo-date-picker-calendar .flatpickr-day.endRange {
        background: linear-gradient(135deg, var(--ep-primary, #4B00E8), var(--ep-secondary, #FF5252)) !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
    }

    .flatpickr-calendar.orbo-date-picker-calendar .flatpickr-day:hover:not(.selected) {
        background: var(--ep-soft, #F3EDFF) !important;
        color: var(--ep-primary, #4B00E8) !important;
        border-radius: 8px !important;
    }

    /* MODAL DESIGN SYSTEM */
    .modal-content.ep-form {
        border-radius: 24px !important;
        overflow: hidden !important;
        border: 1px solid var(--ep-border) !important;
        box-shadow: 0 24px 70px rgba(16,24,40,.18) !important;
        background: #fff !important;
    }

    .ep-modal-header {
        background: linear-gradient(135deg, var(--ep-primary) 0%, var(--ep-secondary) 100%) !important;
        padding: 16px 20px !important;
        border-bottom: none !important;
        position: relative !important;
        display: block !important;
    }

    .ep-modal-header .modal-title {
        color: #fff !important;
        font-weight: 800 !important;
        font-size: 16px !important;
        margin: 0 !important;
    }

    .ep-modal-header p {
        color: rgba(255, 255, 255, 0.8) !important;
        font-size: 12px !important;
        margin: 4px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .ep-modal-header .close {
        color: #fff !important;
        opacity: 0.8 !important;
        text-shadow: none !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        background: rgba(255, 255, 255, 0.15) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        position: absolute !important;
        right: 16px !important;
        top: 16px !important;
        margin: 0 !important;
        padding: 0 !important;
        transition: all 0.2s ease !important;
        border: none !important;
        outline: none !important;
    }

    .ep-modal-header .close:hover {
        opacity: 1 !important;
        background: rgba(255, 255, 255, 0.25) !important;
    }

    .ep-modal-body {
        padding: 20px !important;
        background: #fff !important;
    }

    .ep-modal-footer {
        padding: 14px 20px !important;
        border-top: 1px solid var(--ep-border) !important;
        background: #F8FAFC !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 10px !important;
    }

    /* Modal Form Inputs & Groups */
    .ep-form-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 14px 16px !important;
    }

    @media (max-width: 768px) {
        .ep-form-grid {
            grid-template-columns: 1fr !important;
            gap: 14px !important;
        }
        .modal-dialog {
            width: calc(100% - 24px) !important;
            margin: 12px auto !important;
            max-width: none !important;
        }
    }

    .ep-form-group {
        margin-bottom: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 6px !important;
    }

    .ep-form-group label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--ep-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin: 0 !important;
        display: inline-block !important;
    }

    .ep-form-group .form-control, .ep-form-group select {
        height: 42px !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        border: 1px solid var(--ep-border) !important;
        border-radius: 9px !important;
        background: #fff !important;
        color: var(--ep-text) !important;
        outline: none !important;
        box-sizing: border-box !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    .ep-form-group .form-control:focus, .ep-form-group select:focus {
        border-color: var(--ep-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    .ep-form-group textarea.form-control {
        height: auto !important;
        min-height: 80px !important;
        resize: vertical !important;
    }

    /* Modal Action Buttons */
    .ep-modal-btn {
        height: 38px !important;
        padding: 0 18px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: all 0.2s ease !important;
        border: none !important;
        cursor: pointer !important;
        outline: none !important;
    }

    .ep-modal-btn-primary {
        background: linear-gradient(135deg, var(--ep-primary) 0%, var(--ep-secondary) 100%) !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.15) !important;
    }

    .ep-modal-btn-primary:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.25) !important;
        color: #fff !important;
    }

    .ep-modal-btn-light {
        background: #EAECEF !important;
        color: #4A5568 !important;
    }

    .ep-modal-btn-light:hover {
        background: #DFE2E6 !important;
        color: #2D3748 !important;
    }

    .ep-modal-btn-danger {
        background: #FFF1F2 !important;
        color: #E11D48 !important;
        border: 1px solid #FFE4E6 !important;
    }

    .ep-modal-btn-danger:hover {
        background: #FFE4E6 !important;
        color: #BE123C !important;
    }

    /* Target standard sizes strictly */
    .modal-dialog.modal-xs {
        max-width: 480px !important;
    }
    .modal-dialog.modal-md {
        max-width: 600px !important;
    }
    .modal-dialog.modal-lg {
        max-width: 760px !important;
    }
    .modal-dialog.modal-xl {
        max-width: 960px !important;
    }

    /* Premium Dashboard Metrics & Cards */
    .ep-metrics-grid {
        margin: 0 -8px 24px -8px !important;
    }
    
    .ep-metrics-grid [class*="col-"] {
        padding: 0 8px !important;
    }
    
    .ep-metric-card {
        background: #fff !important;
        border-radius: 18px !important;
        border: 1px solid var(--ep-border) !important;
        padding: 16px 20px !important;
        box-shadow: var(--ep-shadow) !important;
        display: flex !important;
        align-items: center !important;
        gap: 16px !important;
        height: 100% !important;
        position: relative !important;
        overflow: hidden !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    }
    
    .ep-metric-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 16px 36px rgba(16, 24, 40, 0.08) !important;
    }

    .ep-metric-card.border-bottom-primary { border-bottom: 3px solid var(--ep-primary) !important; }
    .ep-metric-card.border-bottom-success { border-bottom: 3px solid #027A48 !important; }
    .ep-metric-card.border-bottom-info { border-bottom: 3px solid #026AA2 !important; }
    .ep-metric-card.border-bottom-warning { border-bottom: 3px solid #B54708 !important; }
    .ep-metric-card.border-bottom-danger { border-bottom: 3px solid #B42318 !important; }

    .ep-metric-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        flex-shrink: 0 !important;
    }

    .ep-metric-content {
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
        min-width: 0 !important;
        flex-grow: 1 !important;
    }

    .ep-metric-label {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: var(--ep-muted) !important;
        letter-spacing: 0.05em !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .ep-metric-value {
        font-size: 20px !important;
        font-weight: 900 !important;
        color: var(--ep-text) !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .ep-metric-trend {
        font-size: 11px !important;
        font-weight: 700 !important;
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
    }

    /* Dashboard Premium Sections */
    .ep-dash-card-premium {
        background: #fff !important;
        border: 1px solid var(--ep-border) !important;
        border-radius: 22px !important;
        box-shadow: var(--ep-shadow) !important;
        padding: 24px !important;
        height: 100% !important;
        margin-bottom: 24px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    }

    .ep-dash-card-premium:hover {
        box-shadow: 0 16px 40px rgba(16, 24, 40, 0.06) !important;
    }

    .ep-dash-header-premium {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        margin-bottom: 20px !important;
        border-bottom: 1px solid var(--ep-border) !important;
        padding-bottom: 16px !important;
    }

    .ep-dash-header-premium .ep-icon-box {
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        background: var(--ep-soft) !important;
        color: var(--ep-primary) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        margin: 0 !important;
    }

    .ep-dash-title-premium {
        font-size: 15px !important;
        font-weight: 900 !important;
        color: var(--ep-text) !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }

    .ep-dash-subtitle-premium {
        font-size: 12px !important;
        color: var(--ep-muted) !important;
        margin: 2px 0 0 0 !important;
    }

</style>
