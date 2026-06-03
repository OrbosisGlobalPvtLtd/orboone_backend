<style>
    :root {

        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    /* Core Page Layout and Spacing Rules */
    .eo-page,
    .em-page,
    .ev-page {
        min-height: calc(100vh - 90px);
        padding: 24px !important;
        background: var(--orb-bg) !important;
        font-family: 'Outfit', 'Inter', sans-serif !important;
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 1500px !important;
        margin: 0 auto !important;
        overflow-x: hidden !important;
    }

    @media (max-width: 991px) {

        .eo-page,
        .em-page,
        .ev-page {
            padding: 18px !important;
        }
    }

    @media (max-width: 575px) {

        .eo-page,
        .em-page,
        .ev-page {
            padding: 12px !important;
        }
    }

    /* Premium Purple Gradient Hero Header */
    .orb-page-header,
    .em-hero,
    .eo-header,
    .ev-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 26px !important;
        padding: 28px 36px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.15) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
        border: none !important;
    }

    .orb-page-header::before,
    .em-hero::before,
    .eo-header::before,
    .ev-header::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 300px !important;
        height: 300px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border-radius: 50% !important;
        filter: blur(40px) !important;
        pointer-events: none !important;
    }

    .orb-page-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.15em !important;
        color: rgba(255, 255, 255, 0.75) !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .orb-page-title,
    .em-title,
    .eo-title,
    .ev-title {
        font-size: 26px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .orb-page-subtitle,
    .em-sub,
    .eo-subtitle,
    .ev-sub {
        font-size: 14px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 6px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .orb-page-actions,
    .em-actions,
    .ev-actions {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        flex-shrink: 0 !important;
        z-index: 2 !important;
    }

    @media (max-width: 768px) {

        .orb-page-header,
        .em-hero,
        .eo-header,
        .ev-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 24px !important;
        }

        .orb-page-actions,
        .em-actions,
        .ev-actions {
            width: 100% !important;
            flex-wrap: wrap !important;
            margin-top: 8px !important;
        }

        .orb-page-actions a,
        .em-actions button,
        .em-actions a,
        .ev-actions a {
            flex: 1 1 auto !important;
            justify-content: center !important;
        }
    }

    /* Premium Pill Buttons */
    .orb-btn-light,
    .em-btn,
    .ev-btn {
        height: 42px !important;
        padding: 0 20px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 850 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        border: none !important;
        cursor: pointer !important;
        text-decoration: none !important;
        outline: none !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    }

    .orb-btn-light,
    .em-btn-light,
    .ev-btn:not(.ev-btn-primary):not(.ev-btn-soft) {
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
    }

    .orb-btn-light:hover,
    .em-btn-light:hover,
    .ev-btn:not(.ev-btn-primary):not(.ev-btn-soft):hover {
        background: rgba(255, 255, 255, 0.3) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    .em-btn-primary,
    .ev-btn-primary {
        background: #fff !important;
        color: var(--orb-primary) !important;
    }

    .em-btn-primary:hover,
    .ev-btn-primary:hover {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
        text-decoration: none !important;
    }

    .ev-btn-soft {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
    }

    .ev-btn-soft:hover {
        background: rgba(255, 255, 255, 0.35) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    .em-btn-success {
        background: #12B76A !important;
        color: #fff !important;
    }

    .em-btn-success:hover {
        background: #0F9F5B !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
    }

    .eo-code-badge {
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
        padding: 8px 16px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        white-space: nowrap !important;
    }

    /* Premium Summary Cards */
    .eo-stat-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 16px !important;
        margin-bottom: 24px !important;
    }

    @media (max-width: 991px) {
        .eo-stat-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 575px) {
        .eo-stat-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .eo-stat {
        background: #fff !important;
        border-radius: 18px !important;
        border: 1px solid var(--orb-border) !important;
        padding: 16px 20px !important;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .045) !important;
        display: flex !important;
        align-items: center !important;
        gap: 16px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        border-bottom: 3px solid var(--orb-primary) !important;
    }

    .eo-stat:hover {
        transform: translateY(-2px) !important;
        box-shadow: var(--orb-shadow) !important;
    }

    .eo-stat.border-bottom-primary { border-bottom: 3px solid var(--orb-primary) !important; }
    .eo-stat.border-bottom-success { border-bottom: 3px solid #12B76A !important; }
    .eo-stat.border-bottom-info { border-bottom: 3px solid #06AED4 !important; }
    .eo-stat.border-bottom-warning { border-bottom: 3px solid #F79009 !important; }
    .eo-stat.border-bottom-danger { border-bottom: 3px solid #EC4E74 !important; }

    .eo-stat-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        flex-shrink: 0 !important;
    }

    .eo-stat-icon.primary {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
    }

    .eo-stat-icon.success {
        background: #ECFDF3 !important;
        color: #027A48 !important;
    }

    .eo-stat-icon.warning {
        background: #FFFAEB !important;
        color: #B54708 !important;
    }

    .eo-stat-icon.info {
        background: #F0F9FF !important;
        color: #026AA2 !important;
    }

    .eo-stat-icon.danger {
        background: #FEF3F2 !important;
        color: #B42318 !important;
    }

    .eo-stat-label {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: var(--orb-muted) !important;
        letter-spacing: 0.05em !important;
        margin: 0 !important;
    }

    .eo-stat-value {
        font-size: 24px !important;
        font-weight: 950 !important;
        color: var(--orb-text) !important;
        margin: 2px 0 0 0 !important;
    }

    /* Premium Containers & Cards */
    .orb-table-card,
    .eo-card,
    .em-card,
    .ev-card {
        background: #fff !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 22px !important;
        box-shadow: var(--orb-shadow) !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
    }

    .eo-card-head,
    .em-card-head,
    .ev-card-head {
        padding: 20px 24px !important;
        background: linear-gradient(180deg, #fff, #FAFBFF) !important;
        border-bottom: 1px solid var(--orb-border) !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 12px !important;
    }

    .eo-section-title,
    .ev-card-title,
    .em-card-title {
        margin: 0 !important;
        font-size: 16px !important;
        font-weight: 900 !important;
        color: var(--orb-text) !important;
        display: flex !important;
        gap: 10px !important;
        align-items: center !important;
    }

    .eo-section-title i,
    .em-card-title i,
    .ev-card-title i {
        color: var(--orb-primary) !important;
        font-size: 18px !important;
    }

    .eo-section-title h5,
    .em-card-title h5,
    .ev-card-title h5 {
        font-size: 16px !important;
        font-weight: 900 !important;
        margin: 0 !important;
    }

    .eo-section-title p,
    .em-card-sub,
    .ev-card-sub {
        font-size: 12px !important;
        color: var(--orb-muted) !important;
        font-weight: 500 !important;
        margin-top: 3px !important;
    }

    .eo-card-body,
    .em-card-body,
    .ev-card-body {
        padding: 24px !important;
    }

    /* Embedded Filter Toolbar */
    .orb-table-toolbar {
        background: #F8FAFC !important;
        border-bottom: 1px solid var(--orb-border) !important;
        padding: 20px 24px !important;
    }

    .eo-filter-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
        gap: 16px !important;
        align-items: flex-end !important;
    }

    @media (max-width: 768px) {
        .eo-filter-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Inputs, Selects and Labels styling */
    .eo-field {
        margin-bottom: 18px !important;
    }

    .eo-field label,
    .em-field label,
    .ev-label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--orb-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .eo-control,
    .form-control,
    .form-select,
    .em-control {
        height: 40px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        width: 100% !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    .eo-control:focus,
    .form-control:focus,
    .form-select:focus,
    .em-control:focus {
        border-color: var(--orb-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
        background: #fff !important;
    }

    .readonly-field,
    .em-control[readonly],
    .em-control[disabled] {
        background: #F8FAFC !important;
        color: var(--orb-muted) !important;
        cursor: not-allowed !important;
    }

    .required {
        color: #D92D20 !important;
        font-weight: 950 !important;
    }

    .small-note {
        font-size: 11px !important;
        color: var(--orb-muted) !important;
        margin-top: 4px !important;
        font-weight: 500 !important;
    }

    /* Tables, Responsive Scroll, and Datatables Layouts */
    .orb-table-wrapper {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .table {
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .table thead th {
        background: #F8FAFC !important;
        color: var(--orb-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 14px 20px !important;
        border-top: none !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .table tbody td {
        padding: 16px 20px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
    }

    .table tbody tr:hover td {
        background: #FDFDFF !important;
    }

    .table .btn {
        padding: 5px 12px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        border-radius: 30px !important;
        text-transform: uppercase !important;
    }

    /* DataTables length entries and export toolbar styling */
    #employeeLengthBox .dataTables_length select {
        border-radius: 9px !important;
        padding: 4px 22px 4px 8px !important;
        border-color: var(--orb-border) !important;
    }

    #employeeExportButtons .dt-buttons .btn {
        border-radius: 50px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        margin-right: 6px !important;
        margin-bottom: 6px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: #475467 !important;
        padding: 6px 14px !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05) !important;
    }

    #employeeExportButtons .dt-buttons .btn:hover {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
    }

    .eo-table-footer {
        padding: 16px 24px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 16px !important;
        border-top: 1px solid var(--orb-border) !important;
    }

    .dataTables_info {
        font-size: 13px !important;
        color: var(--orb-muted) !important;
        font-weight: 600 !important;
    }

    .dataTables_paginate .pagination .page-item .page-link {
        border-radius: 9px !important;
        margin: 0 2px !important;
        border-color: var(--orb-border) !important;
        color: var(--orb-primary) !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        padding: 6px 12px !important;
    }

    .dataTables_paginate .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-color: transparent !important;
        color: #fff !important;
    }

    /* Premium Pill Badge System */
    .eo-pill {
        display: inline-flex !important;
        align-items: center !important;
        border-radius: 999px !important;
        white-space: nowrap !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        font-size: 10px !important;
        padding: 5px 10px !important;
        gap: 6px !important;
    }

    .eo-dot {
        width: 6px !important;
        height: 6px !important;
        border-radius: 50% !important;
        display: inline-block !important;
        background: currentColor !important;
    }

    .eo-pill-active {
        background: #DCFCE7 !important;
        color: #166534 !important;
    }

    .eo-pill-pending {
        background: #FEF3C7 !important;
        color: #92400E !important;
    }

    .eo-pill-danger {
        background: #FEE2E2 !important;
        color: #B42318 !important;
    }

    .eo-pill-wfh {
        background: #ECFEFF !important;
        color: #155E75 !important;
    }

    .eo-pill-wfo {
        background: #EEF2FF !important;
        color: #3730A3 !important;
    }

    .eo-pill-hybrid {
        background: #EDE9FE !important;
        color: #5B21B6 !important;
    }

    .eo-pill-blue {
        background: #E0F2FE !important;
        color: #0369A1 !important;
    }

    .eo-pill-default {
        background: #F1F5F9 !important;
        color: #475569 !important;
    }

    /* Employee Avatar Layout */
    .att-emp {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }

    .att-avatar {
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        border: 1px solid rgba(75, 0, 232, 0.1) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 800 !important;
        flex-shrink: 0 !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .att-emp-name {
        color: var(--orb-text) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
    }

    .att-emp-code {
        color: var(--orb-muted) !important;
        font-size: 11px !important;
        font-weight: 600 !important;
    }

    /* Modal Styling */
    .modal-content {
        border: none !important;
        border-radius: 24px !important;
        overflow: hidden !important;
        box-shadow: 0 20px 50px rgba(16, 24, 40, 0.15) !important;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        padding: 20px 24px !important;
        border: none !important;
        color: #fff !important;
    }

    .modal-header .modal-title {
        font-size: 18px !important;
        font-weight: 900 !important;
        color: #fff !important;
    }

    .modal-header .close {
        color: #fff !important;
        opacity: 0.8 !important;
        text-shadow: none !important;
        background: rgba(255, 255, 255, 0.15) !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: none !important;
        outline: none !important;
    }

    .modal-header .close:hover {
        opacity: 1 !important;
        background: rgba(255, 255, 255, 0.25) !important;
    }

    .modal-body {
        padding: 24px !important;
        background: #fff !important;
    }

    .modal-footer {
        padding: 16px 24px !important;
        border-top: 1px solid var(--orb-border) !important;
        background: #F8FAFC !important;
        display: flex !important;
        justify-content: flex-end !important;
        gap: 12px !important;
    }

    /* Modal Form / Button Pill Actions */
    .btn-orb,
    .btn-primary {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #fff !important;
        border-radius: 50px !important;
        font-weight: 800 !important;
        padding: 10px 24px !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.15) !important;
    }

    .btn-orb:hover,
    .btn-primary:hover {
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.25) !important;
        transform: translateY(-1px) !important;
        color: #fff !important;
    }

    .btn-soft,
    .btn-secondary {
        background: #EAECEF !important;
        color: #4A5568 !important;
        border-radius: 50px !important;
        font-weight: 800 !important;
        padding: 10px 24px !important;
        border: none !important;
    }

    .btn-soft:hover,
    .btn-secondary:hover {
        background: #DFE2E6 !important;
        color: #2D3748 !important;
        transform: translateY(-1px) !important;
    }

    /* Form Panel Smart Setup Layouts */
    .eo-smart-panel {
        background: #F8FAFC !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 16px !important;
        padding: 20px !important;
        margin-top: 20px !important;
        margin-bottom: 20px !important;
    }

    .eo-panel-title {
        font-size: 14px !important;
        font-weight: 900 !important;
        color: var(--orb-primary) !important;
        margin-bottom: 16px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    .eo-actions-bar {
        background: #fff !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 18px !important;
        padding: 20px 24px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        margin-top: 24px !important;
        box-shadow: 0 10px 30px rgba(16, 24, 40, .05) !important;
    }

    @media (max-width: 768px) {
        .eo-actions-bar {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .eo-actions {
            width: 100% !important;
            flex-wrap: wrap !important;
            margin-top: 12px !important;
        }

        .eo-actions .btn {
            flex: 1 1 auto !important;
            justify-content: center !important;
        }
    }

    .eo-actions-note {
        font-size: 13px !important;
        color: var(--orb-muted) !important;
        font-weight: 600 !important;
    }

    .eo-actions {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
    }

    .btn-profile {
        background: linear-gradient(135deg, var(--orb-secondary) 0%, #B000EE 100%) !important;
        color: #fff !important;
        border-radius: 50px !important;
        font-weight: 800 !important;
        padding: 10px 24px !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(134, 0, 238, 0.15) !important;
    }

    .btn-profile:hover {
        box-shadow: 0 6px 16px rgba(134, 0, 238, 0.25) !important;
        transform: translateY(-1px) !important;
        color: #fff !important;
    }

    /* Single Employee View (show.blade.php) Styling */
    .ev-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 20px !important;
    }

    @media (max-width: 991px) {
        .ev-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .ev-card.ev-full {
        grid-column: 1 / -1 !important;
    }

    .ev-info-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 16px !important;
    }

    @media (max-width: 575px) {
        .ev-info-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .ev-item {
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
    }

    .ev-avatar {
        width: 64px !important;
        height: 64px !important;
        border-radius: 16px !important;
        background: rgba(255, 255, 255, 0.2) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        color: #fff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 24px !important;
        font-weight: 900 !important;
        overflow: hidden !important;
    }

    .ev-avatar img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    .ev-user {
        display: flex !important;
        align-items: center !important;
        gap: 16px !important;
    }

    .ev-pill {
        display: inline-flex !important;
        align-items: center !important;
        border-radius: 999px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        font-size: 10px !important;
        padding: 4px 10px !important;
        background: rgba(255, 255, 255, 0.2) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        margin-right: 6px !important;
    }

    .ev-pill.ev-pill-active,
    .ev-pill.ev-pill-completed {
        background: rgba(22, 163, 74, 0.25) !important;
        border-color: rgba(22, 163, 74, 0.4) !important;
    }

    .ev-pill.ev-pill-resigned,
    .ev-pill.ev-pill-pending {
        background: rgba(217, 119, 6, 0.25) !important;
        border-color: rgba(217, 119, 6, 0.4) !important;
    }

    .ev-pill.ev-pill-terminated,
    .ev-pill.ev-pill-rejected {
        background: rgba(220, 38, 38, 0.25) !important;
        border-color: rgba(220, 38, 38, 0.4) !important;
    }

    .ev-docs {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 16px !important;
    }

    .ev-doc {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 10px 16px !important;
        border-radius: 12px !important;
        border: 1px solid var(--orb-border) !important;
        background: #F8FAFC !important;
        color: var(--orb-primary) !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        font-size: 13px !important;
    }

    .ev-doc:hover {
        background: var(--orb-soft) !important;
        border-color: rgba(75, 0, 232, 0.2) !important;
        text-decoration: none !important;
    }

    .ev-doc-preview {
        width: 120px !important;
        height: 120px !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        border: 1px solid var(--orb-border) !important;
        display: block !important;
    }

    .ev-doc-preview img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    .ev-empty {
        color: var(--orb-muted) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }

    /* Manage Page (manage.blade.php) UI Styling */
    .em-layout {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 20px !important;
    }

    .em-badges {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
        margin-top: 10px !important;
    }

    .em-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        border-radius: 999px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        font-size: 10px !important;
        padding: 4px 10px !important;
        background: rgba(255, 255, 255, 0.2) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
    }

    .em-badge i {
        font-size: 8px !important;
    }

    .em-badge.em-badge-success {
        background: rgba(22, 163, 74, 0.25) !important;
        border-color: rgba(22, 163, 74, 0.4) !important;
    }

    .em-badge.em-badge-warning {
        background: rgba(217, 119, 6, 0.25) !important;
        border-color: rgba(217, 119, 6, 0.4) !important;
    }

    .em-badge.em-badge-danger {
        background: rgba(220, 38, 38, 0.25) !important;
        border-color: rgba(220, 38, 38, 0.4) !important;
    }

    .em-badge.em-badge-info {
        background: rgba(2, 106, 162, 0.25) !important;
        border-color: rgba(2, 106, 162, 0.4) !important;
    }

    .em-section {
        margin-bottom: 24px !important;
        padding-bottom: 16px !important;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    .em-section:last-child {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
        border-bottom: none !important;
    }

    .em-section-title {
        font-size: 13px !important;
        font-weight: 850 !important;
        color: var(--orb-primary) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 16px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .em-form-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 16px !important;
    }

    @media (max-width: 1200px) {
        .em-form-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }

    @media (max-width: 991px) {
        .em-form-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 575px) {
        .em-form-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .edit-mode .editable {
        background: #fff !important;
        border-color: var(--orb-primary) !important;
    }

    .edit-mode .editable-select {
        background: #fff !important;
        border-color: var(--orb-primary) !important;
    }

    .em-file-view-box {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 8px 12px !important;
        border-radius: 9px !important;
        border: 1px solid var(--orb-border) !important;
        background: #F8FAFC !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #475467 !important;
        margin-bottom: 8px !important;
    }

    .em-file-view-box a {
        color: var(--orb-primary) !important;
        font-weight: 700 !important;
        text-decoration: none !important;
    }

    .em-upload-control {
        display: none !important;
    }

    .edit-mode .em-upload-control {
        display: block !important;
    }

    .em-upload-label {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 12px 16px !important;
        border-radius: 12px !important;
        border: 2px dashed rgba(75, 0, 232, 0.2) !important;
        background: #FDFDFF !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        margin: 0 !important;
    }

    .em-upload-label:hover {
        background: var(--orb-soft) !important;
        border-color: var(--orb-primary) !important;
    }

    .em-upload-label input {
        display: none !important;
    }

    .em-upload-icon {
        font-size: 20px !important;
        color: var(--orb-primary) !important;
    }

    .em-upload-text {
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
    }

    .em-upload-text strong {
        font-size: 13px !important;
        color: var(--orb-text) !important;
    }

    .em-upload-text small {
        font-size: 11px !important;
        color: var(--orb-muted) !important;
    }

    .em-error {
        font-size: 11px !important;
        color: #D92D20 !important;
        font-weight: 600 !important;
        margin-top: 4px !important;
    }

    /* Salary histories & Documents inside Manage card */
    .salary-table-wrap,
    .em-doc-table-wrap {
        width: 100% !important;
        overflow-x: auto !important;
    }

    .salary-table,
    .em-doc-table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .salary-table th,
    .em-doc-table th {
        background: #F8FAFC !important;
        color: var(--orb-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 12px 16px !important;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    .salary-table td,
    .em-doc-table td {
        padding: 14px 16px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid var(--orb-border) !important;
        vertical-align: middle !important;
    }

    .salary-table tbody tr:hover td,
    .em-doc-table tbody tr:hover td {
        background: #FDFDFF !important;
    }

    .salary-pill,
    .em-doc-pill {
        display: inline-flex !important;
        align-items: center !important;
        border-radius: 999px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        font-size: 10px !important;
        padding: 4px 8px !important;
    }

    .salary-active,
    .em-doc-verified {
        background: #DCFCE7 !important;
        color: #166534 !important;
    }

    .salary-closed,
    .em-doc-rejected {
        background: #FEE2E2 !important;
        color: #B42318 !important;
    }

    .em-doc-pending {
        background: #FEF3C7 !important;
        color: #92400E !important;
    }

    .em-doc-required {
        background: #FFFAEB !important;
        color: #B54708 !important;
    }

    .em-doc-optional {
        background: #F1F5F9 !important;
        color: #475569 !important;
    }

    .salary-type {
        background: #E0F2FE !important;
        color: #0369A1 !important;
    }

    .empty-history {
        padding: 24px !important;
        text-align: center !important;
        color: var(--orb-muted) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }

    .em-doc-name {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }

    .em-doc-icon {
        width: 32px !important;
        height: 32px !important;
        border-radius: 8px !important;
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 14px !important;
    }

    .em-doc-title {
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--orb-text) !important;
    }

    .em-doc-sub {
        font-size: 11px !important;
        color: var(--orb-muted) !important;
        font-weight: 500 !important;
    }

    .em-doc-actions {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
    }

    .em-doc-view {
        color: var(--orb-primary) !important;
        font-weight: 700 !important;
        text-decoration: none !important;
    }

    .em-doc-view:hover {
        text-decoration: underline !important;
    }

    .em-reupload-label {
        color: #12B76A !important;
        font-weight: 750 !important;
        cursor: pointer !important;
        margin: 0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
    }

    .em-reupload-label input {
        display: none !important;
    }

    /* Improved Premium Avatar rendering system styling */
    .att-avatar-img {
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        object-fit: cover !important;
        display: block !important;
        border: 1px solid rgba(75, 0, 232, 0.1) !important;
        flex-shrink: 0 !important;
    }
</style>