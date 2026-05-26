@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'View Employee Profile')

@section('_head')
@include('hrms.employee.partials.styles')
@endsection

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 34px rgba(16, 24, 40, .07);
    }

    .profile-page {
        min-height: calc(100vh - 90px);
        padding: 24px 24px 32px !important;
        background: var(--orb-bg);
        width: 100% !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    .profile-container {
        max-width: 1240px;
        margin: 0 auto;
    }

    .profile-hero {
        border-radius: 26px !important;
        padding: 28px !important;
        color: #ffffff !important;
        background: linear-gradient(135deg, #4B00E8, #8600EE) !important;
        border: none !important;
        box-shadow: 0 14px 35px rgba(75, 0, 232, 0.15) !important;
        margin-bottom: 24px !important;
        display: grid !important;
        grid-template-columns: 1fr auto !important;
        gap: 20px !important;
        align-items: center !important;
    }

    .profile-main {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .profile-avatar {
        width: 96px !important;
        height: 96px !important;
        border-radius: 26px !important;
        background: rgba(255, 255, 255, 0.15) !important;
        border: 2px solid #ffffff !important;
        overflow: hidden !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #ffffff !important;
        font-size: 32px !important;
        font-weight: 950 !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
        flex: 0 0 auto;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-name {
        margin: 0 !important;
        font-size: 1.65rem !important;
        font-weight: 950 !important;
        color: #ffffff !important;
        letter-spacing: -0.5px !important;
    }

    .profile-meta {
        margin-top: 6px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        font-size: 0.88rem !important;
        font-weight: 700 !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .profile-meta i {
        color: rgba(255, 255, 255, 0.75) !important;
    }

    .status-panel {
        min-width: 280px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.18) !important;
        backdrop-filter: blur(12px) !important;
        border-radius: 22px !important;
        padding: 18px !important;
        box-sizing: border-box !important;
    }

    .status-label {
        font-size: 0.75rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.8px !important;
        font-weight: 900 !important;
        color: rgba(255, 255, 255, 0.75) !important;
    }

    .status-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        padding: 8px 14px !important;
        border-radius: 999px !important;
        font-size: 0.78rem !important;
        font-weight: 950 !important;
        margin-top: 8px !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05) !important;
    }

    .status-pending {
        background: #FEF3C7 !important;
        color: #D97706 !important;
    }

    .status-submitted {
        background: #DBEAFE !important;
        color: #2563EB !important;
    }

    .status-approved {
        background: #D1FAE5 !important;
        color: #059669 !important;
    }

    .status-rejected {
        background: #FEE2E2 !important;
        color: #DC2626 !important;
    }

    .profile-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .btn-soft,
    .btn-orb,
    .btn-successx,
    .btn-dangerx {
        border-radius: 13px;
        padding: 9px 13px;
        font-size: .8rem;
        font-weight: 950;
        border: 0;
        text-decoration: none;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-soft {
        background: rgba(255, 255, 255, 0.15) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.05) !important;
        backdrop-filter: blur(8px) !important;
        transition: all 0.25s ease !important;
    }

    .btn-soft:hover {
        background: rgba(255, 255, 255, 0.25) !important;
        border-color: rgba(255, 255, 255, 0.35) !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
    }

    .btn-orb {
        background: #ffffff !important;
        color: #4B00E8 !important;
        border: none !important;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12) !important;
        transition: all 0.25s ease !important;
    }

    .btn-orb:hover {
        background: #f8fafc !important;
        color: #3f00c8 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
    }

    .btn-successx {
        background: #16A34A;
        color: #fff !important;
    }

    .btn-dangerx {
        background: #DC2626;
        color: #fff !important;
    }

    .section-two-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 20px !important;
        align-items: start !important;
    }

    .profile-card {
        background: #ffffff !important;
        border: 1px solid #E7EAF3 !important;
        box-shadow: var(--orb-shadow) !important;
        border-radius: 22px !important;
        overflow: hidden !important;
        margin-bottom: 20px !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease !important;
    }

    .profile-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 18px 45px rgba(16, 24, 40, 0.1) !important;
    }

    .profile-card-head {
        padding: 20px 24px !important;
        border-bottom: 1px solid #E7EAF3 !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        background: #ffffff !important;
    }

    .profile-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #4B00E8 !important;
        background: rgba(75, 0, 232, 0.06) !important;
        font-size: 16px !important;
        flex: 0 0 auto;
    }

    .profile-card-head h5 {
        margin: 0 !important;
        color: var(--orb-text) !important;
        font-size: 1.05rem !important;
        font-weight: 950 !important;
    }

    .profile-card-head p {
        margin: 3px 0 0 !important;
        color: var(--orb-muted) !important;
        font-size: 0.78rem !important;
        font-weight: 650 !important;
    }

    .profile-card-body {
        padding: 24px !important;
    }

    .info-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 14px !important;
    }

    .bank-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 14px !important;
    }

    .profile-info {
        padding: 14px 16px !important;
        border: 1px solid #E7EAF3 !important;
        border-radius: 14px !important;
        background: #FAFAFB !important;
        min-height: 74px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }

    .profile-label {
        display: block !important;
        color: var(--orb-muted) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        margin-bottom: 4px !important;
    }

    .profile-value {
        color: var(--orb-text) !important;
        font-size: 13px !important;
        font-weight: 900 !important;
    }

    .muted {
        color: #98A2B3 !important;
    }

    .wide {
        grid-column: 1 / -1 !important;
    }

    .profile-edit-control {
        border: 1px solid #E7EAF3;
        border-radius: 13px;
        min-height: 42px;
        font-size: .84rem;
        font-weight: 750;
        color: var(--orb-text);
        background: #fff;
        box-shadow: none;
    }

    .profile-edit-control:focus {
        border-color: rgba(75, 0, 232, .45);
        box-shadow: 0 0 0 3px rgba(75, 0, 232, .08);
    }

    .file-link {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 6px 12px !important;
        border-radius: 20px !important;
        background: #EEF2FF !important;
        color: #4F46E5 !important;
        border: 1px solid #C7D2FE !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
        cursor: pointer;
        white-space: nowrap;
    }

    .file-link:hover {
        background: #E0E7FF !important;
        color: #4338CA !important;
    }

    .doc-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .doc-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
        margin: 0;
    }

    .doc-table thead th {
        color: var(--orb-muted);
        font-size: .72rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .4px;
        border: 0;
        padding: 0 12px 4px;
        white-space: nowrap;
    }

    .doc-table tbody tr {
        box-shadow: 0 8px 18px rgba(16, 24, 40, .045);
    }

    .doc-table tbody td {
        background: #FCFCFD;
        border-top: 1px solid #EEF1F6;
        border-bottom: 1px solid #EEF1F6;
        padding: 13px 12px;
        vertical-align: middle;
        font-size: .84rem;
        font-weight: 800;
        color: var(--orb-text);
    }

    .doc-table tbody td:first-child {
        border-left: 1px solid #EEF1F6;
        border-radius: 16px 0 0 16px;
    }

    .doc-table tbody td:last-child {
        border-right: 1px solid #EEF1F6;
        border-radius: 0 16px 16px 0;
        text-align: right;
    }

    .doc-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 260px;
    }

    .doc-icon {
        height: 42px;
        width: 42px;
        border-radius: 15px;
        background: #F4F2FF;
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .doc-title {
        font-size: .88rem;
        font-weight: 950;
        color: var(--orb-text);
    }

    .doc-sub {
        margin-top: 2px;
        font-size: .73rem;
        font-weight: 700;
        color: var(--orb-muted);
        max-width: 360px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .doc-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 950;
        white-space: nowrap;
    }

    .doc-required {
        background: #FFF7ED;
        color: #C2410C;
    }

    .doc-optional {
        background: #F1F5F9;
        color: #475569;
    }

    .doc-verified {
        background: #DCFCE7;
        color: #166534;
    }

    .doc-pending {
        background: #E0F2FE;
        color: #0369A1;
    }

    .doc-rejected {
        background: #FEE2E2;
        color: #991B1B;
    }

    .doc-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .doc-action-btn {
        border: 0;
        min-height: 34px;
        border-radius: 11px;
        padding: 7px 10px;
        font-size: .72rem;
        font-weight: 950;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        white-space: nowrap;
    }

    .doc-view-btn {
        background: #F4F2FF;
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .14);
    }

    .doc-verify-btn {
        background: #DCFCE7;
        color: #166534;
    }

    .doc-reject-btn {
        background: #FEE2E2;
        color: #991B1B;
    }

    .doc-upload-btn {
        background: #E0F2FE;
        color: #0369A1;
    }

    .doc-disabled-btn {
        background: #F1F5F9;
        color: #64748B;
        cursor: not-allowed;
    }

    .doc-upload-card-form {
        margin: 0;
    }

    .doc-upload-card {
        min-width: 132px;
        min-height: 74px;
        padding: 10px 12px;
        border-radius: 16px;
        border: 1px dashed rgba(75, 0, 232, .32);
        background: linear-gradient(180deg, #fff, #F8F5FF);
        color: var(--orb-primary);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        cursor: pointer;
        transition: .18s ease;
        margin: 0;
    }

    .doc-upload-card:hover {
        border-color: var(--orb-primary);
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(75, 0, 232, .10);
    }

    .doc-upload-card input {
        display: none;
    }

    .doc-upload-icon {
        height: 28px;
        width: 28px;
        border-radius: 10px;
        background: #F4F2FF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .doc-upload-text {
        font-size: .75rem;
        font-weight: 950;
        line-height: 1;
    }

    .doc-upload-card small {
        font-size: .62rem;
        font-weight: 800;
        color: var(--orb-muted);
    }

    .doc-upload-card.is-uploading {
        pointer-events: none;
        opacity: .75;
    }

    .doc-upload-card.is-uploading .doc-upload-icon i:before {
        content: "\f110";
    }

    .doc-upload-card.is-uploading .doc-upload-icon i {
        animation: docSpin .8s linear infinite;
    }

    @keyframes docSpin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .review-clean-body {
        padding: 18px !important;
    }

    .review-clean-top {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 14px;
    }

    .review-mini-stat {
        border: 1px solid #EEF1F6;
        background: #FCFCFD;
        border-radius: 16px;
        padding: 13px;
        min-height: 88px;
    }

    .review-mini-stat span {
        display: block;
        color: var(--orb-muted);
        font-size: .68rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 7px;
    }

    .review-mini-stat strong {
        display: block;
        color: var(--orb-text);
        font-size: 1.2rem;
        font-weight: 950;
        line-height: 1;
    }

    .review-mini-stat small {
        display: block;
        color: var(--orb-muted);
        font-size: .72rem;
        font-weight: 800;
        margin-top: 5px;
    }

    .review-mini-stat.warning {
        background: #FFFBEB;
        border-color: #FDE68A;
    }

    .review-mini-stat.warning strong {
        color: #B45309;
    }

    .review-mini-stat.danger {
        background: #FFF5F5;
        border-color: #FEE2E2;
    }

    .review-mini-stat.danger strong {
        color: #991B1B;
    }

    .review-note-clean {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 14px;
        background: #F8FAFC;
        border: 1px solid #EEF1F6;
        color: var(--orb-muted);
        font-size: .82rem;
        font-weight: 800;
        margin-bottom: 14px;
    }

    .review-note-clean i {
        color: var(--orb-primary);
    }

    .review-reason {
        padding: 10px 12px;
        border-radius: 14px;
        background: #FFF5F5;
        border: 1px solid #FEE2E2;
        color: #991B1B;
        font-size: .82rem;
        font-weight: 800;
        margin-bottom: 12px;
    }

    .review-clean-actions {
        border-top: 1px solid #EEF1F6;
        padding-top: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .review-clean-actions form {
        margin: 0;
    }

    .review-clean-actions .btn-successx,
    .review-clean-actions .btn-dangerx,
    .review-clean-actions .btn-soft {
        min-width: 180px;
    }

    .review-reject-form {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .review-reject-form .form-control {
        width: 260px;
        height: 40px;
        border-radius: 13px;
        font-size: .8rem;
        font-weight: 700;
    }

    .review-approved-box {
        width: 100%;
        padding: 13px;
        border-radius: 16px;
        background: #DCFCE7;
        color: #166534;
        font-size: .88rem;
        font-weight: 950;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    #docPreviewModal {
        z-index: 99999 !important;
        padding-left: 0 !important;
    }

    .modal-backdrop {
        z-index: 99990 !important;
    }

    #docPreviewModal .modal-dialog {
        margin: 24px auto !important;
        max-width: 92vw;
    }

    #docPreviewModal .modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background: #fff;
    }

    .doc-preview-head {
        background: linear-gradient(135deg, #4B00E8, #8600EE);
        color: #fff;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .doc-preview-title {
        font-weight: 950;
        margin: 0;
        font-size: .98rem;
    }

    .doc-preview-body {
        background: #F8FAFC;
    }

    .doc-preview-frame {
        width: 100%;
        border: 0;
        background: #F8FAFC;
        display: block;
    }

    .doc-preview-image-wrap {
        overflow: auto;
        background: #0F172A;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
    }

    .doc-preview-image-wrap img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 14px;
        background: #fff;
    }

    .doc-modal-pdf {
        max-width: min(1100px, 92vw) !important;
    }

    .doc-modal-pdf .doc-preview-frame {
        height: 78vh;
    }

    .doc-modal-image {
        max-width: min(900px, 92vw) !important;
    }

    .doc-modal-image .doc-preview-image-wrap {
        height: 72vh;
    }

    .doc-modal-small-image {
        max-width: min(680px, 92vw) !important;
    }

    .doc-modal-small-image .doc-preview-image-wrap {
        height: 62vh;
    }

    .doc-modal-other {
        max-width: min(900px, 92vw) !important;
    }

    .doc-modal-other .doc-preview-frame {
        height: 70vh;
    }

    @media(max-width:1199px) {
        .bank-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media(max-width:991px) {

        .profile-hero,
        .section-two-grid {
            grid-template-columns: 1fr;
        }

        .status-panel {
            min-width: 100%;
        }

        .bank-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .review-clean-top {
            grid-template-columns: repeat(2, 1fr);
        }

        .review-reject-form {
            width: 100%;
            flex-direction: column;
        }

        .review-reject-form .form-control,
        .review-clean-actions .btn-successx,
        .review-clean-actions .btn-dangerx,
        .review-clean-actions .btn-soft {
            width: 100%;
            min-width: 100%;
        }

        #docPreviewModal .modal-dialog {
            max-width: calc(100vw - 20px) !important;
            margin: 10px auto !important;
        }
    }

    @media(max-width:767px) {
        .profile-page {
            padding: 10px 8px 26px;
        }

        .profile-main {
            align-items: flex-start;
        }

        .profile-avatar {
            width: 76px;
            height: 76px;
            border-radius: 21px;
        }

        .info-grid,
        .bank-grid,
        .review-clean-top {
            grid-template-columns: 1fr;
        }

        .profile-actions a,
        .profile-actions button,
        .profile-actions form {
            width: 100%;
        }

        .profile-actions button,
        .profile-actions a {
            text-align: center;
            justify-content: center;
        }

        .doc-table thead {
            display: none;
        }

        .doc-table,
        .doc-table tbody,
        .doc-table tr,
        .doc-table td {
            display: block;
            width: 100%;
        }

        .doc-table tbody tr {
            margin-bottom: 12px;
        }

        .doc-table tbody td {
            border-left: 1px solid #EEF1F6;
            border-right: 1px solid #EEF1F6;
            border-radius: 0 !important;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .doc-table tbody td:first-child {
            border-radius: 16px 16px 0 0 !important;
        }

        .doc-table tbody td:last-child {
            border-radius: 0 0 16px 16px !important;
            text-align: left;
        }

        .doc-table tbody td:before {
            content: attr(data-label);
            color: var(--orb-muted);
            font-size: .72rem;
            font-weight: 950;
            text-transform: uppercase;
        }

        .doc-table tbody td:first-child:before {
            display: none;
        }

        .doc-name-cell {
            min-width: 0;
        }

        .doc-actions {
            justify-content: flex-start;
        }

        .doc-modal-pdf .doc-preview-frame,
        .doc-modal-other .doc-preview-frame {
            height: 82vh;
        }

        .doc-modal-image .doc-preview-image-wrap,
        .doc-modal-small-image .doc-preview-image-wrap {
            height: 76vh;
        }
    }
</style>

@php
$isFullEditMode = request()->boolean('edit');
$isDocOnlyEditMode = request()->boolean('doc_edit') && ! $isFullEditMode;
$isDocEditMode = $isFullEditMode || $isDocOnlyEditMode;
$isProfileEditMode = $isFullEditMode;

$initial = strtoupper(substr($profile->name ?? 'E', 0, 1));
$status = $profile->profile_status ?? 'pending';

$statusClass = match($status) {
'submitted' => 'status-submitted',
'approved' => 'status-approved',
'rejected' => 'status-rejected',
default => 'status-pending',
};

$statusText = match($status) {
'submitted' => 'Submitted For Review',
'approved' => 'Completed / Approved',
'rejected' => 'Rejected',
default => 'Pending',
};

$documents = $documents ?? collect();
$verifiedDocs = $documents->where('verification_status', 'verified')->count();
$pendingDocs = $documents->where('verification_status', 'pending')->count();
$rejectedDocs = $documents->where('verification_status', 'rejected')->count();
@endphp

<div class="profile-page">
    <div class="profile-container">

        @if(session('success'))
        <div class="alert alert-success rounded-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
        @endif

        <div class="profile-hero">
            <div class="profile-main">
                <div class="profile-avatar">
                    @if (!empty($profile->profile_image) && Route::has('hrms.documents.file'))
                    <img src="{{ route('hrms.documents.file', $profile->profile_image) }}" alt="Profile">
                    @else
                    {{ $initial }}
                    @endif
                </div>

                <div>
                    <h2 class="profile-name">{{ $profile->name ?? '-' }}</h2>
                    <div class="profile-meta"><i class="fas fa-id-badge mr-1"></i>{{ $profile->employee_code ?? '-' }}</div>
                    <div class="profile-meta"><i class="fas fa-envelope mr-1"></i>{{ $profile->email ?? '-' }}</div>
                    <div class="profile-meta">
                        <i class="fas fa-building mr-1"></i>
                        {{ $profile->department_name ?? '-' }}
                        @if(!empty($profile->designation_name))
                        • {{ $profile->designation_name }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="status-panel">
                <div class="status-label">Profile Status</div>

                <div class="status-badge {{ $statusClass }}">
                    <i class="fas fa-circle"></i>
                    {{ $statusText }}
                </div>

                <div class="profile-actions">
                    <a href="{{ route('hrms.employees.pending_profiles') }}" class="btn-soft">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    @if($isFullEditMode)
                    <button type="submit" form="profileInlineForm" class="btn-orb">
                        <i class="fas fa-save"></i> Save
                    </button>

                    <a href="{{ route('hrms.employees.profile.view', $profile->employee_id) }}" class="btn-soft">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    @else
                    <a href="{{ request()->fullUrlWithQuery(['edit' => 1]) }}" class="btn-orb">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <form id="profileInlineForm"
            action="{{ route('hrms.employees.profile.inline_update', $profile->employee_id) }}"
            method="POST">
            @csrf

            <div class="section-two-grid">
                <div class="profile-card">
                    <div class="profile-card-head">
                        <div class="profile-icon"><i class="fas fa-user"></i></div>
                        <div>
                            <h5>Personal Details</h5>
                            <p>Basic identity and address information.</p>
                        </div>
                    </div>

                    <div class="profile-card-body">
                        <div class="info-grid">
                            <div class="profile-info">
                                <span class="profile-label">Date of Birth</span>
                                @if($isProfileEditMode)
                                <input type="date" name="date_of_birth" class="form-control profile-edit-control"
                                    value="{{ old('date_of_birth', !empty($profile->date_of_birth) ? \Carbon\Carbon::parse($profile->date_of_birth)->format('Y-m-d') : '') }}">
                                @else
                                <div class="profile-value {{ empty($profile->date_of_birth) ? 'muted' : '' }}">
                                    {{ !empty($profile->date_of_birth) ? \Carbon\Carbon::parse($profile->date_of_birth)->format('d M Y') : '-' }}
                                </div>
                                @endif
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Gender</span>
                                @if($isProfileEditMode)
                                <select name="gender" class="form-control profile-edit-control">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $profile->gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $profile->gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $profile->gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @else
                                <div class="profile-value {{ empty($profile->gender) ? 'muted' : '' }}">
                                    {{ !empty($profile->gender) ? ucfirst($profile->gender) : '-' }}
                                </div>
                                @endif
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Phone</span>
                                @if($isProfileEditMode)
                                <input type="text" name="phone" class="form-control profile-edit-control"
                                    value="{{ old('phone', $profile->phone ?? '') }}">
                                @else
                                <div class="profile-value {{ empty($profile->phone) ? 'muted' : '' }}">{{ $profile->phone ?? '-' }}</div>
                                @endif
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Email</span>
                                <div class="profile-value {{ empty($profile->email) ? 'muted' : '' }}">{{ $profile->email ?? '-' }}</div>
                            </div>

                            <div class="profile-info wide">
                                <span class="profile-label">Address</span>
                                @if($isProfileEditMode)
                                <textarea name="address" rows="2" class="form-control profile-edit-control">{{ old('address', $profile->address ?? '') }}</textarea>
                                @else
                                <div class="profile-value {{ empty($profile->address) ? 'muted' : '' }}">{{ $profile->address ?? '-' }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="profile-card-head">
                        <div class="profile-icon"><i class="fas fa-graduation-cap"></i></div>
                        <div>
                            <h5>Education & Experience</h5>
                            <p>Qualification, score and total work experience.</p>
                        </div>
                    </div>

                    <div class="profile-card-body">
                        <div class="info-grid">
                            <div class="profile-info">
                                <span class="profile-label">Qualification</span>
                                @if($isProfileEditMode)
                                <input type="text" name="highest_qualification" class="form-control profile-edit-control"
                                    value="{{ old('highest_qualification', $profile->highest_qualification ?? '') }}">
                                @else
                                <div class="profile-value {{ empty($profile->highest_qualification) ? 'muted' : '' }}">{{ $profile->highest_qualification ?? '-' }}</div>
                                @endif
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">CGPA / Percentage</span>
                                @if($isProfileEditMode)
                                <input type="text" name="cgpa_percentage" class="form-control profile-edit-control"
                                    value="{{ old('cgpa_percentage', $profile->cgpa_percentage ?? '') }}">
                                @else
                                <div class="profile-value {{ empty($profile->cgpa_percentage) ? 'muted' : '' }}">{{ $profile->cgpa_percentage ?? '-' }}</div>
                                @endif
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Experience Type</span>
                                @if($isProfileEditMode)
                                <select name="experience_type" id="view_experience_type" class="form-select profile-edit-control" onchange="toggleViewExperienceFields(this.value)">
                                    <option value="fresher" {{ old('experience_type', $profile->experience_type ?? 'fresher') == 'fresher' ? 'selected' : '' }}>Fresher</option>
                                    <option value="experienced" {{ old('experience_type', $profile->experience_type) == 'experienced' ? 'selected' : '' }}>Experienced</option>
                                </select>
                                @else
                                <div class="profile-value {{ empty($profile->experience_type) ? 'muted' : '' }}">{{ !empty($profile->experience_type) ? ucfirst($profile->experience_type) : 'Fresher' }}</div>
                                @endif
                            </div>

                            <div class="profile-info" id="view_total_experience_container">
                                <span class="profile-label">Total Experience</span>
                                @if($isProfileEditMode)
                                <input type="text" name="total_experience" id="view_total_experience" class="form-control profile-edit-control"
                                    value="{{ old('total_experience', $profile->total_experience ?? '') }}">
                                @else
                                <div class="profile-value {{ empty($profile->total_experience) ? 'muted' : '' }}">{{ $profile->total_experience ?? '-' }}</div>
                                @endif
                            </div>

                            <div class="profile-info">
                                <span class="profile-label">Employee Code</span>
                                <div class="profile-value {{ empty($profile->employee_code) ? 'muted' : '' }}">{{ $profile->employee_code ?? '-' }}</div>
                            </div>

                            <div class="profile-info wide">
                                <span class="profile-label">Resume</span>
                                @if (!empty($profile->resume_file) && Route::has('hrms.documents.file'))
                                @php $resumeUrl = route('hrms.documents.file', $profile->resume_file); @endphp
                                <button type="button" class="file-link js-doc-preview"
                                    data-title="Resume"
                                    data-url="{{ $resumeUrl }}"
                                    data-ext="{{ strtolower(pathinfo($profile->resume_file, PATHINFO_EXTENSION)) }}">
                                    <i class="fas fa-eye"></i> View Resume
                                </button>
                                @else
                                <div class="profile-value muted">No resume uploaded</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-card-head">
                    <div class="profile-icon"><i class="fas fa-university"></i></div>
                    <div>
                        <h5>Bank Details</h5>
                        <p>Salary account and banking information.</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="bank-grid">
                        @foreach([
                        'bank_holder_name' => 'Account Holder',
                        'bank_account_no' => 'Account No',
                        'bank_account_type' => 'Account Type',
                        'ifsc_code' => 'IFSC',
                        'bank_branch' => 'Bank Branch',
                        ] as $field => $label)
                        <div class="profile-info">
                            <span class="profile-label">{{ $label }}</span>
                            @if($isProfileEditMode)
                            <input type="text" name="{{ $field }}" class="form-control profile-edit-control"
                                value="{{ old($field, $profile->{$field} ?? '') }}">
                            @else
                            <div class="profile-value {{ empty($profile->{$field}) ? 'muted' : '' }}">
                                {{ $profile->{$field} ?? '-' }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>

        <div class="profile-card">
            <div class="profile-card-head">
                <div class="profile-icon"><i class="fas fa-folder-open"></i></div>
                <div style="flex:1;">
                    <h5>Employee Documents</h5>
                    <p>{{ $isDocEditMode ? 'Select file to upload or re-upload document.' : 'Document name, status and verification actions.' }}</p>
                </div>

                @if(!$isFullEditMode)
                @if($isDocOnlyEditMode)
                <a href="{{ route('hrms.employees.profile.view', $profile->employee_id) }}" class="btn-soft">
                    <i class="fas fa-times"></i> Cancel
                </a>
                @else
                <a href="{{ request()->fullUrlWithQuery(['doc_edit' => 1]) }}" class="btn-orb">
                    <i class="fas fa-file-upload"></i> Edit Documents
                </a>
                @endif
                @endif
            </div>

            <div class="profile-card-body">
                @if($documents->count())
                <div class="doc-table-wrap">
                    <table class="doc-table">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <!-- <th>Required</th> -->
                                <th>Status</th>
                                <th>Uploaded At</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($documents as $doc)
                            @php
                            $docStatus = strtolower($doc->verification_status ?? 'pending');
                            $docStatusClass = match($docStatus) {
                            'verified' => 'doc-verified',
                            'rejected' => 'doc-rejected',
                            default => 'doc-pending',
                            };

                            $docTitle = $doc->document_type_name ?? $doc->title ?? 'Document';
                            $docPath = $doc->file_path ?? null;
                            $docUrl = null;

                            if (!empty($doc->file_url)) {
                            $docUrl = $doc->file_url;
                            } elseif (!empty($docPath) && Route::has('hrms.documents.file')) {
                            $docUrl = route('hrms.documents.file', $docPath);
                            } elseif (!empty($docPath)) {
                            $docUrl = route('hrms.documents.file', ['path' => $docPath]);
                            }

                            $ext = strtolower(pathinfo($doc->file_original_name ?: $docPath, PATHINFO_EXTENSION));
                            $documentTypeId = $doc->document_type_id ?? $doc->category_id ?? null;
                            @endphp

                            <tr>
                                <td data-label="Document">
                                    <div class="doc-name-cell">
                                        <div class="doc-icon"><i class="fas fa-file-alt"></i></div>
                                        <div>
                                            <div class="doc-title">{{ $docTitle }}</div>
                                            <div class="doc-sub">{{ $doc->file_original_name ?? 'No file uploaded' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- <td data-label="Required">
                                    <span class="doc-pill {{ !empty($doc->is_required) ? 'doc-required' : 'doc-optional' }}">
                                        {{ !empty($doc->is_required) ? 'Required' : 'Optional' }}
                                    </span>
                                </td> -->

                                <td data-label="Status">
                                    <span class="doc-pill {{ $docStatusClass }}">{{ ucfirst($docStatus) }}</span>
                                </td>

                                <td data-label="Uploaded At">
                                    @if(!empty($doc->uploaded_at))
                                    {{ \Carbon\Carbon::parse($doc->uploaded_at)->format('d M Y, h:i A') }}
                                    @elseif(!empty($doc->created_at))
                                    {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y, h:i A') }}
                                    @else
                                    -
                                    @endif
                                </td>

                                <td data-label="Action">
                                    <div class="doc-actions">
                                        @if(!empty($docUrl))
                                        <button type="button" class="doc-action-btn doc-view-btn js-doc-preview"
                                            data-title="{{ $docTitle }}"
                                            data-url="{{ $docUrl }}"
                                            data-ext="{{ $ext }}">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        @endif

                                        @if($isDocEditMode)
                                        @if($documentTypeId && Route::has('hrms.documents.employee.upload_from_profile'))
                                        <form action="{{ route('documents.employee.upload_from_profile', [$profile->employee_id, $documentTypeId]) }}"
                                            method="POST"
                                            enctype="multipart/form-data"
                                            class="doc-upload-card-form js-auto-upload-form">
                                            @csrf

                                            <label class="doc-upload-card">
                                                <input type="file"
                                                    name="file"
                                                    class="js-auto-upload-input"
                                                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                    required>

                                                <span class="doc-upload-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </span>

                                                <span class="doc-upload-text">
                                                    {{ !empty($doc->file_path) ? 'Re-upload' : 'Upload' }}
                                                </span>

                                                <small>PDF, JPG, PNG, WEBP</small>
                                            </label>
                                        </form>
                                        @endif
                                        @else
                                        @if($docStatus === 'verified')
                                        <button type="button" class="doc-action-btn doc-disabled-btn" disabled>
                                            <i class="fas fa-lock"></i> Verified
                                        </button>
                                        @else
                                        @if(Route::has('hrms.documents.employee.verify'))
                                        <form action="{{ route('documents.employee.verify', $doc->id) }}" method="POST" style="display:inline-block;margin:0;">
                                            @csrf
                                            <button type="submit" class="doc-action-btn doc-verify-btn" onclick="return confirm('Verify this document?')">
                                                <i class="fas fa-check"></i> Verify
                                            </button>
                                        </form>
                                        @endif

                                        @if(Route::has('hrms.documents.employee.reject'))
                                        <form action="{{ route('documents.employee.reject', $doc->id) }}" method="POST" style="display:inline-block;margin:0;">
                                            @csrf
                                            <input type="hidden" name="rejection_reason" value="Document rejected by HR">
                                            <button type="submit" class="doc-action-btn doc-reject-btn" onclick="return confirm('Reject this document?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                        @endif
                                        @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="profile-value muted">No documents uploaded yet.</div>
                @endif
            </div>
        </div>

        <div class="profile-card review-card">
            <div class="profile-card-head">
                <div class="profile-icon"><i class="fas fa-user-check"></i></div>
                <div>
                    <h5>HR Review</h5>
                    <p>
                        @if($isFullEditMode)
                        Update employee profile and documents.
                        @elseif($isDocOnlyEditMode)
                        Upload or re-upload employee documents only.
                        @else
                        Approve or reject employee profile.
                        @endif
                    </p>
                </div>
            </div>

            <div class="profile-card-body review-clean-body">
                <div class="review-clean-top">
                    <div class="review-mini-stat">
                        <span>Profile Status</span>
                        <div class="status-badge {{ $statusClass }}">
                            <i class="fas fa-circle"></i>
                            {{ $statusText }}
                        </div>
                    </div>

                    <div class="review-mini-stat">
                        <span>Documents</span>
                        <strong>{{ $verifiedDocs }}/{{ $documents->count() }}</strong>
                        <small>Verified</small>
                    </div>

                    <div class="review-mini-stat warning">
                        <span>Pending</span>
                        <strong>{{ $pendingDocs }}</strong>
                        <small>Documents</small>
                    </div>

                    <div class="review-mini-stat danger">
                        <span>Rejected</span>
                        <strong>{{ $rejectedDocs }}</strong>
                        <small>Documents</small>
                    </div>
                </div>

                @if(!empty($profile->rejection_reason))
                <div class="review-reason">
                    <strong>Rejection Reason:</strong>
                    {{ $profile->rejection_reason }}
                </div>
                @endif

                <div class="review-note-clean">
                    <i class="fas fa-info-circle"></i>
                    @if($isFullEditMode)
                    Save button will update profile fields. Document file selection uploads separately.
                    @elseif($isDocOnlyEditMode)
                    Select a file in document list to auto upload or re-upload.
                    @else
                    Approve only after checking all submitted documents.
                    @endif
                </div>

                <div class="review-clean-actions">
                    @if($isFullEditMode)
                    <button type="submit" form="profileInlineForm" class="btn-successx">
                        <i class="fas fa-save"></i> Update Profile
                    </button>

                    <a href="{{ route('hrms.employees.profile.view', $profile->employee_id) }}" class="btn-dangerx">
                        <i class="fas fa-times-circle"></i> Cancel
                    </a>
                    @elseif($isDocOnlyEditMode)
                    <a href="{{ route('hrms.employees.profile.view', $profile->employee_id) }}" class="btn-dangerx">
                        <i class="fas fa-times-circle"></i> Cancel Document Edit
                    </a>
                    @else
                    @if($status === 'approved')
                    <div class="review-approved-box">
                        <i class="fas fa-check-circle"></i>
                        Profile already approved
                    </div>
                    @else
                    @if(Route::has('hrms.employees.profile.approve') && $status === 'submitted')
                    <form action="{{ route('hrms.employees.profile.approve', $profile->employee_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-successx"
                            onclick="return confirm('Approve profile and verify all uploaded documents?')">
                            <i class="fas fa-check-circle"></i>
                            Approve Profile
                        </button>
                    </form>
                    @elseif($status === 'pending')
                    <button type="button" class="btn-soft" disabled>
                        <i class="fas fa-clock"></i>
                        Waiting for Submission
                    </button>
                    @endif

                    @if(Route::has('hrms.employees.profile.reject') && in_array($status, ['submitted', 'rejected']))
                    <form action="{{ route('hrms.employees.profile.reject', $profile->employee_id) }}" method="POST" class="review-reject-form">
                        @csrf
                        <input type="text" name="rejection_reason" class="form-control" placeholder="Reject reason" value="{{ $profile->rejection_reason ?? '' }}">

                        <button type="submit" class="btn-dangerx" onclick="return confirm('Reject this profile?')">
                            <i class="fas fa-times-circle"></i>
                            Reject
                        </button>
                    </form>
                    @endif
                    @endif
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="docPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="docPreviewDialog" role="document">
        <div class="modal-content">
            <div class="doc-preview-head">
                <h5 class="doc-preview-title" id="docPreviewTitle">Document Preview</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color:#fff;">&times;</span>
                </button>
            </div>

            <div id="docPreviewBody" class="doc-preview-body"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = $('#docPreviewModal');
        const dialog = document.getElementById('docPreviewDialog');
        const title = document.getElementById('docPreviewTitle');
        const body = document.getElementById('docPreviewBody');

        function resetDialogClass() {
            dialog.classList.remove('doc-modal-pdf', 'doc-modal-image', 'doc-modal-small-image', 'doc-modal-other');
        }

        document.querySelectorAll('.js-doc-preview').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const ext = (this.getAttribute('data-ext') || '').toLowerCase();
                const docTitle = this.getAttribute('data-title') || 'Document Preview';

                title.textContent = docTitle;
                body.innerHTML = '';
                resetDialogClass();

                if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
                    dialog.classList.add('doc-modal-image');

                    body.innerHTML = `
                        <div class="doc-preview-image-wrap">
                            <img id="docPreviewImage" src="${url}" alt="${docTitle}">
                        </div>
                    `;

                    const img = document.getElementById('docPreviewImage');

                    img.onload = function() {
                        resetDialogClass();
                        if (img.naturalWidth <= 700 && img.naturalHeight <= 900) {
                            dialog.classList.add('doc-modal-small-image');
                        } else {
                            dialog.classList.add('doc-modal-image');
                        }
                    };
                } else if (ext === 'pdf') {
                    dialog.classList.add('doc-modal-pdf');
                    body.innerHTML = `<iframe class="doc-preview-frame" src="${url}#toolbar=0&navpanes=0&scrollbar=1"></iframe>`;
                } else {
                    dialog.classList.add('doc-modal-other');
                    body.innerHTML = `<iframe class="doc-preview-frame" src="${url}"></iframe>`;
                }

                modal.modal('show');
            });
        });

        document.querySelectorAll('.js-auto-upload-input').forEach(function(input) {
            input.addEventListener('change', function() {
                if (!this.files || !this.files.length) return;

                const form = this.closest('.js-auto-upload-form');
                const card = this.closest('.doc-upload-card');

                if (card) {
                    card.classList.add('is-uploading');
                    const text = card.querySelector('.doc-upload-text');
                    if (text) text.textContent = 'Uploading...';
                }

                if (form) form.submit();
            });
        });

        $('#docPreviewModal').on('hidden.bs.modal', function() {
            body.innerHTML = '';
            resetDialogClass();
        });

        function toggleViewExperienceFields(value) {
            const container = document.getElementById('view_total_experience_container');
            const input = document.getElementById('view_total_experience');
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
        window.toggleViewExperienceFields = toggleViewExperienceFields;
        
        const expSelect = document.getElementById('view_experience_type');
        if (expSelect) {
            toggleViewExperienceFields(expSelect.value);
        } else {
            const currentExpType = '{{ strtolower($profile->experience_type ?? "fresher") }}';
            if (currentExpType === 'fresher') {
                const container = document.getElementById('view_total_experience_container');
                if (container) container.style.display = 'none';
            }
        }
    });
</script>
@endsection
