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

    .pp-page {
        min-height: calc(100vh - 90px);
        padding: 24px !important;
        background: var(--orb-bg) !important;
        box-sizing: border-box !important;
        width: 100% !important;
    }

    .pp-container,
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

    .orb-page-actions {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        flex-shrink: 0 !important;
        flex-wrap: wrap !important;
        z-index: 2 !important;
    }

    .orb-btn-light {
        height: 38px !important;
        padding: 0 16px !important;
        border-radius: 50px !important;
        font-size: 12.5px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
        white-space: nowrap !important;
    }

    .orb-btn-light:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
    }

    /* Metric Stat Grid & Cards */
    .eo-page.pp-page {
        width: 100% !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    .pp-page .eo-stat-grid {
        display: grid !important;
        grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
        gap: 12px !important;
        margin-bottom: 20px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .pp-page .eo-stat {
        min-height: 90px !important;
        padding: 14px 16px !important;
        border-radius: 18px !important;
        background: #fff !important;
        border: 1px solid var(--orb-border) !important;
        box-shadow: 0 8px 24px rgba(16, 24, 40, .05) !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        box-sizing: border-box !important;
        transition: all 0.25s ease !important;
    }

    .pp-page .eo-stat:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 12px 30px rgba(16, 24, 40, .08) !important;
    }

    .pp-page .eo-stat.border-bottom-primary { border-bottom: 3.5px solid var(--orb-primary) !important; }
    .pp-page .eo-stat.border-bottom-warning { border-bottom: 3.5px solid #F79009 !important; }
    .pp-page .eo-stat.border-bottom-info { border-bottom: 3.5px solid #15B79E !important; }
    .pp-page .eo-stat.border-bottom-success { border-bottom: 3.5px solid #12B76A !important; }
    .pp-page .eo-stat.border-bottom-danger { border-bottom: 3.5px solid #F04438 !important; }

    .pp-page .eo-stat-icon {
        width: 42px !important;
        height: 42px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        flex-shrink: 0 !important;
    }

    .pp-page .eo-stat-icon.primary { background: rgba(75, 0, 232, 0.08) !important; color: var(--orb-primary) !important; }
    .pp-page .eo-stat-icon.warning { background: rgba(247, 144, 9, 0.08) !important; color: #F79009 !important; }
    .pp-page .eo-stat-icon.info { background: rgba(21, 183, 158, 0.08) !important; color: #15B79E !important; }
    .pp-page .eo-stat-icon.success { background: rgba(18, 183, 106, 0.08) !important; color: #12B76A !important; }
    .pp-page .eo-stat-icon.danger { background: rgba(240, 68, 56, 0.08) !important; color: #F04438 !important; }

    .pp-page .eo-stat-label {
        margin: 0 0 3px 0 !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        color: var(--orb-muted) !important;
        line-height: 1.2 !important;
    }

    .pp-page .eo-stat-value {
        margin: 0 !important;
        font-size: 22px !important;
        font-weight: 900 !important;
        color: var(--orb-text) !important;
        line-height: 1 !important;
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
        border-bottom: 1px so+ `123490-/-lid var(--orb-border) !important;
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
        grid-template-columns: 2fr 1.5fr 1.5fr auto !important;
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

    #pendingProfilesTable {
        width: 100% !important;
        min-width: 850px !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    #pendingProfilesTable thead th {
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

    #pendingProfilesTable tbody td {
        padding: 13px 16px !important;
        font-size: 13px !important;
        font-weight: 650 !important;
        color: var(--orb-text) !important;
        border-bottom: 1px solid #F1F3F8 !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    #pendingProfilesTable tbody tr:hover td {
        background: #FCFAFF !important;
    }

    /* Desktop Row Components */
    .emp-cell {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 12px !important;
        min-width: 220px !important;
    }

    .emp-info {
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        min-width: 0 !important;
    }

    .emp-name {
        color: var(--orb-text, #101828) !important;
        font-size: 13.5px !important;
        font-weight: 850 !important;
        line-height: 1.3 !important;
        white-space: nowrap !important;
    }

    .emp-email {
        color: var(--orb-muted, #667085) !important;
        font-size: 11.5px !important;
        font-weight: 500 !important;
        line-height: 1.3 !important;
        margin-top: 2px !important;
        white-space: nowrap !important;
    }

    /* Badges */
    .code-badge,
    .status-badge,
    .lock-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        border-radius: 50px !important;
        padding: 4px 10px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        white-space: nowrap !important;
    }

    .code-badge {
        background: #F4F2FF !important;
        color: var(--orb-primary, #4B00E8) !important;
        border: 1px solid rgba(75, 0, 232, 0.12) !important;
        border-radius: 6px !important;
        letter-spacing: 0.2px !important;
        padding: 3px 8px !important;
        font-size: 10.5px !important;
    }

    .status-pending {
        background: #FFFAEB !important;
        color: #B54708 !important;
        border: 1px solid rgba(181, 71, 8, 0.15) !important;
    }

    .status-submitted {
        background: #F0F9FF !important;
        color: #026AA2 !important;
        border: 1px solid rgba(2, 106, 162, 0.15) !important;
    }

    .status-approved {
        background: #ECFDF3 !important;
        color: #027A48 !important;
        border: 1px solid rgba(2, 122, 72, 0.15) !important;
    }

    .status-rejected {
        background: #FEF3F2 !important;
        color: #B42318 !important;
        border: 1px solid rgba(180, 35, 24, 0.15) !important;
    }

    .status-badge i {
        font-size: 6px !important;
    }

    .lock-badge {
        background: #F2F4F7 !important;
        color: #475467 !important;
        border: 1px solid #E4E7EC !important;
        font-size: 11px !important;
        padding: 4px 8px !important;
    }

    /* Approve Switch */
    .complete-cell {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
    }

    .complete-switch {
        position: relative !important;
        width: 40px !important;
        height: 22px !important;
        margin: 0 !important;
        display: inline-block !important;
    }

    .complete-switch input {
        opacity: 0 !important;
        width: 0 !important;
        height: 0 !important;
    }

    .slider {
        position: absolute !important;
        cursor: pointer !important;
        inset: 0 !important;
        background: #D0D5DD !important;
        transition: .2s !important;
        border-radius: 50px !important;
    }

    .slider:before {
        content: "" !important;
        position: absolute !important;
        height: 16px !important;
        width: 16px !important;
        left: 3px !important;
        top: 3px !important;
        background: #fff !important;
        transition: .2s !important;
        border-radius: 50% !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .15) !important;
    }

    .complete-switch input:checked + .slider {
        background: #12B76A !important;
    }

    .complete-switch input:checked + .slider:before {
        transform: translateX(18px) !important;
    }

    /* Action Buttons */
    .actions {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        flex-wrap: nowrap !important;
    }

    .action-btn {
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

    .action-btn:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .08) !important;
    }

    .action-view:hover {
        background: var(--orb-primary, #4B00E8) !important;
        border-color: var(--orb-primary, #4B00E8) !important;
        color: #fff !important;
    }

    .action-edit:hover {
        background: #F79009 !important;
        border-color: #F79009 !important;
        color: #fff !important;
    }

    .action-reject:hover {
        background: #F04438 !important;
        border-color: #F04438 !important;
        color: #fff !important;
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
    .confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(15, 23, 42, .6);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        box-sizing: border-box;
    }

    .confirm-box {
        width: 100%;
        max-width: 460px;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 25px 70px rgba(15, 23, 42, .25);
        overflow: hidden;
        border: none;
        animation: modalPop .2s ease-out;
    }

    @keyframes modalPop {
        0% { transform: scale(0.95); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    .confirm-header {
        padding: 16px 20px;
        color: #fff;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .confirm-header.approve-header { background: linear-gradient(135deg, #059669, #10B981); }
    .confirm-header.reject-header { background: linear-gradient(135deg, #DC2626, #EF4444); }

    .confirm-title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #fff;
    }

    .confirm-subtitle {
        margin: 4px 0 0;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 500;
    }

    .confirm-close-btn {
        background: transparent;
        border: 0;
        color: #fff;
        font-size: 22px;
        line-height: 1;
        opacity: 0.85;
        cursor: pointer;
        padding: 0;
        transition: opacity .15s;
    }

    .confirm-close-btn:hover {
        opacity: 1;
    }

    .confirm-body {
        padding: 18px 20px;
    }

    .confirm-alert {
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 12px;
    }

    .confirm-alert.approve-alert { background: #ECFDF5; border: 1px solid #D1FAE5; color: #065F46; }
    .confirm-alert.reject-alert { background: #FFF5F5; border: 1px solid #FEE2E2; color: #991B1B; }

    .reject-textarea {
        width: 100%;
        min-height: 85px;
        border: 1px solid var(--orb-border, #E2E8F0);
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 12.5px;
        font-weight: 600;
        outline: none;
        transition: all .2s;
        box-sizing: border-box;
    }

    .reject-textarea:focus {
        border-color: #EF4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, .12) !important;
    }

    .confirm-footer {
        background: #F9FAFB;
        border-top: 1px solid #E5E7EB;
        padding: 12px 20px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
    }

    .btn-cancel,
    .btn-confirm,
    .btn-reject {
        border: 0;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 12.5px;
        font-weight: 750;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all .2s ease;
        cursor: pointer;
    }

    .btn-cancel {
        background: #F3F4F6;
        color: #374151;
        border: 1px solid #E5E7EB;
    }

    .btn-cancel:hover { background: #E5E7EB; color: #111827; }

    .btn-confirm {
        background: linear-gradient(135deg, #059669, #10B981);
        color: #fff;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    .btn-confirm:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
    }

    .btn-reject {
        background: linear-gradient(135deg, #DC2626, #EF4444);
        color: #fff;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }

    .btn-reject:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
    }

    /* Media Queries */
    @media (max-width: 1200px) {
        .pp-page .eo-stat-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }

        .eo-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 991px) {
        .pp-page {
            padding: 16px !important;
        }

        .orb-page-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 20px 22px !important;
        }

        .orb-page-actions {
            width: 100% !important;
            margin-top: 6px !important;
        }

        .orb-btn-light {
            flex: 1 1 auto !important;
        }

        .pp-page .eo-stat-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }

        .eo-filter-grid {
            grid-template-columns: 1fr 1fr !important;
        }
    }

    @media (max-width: 767px) {
        .pp-page .eo-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }

        .pp-page .eo-stat:last-child {
            grid-column: span 2 !important;
        }

        .pp-page .eo-stat {
            min-height: auto !important;
            padding: 12px 14px !important;
        }

        .pp-page .eo-stat-icon {
            width: 38px !important;
            height: 38px !important;
            font-size: 15px !important;
        }

        .pp-page .eo-stat-value {
            font-size: 20px !important;
        }

        .eo-filter-grid {
            grid-template-columns: 1fr !important;
        }

        .orb-table-tools-bar {
            flex-direction: column !important;
            align-items: stretch !important;
            padding: 12px 16px !important;
            gap: 12px !important;
        }


    }

    @media (max-width: 575px) {
        .pp-page {
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

        .orb-page-actions {
            gap: 8px !important;
        }

        .orb-btn-light {
            height: 36px !important;
            padding: 0 12px !important;
            font-size: 11.5px !important;
            width: 100% !important;
        }

        .pp-page .eo-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 8px !important;
            margin-bottom: 14px !important;
        }

        .pp-page .eo-stat {
            padding: 10px 12px !important;
            border-radius: 14px !important;
            gap: 10px !important;
        }

        .pp-page .eo-stat-icon {
            width: 34px !important;
            height: 34px !important;
            border-radius: 10px !important;
            font-size: 13px !important;
        }

        .pp-page .eo-stat-label {
            font-size: 9.5px !important;
        }

        .pp-page .eo-stat-value {
            font-size: 18px !important;
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



        .confirm-box {
            border-radius: 16px !important;
        }

        .confirm-header {
            padding: 14px 16px !important;
        }

        .confirm-title {
            font-size: 15px !important;
        }

        .confirm-body {
            padding: 14px 16px !important;
        }

        .confirm-footer {
            padding: 10px 16px !important;
        }

        .btn-cancel,
        .btn-confirm,
        .btn-reject {
            flex: 1 !important;
            padding: 8px 12px !important;
            font-size: 12px !important;
        }
    }


</style>
