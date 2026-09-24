<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
:root {
    --orb-primary-hover: #3A00B7;
    --orb-bg: #F8FAFC;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-muted: #64748B;
    --orb-soft: #F1EDFF;
    --orb-shadow: 0 10px 30px -10px rgba(75, 0, 232, 0.08);
}

body {
    overflow-x: hidden !important;
}

#regularizationDataTable thead th[colspan]::before,
#regularizationDataTable thead th[colspan]::after,
#regularizationDataTable thead tr:first-child th[colspan]::before,
#regularizationDataTable thead tr:first-child th[colspan]::after {
    display: none !important;
    content: "" !important;
}
#regularizationDataTable thead th[colspan] {
    cursor: default !important;
    pointer-events: none !important;
    padding: 3px 6px !important;
}
#regularizationDataTable thead th {
    vertical-align: middle !important;
    padding: 4px 8px !important;
    line-height: 1.2 !important;
    font-size: 11px !important;
}
#regularizationDataTable thead tr {
    height: auto !important;
}

.table-responsive-wrap,
.att-table-responsive {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 12px;
}
#regularizationDataTable {
    width: 100% !important;
    min-width: 1400px !important;
    table-layout: auto !important;
}

.dataTables_length {
    margin-bottom: 0 !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    color: var(--orb-muted) !important;
}
.dataTables_length label {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-bottom: 0 !important;
    font-size: 13px !important;
    font-weight: 700 !important;
}
.dataTables_length select,
.dataTables_length select.custom-select,
.dataTables_length select.form-control {
    height: 34px !important;
    min-width: 68px !important;
    padding: 2px 28px 2px 10px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    color: var(--orb-text) !important;
    border-radius: 8px !important;
    border: 1px solid var(--orb-border) !important;
    background-color: #fff !important;
    line-height: 1.4 !important;
    vertical-align: middle !important;
    display: inline-block !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
}

.att-page {
    min-height: calc(100vh - 90px);
    background: var(--orb-bg);
    padding: 24px 20px 48px;
}

.att-container {
    max-width: 1480px;
    margin: 0 auto;
}

.att-hero {
    background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    border-radius: 24px !important;
    padding: 36px;
    margin-bottom: 24px;
    box-shadow: 0 20px 50px rgba(75, 0, 232, 0.15);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
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
    background: rgba(255, 255, 255, 0.1);
    pointer-events: none;
}

.att-kicker {
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    opacity: 0.9;
    margin-bottom: 8px;
    display: flex;
    gap: 8px;
    align-items: center;
}

.att-title {
    font-size: 32px;
    font-weight: 900;
    margin: 0;
    line-height: 1.2;
    color: #fff;
}

.att-subtitle {
    font-size: 15px;
    opacity: 0.9;
    margin-top: 8px;
    max-width: 800px;
}

.att-hero-actions {
    display: flex;
    gap: 12px;
    position: relative;
    z-index: 1;
}

.att-btn {
    border-radius: 12px;
    padding: 12px 20px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none !important;
    white-space: nowrap;
    border: 0;
    cursor: pointer;
    transition: all 0.2s ease;
}

.att-btn-light {
    background: #fff;
    color: var(--orb-primary) !important;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
}

.att-btn-light:hover {
    background: var(--orb-soft);
    transform: translateY(-1px);
}

.att-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 20px !important;
    box-shadow: var(--orb-shadow);
    overflow: hidden !important;
}

.att-section-head {
    padding: 24px;
    border-bottom: 1px solid var(--orb-border);
    background: linear-gradient(180deg, #fff, #FCFDFF);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.att-section-title {
    font-size: 20px;
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
    margin-top: 4px;
}

.att-head-badges {
    display: flex;
    gap: 10px;
}

.att-total-pill {
    border: 1px solid var(--orb-border);
    background: #F8FAFC;
    color: var(--orb-text);
    border-radius: 12px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.att-total-pill.purple {
    border-color: #E2D9FF;
    background: #F5F1FF;
    color: var(--orb-primary);
}

.att-filter-panel {
    padding: 12px 20px !important;
    border-bottom: 1px solid var(--orb-border);
    background: #fff;
}

.att-filter-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 10px !important;
    align-items: end;
}

.att-filter-grid label {
    font-size: 10px !important;
    font-weight: 800 !important;
    color: var(--orb-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 3px !important;
    display: block;
}

.att-filter-grid .form-control,
.att-filter-grid .custom-select {
    height: 36px !important;
    border-radius: 8px !important;
    border: 1px solid var(--orb-border);
    font-size: 12px !important;
    font-weight: 600;
    padding: 0 10px !important;
    box-shadow: none !important;
    background: #fff;
    width: 100%;
}

.att-filter-grid .form-control:focus,
.att-filter-grid .custom-select:focus {
    border-color: var(--orb-primary);
}

.att-table-wrap {
    padding: 16px;
}

.att-table-responsive {
    width: 100% !important;
    overflow-x: auto !important;
}

.att-table {
    width: 100% !important;
    border-collapse: collapse !important;
}

.att-table thead th {
    background: #F8FAFC !important;
    color: var(--orb-muted) !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    padding: 16px 14px !important;
    border-top: 1px solid var(--orb-border) !important;
    border-bottom: 1px solid var(--orb-border) !important;
    white-space: nowrap;
}

.att-table td {
    background: #fff;
    border-bottom: 1px solid #F1F5F9 !important;
    padding: 16px 14px !important;
    vertical-align: middle;
    font-size: 14px;
    color: var(--orb-text);
}

.att-table tbody tr {
    transition: 0.2s ease;
}

.att-table tbody tr:hover td {
    background: #FAF9FF;
}

.orb-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    border: 1px solid transparent;
}

.orb-badge-success {
    background: #ECFDF3;
    color: #027A48;
    border-color: #ABEFC6;
}

.orb-badge-warning {
    background: #FFFAEB;
    color: #B54708;
    border-color: #FEDF89;
}

.orb-badge-danger {
    background: #FEF3F2;
    color: #B42318;
    border-color: #FECDCA;
}

.orb-badge-secondary {
    background: #F2F4F7;
    color: #344054;
    border-color: #D0D5DD;
}

.orb-badge-primary {
    background: var(--orb-soft);
    color: var(--orb-primary);
    border-color: rgba(75, 0, 232, 0.15);
}

.orb-action-btn {
    width: 36px;
    height: 36px;
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

/* Glassmorphism View Details Modal styling */
.glass-modal .modal-content {
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(20px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
    border: 1px solid rgba(255, 255, 255, 0.5) !important;
    border-radius: 24px !important;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.15) !important;
}

.glass-modal .modal-header {
    background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
    border: 0 !important;
    color: #fff !important;
    padding: 24px !important;
}

.glass-modal .modal-title {
    color: #fff !important;
    font-weight: 900 !important;
    font-size: 20px !important;
}

.glass-modal .close {
    color: #fff !important;
    opacity: 0.8;
}

.glass-modal .close:hover {
    opacity: 1;
}

.glass-modal .modal-body {
    padding: 24px !important;
    background: rgba(255, 255, 255, 0.4) !important;
}

.detail-card {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.detail-card-title {
    font-size: 13px;
    font-weight: 800;
    color: var(--orb-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 12px;
    border-bottom: 1px solid #EDF2F7;
    padding-bottom: 6px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #F1F5F9;
}

.detail-row:last-child {
    border-bottom: 0;
}

.detail-label {
    font-weight: 600;
    color: var(--orb-muted);
    font-size: 13px;
}

.detail-value {
    font-weight: 800;
    color: var(--orb-text);
    font-size: 13px;
}

.timeline-item {
    position: relative;
    padding-left: 28px;
    padding-bottom: 16px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 8px;
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
    left: 4px;
    top: 6px;
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
    font-size: 13px;
    color: var(--orb-text);
}

.timeline-time {
    font-size: 11px;
    color: var(--orb-muted);
}

@media(max-width: 1300px) {
    .att-filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media(max-width: 768px) {
    .att-page {
        padding: 16px 12px 32px;
    }

    .att-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 24px;
        border-radius: 20px;
    }

    .att-title {
        font-size: 26px;
    }

    .att-hero-actions {
        width: 100%;
    }

    .att-btn {
        width: 100%;
    }

    .att-section-head {
        flex-direction: column;
        align-items: flex-start;
        padding: 18px 24px;
    }

    .att-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media(max-width: 480px) {
    .att-filter-grid {
        grid-template-columns: 1fr;
    }
}

/* Custom modal layout and compact columns adjustments */
.glass-modal .modal-dialog {
    max-width: 900px !important;
    width: 90% !important;
    margin: 1.75rem auto !important;
}
.glass-modal .modal-content {
    max-height: 82vh !important;
    overflow: hidden !important;
    height: auto !important;
    border-radius: 24px !important;
}
.glass-modal .modal-header {
    padding: 14px 20px !important;
}
.glass-modal .modal-body {
    max-height: calc(82vh - 120px) !important;
    overflow-y: auto !important;
    padding: 20px !important;
}
.att-table-responsive {
    width: 100% !important;
    overflow-x: visible !important;
    overflow-y: visible !important;
}
.dataTables_wrapper {
    width: 100% !important;
    overflow-x: visible !important;
}
.dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    width: 100% !important;
}
.dataTables_paginate,
.dataTables_info,
.regularization-pagination {
    overflow-x: hidden !important;
}
.dt-buttons.btn-group {
    display: inline-flex !important;
    gap: 8px !important;
    float: right !important;
}
.dt-buttons .btn {
    border-radius: 10px !important;
    padding: 6px 14px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    border: 1px solid var(--orb-border) !important;
    background: #fff !important;
    color: var(--orb-text) !important;
    box-shadow: none !important;
}
.dt-buttons .btn:hover {
    background: var(--orb-soft) !important;
    color: var(--orb-primary) !important;
}
.dataTables_length {
    float: left !important;
    margin-bottom: 0 !important;
}
.dataTables_length select {
    height: 34px !important;
    border-radius: 8px !important;
    border: 1px solid var(--orb-border) !important;
    padding: 0 8px !important;
}
.dataTables_info {
    padding-top: 8px !important;
}
</style>
