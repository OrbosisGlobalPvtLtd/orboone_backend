@extends('layouts.app')

@section('title', 'OrboOne Panel')

@section('head')
<style>
    :root{
        --primary:#4B00E8;
        --primary-2:#8600EE;
        --primary-3:#D400D5;
        --primary-4:#EC4E74;
        --primary-5:#FFB101;

        --primary-light:#F3EDFF;
        --bg:#F6F7FB;
        --white:#FFFFFF;
        --border:#E7EAF3;
        --text:#111827;
        --muted:#6B7280;

        --sidebar-width:290px;
        --sidebar-collapsed:92px;
        --topbar-height:74px;
        --radius:18px;
        --shadow:0 14px 34px rgba(15,23,42,.08);
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
    }

    /* ========== SIDEBAR ========== */
    .sidebar{
        position:fixed;
        top:0;
        left:0;
        width:var(--sidebar-width);
        height:100vh;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,0.14), transparent 26%),
            radial-gradient(circle at bottom left, rgba(255,255,255,0.10), transparent 24%),
            linear-gradient(180deg, #4B00E8 0%, #8600EE 38%, #D400D5 70%, #EC4E74 88%, #FFB101 100%);
        z-index:1200;
        transition:width .28s ease, transform .28s ease;
        overflow:hidden;
        box-shadow:0 16px 40px rgba(75, 0, 232, 0.22);
        display:flex;
        flex-direction:column;
    }

    /* .sidebar-header{
        position:relative;
        z-index:2;
        height:86px;
        padding:14px 16px 12px;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:12px;
    } */
    .sidebar-header{
    position: relative;
    z-index: 5;
    height: 74px;
    padding: 0 16px;
    display: flex;
    align-items: center;
    justify-content: center; /* center layout */
    background: #ffffff;
    border-bottom: 1px solid #eef1f7;
}

    .brand{
    width: 100%;
    display: flex;
    justify-content: center; /* center logo */
    align-items: center;
}

    /* .brand-logo-box{
        width:100%;
        min-height:58px;
        background:#fff;
        border-radius:18px;
        display:flex;
        align-items:center;
        padding:8px 14px;
        box-shadow:
            0 10px 24px rgba(17,24,39,0.10),
            inset 0 1px 0 rgba(255,255,255,0.7);
    } */
    .brand-logo-box{
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

    
/* LOGO IMAGE */
.brand-logo{
    max-height: 52px;   /* 🔥 increase height */
    max-width: 180px;   /* 🔥 increase width */
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

    .brand-text{
        min-width:0;
    }

    .brand-title{
        color:#4B00E8;
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
        color:#4B00E8;
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

    .menu > a,
    .sidebar-group-toggle{
        width:100%;
        min-height:48px;
        border:none;
        border-radius:14px;
        display:flex;
        align-items:center;
        gap:12px;
        padding:0 14px;
        background:transparent;
        color:rgba(255,255,255,0.92);
        font-weight:700;
        text-align:left;
        transition:.2s ease;
        position:relative;
        overflow:hidden;
        text-decoration:none;
    }

    .menu > a::before,
    .sidebar-group-toggle::before{
        content:"";
        position:absolute;
        inset:0;
        background:rgba(255,255,255,0.08);
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
    }

    .menu > a.active{
        background:#fff;
        color:#4B00E8;
        box-shadow:0 10px 24px rgba(17,24,39,0.14);
    }

    .menu > a.active .menu-icon{
        color:#4B00E8;
    }

    .menu-icon{
        width:20px;
        min-width:20px;
        text-align:center;
        font-size:15px;
        position:relative;
        z-index:1;
    }

    .menu-text{
        white-space:nowrap;
        position:relative;
        z-index:1;
        font-size:14px;
    }

    .menu-badge{
        margin-left:auto;
        min-width:28px;
        height:20px;
        padding:0 7px;
        border-radius:999px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:10px;
        font-weight:800;
        background:#fff;
        color:#4B00E8;
        box-shadow:0 6px 14px rgba(17,24,39,0.10);
        position:relative;
        z-index:1;
    }

    .menu > a.active .menu-badge{
        background:#4B00E8;
        color:#fff;
    }

    /* ========== COLLAPSE GROUPS ========== */
    .sidebar-group{
        margin:0;
    }

    .sidebar-group + .sidebar-group{
        margin-top:2px;
    }

    .sidebar-group.open .sidebar-group-toggle{
        background:rgba(255,255,255,0.10);
        color:#fff;
    }

    .group-chevron{
        margin-left:auto;
        position:relative;
        z-index:1;
        transition:transform .25s ease;
        font-size:12px;
    }

    .sidebar-group.open .group-chevron{
        transform:rotate(180deg);
    }

    .sidebar-submenu{
        padding:6px 0 2px 0;
        margin:2px 0 4px 18px;
        border-left:1px solid rgba(255,255,255,0.22);
    }

    .sub-link{
        min-height:38px;
        display:flex;
        align-items:center;
        gap:10px;
        border-radius:12px;
        padding:0 12px 0 14px;
        margin:4px 0 0 10px;
        color:rgba(255,255,255,0.82);
        font-size:13px;
        font-weight:600;
        transition:.2s ease;
        text-decoration:none;
    }

    .sub-link:hover{
        background:rgba(255,255,255,0.10);
        color:#fff;
    }

    .sub-link.active{
        background:#fff;
        color:#4B00E8;
        box-shadow:0 8px 18px rgba(17,24,39,0.12);
    }

    .sub-link-icon{
        width:16px;
        min-width:16px;
        text-align:center;
        font-size:12px;
    }

    .sub-link-text{
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
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
        width:58px;
        min-height:58px;
        padding:8px;
        justify-content:center;
    }

    body.desktop-collapsed .brand-logo{
        max-height:30px;
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
        min-height:58px;
        padding:10px;
    }

    /* ========== MAIN / TOPBAR ========== */
    .panel-main{
        min-height:100vh;
        margin-left:var(--sidebar-width);
        transition:margin-left .28s ease;
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
        padding:0 22px;
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
        color:#4B00E8;
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
        background:#4B00E8;
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
            width:min(86vw, 320px);
            box-shadow:0 20px 50px rgba(0,0,0,.28);
        }

        .sidebar.show{
            transform:translateX(0);
        }

        .sidebar-close{
        display: flex;
    }
    .brand-logo{
        max-height: 46px;
        max-width: 150px;
    }

        .panel-main{
            margin-left:0 !important;
        }

        .topbar{
            left:0 !important;
        }

        .brand-text,
        .menu-label,
        .menu-text,
        .module-switch-text,
        .group-chevron,
        .menu-badge,
        .sidebar-footer-title,
        .sidebar-footer-sub{
            display:initial !important;
        }

        /* .sidebar-submenu{
            display:block !important;
        } */
         .sidebar-submenu{
        display: none; /* default closed */
    }

    .sidebar-submenu.show{
        display: block; /* open only when clicked */
    }

        .module-switcher{
            grid-template-columns:repeat(2, minmax(0,1fr));
        }

        .module-switch-item,
        .menu > a,
        .sidebar-group-toggle{
            justify-content:flex-start !important;
            padding:0 14px !important;
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
            min-height:46px;
        }
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
</script>
@yield('_script')
@endsection