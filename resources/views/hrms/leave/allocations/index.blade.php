@extends('layouts.panel')

@section('page_title', 'Leave Allocation')

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

    .leave-view-tab {
        border: 1px solid var(--leave-border);
        border-radius: 10px;
        padding: 9px 15px;
        color: var(--leave-text);
        background: #fff;
        font-weight: 800;
        text-decoration: none !important;
        transition: .18s ease;
    }

    .leave-view-tab:hover,
    .leave-view-tab.active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, var(--leave-primary), #e94069);
        box-shadow: 0 7px 18px rgba(75, 0, 232, .18);
    }

    .leave-monthly-table thead th {
        color: #475467;
        background: #f8f7ff;
        border-color: var(--leave-border);
        font-size: 11px;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .leave-monthly-table tbody td {
        border-color: var(--leave-border);
        vertical-align: middle;
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
        letter-spacing: -.03em;
        color: #fff;
    }

    .leave-hero-subtitle {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: 13px;
        max-width: 780px;
        line-height: 1.6;
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
        gap: 12px;
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .leave-card-title {
        font-size: 16px;
        font-weight: 900;
        color: var(--leave-text);
        margin: 0;
        line-height: 1.2;
    }

    .leave-card-subtitle {
        font-size: 12px;
        color: var(--leave-muted);
        margin-top: 3px;
    }

    .leave-card-body {
        padding: 20px;
    }

    .leave-action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        width: 100%;
        box-sizing: border-box;
    }

    .leave-action-box {
        border: 1px solid var(--leave-border);
        border-radius: 18px;
        padding: 16px;
        background: #fff;
        transition: all .2s ease;
        box-sizing: border-box !important;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
    }

    .leave-action-box form {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        margin: 0;
    }

    .leave-action-box:hover {
        border-color: rgba(75, 0, 232, .28);
        box-shadow: 0 10px 24px rgba(75, 0, 232, .06);
    }

    .leave-action-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--leave-text);
        margin-bottom: 4px;
    }

    .leave-action-subtitle {
        font-size: 12px;
        color: var(--leave-muted);
        margin-bottom: 12px;
        line-height: 1.45;
    }

    .leave-form-row {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box !important;
    }

    .leave-control {
        height: 42px;
        border-radius: 14px;
        border: 1px solid var(--leave-border);
        padding: 0 14px;
        font-size: 13px;
        color: var(--leave-text);
        background: #fff;
        outline: none;
        transition: all .2s ease;
        box-sizing: border-box !important;
        max-width: 100%;
    }

    .leave-control:focus {
        border-color: var(--leave-primary);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .10);
    }

    .leave-year-input {
        width: 100px;
        flex-shrink: 0;
        box-sizing: border-box !important;
    }

    .leave-employee-select-wrap {
        flex: 1;
        min-width: 180px;
        max-width: 100%;
        box-sizing: border-box !important;
    }

    .leave-employee-select-wrap .select2-container {
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
        box-sizing: border-box !important;
    }

    .leave-employee-select-wrap .select2-container .select2-selection--single {
        height: 42px !important;
        border-radius: 14px !important;
        border: 1px solid var(--leave-border) !important;
        display: flex !important;
        align-items: center !important;
        background: #FFFFFF !important;
        outline: none !important;
        transition: all 0.2s ease !important;
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .leave-employee-select-wrap .select2-container--open .select2-selection--single,
    .leave-employee-select-wrap .select2-container--focus .select2-selection--single {
        border-color: var(--leave-primary) !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.10) !important;
    }

    .leave-employee-select-wrap .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        font-size: 13px !important;
        color: var(--leave-text) !important;
        font-weight: 600 !important;
        padding-left: 14px !important;
        padding-right: 28px !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .leave-employee-select-wrap .select2-container .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 10px !important;
    }

    .leave-btn {
        height: 42px;
        border-radius: 14px;
        border: none;
        background: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all .2s ease;
        box-shadow: 0 4px 14px rgba(75, 0, 232, .25);
        white-space: nowrap;
        text-decoration: none;
        box-sizing: border-box !important;
        max-width: 100% !important;
    }

    .leave-btn:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(75, 0, 232, .35);
    }

    .leave-btn-light {
        height: 42px;
        border-radius: 14px;
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        font-size: 13px;
        font-weight: 800;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all .2s ease;
        white-space: nowrap;
        text-decoration: none;
        box-sizing: border-box !important;
        max-width: 100% !important;
    }

    .leave-btn-light:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border-color: rgba(75, 0, 232, .22);
    }

    /* ORB TABLE CARD - Shared Standard */
    .orb-table-card {
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 24px;
        box-shadow: var(--leave-shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .orb-table-head {
        padding: 18px 24px;
        border-bottom: 1px solid var(--leave-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .orb-table-title-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .orb-table-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: var(--leave-soft);
        color: var(--leave-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .orb-table-title-wrap h3 {
        font-size: 17px;
        font-weight: 800;
        color: var(--leave-text);
        margin: 0;
        letter-spacing: -0.02em;
    }

    .orb-table-title-wrap p {
        font-size: 12px;
        color: var(--leave-muted);
        margin: 2px 0 0 0;
    }

    .leave-table-wrap {
        padding: 0;
    }

    .leave-table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .leave-table {
        width: 100%;
        min-width: 1100px;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0 !important;
    }

    .leave-table thead th {
        background: #FAFBFD !important;
        color: #475467;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 14px 16px;
        border-top: none;
        border-bottom: 1px solid var(--leave-border);
        white-space: nowrap;
        vertical-align: middle;
    }

    .leave-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid #F2F4F7;
        font-size: 13px;
        color: var(--leave-text);
        background: #fff;
    }

    .leave-table tbody tr:hover td {
        background: #FAF9FF;
    }

    .leave-employee {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 220px;
    }

    .leave-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 13px;
        flex-shrink: 0;
        overflow: hidden;
    }

    .leave-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .leave-employee-name {
        font-weight: 800;
        color: var(--leave-text);
        line-height: 1.25;
        font-size: 13px;
    }

    .leave-employee-meta {
        font-size: 11px;
        color: var(--leave-muted);
        margin-top: 2px;
    }

    .leave-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    .pill-stage {
        background: #EEF4FF;
        color: #3538CD;
        border: 1px solid #D1E0FF;
    }

    .pill-policy {
        background: #F8F9FC;
        color: #344054;
        border: 1px solid #EAECF0;
    }

    .pill-lwp {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .leave-metric {
        font-size: 14px;
        font-weight: 900;
        color: var(--leave-text);
        line-height: 1.2;
    }

    .leave-metric.text-success {
        color: #12B76A !important;
    }

    .leave-breakdown-box {
        font-size: 11px;
        color: var(--leave-muted);
        margin-top: 4px;
        line-height: 1.4;
        white-space: nowrap;
    }

    .leave-breakdown-item {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 6px;
        background: #F8F9FA;
        border: 1px solid #E9ECEF;
        margin-right: 2px;
        margin-bottom: 2px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .leave-breakdown-item span {
        color: var(--leave-text);
        font-weight: 900;
    }

    .leave-action-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
    }

    .leave-modal-content {
        border: none;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(16, 24, 40, .18);
    }

    .leave-modal-header {
        background: linear-gradient(180deg, #fff, #FCFCFD);
        border-bottom: 1px solid var(--leave-border);
        padding: 18px 22px;
    }

    .leave-modal-title {
        font-size: 17px;
        font-weight: 900;
        color: var(--leave-text);
        margin: 0;
    }

    .leave-modal-subtitle {
        font-size: 12px;
        color: var(--leave-muted);
        margin-top: 3px;
    }

    .leave-modal-body {
        padding: 22px;
    }

    .leave-modal-footer {
        border-top: 1px solid var(--leave-border);
        padding: 16px 22px;
        background: #FCFCFD;
    }

    .empty-state {
        text-align: center;
        padding: 48px 20px;
    }

    .empty-state i {
        font-size: 40px;
        color: #D0D5DD;
        margin-bottom: 14px;
    }

    /* Attached Filters Toolbar */
    .eo-filter-grid {
        display: grid;
        grid-template-columns: 2.2fr 1fr 1.3fr 1.5fr 1.2fr auto;
        gap: 12px;
        align-items: flex-end;
        padding: 14px 20px;
        background: #FAFAFB;
        border-bottom: 1px solid var(--leave-border);
    }

    @media (max-width: 1200px) {
        .eo-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .eo-filter-grid {
            grid-template-columns: 1fr;
        }
    }

    .eo-filter-grid-transactions {
        grid-template-columns: 2fr 0.85fr 1.2fr 1fr 1.2fr 1fr auto !important;
    }

    @media (max-width: 1400px) {
        .eo-filter-grid-transactions {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 991px) {
        .eo-filter-grid-transactions {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 576px) {
        .eo-filter-grid-transactions {
            grid-template-columns: 1fr !important;
        }
    }

    .eo-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .eo-field label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--leave-muted);
        margin: 0;
        white-space: nowrap;
    }

    .eo-control {
        width: 100%;
        height: 38px;
        padding: 0 12px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--leave-text);
        background: #ffffff;
        border: 1.5px solid var(--leave-border);
        border-radius: 10px;
        outline: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .eo-control:focus {
        border-color: var(--leave-primary);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12);
    }

    .eo-filter-actions-col {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .eo-filter-actions-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #btnFilterSubmit, #btnTxFilterSubmit {
        height: 38px;
        padding: 0 18px;
        font-size: 12.5px;
        font-weight: 800;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #ffffff;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.25);
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    #btnFilterSubmit:hover, #btnTxFilterSubmit:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.35);
    }

    #resetFilter, #resetTxFilter {
        height: 38px;
        padding: 0 14px;
        font-size: 12px;
        font-weight: 800;
        border-radius: 10px;
        background: #ffffff;
        color: var(--leave-muted);
        border: 1.5px solid var(--leave-border);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    #resetFilter:hover, #resetTxFilter:hover {
        background: #F4F5F7;
        color: var(--leave-text);
        border-color: #D0D5DD;
    }

    /* DataTable Overrides */
    .dataTables_wrapper {
        padding: 0 !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin: 0 !important;
        padding: 0 !important;
    }

    .dataTables_filter {
        display: none !important;
    }

    .dataTables_length label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin: 0 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        color: var(--leave-muted) !important;
    }

    .dataTables_length select {
        height: 34px !important;
        border-radius: 8px !important;
        border: 1px solid var(--leave-border) !important;
        padding: 0 10px !important;
        font-size: 12px !important;
        color: var(--leave-text) !important;
        background-color: #fff !important;
    }

    #allocationExportButtons .dt-buttons,
    .dt-buttons {
        display: inline-flex !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        margin: 0 !important;
    }

    #allocationExportButtons .dt-button,
    #allocationExportButtons .btn,
    .dt-buttons .dt-button,
    .dt-buttons .btn {
        height: 36px !important;
        padding: 0 14px !important;
        border-radius: 10px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        border: 1.5px solid var(--leave-border) !important;
        background: #ffffff !important;
        color: var(--leave-text) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
        cursor: pointer !important;
    }

    #allocationExportButtons .dt-button:hover,
    #allocationExportButtons .btn:hover,
    .dt-buttons .dt-button:hover,
    .dt-buttons .btn:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 10px rgba(75, 0, 232, 0.12) !important;
    }

    .dt-buttons .buttons-excel:hover,
    #allocationExportButtons .buttons-excel:hover {
        border-color: #12B76A !important;
        background: #ECFDF3 !important;
        color: #027A48 !important;
    }

    .dt-buttons .buttons-csv:hover,
    #allocationExportButtons .buttons-csv:hover {
        border-color: #2E90FA !important;
        background: #EFF8FF !important;
        color: #175CD3 !important;
    }

    .dt-buttons .buttons-pdf:hover,
    #allocationExportButtons .buttons-pdf:hover {
        border-color: #F04438 !important;
        background: #FEF3F2 !important;
        color: #B42318 !important;
    }

    .dt-buttons .buttons-print:hover,
    #allocationExportButtons .buttons-print:hover {
        border-color: #7A5AF8 !important;
        background: #F4F3FF !important;
        color: #5925DC !important;
    }

    .orb-table-footer {
        padding: 16px 24px;
        background: #ffffff;
        border-top: 1px solid var(--leave-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .dataTables_info {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #475467 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .dataTables_paginate {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .dataTables_paginate .pagination {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    .dataTables_paginate .pagination .page-item {
        margin: 0 !important;
    }

    .dataTables_paginate .pagination .page-item .page-link,
    .dataTables_paginate .paginate_button {
        height: 36px !important;
        min-width: 36px !important;
        padding: 0 14px !important;
        border-radius: 10px !important;
        border: 1.5px solid #E7EAF3 !important;
        background: #ffffff !important;
        color: #4B00E8 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
        text-decoration: none !important;
        outline: none !important;
    }

    .dataTables_paginate .pagination .page-item:not(.active):not(.disabled) .page-link:hover,
    .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: #F4F2FF !important;
        border-color: #DDD6FE !important;
        color: #4B00E8 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 10px rgba(75, 0, 232, 0.12) !important;
    }

    .dataTables_paginate .pagination .page-item.active .page-link,
    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(135deg, #4B00E8, #E02870) !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 900 !important;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.35) !important;
        cursor: default !important;
        transform: none !important;
    }

    .dataTables_paginate .pagination .page-item.disabled .page-link,
    .dataTables_paginate .paginate_button.disabled,
    .dataTables_paginate .paginate_button.disabled:hover {
        background: #ffffff !important;
        color: #4B00E8 !important;
        border-color: #E7EAF3 !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        transform: none !important;
        opacity: 0.8 !important;
    }

    /* Select2 Custom Responsive Theme */
    .select2-container {
        max-width: 100% !important;
    }

    .select2-dropdown {
        border-radius: 12px !important;
        border: 1px solid var(--leave-border) !important;
        box-shadow: 0 10px 30px rgba(16, 24, 40, 0.12) !important;
        overflow: hidden !important;
        z-index: 1060 !important;
    }

    .select2-search--dropdown {
        padding: 8px !important;
    }

    .select2-search--dropdown .select2-search__field {
        border: 1.5px solid var(--leave-border) !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        outline: none !important;
        font-size: 13px !important;
    }

    .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--leave-primary) !important;
    }

    .select2-results__option--highlighted[aria-selected] {
        background-color: var(--leave-primary) !important;
        color: #fff !important;
    }

    /* Responsive Breakpoints */
    @media (max-width: 991.98px) {
        .leave-action-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .leave-hero {
            padding: 18px 20px;
            border-radius: 20px;
        }

        .leave-hero-title {
            font-size: 22px;
        }

        .leave-card-head {
            padding: 16px 18px;
        }

        .leave-card-body {
            padding: 16px 18px;
        }

        .leave-form-row {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        .leave-control,
        .leave-btn,
        .leave-btn-light {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        .leave-year-input,
        .leave-employee-select-wrap {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            flex: none !important;
            box-sizing: border-box !important;
        }

        .leave-employee-select-wrap .select2-container {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        .leave-employee-select-wrap .select2-container .select2-selection--single {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
    }

    @media (max-width: 768px) {
        .leave-hero {
            padding: 16px;
            border-radius: 18px;
            margin-bottom: 14px;
        }

        .leave-hero-title {
            font-size: 20px;
        }

        .leave-hero-subtitle {
            font-size: 12px;
        }

        .leave-card {
            border-radius: 18px;
            margin-bottom: 14px;
        }

        .orb-table-card {
            border-radius: 18px;
        }

        .eo-filter-grid {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
            padding: 14px 16px !important;
        }

        .eo-filter-actions-wrap {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 8px !important;
            width: 100% !important;
        }

        #btnFilterSubmit, #resetFilter, #btnTxFilterSubmit, #resetTxFilter {
            width: 100% !important;
            height: 40px !important;
            justify-content: center !important;
        }

        .orb-table-head {
            padding: 14px 16px;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        #allocationExportButtons {
            width: 100% !important;
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 6px !important;
        }

        #allocationExportButtons .dt-buttons {
            width: 100% !important;
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 6px !important;
        }

        #allocationExportButtons .dt-button,
        #allocationExportButtons .btn {
            flex: 1 1 calc(50% - 6px) !important;
            min-width: 60px !important;
            height: 36px !important;
            padding: 0 8px !important;
            font-size: 11.5px !important;
        }

        .orb-table-footer {
            padding: 14px 16px !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            gap: 12px !important;
        }

        .dataTables_info {
            text-align: center !important;
            width: 100% !important;
            font-size: 12px !important;
        }

        .dataTables_paginate {
            width: 100% !important;
            justify-content: center !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 0 !important;
        }

        .dataTables_paginate .pagination {
            flex-wrap: wrap !important;
            justify-content: center !important;
            gap: 4px !important;
        }

        .dataTables_paginate .pagination .page-item .page-link,
        .dataTables_paginate .paginate_button {
            height: 34px !important;
            min-width: 34px !important;
            padding: 0 10px !important;
            font-size: 12px !important;
        }
    }

    @media (max-width: 576px) {
        .leave-hero-kicker {
            font-size: 11px;
            padding: 4px 10px;
        }

        .leave-hero-title {
            font-size: 18px;
        }

        .leave-card-title {
            font-size: 15px;
        }

        .leave-action-box {
            padding: 14px;
        }
    }
</style>
@endsection

@section('_content')
<div class="leave-page-wrap">

    @include('hrms.leave.allocations.partials.hero')

    @include('hrms.leave.shared.flash')

    @include('hrms.leave.allocations.partials.action-panel')

    <div class="d-flex flex-wrap align-items-center mb-3" style="gap:10px;">
        <a class="leave-view-tab {{ $activeTab === 'allocations' ? 'active' : '' }}"
           href="{{ route('leave-allocations.index', array_merge(request()->query(), ['year' => $year, 'tab' => 'allocations'])) }}">
            <i class="fas fa-wallet mr-1"></i> Leave Allocations
        </a>
        <a class="leave-view-tab {{ $activeTab === 'monthly' ? 'active' : '' }}"
           href="{{ route('leave-allocations.index', array_merge(request()->query(), ['year' => $year, 'tab' => 'monthly'])) }}">
            <i class="fas fa-calendar-check mr-1"></i> Monthly Balances
        </a>
        <a class="leave-view-tab {{ $activeTab === 'transactions' ? 'active' : '' }}"
           href="{{ route('leave-allocations.index', array_merge(request()->query(), ['year' => $year, 'tab' => 'transactions'])) }}">
            <i class="fas fa-list-alt mr-1"></i> Accrual & Balance Transactions
        </a>
        {{-- @if($activeTab === 'monthly')
            <span class="text-muted small">Month-wise opening (carry forward), earned quota, leaves used, comp-off, LWP and available balance.</span>
        @endif --}}
    </div>

    <div class="orb-table-card">
        <div class="orb-table-head">
            <div class="orb-table-title-wrap">
                <div class="orb-table-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <h3 class="m-0">{{ $activeTab === 'monthly' ? 'Monthly Leave Balances' : ($activeTab === 'transactions' ? 'Accrual & Balance Transactions' : 'Allocation Records') }}</h3>
                    <p class="m-0 text-muted" style="font-size: 12.5px;">
                        {{ $activeTab === 'monthly' ? 'Month-wise breakdown: opening carry-forward, earned quota, leaves used, comp-off, LWP and available balance.' : ($activeTab === 'transactions' ? 'Read-only audit history for accruals, deductions, reversals, adjustments and lapses.' : 'View each employee allocation with total allocated, used and remaining leave balances.') }}
                    </p>
                </div>
            </div>

            <div id="allocationExportButtons" class="d-flex align-items-center gap-2"></div>
        </div>

        @if($activeTab === 'transactions')
            @include('hrms.leave.allocations.partials.transaction-filter-bar')
        @else
            @include('hrms.leave.allocations.partials.filter-bar')
        @endif
        @if($activeTab === 'allocations')
            @include('hrms.leave.allocations.partials.allocation-table')
            @if($canManageAllocations)
                @include('hrms.leave.allocations.partials.edit-modal')
                @include('hrms.leave.allocations.partials.delete-modal')
            @endif
        @elseif($activeTab === 'monthly')
            <div class="leave-table-wrap">
                <div class="leave-table-responsive">
                    <table id="leaveAllocationsTable" class="leave-table js-leave-allocations-table leave-monthly-table" style="width:100%;">
                        <thead>
                            <tr style="background: #F8F9FC; border-bottom: 1px solid #E4E7EC;">
                                <th rowspan="2" class="text-center" style="width: 45px; vertical-align: middle;">#</th>
                                <th rowspan="2" class="text-left" style="min-width: 190px; vertical-align: middle;">Employee</th>
                                <th rowspan="2" class="text-center" style="min-width: 110px; vertical-align: middle;">Month</th>
                                <th rowspan="2" class="text-center" style="min-width: 100px; vertical-align: middle;">Stage</th>
                                <th colspan="4" class="text-center" style="background: #EEF4FF; color: #175CD3; border-left: 1px solid #D0D5DD; border-right: 1px solid #D0D5DD; font-weight: 700; font-size: 12px;">
                                    <i class="fas fa-umbrella-beach mr-1"></i> PAID LEAVE (PL)
                                </th>
                                <th colspan="4" class="text-center" style="background: #F4F3FF; color: #5925DC; border-right: 1px solid #D0D5DD; font-weight: 700; font-size: 12px;">
                                    <i class="fas fa-notes-medical mr-1"></i> SICK LEAVE (SL)
                                </th>
                                <th colspan="4" class="text-center" style="background: #ECFDF3; color: #027A48; border-right: 1px solid #D0D5DD; font-weight: 700; font-size: 12px;">
                                    <i class="fas fa-gift mr-1"></i> COMP-OFF
                                </th>
                                <th rowspan="2" class="text-center" style="min-width: 75px; vertical-align: middle; background: #FFF4ED; color: #B93815;">LWP</th>
                                <th rowspan="2" class="text-center" style="min-width: 85px; vertical-align: middle;">Status</th>
                            </tr>
                            <tr style="background: #F8F9FC; border-bottom: 2px solid #D0D5DD; font-size: 11.5px;">
                                <!-- PL -->
                                <th class="text-center" style="background: #F8FAFC; border-left: 1px solid #D0D5DD; min-width: 70px;" title="Carry Forward / Opening">Carry Fwd</th>
                                <th class="text-center" style="background: #F8FAFC; min-width: 65px;" title="Earned this Month">Earned</th>
                                <th class="text-center" style="background: #F8FAFC; min-width: 65px;" title="Used this Month">Used</th>
                                <th class="text-center font-weight-bold" style="background: #EEF4FF; color: #175CD3; border-right: 1px solid #D0D5DD; min-width: 75px;" title="Available Balance">Available</th>

                                <!-- SL -->
                                <th class="text-center" style="background: #F8FAFC; min-width: 70px;" title="Carry Forward / Opening">Carry Fwd</th>
                                <th class="text-center" style="background: #F8FAFC; min-width: 65px;" title="Earned this Month">Earned</th>
                                <th class="text-center" style="background: #F8FAFC; min-width: 65px;" title="Used this Month">Used</th>
                                <th class="text-center font-weight-bold" style="background: #F4F3FF; color: #5925DC; border-right: 1px solid #D0D5DD; min-width: 75px;" title="Available Balance">Available</th>

                                <!-- Comp-Off -->
                                <th class="text-center" style="background: #F8FAFC; min-width: 65px;" title="Comp-Off Earned">Earned</th>
                                <th class="text-center" style="background: #F8FAFC; min-width: 65px;" title="Comp-Off Used">Used</th>
                                <th class="text-center" style="background: #F8FAFC; min-width: 65px;" title="Comp-Off Lapsed at Month-End">Lapsed</th>
                                <th class="text-center font-weight-bold" style="background: #ECFDF3; color: #027A48; border-right: 1px solid #D0D5DD; min-width: 75px;" title="Available Balance">Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlyBalances as $monthlyBalance)
                                @php
                                    $monthlyEmployee = $monthlyBalance->employee;
                                    $monthlyEmployeeName = $monthlyEmployee?->user?->name ?? $monthlyEmployee?->employee_code ?? 'Employee';
                                    $monthlyNumber = static fn ($value) => number_format((float) $value, 2);
                                    $stageAllocation = $monthlyBalance->allocation;
                                    $monthlyPolicyName = $monthlyBalance->policy?->policy_name ?? '';
                                    $isPermanent = $monthlyBalance->employment_stage === 'permanent';
                                @endphp
                                <tr data-stage="{{ strtolower($monthlyBalance->employment_stage ?? '') }}" data-policy="{{ strtolower($monthlyPolicyName) }}" data-search="{{ strtolower($monthlyEmployeeName . ' ' . ($monthlyEmployee?->employee_code ?? '')) }}">
                                    <td class="text-center align-middle font-weight-bold" style="color: var(--leave-text);">
                                        {{ $loop->iteration + ($monthlyBalances->currentPage() - 1) * $monthlyBalances->perPage() }}
                                    </td>
                                    <td class="align-middle text-left">
                                        <div class="leave-employee">
                                            <div>
                                                <div class="leave-employee-name" style="font-weight: 700; color: #1D2939;">{{ $monthlyEmployeeName }}</div>
                                                <div class="leave-employee-meta" style="font-size: 11px; color: #667085;">{{ $monthlyEmployee?->employee_code ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="font-weight-bold text-dark d-inline-flex align-items-center" style="font-size: 12.5px;">
                                            <i class="far fa-calendar-alt mr-1 text-primary"></i>
                                            {{ \Carbon\Carbon::create((int) $monthlyBalance->year, (int) $monthlyBalance->month, 1)->format('M Y') }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="leave-pill pill-stage" style="font-size: 11px;">
                                            {{ ucfirst(str_replace('_', ' ', $monthlyBalance->employment_stage ?? '-')) }}
                                        </span>
                                    </td>

                                    <!-- PL: Carry Fwd, Earned, Used, Available -->
                                    <td class="text-center align-middle" style="border-left: 1px solid #EAECF0;">
                                        {{ $isPermanent ? $monthlyNumber($monthlyBalance->pl_opening) : '—' }}
                                    </td>
                                    <td class="text-center align-middle text-success font-weight-bold">
                                        {{ $isPermanent ? '+' . $monthlyNumber($monthlyBalance->pl_accrued) : '—' }}
                                    </td>
                                    <td class="text-center align-middle {{ (float) $monthlyBalance->pl_used > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                        {{ (float) $monthlyBalance->pl_used > 0 ? '-' . $monthlyNumber($monthlyBalance->pl_used) : '0.00' }}
                                    </td>
                                    <td class="text-center align-middle font-weight-bold" style="background: #EEF4FF; color: #175CD3; border-right: 1px solid #EAECF0;">
                                        {{ $isPermanent ? $monthlyNumber($monthlyBalance->pl_closing) : $monthlyNumber($stageAllocation?->paid_remaining ?? 0) }}
                                    </td>

                                    <!-- SL: Carry Fwd, Earned, Used, Available -->
                                    <td class="text-center align-middle">
                                        {{ $isPermanent ? $monthlyNumber($monthlyBalance->sl_opening) : '—' }}
                                    </td>
                                    <td class="text-center align-middle text-success font-weight-bold">
                                        {{ $isPermanent ? '+' . $monthlyNumber($monthlyBalance->sl_accrued) : '—' }}
                                    </td>
                                    <td class="text-center align-middle {{ (float) $monthlyBalance->sl_used > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                        {{ (float) $monthlyBalance->sl_used > 0 ? '-' . $monthlyNumber($monthlyBalance->sl_used) : '0.00' }}
                                    </td>
                                    <td class="text-center align-middle font-weight-bold" style="background: #F4F3FF; color: #5925DC; border-right: 1px solid #EAECF0;">
                                        {{ $isPermanent ? $monthlyNumber($monthlyBalance->sl_closing) : $monthlyNumber($stageAllocation?->sick_remaining ?? 0) }}
                                    </td>

                                    <!-- Comp-Off: Earned, Used, Lapsed, Available -->
                                    <td class="text-center align-middle text-success font-weight-bold">
                                        {{ (float) $monthlyBalance->comp_off_earned > 0 ? '+' . $monthlyNumber($monthlyBalance->comp_off_earned) : '0.00' }}
                                    </td>
                                    <td class="text-center align-middle {{ (float) $monthlyBalance->comp_off_used > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                        {{ (float) $monthlyBalance->comp_off_used > 0 ? '-' . $monthlyNumber($monthlyBalance->comp_off_used) : '0.00' }}
                                    </td>
                                    <td class="text-center align-middle {{ (float) $monthlyBalance->comp_off_lapsed > 0 ? 'text-warning font-weight-bold' : 'text-muted' }}">
                                        {{ (float) $monthlyBalance->comp_off_lapsed > 0 ? $monthlyNumber($monthlyBalance->comp_off_lapsed) : '0.00' }}
                                    </td>
                                    <td class="text-center align-middle font-weight-bold" style="background: #ECFDF3; color: #027A48; border-right: 1px solid #EAECF0;">
                                        {{ $monthlyNumber($monthlyBalance->comp_off_closing) }}
                                    </td>

                                    <!-- LWP -->
                                    <td class="text-center align-middle">
                                        @if((float) $monthlyBalance->lwp_used > 0)
                                            <span class="badge px-2 py-1" style="border-radius:6px; font-weight:800; background:#FEF3F2; color:#B42318; border:1px solid #FECDCA;">{{ $monthlyNumber($monthlyBalance->lwp_used) }}</span>
                                        @else
                                            <span class="text-muted">0.00</span>
                                        @endif
                                    </td>

                                    <!-- Payroll Status -->
                                    <td class="text-center align-middle">
                                        @if($monthlyBalance->is_locked)
                                            <span class="badge badge-success" style="font-size: 11px;"><i class="fas fa-lock mr-1"></i>Locked</span>
                                        @else
                                            <span class="badge badge-light border" style="font-size: 11px; color:#667085;">Open</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="18">
                                        <div class="empty-state p-4 text-center">
                                            <i class="fas fa-calendar-times text-muted" style="font-size: 32px;"></i>
                                            <h6 class="font-weight-bold text-dark mt-2">No Monthly Balance Records Found</h6>
                                            <p class="text-muted small mb-0">No records found for the selected month/year. Try selecting "All Months" or another filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="orb-table-footer p-0 border-0">{{ $monthlyBalances->links('vendor.pagination.orbo') }}</div>
        @else
            <div class="leave-table-wrap">
                <div class="leave-table-responsive">
                    <table id="leaveAllocationsTable" class="leave-table js-leave-allocations-table leave-transactions-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th class="text-left" style="min-width: 110px;">Date</th>
                                <th class="text-center" style="min-width: 100px;">Month</th>
                                <th class="text-left" style="min-width: 220px;">Employee</th>
                                <th class="text-center" style="min-width: 80px;">Bucket</th>
                                <th class="text-center" style="min-width: 120px;">Type</th>
                                <th class="text-center" style="min-width: 100px;">Amount</th>
                                <th class="text-center" style="min-width: 110px;">Balance After</th>
                                <th class="text-center" style="min-width: 90px;">Source</th>
                                <th class="text-left" style="min-width: 120px;">Done By</th>
                                <th class="text-center" style="min-width: 90px;">Request</th>
                                <th class="text-left" style="min-width: 200px;">Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                @php
                                    $txEmpInitial = strtoupper(substr(trim($transaction->employee_name ?? 'E'), 0, 1));
                                    $amount = (float) $transaction->amount;
                                    $typeKey = $transaction->transaction_type;
                                    $typeLabel = ['monthly_accrual'=>'Accrual','lapse'=>'Lapse','leave_debit'=>'Leave debit','reversal'=>'Reversal','adjustment'=>'Adjustment','opening_balance'=>'Opening'][$typeKey] ?? ucfirst(str_replace('_', ' ', $typeKey));
                                    $src = strtolower($transaction->source ?? 'system');
                                @endphp
                                <tr>
                                    <td class="text-center align-middle font-weight-bold" style="color: var(--leave-text);">
                                        {{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}
                                    </td>
                                    <td class="align-middle text-left">
                                        <span class="font-weight-bold text-dark d-inline-flex align-items-center">
                                            <i class="far fa-calendar-alt mr-2" style="color: var(--leave-primary); font-size: 13px;"></i>
                                            {{ \Carbon\Carbon::parse($transaction->effective_date)->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge px-2 py-1" style="background:#F8F9FA; color:#344054; border:1px solid #E4E7EC; font-weight:700; font-size:11.5px; border-radius:6px;">
                                            {{ $transaction->period_month ? \Carbon\Carbon::create((int)$transaction->period_year, (int)$transaction->period_month, 1)->format('M Y') : $transaction->period_year }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-left">
                                        <div class="leave-employee">
                                            {{-- <div class="leave-avatar">{{ $txEmpInitial }}</div> --}}
                                            <div>
                                                <div class="leave-employee-name">{{ $transaction->employee_name }}</div>
                                                <div class="leave-employee-meta">{{ $transaction->employee_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($transaction->bucket === 'PL')
                                            <span class="badge px-2 py-1" style="background:#F4F2FF; color:#4B00E8; border:1px solid #DDD6FE; font-weight:800; font-size:11.5px; border-radius:6px;">PL</span>
                                        @elseif($transaction->bucket === 'SL')
                                            <span class="badge px-2 py-1" style="background:#EFF8FF; color:#175CD3; border:1px solid #B2DDFF; font-weight:800; font-size:11.5px; border-radius:6px;">SL</span>
                                        @else
                                            <span class="badge px-2 py-1" style="background:#F8F9FA; color:#344054; border:1px solid #E4E7EC; font-weight:800; font-size:11.5px; border-radius:6px;">{{ $transaction->bucket }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($typeKey === 'monthly_accrual')
                                            <span class="badge px-2 py-1" style="background:#ECFDF3; color:#027A48; border:1px solid #A6F4C5; font-weight:700; font-size:11px; border-radius:6px;">
                                                <i class="fas fa-plus-circle mr-1"></i> Accrual
                                            </span>
                                        @elseif($typeKey === 'leave_debit')
                                            <span class="badge px-2 py-1" style="background:#FEF0C7; color:#B54708; border:1px solid #FEDF89; font-weight:700; font-size:11px; border-radius:6px;">
                                                <i class="fas fa-minus-circle mr-1"></i> Leave Debit
                                            </span>
                                        @elseif($typeKey === 'lapse')
                                            <span class="badge px-2 py-1" style="background:#FEF3F2; color:#B42318; border:1px solid #FECDCA; font-weight:700; font-size:11px; border-radius:6px;">
                                                <i class="fas fa-hourglass-end mr-1"></i> Lapse
                                            </span>
                                        @elseif($typeKey === 'reversal')
                                            <span class="badge px-2 py-1" style="background:#F0F9FF; color:#026AA7; border:1px solid #B2DDFF; font-weight:700; font-size:11px; border-radius:6px;">
                                                <i class="fas fa-undo mr-1"></i> Reversal
                                            </span>
                                        @elseif($typeKey === 'adjustment')
                                            <span class="badge px-2 py-1" style="background:#F4F3FF; color:#5925DC; border:1px solid #D9D6FE; font-weight:700; font-size:11px; border-radius:6px;">
                                                <i class="fas fa-sliders-h mr-1"></i> Adjustment
                                            </span>
                                        @elseif($typeKey === 'opening_balance')
                                            <span class="badge px-2 py-1" style="background:#F8F9FA; color:#344054; border:1px solid #E4E7EC; font-weight:700; font-size:11px; border-radius:6px;">
                                                <i class="fas fa-flag mr-1"></i> Opening
                                            </span>
                                        @else
                                            <span class="badge px-2 py-1" style="background:#F8F9FA; color:#344054; border:1px solid #E4E7EC; font-weight:700; font-size:11px; border-radius:6px;">
                                                {{ $typeLabel }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($amount > 0)
                                            <span class="badge px-2 py-1" style="background:#ECFDF3; color:#027A48; border:1px solid #A6F4C5; font-weight:800; font-size:12px; border-radius:6px;">
                                                +{{ number_format($amount, 2) }}
                                            </span>
                                        @elseif($amount < 0)
                                            <span class="badge px-2 py-1" style="background:#FEF3F2; color:#B42318; border:1px solid #FECDCA; font-weight:800; font-size:12px; border-radius:6px;">
                                                {{ number_format($amount, 2) }}
                                            </span>
                                        @else
                                            <span class="badge px-2 py-1" style="background:#F8F9FA; color:#667085; border:1px solid #E4E7EC; font-weight:800; font-size:12px; border-radius:6px;">
                                                0.00
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="font-weight-bold text-success" style="font-size: 13.5px;">
                                            {{ number_format((float) $transaction->balance_after, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($src === 'import')
                                            <span class="badge px-2 py-1" style="background:#EFF8FF; color:#175CD3; border:1px solid #B2DDFF; font-weight:700; font-size:11px; border-radius:6px;">
                                                <i class="fas fa-file-import mr-1"></i> Import
                                            </span>
                                        @elseif($src === 'manual')
                                            <span class="badge px-2 py-1" style="background:#FEF0C7; color:#B54708; border:1px solid #FEDF89; font-weight:700; font-size:11px; border-radius:6px;">
                                                <i class="fas fa-user-edit mr-1"></i> Manual
                                            </span>
                                        @else
                                            <span class="badge px-2 py-1" style="background:#F2F4F7; color:#344054; border:1px solid #EAECF0; font-weight:700; font-size:11px; border-radius:6px;">
                                                <i class="fas fa-robot mr-1 text-muted"></i> System
                                            </span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-left">
                                        <div class="d-inline-flex align-items-center" style="font-size: 12.5px; font-weight: 700; color: var(--leave-text);">
                                            <i class="far fa-user mr-1 text-muted" style="font-size: 11px;"></i>
                                            {{ $transaction->actor_name }}
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($transaction->leave_request_id)
                                            <a href="{{ url('/leave-approvals/'.$transaction->leave_request_id) }}" target="_blank" title="View Leave Request #{{ $transaction->leave_request_id }} Details" class="badge px-2.5 py-1" style="background:#F4F2FF; color:#4B00E8; border:1px solid #DDD6FE; font-weight:800; font-size:11.5px; text-decoration:none; border-radius:6px; transition:0.2s ease;">
                                                <i class="fas fa-external-link-alt mr-1" style="font-size: 9px;"></i> #{{ $transaction->leave_request_id }}
                                            </a>
                                        @else
                                            <span class="text-muted font-weight-bold">—</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-left">
                                        <span class="text-muted" style="font-size: 12px; font-weight: 600; line-height: 1.35; display: inline-block; max-width: 260px;" title="{{ $transaction->reason }}">
                                            {{ $transaction->reason ?: '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12">
                                        <div class="empty-state">
                                            <i class="fas fa-folder-open"></i>
                                            <h6 class="font-weight-bold text-dark">No Transactions Found</h6>
                                            <p class="text-muted small mb-0">No balance or accrual transactions match these filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="orb-table-footer p-0 border-0">{{ $transactions->links('vendor.pagination.orbo') }}</div>
        @endif
    </div>

</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')

@if($activeTab === 'allocations' && $canManageAllocations)
<script>
    // Event delegation for opening Single Reusable Edit Modal
    $(document).on('click', '.btn-edit-allocation', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var url = $btn.data('url');

        var $modal = $('#editAllocationModal');
        var $form = $('#editAllocationForm');
        var $loading = $('#editModalLoading');
        var $fields = $('#editModalFormFields');

        $loading.show();
        $fields.hide();
        $('#edit_modal_employee_name').text('Loading...');
        $modal.modal('show');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (res) {
                if (res && res.success && res.allocation) {
                    var a = res.allocation;
                    $form.attr('action', a.update_url);
                    $('#edit_allocation_id').val(a.id);
                    $('#edit_employee_id').val(a.employee_id);
                    $('#edit_adjustment_uuid').val(a.adjustment_uuid || '');
                    $('#edit_modal_employee_name').text(a.employee_name + ' (' + a.employee_code + ')');
                    $('#edit_year').val(a.year);
                    $('#edit_employment_stage').val(a.employment_stage);
                    $('#edit_policy_id').val(a.policy_id || '');
                    if (typeof window.initOrboDatePickers === 'function') {
                        window.initOrboDatePickers(document.getElementById('editAllocationModal'));
                    }
                    var fromEl = document.getElementById('edit_allocation_from_date');
                    if (fromEl) {
                        fromEl.value = a.allocation_from_date || '';
                        if (fromEl._flatpickr) {
                            fromEl._flatpickr.setDate(a.allocation_from_date || '', true);
                        }
                    } else {
                        $('#edit_allocation_from_date').val(a.allocation_from_date || '');
                    }
                    var toEl = document.getElementById('edit_allocation_to_date');
                    if (toEl) {
                        toEl.value = a.allocation_to_date || '';
                        if (toEl._flatpickr) {
                            toEl._flatpickr.setDate(a.allocation_to_date || '', true);
                        }
                    } else {
                        $('#edit_allocation_to_date').val(a.allocation_to_date || '');
                    }
                    $('#edit_paid_allocated').val(a.paid_allocated);
                    $('#edit_sick_allocated').val(a.sick_allocated);

                    if (isLedgerBacked && a.accrued_paid_to_date !== undefined) {
                        var period = a.accrued_period || 'current month';
                        $('#edit_accrued_pl_info').html('<i class="fas fa-history mr-1"></i> Accrued till ' + period + ': <strong>' + parseFloat(a.accrued_paid_to_date).toFixed(2) + '</strong> days').show();
                        $('#edit_accrued_sl_info').html('<i class="fas fa-history mr-1"></i> Accrued till ' + period + ': <strong>' + parseFloat(a.accrued_sick_to_date).toFixed(2) + '</strong> days').show();
                    } else {
                        $('#edit_accrued_pl_info').hide().empty();
                        $('#edit_accrued_sl_info').hide().empty();
                    }

                    $('#edit_paid_remaining').val(a.paid_remaining);
                    $('#edit_sick_remaining').val(a.sick_remaining);
                    $('#edit_comp_off_allocated').val(a.comp_off_allocated);
                    $('#edit_comp_off_reason').val('').prop('required', false);
                    $form.data('orig-comp-off-allocated', parseFloat(a.comp_off_allocated) || 0);
                    $('#edit_paid_used').val(a.paid_used);
                    $('#edit_sick_used').val(a.sick_used);
                    $('#edit_comp_off_used').val(a.comp_off_used);
                    $('#edit_lwp_used').val(a.lwp_used);
                    var coAlloc = parseFloat(a.comp_off_allocated) || 0;
                    var coUsed = parseFloat(a.comp_off_used) || 0;
                    var coRem = (a.comp_off_remaining !== undefined && a.comp_off_remaining !== null)
                        ? parseFloat(a.comp_off_remaining)
                        : Math.max(0, coAlloc - coUsed);
                    $('#edit_comp_off_remaining').val(coRem.toFixed(2));
                    $('#edit_allocation_reason').val(a.allocation_reason || '');
                    $('#edit_is_locked').prop('checked', !!a.is_locked);

                    var isLedgerBacked = !!a.is_ledger_backed;
                    $('#edit_paid_allocated, #edit_sick_allocated').prop('readonly', isLedgerBacked);
                    $('#edit_paid_used, #edit_sick_used').prop('readonly', false);
                    $('#edit_comp_off_used').prop('readonly', isLedgerBacked);
                    $form.data('orig-paid-used', parseFloat(a.paid_used) || 0);
                    $form.data('orig-sick-used', parseFloat(a.sick_used) || 0);
                    $form.data('orig-lwp-used', parseFloat(a.lwp_used) || 0);
                    $form.data('orig-paid-rem', parseFloat(a.paid_remaining) || 0);
                    $form.data('orig-sick-rem', parseFloat(a.sick_remaining) || 0);
                    $form.data('orig-reason', a.allocation_reason || '');
                    $form.data('user-typed-reason', false);
                    $form.data('is-ledger-backed', isLedgerBacked);
                    $('#edit_pl_target_edited').val(0);
                    $('#edit_sl_target_edited').val(0);

                    $('#editLedgerPlTarget, #editLedgerSlTarget').toggle(isLedgerBacked);
                    $('.ledger-allocation-only').filter(':not(#editLedgerPlTarget):not(#editLedgerSlTarget)').toggle(isLedgerBacked);
                    $('#edit_allocation_reason').prop('required', isLedgerBacked);
                    $('#btnRecalculateAllocation').toggle(!isLedgerBacked);

                    if (a.is_unpaid_intern) {
                        $('#editNonPermNotice').show();
                    } else {
                        $('#editNonPermNotice').hide();
                    }
                    $('#editSpilloverNotice').hide().empty();

                    $loading.hide();
                    $fields.show();
                } else {
                    alert('Could not load allocation details.');
                    $modal.modal('hide');
                }
            },
            error: function (xhr) {
                alert('Error fetching allocation details: ' + (xhr.responseJSON?.message || xhr.statusText));
                $modal.modal('hide');
            }
        });
    });

    $(document).on('input', '#edit_allocation_reason', function () {
        var val = $(this).val().trim();
        $('#editAllocationForm').data('user-typed-reason', val !== '');
    });

    function updateAllocationAutoReason(spilloverText) {
        var $form = $('#editAllocationForm');
        if (!$form.data('is-ledger-backed') || $form.data('user-typed-reason')) return;

        if (spilloverText) {
            $('#edit_allocation_reason').val(spilloverText);
            return;
        }

        var origPlUsed = parseFloat($form.data('orig-paid-used')) || 0;
        var newPlUsed = parseFloat($('#edit_paid_used').val());
        var deltaPl = !isNaN(newPlUsed) ? (newPlUsed - origPlUsed) : 0;

        var origSlUsed = parseFloat($form.data('orig-sick-used')) || 0;
        var newSlUsed = parseFloat($('#edit_sick_used').val());
        var deltaSl = !isNaN(newSlUsed) ? (newSlUsed - origSlUsed) : 0;

        var parts = [];
        if (Math.abs(deltaPl) >= 0.01) {
            var actionPl = deltaPl > 0 ? ('deducted ' + deltaPl.toFixed(2)) : ('reversed ' + Math.abs(deltaPl).toFixed(2));
            parts.push('Paid used: ' + actionPl + ' day(s) (' + origPlUsed + ' -> ' + newPlUsed + ')');
        }
        if (Math.abs(deltaSl) >= 0.01) {
            var actionSl = deltaSl > 0 ? ('deducted ' + deltaSl.toFixed(2)) : ('reversed ' + Math.abs(deltaSl).toFixed(2));
            parts.push('Sick used: ' + actionSl + ' day(s) (' + origSlUsed + ' -> ' + newSlUsed + ')');
        }

        if (parts.length > 0) {
            $('#edit_allocation_reason').val('Manual adjustment: ' + parts.join(', '));
        } else {
            $('#edit_allocation_reason').val($form.data('orig-reason') || '');
        }
    }

    function syncLiveSpillover() {
        var $form = $('#editAllocationForm');
        if (!$form.data('is-ledger-backed')) return;

        var origPlUsed = parseFloat($form.data('orig-paid-used')) || 0;
        var origPlRem = parseFloat($form.data('orig-paid-rem')) || 0;
        var origSlUsed = parseFloat($form.data('orig-sick-used')) || 0;
        var origSlRem = parseFloat($form.data('orig-sick-rem')) || 0;
        var origLwpUsed = parseFloat($form.data('orig-lwp-used')) || 0;

        var newPlUsed = parseFloat($('#edit_paid_used').val());
        var newSlUsed = parseFloat($('#edit_sick_used').val());
        var deltaPl = !isNaN(newPlUsed) ? (newPlUsed - origPlUsed) : 0;
        var deltaSl = !isNaN(newSlUsed) ? (newSlUsed - origSlUsed) : 0;

        var unit = 0.50;
        var maxUsableSl = Math.max(0, Math.floor(origSlRem / unit) * unit);
        var maxUsablePl = Math.max(0, Math.floor(origPlRem / unit) * unit);

        var finalPlRem = origPlRem;
        var finalSlRem = origSlRem;
        var spilloverSummary = '';
        var totalLwpAddition = 0;

        // Check Sick Used change
        if (deltaSl > 0) {
            if (deltaSl > maxUsableSl) {
                var takeSl = maxUsableSl;
                var shortfall = deltaSl - takeSl;
                finalSlRem = Math.max(0, origSlRem - takeSl);

                var takePl = Math.min(shortfall, maxUsablePl);
                var remShortfall = shortfall - takePl;
                finalPlRem = Math.max(0, origPlRem - takePl);
                totalLwpAddition += remShortfall;

                spilloverSummary = 'SL absorbs ' + takeSl.toFixed(2) + ' day(s) (' + finalSlRem.toFixed(2) + ' left). ' +
                    takePl.toFixed(2) + ' day(s) auto-adjusted from PL (' + finalPlRem.toFixed(2) + ' left)' +
                    (remShortfall > 0 ? ', and ' + remShortfall.toFixed(2) + ' day(s) moved to LWP.' : '.');
            } else {
                finalSlRem = Math.max(0, origSlRem - deltaSl);
            }
        } else if (deltaSl < 0) {
            finalSlRem = origSlRem - deltaSl;
        }

        // Check Paid Used change
        if (deltaPl > 0) {
            if (deltaPl > maxUsablePl) {
                var takePl = maxUsablePl;
                var shortfall = deltaPl - takePl;
                finalPlRem = Math.max(0, origPlRem - takePl);

                var takeSl = Math.min(shortfall, maxUsableSl);
                var remShortfall = shortfall - takeSl;
                finalSlRem = Math.max(0, origSlRem - takeSl);
                totalLwpAddition += remShortfall;

                spilloverSummary = 'PL absorbs ' + takePl.toFixed(2) + ' day(s) (' + finalPlRem.toFixed(2) + ' left). ' +
                    takeSl.toFixed(2) + ' day(s) auto-adjusted from SL (' + finalSlRem.toFixed(2) + ' left)' +
                    (remShortfall > 0 ? ', and ' + remShortfall.toFixed(2) + ' day(s) moved to LWP.' : '.');
            } else {
                finalPlRem = Math.max(0, origPlRem - deltaPl);
            }
        } else if (deltaPl < 0) {
            finalPlRem = origPlRem - deltaPl;
        }

        $('#edit_paid_remaining').val(finalPlRem.toFixed(2));
        $('#edit_sick_remaining').val(finalSlRem.toFixed(2));
        $('#edit_lwp_used').val((origLwpUsed + totalLwpAddition).toFixed(2));

        if (spilloverSummary !== '') {
            var fullSpilloverReason = spilloverSummary;
            $('#editSpilloverNotice').html('<div class="alert alert-info py-2 px-3 mb-0" style="font-size:11.5px; border-radius:10px; background-color:#eff6ff; border:1px solid #bfdbfe; color:#1e40af;"><i class="fas fa-info-circle mr-1 text-primary"></i> ' + spilloverSummary + '</div>').show();
            updateAllocationAutoReason(fullSpilloverReason);
        } else {
            $('#editSpilloverNotice').hide().empty();
            updateAllocationAutoReason();
        }
    }

    // Live synchronization between Used leaves and Available Targets in Edit Modal
    $(document).on('input', '#edit_paid_used, #edit_sick_used', function () {
        syncLiveSpillover();
    });

    $(document).on('input', '#edit_paid_remaining', function () {
        var $form = $('#editAllocationForm');
        if (!$form.data('is-ledger-backed')) return;
        $('#edit_pl_target_edited').val(1);
        var origUsed = parseFloat($form.data('orig-paid-used')) || 0;
        var origRem = parseFloat($form.data('orig-paid-rem')) || 0;
        var val = $(this).val();
        if (val === '' || isNaN(parseFloat(val))) return;
        var newRem = parseFloat(val);
        var deltaRem = newRem - origRem;
        var newUsed = Math.max(0, origUsed - deltaRem);
        $('#edit_paid_used').val(newUsed.toFixed(2));
        updateAllocationAutoReason();
    });

    $(document).on('input', '#edit_sick_remaining', function () {
        var $form = $('#editAllocationForm');
        if (!$form.data('is-ledger-backed')) return;
        $('#edit_sl_target_edited').val(1);
        var origUsed = parseFloat($form.data('orig-sick-used')) || 0;
        var origRem = parseFloat($form.data('orig-sick-rem')) || 0;
        var val = $(this).val();
        if (val === '' || isNaN(parseFloat(val))) return;
        var newRem = parseFloat(val);
        var deltaRem = newRem - origRem;
        var newUsed = Math.max(0, origUsed - deltaRem);
        $('#edit_sick_used').val(newUsed.toFixed(2));
        updateAllocationAutoReason();
    });

    $(document).on('input', '#edit_comp_off_allocated', function () {
        var $form = $('#editAllocationForm');
        var target = parseFloat($(this).val()) || 0;
        var original = parseFloat($form.data('orig-comp-off-allocated')) || 0;
        $('#edit_comp_off_reason').prop('required', Math.abs(target - original) > 0.000001);
    });

    $(document).on('input', '#edit_comp_off_used', function () {
        var alloc = parseFloat($('#edit_comp_off_allocated').val()) || 0;
        var used = parseFloat($('#edit_comp_off_used').val()) || 0;
        var rem = Math.max(0, alloc - used);
        $('#edit_comp_off_remaining').val(rem.toFixed(2));
    });

    // Recalculate From Policy using authoritative backend calculation endpoint
    $(document).on('click', '#btnRecalculateAllocation', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var employeeId = $('#edit_employee_id').val();
        var policyId = $('#edit_policy_id').val();
        var stage = $('#edit_employment_stage').val();
        var fromDate = $('#edit_allocation_from_date').val();
        var toDate = $('#edit_allocation_to_date').val();

        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Recalculating...');

        $.ajax({
            url: "{{ route('leave-allocations.calculate-quota') }}",
            type: "GET",
            data: {
                employee_id: employeeId,
                policy_id: policyId,
                employment_stage: stage,
                allocation_from_date: fromDate,
                allocation_to_date: toDate
            },
            success: function (res) {
                if (res && res.paid_allocated !== undefined) {
                    $('#edit_paid_allocated').val(parseFloat(res.paid_allocated).toFixed(2));
                    $('#edit_sick_allocated').val(parseFloat(res.sick_allocated).toFixed(2));
                    var isNonPerm = stage.indexOf('intern') !== -1 || stage.indexOf('probation') !== -1 || stage !== 'permanent';
                    if (isNonPerm) {
                        $('#editNonPermNotice').show();
                    } else {
                        $('#editNonPermNotice').hide();
                    }
                }
            },
            error: function (xhr) {
                alert('Failed to calculate quotas: ' + (xhr.responseJSON?.message || xhr.statusText));
            },
            complete: function () {
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    });

    // Event delegation for opening Single Reusable Delete Modal
    $(document).on('click', '.btn-delete-allocation', function () {
        var $button = $(this);
        $('#delete_employee_name').text(($button.data('name') || 'Employee') + ' · ' + ($button.data('year') || ''));
        $('#deleteAllocationForm').attr('action', $button.data('action'));
        $('#delete_reason').val('');
        $('#deleteAllocationModal').modal('show');
    });
</script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.DataTable) {
            var activeTab = "{{ $activeTab }}";
            var isMonthlyTab = (activeTab === 'monthly');
            var isTransactionsTab = (activeTab === 'transactions');
            var isAllocationsTab = (activeTab === 'allocations');
            var totalTh = $('#leaveAllocationsTable thead th').length;

            var exportColumns = [];
            for (var c = 0; c < totalTh; c++) {
                if (isAllocationsTab && c === totalTh - 1) continue; // skip actions column on allocations tab
                exportColumns.push(c);
            }

            var exportFormat = {
                body: function (data, row, column, node) {
                    if (typeof data === 'string') {
                        var text = data.replace(/<br\s*\/?>/gi, '\n')
                                       .replace(/<\/div>/gi, '\n')
                                       .replace(/<\/p>/gi, '\n')
                                       .replace(/<\/span>/gi, ' ')
                                       .replace(/<[^>]+>/g, '')
                                       .replace(/&nbsp;/g, ' ')
                                       .replace(/&amp;/g, '&')
                                       .replace(/&lt;/g, '<')
                                       .replace(/&gt;/g, '>');

                        var lines = text.split('\n').map(function(l) {
                            return l.trim();
                        }).filter(function(l) {
                            return l.length > 0;
                        });

                        // Clean Employee column: strip single letter avatar fallback if present
                        if ((column === 1 || column === 2 || column === 3) && lines.length > 1) {
                            if (lines[0].length === 1) {
                                lines.shift();
                            }
                        }

                        return lines.join('\n');
                    }
                    return data;
                }
            };

            var leaveAllocTable = null;

            // Custom Filter Extension for Leave Allocations Table
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.sTableId !== 'leaveAllocationsTable' && !$(settings.nTable).hasClass('js-leave-allocations-table')) {
                    return true;
                }

                var $row = $(settings.aoData[dataIndex].nTr);
                if (!$row || !$row.length) return true;

                var searchVal = $.trim($('#filterSearch').val()).toLowerCase();
                var stageVal = $.trim($('#filterStage').val()).toLowerCase();
                var policyVal = $.trim($('#filterPolicy').val()).toLowerCase();
                var statusVal = $.trim($('#filterStatus').val()).toLowerCase();

                var rowStage = ($row.attr('data-stage') || '').toLowerCase();
                var rowPolicy = ($row.attr('data-policy') || '').toLowerCase();
                var rowStatus = ($row.attr('data-status') || '').toLowerCase();
                var rowSearch = ($row.attr('data-search') || $row.text()).toLowerCase();

                // 1. Text Search Filter (name, code, year)
                if (searchVal && rowSearch.indexOf(searchVal) === -1) {
                    return false;
                }

                // 2. Employment Stage Filter
                if (stageVal && rowStage !== stageVal && rowStage.indexOf(stageVal) === -1) {
                    return false;
                }

                // 3. Leave Policy Filter
                if (policyVal && rowPolicy !== policyVal && rowPolicy.indexOf(policyVal) === -1) {
                    return false;
                }

                // 4. Status Filter
                if (statusVal && rowStatus !== statusVal) {
                    return false;
                }

                return true;
            });

            var hasEmptyState = $('#leaveAllocationsTable tbody .empty-state').length > 0;
            if ($.fn.DataTable.isDataTable('#leaveAllocationsTable')) {
                try {
                    $('#leaveAllocationsTable').DataTable().destroy();
                } catch (e) {}
            }

            if (!hasEmptyState && !isTransactionsTab && !isMonthlyTab) {
                try {
                    leaveAllocTable = $('#leaveAllocationsTable').DataTable({
                order: [], 
                paging: false,
                info: false,
                responsive: false,
                scrollX: false,
                autoWidth: false,
                language: {
                    emptyTable: '<div class="py-4 text-center"><i class="fas fa-folder-open fa-3x mb-3 text-muted opacity-50"></i><br>No matching records found</div>',
                    zeroRecords: '<div class="py-4 text-center"><i class="fas fa-folder-open fa-3x mb-3 text-muted opacity-50"></i><br>No matching records found</div>'
                },
                dom: 'Brt',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel mr-1 text-success"></i> Excel',
                        title: isTransactionsTab ? 'OrboOne_Leave_Transactions_Report' : (isMonthlyTab ? 'OrboOne_Monthly_Passbook_Report' : 'OrboOne_Leave_Allocation_Report'),
                        filename: (isTransactionsTab ? 'Leave_Transactions_Report_' : (isMonthlyTab ? 'Monthly_Passbook_Report_' : 'Leave_Allocation_Report_')) + new Date().toISOString().slice(0,10),
                        className: 'btn btn-sm buttons-excel',
                        exportOptions: {
                            columns: exportColumns,
                            format: exportFormat
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv mr-1 text-primary"></i> CSV',
                        title: isTransactionsTab ? 'OrboOne_Leave_Transactions_Report' : (isMonthlyTab ? 'OrboOne_Monthly_Passbook_Report' : 'OrboOne_Leave_Allocation_Report'),
                        filename: (isTransactionsTab ? 'Leave_Transactions_Report_' : (isMonthlyTab ? 'Monthly_Passbook_Report_' : 'Leave_Allocation_Report_')) + new Date().toISOString().slice(0,10),
                        className: 'btn btn-sm buttons-csv',
                        exportOptions: {
                            columns: exportColumns,
                            format: exportFormat
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf mr-1 text-danger"></i> PDF',
                        title: isTransactionsTab ? 'OrboOne - Accrual & Balance Transactions Report' : (isMonthlyTab ? 'OrboOne - Monthly Passbook Report' : 'OrboOne - Leave Allocation Report'),
                        filename: (isTransactionsTab ? 'Leave_Transactions_Report_' : (isMonthlyTab ? 'Monthly_Passbook_Report_' : 'Leave_Allocation_Report_')) + new Date().toISOString().slice(0,10),
                        orientation: 'landscape',
                        pageSize: 'A4',
                        className: 'btn btn-sm buttons-pdf',
                        exportOptions: {
                            columns: exportColumns,
                            format: exportFormat
                        },
                        customize: function (doc) {
                            doc.pageMargins = [20, 25, 20, 25];
                            doc.defaultStyle.fontSize = 8;
                            doc.styles.tableHeader.fontSize = 9;
                            doc.styles.tableHeader.fillColor = '#4B00E8';
                            doc.styles.tableHeader.color = '#FFFFFF';
                            doc.styles.tableHeader.alignment = 'center';
                            doc.styles.title = {
                                color: '#4B00E8',
                                fontSize: 14,
                                alignment: 'center',
                                bold: true,
                                margin: [0, 0, 0, 10]
                            };

                            var objLayout = {};
                            objLayout['hLineWidth'] = function(i) { return 0.5; };
                            objLayout['vLineWidth'] = function(i) { return 0; };
                            objLayout['hLineColor'] = function(i) { return '#E7EAF3'; };
                            objLayout['paddingLeft'] = function(i) { return 5; };
                            objLayout['paddingRight'] = function(i) { return 5; };
                            objLayout['paddingTop'] = function(i) { return 5; };
                            objLayout['paddingBottom'] = function(i) { return 5; };
                            doc.content[1].layout = objLayout;

                            if (isTransactionsTab) {
                                doc.content[1].table.widths = ['4%', '8%', '7%', '14%', '5%', '9%', '7%', '8%', '7%', '10%', '7%', '14%'];
                            } else if (isMonthlyTab) {
                                doc.content[1].table.widths = ['5%', '13%', '22%', '12%', '12%', '12%', '12%', '12%'];
                            } else {
                                doc.content[1].table.widths = ['5%', '22%', '18%', '13%', '13%', '14%', '15%'];
                            }

                            // Align table cells
                            var tableBody = doc.content[1].table.body;
                            for (var i = 1; i < tableBody.length; i++) {
                                for (var j = 0; j < tableBody[i].length; j++) {
                                    if (isTransactionsTab) {
                                        if (j === 1 || j === 3 || j === 9 || j === 11) {
                                            tableBody[i][j].alignment = 'left';
                                        } else {
                                            tableBody[i][j].alignment = 'center';
                                        }
                                    } else if (isMonthlyTab) {
                                        if (j === 1 || j === 2) {
                                            tableBody[i][j].alignment = 'left';
                                        } else {
                                            tableBody[i][j].alignment = 'center';
                                        }
                                    } else {
                                        if (j === 1) {
                                            tableBody[i][j].alignment = 'left';
                                        } else {
                                            tableBody[i][j].alignment = 'center';
                                        }
                                    }
                                }
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1 text-secondary"></i> Print',
                        title: '',
                        className: 'btn btn-sm buttons-print',
                        exportOptions: {
                            columns: exportColumns,
                            format: exportFormat
                        },
                        customize: function (win) {
                            var doc = win.document;

                            var style = doc.createElement('style');
                            style.type = 'text/css';
                            style.innerHTML = `
                                @page {
                                    size: A4 landscape;
                                    margin: 10mm 12mm;
                                }
                                body {
                                    font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
                                    color: #101828 !important;
                                    background: #ffffff !important;
                                    margin: 0 !important;
                                    padding: 15px !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                .print-header-box {
                                    display: flex;
                                    align-items: center;
                                    justify-content: space-between;
                                    border-bottom: 2px solid #4B00E8;
                                    padding-bottom: 12px;
                                    margin-bottom: 18px;
                                }
                                .print-brand {
                                    font-size: 22px;
                                    font-weight: 900;
                                    color: #4B00E8;
                                    letter-spacing: -0.5px;
                                }
                                .print-subbrand {
                                    font-size: 12px;
                                    color: #667085;
                                    font-weight: 700;
                                }
                                .print-title-right {
                                    text-align: right;
                                }
                                .print-title {
                                    font-size: 18px;
                                    font-weight: 900;
                                    color: #101828;
                                }
                                .print-meta {
                                    font-size: 11px;
                                    color: #667085;
                                    margin-top: 2px;
                                }
                                table.print-table {
                                    width: 100% !important;
                                    border-collapse: collapse !important;
                                    margin-top: 10px !important;
                                    font-size: 11px !important;
                                }
                                table.print-table th {
                                    background-color: #4B00E8 !important;
                                    color: #ffffff !important;
                                    font-size: 11px !important;
                                    font-weight: 800 !important;
                                    text-transform: uppercase !important;
                                    letter-spacing: 0.5px !important;
                                    padding: 10px 12px !important;
                                    border: 1px solid #4B00E8 !important;
                                    text-align: center !important;
                                    vertical-align: middle !important;
                                }
                                table.print-table th:nth-child(2),
                                table.print-table th:nth-child(3) {
                                    text-align: left !important;
                                }
                                table.print-table td {
                                    padding: 8px 10px !important;
                                    border: 1px solid #E7EAF3 !important;
                                    vertical-align: middle !important;
                                    text-align: center !important;
                                    line-height: 1.5 !important;
                                    white-space: pre-line !important;
                                }
                                table.print-table td:nth-child(2),
                                table.print-table td:nth-child(3) {
                                    text-align: left !important;
                                }
                                table.print-table tr:nth-child(even) td {
                                    background-color: #F9FAFB !important;
                                }
                                .print-footer-box {
                                    margin-top: 25px;
                                    padding-top: 12px;
                                    border-top: 1px solid #E7EAF3;
                                    font-size: 10px;
                                    color: #667085;
                                    display: flex;
                                    justify-content: space-between;
                                }
                            `;
                            doc.head.appendChild(style);

                            var today = new Date();
                            var dateStr = today.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + today.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            var reportTitle = isMonthlyTab ? 'Monthly Passbook Report' : 'Leave Allocation Report';

                            var headerHtml = `
                                <div class="print-header-box">
                                    <div>
                                        <div class="print-brand">OrboOne HRMS</div>
                                        <div class="print-subbrand">Employee Leave Management System</div>
                                    </div>
                                    <div class="print-title-right">
                                        <div class="print-title">${reportTitle}</div>
                                        <div class="print-meta">Printed: ${dateStr}</div>
                                    </div>
                                </div>
                            `;

                            var footerHtml = `
                                <div class="print-footer-box">
                                    <div>Confidential - OrboOne HRMS</div>
                                    <div>Official Leave Statement</div>
                                </div>
                            `;

                            var $body = $(doc.body);
                            $body.find('h1').remove();
                            $body.prepend(headerHtml);
                            $body.append(footerHtml);

                            var $table = $body.find('table');
                            $table.addClass('print-table');
                            $table.css('width', '100%');
                        }
                    }
                ],
                initComplete: function () {
                    $('.dataTables_length').appendTo('#allocationLengthBox');
                    $('.dt-buttons').appendTo('#allocationExportButtons');
                }
            });
                } catch (err) {
                    console.warn('DataTables init safe catch:', err);
                }
            }

            function applyAllocationFilters() {
                var $form = $('#allocationFilterForm');
                if ($form.length) {
                    $form.submit();
                    return;
                }

                var selectedYear = $('#filterYear').val();
                var currentYear = "{{ $year }}";

                if (isMonthlyTab) {
                    var query = new URLSearchParams({year: selectedYear || currentYear, tab: 'monthly'});
                    var search = $.trim($('#filterSearch').val());
                    var stage = $.trim($('#filterStage').val());
                    var policy = $.trim($('#filterPolicy').val());
                    var month = $.trim($('#filterMonth').val());
                    if (month) query.set('month', month);
                    if (search) query.set('search', search);
                    if (stage) query.set('stage', stage);
                    if (policy) query.set('policy', policy);
                    window.location.href = "{{ route('leave-allocations.index') }}?" + query.toString();
                    return;
                }

                if (selectedYear && selectedYear !== currentYear) {
                    window.location.href = "{{ route('leave-allocations.index') }}?year=" + encodeURIComponent(selectedYear) + "&tab={{ $activeTab }}";
                    return;
                }

                if (leaveAllocTable) {
                    leaveAllocTable.draw();
                }
            }
            $(document).on('change', '#filterYear, #filterMonth', function() {
                applyAllocationFilters();
            });

            $(document).on('change', '#filterStage, #filterPolicy, #filterStatus', function() {
                if (isMonthlyTab || isTransactionsTab) {
                    applyAllocationFilters();
                } else if (leaveAllocTable) {
                    leaveAllocTable.draw();
                }
            });

            $(document).on('click', '#btnFilterSubmit', function(e) {
                var $form = $('#allocationFilterForm');
                if ($form.length) {
                    return; // native form submit
                }
                e.preventDefault();
                applyAllocationFilters();
            });

            $(document).on('keypress', '#filterSearch', function(e) {
                if (e.which === 13) {
                    var $form = $('#allocationFilterForm');
                    if ($form.length) {
                        return; // native form submit
                    }
                    e.preventDefault();
                    applyAllocationFilters();
                }
            });

            $(document).on('click', '#resetFilter', function(e) {
                if ($(this).is('a') && $(this).attr('href')) {
                    return;
                }
                e.preventDefault();
                window.location.href = "{{ route('leave-allocations.index') }}?year={{ date('Y') }}&tab={{ $activeTab }}";
            });
        }

        if (window.jQuery && $.fn.select2) {
            $('#singleEmployeeSelect').select2({
                placeholder: 'Select an employee...',
                allowClear: false,
                width: '100%'
            });
        }

        if (window.jQuery && $.fn.tooltip) {
            $('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip({ html: true });
        }

    });

    function triggerLeaveExport(type) {
        if ($.fn.DataTable.isDataTable('#leaveAllocationsTable')) {
            let table = $('#leaveAllocationsTable').DataTable();

            let buttons = {
                csv: '.buttons-csv',
                excel: '.buttons-excel',
                pdf: '.buttons-pdf',
                print: '.buttons-print'
            };

            if (buttons[type]) {
                table.button(buttons[type]).trigger();
            }
        } else if ($.fn.DataTable.isDataTable('.js-datatable')) {
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
        } else {
            alert('No records available to export.');
        }
    }
</script>
@endsection
