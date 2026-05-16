<style>
:root{
    --orb-primary:#4B00E8;
    --orb-secondary:#8600EE;
    --orb-bg:#F6F7FB;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 14px 35px rgba(16,24,40,.07);
}

.leave-page{min-height:calc(100vh - 90px);padding:18px 12px 35px;background:var(--orb-bg);}
.leave-container{max-width:1280px;margin:0 auto;}
.leave-card{background:#fff;border:1px solid var(--orb-border);border-radius:24px;box-shadow:var(--orb-shadow);overflow:hidden;}

.leave-header{
    padding:22px;
    margin-bottom:18px;
    background:linear-gradient(135deg,#fff,#f8f5ff);
    border:1px solid var(--orb-border);
    border-radius:26px;
    box-shadow:var(--orb-shadow);
    display:flex;
    justify-content:space-between;
    gap:16px;
    align-items:center;
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

.leave-btn-primary{background:linear-gradient(135deg,var(--orb-primary),var(--orb-secondary));color:#fff!important;}
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
    border:0;
    border-radius:24px;
    overflow:hidden;
    background:#fff!important;
    box-shadow:0 24px 70px rgba(15,23,42,.28);
}

.leave-modal-header{
    padding:18px 22px;
    background:linear-gradient(135deg,#4B00E8,#8600EE);
    color:#fff;
    border-bottom:0;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.leave-modal-title{
    margin:0;
    font-size:18px;
    font-weight:900;
}

.leave-modal-subtitle{
    margin-top:3px;
    font-size:12px;
    color:rgba(255,255,255,.78);
}

.leave-modal-header .close{
    color:#fff;
    opacity:1;
    text-shadow:none;
    outline:none;
}

.leave-modal-body{
    padding:22px;
    background:#fff!important;
}

.leave-modal-body label{
    font-size:11px;
    font-weight:900;
    color:#667085;
    text-transform:uppercase;
    letter-spacing:.04em;
}

.leave-modal-body .form-control, .leave-modal-body .form-select{
    height:43px;
    border-radius:13px;
    border:1px solid #E4E7EC;
    font-size:13px;
    background:#fff;
}

.leave-modal-body .form-control[type="color"]{
    padding:6px;
}
.leave-modal-body textarea.form-control {
    height: auto;
}

.leave-modal-body .form-control:focus, .leave-modal-body .form-select:focus{
    border-color:var(--orb-primary);
    box-shadow:0 0 0 .15rem rgba(75,0,232,.12);
}

.leave-modal-section{
    border:1px solid #EEF2F6;
    background:#FAFBFF;
    border-radius:18px;
    padding:16px;
    margin-bottom:16px;
}

.leave-modal-section-title{
    font-size:13px;
    font-weight:950;
    color:var(--orb-text);
    margin-bottom:14px;
    display:flex;
    align-items:center;
    gap:8px;
}

.leave-modal-section-title i{color:var(--orb-primary);}

.leave-modal-footer{
    padding:16px 22px;
    background:#F8FAFC;
    border-top:1px solid #EEF2F6;
    display:flex;
    justify-content:flex-end;
    gap:10px;
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

@media(max-width:768px){
    .leave-header{flex-direction:column;align-items:flex-start}
    .leave-page{padding:12px 8px 25px;}
    .leave-title{font-size:22px;}
    .orb-type-modal .modal-dialog{margin:12px;}
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
