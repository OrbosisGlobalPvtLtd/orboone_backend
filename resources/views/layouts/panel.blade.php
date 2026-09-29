@extends('layouts.app')

@section('title', $branding['company_name'] ?? config('app.name', 'OrboOne HRMS'))

@section('head')
<style>
    :root{
        --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
        --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
        --primary: var(--orb-primary);
        --primary-2: var(--orb-secondary);
        
        --primary-3:#D400D5;
        --primary-4:#EC4E74;
        --primary-5:#FFB101;

        --primary-light:#F3EDFF;
        --bg:#F6F7FB;
        --white:#FFFFFF;
        --border:#E7EAF3;
        --text:#111827;
        --muted:#6B7280;

        --sidebar-width: 260px;
        --sidebar-collapsed: 72px;
        --topbar-height: 56px;
        --mobile-sidebar-width: min(86vw, 290px);
        --radius: 14px;
        --shadow: 0 8px 24px rgba(15,23,42,.06);
    }

    html, body{
        background:var(--bg);
    }

    body.panel-open{
        overflow:hidden;
    }

    .panel-layout{
        min-height:100vh;
        background:var(--bg);
        width: 100%;
        max-width: 100vw;
        overflow-x: clip;
    }

    /* ========== SIDEBAR ========== */
    .sidebar{
        --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
        --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
        position:fixed;
        top:0;
        left:0;
        bottom:0;
        width:var(--sidebar-width);
        height:100vh;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.12), transparent 30%),
            radial-gradient(circle at bottom left, rgba(255,255,255,.08), transparent 25%),
            linear-gradient(
                180deg,
                var(--orb-primary) 0%,
                var(--orb-primary) 22%,
                var(--orb-secondary) 100%
            ) !important;
        z-index:1200;
        transition:width .28s ease, transform .28s ease;
        overflow:hidden;
        box-shadow:0 16px 40px rgba(75, 0, 232, 0.22);
        display:flex;
        flex-direction:column;
    }

    .sidebar-header{
        position: relative;
        z-index: 5;
        height: 60px;
        padding: 0 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border-bottom: 1px solid #eef1f7;
    }

    .brand{
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .brand-logo-box{
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .brand-logo{
        max-height: 40px;
        max-width: 155px;
        object-fit: contain;
        display: block;
        margin: 0 auto;
    }

    .brand-text{
        min-width:0;
    }

    .brand-title{
        color:var(--orb-primary);
        font-size:21px;
        font-weight:800;
        line-height:1;
        white-space:nowrap;
    }

    .brand-subtitle{
        color:#6B7280;
        font-size:12px;
        margin-top:4px;
        white-space:nowrap;
        font-weight:700;
    }

    .sidebar-close{
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 10px;
    background: #f3f4f6;
    color: #111827;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

    /* .sidebar-body{
        position:relative;
        z-index:2;
        flex:1;
        overflow-y:auto;
        padding:8px 12px 12px;
    } */
        .sidebar-body{
    flex:1;
    overflow-y:auto;
    padding:10px 12px 12px;
}

    .sidebar-body::-webkit-scrollbar{
        width:6px;
    }

    .sidebar-body::-webkit-scrollbar-thumb{
        background:rgba(255,255,255,0.22);
        border-radius:999px;
    }

    .menu-label{
        padding:6px 10px 8px;
        margin:0 0 6px 0;
        font-size:10px;
        text-transform:uppercase;
        letter-spacing:.14em;
        color:rgba(255,255,255,0.82);
        font-weight:800;
    }

    /* ========== MODULE SWITCHER ========== */
    .module-switcher{
        display:grid;
        grid-template-columns:repeat(2, minmax(0,1fr));
        gap:8px;
        margin-bottom:10px;
    }

    .module-switch-item{
        min-height:44px;
        border-radius:14px;
        display:flex;
        align-items:center;
        gap:10px;
        padding:0 12px;
        color:rgba(255,255,255,0.92);
        font-weight:700;
        position:relative;
        overflow:hidden;
        transition:.22s ease;
        background:rgba(255,255,255,0.08);
        border:1px solid rgba(255,255,255,0.12);
        backdrop-filter:blur(8px);
    }

    .module-switch-item:hover{
        color:#fff;
        transform:translateY(-1px);
        background:rgba(255,255,255,0.15);
    }

    .module-switch-icon{
        width:16px;
        min-width:16px;
        text-align:center;
        font-size:13px;
    }

    .module-switch-text{
        white-space:nowrap;
        transition:.2s ease;
        font-size:13px;
    }

    .module-switch-item.active{
        background:#fff;
        color:var(--orb-primary);
        border-color:#fff;
        box-shadow:0 10px 24px rgba(17,24,39,0.14);
    }

    .module-switch-item.active.crm{ color:#EC4E74; }
    .module-switch-item.active.pm{ color:#14b87a; }
    .module-switch-item.active.fin{ color:#d89600; }

    /* ========== MAIN MENU ========== */
    .menu{
        display:flex;
        flex-direction:column;
        gap:4px;
    }

    .sidebar a,
    .sidebar a:hover,
    .sidebar a:focus,
    .sidebar a:active,
    .menu > a,
    .menu > a:hover,
    .menu > a:focus,
    .menu > a:active,
    .sidebar-group-toggle,
    .sidebar-group-toggle:hover,
    .sidebar-group-toggle:focus,
    .sidebar-group-toggle:active,
    .sub-link,
    .sub-link:hover,
    .sub-link:focus,
    .sub-link:active {
        text-decoration: none !important;
    }

    .menu > a,
    .sidebar-group-toggle{
        width:100%;
        min-width:0;
        min-height:40px;
        border:none;
        border-radius:10px;
        display:flex;
        align-items:center;
        gap:8px;
        padding:0 8px;
        box-sizing:border-box;
        background:transparent;
        color:rgba(255,255,255,0.92);
        font-weight:700;
        text-align:left;
        transition:color .2s ease, background .2s ease, transform .2s ease;
        position:relative;
        overflow:hidden;
        text-decoration:none !important;
    }

    .menu > a::before,
    .sidebar-group-toggle::before{
        content:"";
        position:absolute;
        inset:0;
        background:rgba(255,255,255,0.14);
        opacity:0;
        transition:.2s ease;
        border-radius:inherit;
    }

    .menu > a:hover::before,
    .sidebar-group-toggle:hover::before{
        opacity:1;
    }

    .menu > a:hover,
    .sidebar-group-toggle:hover{
        color:#fff;
        transform:translateX(2px);
        text-decoration:none !important;
    }

    .menu > a.active{
        background:rgba(255,255,255,0.95);
        color:var(--orb-primary);
        box-shadow:0 10px 24px rgba(17,24,39,0.14);
    }

    .menu > a.active .menu-icon{
        color:var(--orb-primary);
    }

    .menu-icon{
        flex:0 0 20px;
        width:20px;
        min-width:20px;
        max-width:20px;
        height:20px;
        text-align:center;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;
        font-size:15px;
        position:relative;
        z-index:1;
    }

    .menu-icon i,
    .menu-icon svg{
        font-size:15px;
        width:18px;
        max-width:18px;
        max-height:16px;
        text-align:center;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        line-height:1;
        margin:0 auto;
    }

    .menu-text{
        flex:1 1 0;
        min-width:0;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        font-size:13px;
        font-weight:700;
        line-height:1.25;
        transition:opacity 0.2s ease;
    }

    .group-chevron{
        flex:0 0 16px;
        width:16px;
        min-width:16px;
        margin-left:auto;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;
        font-size:11px;
        transition:transform .24s ease;
        position:relative;
        z-index:1;
    }

    .sidebar-group.open .group-chevron{
        transform:rotate(180deg);
    }

    .menu-badge{
        font-size:10px;
        font-weight:900;
        padding:2px 6px;
        border-radius:6px;
        background:rgba(255,255,255,0.15);
        color:#fff;
        position:relative;
        z-index:1;
    }

    .menu > a.active .menu-badge{
        background:var(--orb-primary);
        color:#fff;
    }

    .sidebar-group{
        margin-bottom:0;
    }

    .sidebar-group + .sidebar-group{
        border-top:none;
        padding-top:0;
    }

    .sidebar-group.open .sidebar-group-toggle{
        background:rgba(255,255,255,0.05);
    }

    .sidebar-submenu{
        display:none;
        padding:2px 0 2px 8px;
    }

    .sidebar-group.open .sidebar-submenu,
    .sidebar-submenu.show{
        display:block !important;
    }

    .sub-link{
        display:flex;
        align-items:center;
        gap:8px;
        min-height:32px;
        padding:0 8px;
        border-radius:8px;
        color:rgba(255,255,255,0.92);
        font-size:12.5px;
        font-weight:700;
        transition:.2s ease;
        text-decoration:none;
        box-sizing:border-box;
        width:100%;
        min-width:0;
    }

    .sub-link:hover{
        background:rgba(255,255,255,0.14);
        color:#fff;
    }

    .sub-link.active{
        background:rgba(255,255,255,0.95);
        color:var(--orb-primary);
        font-weight:800;
        box-shadow:0 8px 18px rgba(17,24,39,0.12);
    }

    .sub-link-icon{
        flex:0 0 16px;
        width:16px;
        min-width:16px;
        max-width:16px;
        height:16px;
        text-align:center;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;
        font-size:12px;
    }

    .sub-link-icon i,
    .sub-link-icon svg{
        font-size:12px;
        width:14px;
        max-width:14px;
        max-height:14px;
        text-align:center;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        line-height:1;
        margin:0 auto;
    }

    .sub-link-text{
        flex:1 1 0;
        min-width:0;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        font-size:12.5px;
        font-weight:700;
        line-height:1.25;
    }

    .submenu-divider{
        height:1px;
        margin:8px 10px 4px 10px;
        background:rgba(255,255,255,0.18);
    }

    /* ========== FOOTER ========== */
    .sidebar-footer{
        position:relative;
        z-index:2;
        padding:12px 14px 16px;
        border-top:1px solid rgba(255,255,255,0.14);
        color:rgba(255,255,255,0.95);
        background:rgba(0,0,0,0.07);
        backdrop-filter:blur(8px);
    }

    .sidebar-footer-title{
        font-size:12px;
        font-weight:800;
        line-height:1.2;
    }

    .sidebar-footer-sub{
        font-size:11px;
        margin-top:4px;
        color:rgba(255,255,255,0.76);
        font-weight:600;
    }

    /* ========== COLLAPSED ========== */
    body.desktop-collapsed .sidebar{
        width:var(--sidebar-collapsed);
    }

    body.desktop-collapsed .panel-main{
        margin-left:var(--sidebar-collapsed);
    }

    body.desktop-collapsed .topbar{
        left:var(--sidebar-collapsed);
    }

    body.desktop-collapsed .brand-text,
    body.desktop-collapsed .menu-label,
    body.desktop-collapsed .menu-text,
    body.desktop-collapsed .module-switch-text,
    body.desktop-collapsed .group-chevron,
    body.desktop-collapsed .menu-badge,
    body.desktop-collapsed .sidebar-footer-title,
    body.desktop-collapsed .sidebar-footer-sub,
    body.desktop-collapsed .sidebar-submenu{
        display:none !important;
    }

    body.desktop-collapsed .sidebar-header{
        padding:14px 10px 12px;
        justify-content:center;
    }

    body.desktop-collapsed .brand{
        justify-content:center;
    }

    body.desktop-collapsed .brand-logo-box{
        width:100%;
        min-height:40px;
        padding:4px;
        justify-content:center;
    }

    body.desktop-collapsed .module-switcher{
        grid-template-columns:1fr;
        gap:8px;
    }

    body.desktop-collapsed .module-switch-item,
    body.desktop-collapsed .menu > a,
    body.desktop-collapsed .sidebar-group-toggle{
        justify-content:center;
        padding:0;
    }

    body.desktop-collapsed .sidebar-footer{
        display:flex;
        align-items:center;
        justify-content:center;
        min-height:50px;
        padding:10px;
    }

    /* ========== MAIN / TOPBAR ========== */
    .panel-main{
        min-height:100vh;
        margin-left:var(--sidebar-width);
        transition:margin-left .28s ease;
        max-width: 100%;
        overflow-x: clip;
    }

    .topbar{
        position:fixed;
        top:0;
        left:var(--sidebar-width);
        right:0;
        height:var(--topbar-height);
        background:rgba(255,255,255,0.94);
        backdrop-filter:blur(12px);
        -webkit-backdrop-filter:blur(12px);
        border-bottom:1px solid var(--border);
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:0 16px;
        z-index:1100;
        transition:left .28s ease;
    }

    .topbar-left{
        display:flex;
        align-items:center;
        gap:14px;
        min-width:0;
    }

    .sidebar-toggle{
        width:44px;
        height:44px;
        border:none;
        border-radius:14px;
        background:#F3F4F6;
        color:#111827;
        cursor:pointer;
        font-size:18px;
        display:flex;
        align-items:center;
        justify-content:center;
        transition:.2s ease;
    }

    .sidebar-toggle:hover{
        background:#F3EDFF;
        color:var(--orb-primary);
    }

    .page-title{
        font-size:20px;
        font-weight:800;
        color:var(--text);
        white-space:nowrap;
    }

    .topbar-right{
        display:flex;
        align-items:center;
        gap:12px;
    }

    .profile-chip{
        height:46px;
        padding:0 16px;
        border-radius:999px;
        border:1px solid var(--border);
        background:#fff;
        display:flex;
        align-items:center;
        gap:10px;
        font-weight:700;
        color:#111827;
        box-shadow:0 4px 14px rgba(15,23,42,.04);
    }

    .profile-dot{
        width:10px;
        height:10px;
        border-radius:50%;
        background:var(--orb-primary);
        flex-shrink:0;
    }

    /* .page-content{
        padding:calc(var(--topbar-height) + 20px) 20px 20px;
    } */

    .overlay{
        position:fixed;
        inset:0;
        background:rgba(2,6,23,.52);
        opacity:0;
        visibility:hidden;
        transition:.25s ease;
        z-index:1150;
    }

    .overlay.show{
        opacity:1;
        visibility:visible;
    }

    @media (max-width: 992px){
        .sidebar{
            transform:translateX(-100%);
            width:min(88vw, 300px) !important;
            min-width:min(280px, 94vw) !important;
            max-width:100vw;
            box-shadow:0 20px 50px rgba(0,0,0,.35);
        }

        .sidebar.show{
            transform:translateX(0);
        }

        .sidebar-close{
            display: flex !important;
        }

        .panel-main{
            margin-left:0 !important;
        }

        .topbar{
            left:0 !important;
            width:100%;
        }

        .menu-label{
            display: block !important;
        }

        .sidebar-submenu{
            display: none;
        }

        .sidebar-submenu.show{
            display: block !important;
        }
    }

    @media (max-width: 640px){
        .topbar{
            padding:0 14px;
        }

        /* .page-content{
            padding:calc(var(--topbar-height) + 14px) 14px 14px;
        } */

        .page-title{
            font-size:17px;
        }

        .profile-chip{
            padding:0 12px;
            font-size:13px;
        }

        .module-switcher{
            gap:6px;
        }

        .module-switch-item{
            min-height:42px;
            padding:0 10px;
            font-size:12px;
        }

        .menu > a,
        .sidebar-group-toggle{
            min-height:38px;
        }
    }

    /* HRMS Passport Photo Avatar Standard Sizing & Fallback styling */
    .hrms-emp-avatar {
        width: 42px !important;
        height: 42px !important;
        min-width: 42px !important;
        max-width: 42px !important;
        border-radius: 14px !important;
        overflow: hidden !important;
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        font-weight: 800 !important;
        flex-shrink: 0 !important;
    }

    .hrms-emp-avatar img,
    .hrms-emp-avatar-img {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: cover !important;
        display: block !important;
        border-radius: inherit !important;
    }

    .hrms-emp-avatar-fallback {
        width: 100% !important;
        height: 100% !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 14px !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
    }

    .hrms-emp-avatar-fallback.is-visible {
        display: flex !important;
    }

    .hrms-emp-avatar-fallback.is-hidden {
        display: none !important;
    }

    .hrms-emp-avatar-sm {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        max-width: 36px !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #F4F2FF !important;
        color: var(--orb-primary) !important;
        font-weight: 800 !important;
        flex-shrink: 0 !important;
    }

    .hrms-emp-avatar-sm img,
    .hrms-emp-avatar-sm .hrms-emp-avatar-img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
        border-radius: inherit !important;
    }

    .hrms-emp-avatar-sm .hrms-emp-avatar-fallback {
        font-size: 12px !important;
    }
</style>
@yield('_head')
@endsection

@section('content')
<div class="panel-layout">
    @include('components.sidebar')

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <main class="panel-main">
        @include('components.topbar')

        <section class="page-content">
            @yield('_content')
        </section>
    </main>
</div>
@endsection

@section('script')
<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const mobileBreakpoint = 992;

    function openSidebar() {
        if (!sidebar || !overlay) return;
        sidebar.classList.add('show');
        overlay.classList.add('show');
        document.body.classList.add('panel-open');
    }

    function closeSidebar() {
        if (!sidebar || !overlay) return;
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.classList.remove('panel-open');
    }

    function toggleSidebar() {
        if (window.innerWidth <= mobileBreakpoint) {
            if (sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        } else {
            document.body.classList.toggle('desktop-collapsed');
        }
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth > mobileBreakpoint) {
            closeSidebar();
        }
    });

    // Global Select2 Searchable Dropdown Initializer
    function initSearchableSelects(context) {
        if (typeof $.fn.select2 === 'undefined') return;
        const $target = context ? $(context).find('select.select2-searchable, select.js-searchable, select.auto-filter, select.js-auto-filter, select.select2-modal-searchable') : $('select.select2-searchable, select.js-searchable, select.auto-filter, select.js-auto-filter, select.select2-modal-searchable');
        $target.each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            const $modalParent = $(this).closest('.modal');
            $(this).select2({
                placeholder: $(this).data('placeholder') || $(this).attr('placeholder') || $(this).find('option:first').text() || 'Search or select...',
                allowClear: true,
                width: '100%',
                dropdownParent: $modalParent.length ? $modalParent : undefined
            });
        });
    }

    $(document).ready(function() {
        initSearchableSelects();
    });

    $(document).on('shown.bs.modal', '.modal', function () {
        initSearchableSelects(this);
    });
</script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    /* Select2 OrboOne Theme Custom Overrides */
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 34px !important;
        border-radius: 8px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        background-color: #FFFFFF !important;
        display: flex !important;
        align-items: center !important;
        padding-left: 8px !important;
        padding-right: 28px !important;
        transition: all 0.2s ease !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--orb-primary, #4B00E8) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.10) !important;
        outline: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #101828 !important;
        font-weight: 600 !important;
        font-size: 12.5px !important;
        line-height: 32px !important;
        padding-left: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
        right: 8px !important;
    }
    .select2-dropdown {
        border-radius: 10px !important;
        border: 1px solid var(--orb-border, #E7EAF3) !important;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12) !important;
        overflow: hidden !important;
        z-index: 31050 !important;
        background: #FFFFFF !important;
    }
    .select2-search--dropdown {
        padding: 10px 12px !important;
        background: #F8FAFC !important;
        border-bottom: 1px solid #EEF2F6 !important;
    }
    .select2-search--dropdown .select2-search__field {
        border-radius: 10px !important;
        border: 1px solid #CBD5E1 !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        outline: none !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--orb-primary, #4B00E8) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.10) !important;
    }
    .select2-results__options {
        max-height: 240px !important;
        padding: 6px !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        margin-bottom: 2px !important;
        color: #334155 !important;
    }
    .select2-container--default .select2-results__option[aria-selected="true"] {
        background-color: rgba(75, 0, 232, 0.08) !important;
        color: var(--orb-primary, #4B00E8) !important;
        font-weight: 700 !important;
    }
    .select2-container--default .select2-results__option--highlighted,
    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .select2-container--default .select2-results__option--highlighted[aria-selected="true"],
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)) !important;
        color: #FFFFFF !important;
    }
    .select2-container--default .select2-results__option--highlighted *,
    .select2-container--default .select2-results__option--highlighted[aria-selected] *,
    .select2-container--default .select2-results__option--highlighted[aria-selected="true"] * {
        color: #FFFFFF !important;
    }
</style>
@yield('_script')
@endsection