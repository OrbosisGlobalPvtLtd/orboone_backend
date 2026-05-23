<style>
    /* Premium Orbosis HRMS Access Control Design System */
    :root {
        --ac-primary: #4B00E8;
        --ac-secondary: #8600EE;
        --ac-bg: #F6F7FB;
        --ac-border: #E7EAF3;
        --ac-text: #101828;
        --ac-muted: #667085;
        --ac-soft: #F4F2FF;
        --ac-shadow: 0 14px 35px rgba(16, 24, 40, 0.07);
        
        --ac-success: #12B76A;
        --ac-success-soft: rgba(18, 183, 106, 0.1);
        --ac-danger: #F04438;
        --ac-danger-soft: rgba(240, 68, 56, 0.1);
        --ac-warning: #F79009;
        --ac-warning-soft: rgba(247, 144, 9, 0.1);
    }

    /* Page container spacing */
    .ac-page {
        min-height: calc(100vh - 90px);
        padding: 24px;
        background: var(--ac-bg);
    }

    .ac-container {
        max-width: 1320px;
        margin: 0 auto;
    }

    /* Hero Header Banner */
    .ac-header {
        background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary));
        border-radius: 26px;
        padding: 24px 30px;
        box-shadow: var(--ac-shadow);
        color: #fff;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        position: relative;
        overflow: hidden;
        flex-wrap: wrap;
        gap: 16px;
    }

    .ac-header::after {
        content: "";
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 70%);
        right: -50px;
        top: -100px;
        pointer-events: none;
    }

    .ac-kicker {
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.78);
        margin-bottom: 6px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .ac-title {
        margin: 0;
        color: #fff !important;
        font-size: 26px !important;
        font-weight: 900 !important;
        line-height: 1.2;
    }

    .ac-subtitle {
        margin: 4px 0 0;
        color: rgba(255, 255, 255, 0.85) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
    }

    /* Premium Cards & Containers */
    .ac-card {
        background: #fff;
        border: 1px solid var(--ac-border);
        border-radius: 22px;
        box-shadow: var(--ac-shadow);
        overflow: hidden;
        margin-bottom: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .ac-card-body {
        padding: 24px;
    }

    /* Table Header Alignment */
    .ac-table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid var(--ac-border);
        background: #fff;
        flex-wrap: wrap;
        gap: 12px;
    }

    .ac-table-head-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .ac-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--ac-soft);
        color: var(--ac-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        box-shadow: inset 0 2px 6px rgba(75, 0, 232, 0.05);
    }

    .ac-table-title {
        margin: 0;
        font-weight: 900;
        color: var(--ac-text);
        font-size: 15px;
    }

    .ac-table-subtitle {
        margin: 3px 0 0;
        font-size: 12px;
        color: var(--ac-muted);
        font-weight: 700;
    }

    /* Attached filter styling */
    .ac-filter-wrapper {
        padding: 16px 24px;
        background: #F8FAFC;
        border-bottom: 1px solid var(--ac-border);
    }

    .ac-filter-row {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .ac-filter-col {
        flex: 1 1 200px;
        min-width: 0;
    }

    .ac-filter-label {
        font-size: 11px;
        font-weight: 800;
        color: var(--ac-muted);
        text-transform: uppercase;
        margin-bottom: 6px;
        display: block;
    }

    .ac-filter-control {
        width: 100%;
        height: 38px;
        border-radius: 9px;
        border: 1px solid var(--ac-border);
        background: #fff;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 700;
        color: var(--ac-text);
        outline: none;
        transition: all 0.2s ease;
    }

    .ac-filter-control:focus {
        border-color: var(--ac-primary);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08);
    }

    /* Metric Aggregation Cards */
    .ac-metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .ac-metric-card {
        background: #fff;
        border: 1px solid var(--ac-border);
        border-radius: 18px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 10px 28px rgba(16, 24, 40, 0.04);
        position: relative;
        overflow: hidden;
    }

    .ac-metric-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: var(--ac-soft);
        color: var(--ac-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .ac-metric-value {
        font-size: 20px;
        font-weight: 900;
        color: var(--ac-text);
        line-height: 1.2;
    }

    .ac-metric-label {
        font-size: 11px;
        font-weight: 800;
        color: var(--ac-muted);
        text-transform: uppercase;
        margin-top: 2px;
    }

    /* Buttons & Actions */
    .ac-btn {
        min-height: 38px;
        border-radius: 12px;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none !important;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .ac-btn-primary {
        background: #fff !important;
        color: var(--ac-primary) !important;
        border-color: #fff !important;
    }

    .ac-btn-primary:hover {
        background: rgba(255, 255, 255, 0.95) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .ac-btn-soft {
        background: rgba(255, 255, 255, 0.15) !important;
        color: #fff !important;
        border-color: rgba(255, 255, 255, 0.25) !important;
    }

    .ac-btn-soft:hover {
        background: rgba(255, 255, 255, 0.25) !important;
        transform: translateY(-1px);
    }

    /* Secondary buttons for inner forms */
    .ac-card .ac-btn-primary {
        background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important;
        color: #fff !important;
    }

    .ac-card .ac-btn-primary:hover {
        background: linear-gradient(135deg, var(--ac-primary), var(--ac-secondary)) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2);
    }

    .ac-card .ac-btn-soft {
        background: #F1F5F9 !important;
        color: #475569 !important;
        border-color: #E2E8F0 !important;
    }

    .ac-card .ac-btn-soft:hover {
        background: #E2E8F0 !important;
    }

    /* Table Scrolling Mechanics */
    .ac-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .ac-table {
        width: 100%;
        border-collapse: collapse !important;
    }

    .ac-table th {
        background: #F8FAFC !important;
        color: var(--ac-muted) !important;
        font-size: 11px !important;
        font-weight: 850 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        padding: 14px 20px !important;
        border-bottom: 1px solid var(--ac-border) !important;
        border-top: none !important;
        white-space: nowrap !important;
    }

    .ac-table td {
        vertical-align: middle !important;
        color: var(--ac-text) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        padding: 14px 20px !important;
        border-bottom: 1px solid #F1F3F8 !important;
        background: #fff !important;
    }

    .ac-table tbody tr:hover td {
        background: #FAF8FF !important;
    }

    /* Badge Pills styling */
    .ac-pill {
        display: inline-flex;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 850;
        text-transform: uppercase;
        white-space: nowrap;
        background: #F1F5F9;
        color: #475569;
        letter-spacing: 0.3px;
    }

    .ac-pill-on {
        background: var(--ac-success-soft) !important;
        color: var(--ac-success) !important;
    }

    .ac-pill-off {
        background: var(--ac-danger-soft) !important;
        color: var(--ac-danger) !important;
    }

    /* Actions configuration */
    .ac-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .ac-icon-btn {
        width: 32px;
        height: 32px;
        border: 1px solid var(--ac-border);
        border-radius: 9px;
        background: #fff;
        color: var(--ac-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        text-decoration: none !important;
        cursor: pointer;
        font-size: 12px;
    }

    .ac-icon-btn:hover {
        color: var(--ac-primary);
        border-color: var(--ac-primary);
        background: var(--ac-soft);
    }

    .ac-icon-danger:hover {
        color: var(--ac-danger) !important;
        border-color: var(--ac-danger) !important;
        background: var(--ac-danger-soft) !important;
    }

    /* Forms styling system */
    .ac-label {
        display: block;
        margin-bottom: 6px;
        color: var(--ac-muted);
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ac-control {
        width: 100%;
        height: 42px;
        border-radius: 10px !important;
        border: 1px solid var(--ac-border) !important;
        background: #F9FAFB !important;
        color: var(--ac-text) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        padding: 8px 12px !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    textarea.ac-control {
        height: auto !important;
        min-height: 92px !important;
    }

    .ac-control:focus {
        border-color: var(--ac-primary) !important;
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.08) !important;
    }

    /* Grid configuration */
    .ac-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    /* Role checkcard boxes styling */
    .ac-check-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 12px;
    }

    .ac-check {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 14px 16px;
        border: 1px solid var(--ac-border);
        border-radius: 14px;
        background: #FCFCFD;
        cursor: pointer;
        margin: 0 !important;
        transition: all 0.2s ease;
    }

    .ac-check:hover {
        border-color: var(--ac-primary);
        background: var(--ac-soft);
    }

    .ac-check input[type="checkbox"] {
        margin-top: 4px;
        width: 16px;
        height: 16px;
        accent-color: var(--ac-primary);
        cursor: pointer;
    }

    .ac-check strong {
        display: block;
        color: var(--ac-text);
        font-size: 13px;
        font-weight: 800;
    }

    .ac-check span {
        display: block;
        color: var(--ac-muted);
        font-size: 11px;
        font-weight: 700;
        margin-top: 2px;
        line-height: 1.4;
    }

    .ac-section-title {
        margin: 0 0 14px;
        color: var(--ac-text);
        font-size: 15px;
        font-weight: 900;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Collapsible card groups styling */
    .ac-group-card {
        border: 1px solid var(--ac-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        margin-bottom: 16px;
        overflow: hidden;
    }

    .ac-group-header {
        padding: 16px 20px;
        background: #F8FAFC;
        border-bottom: 1px solid var(--ac-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .ac-group-body {
        padding: 20px;
    }

    @media (max-width: 768px) {
        .ac-page {
            padding: 12px;
        }
        .ac-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 20px;
        }
        .ac-grid {
            grid-template-columns: 1fr;
        }
        .ac-check-list {
            grid-template-columns: 1fr;
        }
    }
</style>
