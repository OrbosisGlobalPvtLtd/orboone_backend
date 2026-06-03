<style>
    :root {
        --dm-primary: var(--orb-primary, #4B00E8);
        --dm-secondary: var(--orb-secondary, #FF5252);
        --dm-bg: #F6F7FB;
        --dm-border: #E7EAF3;
        --dm-text: #101828;
        --dm-muted: #667085;
        --dm-soft: #F4F2FF;
        --dm-shadow: 0 14px 35px rgba(16,24,40,.07);
        
        --dm-success-bg: #ECFDF3;
        --dm-success-text: #027A48;
        --dm-warning-bg: #FFFAEB;
        --dm-warning-text: #B54708;
        --dm-danger-bg: #FEF3F2;
        --dm-danger-text: #B42318;
    }

    /* Reset / Global Layout Spacing */
    .dm-page {
        padding: 24px !important;
        background: var(--dm-bg) !important;
        min-height: calc(100vh - 90px) !important;
        font-family: 'Outfit', 'Inter', sans-serif !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 24px !important;
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        overflow-x: hidden !important;
    }

    @media (max-width: 991px) {
        .dm-page {
            padding: 18px !important;
            gap: 18px !important;
        }
    }

    @media (max-width: 575px) {
        .dm-page {
            padding: 12px !important;
            gap: 12px !important;
        }
    }

    /* Premium Purple Gradient Hero */
    .dm-hero {
        background: linear-gradient(135deg, var(--dm-primary) 0%, var(--dm-secondary) 100%) !important;
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
    }

    .dm-hero::before {
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

    .dm-hero .dm-kicker {
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

    .dm-hero h1 {
        font-size: 26px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .dm-hero p {
        font-size: 14px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 6px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .dm-hero-actions {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        flex-shrink: 0 !important;
    }

    @media (max-width: 768px) {
        .dm-hero {
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 24px !important;
        }
        .dm-hero-actions {
            width: 100% !important;
            flex-wrap: wrap !important;
        }
        .dm-hero-actions .dm-btn {
            flex: 1 1 auto !important;
            justify-content: center !important;
        }
    }

    /* Premium Pill Buttons */
    .dm-btn {
        height: 42px !important;
        padding: 0 20px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        border: none !important;
        cursor: pointer !important;
        text-decoration: none !important;
        outline: none !important;
    }

    .dm-btn:hover {
        text-decoration: none !important;
    }

    .dm-btn-primary {
        background: #fff !important;
        color: var(--dm-primary) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    }

    .dm-btn-primary:hover {
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
    }

    .dm-btn-gradient {
        background: linear-gradient(135deg, var(--dm-primary) 0%, var(--dm-secondary) 100%) !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.15) !important;
    }

    .dm-btn-gradient:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.25) !important;
        color: #fff !important;
    }

    .dm-btn-light {
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
    }

    .dm-btn-light:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
    }

    .dm-btn-dark-light {
        background: #EAECEF !important;
        color: #4A5568 !important;
    }

    .dm-btn-dark-light:hover {
        background: #DFE2E6 !important;
        color: #2D3748 !important;
    }

    .dm-btn-danger {
        background: #FFF1F2 !important;
        color: #E11D48 !important;
        border: 1px solid #FFE4E6 !important;
    }

    .dm-btn-danger:hover {
        background: #FFE4E6 !important;
        color: #BE123C !important;
    }

    /* Premium Summary Cards */
    .dm-metrics-grid {
        margin: 0 -8px 0 -8px !important;
    }

    .dm-metrics-grid [class*="col-"] {
        padding: 0 8px !important;
    }

    .dm-metric-card {
        background: #fff !important;
        border-radius: 18px !important;
        border: 1px solid var(--dm-border) !important;
        padding: 16px 20px !important;
        box-shadow: var(--dm-shadow) !important;
        display: flex !important;
        align-items: center !important;
        gap: 16px !important;
        height: 100% !important;
        position: relative !important;
        overflow: hidden !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    }

    .dm-metric-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 16px 36px rgba(16, 24, 40, 0.08) !important;
    }

    .dm-metric-card.border-bottom-primary { border-bottom: 3px solid var(--dm-primary) !important; }
    .dm-metric-card.border-bottom-success { border-bottom: 3px solid #027A48 !important; }
    .dm-metric-card.border-bottom-info { border-bottom: 3px solid #026AA2 !important; }
    .dm-metric-card.border-bottom-warning { border-bottom: 3px solid #B54708 !important; }
    .dm-metric-card.border-bottom-danger { border-bottom: 3px solid #B42318 !important; }

    .dm-metric-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        flex-shrink: 0 !important;
    }

    .dm-icon-primary { background: var(--dm-soft) !important; color: var(--dm-primary) !important; }
    .dm-icon-success { background: var(--dm-success-bg) !important; color: var(--dm-success-text) !important; }
    .dm-icon-warning { background: var(--dm-warning-bg) !important; color: var(--dm-warning-text) !important; }
    .dm-icon-danger { background: var(--dm-danger-bg) !important; color: var(--dm-danger-text) !important; }
    .dm-icon-info { background: #F0F9FF !important; color: #026AA2 !important; }

    .dm-metric-content {
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
        min-width: 0 !important;
        flex-grow: 1 !important;
    }

    .dm-metric-label {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: var(--dm-muted) !important;
        letter-spacing: 0.05em !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .dm-metric-value {
        font-size: 20px !important;
        font-weight: 900 !important;
        color: var(--dm-text) !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .dm-metric-trend {
        font-size: 11px !important;
        font-weight: 700 !important;
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
    }

    /* Premium Document Card & Table Layouts */
    .dm-card {
        background: #fff !important;
        border: 1px solid var(--dm-border) !important;
        border-radius: 22px !important;
        box-shadow: var(--dm-shadow) !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
    }

    .dm-table-header {
        padding: 20px 24px !important;
        border-bottom: 1px solid var(--dm-border) !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 16px !important;
    }

    .dm-table-head-left {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
    }

    .dm-table-head-left .dm-icon-box {
        width: 42px !important;
        height: 42px !important;
        border-radius: 12px !important;
        background: var(--dm-soft) !important;
        color: var(--dm-primary) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
    }

    .dm-table-title {
        font-size: 16px !important;
        font-weight: 900 !important;
        color: var(--dm-text) !important;
        margin: 0 !important;
    }

    .dm-table-subtitle {
        font-size: 12px !important;
        color: var(--dm-muted) !important;
        margin: 3px 0 0 0 !important;
    }

    /* Attached Filter Block */
    .dm-filter-wrapper {
        background: #F8FAFC !important;
        border-bottom: 1px solid var(--dm-border) !important;
        padding: 16px 24px !important;
    }

    .dm-filter-row {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: flex-end !important;
        gap: 14px !important;
    }

    .dm-filter-row .dm-filter-col {
        flex: 1 1 200px !important;
        min-width: 0 !important;
    }

    .dm-filter-label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--dm-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .dm-filter-control {
        height: 40px !important;
        border-radius: 9px !important;
        border: 1px solid var(--dm-border) !important;
        background: #fff !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--dm-text) !important;
        width: 100% !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    .dm-filter-control:focus {
        border-color: var(--dm-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    /* Form Modals */
    .dm-modal-header {
        background: linear-gradient(135deg, var(--dm-primary) 0%, var(--dm-secondary) 100%) !important;
        padding: 24px !important;
        border: none !important;
        color: #fff !important;
        position: relative !important;
    }

    .dm-modal-header .modal-title {
        font-size: 18px !important;
        font-weight: 900 !important;
        color: #fff !important;
        margin: 0 !important;
    }

    .dm-modal-header p {
        font-size: 12px !important;
        color: rgba(255, 255, 255, 0.8) !important;
        margin: 4px 0 0 0 !important;
    }

    .dm-modal-header .close {
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
        position: absolute !important;
        right: 20px !important;
        top: 20px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    .dm-modal-header .close:hover {
        opacity: 1 !important;
        background: rgba(255, 255, 255, 0.25) !important;
    }

    .dm-modal-body {
        padding: 24px !important;
        background: #fff !important;
    }

    .dm-modal-footer {
        padding: 16px 24px !important;
        border-top: 1px solid var(--dm-border) !important;
        background: #F8FAFC !important;
        display: flex !important;
        justify-content: flex-end !important;
        gap: 12px !important;
    }

    /* Modal Form Fields */
    .dm-form-group {
        display: flex !important;
        flex-direction: column !important;
        gap: 6px !important;
        margin-bottom: 0 !important;
    }

    .dm-form-group label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: var(--dm-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin: 0 !important;
    }

    .dm-form-control, .dm-form-group .form-control, .dm-form-group select {
        height: 42px !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        border: 1px solid var(--dm-border) !important;
        border-radius: 9px !important;
        background: #fff !important;
        color: var(--dm-text) !important;
        outline: none !important;
        box-sizing: border-box !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    .dm-form-control:focus, .dm-form-group .form-control:focus, .dm-form-group select:focus {
        border-color: var(--dm-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
    }

    .dm-form-group textarea.form-control {
        height: auto !important;
        min-height: 80px !important;
        resize: vertical !important;
    }

    /* Document Status Pill Badges */
    .dm-badge {
        font-size: 11px !important;
        font-weight: 800 !important;
        padding: 4px 12px !important;
        border-radius: 50px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.03em !important;
    }

    .dm-badge-warning { background: var(--dm-warning-bg) !important; color: var(--dm-warning-text) !important; }
    .dm-badge-success { background: var(--dm-success-bg) !important; color: var(--dm-success-text) !important; }
    .dm-badge-danger { background: var(--dm-danger-bg) !important; color: var(--dm-danger-text) !important; }
    .dm-badge-purple { background: var(--dm-soft) !important; color: var(--dm-primary) !important; }
    .dm-badge-secondary { background: #F1F5F9 !important; color: #475569 !important; }

    /* Compact Pill Action Buttons for Tables */
    .dm-action-btn-pill {
        height: 30px !important;
        padding: 0 12px !important;
        border-radius: 50px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        transition: all 0.2s ease !important;
        border: none !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    .dm-action-btn-pill:hover {
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    .dm-action-btn-primary { background: var(--dm-soft) !important; color: var(--dm-primary) !important; }
    .dm-action-btn-success { background: var(--dm-success-bg) !important; color: var(--dm-success-text) !important; }
    .dm-action-btn-danger { background: var(--dm-danger-bg) !important; color: var(--dm-danger-text) !important; }
    .dm-action-btn-light { background: #F1F5F9 !important; color: #475569 !important; }

    /* Tables scrollable wrap */
    .dm-table-wrap {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .dm-table {
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .dm-table thead th {
        background: #F8FAFC !important;
        color: var(--dm-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 14px 24px !important;
        border-top: none !important;
        border-bottom: 1px solid var(--dm-border) !important;
    }

    .dm-table tbody td {
        padding: 16px 24px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--dm-text) !important;
        border-bottom: 1px solid var(--dm-border) !important;
        vertical-align: middle !important;
    }

    .dm-table tbody tr:hover td {
        background: #FDFDFF !important;
    }

    /* Modal Layout Standard sizes */
    .modal-dialog.modal-xs { max-width: 480px !important; }
    .modal-dialog.modal-md { max-width: 600px !important; }
    .modal-dialog.modal-lg { max-width: 760px !important; }
    .modal-dialog.modal-xl { max-width: 960px !important; }

</style>
