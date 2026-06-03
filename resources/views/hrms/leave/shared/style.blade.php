<style>
:root{
    --orb-bg:#F6F7FB;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 14px 35px rgba(16,24,40,.07);
}

body {
    background: #F6F7FB !important;
    overflow-x: hidden !important;
}

.leave-page,
.leave-page-wrap {
    min-height: calc(100vh - 90px) !important;
    background: #F6F7FB !important;
    padding: 24px !important;
    box-sizing: border-box !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 auto !important;
    overflow-x: hidden !important;
    display: block !important;
}

.leave-container {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
}

.leave-card,
.leave-table-card {
    border: 1px solid #E7EAF3 !important;
    border-radius: 22px !important;
    background: #fff !important;
    box-shadow: 0 14px 35px rgba(16,24,40,.07) !important;
    margin-bottom: 24px !important;
    overflow: hidden !important;
    padding: 0 !important;
}

.leave-card-head {
    padding: 18px 24px !important;
    border-bottom: 1px solid #E7EAF3 !important;
}

.leave-table-wrap {
    padding: 24px !important;
}

.leave-hero {
    position: relative !important;
    overflow: hidden !important;
    border-radius: 26px !important;
    padding: 26px 28px !important;
    background: radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
        linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #8600EE)) !important;
    color: #fff !important;
    box-shadow: 0 20px 45px rgba(75, 0, 232, .22) !important;
    margin-bottom: 24px !important;
    border: 0 !important;
}

.leave-hero::after {
    content: '' !important;
    position: absolute !important;
    width: 230px !important;
    height: 230px !important;
    border-radius: 50% !important;
    right: -95px !important;
    bottom: -115px !important;
    background: rgba(255, 255, 255, .10) !important;
    z-index: 1 !important;
}

.leave-hero-content {
    position: relative !important;
    z-index: 2 !important;
}

.leave-hero-title {
    font-size: 28px !important;
    font-weight: 950 !important;
    color: #fff !important;
    margin: 0 !important;
    letter-spacing: -.03em !important;
}

.leave-hero-subtitle {
    margin: 6px 0 0 !important;
    color: rgba(255, 255, 255, .84) !important;
    font-size: 13px !important;
    line-height: 1.6 !important;
}

.leave-title{font-size:26px;font-weight:950;color:var(--orb-text);margin:0;}
.leave-subtitle{font-size:13px;color:var(--orb-muted);margin:5px 0 0;}

.leave-btn{
    border:0;
    border-radius:14px;
    padding:10px 16px;
    font-weight:900;
    display:inline-flex;
    gap:8px;
    align-items:center;
    justify-content:center;
    text-decoration:none!important;
}

.leave-btn-primary{background:linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));color:#fff!important;}
.leave-btn-light{background:#fff;color:var(--orb-text);border:1px solid var(--orb-border);}
.leave-btn-danger{background:linear-gradient(135deg,#ec4e74,#ff7675);color:#fff!important;}
.leave-btn-success{background:linear-gradient(135deg,#10b981,#059669);color:#fff!important;}
.leave-btn-warning{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff!important;}

.leave-table-wrap{padding:16px;}
.leave-table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;}

.leave-table{
    width:100%;
    min-width:900px;
    border-collapse:collapse!important;
}

.leave-table th{
    background:#F8FAFC;
    color:#475467;
    font-size:11px;
    font-weight:950;
    text-transform:uppercase;
    padding:13px 14px;
    border-top:1px solid #EAECF0;
    border-bottom:1px solid #EAECF0;
    white-space:nowrap;
}

.leave-table td{
    background:#fff;
    border-bottom:1px solid #EEF2F6;
    padding:14px;
    vertical-align:middle;
}

.leave-table tbody tr:hover td{background:#FAF8FF;}

.leave-badge{
    display:inline-flex;
    align-items:center;
    border-radius:999px;
    padding:6px 11px;
    font-size:11px;
    font-weight:950;
    text-transform:uppercase;
    white-space:nowrap;
}

/* Premium Badges mapped from requirements */
.badge-active{background:#dcfce7;color:#166534}
.badge-muted{background:#f1f5f9;color:#475569}
.badge-paid{background:#dbeafe;color:#1e40af}
.badge-unpaid{background:#fee2e2;color:#991b1b}

.badge-approved{background:#dcfce7;color:#166534}
.badge-pending{background:#ffedd5;color:#c2410c}
.badge-rejected{background:#fee2e2;color:#991b1b}
.badge-cancelled{background:#f1f5f9;color:#475569}
.badge-half-day{background:#dbeafe;color:#1e40af}
.badge-lwp{background:#7f1d1d;color:#fecaca}
.badge-comp-off{background:#f3e8ff;color:#7e22ce}

.type-dot{
    width:16px;
    height:16px;
    border-radius:999px;
    display:inline-block;
    border:1px solid rgba(0,0,0,.08);
    vertical-align:middle;
    margin-right:8px;
}

.leave-actions{display:flex;gap:7px;justify-content:flex-end;}

.icon-btn{
    width:37px;
    height:37px;
    border-radius:12px;
    border:1px solid var(--orb-border);
    background:#fff;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    color: var(--orb-muted);
}
.icon-btn:hover { background: #f8fafc; color: var(--orb-primary); }

/* Premium Modal Fix */
.modal-backdrop{
    z-index:1240!important;
    background:#0F172A!important;
}

.modal-backdrop.show{opacity:.58!important;}
.modal{z-index:1250!important;}

.orb-type-modal .modal-dialog{
    max-width:620px;
}

.leave-modal-content{
    border:0 !important;
    border-radius:24px !important;
    overflow:hidden !important;
    background:#fff!important;
    box-shadow:0 24px 70px rgba(16, 24, 40, 0.25) !important;
}

.leave-modal-header{
    padding: 22px 26px !important;
    background:linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
    color:#fff !important;
    border-bottom:0 !important;
    display:flex !important;
    align-items:center !important;
    justify-content:space-between !important;
}

.leave-modal-title{
    margin:0 !important;
    font-size:1.25rem !important;
    font-weight:800 !important;
    color:#fff !important;
}

.leave-modal-subtitle{
    margin-top:4px !important;
    font-size:13px !important;
    color:rgba(255,255,255,.8) !important;
    font-weight:550 !important;
}

.leave-modal-header .close{
    color:#fff !important;
    opacity:1 !important;
    text-shadow:none !important;
    outline:none !important;
    border:0 !important;
    background:transparent !important;
    font-size:24px !important;
    padding:0 !important;
}

.leave-modal-body{
    padding:26px !important;
    background:#F6F7FB !important;
}

.leave-modal-body label{
    font-size: 12px !important;
    font-weight: 750 !important;
    color: #344054 !important;
    margin-bottom: 6px !important;
    display: block !important;
    text-transform: none !important;
    letter-spacing: normal !important;
}

.leave-modal-body .form-control, .leave-modal-body .form-select{
    display: block !important;
    width: 100% !important;
    height: 44px !important;
    padding: 10px 16px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    line-height: 1.5 !important;
    color: #101828 !important;
    background-color: #ffffff !important;
    border: 1px solid #E7EAF3 !important;
    border-radius: 12px !important;
    box-shadow: none !important;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out !important;
}

.leave-modal-body .form-control[type="color"]{
    padding:6px !important;
}
.leave-modal-body textarea.form-control {
    height: auto !important;
}

.leave-modal-body .form-control:focus, .leave-modal-body .form-select:focus{
    border-color:var(--orb-primary) !important;
    box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.1) !important;
    outline: 0 !important;
}

.leave-modal-section{
    background: #ffffff !important;
    border: 1px solid #E7EAF3 !important;
    border-radius: 20px !important;
    padding: 20px !important;
    margin-bottom: 16px !important;
}

.leave-modal-section-title{
    font-size: 14px !important;
    font-weight: 850 !important;
    color: #101828 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    margin-bottom: 16px !important;
    border-bottom: 1px solid #E7EAF3 !important;
    padding-bottom: 8px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.leave-modal-section-title i{color:var(--orb-primary) !important;}

.leave-modal-footer{
    padding: 16px 26px !important;
    background: #ffffff !important;
    border-top: 1px solid #E7EAF3 !important;
    display: flex !important;
    justify-content: flex-end !important;
    gap: 12px !important;
    border-bottom-left-radius: 24px !important;
    border-bottom-right-radius: 24px !important;
}

/* Filters */
.leave-filters {
    padding: 16px;
    background: #FAFBFF;
    border-bottom: 1px solid var(--orb-border);
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}
.leave-filter-select {
    border-radius: 10px;
    border: 1px solid #E4E7EC;
    padding: 8px 12px;
    font-size: 13px;
    background: #fff;
    min-width: 150px;
}
.leave-filter-select:focus {
    outline: none;
    border-color: var(--orb-primary);
}

/* Floating labels */
.form-floating {
    position: relative;
}
.form-floating > .form-control,
.form-floating > .form-select {
    height: calc(3.5rem + 2px);
    line-height: 1.25;
}
.form-floating > label {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    padding: 1rem 0.75rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    pointer-events: none;
    border: 1px solid transparent;
    transform-origin: 0 0;
    transition: opacity .1s ease-in-out,transform .1s ease-in-out;
}
.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label,
.form-floating > .form-select ~ label {
    opacity: .65;
    transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
}

/* Dropdown Action Menu */
.dropdown-menu.leave-action-menu {
    border-radius: 14px;
    border: 1px solid var(--orb-border);
    box-shadow: var(--orb-shadow);
    padding: 8px;
}
.dropdown-menu.leave-action-menu .dropdown-item {
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 600;
    color: var(--orb-text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.dropdown-menu.leave-action-menu .dropdown-item:hover {
    background: #F4F2FF;
    color: var(--orb-primary);
}
.dropdown-menu.leave-action-menu .dropdown-item.text-danger:hover {
    background: #fee2e2;
    color: #991b1b !important;
}

@media (max-width: 991px) {
    .leave-page,
    .leave-page-wrap {
        padding: 18px !important;
    }
    .leave-hero {
        padding: 20px 22px !important;
        border-radius: 22px !important;
    }
}

@media (max-width: 575px) {
    .leave-page,
    .leave-page-wrap {
        padding: 12px !important;
    }
    .leave-hero {
        padding: 16px 18px !important;
        border-radius: 20px !important;
        margin-bottom: 18px !important;
    }
    .leave-hero-title {
        font-size: 22px !important;
    }
    .leave-table-wrap {
        padding: 14px !important;
    }
}

/* Summary Cards */
.leave-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 18px;
}
.summary-card {
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    border: 1px solid var(--orb-border);
    box-shadow: var(--orb-shadow);
    display: flex;
    align-items: center;
    gap: 16px;
}
.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.summary-info h4 {
    margin: 0;
    font-size: 24px;
    font-weight: 900;
    color: var(--orb-text);
}
.summary-info p {
    margin: 0;
    font-size: 12px;
    font-weight: 700;
    color: var(--orb-muted);
    text-transform: uppercase;
    letter-spacing: .05em;
}
</style>
