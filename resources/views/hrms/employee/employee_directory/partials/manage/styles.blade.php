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

    .ev-header .ev-pill-active {
        background: rgba(22, 163, 74, 0.3) !important;
        border-color: rgba(22, 163, 74, 0.4) !important;
    }

    .ev-header .ev-pill-inactive {
        background: rgba(220, 38, 38, 0.3) !important;
        border-color: rgba(220, 38, 38, 0.4) !important;
    }

    .ev-header .ev-pill-completed {
        background: rgba(22, 163, 74, 0.3) !important;
        border-color: rgba(22, 163, 74, 0.4) !important;
    }

    .ev-header .ev-pill-pending {
        background: rgba(217, 119, 6, 0.3) !important;
        border-color: rgba(217, 119, 6, 0.4) !important;
    }

    .ev-header .ev-pill-submitted {
        background: rgba(2, 132, 199, 0.3) !important;
        border-color: rgba(2, 132, 199, 0.4) !important;
    }

    .ev-header .ev-pill-rejected {
        background: rgba(220, 38, 38, 0.3) !important;
        border-color: rgba(220, 38, 38, 0.4) !important;
    }

    .ev-header .ev-pill-default {
        background: rgba(255, 255, 255, 0.1) !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }

    .em-layout {
        display: flex;
        flex-direction: column;
        gap: 24px;
        align-items: stretch;
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
        border-color: var(--orb-border);
    }

    /* Highlight ONLY inputs inside the container that is currently being edited */
    .em-card.is-editing .em-control:not([readonly]):not(:disabled) {
        border-color: rgba(75, 0, 232, 0.5) !important;
        background: #F9FAFB;
    }

    .em-card.is-editing .em-control:not([readonly]):not(:disabled):focus {
        outline: none;
        border-color: rgba(75, 0, 232, 0.85) !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.12);
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
        box-shadow: 0 8px 18px rgba(16, 24, 40, .06) !important;
    }

    .manage-btn-save {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
        border: 0 !important;
        box-shadow: 0 10px 24px rgba(75, 0, 232, .24) !important;
    }

    .manage-btn-save:hover,
    .manage-btn-save:focus,
    .manage-btn-save:active {
        background: linear-gradient(135deg, #3F00C8, #7600D6) !important;
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
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-text);
    }
</style>
