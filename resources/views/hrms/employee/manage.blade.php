@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Manage Employee')

@section('_head')
@include('hrms.employee.partials.styles')
@endsection

@section('_content')
<style>
    :root {

        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, 0.07);
    }

    .em-page {
        min-height: calc(100vh - 90px) !important;
        padding: 24px !important;
        background: var(--orb-bg) !important;
    }

    .em-container {
        max-width: 1280px !important;
        margin: 0 auto !important;
    }

    .ev-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #ffffff !important;
        border: 0 !important;
        border-radius: 26px !important;
        box-shadow: var(--orb-shadow) !important;
        padding: 24px 28px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        margin-bottom: 20px !important;
    }

    .ev-user {
        display: flex !important;
        align-items: center !important;
        gap: 16px !important;
    }

    .ev-avatar {
        width: 74px !important;
        height: 74px !important;
        border-radius: 50% !important;
        background: rgba(255, 255, 255, 0.15) !important;
        color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 28px !important;
        font-weight: 900 !important;
        overflow: hidden !important;
        border: 3px solid rgba(255, 255, 255, 0.25) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }

    .ev-avatar img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
    }

    .ev-title {
        margin: 0 !important;
        color: #ffffff !important;
        font-size: 24px !important;
        font-weight: 900 !important;
    }

    .ev-sub {
        margin: 6px 0 0 !important;
        color: rgba(255, 255, 255, 0.8) !important;
        font-size: 13px !important;
        font-weight: 500 !important;
    }

    .ev-actions {
        display: flex !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
        align-items: center !important;
    }

    /* Header action buttons */
    .ev-btn-back,
    .ev-btn-edit,
    .ev-btn-save {
        height: 40px !important;
        min-height: 40px !important;
        border-radius: 50px !important;
        padding: 0 20px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        text-decoration: none !important;
        transition: all 0.2s ease-in-out !important;
        cursor: pointer !important;
    }

    .ev-btn-back {
        background: rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    }

    .ev-btn-back:hover {
        background: rgba(255, 255, 255, 0.25) !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
        text-decoration: none !important;
    }

    .ev-btn-edit {
        background: #ffffff !important;
        color: var(--orb-primary) !important;
        border: 0 !important;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.2) !important;
    }

    .ev-btn-edit:hover {
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 18px rgba(75, 0, 232, 0.3) !important;
        text-decoration: none !important;
    }

    .ev-btn-save {
        background: #10B981 !important;
        color: #ffffff !important;
        border: 0 !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2) !important;
    }

    .ev-btn-save:hover {
        background: #059669 !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.3) !important;
        text-decoration: none !important;
    }

    /* Glass Status Pills inside header */
    .ev-header .ev-pill {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        border-radius: 999px !important;
        padding: 6px 12px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
        background: rgba(255, 255, 255, 0.15) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    }

    .ev-header .ev-pill-active { background: rgba(22, 163, 74, 0.3) !important; border-color: rgba(22, 163, 74, 0.4) !important; }
    .ev-header .ev-pill-inactive { background: rgba(220, 38, 38, 0.3) !important; border-color: rgba(220, 38, 38, 0.4) !important; }
    .ev-header .ev-pill-completed { background: rgba(22, 163, 74, 0.3) !important; border-color: rgba(22, 163, 74, 0.4) !important; }
    .ev-header .ev-pill-pending { background: rgba(217, 119, 6, 0.3) !important; border-color: rgba(217, 119, 6, 0.4) !important; }
    .ev-header .ev-pill-submitted { background: rgba(2, 132, 199, 0.3) !important; border-color: rgba(2, 132, 199, 0.4) !important; }
    .ev-header .ev-pill-rejected { background: rgba(220, 38, 38, 0.3) !important; border-color: rgba(220, 38, 38, 0.4) !important; }
    .ev-header .ev-pill-default { background: rgba(255, 255, 255, 0.1) !important; border-color: rgba(255, 255, 255, 0.15) !important; }

    .em-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        align-items: start;
    }

    .em-card {
        background: var(--orb-card);
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .em-card-full {
        grid-column: 1/-1;
    }

    .em-card-head {
        padding: 15px 16px;
        border-bottom: 1px solid #EEF1F6;
        background: linear-gradient(135deg, #FCFCFD, #F7F4FF);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }

    .em-card-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 16px;
        font-weight: 900;
    }

    .em-card-title i {
        color: var(--orb-primary);
    }

    .em-card-sub {
        margin-top: 3px;
        color: var(--orb-muted);
        font-size: 12px;
        font-weight: 750;
    }

    .em-card-body {
        padding: 16px;
    }

    .em-section {
        padding: 14px;
        border: 1px solid #EEF1F6;
        border-radius: 18px;
        background: #fff;
        margin-bottom: 12px;
    }

    .em-section:last-child {
        margin-bottom: 0;
    }

    .em-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 12px;
        color: var(--orb-primary);
        font-size: 13px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .em-section-title i {
        width: 30px;
        height: 30px;
        border-radius: 11px;
        background: var(--orb-soft);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .em-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .em-form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .em-field label {
        display: block;
        margin: 0 0 6px;
        color: var(--orb-muted);
        font-size: 10.5px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .em-control {
        width: 100%;
        min-height: 42px;
        border-radius: 13px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 800;
        padding: 8px 12px;
    }

    textarea.em-control {
        height: 92px;
        resize: vertical;
    }

    .em-control[readonly],
    .em-control:disabled {
        background: #fff;
        color: #344054;
        opacity: 1;
        pointer-events: none;
    }

    body.edit-mode .em-control {
        background: #F9FAFB;
    }

    body.edit-mode .em-control:not([readonly]):not(:disabled):focus {
        outline: none;
        border-color: rgba(75, 0, 232, .45);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08);
        background: #fff;
    }

    .em-error {
        color: #DC2626;
        font-size: 11px;
        font-weight: 800;
        margin-top: 5px;
    }

    .em-hidden {
        display: none !important;
    }

    .em-file-view-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px;
        border: 1px solid #EEF1F6;
        border-radius: 15px;
        background: #F8FAFC;
        margin-top: 8px;
    }

    .em-file-view-box span {
        font-size: 12px;
        font-weight: 900;
        color: var(--orb-muted);
    }

    .em-file-view-box a {
        font-size: 12px;
        font-weight: 900;
        color: var(--orb-primary);
        text-decoration: none;
    }

    .em-upload-control {
        display: none;
        margin-top: 8px;
    }

    body.edit-mode .em-upload-control {
        display: block;
    }

    .em-upload-label {
        width: 100%;
        min-height: 76px;
        border-radius: 16px;
        border: 1px dashed rgba(75, 0, 232, .35);
        background: linear-gradient(180deg, #fff, #F8F5FF);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        cursor: pointer;
        margin: 0;
    }

    .em-upload-label input {
        display: none;
    }

    .em-upload-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: #F4F2FF;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .em-upload-text strong {
        display: block;
        font-size: 13px;
        font-weight: 950;
        color: var(--orb-primary);
    }

    .em-upload-text small {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        font-weight: 800;
        color: var(--orb-muted);
    }

    .em-doc-table-wrap {
        overflow-x: auto;
        margin-top: 10px;
    }

    .em-doc-table {
        width: 100%;
        min-width: 760px;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .em-doc-table th {
        color: var(--orb-muted);
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        padding: 0 10px 4px;
        border: 0;
    }

    .em-doc-table td {
        background: #FCFCFD;
        border-top: 1px solid #EEF1F6;
        border-bottom: 1px solid #EEF1F6;
        padding: 12px 10px;
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-text);
        vertical-align: middle;
    }

    .em-doc-table td:first-child {
        border-left: 1px solid #EEF1F6;
        border-radius: 14px 0 0 14px;
    }

    .em-doc-table td:last-child {
        border-right: 1px solid #EEF1F6;
        border-radius: 0 14px 14px 0;
        text-align: right;
    }

    .em-doc-name {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .em-doc-icon {
        width: 38px;
        height: 38px;
        border-radius: 13px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .em-doc-title {
        font-weight: 950;
        color: var(--orb-text);
    }

    .em-doc-sub {
        font-size: 11px;
        font-weight: 750;
        color: var(--orb-muted);
        margin-top: 2px;
    }

    .em-doc-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .em-doc-required {
        background: #FFF7ED;
        color: #C2410C;
    }

    .em-doc-optional {
        background: #F1F5F9;
        color: #475569;
    }

    .em-doc-verified {
        background: #DCFCE7;
        color: #166534;
    }

    .em-doc-pending {
        background: #E0F2FE;
        color: #0369A1;
    }

    .em-doc-rejected {
        background: #FEE2E2;
        color: #991B1B;
    }

    .em-doc-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .em-doc-view {
        min-height: 34px;
        border-radius: 11px;
        padding: 7px 10px;
        background: #F4F2FF;
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .14);
        font-size: 12px;
        font-weight: 950;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .em-reupload-label {
        min-height: 34px;
        border-radius: 11px;
        padding: 7px 10px;
        background: #E0F2FE;
        color: #0369A1;
        border: 0;
        font-size: 12px;
        font-weight: 950;
        display: none;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        margin: 0;
    }

    body.edit-mode .em-reupload-label {
        display: inline-flex;
    }

    .em-reupload-label input {
        display: none;
    }

    .em-reupload-label.is-uploading {
        opacity: .75;
        pointer-events: none;
    }

    .em-reupload-label.is-uploading i {
        animation: docSpin .8s linear infinite;
    }

    .em-reupload-label.is-uploading i:before {
        content: "\f110";
    }

    @keyframes docSpin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .salary-table-wrap {
        overflow-x: auto;
    }

    .salary-table {
        width: 100%;
        min-width: 860px;
        margin: 0;
    }

    .salary-table th {
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .4px;
        border-bottom: 1px solid var(--orb-border);
        padding: 11px 12px;
        white-space: nowrap;
    }

    .salary-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #F1F3F8;
        font-size: 13px;
        font-weight: 700;
        color: #344054;
        vertical-align: middle;
    }

    .salary-pill {
        display: inline-flex;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .salary-active {
        background: #DCFCE7;
        color: #166534;
    }

    .salary-closed {
        background: #F2F4F7;
        color: #667085;
    }

    .salary-type {
        background: #F4F2FF;
        color: var(--orb-primary);
    }

    .empty-history {
        padding: 22px;
        text-align: center;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 800;
    }

    @media(max-width:1100px) {
        .em-layout {
            grid-template-columns: 1fr;
        }

        .em-form-grid-3 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:768px) {
        .em-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .em-form-grid,
        .em-form-grid-3 {
            grid-template-columns: 1fr;
        }

        .em-actions,
        .em-btn {
            width: 100%;
        }
    }

    @media(max-width:575px) {
        .em-page {
            padding: 10px 8px 24px;
        }

        .em-user {
            align-items: flex-start;
        }

        .em-title {
            font-size: 21px;
        }
    }

    /* Section-based editing UI adjustments */
    .em-card {
        transition: all 0.3s ease;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08);
    }
    
    .em-card.is-editing {
        border-color: var(--orb-primary);
        box-shadow: 0 8px 30px rgba(75, 0, 232, 0.1);
        transform: translateY(-2px);
    }
    
    .em-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    
    .card-header-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    /* Document grid layout styles */
    .em-doc-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    @media (min-width: 992px) {
        .em-doc-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .em-doc-item-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 16px;
        transition: all 0.3s ease;
        position: relative;
    }

    .em-doc-item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(148, 163, 184, 0.12);
        border-color: rgba(75, 0, 232, 0.2);
    }

    .em-doc-item-main {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .em-doc-item-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .em-doc-item-details {
        flex-grow: 1;
        min-width: 0;
    }

    .em-doc-item-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 4px;
    }

    .em-doc-item-title {
        font-size: 15px;
        font-weight: 750;
        color: var(--orb-text);
    }

    .em-doc-item-filename {
        font-size: 13px;
        color: var(--orb-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 8px;
    }

    .em-doc-item-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .em-doc-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 750;
    }

    .em-doc-item-date {
        font-size: 11px;
        color: var(--orb-muted);
        font-weight: 500;
    }

    .em-doc-item-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        border-top: 1px solid #F1F5F9;
        padding-top: 16px;
    }

    .btn-doc-action {
        flex: 1;
        min-height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 750;
        cursor: pointer;
        text-decoration: none !important;
        transition: all 0.2s ease;
        margin: 0;
        border: none;
    }

    /* Edit = outline primary */
    .edit-sec-btn {
        background: transparent !important;
        color: var(--orb-primary) !important;
        border: 1px solid var(--orb-primary) !important;
        font-weight: 750 !important;
        border-radius: 10px !important;
        padding: 6px 14px !important;
        transition: all 0.2s ease !important;
    }

    .edit-sec-btn:hover {
        background: rgba(75, 0, 232, 0.05) !important;
    }

    /* Save = gradient primary */
    .save-sec-btn {
        background: var(--orb-gradient) !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 750 !important;
        border-radius: 10px !important;
        padding: 6px 14px !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2) !important;
        transition: all 0.2s ease !important;
    }

    .save-sec-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.3) !important;
    }

    /* Cancel = light gray */
    .cancel-sec-btn {
        background: #F1F5F9 !important;
        color: #64748B !important;
        border: 1px solid #E2E8F0 !important;
        font-weight: 750 !important;
        border-radius: 10px !important;
        padding: 6px 14px !important;
        transition: all 0.2s ease !important;
    }

    .cancel-sec-btn:hover {
        background: #E2E8F0 !important;
        color: #334155 !important;
    }

    /* View = soft info button */
    .btn-doc-view {
        background: #E0F2FE;
        color: #0369A1;
    }

    .btn-doc-view:hover {
        background: #BAE6FD;
        color: #0369A1;
    }

    /* Reupload = warning soft button */
    .btn-doc-reupload {
        background: #FEF3C7;
        color: #D97706;
        position: relative;
        overflow: hidden;
    }

    .btn-doc-reupload:hover {
        background: #FDE68A;
        color: #B45309;
    }

    .btn-doc-reupload input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    /* Premium Toast alerts styles */
    #orb-toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .orb-toast {
        background: #ffffff;
        border-radius: 16px;
        padding: 16px 20px;
        box-shadow: 0 10px 30px rgba(16, 24, 40, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-width: 320px;
        max-width: 450px;
        border-left: 5px solid var(--orb-primary);
        animation: toastIn 0.3s ease forwards;
        transition: all 0.3s ease;
    }

    .orb-toast-success {
        border-left-color: #10B981;
    }

    .orb-toast-error {
        border-left-color: #EF4444;
    }

    .orb-toast-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .orb-toast-icon {
        font-size: 20px;
    }

    .orb-toast-success .orb-toast-icon {
        color: #10B981;
    }

    .orb-toast-error .orb-toast-icon {
        color: #EF4444;
    }

    .orb-toast-text {
        font-size: 14px;
        font-weight: 700;
        color: var(--orb-text);
    }

    .orb-toast-close {
        background: none;
        border: none;
        color: var(--orb-muted);
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }

    .orb-toast-close:hover {
        color: var(--orb-text);
    }

    @keyframes toastIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .manage-action-btn {
      height: 40px;
      border-radius: 12px;
      padding: 0 16px;
      font-size: 13px;
      font-weight: 900;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      text-decoration: none !important;
    }

    .manage-btn-cancel {
      background: #fff !important;
      color: #344054 !important;
      border: 1px solid #E7EAF3 !important;
      box-shadow: 0 8px 18px rgba(16,24,40,.06) !important;
    }

    .manage-btn-save {
      background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
      color: #fff !important;
      border: 0 !important;
      box-shadow: 0 10px 24px rgba(75,0,232,.24) !important;
    }

    .manage-btn-save:hover,
    .manage-btn-save:focus,
    .manage-btn-save:active {
      background: linear-gradient(135deg,#3F00C8,#7600D6) !important;
      color: #fff !important;
    }

    .manage-btn-save:disabled {
      opacity: .6 !important;
      color: #fff !important;
      cursor: not-allowed !important;
    }

    .btn-doc-edit-pill {
        background: #EEF2FF !important;
        color: #4F46E5 !important;
        border: 1px solid #C7D2FE !important;
        border-radius: 20px !important;
        padding: 6px 14px !important;
        font-weight: 750 !important;
    }

    .btn-doc-edit-pill:hover {
        background: #E0E7FF !important;
        color: #4338CA !important;
    }

    .btn-doc-download {
        background: #ECFDF5 !important;
        color: #059669 !important;
        border: 1px solid #A7F3D0 !important;
        border-radius: 20px !important;
        padding: 6px 14px !important;
        font-weight: 750 !important;
    }

    .btn-doc-download:hover {
        background: #D1FAE5 !important;
        color: #047857 !important;
    }

    .btn-doc-verify {
        background: #ECFDF5 !important;
        color: #10B981 !important;
        border: 1px solid #10B981 !important;
        border-radius: 20px !important;
        padding: 6px 14px !important;
        font-weight: 750 !important;
    }

    .btn-doc-verify:hover {
        background: #D1FAE5 !important;
    }

    .btn-doc-reject {
        background: #FEF2F2 !important;
        color: #EF4444 !important;
        border: 1px solid #EF4444 !important;
        border-radius: 20px !important;
        padding: 6px 14px !important;
        font-weight: 750 !important;
    }

    .btn-doc-reject:hover {
        background: #FEE2E2 !important;
    }
</style>

@php
$name = $employeeData->name ?? 'Employee';
$initial = strtoupper(substr($name, 0, 1));
$isCompleted = (int) ($employeeData->is_profile_completed ?? 0) === 1;

$employmentStatus = strtolower($employeeData->employment_status ?? 'active');
$profileStatus = strtolower($employeeData->profile_status ?? 'pending');
$stage = strtolower($employeeData->employee_stage ?? 'probation');
$isPermanent = (int) ($employeeData->is_permanent ?? 0) === 1 || $stage === 'permanent';

$employmentBadgeClass = match ($employmentStatus) {
'active' => 'em-badge-success',
'resigned' => 'em-badge-warning',
'terminated', 'inactive' => 'em-badge-danger',
default => '',
};

$profileBadgeClass = match ($profileStatus) {
'approved' => 'em-badge-success',
'submitted' => 'em-badge-info',
'rejected' => 'em-badge-danger',
default => 'em-badge-warning',
};

$stageBadgeClass = match ($stage) {
'internship' => 'em-badge-info',
'probation' => 'em-badge-warning',
'permanent' => 'em-badge-success',
'contract', 'freelance' => 'em-badge-info',
default => '',
};

$internshipStatus = strtolower($employeeData->internship_status ?? '');
$approvedAt = !empty($employeeData->approved_at) ? \Carbon\Carbon::parse($employeeData->approved_at)->format('d M Y') : null;

$fileUrl = function ($path) {
return !empty($path) && Route::has('hrms.documents.file')
? route('hrms.documents.file', $path)
: '#';
};

$employeeDocuments = $employeeDocuments ?? collect();

$user = auth()->user();
$canSeeSalary = false;
if ($user) {
    $canSeeSalary = $user->hasRole('super_admin') 
        || $user->hasRole('Super Admin') 
        || $user->hasRole('hr_admin') 
        || $user->hasRole('hr') 
        || $user->hasRole('admin') 
        || $user->hasRole('finance_admin')
        || $user->can('hrms.employees.salary')
        || $user->can('employees.salary')
        || $user->can('salary.view')
        || $user->can('payroll.view')
        || $user->can('employees.edit')
        || $user->can('hrms.employees.edit');
}
@endphp

<div class="em-page">
    <div class="em-container">

        @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('hrms.employees.manage.update', $employeeData->id) }}"
            enctype="multipart/form-data" id="employeeManageForm">
            @csrf
            @method('PUT')

            <div class="ev-header">
                <div class="ev-user">
                    @php
                        $passportPhotoUrl = resolveEmployeePassportPhoto($employeeData);
                        $employeeInitial = $initial;
                        $employeeName = $employeeData->name ?? 'Employee';
                    @endphp
                    <span class="hrms-emp-avatar mr-3">
                        @if($passportPhotoUrl)
                            <img
                                src="{{ $passportPhotoUrl }}"
                                alt="{{ $employeeName }}"
                                class="hrms-emp-avatar-img"
                                onerror="this.style.display='none'; this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.remove('is-hidden'); this.parentElement.querySelector('.hrms-emp-avatar-fallback').classList.add('is-visible');"
                            >
                            <span class="hrms-emp-avatar-fallback is-hidden">
                                {{ $employeeInitial }}
                            </span>
                        @else
                            <span class="hrms-emp-avatar-fallback is-visible">
                                {{ $employeeInitial }}
                            </span>
                        @endif
                    </span>

                    <div>
                        <h1 class="ev-title">{{ $employeeData->name ?? 'Employee' }}</h1>
                        <p class="ev-sub">
                            {{ $employeeData->employee_code ?? '-' }}
                            · {{ $employeeData->department_name ?? 'No Department' }}
                            · {{ $employeeData->designation_name ?? 'No Designation' }}
                        </p>

                        <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                            <span class="ev-pill {{ $employmentStatus === 'active' ? 'ev-pill-active' : 'ev-pill-inactive' }}">
                                <i class="fas fa-circle mr-1" style="font-size: 8px;"></i>{{ ucfirst($employmentStatus) }}
                            </span>
                            <span class="ev-pill ev-pill-default">
                                <i class="fas fa-layer-group mr-1"></i>{{ ucfirst(str_replace('_', ' ', $stage)) }}
                            </span>
                            <span class="ev-pill {{ $profileStatus === 'approved' ? 'ev-pill-completed' : ($profileStatus === 'submitted' ? 'ev-pill-submitted' : ($profileStatus === 'rejected' ? 'ev-pill-rejected' : 'ev-pill-pending')) }}">
                                <i class="fas fa-id-card mr-1"></i>{{ $isCompleted ? 'Profile Approved' : ucfirst($profileStatus) }}
                            </span>
                            @if ($isPermanent)
                            <span class="ev-pill ev-pill-completed">
                                <i class="fas fa-user-check mr-1"></i>Permanent
                            </span>
                            @endif
                            <span class="ev-pill ev-pill-default">
                                <i class="fas fa-briefcase mr-1"></i>{{ strtoupper($employeeData->work_mode ?? '-') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="ev-actions">
                    <a href="{{ route('hrms.employees.index') }}" class="ev-btn-back">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="em-layout">
                <div class="em-card" id="cardA">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-user-tie mr-2"></i>Employee Details</h5>
                            <div class="em-card-sub">Basic, job, lifecycle and salary setup</div>
                        </div>
                        <div class="card-header-actions">
                            <button type="button" class="btn edit-sec-btn" data-section="cardA"><i class="fas fa-edit mr-1"></i>Edit</button>
                            <button type="button" class="btn manage-action-btn manage-btn-cancel cancel-sec-btn" data-section="cardA" style="display: none;"><i class="fas fa-times mr-1"></i>Cancel</button>
                            <button type="button" class="btn manage-action-btn manage-btn-save save-sec-btn" data-section="cardA" style="display: none;"><i class="fas fa-save mr-1"></i>Save Changes</button>
                        </div>
                    </div>

                    <div class="em-card-body">
                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-id-badge"></i>Basic Information</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Employee Code</label>
                                    <input type="text" class="em-control" value="{{ $employeeData->employee_code ?? '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Name</label>
                                    <input type="text" name="name" class="em-control editable" value="{{ old('name', $employeeData->name) }}" readonly>
                                    @error('name') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Email</label>
                                    <input type="email" name="email" class="em-control editable" value="{{ old('email', $employeeData->email) }}" readonly>
                                    @error('email') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="em-control editable" value="{{ old('phone', $employeeData->phone) }}" readonly>
                                    @error('phone') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-building"></i>Job Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Department</label>
                                    <select name="department_id" id="department_id" class="em-control editable-select" disabled>
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $employeeData->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Designation</label>
                                    <select name="designation_id" id="designation_id" class="em-control editable-select" disabled>
                                        <option value="">Select Designation</option>
                                        @foreach ($designations as $des)
                                        <option value="{{ $des->id }}" data-department-id="{{ $des->department_id ?? '' }}" {{ old('designation_id', $employeeData->designation_id) == $des->id ? 'selected' : '' }}>
                                            {{ $des->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('designation_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Reporting Manager</label>
                                    <select name="reporting_manager_employee_id" class="em-control editable-select" disabled>
                                        <option value="">Select Manager</option>
                                        @foreach(($reportingManagers ?? collect()) as $manager)
                                        <option value="{{ $manager->id }}" {{ old('reporting_manager_employee_id', $employeeData->reporting_manager_employee_id ?? '') == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }} - {{ $manager->employee_code }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('reporting_manager_employee_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>System Role</label>
                                    <select name="system_role_id" class="em-control editable-select" disabled>
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('system_role_id', $employeeData->system_role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name ?? ($role->name ?? ($role->title ?? 'Role '.$role->id)) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('system_role_id') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-briefcase"></i>Employment & Lifecycle</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Employment Type</label>
                                    <select name="employment_type" id="employment_type" class="em-control editable-select" disabled>
                                        <option value="">Select Employment Type</option>
                                        <option value="full_time" {{ old('employment_type', $employeeData->employment_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="part_time" {{ old('employment_type', $employeeData->employment_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                        <option value="intern" {{ old('employment_type', $employeeData->employment_type) == 'intern' ? 'selected' : '' }}>Intern</option>
                                        <option value="freelancer" {{ old('employment_type', $employeeData->employment_type) == 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                                        <option value="contract" {{ old('employment_type', $employeeData->employment_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                                    </select>
                                    @error('employment_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Employee Stage</label>
                                    <input type="text" id="employee_stage_display" class="em-control" value="{{ ucfirst(str_replace('_', ' ', $stage ?: 'Auto')) }}" readonly>
                                    <input type="hidden" id="employee_stage" name="derived_employee_stage" value="{{ old('derived_employee_stage', $employeeData->employee_stage ?? '') }}">
                                </div>

                                <div class="em-field">
                                    <label>Work Mode</label>
                                    <select name="work_mode" class="em-control editable-select" disabled>
                                        <option value="">Select Work Mode</option>
                                        <option value="wfo" {{ old('work_mode', $employeeData->work_mode) == 'wfo' ? 'selected' : '' }}>WFO</option>
                                        <option value="wfh" {{ old('work_mode', $employeeData->work_mode) == 'wfh' ? 'selected' : '' }}>WFH</option>
                                        <option value="hybrid" {{ old('work_mode', $employeeData->work_mode) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    </select>
                                    @error('work_mode') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Work Schedule</label>
                                    <select name="work_schedule_type" class="em-control editable-select" disabled>
                                        <option value="">Select Schedule</option>
                                        <option value="full_day" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'full_day' ? 'selected' : '' }}>Full Day</option>
                                        <option value="part_day" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'part_day' ? 'selected' : '' }}>Part Day</option>
                                        <option value="hourly" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                        <option value="shift_based" {{ old('work_schedule_type', $employeeData->work_schedule_type ?? '') == 'shift_based' ? 'selected' : '' }}>Shift Based</option>
                                    </select>
                                    @error('work_schedule_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Employment Status</label>
                                    <select name="employment_status" class="em-control editable-select" disabled>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('employment_status', $employeeData->employment_status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="resigned" {{ old('employment_status', $employeeData->employment_status) == 'resigned' ? 'selected' : '' }}>Resigned</option>
                                        <option value="terminated" {{ old('employment_status', $employeeData->employment_status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                        <option value="inactive" {{ old('employment_status', $employeeData->employment_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('employment_status') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field non-intern-field">
                                    <label>Joining Date</label>
                                    <input type="date" name="joining_date" class="em-control editable" value="{{ old('joining_date', $employeeData->joining_date) }}" readonly>
                                    @error('joining_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Relieving Date</label>
                                    <input type="date" name="relieving_date" class="em-control editable" value="{{ old('relieving_date', $employeeData->relieving_date) }}" readonly>
                                    @error('relieving_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section probation-section">
                            <h6 class="em-section-title"><i class="fas fa-hourglass-half"></i>Probation / Permanent Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Probation Months</label>
                                    <input type="number" name="probation_months" class="em-control editable" value="{{ old('probation_months', $employeeData->probation_months) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Probation Status</label>
                                    <input type="text" name="probation_status" class="em-control editable" value="{{ old('probation_status', $employeeData->probation_status) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Probation Start</label>
                                    <input type="date" name="probation_start_date" class="em-control editable" value="{{ old('probation_start_date', $employeeData->probation_start_date) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Probation End</label>
                                    <input type="date" name="probation_end_date" class="em-control editable" value="{{ old('probation_end_date', $employeeData->probation_end_date) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Is Permanent</label>
                                    <input type="text" class="em-control" value="{{ $isPermanent ? 'Yes' : 'No' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Permanent Date</label>
                                    <input type="date" class="em-control" value="{{ $employeeData->permanent_at ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="em-section internship-section">
                            <h6 class="em-section-title"><i class="fas fa-user-graduate"></i>Internship Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Internship Start</label>
                                    <input type="date" name="internship_start_date" class="em-control editable" value="{{ old('internship_start_date', $employeeData->internship_start_date) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Internship End</label>
                                    <input type="date" name="internship_end_date" class="em-control editable" value="{{ old('internship_end_date', $employeeData->internship_end_date) }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Extended To</label>
                                    <input type="date" class="em-control" value="{{ $employeeData->internship_extended_to ?? '' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Internship Status</label>
                                    <input type="text" class="em-control" value="{{ $internshipStatus ? ucfirst(str_replace('_', ' ', $internshipStatus)) : '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Completed At</label>
                                    <input type="text" class="em-control" value="{{ !empty($employeeData->internship_completed_at) ? \Carbon\Carbon::parse($employeeData->internship_completed_at)->format('d M Y h:i A') : '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Paid Intern</label>
                                    <select name="is_paid_intern" class="em-control editable-select" disabled>
                                        <option value="">Select</option>
                                        <option value="1" {{ (string) old('is_paid_intern', $employeeData->is_paid_intern) === '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ (string) old('is_paid_intern', $employeeData->is_paid_intern) === '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="em-section contract-section">
                            <h6 class="em-section-title"><i class="fas fa-file-contract"></i>Contract / Freelance Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Contract End / Review Date</label>
                                    <input type="date" name="contract_end_date" class="em-control editable" value="{{ old('contract_end_date', $employeeData->contract_end_date ?? '') }}" readonly>
                                    @error('contract_end_date') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-money-bill-wave"></i>Current Salary Update</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Actual Salary</label>
                                    <input type="number" step="0.01" name="actual_salary" class="em-control editable" value="{{ old('actual_salary', $employeeData->actual_salary) }}" readonly>
                                    @error('actual_salary') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Salary Effective From</label>
                                    <input type="date" name="salary_effective_from" class="em-control editable" value="{{ old('salary_effective_from', now()->toDateString()) }}" readonly>
                                    @error('salary_effective_from') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field" style="grid-column:1/-1;">
                                    <label>Salary Reason</label>
                                    <input type="text" name="salary_change_reason" class="em-control editable" value="{{ old('salary_change_reason') }}" placeholder="Increment / Stage change / Correction" readonly>
                                    @error('salary_change_reason') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="em-card" id="cardB">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-id-card mr-2"></i>Profile Details</h5>
                            <div class="em-card-sub">Personal, education, experience and bank details</div>
                        </div>
                        <div class="card-header-actions">
                            <button type="button" class="btn edit-sec-btn" data-section="cardB"><i class="fas fa-edit mr-1"></i>Edit</button>
                            <button type="button" class="btn manage-action-btn manage-btn-cancel cancel-sec-btn" data-section="cardB" style="display: none;"><i class="fas fa-times mr-1"></i>Cancel</button>
                            <button type="button" class="btn manage-action-btn manage-btn-save save-sec-btn" data-section="cardB" style="display: none;"><i class="fas fa-save mr-1"></i>Save Changes</button>
                        </div>
                    </div>

                    <div class="em-card-body">
                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-user"></i>Personal Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Profile Image</label>

                                    <div class="em-file-view-box">
                                        <span>{{ !empty($employeeData->profile_image) ? 'Image uploaded' : 'No image uploaded' }}</span>
                                        @if (!empty($employeeData->profile_image))
                                        <a href="{{ $fileUrl($employeeData->profile_image) }}" target="_blank"><i class="fas fa-eye"></i> View</a>
                                        @endif
                                    </div>

                                    <div class="em-upload-control">
                                        <label class="em-upload-label">
                                            <input type="file" name="profile_image" class="editable-file" disabled accept=".jpg,.jpeg,.png,.webp">
                                            <span class="em-upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="em-upload-text">
                                                <strong>{{ !empty($employeeData->profile_image) ? 'Replace Profile Image' : 'Upload Profile Image' }}</strong>
                                                <small>JPG, PNG, WEBP supported</small>
                                            </span>
                                        </label>
                                    </div>

                                    @error('profile_image') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="em-control editable" value="{{ old('date_of_birth', $employeeData->date_of_birth) }}" readonly>
                                    @error('date_of_birth') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Gender</label>
                                    <select name="gender" class="em-control editable-select" disabled>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $employeeData->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $employeeData->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $employeeData->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Address</label>
                                    <textarea name="address" class="em-control editable" readonly>{{ old('address', $employeeData->address) }}</textarea>
                                    @error('address') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Emergency Contact Number</label>
                                    <input type="text" name="emergency_contact_number" class="em-control editable" value="{{ old('emergency_contact_number', $employeeData->emergency_contact_number) }}" readonly>
                                    @error('emergency_contact_number') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-graduation-cap"></i>Education & Experience</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Highest Qualification</label>
                                    <input type="text" name="highest_qualification" class="em-control editable" value="{{ old('highest_qualification', $employeeData->highest_qualification) }}" readonly>
                                    @error('highest_qualification') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>CGPA / Percentage</label>
                                    <input type="text" name="cgpa_percentage" class="em-control editable" value="{{ old('cgpa_percentage', $employeeData->cgpa_percentage) }}" readonly>
                                    @error('cgpa_percentage') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Experience Type</label>
                                    <select name="experience_type" id="manage_experience_type" class="em-control editable-select" disabled onchange="toggleManageExperienceFields(this.value)">
                                        <option value="">Select Experience Type</option>
                                        <option value="fresher" {{ old('experience_type', $employeeData->experience_type ?? '') == 'fresher' ? 'selected' : '' }}>Fresher</option>
                                        <option value="experienced" {{ old('experience_type', $employeeData->experience_type ?? '') == 'experienced' ? 'selected' : '' }}>Experienced</option>
                                    </select>
                                    @error('experience_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field" id="manage_total_experience_container">
                                    <label>Total Experience</label>
                                    <input type="text" name="total_experience" id="manage_total_experience" class="em-control editable" value="{{ old('total_experience', $employeeData->total_experience) }}" readonly>
                                    @error('total_experience') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field" style="grid-column:1/-1;">
                                    <label>Resume File</label>

                                    <div class="em-file-view-box">
                                        <span>{{ !empty($employeeData->resume_file) ? 'Resume uploaded' : 'No resume uploaded' }}</span>
                                        @if (!empty($employeeData->resume_file))
                                        <a href="{{ $fileUrl($employeeData->resume_file) }}" target="_blank"><i class="fas fa-eye"></i> View</a>
                                        @endif
                                    </div>

                                    <div class="em-upload-control">
                                        <label class="em-upload-label">
                                            <input type="file" name="resume_file" class="editable-file" disabled accept=".pdf,.jpg,.jpeg,.png,.webp">
                                            <span class="em-upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="em-upload-text">
                                                <strong>{{ !empty($employeeData->resume_file) ? 'Replace Resume' : 'Upload Resume' }}</strong>
                                                <small>PDF, JPG, PNG, WEBP supported</small>
                                            </span>
                                        </label>
                                    </div>

                                    @error('resume_file') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-university"></i>Bank Details</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Account Holder</label>
                                    <input type="text" name="bank_holder_name" class="em-control editable" value="{{ old('bank_holder_name', $employeeData->bank_holder_name) }}" readonly>
                                    @error('bank_holder_name') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Account Number</label>
                                    <input type="text" name="bank_account_no" class="em-control editable" value="{{ old('bank_account_no', $employeeData->bank_account_no) }}" readonly>
                                    @error('bank_account_no') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Account Type</label>
                                    <select name="bank_account_type" class="em-control editable-select" disabled>
                                        <option value="">Select Account Type</option>
                                        <option value="saving" {{ old('bank_account_type', $employeeData->bank_account_type) == 'saving' ? 'selected' : '' }}>Saving</option>
                                        <option value="savings" {{ old('bank_account_type', $employeeData->bank_account_type) == 'savings' ? 'selected' : '' }}>Savings</option>
                                        <option value="current" {{ old('bank_account_type', $employeeData->bank_account_type) == 'current' ? 'selected' : '' }}>Current</option>
                                        <option value="salary" {{ old('bank_account_type', $employeeData->bank_account_type) == 'salary' ? 'selected' : '' }}>Salary</option>
                                    </select>
                                    @error('bank_account_type') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>IFSC Code</label>
                                    <input type="text" name="ifsc_code" class="em-control editable" value="{{ old('ifsc_code', $employeeData->ifsc_code) }}" readonly>
                                    @error('ifsc_code') <div class="em-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="em-field">
                                    <label>Bank Branch</label>
                                    <input type="text" name="bank_branch" class="em-control editable" value="{{ old('bank_branch', $employeeData->bank_branch) }}" readonly>
                                    @error('bank_branch') <div class="em-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="em-section">
                            <h6 class="em-section-title"><i class="fas fa-id-card"></i>Profile Approval Status</h6>
                            <div class="em-form-grid">
                                <div class="em-field">
                                    <label>Profile Status</label>
                                    <input type="text" class="em-control" value="{{ ucfirst($employeeData->profile_status ?? 'pending') }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Profile Completed</label>
                                    <input type="text" class="em-control" value="{{ $isCompleted ? 'Yes' : 'No' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Approved At</label>
                                    <input type="text" class="em-control" value="{{ $approvedAt ?? '-' }}" readonly>
                                </div>

                                <div class="em-field">
                                    <label>Rejection Reason</label>
                                    <input type="text" class="em-control" value="{{ $employeeData->rejection_reason ?? '-' }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @if ($canSeeSalary)
            <div class="em-card em-card-full animate__animated animate__fadeInUp">
                <div class="em-card-head">
                    <div>
                        <h5 class="em-card-title"><i class="fas fa-history mr-2"></i>Salary History</h5>
                        <div class="em-card-sub">Date-wise salary/stipend records. Old records are preserved.</div>
                    </div>
                </div>

                <div class="em-card-body" style="padding: 0;">
                    <div class="salary-table-wrap">
                        @if (isset($salaryHistories) && $salaryHistories->count())
                        <table class="salary-table">
                            <thead>
                                <tr>
                                    <th>Effective Date</th>
                                    <th>Lifecycle Stage</th>
                                    <th>Salary Type</th>
                                    <th>Authorized By</th>
                                    <th>Reason / Revision Type</th>
                                    <th style="text-align: right;">Gross Salary / CTC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($salaryHistories as $history)
                                @php
                                $historyStage = $history->employment_stage ?? ($history->stage ?? '-');
                                $historyType = $history->salary_type ?? ((float) ($history->salary_amount ?? 0) <= 0 ? 'unpaid' : ($historyStage==='internship' ? 'stipend' : 'salary' ));
                                $active = isset($history->is_active) ? (int) $history->is_active === 1 : empty($history->effective_to);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold" style="color: var(--orb-text);">{{ !empty($history->effective_from) ? \Carbon\Carbon::parse($history->effective_from)->format('d M Y') : '-' }}</span>
                                            @if(!empty($history->effective_to))
                                                <span class="text-muted" style="font-size: 11px;">to {{ \Carbon\Carbon::parse($history->effective_to)->format('d M Y') }}</span>
                                            @else
                                                <span class="text-success" style="font-size: 11px; font-weight: 750;"><i class="fas fa-dot-circle" style="font-size: 8px;"></i> Active / Present</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ev-pill ev-pill-default">
                                            {{ ucfirst(str_replace('_', ' ', $historyStage)) }}
                                        </span>
                                    </td>
                                    <td><span class="salary-pill salary-type">{{ ucfirst($historyType) }}</span></td>
                                    <td>
                                        <span class="text-muted" style="font-size: 12px; font-weight: 700;">
                                            <i class="far fa-user-circle"></i> {{ $history->creator_name ?? 'System' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-weight-medium" style="color: var(--orb-text); font-size: 13px;">
                                            {{ $history->reason ?: 'Regular revision' }}
                                        </span>
                                    </td>
                                    <td style="text-align: right; font-size: 14px; font-weight: 900; color: var(--orb-primary);">
                                        ₹{{ number_format((float) ($history->salary_amount ?? 0), 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="empty-history">
                            <i class="fas fa-info-circle mr-1"></i> No salary history found.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

                <div class="em-card em-card-full animate__animated animate__fadeInUp" id="documentCard">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-folder-open mr-2"></i>Employee Documents</h5>
                            <div class="em-card-sub">Manage uploaded documents. Upload new or replace files with instant auto-save.</div>
                        </div>
                    </div>

                    <div class="em-card-body" style="padding: 24px;">
                        @if($employeeDocuments && $employeeDocuments->count() > 0)
                        <div class="em-doc-grid">
                            @foreach($employeeDocuments as $doc)
                            @php
                            $docTitle = $doc->document_type_name ?? $doc->title ?? 'Document';
                            $docStatus = strtolower($doc->verification_status ?? 'pending');
                            
                            $docStatusClass = match($docStatus) {
                                'verified' => 'em-doc-verified',
                                'rejected' => 'em-doc-rejected',
                                default => 'em-doc-pending',
                            };

                            $docStatusText = match($docStatus) {
                                'verified' => 'Verified & Locked',
                                'rejected' => 'Rejected / Needs Reupload',
                                default => 'Pending Verification',
                            };

                            $docPath = $doc->file_path ?? null;
                            $docUrl = !empty($docPath) && Route::has('hrms.documents.file')
                                ? route('hrms.documents.file', $docPath)
                                : (!empty($docPath) ? route('hrms.documents.file', ['path' => $docPath]) : null);

                            $documentTypeId = $doc->document_type_id ?? $doc->category_id ?? null;
                            $fileName = $doc->file_original_name ?? null;
                            $fileExt = strtolower(pathinfo($fileName ?? '', PATHINFO_EXTENSION));
                            
                            $iconClass = 'fa-file-alt text-primary';
                            if ($fileExt === 'pdf') {
                                $iconClass = 'fa-file-pdf text-danger';
                            } elseif (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                                $iconClass = 'fa-file-image text-success';
                            }
                            @endphp

                            <div class="em-doc-item-card em-doc-row">
                                <div class="em-doc-item-main">
                                    <div class="em-doc-item-icon-box">
                                        <i class="fas {{ $iconClass }} fa-2x"></i>
                                    </div>
                                    <div class="em-doc-item-details">
                                        <div class="em-doc-item-title-row">
                                            <span class="em-doc-item-title">{{ $docTitle }}</span>
                                            <span class="em-doc-pill {{ !empty($doc->is_required) ? 'em-doc-required' : 'em-doc-optional' }}">
                                                {{ !empty($doc->is_required) ? 'Required' : 'Optional' }}
                                            </span>
                                        </div>
                                        <div class="em-doc-item-filename" title="{{ $fileName ?? 'No file uploaded' }}">
                                            {{ $fileName ?? 'No file uploaded' }}
                                        </div>
                                        <div class="em-doc-item-meta">
                                            <span class="em-doc-badge {{ $docStatusClass }}">
                                                <i class="fas {{ $docStatus === 'verified' ? 'fa-check-circle' : ($docStatus === 'rejected' ? 'fa-times-circle' : 'fa-clock') }} mr-1"></i>
                                                {{ $docStatusText }}
                                            </span>
                                            @if(!empty($doc->uploaded_at) || !empty($doc->created_at))
                                            <span class="em-doc-item-date">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                {{ \Carbon\Carbon::parse($doc->uploaded_at ?? $doc->created_at)->format('d M Y, h:i A') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="em-doc-item-actions em-doc-actions" style="flex-wrap: wrap; gap: 8px;">
                                    @if($docUrl)
                                    <a href="{{ $docUrl }}" target="_blank" class="btn-doc-action btn-doc-view" title="View Document">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                    @endif

                                    @if($doc->id && Route::has('hrms.documents.employee.download'))
                                    <a href="{{ route('documents.employee.download', $doc->id) }}" class="btn-doc-action btn-doc-download" title="Download Document">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                    @endif

                                    @if($documentTypeId && Route::has('hrms.documents.employee.upload_from_profile') && ($docStatus !== 'verified' || auth()->user()->can('company_documents.manage')))
                                    <label class="btn-doc-action btn-doc-edit-pill btn-doc-reupload" data-doc-title="{{ $docTitle }}" title="Re-upload or Edit Document">
                                        <i class="fas fa-edit mr-1"></i>
                                        <span>Re-upload / Edit</span>

                                        <input type="file"
                                            name="file"
                                            data-action="{{ route('documents.employee.upload_from_profile', [$employeeData->id, $documentTypeId]) }}"
                                            class="js-ajax-doc-upload"
                                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                                            required>
                                    </label>
                                    @endif

                                    @if($docStatus === 'pending' && $doc->id && auth()->user()->can('company_documents.manage'))
                                    <button type="button" class="btn-doc-action btn-doc-verify js-ajax-doc-verify" data-action="{{ route('documents.employee.verify', $doc->id) }}" data-doc-title="{{ $docTitle }}" title="Verify Document">
                                        <i class="fas fa-check-circle mr-1"></i> Verify
                                    </button>
                                    <button type="button" class="btn-doc-action btn-doc-reject js-ajax-doc-reject" data-action="{{ route('documents.employee.reject', $doc->id) }}" data-doc-title="{{ $docTitle }}" title="Reject Document">
                                        <i class="fas fa-times-circle mr-1"></i> Reject
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="empty-history text-center py-5">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64' viewBox='0 0 24 24' fill='none' stroke='%23d1d5db' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z'%3E%3C/path%3E%3Cpolyline points='14 2 14 8 20 8'%3E%3C/polyline%3E%3Cline x1='16' y1='13' x2='8' y2='13'%3E%3C/line%3E%3Cline x1='16' y1='17' x2='8' y2='17'%3E%3C/line%3E%3Cpolyline points='10 9 9 9 8 9'%3E%3C/polyline%3E%3C/svg%3E" alt="No documents" class="mb-3" style="opacity: 0.5;">
                            <div class="font-weight-bold text-muted">No documents found for this employee.</div>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </form>

        @foreach($employeeDocuments as $doc)
        @php
        $documentTypeId = $doc->document_type_id ?? $doc->category_id ?? null;
        @endphp

        @if($documentTypeId && Route::has('hrms.documents.employee.upload_from_profile'))
        <form id="docUploadForm{{ $loop->iteration }}"
            action="{{ route('documents.employee.upload_from_profile', [$employeeData->id, $documentTypeId]) }}"
            method="POST"
            enctype="multipart/form-data"
            class="d-none">
            @csrf
            <input type="hidden" name="keep_verified" value="1">
        </form>
        @endif
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toast Alert System Helper
        function showToast(message, type = 'success') {
            let container = document.getElementById('orb-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'orb-toast-container';
                document.body.appendChild(container);
            }
            
            const toast = document.createElement('div');
            toast.className = `orb-toast orb-toast-${type}`;
            
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            toast.innerHTML = `
                <div class="orb-toast-content">
                    <span class="orb-toast-icon"><i class="fas ${icon}"></i></span>
                    <span class="orb-toast-text">${message}</span>
                </div>
                <button class="orb-toast-close"><i class="fas fa-times"></i></button>
            `;
            
            container.appendChild(toast);
            
            // Close event
            toast.querySelector('.orb-toast-close').addEventListener('click', function() {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            });
            
            // Auto close
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 4000);
        }

        // Keep track of original values for Cancel restoration
        const originalValues = {};

        function getCardInputs(cardId) {
            const card = document.getElementById(cardId);
            return card ? card.querySelectorAll('input, select, textarea') : [];
        }

        function toggleEmploymentSections() {
            const employmentTypeSelect = document.getElementById('employment_type');
            const employeeStageSelect = document.getElementById('employee_stage');
            const employeeStageDisplay = document.getElementById('employee_stage_display');

            const type = employmentTypeSelect ? employmentTypeSelect.value : '';
            const currentStage = employeeStageSelect ? employeeStageSelect.value : '';

            let stage = currentStage || (
                type === 'intern' ? 'internship' :
                (type === 'freelancer' ? 'freelance' :
                    (type === 'contract' ? 'contract' : 'probation'))
            );

            if (employmentTypeSelect && document.body.classList.contains('edit-mode')) {
                stage = type === 'intern' ? 'internship' :
                    (type === 'freelancer' ? 'freelance' :
                        (type === 'contract' ? 'contract' : 'probation'));

                if (employeeStageSelect) {
                    employeeStageSelect.value = stage;
                }
            }

            if (employeeStageDisplay) {
                employeeStageDisplay.value = stage ?
                    stage.replace(/_/g, ' ').replace(/\b\w/g, function(char) {
                        return char.toUpperCase();
                    }) :
                    'Auto';
            }

            document.querySelectorAll('.internship-section').forEach(function(el) {
                el.style.display = stage === 'internship' ? 'block' : 'none';
            });

            document.querySelectorAll('.probation-section').forEach(function(el) {
                el.style.display = stage === 'internship' || stage === 'contract' || stage === 'freelance' ?
                    'none' :
                    'block';
            });

            document.querySelectorAll('.contract-section').forEach(function(el) {
                el.style.display = stage === 'contract' || stage === 'freelance' ? 'block' : 'none';
            });
        }

        function loadDesignations(departmentId, selectedId = '') {
            const designationSelect = document.getElementById('designation_id');
            if (!designationSelect) return;

            if (!departmentId) {
                designationSelect.innerHTML = '<option value="">Select Designation</option>';
                return;
            }

            designationSelect.innerHTML = '<option value="">Loading...</option>';

            fetch("{{ url('/hrms/employees/get-designations') }}/" + departmentId)
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    designationSelect.innerHTML = '<option value="">Select Designation</option>';

                    data.forEach(function(item) {
                        const selected = String(selectedId) === String(item.id) ? 'selected' : '';
                        designationSelect.innerHTML += '<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>';
                    });
                })
                .catch(function() {
                    designationSelect.innerHTML = '<option value="">Unable to load designations</option>';
                });
        }

        // Rebind and reinitialize all event listeners
        function rebindAllListeners() {
            // 1. Section EDIT buttons
            document.querySelectorAll('.edit-sec-btn').forEach(btn => {
                // Remove existing listener to prevent duplicate binding
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.edit-sec-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section');
                    const card = document.getElementById(sectionId);
                    if (!card) return;

                    card.classList.add('is-editing');

                    getCardInputs(sectionId).forEach(el => {
                        const name = el.name || el.id;
                        if (name) {
                            if (el.type === 'file') {
                                originalValues[name] = '';
                            } else {
                                originalValues[name] = el.value;
                            }
                        }

                        // Enable fields except readonly system fields
                        if (el.type !== 'file' && !el.classList.contains('em-control-readonly') && el.name !== 'derived_employee_stage' && el.id !== 'employee_stage_display') {
                            el.removeAttribute('readonly');
                            el.removeAttribute('disabled');
                        } else if (el.type === 'file') {
                            el.removeAttribute('disabled');
                        }
                    });

                    this.style.display = 'none';
                    card.querySelector('.cancel-sec-btn').style.display = 'inline-flex';
                    card.querySelector('.save-sec-btn').style.display = 'inline-flex';

                    if (sectionId === 'cardA') {
                        document.body.classList.add('edit-mode');
                        toggleEmploymentSections();
                    }
                });
            });

            // 2. Section CANCEL buttons
            document.querySelectorAll('.cancel-sec-btn').forEach(btn => {
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.cancel-sec-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section');
                    const card = document.getElementById(sectionId);
                    if (!card) return;

                    card.classList.remove('is-editing');

                    getCardInputs(sectionId).forEach(el => {
                        const name = el.name || el.id;
                        if (name && originalValues[name] !== undefined) {
                            el.value = originalValues[name];
                        }

                        // Restore readonly/disabled
                        if (el.type !== 'file' && el.name !== 'name' && el.name !== 'email' && el.name !== 'phone' && !el.classList.contains('editable') && !el.classList.contains('editable-select')) {
                            // Keep unchanged
                        } else {
                            if (el.tagName === 'SELECT' || el.type === 'file') {
                                el.setAttribute('disabled', 'disabled');
                            } else {
                                el.setAttribute('readonly', 'readonly');
                            }
                        }
                    });

                    card.querySelector('.edit-sec-btn').style.display = 'inline-flex';
                    this.style.display = 'none';
                    card.querySelector('.save-sec-btn').style.display = 'none';

                    if (sectionId === 'cardA') {
                        document.body.classList.remove('edit-mode');
                        toggleEmploymentSections();
                    }
                });
            });

            // 3. Section SAVE buttons
            document.querySelectorAll('.save-sec-btn').forEach(btn => {
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.save-sec-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section');
                    const card = document.getElementById(sectionId);
                    const saveBtn = this;
                    const cancelBtn = card.querySelector('.cancel-sec-btn');
                    const form = document.getElementById('employeeManageForm');

                    const originalHtml = saveBtn.innerHTML;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
                    saveBtn.setAttribute('disabled', 'disabled');
                    cancelBtn.setAttribute('disabled', 'disabled');

                    // Temporarily enable all fields in the entire form so FormData captures all required fields
                    const disabledElements = [];
                    form.querySelectorAll('[disabled]').forEach(el => {
                        disabledElements.push(el);
                        el.removeAttribute('disabled');
                    });

                    const formData = new FormData(form);

                    // Restore disabled state immediately
                    disabledElements.forEach(el => {
                        el.setAttribute('disabled', 'disabled');
                    });

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(htmlText => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(htmlText, 'text/html');

                        const hasError = doc.querySelector('.alert-danger');
                        if (hasError) {
                            const errorMsg = hasError.textContent.trim();
                            showToast(errorMsg || 'Validation failed. Please check inputs.', 'error');

                            saveBtn.innerHTML = originalHtml;
                            saveBtn.removeAttribute('disabled');
                            cancelBtn.removeAttribute('disabled');
                            return;
                        }

                        // Success! Update DOM sections dynamically
                        const updateSection = (id) => {
                            const oldSec = document.getElementById(id);
                            const newSec = doc.getElementById(id);
                            if (oldSec && newSec) {
                                oldSec.innerHTML = newSec.innerHTML;
                            }
                        };

                        updateSection('cardA');
                        updateSection('cardB');

                        const oldHeader = document.querySelector('.ev-header');
                        const newHeader = doc.querySelector('.ev-header');
                        if (oldHeader && newHeader) {
                            oldHeader.innerHTML = newHeader.innerHTML;
                        }

                        rebindAllListeners();

                        showToast(sectionId === 'cardA' ? 'Employee details updated successfully!' : 'Profile details updated successfully!', 'success');
                    })
                    .catch(error => {
                        console.error('Save error:', error);
                        showToast('An error occurred while saving. Please try again.', 'error');

                        saveBtn.innerHTML = originalHtml;
                        saveBtn.removeAttribute('disabled');
                        cancelBtn.removeAttribute('disabled');
                    });
                });
            });

            // 4. Asynchronous Document Re-upload flow
            document.querySelectorAll('.js-ajax-doc-upload').forEach(input => {
                input.replaceWith(input.cloneNode(true));
            });

            document.querySelectorAll('.js-ajax-doc-upload').forEach(input => {
                input.addEventListener('change', function() {
                    const fileInput = this;
                    const file = fileInput.files[0];
                    if (!file) return;

                    const label = fileInput.closest('.btn-doc-reupload');
                    const labelSpan = label ? label.querySelector('span') : null;
                    const labelIcon = label ? label.querySelector('.fas') : null;
                    const docTitle = label ? label.getAttribute('data-doc-title') : 'Document';

                    const originalText = labelSpan ? labelSpan.textContent : 'Reupload';
                    if (labelSpan) labelSpan.textContent = 'Uploading...';
                    if (labelIcon) {
                        labelIcon.className = 'fas fa-spinner fa-spin mr-1';
                    }
                    fileInput.setAttribute('disabled', 'disabled');

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch(fileInput.getAttribute('data-action'), {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Upload failed');
                        }
                        return response.text();
                    })
                    .then(htmlText => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(htmlText, 'text/html');

                        const newCardC = doc.getElementById('documentCard');
                        if (newCardC) {
                            document.getElementById('documentCard').innerHTML = newCardC.innerHTML;
                        }

                        rebindAllListeners();
                        showToast(`${docTitle} uploaded successfully!`, 'success');
                    })
                    .catch(error => {
                        console.error('Upload error:', error);
                        showToast(`Failed to upload ${docTitle}. Please try again.`, 'error');

                        if (labelSpan) labelSpan.textContent = originalText;
                        if (labelIcon) {
                            labelIcon.className = 'fas fa-cloud-upload-alt mr-1';
                        }
                        fileInput.removeAttribute('disabled');
                    });
                });
            });

            // 4a. Asynchronous Document Verification
            document.querySelectorAll('.js-ajax-doc-verify').forEach(btn => {
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.js-ajax-doc-verify').forEach(btn => {
                btn.addEventListener('click', function() {
                    const actionUrl = this.getAttribute('data-action');
                    const docTitle = this.getAttribute('data-doc-title') || 'Document';
                    
                    if (!confirm(`Are you sure you want to verify and lock ${docTitle}?`)) return;
                    
                    const verifyBtn = this;
                    const originalHtml = verifyBtn.innerHTML;
                    verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Verifying...';
                    verifyBtn.setAttribute('disabled', 'disabled');
                    
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    
                    fetch(actionUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Verification failed');
                        }
                        return response.text();
                    })
                    .then(htmlText => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(htmlText, 'text/html');

                        const newCardC = doc.getElementById('documentCard');
                        if (newCardC) {
                            document.getElementById('documentCard').innerHTML = newCardC.innerHTML;
                        }

                        rebindAllListeners();
                        showToast(`${docTitle} verified successfully!`, 'success');
                    })
                    .catch(error => {
                        console.error('Verify error:', error);
                        showToast(`Failed to verify ${docTitle}. Please try again.`, 'error');
                        verifyBtn.innerHTML = originalHtml;
                        verifyBtn.removeAttribute('disabled');
                    });
                });
            });

            // 4b. Asynchronous Document Rejection
            document.querySelectorAll('.js-ajax-doc-reject').forEach(btn => {
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.js-ajax-doc-reject').forEach(btn => {
                btn.addEventListener('click', function() {
                    const actionUrl = this.getAttribute('data-action');
                    const docTitle = this.getAttribute('data-doc-title') || 'Document';
                    
                    const reason = prompt(`Enter rejection reason for ${docTitle}:`);
                    if (reason === null) return; // cancelled
                    if (!reason.trim()) {
                        showToast('Rejection reason is required!', 'error');
                        return;
                    }
                    
                    const rejectBtn = this;
                    const originalHtml = rejectBtn.innerHTML;
                    rejectBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Rejecting...';
                    rejectBtn.setAttribute('disabled', 'disabled');
                    
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('rejection_reason', reason);
                    
                    fetch(actionUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Rejection failed');
                        }
                        return response.text();
                    })
                    .then(htmlText => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(htmlText, 'text/html');

                        const newCardC = doc.getElementById('documentCard');
                        if (newCardC) {
                            document.getElementById('documentCard').innerHTML = newCardC.innerHTML;
                        }

                        rebindAllListeners();
                        showToast(`${docTitle} rejected successfully!`, 'success');
                    })
                    .catch(error => {
                        console.error('Reject error:', error);
                        showToast(`Failed to reject ${docTitle}. Please try again.`, 'error');
                        rejectBtn.innerHTML = originalHtml;
                        rejectBtn.removeAttribute('disabled');
                    });
                });
            });

            // 5. Department Designation dropdown cascade binding
            const departmentSelect = document.getElementById('department_id');
            if (departmentSelect) {
                // Clean and bind
                const newDept = departmentSelect.cloneNode(true);
                departmentSelect.replaceWith(newDept);
                newDept.addEventListener('change', function() {
                    loadDesignations(this.value);
                });
            }

            // 6. Employment type stage toggle binding
            const employmentTypeSelect = document.getElementById('employment_type');
            if (employmentTypeSelect) {
                const newEmpType = employmentTypeSelect.cloneNode(true);
                employmentTypeSelect.replaceWith(newEmpType);
                newEmpType.addEventListener('change', function() {
                    toggleEmploymentSections();
                });
            }

            // 7. Toggle experience fields initially
            const manageExpSelect = document.getElementById('manage_experience_type');
            if (manageExpSelect) {
                toggleManageExperienceFields(manageExpSelect.value);
            }
        }

        function toggleManageExperienceFields(value) {
            const container = document.getElementById('manage_total_experience_container');
            const input = document.getElementById('manage_total_experience');
            if (value === 'fresher') {
                if (container) container.style.display = 'none';
                if (input) {
                    input.removeAttribute('required');
                    input.value = '0';
                }
            } else {
                if (container) container.style.display = 'block';
                if (input) {
                    input.setAttribute('required', 'required');
                    if (input.value === '0') input.value = '';
                }
            }
        }
        window.toggleManageExperienceFields = toggleManageExperienceFields;

        // Initial setup
        rebindAllListeners();
        toggleEmploymentSections();

        // Handle Laravel validation redirect fallbacks (if any non-ajax errors exist)
        @if($errors->any())
        const cardA = document.getElementById('cardA');
        if (cardA) {
            const editBtn = cardA.querySelector('.edit-sec-btn');
            if (editBtn) editBtn.click();
        }
        @endif
    });
</script>
@endsection
