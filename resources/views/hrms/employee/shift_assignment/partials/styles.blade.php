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

    @media (max-width: 991px) {
        .shift-assignment-page {
            padding: 16px !important;
        }
    }

    @media (max-width: 575px) {
        .shift-assignment-page {
            padding: 12px 8px !important;
        }
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

    @media (max-width: 768px) {
        .report-header-premium {
            padding: 18px 20px !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 16px !important;
            border-radius: 16px !important;
        }
        .report-header-premium .title-area h3 {
            font-size: 20px !important;
        }
        .report-btn-pill {
            width: 100% !important;
        }
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

    @media (max-width: 575px) {
        .orb-table-card {
            border-radius: 14px !important;
        }
        .orb-table-card-header {
            padding: 14px 16px !important;
        }
        .orb-title-wrap {
            gap: 10px !important;
        }
        .orb-title-wrap h3 {
            font-size: 15px !important;
        }
        .orb-title-wrap p {
            font-size: 11.5px !important;
        }
    }

    .report-filters-attached {
        background: #FAFAFC !important;
        border-bottom: 1px solid #EEF2F7 !important;
        padding: 16px 24px !important;
    }

    @media (max-width: 575px) {
        .report-filters-attached {
            padding: 14px 16px !important;
        }
    }

    .report-filter-grid {
        display: grid !important;
        grid-template-columns: 1.3fr 1.1fr 1.2fr auto !important;
        gap: 12px !important;
        align-items: flex-end !important;
    }

    @media (max-width: 991px) {
        .report-filter-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 575px) {
        .report-filter-grid {
            grid-template-columns: 1fr !important;
        }
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

    .report-filter-grid .select2-container {
        width: 100% !important;
    }

    .report-filter-grid .select2-container .select2-selection--single {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid #E2E8F0 !important;
        display: flex !important;
        align-items: center !important;
    }

    .report-filter-grid .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 13px !important;
        color: #101828 !important;
    }

    .report-filter-grid .select2-container .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    .shift-filter-actions-col {
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-end !important;
    }

    .shift-filter-actions-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        white-space: nowrap !important;
    }

    @media (max-width: 575px) {
        .shift-filter-actions-wrap {
            width: 100% !important;
        }
        .btn-shift-search,
        .btn-shift-reset {
            flex: 1 1 50% !important;
            min-width: 0 !important;
            justify-content: center !important;
        }
    }

    .btn-shift-search {
        height: 38px !important;
        border-radius: 10px !important;
        background: linear-gradient(135deg, var(--orb-primary, #6366F1), var(--orb-secondary, #8B5CF6)) !important;
        color: #ffffff !important;
        border: none !important;
        padding: 0 18px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.22) !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
        min-width: 95px !important;
    }

    .btn-shift-search:hover {
        opacity: 0.94 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.32) !important;
        color: #ffffff !important;
    }

    .btn-shift-reset {
        height: 38px !important;
        border-radius: 10px !important;
        background: #F8FAFC !important;
        border: 1px solid #E2E8F0 !important;
        color: #475569 !important;
        padding: 0 16px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        box-shadow: 0 2px 6px rgba(16, 24, 40, .04) !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
        text-decoration: none !important;
        min-width: 85px !important;
    }

    .btn-shift-reset:hover {
        background: #F1F5F9 !important;
        color: var(--orb-primary, #6366F1) !important;
        border-color: #CBD5E1 !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    .report-table th,
    .report-table td {
        white-space: nowrap !important;
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
