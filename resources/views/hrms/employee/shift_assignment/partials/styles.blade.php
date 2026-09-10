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

    .shift-assignment-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    /* Premium Purple Gradient Hero Header */
    .report-header-premium {
        background: linear-gradient(135deg, var(--orb-primary, #6366F1) 0%, var(--orb-secondary, #4F46E5) 100%) !important;
        border-radius: 20px !important;
        padding: 24px 32px !important;
        color: #fff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 12px 30px rgba(99, 102, 241, 0.18) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
        border: none !important;
    }

    .report-header-premium::before {
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

    .report-header-premium .title-area h3 {
        font-size: 24px !important;
        font-weight: 800 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
    }

    .report-header-premium .title-area p {
        font-size: 13.5px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 4px 0 0 0 !important;
        font-weight: 500 !important;
    }

    .report-header-premium .header-kicker {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.15em !important;
        color: rgba(255, 255, 255, 0.8) !important;
        margin-bottom: 6px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    /* Premium Pill Button */
    .report-btn-pill {
        height: 40px !important;
        padding: 0 20px !important;
        border-radius: 50px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        cursor: pointer !important;
        text-decoration: none !important;
        outline: none !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        background: rgba(255, 255, 255, 0.2) !important;
        color: #fff !important;
    }

    .report-btn-pill:hover {
        background: rgba(255, 255, 255, 0.35) !important;
        color: #fff !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    /* Table card styling */
    .orb-table-card {
        background: #fff !important;
        border-radius: 20px !important;
        border: 1px solid #E7EAF3 !important;
        box-shadow: 0 14px 35px rgba(16, 24, 40, .06) !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
    }

    .report-filters-attached {
        background: #FAFAFC !important;
        border-bottom: 1px solid #EEF2F7 !important;
        padding: 16px 24px !important;
    }

    .report-filter-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
        gap: 14px !important;
    }

    .report-filter-grid label {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #667085 !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .report-filter-grid select,
    .report-filter-grid input {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid #E2E8F0 !important;
        font-size: 13px !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    /* Modal Form Field Labels & Time Overlay */
    .shift-modal-card-title {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        color: #667085 !important;
        margin-bottom: 14px !important;
    }

    .shift-modal-label {
        font-weight: 700 !important;
        font-size: 13px !important;
        color: #101828 !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .time-picker-container {
        position: relative !important;
        width: 100% !important;
    }
    .native-time-input {
        color: transparent !important;
        caret-color: transparent !important;
        background: transparent !important;
    }
    .native-time-input::-webkit-calendar-picker-indicator {
        cursor: pointer !important;
        opacity: 1 !important;
    }
    .time-display-val {
        position: absolute !important;
        left: 14px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        pointer-events: none !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: #101828 !important;
    }
</style>
