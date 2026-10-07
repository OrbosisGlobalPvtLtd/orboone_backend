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

    #btnFilterSubmit {
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

    #btnFilterSubmit:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.35);
    }

    #resetFilter {
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

    #resetFilter:hover {
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

    .dt-buttons {
        display: inline-flex !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
        align-items: center !important;
    }

    .dt-buttons .dt-button,
    .dt-buttons .btn {
        height: 36px !important;
        padding: 0 14px !important;
        border-radius: 10px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        border: 1.5px solid var(--leave-border) !important;
        background: #fff !important;
        color: var(--leave-text) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
    }

    .dt-buttons .dt-button:hover,
    .dt-buttons .btn:hover {
        background: var(--leave-soft) !important;
        border-color: rgba(75, 0, 232, 0.3) !important;
        color: var(--leave-primary) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 10px rgba(75, 0, 232, 0.12) !important;
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

        #btnFilterSubmit, #resetFilter {
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

    <div class="orb-table-card">
        <div class="orb-table-head">
            <div class="orb-table-title-wrap">
                <div class="orb-table-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <h3 class="m-0">Allocation Records</h3>
                    <p class="m-0 text-muted" style="font-size: 12.5px;">View allocated, used, remaining and LWP leave balances by employee.</p>
                </div>
            </div>

            <div id="allocationExportButtons" class="d-flex align-items-center gap-2"></div>
        </div>

        @include('hrms.leave.allocations.partials.filter-bar')
        @include('hrms.leave.allocations.partials.allocation-table')
    </div>

    @if($canManageAllocations ?? false)
        @include('hrms.leave.allocations.partials.edit-modal')
        @include('hrms.leave.allocations.partials.delete-modal')
    @endif

</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.DataTable) {
            var exportColumns = [0, 1, 2, 3, 4, 5, 6];

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

                        // Clean Employee column (col 1): strip single letter avatar fallback if present
                        if (column === 1 && lines.length > 1) {
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

            if ($.fn.DataTable.isDataTable('#leaveAllocationsTable')) {
                $('#leaveAllocationsTable').DataTable().destroy();
            }

            leaveAllocTable = $('#leaveAllocationsTable').DataTable({
                order: [], // Preserve server-side database sort order
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
                        title: 'OrboOne_Leave_Allocation_Report',
                        filename: 'Leave_Allocation_Report_' + new Date().toISOString().slice(0,10),
                        className: 'btn btn-sm',
                        exportOptions: {
                            columns: exportColumns,
                            format: exportFormat
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv mr-1 text-primary"></i> CSV',
                        title: 'OrboOne_Leave_Allocation_Report',
                        filename: 'Leave_Allocation_Report_' + new Date().toISOString().slice(0,10),
                        className: 'btn btn-sm',
                        exportOptions: {
                            columns: exportColumns,
                            format: exportFormat
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf mr-1 text-danger"></i> PDF',
                        title: 'OrboOne - Leave Allocation Report',
                        filename: 'Leave_Allocation_Report_' + new Date().toISOString().slice(0,10),
                        orientation: 'landscape',
                        pageSize: 'A4',
                        className: 'btn btn-sm',
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

                            doc.content[1].table.widths = ['5%', '22%', '18%', '13%', '13%', '14%', '15%'];

                            // Align table cells
                            var tableBody = doc.content[1].table.body;
                            for (var i = 1; i < tableBody.length; i++) {
                                for (var j = 0; j < tableBody[i].length; j++) {
                                    if (j === 1) {
                                        tableBody[i][j].alignment = 'left';
                                    } else {
                                        tableBody[i][j].alignment = 'center';
                                    }
                                }
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1 text-secondary"></i> Print',
                        title: '',
                        className: 'btn btn-sm',
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
                                table.print-table th:nth-child(2) {
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
                                table.print-table td:nth-child(2) {
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

                            var headerHtml = `
                                <div class="print-header-box">
                                    <div>
                                        <div class="print-brand">OrboOne HRMS</div>
                                        <div class="print-subbrand">Employee Leave Management System</div>
                                    </div>
                                    <div class="print-title-right">
                                        <div class="print-title">Leave Allocation Report</div>
                                        <div class="print-meta">Printed: ${dateStr}</div>
                                    </div>
                                </div>
                            `;

                            var footerHtml = `
                                <div class="print-footer-box">
                                    <div>Confidential - OrboOne HRMS</div>
                                    <div>Official Leave Allocation Statement</div>
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

            function applyAllocationFilters() {
                var selectedYear = $('#filterYear').val();
                var currentYear = "{{ $year }}";

                if (selectedYear && selectedYear !== currentYear) {
                    window.location.href = "{{ route('leave-allocations.index') }}?year=" + encodeURIComponent(selectedYear);
                    return;
                }

                if (leaveAllocTable) {
                    leaveAllocTable.draw();
                }
            }

            $('#filterYear').off('change').on('change', function() {
                applyAllocationFilters();
            });

            $('#btnFilterSubmit').off('click').on('click', function(e) {
                e.preventDefault();
                applyAllocationFilters();
            });

            $('#filterSearch').off('keypress').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    applyAllocationFilters();
                }
            });

            $('#resetFilter').off('click').on('click', function(e) {
                e.preventDefault();
                $('#filterSearch').val('');
                $('#filterStage').val('');
                $('#filterPolicy').val('');
                $('#filterStatus').val('');
                if (leaveAllocTable) {
                    leaveAllocTable.draw();
                }
            });
        }

        if (window.jQuery && $.fn.select2) {
            $('#singleEmployeeSelect').select2({
                placeholder: 'Select an employee...',
                allowClear: false,
                width: '100%'
            });
        }

        // Live calculation update for Edit Modal
        function updateLiveBreakdown() {
            var quota = parseFloat($('#edit_monthly_quota').val()) || 0;
            var carry = parseFloat($('#edit_monthly_carry_forward').val()) || 0;
            var used = parseFloat($('#edit_monthly_used_this_month').val()) || 0;
            var paidAllocated = parseFloat($('#edit_paid_allocated').val()) || 0;
            var paidUsed = parseFloat($('#edit_paid_used').val()) || 0;
            var paidRemaining = Math.max(0, paidAllocated - paidUsed);

            // Deduct FIRST from Carry Forward
            var usedFromCarry = Math.min(used, carry);
            var remCarry = Math.max(0, carry - usedFromCarry);

            // Deduct SECOND from Monthly Quota
            var usedFromQuota = Math.max(0, used - usedFromCarry);
            var remQuota = Math.max(0, quota - usedFromQuota);

            var totalRemRaw = remCarry + remQuota;
            var totalRem = Math.min(totalRemRaw, paidRemaining);

            $('#edit_total_monthly_remaining_paid').val(totalRem.toFixed(2));
            $('#edit_rem_carry').text(remCarry.toFixed(2));
            $('#edit_rem_quota').text(remQuota.toFixed(2));
            $('#edit_next_carryover').text(totalRem.toFixed(2));
        }

        $(document).on('input change keyup', '#edit_monthly_quota, #edit_monthly_carry_forward, #edit_monthly_used_this_month, #edit_paid_allocated, #edit_paid_used', function () {
            updateLiveBreakdown();
        });

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
                        $('#edit_modal_employee_name').text(a.employee_name + ' (' + a.employee_code + ')');
                        $('#edit_year').val(a.year);
                        $('#edit_employment_stage').val(a.employment_stage);
                        $('#edit_policy_id').val(a.policy_id || '');
                        $('#edit_allocation_from_date').val(a.allocation_from_date || '');
                        $('#edit_allocation_to_date').val(a.allocation_to_date || '');
                        $('#edit_paid_allocated').val(a.paid_allocated);
                        $('#edit_sick_allocated').val(a.sick_allocated);
                        $('#edit_comp_off_allocated').val(a.comp_off_allocated);
                        $('#edit_paid_used').val(a.paid_used);
                        $('#edit_sick_used').val(a.sick_used);
                        $('#edit_comp_off_used').val(a.comp_off_used);
                        $('#edit_lwp_used').val(a.lwp_used);
                        $('#edit_monthly_quota').val(a.monthly_quota);
                        $('#edit_monthly_carry_forward').val(a.monthly_carry_forward);
                        $('#edit_monthly_used_this_month').val(a.monthly_used_this_month);
                        $('#edit_total_monthly_remaining_paid').val(a.total_monthly_remaining_paid);
                        $('#edit_allocation_reason').val(a.allocation_reason || '');
                        $('#edit_is_locked').prop('checked', !!a.is_locked);

                        if (a.is_unpaid_intern) {
                            $('#editNonPermNotice').show();
                        } else {
                            $('#editNonPermNotice').hide();
                        }

                        updateLiveBreakdown();
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
                        var mLimit = parseFloat(res.monthly_quota !== undefined ? res.monthly_quota : 2.0);
                        $('#edit_monthly_quota').val(mLimit.toFixed(2));

                        var isNonPerm = stage.indexOf('intern') !== -1 || stage.indexOf('probation') !== -1 || stage !== 'permanent';
                        if (isNonPerm) {
                            $('#edit_monthly_carry_forward').val('0.00');
                            $('#edit_total_monthly_remaining_paid').val('0.00');
                            $('#editNonPermNotice').show();
                        } else {
                            $('#editNonPermNotice').hide();
                        }
                        updateLiveBreakdown();
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
        $(document).on('click', '.btn-delete-allocation', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var action = $btn.data('action');
            var name = $btn.data('name');
            var year = $btn.data('year');
            var stage = $btn.data('stage');

            $('#deleteAllocationForm').attr('action', action);
            $('#delete_employee_name').text(name || 'Unknown Employee');
            $('#delete_year').text(year || '-');
            $('#delete_stage').text(stage || '-');

            $('#deleteAllocationModal').modal('show');
        });
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