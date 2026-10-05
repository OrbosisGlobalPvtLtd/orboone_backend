<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .eo-page {
        min-height: calc(100vh - 90px);
        padding: 24px !important;
        background: var(--orb-bg) !important;
        box-sizing: border-box !important;
        width: 100% !important;
    }

    .eo-container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        box-sizing: border-box !important;
    }

    /* Page Header Overrides & Responsive styling */
    .orb-page-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 22px !important;
        padding: 24px 28px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 16px !important;
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.15) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 20px !important;
        border: none !important;
    }

    .orb-page-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.12em !important;
        color: rgba(255, 255, 255, 0.8) !important;
        margin-bottom: 6px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .orb-page-title {
        font-size: 24px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        line-height: 1.25 !important;
        letter-spacing: -0.02em !important;
    }

    .orb-page-subtitle {
        font-size: 13px !important;
        color: rgba(255, 255, 255, 0.88) !important;
        margin: 4px 0 0 0 !important;
        font-weight: 500 !important;
        line-height: 1.4 !important;
    }

    /* Main Table Card */
    .orb-table-card {
        background: #fff !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 22px !important;
        box-shadow: var(--orb-shadow) !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
    }

    .orb-table-head {
        padding: 20px 24px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        border-bottom: 1px solid var(--orb-border) !important;
        background: #fff !important;
    }

    .orb-table-title-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 15px !important;
    }

    .orb-table-icon {
        width: 42px !important;
        height: 42px !important;
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        flex-shrink: 0 !important;
    }

    .orb-table-title-wrap h3 {
        margin: 0 !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        color: var(--orb-text) !important;
    }

    .orb-table-title-wrap p {
        margin: 4px 0 0 0 !important;
        font-size: 13px !important;
        color: var(--orb-muted) !important;
        font-weight: 500 !important;
    }

    /* Filters: attached under table header */
    .orb-table-tools {
        padding: 16px 24px !important;
        background: #F8FAFC !important;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    .eo-filter-grid {
        display: grid !important;
        grid-template-columns: 1.5fr 1.2fr 1.1fr 1.1fr auto !important;
        gap: 14px !important;
        align-items: flex-end !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .eo-field {
        display: flex !important;
        flex-direction: column !important;
        gap: 6px !important;
        min-width: 0 !important;
    }

    .eo-field label {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: var(--orb-muted) !important;
        margin: 0 !important;
        letter-spacing: .4px !important;
    }

    .eo-control {
        height: 38px !important;
        border: 1px solid #DDE3EE !important;
        border-radius: 12px !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: var(--orb-text) !important;
        background: #fff !important;
        outline: none !important;
        transition: all .2s !important;
        width: 100% !important;
    }

    .eo-control:focus {
        border-color: var(--orb-secondary, #8600EE) !important;
        box-shadow: 0 0 0 4px rgba(134, 0, 238, .08) !important;
    }

    .eo-filter-actions-col {
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-end !important;
        min-width: 0 !important;
    }

    .eo-filter-actions-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        width: 100% !important;
    }

    /* Buttons inside filter */
    .orbo-button-flex {
        height: 38px !important;
        padding: 0 16px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        border-radius: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        cursor: pointer !important;
        transition: all .2s ease !important;
        white-space: nowrap !important;
    }

    .orbo-button-search {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #fff !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2) !important;
    }

    .orbo-button-search:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.3) !important;
        color: #fff !important;
    }

    .orbo-button-reset {
        background: #fff !important;
        color: var(--orb-muted) !important;
        border: 1px solid var(--orb-border) !important;
    }

    .orbo-button-reset:hover {
        background: #F1F3F8 !important;
        color: var(--orb-text) !important;
        transform: translateY(-1px) !important;
    }

    /* DataTable Toolbar: Length LEFT, Export buttons RIGHT */
    .orb-table-tools-bar {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 14px 24px !important;
        background: #fff !important;
        border-bottom: 1px solid var(--orb-border) !important;
        gap: 12px !important;
        flex-wrap: wrap !important;
        box-sizing: border-box !important;
    }



    .dataTables_filter {
        display: none !important;
    }

    /* Main Responsive Scrollable Table */
    .orb-table-wrap {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        display: block !important;
    }

    #probationInternshipTable {
        width: 100% !important;
        min-width: 950px !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    #probationInternshipTable thead th {
        background: #F8FAFC !important;
        color: #475467 !important;
        font-size: 11px !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: .45px !important;
        border-bottom: 1px solid var(--orb-border) !important;
        padding: 14px 16px !important;
        border-top: 0 !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
    }

    #probationInternshipTable tbody td {
        padding: 13px 16px !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid #F1F3F8 !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    #probationInternshipTable tbody tr:hover td {
        background: #FCFAFF !important;
    }

    .eo-emp-cell {
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
        min-width: 160px !important;
    }

    .eo-name {
        font-weight: 850 !important;
        color: var(--orb-text) !important;
        font-size: 13.5px !important;
        line-height: 1.3 !important;
        white-space: nowrap !important;
    }

    .eo-code-under {
        display: inline-flex !important;
        align-items: center !important;
        width: max-content !important;
        padding: 2px 7px !important;
        border-radius: 6px !important;
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        border: 1px solid rgba(75, 0, 232, 0.12) !important;
        font-size: 10.5px !important;
        font-weight: 800 !important;
        letter-spacing: 0.2px !important;
    }

    .eo-muted-text {
        font-size: 12.5px !important;
        color: var(--orb-muted) !important;
        font-weight: 600 !important;
    }

    /* Status Pills */
    .eo-pill {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        border-radius: 50px !important;
        padding: 4px 10px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        white-space: nowrap !important;
    }

    .eo-pill-active {
        background: #ECFDF3 !important;
        color: #027A48 !important;
        border: 1px solid rgba(2, 122, 72, 0.15) !important;
    }

    .eo-pill-warning {
        background: #FFFAEB !important;
        color: #B54708 !important;
        border: 1px solid rgba(181, 71, 8, 0.15) !important;
    }

    .eo-pill-purple {
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        border: 1px solid rgba(75, 0, 232, 0.15) !important;
    }

    .eo-pill-danger {
        background: #FEF3F2 !important;
        color: #B42318 !important;
        border: 1px solid rgba(180, 35, 24, 0.15) !important;
    }

    /* Action Buttons */
    .eo-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
        white-space: nowrap !important;
    }

    .eo-icon-btn,
    .eo-more-btn {
        width: 32px !important;
        height: 32px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-decoration: none !important;
        transition: all .2s ease !important;
        background: #fff !important;
        color: #475467 !important;
        font-size: 12px !important;
        cursor: pointer !important;
        padding: 0 !important;
    }

    .eo-icon-btn:hover,
    .eo-more-btn:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .08) !important;
        background: var(--orb-primary) !important;
        border-color: var(--orb-primary) !important;
        color: #fff !important;
    }

    .eo-highlight-row {
        background: #FFF7ED !important;
        box-shadow: inset 4px 0 0 #F79009 !important;
    }

    /* Table Footer */
    .eo-table-footer {
        padding: 16px 24px !important;
        border-top: 1px solid var(--orb-border) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        flex-wrap: wrap !important;
        background: #fff !important;
    }

    /* Modals */
    .modal-backdrop {
        z-index: 1240 !important;
        background: #0F172A !important;
    }

    .modal-backdrop.show {
        opacity: .58 !important;
    }

    .modal {
        z-index: 1250 !important;
    }

    .eo-life-modal .modal-dialog {
        max-width: 780px !important;
    }

    .eo-modal-content {
        border: 0 !important;
        border-radius: 24px !important;
        overflow: hidden !important;
        background: #fff !important;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28) !important;
    }

    .eo-modal-header {
        padding: 18px 24px !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
        border-bottom: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 14px !important;
    }

    .eo-modal-title {
        margin: 0 !important;
        font-size: 18px !important;
        font-weight: 900 !important;
        color: #fff !important;
    }

    .eo-modal-subtitle {
        margin-top: 4px !important;
        font-size: 12px !important;
        color: rgba(255, 255, 255, .85) !important;
        font-weight: 600 !important;
    }

    .eo-modal-close {
        color: #fff !important;
        opacity: 0.85 !important;
        text-shadow: none !important;
        background: transparent !important;
        border: 0 !important;
        font-size: 24px !important;
        line-height: 1 !important;
        cursor: pointer !important;
        padding: 0 !important;
        transition: opacity .15s !important;
    }

    .eo-modal-close:hover {
        opacity: 1 !important;
    }

    .eo-modal-body {
        padding: 20px 24px !important;
        background: #fff !important;
        max-height: 78vh !important;
        overflow-y: auto !important;
    }

    .eo-action-card {
        border: 1px solid #EEF1F6 !important;
        border-radius: 18px !important;
        background: #fff !important;
        overflow: hidden !important;
        margin-bottom: 14px !important;
    }

    .eo-action-card:last-child {
        margin-bottom: 0 !important;
    }

    .eo-action-card-head {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 14px 18px !important;
        background: #F8FAFC !important;
        border-bottom: 1px solid #EEF1F6 !important;
    }

    .eo-action-icon {
        width: 38px !important;
        height: 38px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        flex: 0 0 auto !important;
        font-size: 16px !important;
    }

    .eo-action-title {
        font-size: 14px !important;
        font-weight: 850 !important;
        color: var(--orb-text) !important;
        line-height: 1.2 !important;
    }

    .eo-action-sub {
        font-size: 12px !important;
        font-weight: 600 !important;
        color: var(--orb-muted) !important;
        margin-top: 3px !important;
        line-height: 1.35 !important;
    }

    .eo-action-body {
        padding: 18px !important;
        background: #fff !important;
    }

    .eo-action-body label {
        display: block !important;
        margin: 0 0 6px !important;
        color: var(--orb-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: .4px !important;
    }

    /* Datepicker and Readonly Date Inputs with Calendar SVG Icon */
    .eo-date,
    .eo-readonly-date,
    .orbo-date-picker-display,
    input[data-date-picker],
    .flatpickr-input {
        width: 100% !important;
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234B00E8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 12px center !important;
        background-size: 16px 16px !important;
        color: var(--orb-text, #101828) !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        padding: 8px 36px 8px 12px !important;
        outline: none !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
    }

    .eo-date:focus,
    .orbo-date-picker-display:focus,
    input[data-date-picker]:focus {
        border-color: var(--orb-primary, #4B00E8) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1) !important;
    }

    .eo-readonly-date {
        background-color: #F8FAFC !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E") !important;
        color: #475467 !important;
        cursor: not-allowed !important;
    }

    /* Standard modal text and number inputs */
    .eo-input-control {
        width: 100% !important;
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        background: #fff !important;
        color: var(--orb-text, #101828) !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        padding: 8px 12px !important;
        outline: none !important;
        box-sizing: border-box !important;
        transition: all 0.2s ease !important;
    }

    .eo-input-control:focus {
        border-color: var(--orb-primary, #4B00E8) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1) !important;
    }

    /* Custom Duration Flex Input Group (Number + Unit Select2) */
    .eo-custom-duration-group {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        width: 100% !important;
    }

    .eo-custom-duration-group input[type="number"] {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        width: 100% !important;
        height: 38px !important;
    }

    .eo-custom-duration-group .select2-container,
    .eo-custom-duration-group select {
        flex: 0 0 120px !important;
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
    }

    .flatpickr-calendar {
        z-index: 999999 !important;
    }

    .eo-info-note {
        padding: 10px 14px !important;
        border-radius: 12px !important;
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        line-height: 1.45 !important;
        border: 1px solid rgba(75, 0, 232, 0.1) !important;
    }

    .eo-menu-submit {
        width: 100% !important;
        min-height: 40px !important;
        border: 0 !important;
        border-radius: 12px !important;
        padding: 10px 16px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        color: #fff !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        cursor: pointer !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        transition: all .2s ease !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2) !important;
    }

    .eo-menu-submit:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.3) !important;
        color: #fff !important;
    }

    .eo-menu-submit-success {
        background: linear-gradient(135deg, #059669, #10B981) !important;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25) !important;
    }

    .eo-menu-submit-success:hover {
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35) !important;
    }

    .eo-menu-submit-warning {
        background: linear-gradient(135deg, #D97706, #F59E0B) !important;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25) !important;
    }

    .eo-menu-submit-warning:hover {
        box-shadow: 0 6px 16px rgba(217, 119, 6, 0.35) !important;
    }

    .eo-menu-submit-danger {
        background: linear-gradient(135deg, #DC2626, #EF4444) !important;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25) !important;
    }

    .eo-menu-submit-danger:hover {
        box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35) !important;
    }

    /* Action Tabs */
    .eo-action-tabs {
        display: flex !important;
        gap: 8px !important;
        background: #F8FAFC !important;
        padding: 6px !important;
        border-radius: 16px !important;
        margin-bottom: 18px !important;
        border: 1px solid #E2E8F0 !important;
        overflow-x: auto !important;
    }

    .eo-action-tab {
        flex: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 9px 16px !important;
        font-size: 12.5px !important;
        font-weight: 800 !important;
        color: #64748B !important;
        background: transparent !important;
        border: none !important;
        border-radius: 12px !important;
        cursor: pointer !important;
        transition: all 0.2s ease-in-out !important;
        white-space: nowrap !important;
        outline: none !important;
    }

    .eo-action-tab:hover {
        color: var(--orb-primary) !important;
        background: rgba(255, 255, 255, 0.7) !important;
    }

    .eo-action-tab.active {
        color: #fff !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.25) !important;
    }

    .eo-tab-pane {
        display: none !important;
    }

    .eo-tab-pane.active {
        display: block !important;
        animation: fadeInTab 0.25s ease-in-out !important;
    }

    @keyframes fadeInTab {
        from { opacity: 0; transform: translateY(3px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Media Queries */
    @media (max-width: 1200px) {
        .eo-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 991px) {
        .eo-page {
            padding: 16px !important;
        }

        .orb-page-header {
            padding: 20px 22px !important;
        }

        .eo-filter-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }

    @media (max-width: 767px) {
        .eo-filter-grid {
            grid-template-columns: 1fr !important;
        }


    }

    @media (max-width: 575px) {
        .eo-page {
            padding: 12px 10px 24px !important;
        }

        .orb-page-header {
            padding: 16px 18px !important;
            border-radius: 18px !important;
            margin-bottom: 14px !important;
        }

        .orb-page-kicker {
            font-size: 10px !important;
        }

        .orb-page-title {
            font-size: 20px !important;
        }

        .orb-page-subtitle {
            font-size: 12px !important;
        }

        .orb-table-card {
            border-radius: 16px !important;
        }

        .orb-table-head {
            padding: 14px 16px !important;
        }

        .orb-table-tools {
            padding: 12px 14px !important;
        }



        .eo-life-modal .modal-dialog {
            margin: 10px !important;
        }

        .eo-modal-content {
            border-radius: 18px !important;
        }

        .eo-modal-header {
            padding: 14px 16px !important;
        }

        .eo-modal-body {
            padding: 14px 16px !important;
        }

        .eo-action-tabs {
            border-radius: 12px !important;
            gap: 4px !important;
            padding: 4px !important;
            margin-bottom: 14px !important;
        }

        .eo-action-tab {
            padding: 7px 10px !important;
            font-size: 11px !important;
            border-radius: 8px !important;
        }
    }
</style>
