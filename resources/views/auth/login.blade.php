<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | OrboOne HRMS</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    @php
        $userAgent = request()->header('User-Agent');
        $isMobile = preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i', $userAgent);
        $showWebLogin = request()->get('continue') == '1';
    @endphp

    <style>
        :root{
            --orb-primary:#4b00e8;
            --orb-secondary:#8600ee;
            --orb-accent:#d400d5;
            --orb-rose:#ec4e74;
            --orb-gold:#ffb101;

            --text-dark:#111827;
            --text-soft:#6b7280;
            --text-muted:#94a3b8;
            --border:#e5e7eb;
            --input-bg:#f8fafc;
            --white:#ffffff;
        }

        *{margin:0;padding:0;box-sizing:border-box;}

        html,body{
            width:100%;
            min-height:100%;
            overflow-x:hidden;
            font-family:'Inter',sans-serif;
            background:
                radial-gradient(circle at top left, rgba(75,0,232,.12), transparent 26%),
                radial-gradient(circle at bottom right, rgba(236,78,116,.10), transparent 26%),
                linear-gradient(135deg, #f8faff 0%, #f4f6ff 45%, #fcf7ff 100%);
        }

        body{color:var(--text-dark);}
        a{text-decoration:none;}
        img{display:block;max-width:100%;}

        .page-wrap{
            width:100%;
            min-height:100vh;
        }

        /* =========================================================
           MOBILE INTRO
        ========================================================= */
        .mobile-intro-page{
            min-height:100vh;
            background:#efeff2;
            position:relative;
            overflow:hidden;
        }

        .mobile-scroll-area{
            min-height:100vh;
            overflow-y:auto;
            overflow-x:hidden;
            -webkit-overflow-scrolling:touch;
            background:linear-gradient(135deg,#50a6f8 0%, #6d83f1 20%, #8c62ef 42%, #c74adc 68%, #eb4f86 85%, #f2a42d 100%);
            position:relative;
            padding-bottom:255px;
        }

        .mobile-scroll-area::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,.14), transparent 24%),
                radial-gradient(circle at center right, rgba(255,255,255,.09), transparent 22%);
            pointer-events:none;
        }

        .mobile-scroll-area::after{
            content:"";
            position:absolute;
            width:260px;
            height:260px;
            border-radius:50%;
            background:rgba(255,255,255,.08);
            top:-80px;
            right:-90px;
            filter:blur(2px);
            pointer-events:none;
        }

        .mobile-top-content{
            position:relative;
            z-index:2;
            width:100%;
            max-width:470px;
            margin:0 auto;
            padding:34px 18px 0;
            text-align:center;
        }

        .mobile-brand-block img{
            width:clamp(170px, 18vw, 86px);
            height:clamp(70px, 18vw, 86px);
            margin:0 auto 12px;
            object-fit:contain;
        }

        .mobile-main-title{
            color:#fff;
            font-size:clamp(25px, 6.4vw, 32px);
            line-height:1.28;
            font-weight:900;
            letter-spacing:-0.7px;
            max-width:365px;
            margin:28px auto 14px;
        }

        .mobile-main-desc{
            color:rgba(255,255,255,.96);
            font-size:clamp(14px, 3.8vw, 15px);
            line-height:1.78;
            max-width:355px;
            margin:0 auto 24px;
            font-weight:500;
        }

        .playstore-badge{
            display:inline-block;
            margin:0 auto 24px;
        }

        .playstore-badge img{
            height:clamp(50px, 12vw, 58px);
            width:auto;
            border-radius:10px;
            box-shadow:0 14px 28px rgba(0,0,0,.18);
        }

        .mobile-phone-wrap{
            width:100%;
            max-width:min(365px, 92vw);
            margin:0 auto;
            position:relative;
            z-index:2;
        }

        .phone-back-device{
            width:100%;
            background:#efeff1;
            border-radius:38px;
            padding:14px;
            box-shadow:0 18px 46px rgba(15,23,42,.18), inset 0 2px 0 rgba(255,255,255,.65);
            position:relative;
        }

        .phone-back-device::before{
            content:"";
            position:absolute;
            left:-4px;
            top:72px;
            width:4px;
            height:26px;
            border-radius:4px 0 0 4px;
            background:#cfd3db;
        }

        .phone-back-device::after{
            content:"";
            position:absolute;
            right:-4px;
            top:96px;
            width:4px;
            height:38px;
            border-radius:0 4px 4px 0;
            background:#cfd3db;
        }

        .phone-screen{
            width:100%;
            background:#fbfbfc;
            border-radius:28px;
            overflow:hidden;
            min-height:640px;
            position:relative;
        }

        .phone-status-bar{
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:12px 16px 6px;
            font-size:11px;
            color:#111827;
            font-weight:700;
        }

        .phone-status-right{
            display:flex;
            align-items:center;
            gap:6px;
            font-size:11px;
        }

        .phone-notch{
            width:92px;
            height:8px;
            border-radius:999px;
            background:#d7d8dd;
            margin:0 auto 12px;
        }

        .mock-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:0 16px 12px;
            gap:10px;
        }

        .mock-header-left{
            display:flex;
            align-items:center;
            gap:10px;
            min-width:0;
        }

        .mock-app-icon{
            width:34px;
            height:34px;
            border-radius:10px;
            background:linear-gradient(135deg, #f2cff8 0%, #eed6ff 100%);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#8b5cf6;
            font-size:15px;
            flex-shrink:0;
        }

        .mock-app-text{
            text-align:left;
            min-width:0;
        }

        .mock-app-text strong{
            display:block;
            font-size:14px;
            line-height:1.1;
            color:#374151;
            font-weight:700;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .mock-app-text span{
            display:block;
            font-size:12px;
            line-height:1.2;
            color:#6b7280;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .mock-header-right{
            display:flex;
            align-items:center;
            gap:14px;
            color:#475569;
            font-size:15px;
            flex-shrink:0;
        }

        .mock-list-strip{
            background:#f2f1f8;
            border-top:1px solid rgba(0,0,0,.03);
            border-bottom:1px solid rgba(0,0,0,.03);
            padding:14px 16px;
        }

        .mock-list-strip-inner{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
        }

        .mock-list-strip-left{
            display:flex;
            align-items:center;
            gap:12px;
            color:#4b5563;
            min-width:0;
        }

        .mock-yellow-ring{
            width:18px;
            height:18px;
            border:3px solid #f4c430;
            border-radius:50%;
            background:transparent;
            flex-shrink:0;
        }

        .mock-list-strip-left strong{
            font-size:16px;
            font-weight:500;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .mock-task-list{
            padding:0 0 175px;
            background:#ffffff;
        }

        .mock-task-row{
            display:grid;
            grid-template-columns:8px 1fr 46px;
            gap:10px;
            align-items:center;
            padding:14px 16px;
            border-top:1px solid #f1f5f9;
        }

        .mock-task-row:first-child{border-top:none;}

        .mock-task-dot{
            width:8px;
            height:8px;
            border-radius:3px;
            background:#22c1f1;
        }

        .mock-task-text{
            text-align:left;
            font-size:12px;
            color:#4b5563;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
            min-width:0;
        }

        .mock-task-date{
            text-align:right;
            font-size:11px;
            color:#6b7280;
            white-space:nowrap;
        }

        .mock-overlay-card{
            position:absolute;
            left:50%;
            transform:translateX(-50%);
            bottom:108px;
            width:min(112%, 392px);
            background:#f2f2f5;
            border-radius:24px;
            padding:8px;
            box-shadow:0 18px 34px rgba(15,23,42,.10), 0 4px 10px rgba(15,23,42,.05);
        }

        .mock-overlay-inner{
            background:#ffffff;
            border-radius:18px;
            overflow:hidden;
            border:1px solid #ececf1;
        }

        .mock-card-head{
            display:grid;
            grid-template-columns:94px 1fr 94px;
            align-items:center;
            gap:10px;
            background:#eff0f5;
            padding:12px;
            color:#6b7280;
            font-size:12px;
            font-weight:700;
        }

        .mock-open-pill{
            height:34px;
            border-radius:10px;
            background:linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%);
            color:#ffffff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:11px;
            font-weight:700;
        }

        .mock-card-row{
            display:grid;
            grid-template-columns:18px 1fr 64px;
            gap:8px;
            align-items:center;
            padding:16px 12px;
            border-top:1px solid #f1f5f9;
            background:#fff;
        }

        .mock-arrow{
            color:#7c6fd6;
            font-size:11px;
        }

        .mock-row-text{
            display:flex;
            align-items:center;
            gap:8px;
            min-width:0;
        }

        .mock-blue-dot{
            width:10px;
            height:10px;
            border-radius:4px;
            background:#22c1f1;
            flex-shrink:0;
        }

        .mock-row-text span{
            font-size:12px;
            color:#4b5563;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .mock-row-date{
            text-align:right;
            color:#6b7280;
            font-size:12px;
            white-space:nowrap;
        }

        .mobile-fixed-bottom{
            position:fixed;
            left:0;
            right:0;
            bottom:0;
            z-index:30;
            pointer-events:none;
        }

        .mobile-bottom-panel{
            position:relative;
            width:100%;
            background:#ffffff;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:flex-start;
            padding:34px 18px 14px;
            min-height:138px;
            pointer-events:auto;
            box-shadow:0 -8px 24px rgba(15,23,42,.04);
            z-index:5;
        }

        .mobile-round-bg{
            position:absolute;
            left:50%;
            transform:translateX(-50%);
            top:-45px;
            width:100%;
            height:65px;
            background:url('/images/round-bg.png') no-repeat center top;
            background-size:100% 100%;
            z-index:1;
            pointer-events:none;
        }

        .mobile-cta-btn{
            position:relative;
            z-index:2;
            width:100%;
            max-width:470px;
            margin:0 auto;
            height:60px;
            border-radius:12px;
            background:linear-gradient(135deg, #6f35f4 0%, #8b5cf6 50%, #d400d5 100%);
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:18px;
            font-weight:700;
            box-shadow:0 14px 28px rgba(124,58,237,.24);
            text-align:center;
            padding:0 18px;
        }

        .mobile-cta-btn:hover{color:#fff;}

        .mobile-continue-link{
            position:relative;
            z-index:2;
            display:block;
            margin-top:16px;
            text-align:center;
            color:#7c6fd6;
            font-size:17px;
            font-weight:600;
        }

        /* =========================================================
           MOBILE LOGIN
        ========================================================= */
        .mobile-login-page{
            min-height:100vh;
            padding:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:
                radial-gradient(circle at top left, rgba(75,0,232,.12), transparent 28%),
                radial-gradient(circle at bottom right, rgba(236,78,116,.10), transparent 28%),
                linear-gradient(135deg, #f8faff 0%, #f3f4ff 45%, #fcf6ff 100%);
        }

        .mobile-login-card{
            width:100%;
            max-width:430px;
            background:rgba(255,255,255,.96);
            border:1px solid rgba(255,255,255,.92);
            border-radius:26px;
            padding:24px 18px;
            box-shadow:0 22px 44px rgba(15,23,42,.10);
            backdrop-filter:blur(10px);
        }

        /* =========================================================
           DESKTOP / LAPTOP
        ========================================================= */
        .desktop-page{
            width:100%;
            min-height:100vh;
            padding:clamp(10px, 1.3vw, 22px);
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .desktop-shell{
            width:100%;
            max-width:min(1280px, calc(100vw - 24px));
            height:auto;
            min-height:0;
            max-height:none;
        }

        .desktop-layout{
            width:100%;
            min-height:clamp(620px, calc(100vh - 32px), 760px);
            display:grid;
            grid-template-columns:minmax(0, 1.08fr) minmax(380px, .92fr);
            border-radius:32px;
            overflow:hidden;
            background:rgba(255,255,255,.72);
            border:1px solid rgba(255,255,255,.82);
            box-shadow:0 28px 70px rgba(15,23,42,.12);
            backdrop-filter:blur(18px);
        }

        .desktop-left{
            position:relative;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,.20), transparent 26%),
                radial-gradient(circle at bottom left, rgba(255,177,1,.10), transparent 22%),
                linear-gradient(155deg, #4b00e8 0%, #6e1cf0 34%, #a112e6 68%, #ec4e74 100%);
            color:#fff;
            overflow:hidden;
            padding:clamp(18px, 2.1vw, 32px);
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            min-width:0;
        }

        .desktop-left::before{
            content:"";
            position:absolute;
            width:280px;
            height:280px;
            border-radius:50%;
            background:rgba(255,255,255,.07);
            top:-120px;
            right:-90px;
            z-index:0;
        }

        .desktop-left::after{
            content:"";
            position:absolute;
            width:220px;
            height:220px;
            border-radius:50%;
            background:rgba(255,255,255,.05);
            left:-70px;
            bottom:-40px;
            z-index:0;
        }

        .desktop-left-top,
        .desktop-left-bottom{
            position:relative;
            z-index:2;
        }

        .desktop-left-top{
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            height:auto;
            min-height:100%;
            gap:clamp(12px, 1.5vw, 18px);
        }

        .desk-badge{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:8px 14px;
            border-radius:999px;
            background:rgba(255,255,255,.12);
            border:1px solid rgba(255,255,255,.14);
            font-size:11px;
            font-weight:700;
            width:max-content;
            max-width:100%;
            backdrop-filter:blur(8px);
        }

        .desk-hero-card{
            position:relative;
            padding:clamp(18px, 2vw, 24px);
            border-radius:28px;
            background:rgba(255,255,255,.09);
            border:1px solid rgba(255,255,255,.14);
            box-shadow:0 16px 40px rgba(0,0,0,.10);
            backdrop-filter:blur(10px);
            overflow:hidden;
        }

        .desk-hero-card::before{
            content:"";
            position:absolute;
            inset:0;
            background:linear-gradient(135deg, rgba(255,255,255,.06), rgba(255,255,255,.01));
            pointer-events:none;
        }

        .desk-logo-row{
            position:relative;
            z-index:2;
            display:flex;
            align-items:center;
            gap:14px;
            margin-bottom:clamp(10px, 1.4vw, 16px);
            min-width:0;
        }

        .desk-logo-text{
            min-width:0;
        }

        .desk-logo-box{
            width:clamp(142px, 16vw, 198px);
            height:auto;
            min-height:62px;
            display:flex;
            align-items:center;
            justify-content:flex-start;
            flex-shrink:0;
        }

        .desk-logo-box img{
            width:100%;
            height:auto;
            object-fit:contain;
        }

        .desk-logo-text span{
            display:block;
            font-size:13px;
            color:rgba(255,255,255,.86);
            margin-top:4px;
        }

        .desktop-title{
            position:relative;
            z-index:2;
            font-size:clamp(24px, 2.35vw, 38px);
            line-height:1.14;
            font-weight:900;
            letter-spacing:-.9px;
            max-width:100%;
            margin:0 0 10px;
        }

        .desktop-desc{
            position:relative;
            z-index:2;
            font-size:clamp(12px, 1.05vw, 14px);
            line-height:1.6;
            color:rgba(255,255,255,.90);
            max-width:100%;
            margin:0;
        }

        .desk-info-grid{
            display:grid;
            grid-template-columns:repeat(3, minmax(0, 1fr));
            gap:clamp(8px, 1vw, 12px);
        }

        .desk-info-card{
            padding:clamp(11px, 1.25vw, 16px);
            border-radius:clamp(14px, 1.5vw, 20px);
            background:rgba(255,255,255,.11);
            border:1px solid rgba(255,255,255,.14);
            backdrop-filter:blur(10px);
            min-width:0;
        }

        .desk-info-card i{
            font-size:16px;
            margin-bottom:12px;
            color:#fff;
        }

        .desk-info-card h6{
            font-size:14px;
            font-weight:800;
            margin-bottom:6px;
            color:#fff;
        }

        .desk-info-card p{
            font-size:12px;
            line-height:1.55;
            color:rgba(255,255,255,.84);
        }

        .desk-section-card{
            padding:clamp(14px, 1.5vw, 20px);
            border-radius:24px;
            background:rgba(255,255,255,.10);
            border:1px solid rgba(255,255,255,.14);
            backdrop-filter:blur(10px);
        }

        .desk-section-head{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            margin-bottom:16px;
        }

        .desk-section-head h5{
            font-size:16px;
            font-weight:800;
            color:#fff;
        }

        .desk-section-head span{
            font-size:11px;
            font-weight:700;
            color:rgba(255,255,255,.82);
            padding:7px 10px;
            border-radius:999px;
            background:rgba(255,255,255,.12);
            border:1px solid rgba(255,255,255,.12);
        }

        .desk-points{
            display:flex;
            flex-direction:column;
            gap:0;
        }

        .desk-point{
            display:flex;
            align-items:flex-start;
            gap:12px;
            padding:clamp(8px, 1vw, 12px) 0;
            border-top:1px solid rgba(255,255,255,.10);
        }

        .desk-point:first-child{
            border-top:none;
            padding-top:0;
        }

        .desk-point-icon{
            width:36px;
            height:36px;
            min-width:36px;
            border-radius:12px;
            background:rgba(255,255,255,.16);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:14px;
            color:#fff;
        }

        .desk-point-text h6{
            margin:0 0 4px;
            font-size:14px;
            font-weight:800;
            color:#fff;
        }

        .desk-point-text p{
            margin:0;
            font-size:12px;
            line-height:1.55;
            color:rgba(255,255,255,.84);
        }

        .desktop-left-bottom{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            font-size:11px;
            color:rgba(255,255,255,.82);
            margin-top:16px;
            flex-wrap:wrap;
        }

        .desktop-left-bottom .left-foot-note{
            display:flex;
            align-items:center;
            gap:8px;
            flex-wrap:wrap;
        }

        .desktop-right{
            background:rgba(255,255,255,.92);
            backdrop-filter:blur(16px);
            display:flex;
            align-items:center;
            justify-content:center;
            padding:clamp(18px, 2.2vw, 34px);
            min-width:0;
            position:relative;
        }

        .desktop-right::before{
            content:"";
            position:absolute;
            width:260px;
            height:260px;
            border-radius:50%;
            background:radial-gradient(circle, rgba(75,0,232,.06) 0%, rgba(75,0,232,0) 70%);
            top:-90px;
            right:-90px;
            pointer-events:none;
        }

        .login-panel{
            width:100%;
            max-width:430px;
            position:relative;
            z-index:2;
        }

        .login-logo-wrap{
            display:flex;
            justify-content:center;
            margin-bottom:14px;
        }

        .login-logo img{
            width:clamp(150px, 16vw, 182px);
            max-width:100%;
            height:auto;
            object-fit:contain;
            filter:drop-shadow(0 10px 20px rgba(75,0,232,.10));
        }

        .login-mini-badge-wrap{
            display:flex;
            justify-content:center;
        }

        .login-mini-badge{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:8px 14px;
            border-radius:999px;
            background:rgba(75,0,232,.08);
            color:var(--orb-primary);
            font-size:11px;
            font-weight:700;
            margin-bottom:12px;
            text-align:center;
        }

        .login-title{
            text-align:center;
            font-size:clamp(26px, 2vw, 32px);
            font-weight:900;
            color:var(--text-dark);
            line-height:1.15;
            letter-spacing:-.7px;
            margin:0 0 8px;
        }

        .login-subtitle{
            text-align:center;
            font-size:14px;
            line-height:1.65;
            color:var(--text-soft);
            margin-bottom:clamp(16px, 1.7vw, 22px);
        }

        .alert{
            border:none;
            border-radius:14px;
            padding:13px 14px;
            font-size:13px;
            margin-bottom:14px;
        }

        .alert-success{
            background:#ecfdf5;
            color:#047857;
        }

        .alert-danger{
            background:#fef2f2;
            color:#b91c1c;
        }

        .form-label{
            display:block;
            margin-bottom:8px;
            color:#1f2937;
            font-size:13px;
            font-weight:700;
        }

        .input-group-custom{
            position:relative;
            margin-bottom:16px;
        }

        .input-icon{
            position:absolute;
            left:16px;
            top:50%;
            transform:translateY(-50%);
            color:var(--text-muted);
            font-size:14px;
            z-index:2;
        }

        .form-control{
            width:100%;
            height:52px;
            border:1px solid var(--border);
            background:var(--input-bg);
            border-radius:15px;
            padding:14px 46px 14px 46px;
            font-size:14px;
            color:var(--text-dark);
            outline:none;
            transition:all .25s ease;
        }

        .form-control::placeholder{color:var(--text-muted);}

        .form-control:focus{
            border-color:rgba(75,0,232,.34);
            background:#fff;
            box-shadow:0 0 0 4px rgba(75,0,232,.08);
        }

        .eye-icon{
            position:absolute;
            right:16px;
            top:50%;
            transform:translateY(-50%);
            color:var(--text-muted);
            cursor:pointer;
            z-index:3;
            transition:color .2s ease;
        }

        .eye-icon:hover{color:var(--orb-primary);}

        .helper-row{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            flex-wrap:wrap;
            margin:2px 0 18px;
        }

        .helper-info{
            display:flex;
            align-items:center;
            gap:8px;
            font-size:12px;
            color:var(--text-soft);
        }

        .helper-link{
            font-size:12px;
            font-weight:700;
            color:var(--orb-primary);
        }

        .helper-link:hover{color:var(--orb-accent);}

        .btn-login{
            width:100%;
            height:54px;
            border:none;
            border-radius:15px;
            background:linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 52%, var(--orb-accent) 100%);
            color:#fff;
            font-size:14px;
            font-weight:800;
            letter-spacing:.2px;
            cursor:pointer;
            box-shadow:0 16px 30px rgba(75,0,232,.18);
            transition:all .25s ease;
        }

        .btn-login:hover{
            transform:translateY(-2px);
            box-shadow:0 20px 34px rgba(75,0,232,.24);
        }

        .signin-footer{
            margin-top:18px;
            padding-top:16px;
            border-top:1px solid #eef2f7;
            text-align:center;
            font-size:13px;
            color:var(--text-soft);
        }

        .signin-footer a{
            color:var(--orb-primary);
            font-weight:700;
        }

        .signin-footer a:hover{color:var(--orb-accent);}

        .copyright-text{
            text-align:center;
            margin-top:14px;
            font-size:11px;
            color:var(--text-muted);
        }

        .back-mobile-page{
            text-align:center;
            margin-top:14px;
        }

        .back-mobile-page a{
            color:var(--orb-primary);
            font-size:13px;
            font-weight:700;
        }

        .text-danger{
            display:block;
            margin-top:6px;
            color:#dc2626;
            font-size:12px;
        }

        /* =========================================================
           RESPONSIVE VISIBILITY
        ========================================================= */
        @media (min-width: 992px){
            .mobile-intro-page,
            .mobile-login-page{
                display:none !important;
            }
        }

        @media (max-width: 991.98px){
            .desktop-page{
                display:none !important;
            }
        }

        /* 992px - 1100px laptop/tablet landscape */
        @media (min-width: 992px) and (max-width: 1100px){
            .desktop-layout{
                grid-template-columns:minmax(0, 1fr) minmax(350px, .9fr);
                border-radius:24px;
            }

            .desktop-left{
                padding:18px;
            }

            .desktop-right{
                padding:18px;
            }

            .desk-info-grid{
                grid-template-columns:1fr;
            }

            .desk-info-card{
                display:flex;
                align-items:flex-start;
                gap:12px;
            }

            .desk-info-card i{
                margin-bottom:0;
                margin-top:2px;
            }

            .desktop-title{
                font-size:24px;
            }

            .desktop-desc{
                font-size:12px;
            }

            .desk-section-head h5{
                font-size:14px;
            }

            .desk-point-text h6{
                font-size:12px;
            }

            .desk-point-text p{
                font-size:10.5px;
            }

            .login-panel{
                max-width:360px;
            }
        }

        /* Large desktop */
        @media (min-width: 1400px){
            .desktop-shell{
                max-width:1340px;
            }

            .desktop-layout{
                min-height:790px;
            }

            .desktop-title{
                font-size:38px;
            }
        }

        /* Short laptop height fix */
        @media (min-width: 992px) and (max-height: 720px){
            .desktop-layout{
                min-height:calc(100vh - 20px);
            }

            .desk-hero-card{
                padding:14px 16px;
            }

            .desk-logo-box{
                width:130px;
                min-height:50px;
            }

            .desktop-title{
                font-size:23px;
                margin-bottom:6px;
            }

            .desktop-desc{
                font-size:12px;
                line-height:1.45;
            }

            .desk-info-card{
                padding:10px 12px;
            }

            .desk-info-card p{
                display:none;
            }

            .desk-section-card{
                padding:12px 14px;
            }

            .desk-point{
                padding:8px 0;
            }

            .desk-point-icon{
                width:30px;
                height:30px;
                min-width:30px;
            }

            .desk-point-text p{
                line-height:1.4;
            }

            .desktop-left-bottom{
                margin-top:8px;
            }

            .form-control,
            .btn-login{
                height:48px;
            }

            .input-group-custom{
                margin-bottom:12px;
            }

            .signin-footer{
                margin-top:12px;
                padding-top:12px;
            }
        }

        /* =========================================================
           Mobile width tuning
        ========================================================= */
        @media (max-width: 420px){
            .mobile-top-content{
                padding:28px 12px 0;
            }

            .mobile-main-title{
                max-width:310px;
                margin:22px auto 12px;
            }

            .mobile-main-desc{
                line-height:1.7;
                max-width:305px;
                margin-bottom:18px;
            }

            .mobile-scroll-area{
                padding-bottom:228px;
            }

            .mobile-phone-wrap{
                max-width:320px;
            }

            .phone-back-device{
                border-radius:34px;
                padding:12px;
            }

            .phone-screen{
                border-radius:24px;
                min-height:565px;
            }

            .phone-back-device::before{
                top:66px;
                height:24px;
            }

            .phone-back-device::after{
                top:92px;
                height:34px;
            }

            .mock-task-list{
                padding:0 0 160px;
            }

            .mock-overlay-card{
                width:min(112%, 350px);
                bottom:95px;
                border-radius:22px;
            }

            .mock-card-head{
                grid-template-columns:84px 1fr 84px;
                font-size:11px;
            }

            .mock-card-row{
                grid-template-columns:16px 1fr 56px;
            }

            .mobile-bottom-panel{
                padding:28px 14px 12px;
                min-height:116px;
            }

            .mobile-round-bg{
                height:50px;
            }

            .mobile-cta-btn{
                height:56px;
                font-size:16px;
            }

            .mobile-continue-link{
                font-size:16px;
                margin-top:14px;
            }

            .mobile-login-page{
                padding:12px;
            }

            .mobile-login-card{
                padding:20px 14px;
                border-radius:20px;
            }
        }

        @media (max-width: 360px){
            .mobile-top-content{
                padding:24px 10px 0;
            }

            .mobile-scroll-area{
                padding-bottom:220px;
            }

            .mobile-phone-wrap{
                max-width:290px;
            }

            .phone-back-device{
                padding:10px;
                border-radius:28px;
            }

            .phone-screen{
                min-height:500px;
                border-radius:20px;
            }

            .mock-header{
                padding:0 12px 10px;
            }

            .mock-list-strip{
                padding:10px 12px;
            }

            .mock-task-row{
                padding:10px 12px;
                gap:8px;
            }

            .mock-task-text{
                font-size:11px;
            }

            .mock-task-date{
                font-size:10px;
            }

            .mock-overlay-card{
                bottom:86px;
                padding:6px;
            }

            .mobile-bottom-panel{
                min-height:108px;
                padding:24px 12px 10px;
            }

            .mobile-cta-btn{
                height:52px;
                font-size:15px;
            }

            .mobile-continue-link{
                font-size:15px;
            }
        }

        @media (max-height: 760px) and (max-width: 991.98px){
            .mobile-scroll-area{
                padding-bottom:185px;
            }

            .mobile-top-content{
                padding-top:22px;
            }

            .mobile-main-title{
                max-width:290px;
                margin:18px auto 10px;
            }

            .mobile-main-desc{
                line-height:1.55;
                max-width:286px;
                margin-bottom:14px;
            }

            .playstore-badge img{
                height:48px;
            }

            .mobile-phone-wrap{
                max-width:285px;
            }

            .phone-back-device{
                padding:11px;
                border-radius:30px;
            }

            .phone-screen{
                min-height:470px;
                border-radius:22px;
            }

            .phone-status-bar{
                padding:10px 14px 5px;
            }

            .phone-notch{
                margin-bottom:10px;
            }

            .mock-header{
                padding:0 14px 10px;
            }

            .mock-list-strip{
                padding:12px 14px;
            }

            .mock-task-row{
                padding:12px 14px;
            }

            .mock-task-list{
                padding-bottom:138px;
            }

            .mock-overlay-card{
                bottom:78px;
                max-width:306px;
            }

            .mobile-bottom-panel{
                padding:24px 12px 10px;
                min-height:104px;
            }

            .mobile-round-bg{
                height:46px;
            }

            .mobile-cta-btn{
                height:50px;
                font-size:15px;
            }

            .mobile-continue-link{
                font-size:15px;
                margin-top:12px;
            }
        }
    </style>
</head>

<body>
<div class="page-wrap">

@if($isMobile && !$showWebLogin)
    <div class="mobile-intro-page">
        <div class="mobile-scroll-area">
            <div class="mobile-top-content">
                <div class="mobile-brand-block">
                    <img src="{{ asset('images/Picsart_26-04-02_12-19-10-396.png') }}" alt="OrboOne Logo">
                </div>

                <h1 class="mobile-main-title">
                    OrboOne is best experienced using our mobile app.
                </h1>

                <p class="mobile-main-desc">
                    Our HRMS mobile app gives employees faster access to attendance, leave, tasks, and daily office updates on smaller screens.
                </p>

                <a href="{{ url('downloads/orbosis-office.apk') }}" class="playstore-badge" download>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play">
                </a>

                <div class="mobile-phone-wrap">
                    <div class="mobile-phone-shell">
                        <div class="phone-back-device">
                            <div class="phone-screen">
                                <div class="phone-status-bar">
                                    <span>9:41</span>
                                    <div class="phone-status-right">
                                        <i class="fa-solid fa-signal"></i>
                                        <i class="fa-solid fa-wifi"></i>
                                        <i class="fa-solid fa-battery-three-quarters"></i>
                                    </div>
                                </div>

                                <div class="phone-notch"></div>

                                <div class="mock-header">
                                    <div class="mock-header-left">
                                        <div class="mock-app-icon">
                                            <i class="fa-solid fa-building"></i>
                                        </div>
                                        <div class="mock-app-text">
                                            <strong>OrboOne</strong>
                                            <span>HRMS Dashboard</span>
                                        </div>
                                    </div>

                                    <div class="mock-header-right">
                                        <i class="fa-solid fa-sliders"></i>
                                        <i class="fa-regular fa-bell"></i>
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </div>
                                </div>

                                <div class="mock-list-strip">
                                    <div class="mock-list-strip-inner">
                                        <div class="mock-list-strip-left">
                                            <i class="fa-solid fa-caret-down" style="color:#6b7280;"></i>
                                            <div class="mock-yellow-ring"></div>
                                            <strong>Attendance</strong>
                                        </div>
                                        <i class="fa-solid fa-ellipsis" style="color:#6b7280;"></i>
                                    </div>
                                </div>

                                <div class="mock-task-list">
                                    <div class="mock-task-row">
                                        <div class="mock-task-dot"></div>
                                        <div class="mock-task-text">Today punch-in marked successfully</div>
                                        <div class="mock-task-date">09:30</div>
                                    </div>
                                    <div class="mock-task-row">
                                        <div class="mock-task-dot"></div>
                                        <div class="mock-task-text">Leave balance updated for April</div>
                                        <div class="mock-task-date">May 8</div>
                                    </div>
                                    <div class="mock-task-row">
                                        <div class="mock-task-dot"></div>
                                        <div class="mock-task-text">Payroll slip ready for download</div>
                                        <div class="mock-task-date">May 8</div>
                                    </div>
                                    <div class="mock-task-row">
                                        <div class="mock-task-dot"></div>
                                        <div class="mock-task-text">New HR policy acknowledgment pending</div>
                                        <div class="mock-task-date">May 7</div>
                                    </div>
                                    <div class="mock-task-row">
                                        <div class="mock-task-dot"></div>
                                        <div class="mock-task-text">Attendance regularization request pending</div>
                                        <div class="mock-task-date">May 7</div>
                                    </div>
                                    <div class="mock-task-row">
                                        <div class="mock-task-dot"></div>
                                        <div class="mock-task-text">Team announcement published by HR</div>
                                        <div class="mock-task-date">May 6</div>
                                    </div>
                                </div>

                                <div class="mock-overlay-card">
                                    <div class="mock-overlay-inner">
                                        <div class="mock-card-head">
                                            <div class="mock-open-pill">ACTIVE</div>
                                            <div>02 REQUESTS</div>
                                            <div style="text-align:right;">STATUS</div>
                                        </div>

                                        <div class="mock-card-row">
                                            <div class="mock-arrow">▶</div>
                                            <div class="mock-row-text">
                                                <div class="mock-blue-dot"></div>
                                                <span>Leave request | Casual leave</span>
                                            </div>
                                            <div class="mock-row-date">Open</div>
                                        </div>

                                        <div class="mock-card-row">
                                            <div class="mock-arrow">▶</div>
                                            <div class="mock-row-text">
                                                <div class="mock-blue-dot"></div>
                                                <span>Punch status | Today check-in</span>
                                            </div>
                                            <div class="mock-row-date">09:30</div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mobile-fixed-bottom">
            <div class="mobile-bottom-panel">
                <div class="mobile-round-bg"></div>

                <a href="{{ url('downloads/orbosis-office.apk') }}" class="mobile-cta-btn" download>
                    Open in free mobile app
                </a>

                <a href="{{ url('login?continue=1') }}" class="mobile-continue-link">
                    Continue on web anyway
                </a>
            </div>
        </div>
    </div>

@elseif($isMobile && $showWebLogin)
    <div class="mobile-login-page">
        <div class="mobile-login-card">
            <div class="login-panel">

                <div class="login-logo-wrap">
                    <div class="login-logo">
                        <img src="{{ asset('images/Picsart_26-04-02_12-19-10-396.png') }}" alt="OrboOne HRMS Logo">
                    </div>
                </div>

                <div class="login-mini-badge-wrap">
                    <div class="login-mini-badge">
                        <i class="fa-solid fa-lock"></i>
                        Secure Employee Login
                    </div>
                </div>

                <h2 class="login-title">Welcome back</h2>
                <p class="login-subtitle">
                    Sign in to continue to your employee dashboard.
                </p>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('fail'))
                    <div class="alert alert-danger">
                        {{ session('fail') }}
                    </div>
                @endif

                <form action="{{ url('login') }}" method="POST">
                    @csrf

                    <div>
                        <label class="form-label">Work Email</label>
                        <div class="input-group-custom">
                            <span class="input-icon">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="Enter your work email"
                                value="{{ old('email') }}"
                                required
                            >
                        </div>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Password</label>
                        <div class="input-group-custom">
                            <span class="input-icon">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input
                                type="password"
                                name="password"
                                id="passwordMobile"
                                class="form-control"
                                placeholder="Enter your password"
                                required
                            >
                            <span class="eye-icon" id="togglePasswordMobile">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </div>
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="helper-row">
                        <div class="helper-info">
                            <i class="fa-solid fa-shield-heart"></i>
                            Protected company access
                        </div>
                        <a href="{{ route('password.forgot') }}" class="helper-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fa-solid fa-right-to-bracket" style="margin-right:8px;"></i>
                        Sign In to Dashboard
                    </button>
                </form>

                <div class="signin-footer">
                    New on our platform?
                    <a href="{{ url('#') }}">Contact HR to Register</a>
                </div>

                <div class="copyright-text">
                    Trusted office login experience
                </div>

                <div class="back-mobile-page">
                    <a href="{{ url('login') }}">← Back to app view</a>
                </div>
            </div>
        </div>
    </div>

<!-- DESKTOP PART ONLY UPDATED -->

@else
    <div class="desktop-page">
        <div class="desktop-shell">
            <div class="desktop-layout">

                <div class="desktop-left">
                    <div class="desktop-left-top">

                        <div class="desk-badge">
                            <i class="fa-solid fa-building-shield"></i>
                            Enterprise HRMS Platform
                        </div>

                        <div class="desk-hero-card">
                            <h2 class="desktop-title">
                                Professional workforce management for modern teams.
                            </h2>

                            <p class="desktop-desc">
                                Manage attendance, leave, employee records, requests, and daily HR operations through a clean and secure office-ready platform.
                            </p>
                        </div>

                        <div class="desk-info-grid">
                            <div class="desk-info-card">
                                <i class="fa-regular fa-clock"></i>
                                <div>
                                    <h6>Attendance</h6>
                                    <p>Track daily check-in, check-out and work status with clarity.</p>
                                </div>
                            </div>

                            <div class="desk-info-card">
                                <i class="fa-regular fa-calendar-check"></i>
                                <div>
                                    <h6>Leave</h6>
                                    <p>Handle approvals, balances and request flows in one place.</p>
                                </div>
                            </div>

                            <div class="desk-info-card">
                                <i class="fa-solid fa-users-gear"></i>
                                <div>
                                    <h6>Employees</h6>
                                    <p>Maintain profiles, onboarding progress and workforce updates.</p>
                                </div>
                            </div>
                        </div>

                        <div class="desk-section-card">
                            <div class="desk-section-head">
                                <h5>What you can manage</h5>
                                <span>Secure Access</span>
                            </div>

                            <div class="desk-points">
                                <div class="desk-point">
                                    <div class="desk-point-icon">
                                        <i class="fa-solid fa-user-check"></i>
                                    </div>
                                    <div class="desk-point-text">
                                        <h6>Employee login and profile flow</h6>
                                        <p>Support onboarding, employee records and role-based access in a structured HR workflow.</p>
                                    </div>
                                </div>

                                <div class="desk-point">
                                    <div class="desk-point-icon">
                                        <i class="fa-solid fa-file-signature"></i>
                                    </div>
                                    <div class="desk-point-text">
                                        <h6>Requests and approvals</h6>
                                        <p>Handle leave requests, attendance actions and internal approvals through a simplified system.</p>
                                    </div>
                                </div>

                                <div class="desk-point">
                                    <div class="desk-point-icon">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </div>
                                    <div class="desk-point-text">
                                        <h6>Office-grade secure experience</h6>
                                        <p>Designed for reliable company access with a cleaner interface for admin and employee usage.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="desktop-left-bottom">
                        <div class="left-foot-note">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Built for secure office workflow</span>
                        </div>
                        <div>© {{ date('Y') }} OrboOne HRMS. Secure workforce platform.</div>
                    </div>
                </div>

                <div class="desktop-right">
                    <div class="login-panel">

                        <div class="login-logo-wrap">
                            <div class="login-logo">
                                <img src="{{ asset('images/Picsart_26-04-02_12-19-10-396.png') }}" alt="OrboOne HRMS Logo">
                            </div>
                        </div>

                        <div class="login-mini-badge-wrap">
                            <div class="login-mini-badge">
                                <i class="fa-solid fa-lock"></i>
                                Secure Employee Login
                            </div>
                        </div>

                        <h2 class="login-title">Welcome back</h2>
                        <p class="login-subtitle">
                            Sign in to continue to your employee dashboard.
                        </p>

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('fail'))
                            <div class="alert alert-danger">
                                {{ session('fail') }}
                            </div>
                        @endif

                        <form action="{{ url('login') }}" method="POST">
                            @csrf

                            <div>
                                <label class="form-label">Work Email</label>
                                <div class="input-group-custom">
                                    <span class="input-icon">
                                        <i class="fa-regular fa-envelope"></i>
                                    </span>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control"
                                        placeholder="Enter your work email"
                                        value="{{ old('email') }}"
                                        required
                                    >
                                </div>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label">Password</label>
                                <div class="input-group-custom">
                                    <span class="input-icon">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                    <input
                                        type="password"
                                        name="password"
                                        id="passwordDesktop"
                                        class="form-control"
                                        placeholder="Enter your password"
                                        required
                                    >
                                    <span class="eye-icon" id="togglePasswordDesktop">
                                        <i class="fa-solid fa-eye"></i>
                                    </span>
                                </div>
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="helper-row">
                                <div class="helper-info">
                                    <i class="fa-solid fa-shield-heart"></i>
                                    Protected company access
                                </div>
                                <a href="{{ route('password.forgot') }}" class="helper-link">Forgot password?</a>
                            </div>

                            <button type="submit" class="btn-login">
                                <i class="fa-solid fa-right-to-bracket" style="margin-right:8px;"></i>
                                Sign In to Dashboard
                            </button>
                        </form>

                        <div class="signin-footer">
                            New on our platform?
                            <a href="{{ url('#') }}">Contact HR to Register</a>
                        </div>

                        <div class="copyright-text">
                            Trusted office login experience
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endif

</div>

<script>
    function bindToggle(toggleId, inputId) {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);

        if (toggle && input) {
            toggle.addEventListener('click', function () {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                this.innerHTML = type === 'password'
                    ? '<i class="fa-solid fa-eye"></i>'
                    : '<i class="fa-solid fa-eye-slash"></i>';
            });
        }
    }

    bindToggle('togglePasswordDesktop', 'passwordDesktop');
    bindToggle('togglePasswordMobile', 'passwordMobile');
</script>
</body>
</html>