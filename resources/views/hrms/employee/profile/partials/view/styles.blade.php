<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 34px rgba(16, 24, 40, .07);
    }

    .profile-page {
        min-height: calc(100vh - 90px);
        padding: 20px !important;
        background: var(--orb-bg);
        width: 100% !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    .profile-container {
        max-width: 100% !important;
        margin: 0 auto !important;
        width: 100% !important;
    }

    .profile-hero {
        border-radius: 26px !important;
        padding: 24px 28px !important;
        color: #ffffff !important;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        border: none !important;
        box-shadow: 0 14px 35px rgba(75, 0, 232, 0.15) !important;
        margin-bottom: 24px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        width: 100% !important;
        box-sizing: border-box !important;
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
        color: var(--orb-primary) !important;
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
        color: var(--orb-primary) !important;
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
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
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
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }

    @media(max-width:991px) {
        .profile-hero {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 18px !important;
            padding: 20px 22px !important;
        }

        .section-two-grid {
            grid-template-columns: 1fr !important;
            gap: 16px !important;
        }

        .status-panel {
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        .bank-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        .review-clean-top {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        .review-reject-form {
            width: 100% !important;
            flex-direction: column !important;
        }

        .review-reject-form .form-control,
        .review-clean-actions .btn-successx,
        .review-clean-actions .btn-dangerx,
        .review-clean-actions .btn-soft {
            width: 100% !important;
            min-width: 100% !important;
        }

        #docPreviewModal .modal-dialog {
            max-width: calc(100vw - 20px) !important;
            margin: 10px auto !important;
        }
    }

    @media(max-width:767px) {
        .profile-page {
            padding: 12px 10px 26px !important;
        }

        .profile-main {
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            gap: 12px !important;
            width: 100% !important;
        }

        .profile-avatar {
            width: 76px !important;
            height: 76px !important;
            border-radius: 20px !important;
            font-size: 26px !important;
        }

        .profile-name {
            font-size: 1.35rem !important;
            text-align: center !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            width: 100% !important;
        }

        .profile-meta {
            justify-content: center !important;
            text-align: center !important;
            font-size: 0.82rem !important;
            flex-wrap: wrap !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            width: 100% !important;
        }

        .info-grid,
        .bank-grid,
        .review-clean-top {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }

        .profile-actions {
            width: 100% !important;
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            justify-content: stretch !important;
        }

        .profile-actions a,
        .profile-actions button,
        .profile-actions form {
            flex: 1 1 auto !important;
            min-width: 100px !important;
            width: 100% !important;
            text-align: center !important;
            justify-content: center !important;
        }

        .doc-table thead {
            display: none !important;
        }

        .doc-table,
        .doc-table tbody,
        .doc-table tr,
        .doc-table td {
            display: block !important;
            width: 100% !important;
        }

        .doc-table tbody tr {
            margin-bottom: 14px !important;
            border-radius: 18px !important;
            border: 1px solid #E7EAF3 !important;
            background: #FCFCFD !important;
            box-shadow: 0 4px 14px rgba(16, 24, 40, 0.04) !important;
            overflow: hidden !important;
        }

        .doc-table tbody td {
            border: 0 !important;
            border-bottom: 1px solid #EEF1F6 !important;
            border-radius: 0 !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 11px 14px !important;
            box-sizing: border-box !important;
            font-size: 0.82rem !important;
        }

        .doc-table tbody td:first-child {
            background: #F8FAFC !important;
            padding: 13px 14px !important;
            border-bottom: 1px solid #E7EAF3 !important;
            border-radius: 18px 18px 0 0 !important;
        }

        .doc-table tbody td:last-child {
            border-bottom: 0 !important;
            background: #FFFFFF !important;
            padding: 12px 14px !important;
            border-radius: 0 0 18px 18px !important;
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
        }

        .doc-table tbody td:before {
            content: attr(data-label) !important;
            color: var(--orb-muted) !important;
            font-size: .68rem !important;
            font-weight: 950 !important;
            text-transform: uppercase !important;
            letter-spacing: .4px !important;
            flex-shrink: 0 !important;
        }

        .doc-table tbody td:first-child:before {
            display: none !important;
        }

        .doc-table tbody td:last-child:before {
            align-self: flex-start !important;
            margin-bottom: 2px !important;
        }

        .doc-name-cell {
            min-width: 0 !important;
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }

        .doc-sub {
            max-width: 100% !important;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            font-size: 0.73rem !important;
        }

        .doc-actions {
            width: 100% !important;
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            justify-content: flex-start !important;
            align-items: center !important;
        }

        .doc-actions .doc-action-btn,
        .doc-actions form {
            flex: 1 1 auto !important;
            min-width: 90px !important;
        }

        .doc-action-btn {
            width: 100% !important;
            justify-content: center !important;
            min-height: 36px !important;
        }

        .doc-upload-card-form {
            width: 100% !important;
        }

        .doc-upload-card {
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            padding: 12px 14px !important;
        }

        .doc-modal-pdf .doc-preview-frame,
        .doc-modal-other .doc-preview-frame {
            height: 82vh !important;
        }

        .doc-modal-image .doc-preview-image-wrap,
        .doc-modal-small-image .doc-preview-image-wrap {
            height: 76vh !important;
        }
    }

    @media(max-width:575px) {
        .profile-page {
            padding: 10px 6px 20px !important;
        }

        .profile-hero {
            padding: 16px 14px !important;
            border-radius: 18px !important;
            margin-bottom: 14px !important;
        }

        .profile-name {
            font-size: 1.2rem !important;
        }

        .profile-meta {
            font-size: 0.78rem !important;
            gap: 4px !important;
        }

        .profile-card {
            border-radius: 16px !important;
            margin-bottom: 14px !important;
        }

        .profile-card-head {
            padding: 14px 16px !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 12px !important;
        }

        .profile-card-head .profile-icon {
            width: 38px !important;
            height: 38px !important;
            font-size: 14px !important;
        }

        .profile-card-head > div:not(.profile-icon) {
            width: 100% !important;
            min-width: 0 !important;
        }

        .profile-card-head h5 {
            font-size: 15px !important;
        }

        .profile-card-head p {
            font-size: 11.5px !important;
        }

        .profile-card-head .btn-orb,
        .profile-card-head .btn-soft {
            width: 100% !important;
            min-height: 38px !important;
            justify-content: center !important;
            text-align: center !important;
            margin-top: 2px !important;
        }

        .profile-card-body {
            padding: 12px 10px !important;
        }

        .profile-info {
            padding: 10px 10px !important;
            border-radius: 10px !important;
            min-height: auto !important;
        }

        .profile-label {
            font-size: 10px !important;
        }

        .profile-value {
            font-size: 12px !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
        }
    }
</style>
