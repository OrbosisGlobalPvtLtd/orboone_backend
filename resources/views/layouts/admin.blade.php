<!-- @extends('layouts.app')

@section('head')

    @yield('_head')
@endsection

@section('content')
<div class="wrapper">
    @include('components.sidebar')
    <div class="content">
        @include('components.togglesidebar')

        @yield('_content')
    </div>
</div>
@yield('_script')
@endsection -->


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

        --sidebar-width:280px;
        --sidebar-collapsed:88px;
        --topbar-height:74px;
        --radius:18px;
        --shadow:0 12px 30px rgba(15,23,42,.08);
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

    /* SIDEBAR */
    .sidebar{
        position:fixed;
        top:0;
        left:0;
        width:var(--sidebar-width);
        height:100vh;
        background:linear-gradient(180deg,
            #4B00E8 0%,
            #8600EE 35%,
            #D400D5 68%,
            #EC4E74 88%,
            #FFB101 100%);
        z-index:1200;
        transition:width .28s ease, transform .28s ease;
        overflow:hidden;
        box-shadow:0 10px 35px rgba(75, 0, 232, 0.18);
    }

    .sidebar::before{
        content:"";
        position:absolute;
        top:-80px;
        right:-60px;
        width:220px;
        height:220px;
        border-radius:50%;
        background:rgba(255,255,255,0.12);
        pointer-events:none;
    }

    .sidebar::after{
        content:"";
        position:absolute;
        bottom:-100px;
        left:-70px;
        width:220px;
        height:220px;
        border-radius:50%;
        background:rgba(255,255,255,0.08);
        pointer-events:none;
    }

    .sidebar-header{
        position:relative;
        z-index:2;
        height:var(--topbar-height);
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:10px 16px;
        background:#ffffff;
        border-bottom:1px solid rgba(75,0,232,0.08);
    }

    .brand{
        display:flex;
        align-items:center;
        gap:12px;
        min-width:0;
        width:100%;
    }

    .brand-logo-box{
        width:100%;
        min-height:54px;
        background:#ffffff;
        border-radius:16px;
        display:flex;
        align-items:center;
        padding:8px 12px;
    }

    .brand-logo{
        max-height:38px;
        max-width:100%;
        object-fit:contain;
        display:block;
    }

    .brand-text{
        min-width:0;
        transition:.25s ease;
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
    }

    .sidebar-close{
        display:none;
        width:38px;
        height:38px;
        border:none;
        border-radius:12px;
        background:#F3F4F6;
        color:#111827;
        cursor:pointer;
        flex-shrink:0;
        margin-left:10px;
    }

    .sidebar-body{
        position:relative;
        z-index:2;
        height:calc(100vh - var(--topbar-height));
        overflow-y:auto;
        padding:18px 14px;
    }

    .sidebar-body::-webkit-scrollbar{
        width:6px;
    }

    .sidebar-body::-webkit-scrollbar-thumb{
        background:rgba(255,255,255,0.25);
        border-radius:999px;
    }

    .menu-label{
        padding:8px 12px 12px;
        font-size:11px;
        text-transform:uppercase;
        letter-spacing:.12em;
        color:rgba(255,255,255,0.78);
        font-weight:800;
    }

    .menu{
        display:flex;
        flex-direction:column;
        gap:8px;
    }

    .menu a{
        display:flex;
        align-items:center;
        gap:14px;
        min-height:52px;
        padding:0 14px;
        border-radius:16px;
        color:rgba(255,255,255,0.88);
        transition:.2s ease;
        font-weight:700;
        position:relative;
        overflow:hidden;
    }

    .menu a::before{
        content:"";
        position:absolute;
        inset:0;
        background:rgba(255,255,255,0.08);
        opacity:0;
        transition:.2s ease;
    }

    .menu a:hover::before{
        opacity:1;
    }

    .menu a:hover{
        color:#ffffff;
        transform:translateX(2px);
    }

    .menu a.active{
        background:#ffffff;
        color:#4B00E8;
        box-shadow:0 10px 24px rgba(17,24,39,0.12);
    }

    .menu a.active .menu-icon{
        color:#4B00E8;
    }

    .menu-icon{
        width:22px;
        min-width:22px;
        text-align:center;
        font-size:16px;
        position:relative;
        z-index:1;
        color:inherit;
    }

    .menu-text{
        white-space:nowrap;
        transition:.2s ease;
        position:relative;
        z-index:1;
    }

    /* DESKTOP COLLAPSE */
    body.desktop-collapsed .sidebar{
        width:var(--sidebar-collapsed);
    }

    body.desktop-collapsed .brand-text,
    body.desktop-collapsed .menu-label,
    body.desktop-collapsed .menu-text{
        opacity:0;
        visibility:hidden;
        width:0;
        overflow:hidden;
    }

    body.desktop-collapsed .sidebar-header{
        justify-content:center;
        padding:10px;
    }

    body.desktop-collapsed .brand{
        justify-content:center;
    }

    body.desktop-collapsed .brand-logo-box{
        width:56px;
        min-height:56px;
        border-radius:16px;
        justify-content:center;
        padding:8px;
    }

    body.desktop-collapsed .brand-logo{
        max-height:30px;
    }

    body.desktop-collapsed .menu a{
        justify-content:center;
        padding:0;
    }

    /* MAIN */
    .panel-main{
        min-height:100vh;
        margin-left:var(--sidebar-width);
        transition:margin-left .28s ease;
    }

    body.desktop-collapsed .panel-main{
        margin-left:var(--sidebar-collapsed);
    }

    /* TOPBAR */
    .topbar{
        position:fixed;
        top:0;
        left:var(--sidebar-width);
        right:0;
        height:var(--topbar-height);
        background:rgba(255,255,255,0.92);
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

    body.desktop-collapsed .topbar{
        left:var(--sidebar-collapsed);
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
        padding:calc(var(--topbar-height) + 22px) 22px 22px;
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
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .panel-main{
            margin-left:0 !important;
        }

        .topbar{
            left:0 !important;
        }

        .brand-text,
        .menu-label,
        .menu-text{
            opacity:1 !important;
            visibility:visible !important;
            width:auto !important;
        }

        .menu a{
            justify-content:flex-start !important;
            padding:0 14px !important;
        }

        .brand-logo-box{
            width:100%;
            justify-content:flex-start;
        }
    }

    @media (max-width: 640px){
        .topbar{
            padding:0 14px;
        }

        /* .page-content{
            padding:calc(var(--topbar-height) + 16px) 14px 14px;
        } */

        .page-title{
            font-size:17px;
        }

        .profile-chip{
            padding:0 12px;
            font-size:13px;
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