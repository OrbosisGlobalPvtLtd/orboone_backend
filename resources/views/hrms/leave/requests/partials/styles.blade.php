<style>
    body, .ep-page, .set-page, button, input, select, textarea, table {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
    }

    .ep-page {
        padding: 24px !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    .leave-page-container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        box-sizing: border-box !important;
    }

    @media (max-width: 768px) {
        .ep-page {
            padding: 14px !important;
        }
    }

    /* Metric Summary Pills & Cards */
    .metric-card-primary {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .metric-card-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(75, 0, 232, 0.08);
        border-color: #CBD5E1;
    }

    .metric-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .metric-val {
        font-size: 24px;
        font-weight: 900;
        color: #0F172A;
        line-height: 1.1;
        letter-spacing: -0.02em;
    }

    .metric-lbl {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748B;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    /* Counter Pills Row */
    .counter-pill-item {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .counter-pill-lbl {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .counter-pill-num {
        font-size: 15px;
        font-weight: 900;
        padding: 3px 10px;
        border-radius: 8px;
    }

    /* Status Badges */
    .orb-pill {
        border-radius: 30px;
        padding: 5px 12px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        letter-spacing: 0.03em;
    }

    .orb-pill.pending {
        background: #FEF3C7;
        color: #B45309;
    }

    .orb-pill.approved {
        background: #D1FAE5;
        color: #047857;
    }

    .orb-pill.rejected {
        background: #FEE2E2;
        color: #B91C1C;
    }

    .orb-pill.cancelled {
        background: #F1F5F9;
        color: #475569;
    }

    .orb-pill.void {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #CBD5E1;
    }

    .orb-pill.expired {
        background: #F3E8FF;
        color: #6B21A8;
    }

    /* Filter Controls & Responsive Layout */
    .leave-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)) auto;
        gap: 12px;
        align-items: flex-end;
    }

    @media (min-width: 1280px) {
        .leave-filter-grid {
            grid-template-columns: 1.1fr 1fr 1fr 0.8fr 1.8fr auto;
        }
        .leave-filter-grid.has-employee-filter {
            grid-template-columns: 1.3fr 1fr 1fr 1fr 0.8fr 1.6fr auto;
        }
    }

    @media (max-width: 1279px) and (min-width: 768px) {
        .leave-filter-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        .leave-filter-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
    }

    @media (max-width: 767px) {
        .leave-filter-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .leave-filter-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 8px;
        }
        .leave-filter-actions .filter-btn-search {
            flex: 1;
            justify-content: center;
        }
        .orb-table-tools-bar.eo-toolbar {
            padding: 12px 16px !important;
            flex-direction: column !important;
            align-items: stretch !important;
        }
        .orb-table-length-box, .orb-table-export-buttons {
            width: 100% !important;
        }
        .orbo-export-group {
            width: 100% !important;
            display: flex !important;
            justify-content: space-between !important;
        }
        .orbo-export-btn {
            flex: 1 !important;
            padding: 0 6px !important;
            justify-content: center !important;
            font-size: 12px !important;
        }
    }

    .filter-control {
        width: 100%;
        height: 42px;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 0 14px;
        background: #FFFFFF;
        font-size: 13px;
        font-weight: 600;
        color: #0F172A;
        outline: none;
        transition: all 0.2s ease;
    }

    .filter-control:focus {
        border-color: #4F46E5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .leave-filter-grid .select2-container--default .select2-selection--single {
        height: 42px !important;
        border-radius: 12px !important;
        border: 1px solid #E2E8F0 !important;
        display: flex !important;
        align-items: center !important;
    }

    .leave-filter-grid .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #0F172A !important;
        padding-left: 14px !important;
        padding-right: 28px !important;
    }

    .leave-filter-grid .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }

    .filter-btn-reset {
        height: 42px;
        padding: 0 18px;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        font-weight: 800;
        font-size: 12px;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .filter-btn-reset:hover {
        background: #F8FAFC;
        color: #0F172A;
        border-color: #CBD5E1;
    }

    /* Filter Search Button */
    .filter-btn-search {
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #7C3AED) 100%) !important;
        border: none !important;
        padding: 0 20px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.25) !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }

    .filter-btn-search:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 18px rgba(75, 0, 232, 0.35) !important;
        color: #FFFFFF !important;
    }

    /* Orbo Theme Standard Export Buttons & Tools Bar */
    .orb-table-tools-bar.eo-toolbar {
        padding: 14px 24px !important;
        background: #FFFFFF !important;
        border-bottom: 1px solid #E2E8F0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
    }

    .orbo-export-group {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
    }

    .orbo-export-btn {
        height: 36px !important;
        padding: 0 15px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        letter-spacing: -0.2px !important;
        border: 1px solid #E2E8F0 !important;
        background: #FFFFFF !important;
        color: #0F172A !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        transition: all 0.18s ease-in-out !important;
        white-space: nowrap !important;
        text-decoration: none !important;
        cursor: pointer !important;
        user-select: none !important;
    }

    .orbo-export-btn:hover {
        background: #F8FAFC !important;
        color: #0F172A !important;
        border-color: #CBD5E1 !important;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.07) !important;
        transform: translateY(-1px) !important;
    }

    .orbo-export-btn:active {
        transform: translateY(0) !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }

    .icon-excel { color: #16A34A !important; }
    .icon-csv   { color: #0F172A !important; }
    .icon-pdf   { color: #DC2626 !important; }
    .icon-print { color: #0F172A !important; }

    .table-per-page-select {
        transition: all 0.2s ease !important;
    }
    .table-per-page-select:focus {
        border-color: #4B00E8 !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
    }

    /* Hide default DataTables duplicate button bar */
    .dt-buttons,
    div.dt-buttons {
        display: none !important;
    }

    /* 3-Dot Action Button & Dropdown Menu */
    .btn-action-dots {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #F8FAFC;
        color: #475569;
        border: 1px solid #CBD5E1;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .btn-action-dots:hover,
    .btn-action-dots:focus {
        background: #EEF2FF;
        color: var(--orb-primary, #4B00E8);
        border-color: #C7D2FE;
        box-shadow: 0 3px 8px rgba(75, 0, 232, 0.15);
        outline: none;
    }

    .dropdown-menu-action {
        min-width: 195px;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
        border: 1px solid #E2E8F0;
        padding: 6px;
        z-index: 1050;
        background: #FFFFFF;
    }

    .dropdown-menu-action .dropdown-item {
        font-size: 12.5px;
        font-weight: 600;
        padding: 8px 12px;
        border-radius: 8px;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 9px;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .dropdown-menu-action .dropdown-item:hover {
        background: #F8FAFC;
        color: var(--orb-primary, #4B00E8);
    }

    .dropdown-menu-action .dropdown-item i {
        width: 16px;
        text-align: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .ep-table-wrap {
        overflow: visible !important;
    }

    @media (max-width: 991px) {
        .ep-table-wrap {
            overflow-x: auto !important;
        }
    }

    /* Responsive Leave Details Modal Styles (Matched with Leave Approvals) */
    .leave-modal-dialog {
        max-width: 800px;
        width: 95%;
        margin: 1.75rem auto;
    }

    .leave-modal-content {
        border-radius: 16px;
        overflow: hidden;
        max-height: 86vh;
        display: flex;
        flex-direction: column;
        background: #FFFFFF;
    }

    .leave-modal-header {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #7C3AED) 100%);
        min-height: 56px;
        height: auto;
        flex-shrink: 0;
        border-radius: 16px 16px 0 0;
    }

    .leave-modal-info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .leave-info-tile {
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 58px;
        box-shadow: 0 1px 3px rgba(15,23,42,0.03);
        min-width: 0;
    }

    .leave-stage-pipeline-box {
        border-radius: 10px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 1px 3px rgba(15,23,42,0.03);
        background: #FFFFFF;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .leave-balance-breakdown-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }

    .leave-balance-tile {
        padding: 8px 10px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .leave-stage-arrow {
        font-size: 10px;
        transition: transform 0.2s ease;
    }

    @media (max-width: 992px) {
        .leave-modal-info-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .leave-balance-breakdown-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .leave-modal-dialog {
            width: calc(100% - 16px) !important;
            max-width: 100% !important;
            margin: 8px auto !important;
        }
        .leave-modal-content {
            max-height: 92vh !important;
            border-radius: 14px !important;
        }
        .leave-modal-info-grid {
            grid-template-columns: 1fr !important;
            gap: 8px !important;
        }
        .leave-balance-breakdown-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 6px !important;
        }
        .leave-modal-header, .leave-modal-footer {
            padding: 10px 14px !important;
            min-height: 52px !important;
            height: auto !important;
        }
        .leave-stage-pipeline-box {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
        }
        .leave-stage-items-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            width: 100% !important;
            gap: 6px !important;
        }
        .leave-stage-arrow {
            transform: rotate(90deg);
            align-self: center;
            margin: 2px 0;
        }
        .leave-modal-footer {
            flex-direction: column !important;
            gap: 8px !important;
        }
    }

    /* Edit Leave Request Modal Styles */
    .flatpickr-calendar {
        z-index: 999999 !important;
    }

    #editLeaveModal .modal-dialog {
        max-width: 1140px !important;
        width: 94% !important;
        margin: 1.25rem auto;
    }

    #editLeaveModal .modal-content {
        border-radius: 22px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 25px 70px rgba(15, 23, 42, 0.28);
    }

    #editLeaveModal .modal-body {
        background: #F8FAFC;
        max-height: calc(90vh - 125px);
        overflow-y: auto;
    }

    #editLeaveModal .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    #editLeaveModal .modal-body::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 10px;
    }

    #editLeaveModal .form-control:focus {
        border-color: #4B00E8 !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
    }

    .edit-kpi-card {
        border-radius: 11px;
        padding: 7px 10px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    }

    .edit-kpi-card:hover {
        transform: translateY(-1px);
    }

    .edit-option-box {
        background: #F8FAFC;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        padding: 12px 16px;
        transition: all 0.2s ease;
        min-height: 60px;
    }

    .edit-option-box:hover {
        border-color: #CBD5E1;
        background: #F1F5F9;
    }

    @media (max-width: 991px) {
        #editLeaveModal .modal-dialog {
            max-width: 100% !important;
            width: 96% !important;
            margin: 8px auto;
        }
        #editLeaveModal .modal-body {
            padding: 16px;
        }
    }
</style>
