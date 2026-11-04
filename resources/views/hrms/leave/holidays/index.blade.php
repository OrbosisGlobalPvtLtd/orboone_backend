@extends('layouts.panel')

@section('page_title', 'Holiday Management')

@section('_head')
@include('hrms.leave.shared.style')

<style>
    :root {
        --leave-primary: var(--orb-primary, #4B00E8);
        --leave-secondary: var(--orb-secondary, #8600EE);
        --leave-border: var(--orb-border, #E7EAF3);
        --leave-text: var(--orb-text, #101828);
        --leave-muted: var(--orb-muted, #667085);
        --leave-soft: var(--orb-soft, #F4F2FF);
        --leave-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .leave-page-wrap {
        padding-bottom: 24px;
    }

    .leave-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 22px 24px;
        background: radial-gradient(circle at top right, rgba(255, 255, 255, .26), transparent 35%),
            linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
        box-shadow: 0 18px 45px rgba(75, 0, 232, .22);
        margin-bottom: 18px;
    }

    .leave-hero::after {
        content: '';
        position: absolute;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        right: -90px;
        bottom: -120px;
        background: rgba(255, 255, 255, .14);
    }

    .leave-hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .leave-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        color: rgba(255, 255, 255, .92);
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .leave-hero-title {
        font-size: 26px;
        font-weight: 900;
        margin: 0;
        color: #fff;
        letter-spacing: -.03em;
    }

    .leave-hero-subtitle {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: 13px;
        max-width: 780px;
        line-height: 1.6;
    }

    .leave-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .leave-add-btn,
    .leave-import-btn {
        border: 0 !important;
        border-radius: 14px;
        background: #fff !important;
        color: var(--leave-primary) !important;
        font-size: 13px;
        font-weight: 900;
        height: 42px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 12px 24px rgba(16, 24, 40, .14);
        transition: all .2s ease;
    }

    .leave-import-btn {
        background: rgba(255, 255, 255, .16) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, .28) !important;
        box-shadow: none;
    }

    .leave-add-btn i {
        color: var(--leave-primary) !important;
    }

    .leave-import-btn i {
        color: #fff !important;
    }

    .leave-add-btn:hover,
    .leave-import-btn:hover {
        transform: translateY(-1px);
    }

    .leave-btn-gradient {
        border: 0 !important;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--leave-primary, #4B00E8), var(--leave-secondary, #FF5252)) !important;
        color: #ffffff !important;
        font-size: 13px;
        font-weight: 850;
        height: 40px;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 10px 24px rgba(75, 0, 232, 0.22);
        transition: all .2s ease;
        cursor: pointer;
        text-decoration: none !important;
    }

    .leave-btn-gradient i {
        color: #ffffff !important;
    }

    .leave-btn-gradient:hover,
    .leave-btn-gradient:focus {
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(75, 0, 232, 0.32);
        filter: brightness(1.05);
    }

    .leave-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 18px;
    }

    .summary-card {
        position: relative;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 20px;
        box-shadow: var(--leave-shadow);
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 92px;
        transition: all .2s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(16, 24, 40, .10);
    }

    .summary-card::after {
        content: '';
        width: 74px;
        height: 74px;
        border-radius: 50%;
        position: absolute;
        right: -28px;
        bottom: -28px;
        background: var(--summary-soft);
    }

    .summary-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: var(--summary-gradient);
        box-shadow: 0 10px 22px var(--summary-shadow);
        position: relative;
        z-index: 2;
    }

    .summary-info {
        position: relative;
        z-index: 2;
    }

    .summary-info h4 {
        margin: 0;
        font-size: 26px;
        font-weight: 950;
        color: var(--leave-text);
        line-height: 1;
    }

    .summary-info p {
        margin: 5px 0 0;
        color: var(--leave-muted);
        font-size: 12px;
        font-weight: 800;
    }

    .summary-primary {
        --summary-gradient: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        --summary-shadow: rgba(75, 0, 232, .22);
        --summary-soft: rgba(75, 0, 232, .08);
    }

    .summary-success {
        --summary-gradient: linear-gradient(135deg, #12B76A, #039855);
        --summary-shadow: rgba(18, 183, 106, .22);
        --summary-soft: rgba(18, 183, 106, .09);
    }

    .summary-warning {
        --summary-gradient: linear-gradient(135deg, #F79009, #DC6803);
        --summary-shadow: rgba(247, 144, 9, .22);
        --summary-soft: rgba(247, 144, 9, .10);
    }

    .leave-card {
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 24px;
        box-shadow: var(--leave-shadow);
        overflow: hidden;
        margin-bottom: 18px;
    }

    .leave-card-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--leave-border);
        background: linear-gradient(180deg, #fff, #FCFCFD);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .leave-card-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .leave-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: var(--leave-soft);
        color: var(--leave-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .leave-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
        color: var(--leave-text);
    }

    .leave-card-subtitle {
        margin: 2px 0 0;
        font-size: 12px;
        color: var(--leave-muted);
        font-weight: 600;
    }

    .leave-action-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .leave-light-btn {
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        border-radius: 12px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 850;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all .2s ease;
    }

    .leave-light-btn:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border-color: rgba(75, 0, 232, .18);
    }

    .leave-table-wrap {
        padding: 20px;
    }

    .leave-table-responsive {
        overflow-x: auto;
        border-radius: 14px;
        border: 1px solid #E2E8F0;
        background: #fff;
        box-shadow: 0 1px 3px rgba(16, 24, 40, 0.04);
    }

    .leave-table {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--leave-text);
    }

    .leave-table thead th {
        background: #F8FAFC !important;
        color: #475467 !important;
        font-size: 11.5px !important;
        text-transform: uppercase !important;
        letter-spacing: .05em !important;
        font-weight: 850 !important;
        padding: 13px 16px !important;
        border-top: none !important;
        border-bottom: 1px solid #E2E8F0 !important;
        white-space: nowrap !important;
    }

    .leave-table thead th:first-child {
        padding-left: 20px !important;
        border-top-left-radius: 13px;
        text-align: center;
        width: 60px;
    }

    .leave-table thead th:last-child {
        padding-right: 20px !important;
        border-top-right-radius: 13px;
        text-align: center;
        width: 80px;
    }

    .leave-table tbody td {
        padding: 14px 16px !important;
        border-bottom: 1px solid #F1F5F9 !important;
        vertical-align: middle !important;
        font-size: 13px !important;
        white-space: nowrap !important;
        background: #fff;
    }

    .leave-table tbody td:first-child {
        padding-left: 20px !important;
        text-align: center;
        font-weight: 800;
        color: #64748B;
        width: 60px;
    }

    .leave-table tbody td:last-child {
        padding-right: 20px !important;
        text-align: center;
        width: 80px;
    }

    .leave-table tbody tr {
        transition: all .15s ease;
    }

    .leave-table tbody tr:hover td {
        background: #FAF8FF !important;
    }

    .leave-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    .leave-table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 13px;
    }

    .leave-table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 13px;
    }

    /* DataTable toolbar and pagination flush alignment */
    #tableViewPanel .dataTables_wrapper .dt-toolbar {
        margin-bottom: 14px !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    #tableViewPanel .dataTables_wrapper .dt-toolbar .col-sm-12 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    #tableViewPanel .dataTables_wrapper .row:last-child,
    #tableViewPanel .orb-pagination-wrapper {
        padding: 16px 0 4px !important;
        border-top: none !important;
        background: transparent !important;
    }

    /* Sub-toolbar Select2 styling */
    .holiday-toolbar-bar .select2-container {
        min-width: 175px !important;
    }
    .holiday-toolbar-bar .select2-container .select2-selection--single {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1.5px solid var(--leave-border) !important;
        display: flex !important;
        align-items: center !important;
        background: #fff !important;
        font-weight: 750 !important;
        font-size: 13px !important;
    }
    .holiday-toolbar-bar .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
        color: var(--leave-text) !important;
    }
    .holiday-toolbar-bar .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }

    .date-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 180px;
    }

    .date-box {
        min-width: 54px;
        border-radius: 16px;
        border: 1px solid #EAECF0;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(16, 24, 40, .06);
        text-align: center;
    }

    .date-month {
        background: #FEF3F2;
        color: #B42318;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        padding: 5px 4px;
    }

    .date-day {
        color: var(--leave-text);
        font-size: 20px;
        font-weight: 950;
        line-height: 1;
        padding: 8px 4px;
    }

    .holiday-title {
        font-size: 13px;
        font-weight: 900;
        color: var(--leave-text);
        line-height: 1.2;
    }

    .holiday-meta {
        font-size: 11px;
        color: var(--leave-muted);
        margin-top: 2px;
        font-weight: 700;
    }

    .leave-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .pill-company {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .pill-public {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .pill-restricted {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .pill-off {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .pill-working {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: 1px solid var(--leave-border);
        background: #fff;
        color: #667085;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
    }

    .icon-btn:hover {
        color: var(--leave-primary);
        background: var(--leave-soft);
        border-color: rgba(75, 0, 232, .18);
    }

    .leave-action-menu {
        border: 1px solid var(--leave-border);
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(16, 24, 40, .12);
        padding: 8px;
    }

    .leave-action-menu .dropdown-item {
        border-radius: 10px;
        font-size: 13px;
        font-weight: 800;
        padding: 9px 12px;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--leave-border);
        border-radius: 12px;
        padding: 7px 10px;
        outline: none;
        font-size: 12px;
        color: var(--leave-text);
        background: #fff;
    }

    .empty-state {
        padding: 42px 18px;
        text-align: center;
    }

    .empty-state i {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--leave-soft);
        color: var(--leave-primary);
        font-size: 20px;
        margin-bottom: 12px;
    }

    .leave-modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(16, 24, 40, .22);
    }

    #createHolidayModal,
    .holiday-edit-modal,
    #cloneYearModal {
        overflow: hidden !important;
    }

    #createHolidayModal .modal-dialog,
    .holiday-edit-modal .modal-dialog,
    #cloneYearModal .modal-dialog {
        max-width: 640px;
        margin: 1.75rem auto;
        max-height: calc(100vh - 3.5rem);
        display: flex;
        flex-direction: column;
    }

    #createHolidayModal .modal-content,
    .holiday-edit-modal .modal-content,
    #cloneYearModal .modal-content {
        max-height: calc(100vh - 3.5rem);
        display: flex;
        flex-direction: column;
        border-radius: 20px;
        overflow: hidden;
    }

    #createHolidayModal .modal-header,
    #createHolidayModal .modal-footer,
    .holiday-edit-modal .modal-header,
    .holiday-edit-modal .modal-footer,
    #cloneYearModal .modal-header,
    #cloneYearModal .modal-footer {
        flex-shrink: 0;
    }

    #createHolidayModal .modal-body,
    .holiday-edit-modal .modal-body,
    #cloneYearModal .modal-body {
        overflow-y: auto !important;
        flex: 1 1 auto;
        padding: 20px 22px;
        max-height: calc(100vh - 200px);
    }

    #createHolidayModal .modal-body::-webkit-scrollbar,
    .holiday-edit-modal .modal-body::-webkit-scrollbar,
    #cloneYearModal .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    #createHolidayModal .modal-body::-webkit-scrollbar-track,
    .holiday-edit-modal .modal-body::-webkit-scrollbar-track,
    #cloneYearModal .modal-body::-webkit-scrollbar-track {
        background: #F1F5F9;
    }
    #createHolidayModal .modal-body::-webkit-scrollbar-thumb,
    .holiday-edit-modal .modal-body::-webkit-scrollbar-thumb,
    #cloneYearModal .modal-body::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 4px;
    }
    #createHolidayModal .modal-body::-webkit-scrollbar-thumb:hover,
    .holiday-edit-modal .modal-body::-webkit-scrollbar-thumb:hover,
    #cloneYearModal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #94A3B8;
    }

    #createHolidayModal .orbo-date-picker,
    #createHolidayModal .orbo-date-picker-display,
    .holiday-edit-modal .orbo-date-picker,
    .holiday-edit-modal .orbo-date-picker-display {
        height: 44px !important;
        border-radius: 14px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        border: 1px solid var(--leave-border) !important;
        background-color: #fff !important;
        width: 100% !important;
    }

    #createHolidayModal .orbo-date-picker-display:focus,
    .holiday-edit-modal .orbo-date-picker-display:focus {
        border-color: rgba(75, 0, 232, .28) !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    .flatpickr-calendar {
        z-index: 99999 !important;
    }

    /* Year Selector */
    .leave-year-select {
        height: 40px;
        border-radius: 12px;
        border: 1.5px solid var(--leave-border);
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 12px center;
        background-size: 12px;
        padding: 0 32px 0 14px;
        font-size: 13px;
        font-weight: 800;
        color: var(--leave-text);
        cursor: pointer;
        outline: none;
        appearance: none;
        transition: all .2s ease;
    }
    .leave-year-select:focus {
        border-color: var(--leave-primary);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
    }

    /* View Switcher */
    .leave-view-switch {
        display: inline-flex;
        background: #F1F5F9;
        padding: 4px;
        border-radius: 12px;
        gap: 4px;
    }
    .leave-view-btn {
        border: 0;
        background: transparent;
        padding: 6px 14px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 800;
        color: #64748B;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .2s ease;
    }
    .leave-view-btn.active {
        background: #fff;
        color: var(--leave-primary);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    /* Light Secondary Button */
    .leave-btn-light {
        height: 40px;
        border-radius: 12px;
        border: 1.5px solid var(--leave-border);
        background: #fff;
        color: #334155;
        padding: 0 14px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        transition: all .2s ease;
    }
    .leave-btn-light:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: var(--leave-primary);
    }

    /* ==========================================================================
        Annual Holiday Calendar Grid & Interactive Month Cards
       ========================================================================== */
    .annual-calendar-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        padding: 24px;
        background: #F8FAFC;
    }

    @media (max-width: 1400px) {
        .annual-calendar-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            padding: 20px;
        }
    }

    @media (max-width: 991px) {
        .annual-calendar-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            padding: 16px;
        }
    }

    @media (max-width: 600px) {
        .annual-calendar-grid {
            grid-template-columns: 1fr;
            padding: 14px;
        }
    }

    .month-card {
        background: #FFFFFF;
        border: 1.5px solid #E2E8F0;
        border-radius: 20px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
        box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
    }

    .month-card:hover {
        transform: translateY(-4px);
        border-color: #CBD5E1;
        box-shadow: 0 18px 36px -8px rgba(75, 0, 232, 0.12), 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    .month-card.has-holidays::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: linear-gradient(90deg, #4B00E8 0%, #8600EE 55%, #06B6D4 100%);
        z-index: 3;
    }

    .month-card.is-current-month {
        border-color: rgba(75, 0, 232, 0.35);
        box-shadow: 0 0 0 1px rgba(75, 0, 232, 0.18), 0 10px 24px -4px rgba(75, 0, 232, 0.1);
    }

    .month-card.is-current-month::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: linear-gradient(90deg, #10B981, #06B6D4);
        z-index: 3;
    }

    .month-card-head {
        padding: 9px 12px;
        background: linear-gradient(180deg, #FFFFFF 0%, #FAFAFC 100%);
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
    }

    .month-card-title-group {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .month-card-title {
        font-size: 13.5px;
        font-weight: 850;
        color: #0F172A;
        margin: 0;
        line-height: 1.2;
        letter-spacing: -0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .month-current-tag {
        font-size: 9px;
        font-weight: 800;
        color: #059669;
        background: #ECFDF5;
        border: 1px solid #A7F3D0;
        padding: 1px 5px;
        border-radius: 4px;
        line-height: 1.2;
    }

    .month-card-actions {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
    }

    .month-count-pill {
        font-size: 10px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        line-height: 1.2;
    }

    .month-count-pill.active {
        background: #EEF2FF;
        color: #4338CA;
        border: 1px solid #E0E7FF;
    }

    .month-count-pill.empty {
        background: #F1F5F9;
        color: #94A3B8;
        border: 1px solid #E2E8F0;
    }

    .month-quick-add-btn {
        width: 22px;
        height: 22px;
        min-width: 22px;
        border-radius: 6px;
        border: 1px dashed #CBD5E1;
        background: #FFFFFF;
        color: #64748B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0;
    }

    .month-quick-add-btn:hover {
        background: #F5F3FF;
        border-color: var(--leave-primary);
        color: var(--leave-primary);
        transform: scale(1.08);
    }

    .month-card-body {
        padding: 8px 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1;
        min-height: 60px;
    }

    /* Slim Holiday Item */
    .month-holiday-item {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 5px 8px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-left: 3px solid var(--item-accent, #4B00E8);
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        min-height: 38px;
    }

    .month-holiday-item:hover {
        background: #FAF8FF;
        border-color: #CBD5E1;
        border-left-color: var(--item-accent, #4B00E8);
        transform: translateX(2px);
    }

    .month-holiday-date {
        width: 30px;
        min-width: 30px;
        height: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        flex-shrink: 0;
        line-height: 1;
    }

    .holiday-date-num {
        font-size: 12px;
        font-weight: 900;
        color: #0F172A;
        line-height: 1;
    }

    .holiday-date-day {
        font-size: 7.5px;
        font-weight: 800;
        color: #4F46E5;
        text-transform: uppercase;
        line-height: 1;
        margin-top: 1px;
    }

    .month-holiday-info {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }

    .month-holiday-title {
        font-size: 11.5px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0;
    }

    .month-holiday-meta-row {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 2px;
        line-height: 1;
    }

    .holiday-pill-tag {
        font-size: 9px;
        font-weight: 750;
        padding: 1px 4px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 2.5px;
        line-height: 1.1;
    }

    .tag-day-off {
        background: #F0F9FF;
        color: #0284C7;
        border: 1px solid #BAE6FD;
    }

    .tag-working {
        background: #FFFBEB;
        color: #D97706;
        border: 1px solid #FDE68A;
    }

    .tag-public {
        background: #EEF2FF;
        color: #4338CA;
        border: 1px solid #E0E7FF;
    }

    .tag-company {
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }

    .tag-restricted {
        background: #FAF5FF;
        color: #7E22CE;
        border: 1px solid #F3E8FF;
    }

    .month-holiday-actions {
        display: flex;
        align-items: center;
        gap: 3px;
        flex-shrink: 0;
    }

    .holiday-action-btn {
        width: 22px;
        height: 22px;
        min-width: 22px;
        border-radius: 5px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        color: #64748B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 9.5px;
        cursor: pointer;
        transition: all 0.15s ease;
        padding: 0;
    }

    .holiday-action-btn.edit-btn:hover {
        background: #EEF2FF;
        border-color: #C7D2FE;
        color: #4F46E5;
    }

    .holiday-action-btn.del-btn:hover {
        background: #FEE2E2;
        border-color: #FECACA;
        color: #DC2626;
    }

    /* Slim Empty State */
    .month-empty-state {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 10px;
        border: 1px dashed #E2E8F0;
        border-radius: 8px;
        background: #FAFAFC;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .month-empty-state:hover {
        border-color: #CBD5E1;
        background: #F8FAFC;
    }

    .month-empty-title {
        font-size: 11px;
        font-weight: 650;
        color: #94A3B8;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .month-empty-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 10.5px;
        font-weight: 800;
        background: #FFFFFF;
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, 0.20);
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .month-empty-add-btn:hover {
        background: var(--leave-primary);
        color: #FFFFFF;
        border-color: var(--leave-primary);
    }

    /* Month Card Footer */
    .month-card-footer {
        padding: 6px 12px;
        background: #FAFAFC;
        border-top: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 10px;
        font-weight: 700;
        color: #64748B;
    }

    .leave-modal-header {
        border: 0;
        padding: 20px 22px;
        background: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
    }

    .leave-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 950;
        color: #fff;
    }

    .leave-modal-subtitle {
        margin-top: 4px;
        font-size: 12px;
        font-weight: 650;
        color: rgba(255, 255, 255, .78);
    }

    .leave-modal-header .close {
        color: #fff;
        opacity: .9;
        text-shadow: none;
        outline: none;
    }

    .leave-modal-body {
        padding: 18px;
        background: #F8FAFC;
    }

    .leave-modal-section {
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 18px;
        padding: 16px;
        margin-bottom: 14px;
    }

    .leave-modal-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--leave-text);
        font-size: 13px;
        font-weight: 950;
        margin-bottom: 14px;
    }

    .leave-modal-section-title i {
        color: var(--leave-primary);
    }

    .leave-field {
        margin-bottom: 14px;
    }

    .leave-field label {
        display: block;
        margin-bottom: 7px;
        color: #344054;
        font-size: 12px;
        font-weight: 900;
    }

    .leave-input {
        width: 100%;
        height: 44px;
        border-radius: 14px;
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        padding: 0 13px;
        font-size: 13px;
        font-weight: 700;
        outline: none;
        transition: all .2s ease;
    }

    .leave-input:focus {
        border-color: rgba(75, 0, 232, .28);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08);
    }

    .leave-input::placeholder {
        color: #98A2B3;
        font-weight: 600;
    }

    .leave-check-card {
        border: 1px solid var(--leave-border);
        background: #fff;
        border-radius: 14px;
        padding: 11px 12px;
        cursor: pointer;
        transition: all .2s ease;
        min-height: 44px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #344054;
        font-size: 12px;
        font-weight: 900;
        margin-bottom: 10px;
    }

    .leave-check-card:hover {
        background: var(--leave-soft);
        border-color: rgba(75, 0, 232, .20);
    }

    .leave-check-card input {
        width: 16px;
        height: 16px;
        accent-color: var(--leave-primary);
    }

    .leave-modal-footer {
        border: 0;
        padding: 16px 20px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .leave-modal-btn {
        border: 0;
        border-radius: 14px;
        height: 42px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 900;
        transition: all .2s ease;
    }

    .leave-modal-btn-primary {
        background: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
        box-shadow: 0 12px 24px rgba(75, 0, 232, .18);
    }

    .leave-modal-btn-primary:hover {
        color: #fff;
        transform: translateY(-1px);
    }

    .leave-modal-btn-light {
        background: #fff;
        color: var(--leave-text);
        border: 1px solid var(--leave-border);
    }

    .leave-modal-btn-light:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
    }

    @media(max-width:991px) {
        .leave-summary-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width:767px) {
        .leave-hero {
            padding: 18px;
            border-radius: 20px;
        }

        .leave-hero-title {
            font-size: 22px;
        }

        .leave-hero-actions {
            width: 100%;
        }

        .leave-add-btn,
        .leave-import-btn {
            flex: 1;
        }

        .leave-card-head {
            padding: 16px;
        }

        .leave-action-wrap {
            width: 100%;
        }

        .leave-light-btn {
            flex: 1;
            justify-content: center;
        }

        .leave-modal-body {
            padding: 14px;
        }
    }
</style>
@endsection

@section('_content')
<div class="leave-page-wrap">

    <div class="leave-hero">
        <div class="leave-hero-content">
            <div>
                <div class="leave-hero-kicker">
                    <i class="fas fa-glass-cheers"></i>
                    HRMS Holiday Calendar
                </div>

                <h1 class="leave-hero-title">Holiday Management</h1>

                <div class="leave-hero-subtitle">
                    Manage company holidays, public holidays, restricted holidays and working-day overrides for attendance and leave calculations.
                </div>
            </div>
        </div>
    </div>

    @include('hrms.leave.shared.flash')

    <div class="leave-metric-grid">
        <div class="leave-metric" style="--metric-color:#4B00E8;--metric-soft:rgba(75, 0, 232, 0.08);">
            <div class="leave-metric-top">
                <div class="leave-metric-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="leave-metric-value">{{ $holidays->count() }}</div>
            </div>
            <div class="leave-metric-label">Total Holidays ({{ $selectedYear }})</div>
            <div class="leave-metric-line"></div>
        </div>

        <div class="leave-metric" style="--metric-color:#10B981;--metric-soft:#ECFDF5;">
            <div class="leave-metric-top">
                <div class="leave-metric-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="leave-metric-value">{{ $holidays->where('is_working_day_override', 0)->count() }}</div>
            </div>
            <div class="leave-metric-label">Days Off</div>
            <div class="leave-metric-line"></div>
        </div>

        <div class="leave-metric" style="--metric-color:#F59E0B;--metric-soft:#FFFBEB;">
            <div class="leave-metric-top">
                <div class="leave-metric-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="leave-metric-value">{{ $holidays->where('is_working_day_override', 1)->count() }}</div>
            </div>
            <div class="leave-metric-label">Working Overrides</div>
            <div class="leave-metric-line"></div>
        </div>

        <div class="leave-metric" style="--metric-color:#6366F1;--metric-soft:#EEF2FF;">
            <div class="leave-metric-top">
                <div class="leave-metric-icon">
                    <i class="fas fa-glass-cheers"></i>
                </div>
                <div class="leave-metric-value" style="font-size:15px;font-weight:900;line-height:1.2;text-align:right;" title="{{ $nextHoliday ? $nextHoliday->title : 'None' }}">
                    {{ $nextHoliday ? \Illuminate\Support\Str::limit($nextHoliday->title, 14) : 'None' }}
                </div>
            </div>
            <div class="leave-metric-label">
                @if($nextHoliday)
                    Next: {{ optional($nextHoliday->holiday_date)->format('d M (D)') }}
                @else
                    No upcoming holiday
                @endif
            </div>
            <div class="leave-metric-line"></div>
        </div>
    </div>

    <div class="leave-card">
        <div class="leave-card-head" style="flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between;">
            <div class="leave-card-title-wrap">
                <div class="leave-card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <div>
                    <h5 class="leave-card-title">Holiday Calendar — {{ $selectedYear }}</h5>
                    <div class="leave-card-subtitle">
                        Official company holidays, public observances and working-day overrides for {{ $selectedYear }}.
                    </div>
                </div>
            </div>

            <div class="leave-action-wrap" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                {{-- Copy to Next Year / Clone --}}
                <button type="button" class="leave-btn-light" data-toggle="modal" data-target="#cloneYearModal" title="Copy standard holidays to another year">
                    <i class="fas fa-copy"></i> Copy Year
                </button>

                {{-- Add Holiday --}}
                <button type="button" class="leave-btn-gradient" data-toggle="modal" data-target="#createHolidayModal">
                    <i class="fas fa-plus"></i> Add Holiday
                </button>
            </div>
        </div>

        {{-- Sub-Toolbar Section: Left side Year Select, Right side Table & Annual Grid View Switcher --}}
        <div class="holiday-toolbar-bar" style="padding:12px 20px;background:#F8FAFC;border-bottom:1px solid var(--leave-border);display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            {{-- Left Side: Year Selector --}}
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:12px;font-weight:800;color:#64748B;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-calendar text-primary"></i> Calendar Year:
                </span>
                <form id="holidayYearFilterForm" method="GET" action="{{ route('hrms.holidays.index') }}" style="margin:0;">
                    <div style="min-width:160px;">
                        <select name="year" id="holiday_year_select" onchange="document.getElementById('holidayYearFilterForm').submit();" class="leave-year-select select2-searchable" title="Change Calendar Year">
                            @foreach($availableYears as $yr)
                                <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>
                                    Year {{ $yr }} {{ $yr == date('Y') ? '(Current)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            {{-- Right Side: View Switcher (Table vs Annual Grid) --}}
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:12px;font-weight:800;color:#64748B;">View:</span>
                <div class="leave-view-switch">
                    <button type="button" class="leave-view-btn active" id="btnViewTable" onclick="switchHolidayView('table')">
                        <i class="fas fa-list"></i> Table
                    </button>
                    <button type="button" class="leave-view-btn" id="btnViewCalendar" onclick="switchHolidayView('calendar')">
                        <i class="fas fa-th-large"></i> Annual Grid
                    </button>
                </div>
            </div>
        </div>

        {{-- Panel 1: Table List View --}}
        <div id="tableViewPanel">
            <div class="leave-table-wrap">
                <div class="leave-table-responsive">
                    <table class="leave-table js-datatable">
                        <thead>
                            <tr>
                                <th style="text-align:center;width:60px;">S.No.</th>
                                <th>Date</th>
                                <th>Holiday</th>
                                <th>Type</th>
                                <th>Working Override</th>
                                <th style="text-align:center;width:80px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($holidays as $holiday)
                            @php
                            $typeClass = 'pill-company';
                            if (($holiday->holiday_type ?? '') === 'public') $typeClass = 'pill-public';
                            if (($holiday->holiday_type ?? '') === 'restricted') $typeClass = 'pill-restricted';
                            @endphp

                            <tr>
                                <td style="text-align:center;font-weight:800;color:#64748B;">{{ $loop->iteration }}</td>

                                <td>
                                    <div class="date-cell">
                                        <div class="date-box">
                                            <div class="date-month">{{ optional($holiday->holiday_date)->format('M') }}</div>
                                            <div class="date-day">{{ optional($holiday->holiday_date)->format('d') }}</div>
                                        </div>

                                        <div>
                                            <div class="holiday-title">{{ optional($holiday->holiday_date)->format('l') }}</div>
                                            <div class="holiday-meta">{{ optional($holiday->holiday_date)->format('d M Y') }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="holiday-title">{{ $holiday->title }}</div>
                                    <div class="holiday-meta">Holiday record</div>
                                </td>

                                <td>
                                    <span class="leave-pill {{ $typeClass }}">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ ucfirst($holiday->holiday_type) }}
                                    </span>
                                </td>

                                <td>
                                    @if($holiday->is_working_day_override)
                                    <span class="leave-pill pill-working">
                                        <i class="fas fa-briefcase"></i>
                                        Working Day
                                    </span>
                                    @else
                                    <span class="leave-pill pill-off">
                                        <i class="fas fa-moon"></i>
                                        Day Off
                                    </span>
                                    @endif
                                </td>

                                <td style="text-align:center;">
                                    <div class="dropdown d-inline-block">
                                        <button class="icon-btn" type="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-right leave-action-menu">
                                            <a class="dropdown-item"
                                                href="#"
                                                data-toggle="modal"
                                                data-target="#editHolidayModal{{ $holiday->id }}">
                                                <i class="fas fa-edit text-primary mr-2"></i>
                                                Edit
                                            </a>

                                            <form method="POST"
                                                action="{{ route('hrms.holidays.destroy', $holiday->id) }}"
                                                onsubmit="return confirm('Delete this holiday?');"
                                                style="margin:0;">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="dropdown-item text-danger bg-transparent border-0 w-100 text-left">
                                                    <i class="fas fa-trash mr-2"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <div style="font-weight:900;color:var(--leave-text);">
                                            No Holidays Found for {{ $selectedYear }}
                                        </div>
                                        <div style="font-size:12px;margin-top:4px;color:var(--leave-muted);">
                                            Add holidays for {{ $selectedYear }} or copy standard holidays from {{ $selectedYear == 2027 ? 2026 : $selectedYear - 1 }}.
                                        </div>
                                        <div style="margin-top:14px;display:flex;gap:10px;justify-content:center;">
                                            <button type="button" class="leave-btn-light" data-toggle="modal" data-target="#cloneYearModal">
                                                <i class="fas fa-copy mr-1"></i> Copy from {{ $selectedYear == 2027 ? 2026 : $selectedYear - 1 }}
                                            </button>
                                            <button type="button" class="leave-btn-gradient" data-toggle="modal" data-target="#createHolidayModal">
                                                <i class="fas fa-plus mr-1"></i> Add Holiday
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Panel 2: Visual Annual Calendar Grid View (12 Months) --}}
        <div id="calendarViewPanel" style="display:none;">
            <div class="annual-calendar-grid">
                @php
                    $monthNames = [
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                    $currentRealYear = (int)date('Y');
                    $currentRealMonth = (int)date('n');
                @endphp

                @foreach($monthNames as $mNum => $mName)
                    @php
                        $mHolidays = $holidaysByMonth->get($mNum, collect());
                        $isCurrentMonth = ($selectedYear == $currentRealYear && $mNum == $currentRealMonth);
                    @endphp
                    <div class="month-card {{ $mHolidays->isNotEmpty() ? 'has-holidays' : '' }} {{ $isCurrentMonth ? 'is-current-month' : '' }}">
                        <div class="month-card-head">
                            <div class="month-card-title-group">
                                <h6 class="month-card-title">{{ $mName }}</h6>
                                @if($isCurrentMonth)
                                    <span class="month-current-tag">This Month</span>
                                @endif
                            </div>

                            <div class="month-card-actions">
                                @if($mHolidays->isNotEmpty())
                                    <span class="month-count-pill active">
                                        {{ $mHolidays->count() }} {{ \Illuminate\Support\Str::plural('Holiday', $mHolidays->count()) }}
                                    </span>
                                @else
                                    <span class="month-count-pill empty">0 Holidays</span>
                                @endif

                                <button type="button" class="month-quick-add-btn" onclick="openAddHolidayForMonth({{ $selectedYear }}, {{ $mNum }})" title="Add Holiday in {{ $mName }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="month-card-body">
                            @forelse($mHolidays as $h)
                                @php
                                    $accentColor = '#4B00E8';
                                    $typeTagClass = 'tag-company';
                                    if (($h->holiday_type ?? '') === 'public') {
                                        $accentColor = '#4F46E5';
                                        $typeTagClass = 'tag-public';
                                    } elseif (($h->holiday_type ?? '') === 'restricted') {
                                        $accentColor = '#8600EE';
                                        $typeTagClass = 'tag-restricted';
                                    }
                                    if ($h->is_working_day_override) {
                                        $accentColor = '#D97706';
                                    }
                                @endphp
                                <div class="month-holiday-item" style="--item-accent: {{ $accentColor }};">
                                    <div class="month-holiday-date">
                                        <span class="holiday-date-num">{{ optional($h->holiday_date)->format('d') }}</span>
                                        <span class="holiday-date-day">{{ optional($h->holiday_date)->format('D') }}</span>
                                    </div>

                                    <div class="month-holiday-info">
                                        <div class="month-holiday-title" title="{{ $h->title }}">{{ $h->title }}</div>
                                        <div class="month-holiday-meta-row">
                                            @if($h->is_working_day_override)
                                                <span class="holiday-pill-tag tag-working">
                                                    <i class="fas fa-briefcase"></i> Working
                                                </span>
                                            @else
                                                <span class="holiday-pill-tag tag-day-off">
                                                    <i class="fas fa-moon"></i> Day Off
                                                </span>
                                            @endif
                                            <span class="holiday-pill-tag {{ $typeTagClass }}">
                                                {{ ucfirst($h->holiday_type) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="month-holiday-actions">
                                        <button type="button" class="holiday-action-btn edit-btn" data-toggle="modal" data-target="#editHolidayModal{{ $h->id }}" title="Edit {{ $h->title }}">
                                            <i class="fas fa-pen"></i>
                                        </button>

                                        <form method="POST" action="{{ route('hrms.holidays.destroy', $h->id) }}" onsubmit="return confirm('Delete \'{{ addslashes($h->title) }}\'?');" style="margin:0;display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="holiday-action-btn del-btn" title="Delete {{ $h->title }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="month-empty-state">
                                    <span class="month-empty-title">
                                        <i class="far fa-calendar-check"></i> No holidays scheduled
                                    </span>
                                    <button type="button" class="month-empty-add-btn" onclick="openAddHolidayForMonth({{ $selectedYear }}, {{ $mNum }})">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            @endforelse
                        </div>

                        <div class="month-card-footer">
                            @if($mHolidays->isNotEmpty())
                                @php
                                    $offDays = $mHolidays->where('is_working_day_override', 0)->count();
                                    $workDays = $mHolidays->where('is_working_day_override', 1)->count();
                                @endphp
                                <span>
                                    <i class="fas fa-calendar-day text-primary mr-1"></i>
                                    <strong>{{ $offDays }}</strong> {{ \Illuminate\Support\Str::plural('Day Off', $offDays) }}
                                    @if($workDays > 0)
                                        · <span style="color:#D97706;"><strong>{{ $workDays }}</strong> Working</span>
                                    @endif
                                </span>
                            @else
                                <span style="color:#94A3B8;">Regular working month</span>
                            @endif
                            <span style="font-size:10px;font-weight:800;color:#94A3B8;letter-spacing:0.5px;">Q{{ ceil($mNum / 3) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Clone Year Modal --}}
    <div class="modal fade orb-type-modal" id="cloneYearModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <form method="POST" action="{{ route('hrms.holidays.clone_year') }}" class="modal-content leave-modal-content">
                @csrf

                <div class="modal-header leave-modal-header">
                    <div>
                        <h5 class="leave-modal-title">Copy Holidays to Another Year</h5>
                        <div class="leave-modal-subtitle">Quickly populate standard annual holidays without typing them one by one.</div>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body leave-modal-body">
                    <div class="leave-modal-section">
                        <div class="leave-modal-section-title">
                            <i class="fas fa-copy"></i>
                            Year Selection
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="leave-field">
                                    <label>Source Year (Copy From)</label>
                                    <select name="source_year" class="leave-input select2-searchable" required>
                                        @foreach($availableYears as $yr)
                                            <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : ($yr == 2026 ? 'selected' : '') }}>
                                                Year {{ $yr }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="leave-field">
                                    <label>Target Year (Copy To)</label>
                                    <select name="target_year" class="leave-input select2-searchable" required>
                                        @foreach($availableYears as $yr)
                                            <option value="{{ $yr }}" {{ ($selectedYear == 2026 && $yr == 2027) ? 'selected' : ($yr == $selectedYear + 1 ? 'selected' : '') }}>
                                                Year {{ $yr }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:12px 14px;margin-top:6px;">
                            <div style="font-size:12px;font-weight:800;color:#166534;">
                                <i class="fas fa-info-circle mr-1"></i> How it works
                            </div>
                            <div style="font-size:11px;color:#15803D;margin-top:4px;line-height:1.4;">
                                All holidays from the source year will be replicated into the target year on the same calendar dates. Existing holidays with duplicate dates/names are safely skipped. You can then quickly edit any lunar festival dates (e.g. Diwali, Holi, Eid) for the new year.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer leave-modal-footer">
                    <button type="button" class="leave-modal-btn leave-modal-btn-light" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="leave-modal-btn leave-modal-btn-primary">
                        <i class="fas fa-copy"></i>
                        Copy Holidays
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade orb-type-modal" id="createHolidayModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <form method="POST" action="{{ route('hrms.holidays.store') }}" class="modal-content leave-modal-content">
                @csrf

                <div class="modal-header leave-modal-header">
                    <div>
                        <h5 class="leave-modal-title">Add Holiday</h5>
                        <div class="leave-modal-subtitle">Add official company holiday or day off to calendar.</div>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body leave-modal-body">
                    <div class="leave-modal-section">
                        <div class="leave-modal-section-title">
                            <i class="fas fa-calendar-day"></i>
                            Holiday Details
                        </div>

                        <div class="leave-field">
                            <label>Holiday Title</label>
                            <input type="text"
                                name="title"
                                class="leave-input"
                                placeholder="Example: Independence Day"
                                required>
                        </div>

                        <div class="leave-field">
                            <label>Holiday Date</label>
                            <x-form.date-picker
                                name="holiday_date"
                                id="create_holiday_date"
                                class="leave-input"
                                placeholder="Select holiday date"
                                required
                            />
                        </div>

                        <div class="leave-field">
                            <label>Holiday Type</label>
                            <select name="holiday_type" class="leave-input select2-searchable">
                                <option value="company">Company</option>
                                <option value="public">Public</option>
                                <option value="restricted">Restricted</option>
                            </select>
                        </div>

                        <div class="leave-field">
                            <label style="font-weight:700;margin-bottom:6px;display:block;">Day Type / Office Status</label>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;">
                                <label class="leave-check-card" style="margin:0;cursor:pointer;display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border:1.5px solid #E2E8F0;border-radius:10px;background:#F8FAFC;height:100%;">
                                    <input type="radio" name="is_working_day_override" value="0" checked style="margin-top:3px;">
                                    <div>
                                        <strong style="color:#0284C7;font-size:13px;display:block;"><i class="fas fa-moon mr-1"></i> Day Off (Official Holiday)</strong>
                                        <div style="font-size:11px;color:#64748B;margin-top:4px;line-height:1.4;">Office will remain closed. Counted as holiday for all employees (Default).</div>
                                    </div>
                                </label>
                                <label class="leave-check-card" style="margin:0;cursor:pointer;display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border:1.5px solid #E2E8F0;border-radius:10px;background:#F8FAFC;height:100%;">
                                    <input type="radio" name="is_working_day_override" value="1" style="margin-top:3px;">
                                    <div>
                                        <strong style="color:#D97706;font-size:13px;display:block;"><i class="fas fa-briefcase mr-1"></i> Working Day (Office Open)</strong>
                                        <div style="font-size:11px;color:#64748B;margin-top:4px;line-height:1.4;">Office remains open. Attendance is required for all employees.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <label class="leave-check-card" for="create_active">
                            <input type="checkbox"
                                id="create_active"
                                name="is_active"
                                value="1"
                                checked>
                            <span>Active</span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer leave-modal-footer">
                    <button type="button" class="leave-modal-btn leave-modal-btn-light" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="leave-modal-btn leave-modal-btn-primary">
                        <i class="fas fa-save"></i>
                        Save Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>

    @foreach($holidays as $holiday)
    <div class="modal fade orb-type-modal holiday-edit-modal" id="editHolidayModal{{ $holiday->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <form method="POST" action="{{ route('hrms.holidays.update', $holiday->id) }}" class="modal-content leave-modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header leave-modal-header">
                    <div>
                        <h5 class="leave-modal-title">Edit Holiday</h5>
                        <div class="leave-modal-subtitle">{{ $holiday->title }} · {{ optional($holiday->holiday_date)->format('d M Y') }}</div>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body leave-modal-body">
                    <div class="leave-modal-section">
                        <div class="leave-modal-section-title">
                            <i class="fas fa-calendar-day"></i>
                            Holiday Details
                        </div>

                        <div class="leave-field">
                            <label>Holiday Title</label>
                            <input type="text"
                                name="title"
                                class="leave-input"
                                value="{{ old('title', $holiday->title) }}"
                                placeholder="Example: Independence Day"
                                required>
                        </div>

                        <div class="leave-field">
                            <label>Holiday Date</label>
                            <x-form.date-picker
                                name="holiday_date"
                                id="edit_holiday_date_{{ $holiday->id }}"
                                class="leave-input"
                                value="{{ optional($holiday->holiday_date)->format('Y-m-d') }}"
                                placeholder="Select holiday date"
                                required
                            />
                        </div>

                        <div class="leave-field">
                            <label>Holiday Type</label>
                            <select name="holiday_type" class="leave-input select2-searchable">
                                <option value="company" {{ old('holiday_type', $holiday->holiday_type) === 'company' ? 'selected' : '' }}>Company</option>
                                <option value="public" {{ old('holiday_type', $holiday->holiday_type) === 'public' ? 'selected' : '' }}>Public</option>
                                <option value="restricted" {{ old('holiday_type', $holiday->holiday_type) === 'restricted' ? 'selected' : '' }}>Restricted</option>
                            </select>
                        </div>

                        <div class="leave-field">
                            <label style="font-weight:700;margin-bottom:6px;display:block;">Day Type / Office Status</label>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;">
                                <label class="leave-check-card" style="margin:0;cursor:pointer;display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border:1.5px solid #E2E8F0;border-radius:10px;background:#F8FAFC;height:100%;">
                                    <input type="radio" name="is_working_day_override" value="0" {{ !$holiday->is_working_day_override ? 'checked' : '' }} style="margin-top:3px;">
                                    <div>
                                        <strong style="color:#0284C7;font-size:13px;display:block;"><i class="fas fa-moon mr-1"></i> Day Off (Official Holiday)</strong>
                                        <div style="font-size:11px;color:#64748B;margin-top:4px;line-height:1.4;">Office will remain closed. Counted as holiday for all employees (Default).</div>
                                    </div>
                                </label>
                                <label class="leave-check-card" style="margin:0;cursor:pointer;display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border:1.5px solid #E2E8F0;border-radius:10px;background:#F8FAFC;height:100%;">
                                    <input type="radio" name="is_working_day_override" value="1" {{ $holiday->is_working_day_override ? 'checked' : '' }} style="margin-top:3px;">
                                    <div>
                                        <strong style="color:#D97706;font-size:13px;display:block;"><i class="fas fa-briefcase mr-1"></i> Working Day (Office Open)</strong>
                                        <div style="font-size:11px;color:#64748B;margin-top:4px;line-height:1.4;">Office remains open. Attendance is required for all employees.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <label class="leave-check-card" for="edit_active_{{ $holiday->id }}">
                            <input type="checkbox"
                                id="edit_active_{{ $holiday->id }}"
                                name="is_active"
                                value="1"
                                {{ $holiday->is_active ? 'checked' : '' }}>
                            <span>Active</span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer leave-modal-footer">
                    <button type="button" class="leave-modal-btn leave-modal-btn-light" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="leave-modal-btn leave-modal-btn-primary">
                        <i class="fas fa-save"></i>
                        Update Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')

<script>
    function triggerLeaveExport(type) {
        let table = $('.js-datatable').DataTable();

        let buttons = {
            csv: '.buttons-csv',
            excel: '.buttons-excel',
            pdf: '.buttons-pdf',
            print: '.buttons-print'
        };

        if (buttons[type]) {
            table.button(buttons[type]).trigger();
        }
    }

    function switchHolidayView(mode) {
        if (mode === 'calendar') {
            $('#tableViewPanel').hide();
            $('#calendarViewPanel').fadeIn(150);
            $('#btnViewCalendar').addClass('active');
            $('#btnViewTable').removeClass('active');
            localStorage.setItem('orbo_holiday_view', 'calendar');
        } else {
            $('#calendarViewPanel').hide();
            $('#tableViewPanel').fadeIn(150);
            $('#btnViewTable').addClass('active');
            $('#btnViewCalendar').removeClass('active');
            localStorage.setItem('orbo_holiday_view', 'table');
        }
    }

    function openAddHolidayForMonth(year, month) {
        let formattedMonth = String(month).padStart(2, '0');
        let defaultDate = `${year}-${formattedMonth}-01`;
        let dateInput = document.getElementById('create_holiday_date');
        if (dateInput) {
            if (dateInput._flatpickr) {
                dateInput._flatpickr.setDate(defaultDate, true);
            } else {
                dateInput.value = defaultDate;
            }
        }
        $('#createHolidayModal').modal('show');
    }

    $(document).ready(function () {
        let savedView = localStorage.getItem('orbo_holiday_view');
        if (savedView === 'calendar') {
            switchHolidayView('calendar');
        }

        $('#holiday_year_select').on('change', function () {
            $('#holidayYearFilterForm').submit();
        });

        $('#createHolidayModal, .holiday-edit-modal, #cloneYearModal').on('shown.bs.modal', function () {
            if (typeof window.initOrboDatePickers === 'function') {
                window.initOrboDatePickers(this);
            }
        });
    });
</script>
@endsection