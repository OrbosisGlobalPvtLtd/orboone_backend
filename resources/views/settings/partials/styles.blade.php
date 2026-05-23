<style>
    :root {
        --set-primary: #4B00E8;
        --set-secondary: #8600EE;
        --set-bg: #F6F7FB;
        --set-border: #E7EAF3;
        --set-text: #101828;
        --set-muted: #667085;
        --set-soft: #F4F2FF;
        --set-shadow: 0 14px 35px rgba(16,24,40,.07);
    }

    .set-page {
        min-height: calc(100vh - 90px);
        padding: 24px;
        background: var(--set-bg);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .set-container {
        max-width: 1120px;
        margin: 0 auto;
    }

    /* Premium Gradient Hero Header */
    .set-header {
        background: linear-gradient(135deg, var(--set-primary), var(--set-secondary));
        border-radius: 26px;
        padding: 32px;
        margin-bottom: 24px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 10px 30px rgba(75, 0, 232, 0.15);
    }

    .set-kicker {
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.9;
        margin-bottom: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .set-title {
        font-size: 28px;
        font-weight: 900;
        margin: 0;
        line-height: 1.15;
    }

    .set-subtitle {
        font-size: 13px;
        font-weight: 600;
        margin: 8px 0 0;
        opacity: 0.85;
    }

    /* Glassmorphic Metrics Badge */
    .set-glass-badge {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 16px;
        padding: 12px 20px;
        min-width: 140px;
        text-align: center;
        color: #fff;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
    }

    /* Premium White Settings Card */
    .set-card {
        background: #fff;
        border: 1px solid var(--set-border);
        border-radius: 22px;
        box-shadow: var(--set-shadow);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .set-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--set-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .set-head-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .set-icon-box {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--set-soft);
        color: var(--set-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .set-card-title {
        font-size: 15px;
        font-weight: 850;
        color: var(--set-text);
        margin: 0;
    }

    .set-card-subtitle {
        font-size: 11px;
        font-weight: 650;
        color: var(--set-muted);
        margin: 4px 0 0;
    }

    .set-card-body {
        padding: 24px;
    }

    /* Input Controls */
    .set-label {
        display: block;
        margin: 0 0 6px;
        color: var(--set-muted);
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .6px;
    }

    .set-control {
        width: 100%;
        height: 42px;
        border-radius: 12px !important;
        border: 1px solid var(--set-border) !important;
        background: #F9FAFB !important;
        color: var(--set-text) !important;
        font-size: 13px;
        font-weight: 700;
        padding: 8px 14px;
        transition: all 0.25s ease;
    }

    .set-control:focus {
        border-color: var(--set-primary) !important;
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.08) !important;
        outline: none;
    }

    textarea.set-control {
        height: auto !important;
        min-height: 96px;
    }

    /* Pill Buttons */
    .set-btn {
        min-height: 40px;
        border-radius: 12px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        color: #fff;
        background: linear-gradient(135deg, var(--set-primary), var(--set-secondary));
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .set-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.25);
    }

    .set-btn-soft {
        background: #F1F5F9 !important;
        color: #475569 !important;
        border: 1px solid #E2E8F0 !important;
        box-shadow: none !important;
    }

    .set-btn-soft:hover {
        background: #E2E8F0 !important;
        color: #1E293B !important;
    }

    .set-btn-danger {
        background: #FEF2F2 !important;
        color: #EF4444 !important;
        border: 1px solid #FEE2E2 !important;
        box-shadow: none !important;
    }

    .set-btn-danger:hover {
        background: #FEE2E2 !important;
        color: #DC2626 !important;
    }

    /* Responsive Spacing & Grid system */
    .set-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    @media (max-width: 768px) {
        .set-page {
            padding: 18px;
        }

        .set-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 24px;
        }

        .set-glass-badge {
            width: 100%;
        }

        .set-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .set-page {
            padding: 12px;
        }
    }

    /* Toggles / Custom Switches */
    .switch {
        position: relative;
        display: inline-block;
        width: 42px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #E2E8F0;
        transition: .3s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    input:checked + .slider {
        background-color: var(--set-primary);
    }

    input:checked + .slider:before {
        transform: translateX(18px);
    }

    /* Image Preview Boxes */
    .set-preview {
        width: 80px;
        height: 80px;
        border: 1px solid var(--set-border);
        border-radius: 14px;
        background: #F9FAFB;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: var(--set-muted);
        font-size: 11px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .set-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    /* Clean visual tables */
    .set-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .set-table th {
        background: var(--set-soft);
        color: var(--set-primary);
        font-weight: 850;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.6px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--set-border);
    }

    .set-table td {
        padding: 18px 20px;
        vertical-align: middle;
        border-bottom: 1px solid var(--set-border);
        color: var(--set-text);
        font-size: 13px;
    }

    .set-table tr:last-child td {
        border-bottom: 0;
    }

    .set-badge {
        background: var(--set-soft);
        color: var(--set-primary);
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 99px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
    }
</style>
