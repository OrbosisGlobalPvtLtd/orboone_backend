<style>
    :root {
        --orb-rose: #EC4E74;
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
        padding: 16px 10px 30px;
        background: var(--orb-bg);
    }

    .eo-container {
        max-width: 1280px;
        margin: 0 auto;
    }

    .eo-header {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
        border: 0 !important;
        border-radius: 22px !important;
        box-shadow: 0 12px 30px rgba(75, 0, 232, .16) !important;
        padding: 24px 28px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 20px !important;
        margin-bottom: 24px !important;
    }

    .eo-title {
        margin: 0 !important;
        color: #fff !important;
        font-size: 26px !important;
        font-weight: 900 !important;
        letter-spacing: -.5px !important;
    }

    .eo-subtitle {
        margin: 6px 0 0 !important;
        color: rgba(255, 255, 255, 0.85) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }

    .eo-code-badge {
        border-radius: 50px !important;
        padding: 8px 16px !important;
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        white-space: nowrap !important;
    }

    .eo-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-bottom: 14px;
    }

    .eo-card-head {
        padding: 14px 16px;
        border-bottom: 1px solid #EEF1F6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #fff;
    }

    .eo-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .eo-section-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--orb-primary, #4B00E8);
        background: var(--orb-soft);
        flex: 0 0 auto;
    }

    .eo-section-title h5 {
        margin: 0;
        color: var(--orb-text);
        font-size: 15px;
        font-weight: 900;
    }

    .eo-section-title p {
        margin: 2px 0 0;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .eo-card-body {
        padding: 16px;
    }

    .eo-field {
        margin-bottom: 14px;
    }

    .eo-field label {
        display: block;
        margin-bottom: 6px;
        color: #344054;
        font-size: 12px;
        font-weight: 850;
    }

    .required {
        color: var(--orb-rose);
    }

    .form-control,
    .form-select {
        min-height: 42px;
        border-radius: 12px;
        border: 1px solid #DDE3EE;
        font-size: 13px;
        font-weight: 650;
        color: #111827;
        background: #fff;
        box-shadow: none;
    }

    .form-control::placeholder {
        color: #98A2B3;
        font-weight: 500;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--orb-secondary, #FF5252);
        box-shadow: 0 0 0 .16rem rgba(134, 0, 238, .10);
    }

    select.form-select,
    .form-select {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        width: 100% !important;
        cursor: pointer !important;
        padding: 9px 40px 9px 13px !important;
        line-height: 1.35 !important;
        color: #101828 !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3Csvg width='16' height='16' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%234B00E8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 14px center !important;
        background-size: 16px 16px !important;
        transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease, transform .18s ease !important;
    }

    select.form-select:hover,
    .form-select:hover {
        border-color: rgba(75, 0, 232, .34) !important;
        background-color: #FCFAFF !important;
    }

    select.form-select:focus,
    .form-select:focus {
        border-color: var(--orb-secondary, #FF5252) !important;
        box-shadow: 0 0 0 .16rem rgba(134, 0, 238, .10) !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3Csvg width='16' height='16' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%238600EE' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 14px center !important;
        background-size: 16px 16px !important;
    }

    .form-select:disabled {
        cursor: not-allowed;
        background-color: #F8FAFC;
        color: #98A2B3;
        opacity: 1;
    }

    .form-select option {
        color: #101828;
        background: #fff;
        font-size: 13px;
        font-weight: 700;
        padding: 10px 12px;
    }

    .form-select option:first-child {
        color: #98A2B3;
    }

    .readonly-field {
        background: #F8F5FF !important;
        border-color: rgba(75, 0, 232, .14) !important;
        color: var(--orb-primary, #4B00E8) !important;
        font-weight: 900 !important;
    }

    .disabled-soft {
        background: #F8FAFC !important;
        color: #98A2B3 !important;
        border-color: #EAECF0 !important;
    }

    input[type="date"].form-control,
    input[type="date"].eo-date {
        color-scheme: light;
    }

    input[type="date"].form-control::-webkit-calendar-picker-indicator {
        cursor: pointer;
        opacity: .75;
        filter: hue-rotate(245deg) saturate(1.4);
    }

    input[type="date"].form-control:hover::-webkit-calendar-picker-indicator {
        opacity: 1;
    }

    .small-note {
        margin-top: 5px;
        color: #7A8291;
        font-size: 11px;
        font-weight: 600;
    }

    .custom-probation-box .input-group {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        width: 100% !important;
    }

    .custom-probation-box .input-group #custom_duration_value {
        flex: 1 1 auto !important;
        width: auto !important;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .custom-probation-box .input-group #custom_duration_unit {
        flex: 0 0 auto !important;
        width: 110px !important;
        max-width: 110px !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        padding-right: 32px !important;
    }

    .eo-smart-panel {
        display: none;
        margin-top: 4px;
        border-radius: 16px;
        padding: 14px;
        background: #FBFAFF;
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .eo-panel-title {
        font-size: 13px;
        font-weight: 900;
        color: var(--orb-primary, #4B00E8);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .eo-actions-bar {
        position: sticky;
        bottom: 0;
        z-index: 30;
        background: rgba(255, 255, 255, .96);
        backdrop-filter: blur(12px);
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 12px;
        box-shadow: 0 -8px 26px rgba(16, 24, 40, .08);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .eo-actions-note {
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .eo-actions {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn-soft,
    .btn-orb,
    .btn-profile {
        border-radius: 12px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 900;
        min-height: 40px;
    }

    .btn-soft {
        background: #F4F6FB;
        border: 1px solid #E5E7EB;
        color: #111827 !important;
    }

    .btn-orb {
        border: 0;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252));
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(75, 0, 232, .16);
    }

    .btn-profile {
        border: 0;
        background: linear-gradient(135deg, #EC4E74, #D400D5);
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(212, 0, 213, .14);
    }

    .alert {
        border: 0;
        border-radius: 16px;
        box-shadow: var(--orb-shadow);
        font-weight: 650;
    }

    .eo-hidden {
        display: none !important;
    }

    @media(max-width:767px) {
        .eo-page {
            padding: 10px 8px 24px;
        }

        .eo-header {
            flex-direction: column;
            align-items: flex-start;
            border-radius: 16px;
            padding: 14px;
        }

        .eo-title {
            font-size: 21px;
        }

        .eo-subtitle {
            font-size: 12px;
        }

        .eo-code-badge {
            width: 100%;
            text-align: center;
        }

        .eo-card,
        .eo-actions-bar {
            border-radius: 16px;
        }

        .eo-card-head {
            padding: 14px;
        }

        .eo-card-body {
            padding: 14px;
        }

        .eo-actions-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .eo-actions {
            width: 100%;
        }

        .eo-actions .btn,
        .eo-actions a {
            flex: 1 1 100%;
            text-align: center;
        }
    }

    .time-picker-container {
        position: relative;
        width: 100%;
    }

    .native-time-input {
        color: transparent !important;
        caret-color: transparent !important;
        background: transparent !important;
    }

    .native-time-input::-webkit-calendar-picker-indicator {
        cursor: pointer;
        opacity: 1;
    }

    .time-display-val {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        font-size: 14px;
        font-weight: 600;
        color: var(--orb-text);
    }
</style>
