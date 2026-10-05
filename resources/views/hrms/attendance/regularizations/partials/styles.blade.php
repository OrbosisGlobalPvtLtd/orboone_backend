<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<style>
:root {
    --orb-bg: #F6F7FB;
    --orb-border: #E7EAF3;
    --orb-text: #101828;
    --orb-muted: #667085;
    --orb-soft: #F4F2FF;
    --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
}

body {
    overflow-x: hidden !important;
}

.att-page {
    min-height: calc(100vh - 90px);
    background: var(--orb-bg);
    padding: 12px 14px 28px;
}

.att-container {
    max-width: 100% !important;
    width: 100%;
    margin: 0 auto;
}

.att-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 30px !important;
    padding: 30px;
    margin-bottom: 18px;
    box-shadow: 0 18px 45px rgba(75, 0, 232, .20);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    color: #fff;
    position: relative;
    overflow: hidden;
}

.att-hero:before {
    content: "";
    position: absolute;
    right: -80px;
    top: -110px;
    width: 360px;
    height: 360px;
    border-radius: 50%;
    background: rgba(255, 255, 255, .12);
    pointer-events: none;
}

.att-kicker {
    font-size: 12px;
    font-weight: 950;
    letter-spacing: .14em;
    text-transform: uppercase;
    opacity: .9;
    margin-bottom: 10px;
    display: flex;
    gap: 9px;
    align-items: center;
}

.att-title {
    font-size: 34px;
    font-weight: 950;
    margin: 0;
    line-height: 1.1;
    color: #fff;
}

.att-subtitle {
    font-size: 15px;
    font-weight: 650;
    margin-top: 10px;
    opacity: .92;
    max-width: 850px;
}

.att-hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.att-btn {
    border: 0;
    border-radius: 14px;
    padding: 13px 18px;
    font-weight: 950;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    text-decoration: none !important;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.2s ease;
}

.att-btn-glass,
.att-hero-actions .att-btn {
    background: rgba(255, 255, 255, 0.18) !important;
    border: 1px solid rgba(255, 255, 255, 0.38) !important;
    color: #ffffff !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    border-radius: 999px !important;
    padding: 9px 20px !important;
    font-size: 13.5px !important;
    font-weight: 750 !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
}

.att-btn-glass:hover,
.att-hero-actions .att-btn:hover {
    background: rgba(255, 255, 255, 0.32) !important;
    border-color: rgba(255, 255, 255, 0.65) !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

.att-metric-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
}

.att-metric {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 18px;
    padding: 14px 14px 10px;
    box-shadow: 0 10px 24px rgba(16, 24, 40, .055);
    position: relative;
    overflow: hidden;
    min-height: 92px;
}

.att-metric:after {
    content: "";
    position: absolute;
    right: -22px;
    top: -30px;
    width: 86px;
    height: 86px;
    border-radius: 50%;
    background: var(--metric-soft, #F4F2FF);
    pointer-events: none;
}

.att-metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    position: relative;
    z-index: 1;
}

.att-metric-icon {
    width: 36px;
    height: 36px;
    border-radius: 13px;
    background: var(--metric-soft, #F4F2FF);
    color: var(--metric-color, var(--orb-primary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

.att-metric-value {
    font-size: 25px;
    font-weight: 950;
    color: #101828;
    line-height: 1;
}

.att-metric-label {
    font-size: 11px;
    font-weight: 950;
    color: #475467;
    text-transform: uppercase;
    margin-top: 14px;
    position: relative;
    z-index: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.att-metric-line {
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--metric-color, var(--orb-primary)), transparent);
    margin-top: 8px;
}

.att-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 18px !important;
    box-shadow: var(--orb-shadow);
    overflow: hidden !important;
}

.att-section-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--orb-border);
    background: linear-gradient(180deg, #fff, #FAFBFF);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.att-section-title {
    font-size: 18px;
    font-weight: 900;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.att-section-title i {
    color: var(--orb-primary);
}

.att-section-sub {
    font-size: 13px;
    color: var(--orb-muted);
    font-weight: 600;
    margin-top: 4px;
}

.att-section-btn {
    background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
    color: #ffffff !important;
    border: 0 !important;
    border-radius: 12px !important;
    padding: 9px 18px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25) !important;
    transition: all 0.2s ease !important;
    text-decoration: none !important;
    cursor: pointer !important;
    white-space: nowrap !important;
}

.att-section-btn:hover {
    transform: translateY(-1.5px) !important;
    box-shadow: 0 6px 20px rgba(75, 0, 232, 0.35) !important;
    color: #ffffff !important;
}

.att-head-badges {
    display: flex;
    gap: 9px;
    flex-wrap: wrap;
    align-items: center;
}

.att-total-pill {
    border: 1px solid var(--orb-border);
    background: #F8FAFC;
    color: var(--orb-text);
    border-radius: 10px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 850;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.att-total-pill.purple {
    border-color: #E0D7FF;
    background: #F5F2FF;
    color: var(--orb-primary);
}

.att-total-pill.orange {
    border-color: #FED7AA;
    background: #FFF7ED;
    color: #C2410C;
}

.att-total-pill.green {
    border-color: #BBF7D0;
    background: #F0FDF4;
    color: #16A34A;
}

/* Single line filter panel */
.att-filter-panel {
    padding: 14px 18px;
    border-bottom: 1px solid var(--orb-border);
    background: #fff;
}

.att-filter-grid {
    display: flex;
    flex-wrap: nowrap;
    align-items: flex-end;
    gap: 12px;
    width: 100%;
}

.att-filter-grid .att-filter-item {
    flex: 1 1 0;
    min-width: 120px;
}

.att-filter-grid .att-filter-actions {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 8px;
}

.att-filter-grid label {
    font-size: 10px;
    font-weight: 950;
    color: #667085;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 6px;
    display: block;
    white-space: nowrap;
}

.att-filter-grid .form-control {
    height: 42px;
    border-radius: 12px;
    border: 1px solid #E4E7EC;
    font-size: 13px;
    font-weight: 700;
    padding: 0 12px;
    box-shadow: none !important;
    background: #fff;
    width: 100%;
}

.att-filter-grid .form-control:focus {
    border-color: var(--orb-primary);
}

/* UNIFIED DATATABLES TOOLBAR STYLES */
.leave-dt-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 24px;
    border-top: 1px solid #E7EAF3;
    border-bottom: 1px solid #E7EAF3;
    background: #fff;
}

.leave-dt-left {
    display: flex;
    align-items: center;
}

.leave-dt-right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.dt-buttons {
    display: flex !important;
    gap: 8px;
}

.leave-export-btn {
    height: 38px !important;
    border-radius: 12px !important;
    padding: 8px 16px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    color: #344054 !important;
    background: #fff !important;
    border: 1px solid #E7EAF3 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    box-shadow: 0 1px 2px rgba(16,24,40,0.05) !important;
    transition: all 0.2s ease !important;
    margin-bottom: 0 !important;
}

.leave-export-btn:hover {
    background: #F9F5FF !important;
    color: var(--orb-primary) !important;
    border-color: #D9CCFF !important;
}

.select2-dropdown-per-page {
    border-radius: 12px !important;
    border: 1px solid #E7EAF3 !important;
    box-shadow: 0 10px 30px rgba(16, 24, 40, 0.1) !important;
    padding: 6px !important;
    min-width: 75px !important;
    z-index: 1060 !important;
}

.select2-dropdown-per-page .select2-results__option {
    padding: 6px 12px !important;
    border-radius: 8px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    text-align: center !important;
    margin-bottom: 2px !important;
}

.select2-dropdown-per-page .select2-results__option--selected,
.select2-dropdown-per-page .select2-results__option--highlighted {
    background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
    color: #fff !important;
}

.select2-container--per-page .select2-selection--single {
    height: 38px !important;
    border-radius: 10px !important;
    border: 1px solid #D0D5DD !important;
    background: #fff !important;
    display: flex !important;
    align-items: center !important;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
}

.select2-container--per-page .select2-selection__rendered {
    font-weight: 800 !important;
    color: #1D2939 !important;
    font-size: 13px !important;
    padding-left: 10px !important;
    padding-right: 22px !important;
    line-height: 36px !important;
}

.select2-container--per-page .select2-selection__arrow {
    height: 36px !important;
    right: 6px !important;
}

.dataTables_length select {
    border-radius: 10px !important;
    padding: 4px 22px 4px 8px !important;
    height: 38px !important;
    border: 1px solid #E7EAF3 !important;
}

.att-table-wrap {
    padding: 0 !important;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.att-table {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0;
    margin: 0 !important;
}

.att-table thead th {
    background: #F8FAFC !important;
    color: #475467 !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 11px 12px !important;
    border-top: none !important;
    border-bottom: 1px solid #EAECF0 !important;
    white-space: nowrap;
    vertical-align: middle !important;
}

.att-table tbody td {
    background: #fff;
    border-bottom: 1px solid #F2F4F7 !important;
    padding: 10px 12px !important;
    vertical-align: middle !important;
    font-size: 12.5px;
    color: var(--orb-text);
}

.att-table tbody tr:hover td {
    background: #FCFAFF !important;
}

.att-emp {
    display: flex;
    gap: 10px;
    align-items: center;
    min-width: 0;
}

.att-emp-name {
    font-weight: 900;
    color: var(--orb-text);
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}

.att-emp-code {
    font-size: 11px;
    color: var(--orb-muted);
    font-weight: 700;
    margin-top: 2px;
}

.orb-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 10.5px;
    font-weight: 900;
    text-transform: uppercase;
    white-space: nowrap;
}

.orb-badge-success { background: #ECFDF3; color: #027A48; border: 1px solid #ABEFC6; }
.orb-badge-warning { background: #FFFAEB; color: #B54708; border: 1px solid #FEDF89; }
.orb-badge-danger { background: #FEF3F2; color: #B42318; border: 1px solid #FECDCA; }
.orb-badge-secondary { background: #F2F4F7; color: #344054; border: 1px solid #D0D5DD; }
.orb-badge-primary { background: var(--orb-soft); color: var(--orb-primary); border: 1px solid #E0D7FF; }
.orb-badge-info { background: #EFF6FF; color: #2563EB; border: 1px solid #DBEAFE; }

.att-action-btn {
    border-radius: 10px;
    padding: 6px 12px;
    font-size: 11.5px;
    font-weight: 850;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 0;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none !important;
}

.att-action-view {
    background: #EFF6FF;
    color: #2563EB;
    border: 1px solid #DBEAFE;
}

.att-action-view:hover {
    background: #2563EB;
    color: #fff;
}

.orb-action-btn {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid var(--orb-border);
    background: #fff;
    color: var(--orb-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.orb-action-btn:hover {
    background: var(--orb-soft);
    color: var(--orb-primary);
    border-color: rgba(75, 0, 232, 0.2);
}

.avatar-table {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    min-height: 36px !important;
    object-fit: cover !important;
    border-radius: 50% !important;
    border: 2px solid #E2E8F0;
}

.avatar-modal {
    width: 48px !important;
    height: 48px !important;
    min-width: 48px !important;
    min-height: 48px !important;
    object-fit: cover !important;
    border-radius: 50% !important;
    border: 2px solid rgba(255, 255, 255, 0.6);
}

/* Glassmorphism & Modern Modal styling with pinned header/footer & scrollable body */
.glass-modal .modal-dialog {
    max-width: 640px !important;
    width: 94% !important;
    margin: 1.75rem auto !important;
    display: flex !important;
    align-items: center !important;
    min-height: calc(100% - 3.5rem) !important;
}

.glass-modal .modal-dialog.modal-lg {
    max-width: 780px !important;
}

/* Responsive 2-Column Form Grid for Regularization Modals */
.reg-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px 18px;
    align-items: start;
}

.reg-form-grid .form-group {
    margin-bottom: 0 !important;
}

.reg-form-grid .grid-col-full,
.reg-form-grid .grid-col-2 {
    grid-column: 1 / -1 !important;
}

.reg-form-grid .select2-container {
    width: 100% !important;
}

@media(max-width: 768px) {
    .glass-modal .modal-dialog.modal-lg {
        max-width: 96% !important;
    }
    .reg-form-grid {
        grid-template-columns: 1fr !important;
        gap: 14px !important;
    }
    .reg-form-grid > * {
        grid-column: 1 / -1 !important;
    }
}

.glass-modal .modal-content {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 20px !important;
    box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05) !important;
    max-height: 88vh !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
}

.glass-modal form {
    display: flex !important;
    flex-direction: column !important;
    flex: 1 1 auto !important;
    min-height: 0 !important;
    overflow: hidden !important;
    margin: 0 !important;
}

.glass-modal .modal-header {
    flex-shrink: 0 !important;
    background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
    border: 0 !important;
    color: #fff !important;
    padding: 16px 22px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

.glass-modal .modal-title {
    color: #fff !important;
    font-weight: 850 !important;
    font-size: 17px !important;
    letter-spacing: -0.01em !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin: 0 !important;
}

.glass-modal .close {
    color: #fff !important;
    opacity: 0.85 !important;
    text-shadow: none !important;
    font-size: 20px !important;
    padding: 0 !important;
    margin: 0 !important;
    line-height: 1 !important;
    transition: opacity 0.2s ease, transform 0.2s ease !important;
    background: transparent !important;
    border: 0 !important;
}

.glass-modal .close:hover {
    opacity: 1 !important;
    transform: rotate(90deg) scale(1.1) !important;
}

.glass-modal .modal-body {
    flex: 1 1 auto !important;
    overflow-y: auto !important;
    max-height: calc(88vh - 135px) !important;
    padding: 20px 22px !important;
    background: #F8FAFC !important;
}

.glass-modal .modal-body::-webkit-scrollbar {
    width: 5px;
}

.glass-modal .modal-body::-webkit-scrollbar-track {
    background: #F1F5F9;
    border-radius: 999px;
}

.glass-modal .modal-body::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 999px;
}

.glass-modal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #94A3B8;
}

.glass-modal .modal-footer {
    flex-shrink: 0 !important;
    background: #FFFFFF !important;
    border-top: 1px solid #E2E8F0 !important;
    padding: 14px 22px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 10px !important;
}

/* Modal Internal Cards and Components */
.modal-emp-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 12px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

.modal-emp-info h6 {
    font-size: 15px;
    font-weight: 850;
    color: #0F172A;
    margin: 0 0 3px;
}

.modal-emp-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.modal-emp-code-pill {
    background: #F1F5F9;
    color: #475467;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 750;
}

.modal-emp-dept-pill {
    background: var(--orb-soft);
    color: var(--orb-primary);
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 750;
}

.detail-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 14px;
    padding: 14px 16px;
    margin-bottom: 12px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

.detail-card-title {
    font-size: 11.5px;
    font-weight: 850;
    color: var(--orb-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 12px;
}

.detail-item {
    background: #F8FAFC;
    border: 1px solid #EDF2F7;
    border-radius: 10px;
    padding: 8px 12px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 10.5px;
    font-weight: 750;
    color: var(--orb-muted);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.detail-value {
    font-size: 13px;
    font-weight: 800;
    color: var(--orb-text);
    word-break: break-word;
}

.detail-reason-box {
    background: #F8FAFC;
    border-left: 3.5px solid var(--orb-primary);
    border-radius: 0 10px 10px 0;
    padding: 12px 14px;
    color: #1E293B;
    font-size: 13px;
    line-height: 1.5;
    font-weight: 600;
}

.detail-reject-box {
    background: #FEF2F2;
    border-left: 3.5px solid #EF4444;
    border-radius: 0 10px 10px 0;
    padding: 12px 14px;
    color: #991B1B;
    font-size: 13px;
    line-height: 1.5;
    font-weight: 600;
}

.timeline-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 14px;
    padding: 14px 16px;
    margin-bottom: 0;
}

.timeline-item {
    position: relative;
    padding-left: 24px;
    padding-bottom: 12px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 6px;
    bottom: 0;
    width: 2px;
    background: #E2E8F0;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-item::after {
    content: '';
    position: absolute;
    left: 2px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--orb-primary);
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px var(--orb-soft);
}

.timeline-item.success::after {
    background: #10B981;
    box-shadow: 0 0 0 2px #D1FAE5;
}

.timeline-item.danger::after {
    background: #EF4444;
    box-shadow: 0 0 0 2px #FEE2E2;
}

.timeline-label {
    font-weight: 800;
    font-size: 12px;
    color: var(--orb-text);
}

.timeline-time {
    font-size: 11px;
    color: var(--orb-muted);
}

@media(max-width:1300px) {
    .att-metric-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media(max-width:1100px) {
    .att-filter-grid {
        flex-wrap: wrap;
    }
    .att-filter-grid .att-filter-item {
        flex: 1 1 calc(33.33% - 12px);
        min-width: 150px;
    }
    .att-filter-grid .att-filter-actions {
        flex: 1 1 100%;
        margin-top: 4px;
    }
}

@media(max-width:768px) {
    .att-page {
        padding: 12px 8px 25px;
    }
    .att-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 22px;
        border-radius: 24px;
    }
    .att-title {
        font-size: 25px;
    }
    .att-hero-actions {
        width: 100%;
    }
    .att-btn {
        width: 100%;
    }
    .att-metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .att-section-head {
        flex-direction: column;
        align-items: flex-start;
    }
    .att-head-badges {
        justify-content: flex-start;
    }
    .att-filter-grid .att-filter-item {
        flex: 1 1 100%;
    }
    .detail-grid {
        grid-template-columns: 1fr;
    }
}

@media(max-width:576px) {
    .glass-modal .modal-dialog {
        margin: 0.75rem auto !important;
        width: 96% !important;
    }
    .glass-modal .modal-body {
        padding: 14px !important;
    }
    .glass-modal .modal-header {
        padding: 14px 16px !important;
    }
    .glass-modal .modal-footer {
        padding: 12px 16px !important;
    }
}

.att-table td.dataTables_empty {
    text-align: center !important;
    padding: 40px 20px !important;
    color: var(--orb-muted, #64748B) !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    background: transparent !important;
}
</style>
