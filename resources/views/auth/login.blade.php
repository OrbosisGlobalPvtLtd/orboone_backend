<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ $branding['company_name'] ?? config('app.name', 'OrboOne HRMS') }}</title>
    <link rel="shortcut icon" href="{{ $branding['favicon_url'] ?? asset('favicon.ico') }}" type="image/x-icon">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    @php
    $userAgent = request()->header('User-Agent');
    $isMobile = preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i', $userAgent);
    $showWebLogin = request()->get('continue') == '1';

    $company = null;
    try {
    if (\Illuminate\Support\Facades\Schema::hasTable('company_settings')) {
    $company = \Illuminate\Support\Facades\DB::table('company_settings')->first();
    }
    } catch (\Throwable $e) {
    $company = null;
    }
    @endphp

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --orb-primary: <?php echo $branding['primary_color'] ?? '#4B00E8'; ?>;
            --orb-secondary: <?php echo $branding['secondary_color'] ?? '#8600EE'; ?>;
            --orb-accent: #d400d5;
            --orb-rose: #ec4e74;
            --orb-gold: #ffb101;

            --text-dark: #111827;
            --text-soft: #6b7280;
            --text-muted: #94a3b8;
            --border: #e5e7eb;
            --input-bg: #f8fafc;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(75, 0, 232, .12), transparent 26%),
                radial-gradient(circle at bottom right, rgba(236, 78, 116, .10), transparent 26%),
                linear-gradient(135deg, #f8faff 0%, #f4f6ff 45%, #fcf7ff 100%);
        }

        body {
            color: var(--text-dark);
        }

        a {
            text-decoration: none;
        }

        img {
            display: block;
            max-width: 100%;
        }

        .page-wrap {
            width: 100%;
            min-height: 100vh;
        }

        /* =========================================================
           MOBILE INTRO
        ========================================================= */
        .mobile-intro-page {
            min-height: 100vh;
            background: #efeff2;
            position: relative;
            overflow: hidden;
        }

        .mobile-scroll-area {
            min-height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            background: linear-gradient(135deg, #50a6f8 0%, #6d83f1 20%, #8c62ef 42%, #c74adc 68%, #eb4f86 85%, #f2a42d 100%);
            position: relative;
            padding-bottom: 255px;
        }

        .mobile-scroll-area::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, .14), transparent 24%),
                radial-gradient(circle at center right, rgba(255, 255, 255, .09), transparent 22%);
            pointer-events: none;
        }

        .mobile-scroll-area::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            top: -80px;
            right: -90px;
            filter: blur(2px);
            pointer-events: none;
        }

        .mobile-top-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 470px;
            margin: 0 auto;
            padding: 34px 18px 0;
            text-align: center;
        }

        .mobile-brand-block img {
            width: clamp(170px, 18vw, 86px);
            height: clamp(70px, 18vw, 86px);
            margin: 0 auto 12px;
            object-fit: contain;
        }

        .mobile-main-title {
            color: #fff;
            font-size: clamp(25px, 6.4vw, 32px);
            line-height: 1.28;
            font-weight: 900;
            letter-spacing: -0.7px;
            max-width: 365px;
            margin: 28px auto 14px;
        }

        .mobile-main-desc {
            color: rgba(255, 255, 255, .96);
            font-size: clamp(14px, 3.8vw, 15px);
            line-height: 1.78;
            max-width: 355px;
            margin: 0 auto 24px;
            font-weight: 500;
        }

        .playstore-badge {
            display: inline-block;
            margin: 0 auto 24px;
        }

        .playstore-badge img {
            height: clamp(50px, 12vw, 58px);
            width: auto;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0, 0, 0, .18);
        }

        .mobile-phone-wrap {
            width: 100%;
            max-width: min(365px, 92vw);
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .phone-back-device {
            width: 100%;
            background: #efeff1;
            border-radius: 38px;
            padding: 14px;
            box-shadow: 0 18px 46px rgba(15, 23, 42, .18), inset 0 2px 0 rgba(255, 255, 255, .65);
            position: relative;
        }

        .phone-back-device::before {
            content: "";
            position: absolute;
            left: -4px;
            top: 72px;
            width: 4px;
            height: 26px;
            border-radius: 4px 0 0 4px;
            background: #cfd3db;
        }

        .phone-back-device::after {
            content: "";
            position: absolute;
            right: -4px;
            top: 96px;
            width: 4px;
            height: 38px;
            border-radius: 0 4px 4px 0;
            background: #cfd3db;
        }

        .phone-screen {
            width: 100%;
            background: #fbfbfc;
            border-radius: 28px;
            overflow: hidden;
            min-height: 640px;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .phone-status-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px 6px;
            font-size: 11px;
            color: #111827;
            font-weight: 700;
        }

        .phone-status-right {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
        }

        .phone-notch {
            width: 92px;
            height: 8px;
            border-radius: 999px;
            background: #d7d8dd;
            margin: 0 auto 12px;
        }

        .mock-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 16px 12px;
            gap: 10px;
        }

        .mock-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .mock-app-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f2cff8 0%, #eed6ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8b5cf6;
            font-size: 15px;
            flex-shrink: 0;
        }

        .mock-app-text {
            text-align: left;
            min-width: 0;
        }

        .mock-app-text strong {
            display: block;
            font-size: 14px;
            line-height: 1.1;
            color: #374151;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mock-app-text span {
            display: block;
            font-size: 12px;
            line-height: 1.2;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mock-header-right {
            display: flex;
            align-items: center;
            gap: 14px;
            color: #475569;
            font-size: 15px;
            flex-shrink: 0;
        }

        .mock-list-strip {
            background: #f2f1f8;
            border-top: 1px solid rgba(0, 0, 0, .03);
            border-bottom: 1px solid rgba(0, 0, 0, .03);
            padding: 14px 16px;
        }

        .mock-list-strip-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .mock-list-strip-left {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #4b5563;
            min-width: 0;
        }

        .mock-yellow-ring {
            width: 18px;
            height: 18px;
            border: 3px solid #f4c430;
            border-radius: 50%;
            background: transparent;
            flex-shrink: 0;
        }

        .mock-list-strip-left strong {
            font-size: 16px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mock-task-list {
            padding: 0 0 175px;
            background: #ffffff;
        }

        .mock-task-row {
            display: grid;
            grid-template-columns: 8px 1fr 46px;
            gap: 10px;
            align-items: center;
            padding: 14px 16px;
            border-top: 1px solid #f1f5f9;
        }

        .mock-task-row:first-child {
            border-top: none;
        }

        .mock-task-dot {
            width: 8px;
            height: 8px;
            border-radius: 3px;
            background: #22c1f1;
        }

        .mock-task-text {
            text-align: left;
            font-size: 12px;
            color: #4b5563;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .mock-task-date {
            text-align: right;
            font-size: 11px;
            color: #6b7280;
            white-space: nowrap;
        }

        .mock-overlay-card {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 108px;
            width: min(112%, 392px);
            background: #f2f2f5;
            border-radius: 24px;
            padding: 8px;
            box-shadow: 0 18px 34px rgba(15, 23, 42, .10), 0 4px 10px rgba(15, 23, 42, .05);
        }

        .mock-overlay-inner {
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #ececf1;
        }

        .mock-card-head {
            display: grid;
            grid-template-columns: 94px 1fr 94px;
            align-items: center;
            gap: 10px;
            background: #eff0f5;
            padding: 12px;
            color: #6b7280;
            font-size: 12px;
            font-weight: 700;
        }

        .mock-open-pill {
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }

        .mock-card-row {
            display: grid;
            grid-template-columns: 18px 1fr 64px;
            gap: 8px;
            align-items: center;
            padding: 16px 12px;
            border-top: 1px solid #f1f5f9;
            background: #fff;
        }

        .mock-arrow {
            color: #7c6fd6;
            font-size: 11px;
        }

        .mock-row-text {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .mock-blue-dot {
            width: 10px;
            height: 10px;
            border-radius: 4px;
            background: #22c1f1;
            flex-shrink: 0;
        }

        .mock-row-text span {
            font-size: 12px;
            color: #4b5563;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mock-row-date {
            text-align: right;
            color: #6b7280;
            font-size: 12px;
            white-space: nowrap;
        }

        .mobile-fixed-bottom {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 30;
            pointer-events: none;
        }

        .mobile-bottom-panel {
            position: relative;
            width: 100%;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 34px 18px 14px;
            min-height: 138px;
            pointer-events: auto;
            box-shadow: 0 -8px 24px rgba(15, 23, 42, .04);
            z-index: 5;
        }

        .mobile-round-bg {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: -45px;
            width: 100%;
            height: 65px;
            background: url('/images/round-bg.png') no-repeat center top;
            background-size: 100% 100%;
            z-index: 1;
            pointer-events: none;
        }

        .mobile-cta-btn {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 470px;
            margin: 0 auto;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            box-shadow: 0 14px 28px rgba(124, 58, 237, .24);
            text-align: center;
            padding: 0 18px;
        }

        .mobile-cta-btn:hover {
            color: #fff;
        }

        .mobile-continue-link {
            position: relative;
            z-index: 2;
            display: block;
            margin-top: 16px;
            text-align: center;
            color: #7c6fd6;
            font-size: 17px;
            font-weight: 600;
        }

        /* =========================================================
           MOBILE LOGIN
        ========================================================= */
        .mobile-login-page {
            min-height: 100vh;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left, rgba(75, 0, 232, .12), transparent 28%),
                radial-gradient(circle at bottom right, rgba(236, 78, 116, .10), transparent 28%),
                linear-gradient(135deg, #f8faff 0%, #f3f4ff 45%, #fcf6ff 100%);
        }

        .mobile-login-card {
            width: 100%;
            max-width: 430px;
            background: rgba(255, 255, 255, .96);
            border: 1px solid rgba(255, 255, 255, .92);
            border-radius: 26px;
            padding: 24px 18px;
            box-shadow: 0 22px 44px rgba(15, 23, 42, .10);
            backdrop-filter: blur(10px);
        }

        .mobile-apk-download {
            margin: 14px 0 18px;
            padding: 14px;
            border: 1px solid rgba(124, 58, 237, .16);
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(244, 242, 255, .96), rgba(255, 255, 255, .96));
        }

        .mobile-apk-title {
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .mobile-apk-text {
            color: var(--text-soft);
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .mobile-apk-btn {
            min-height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 14px;
            color: #fff;
            background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
            font-size: 13px;
            font-weight: 900;
            box-shadow: 0 12px 24px rgba(75, 0, 232, .18);
        }

        .mobile-apk-btn:hover {
            color: #fff;
        }

        /* =========================================================
           DESKTOP / LAPTOP
        ========================================================= */
        .desktop-page {
            width: 100%;
            min-height: 100vh;
            padding: clamp(10px, 1.3vw, 22px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .desktop-shell {
            width: 100%;
            max-width: min(1280px, calc(100vw - 24px));
            height: auto;
            min-height: 0;
            max-height: none;
        }

        .desktop-layout {
            width: 100%;
            min-height: clamp(540px, calc(100vh - 60px), 680px);
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(380px, .85fr);
            border-radius: 32px;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.12);
        }

        .desktop-left {
            position: relative;
            background: #f3f4f9;
            color: #0f172a;
            overflow: hidden;
            padding: clamp(16px, 2vw, 24px) clamp(16px, 2vw, 24px) 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
        }

        .desktop-left::after {
            content: "";
            position: absolute;
            top: 24px;
            right: 24px;
            width: 80px;
            height: 80px;
            background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px);
            background-size: 10px 10px;
            opacity: 0.6;
            pointer-events: none;
            z-index: 1;
        }

        .desktop-left-top {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            height: auto;
            gap: 15px;
        }

        .desk-logo-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            justify-content: center
        }

        .desk-logo-box {
            width: 180px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .desk-logo-box img {
            max-width: 100%;
            /* max-height: 100%; */
            object-fit: contain;
        }

        .desk-logo-text {
            text-align: left;
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .desk-system-title {
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .desk-system-sub {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .desk-hero-content {
            text-align: left;
            margin-top: 10px;
        }

        .desktop-title {
            font-size: clamp(22px, 2.3vw, 30px) !important;
            line-height: 1.25 !important;
            font-weight: 900 !important;
            color: #0f172a !important;
            letter-spacing: -0.8px;
            margin-bottom: 14px !important;
        }

        .text-primary-grad {
            background: linear-gradient(135deg, #6d28d9 0%, #db2777 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .desktop-desc {
            font-size: clamp(12px, 1.05vw, 14px) !important;
            line-height: 1.6 !important;
            color: #475569 !important;
            max-width: 380px;
            margin: 0;
        }

        .desk-indicator-line {
            width: 42px;
            height: 4px;
            background: linear-gradient(90deg, #6d28d9 0%, #db2777 100%);
            border-radius: 2px;
            margin-top: 15px;
        }

        .desk-vector-wrap {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
            z-index: 2;
        }

        .desk-vector-img {
            width: 85%;
            max-width: 380px;
            height: auto;
            max-height: clamp(200px, 38vh, 320px);
            object-fit: contain;
            margin-bottom: -10px;
            transition: all 0.2s ease;
        }

        .desktop-left-bottom-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 110px;
            z-index: 1;
            overflow: hidden;
        }

        .desk-features-row {
            position: absolute;
            bottom: 8px;
            left: 0;
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 0 30px;
            z-index: 2;
            color: #ffffff;
        }

        .desk-feature-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .desk-feature-icon {
            width: 28px;
            height: 28px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2px;
            font-size: 11px;
        }

        .desk-feature-title {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .desk-feature-desc {
            font-size: 9.5px;
            opacity: 0.8;
            line-height: 1.25;
        }

        .desktop-right {
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(16px, 2vw, 32px) clamp(16px, 2vw, 32px) 60px;
            min-width: 0;
            position: relative;
        }

        .login-card-wrapper {
            background: #ffffff;
            border-radius: 24px;
            padding: clamp(20px, 2vw, 32px);
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.03);
            width: 100%;
            max-width: 440px;
            box-sizing: border-box;
        }

        .login-card-wrapper .login-title {
            text-align: center;
            font-size: 28px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            margin: 0 0 6px !important;
            letter-spacing: -0.5px;
        }

        .login-card-wrapper .login-subtitle {
            text-align: center;
            font-size: 14px !important;
            color: #64748b !important;
            margin-bottom: 16px !important;
            font-weight: 500;
        }

        .login-card-wrapper .form-label {
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .login-card-wrapper .form-control {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            height: 44px;
            font-size: 14px;
            padding: 8px 42px;
        }

        .login-card-wrapper .form-control:focus {
            border-color: #6d28d9;
            box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.1);
        }

        .login-card-wrapper .input-icon {
            left: 14px;
            font-size: 14px;
            color: #94a3b8;
        }

        .login-card-wrapper .eye-icon {
            right: 14px;
            font-size: 14px;
            color: #94a3b8;
        }

        .btn-login-new {
            width: 100%;
            height: 44px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #6d28d9 0%, #db2777 100%);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(109, 40, 217, 0.2);
            transition: all 0.2s ease;
        }

        .btn-login-new:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(109, 40, 217, 0.3);
        }

        .separator-text {
            position: relative;
            text-align: center;
            margin: 12px 0;
        }

        .separator-text::before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e2e8f0;
            z-index: 1;
        }

        .separator-text span {
            position: relative;
            background: #ffffff;
            padding: 0 10px;
            color: #94a3b8;
            font-size: 12px;
            z-index: 2;
            font-weight: 600;
        }

        .btn-sso-login {
            width: 100%;
            height: 44px;
            border: 1px solid #6d28d9;
            border-radius: 8px;
            background: #ffffff;
            color: #6d28d9;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-sso-login:hover {
            background: #fdf4ff;
            border-color: #db2777;
            color: #db2777;
        }

        .signin-footer-new {
            margin-top: 16px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }

        .signin-footer-new a {
            color: #6d28d9;
            font-weight: 700;
            text-decoration: none;
        }

        .signin-footer-new a:hover {
            text-decoration: underline;
        }

        .login-card-wrapper .helper-link {
            color: #6d28d9 !important;
            font-size: 12px;
            font-weight: 600;
        }

        .login-card-wrapper .helper-link:hover {
            color: #db2777 !important;
            text-decoration: underline;
        }

        .desktop-copyright-footer {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .login-panel {
            width: 100%;
            max-width: 430px;
            position: relative;
            z-index: 2;
        }

        .login-logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 14px;
        }

        .login-logo img {
            width: clamp(150px, 16vw, 182px);
            max-width: 100%;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 10px 20px rgba(75, 0, 232, .10));
        }

        .login-mini-badge-wrap {
            display: flex;
            justify-content: center;
        }

        .login-mini-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(75, 0, 232, .08);
            color: var(--orb-primary);
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 12px;
            text-align: center;
        }

        .login-title {
            text-align: center;
            font-size: clamp(26px, 2vw, 32px);
            font-weight: 900;
            color: var(--text-dark);
            line-height: 1.15;
            letter-spacing: -.7px;
            margin: 0 0 8px;
        }

        .login-subtitle {
            text-align: center;
            font-size: 14px;
            line-height: 1.65;
            color: var(--text-soft);
            margin-bottom: clamp(16px, 1.7vw, 22px);
        }

        .alert {
            border: none;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .alert-success {
            background: #ecfdf5;
            color: #047857;
        }

        .alert-danger {
            background: #fef2f2;
            color: #b91c1c;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #1f2937;
            font-size: 13px;
            font-weight: 700;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 16px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            height: 52px;
            border: 1px solid var(--border);
            background: var(--input-bg);
            border-radius: 15px;
            padding: 14px 46px 14px 46px;
            font-size: 14px;
            color: var(--text-dark);
            outline: none;
            transition: all .25s ease;
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-control:focus {
            border-color: rgba(75, 0, 232, .34);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(75, 0, 232, .08);
        }

        .eye-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            z-index: 3;
            transition: color .2s ease;
        }

        .eye-icon:hover {
            color: var(--orb-primary);
        }

        .helper-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin: 2px 0 18px;
        }

        .helper-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-soft);
        }

        .helper-link {
            font-size: 12px;
            font-weight: 700;
            color: var(--orb-primary);
        }

        .helper-link:hover {
            color: var(--orb-accent);
        }

        .btn-login {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 78%);
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: .2px;
            cursor: pointer;
            box-shadow: 0 16px 30px rgba(75, 0, 232, .18);
            transition: all .25s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 34px rgba(75, 0, 232, .24);
        }

        .signin-footer {
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #eef2f7;
            text-align: center;
            font-size: 13px;
            color: var(--text-soft);
        }

        .signin-footer a {
            color: var(--orb-primary);
            font-weight: 700;
        }

        .signin-footer a:hover {
            color: var(--orb-accent);
        }

        .copyright-text {
            text-align: center;
            margin-top: 14px;
            font-size: 11px;
            color: var(--text-muted);
        }

        .back-mobile-page {
            text-align: center;
            margin-top: 14px;
        }

        .back-mobile-page a {
            color: var(--orb-primary);
            font-size: 13px;
            font-weight: 700;
        }

        .text-danger {
            display: block;
            margin-top: 6px;
            color: #dc2626;
            font-size: 12px;
        }

        /* =========================================================
           RESPONSIVE VISIBILITY
        ========================================================= */
        @media (min-width: 992px) {

            .mobile-intro-page,
            .mobile-login-page {
                display: none !important;
            }
        }

        @media (max-width: 991.98px) {
            .desktop-page {
                display: none !important;
            }
        }

        /* 992px - 1100px laptop/tablet landscape */
        @media (min-width: 992px) and (max-width: 1100px) {
            .desktop-layout {
                grid-template-columns: minmax(0, 1fr) minmax(350px, .9fr);
                border-radius: 24px;
            }

            .desktop-left {
                padding: 18px;
            }

            .desktop-right {
                padding: 18px;
            }

            .desk-info-grid {
                grid-template-columns: 1fr;
            }

            .desk-info-card {
                display: flex;
                align-items: flex-start;
                gap: 12px;
            }

            .desk-info-card i {
                margin-bottom: 0;
                margin-top: 2px;
            }

            .desktop-title {
                font-size: 24px;
            }

            .desktop-desc {
                font-size: 12px;
            }

            .desk-section-head h5 {
                font-size: 14px;
            }

            .desk-point-text h6 {
                font-size: 12px;
            }

            .desk-point-text p {
                font-size: 10.5px;
            }

            .login-panel {
                max-width: 360px;
            }
        }

        /* Large desktop */
        @media (min-width: 1400px) {
            .desktop-shell {
                max-width: 1340px;
            }

            .desktop-layout {
                min-height: 790px;
            }

            .desktop-title {
                font-size: 38px;
            }
        }

        /* Short laptop height fix */
        @media (min-width: 992px) and (max-height: 720px) {
            .desktop-layout {
                min-height: calc(100vh - 20px);
            }

            .desk-hero-card {
                padding: 14px 16px;
            }

            .desk-logo-box {
                width: 130px;
                min-height: 50px;
            }

            .desktop-title {
                font-size: 23px;
                margin-bottom: 6px;
            }

            .desktop-desc {
                font-size: 12px;
                line-height: 1.45;
            }

            .desk-info-card {
                padding: 10px 12px;
            }

            .desk-info-card p {
                display: none;
            }

            .desk-section-card {
                padding: 12px 14px;
            }

            .desk-point {
                padding: 8px 0;
            }

            .desk-point-icon {
                width: 30px;
                height: 30px;
                min-width: 30px;
            }

            .desk-point-text p {
                line-height: 1.4;
            }

            .desktop-left-bottom {
                margin-top: 8px;
            }

            .form-control,
            .btn-login {
                height: 48px;
            }

            .input-group-custom {
                margin-bottom: 12px;
            }

            .signin-footer {
                margin-top: 12px;
                padding-top: 12px;
            }
        }

        /* =========================================================
           Mobile width tuning
        ========================================================= */
        @media (max-width: 420px) {
            .mobile-top-content {
                padding: 28px 12px 0;
            }

            .mobile-main-title {
                max-width: 310px;
                margin: 22px auto 12px;
            }

            .mobile-main-desc {
                line-height: 1.7;
                max-width: 305px;
                margin-bottom: 18px;
            }

            .mobile-scroll-area {
                padding-bottom: 228px;
            }

            .mobile-phone-wrap {
                max-width: 320px;
            }

            .phone-back-device {
                border-radius: 34px;
                padding: 12px;
            }

            .phone-screen {
                border-radius: 24px;
                min-height: 565px;
            }

            .phone-back-device::before {
                top: 66px;
                height: 24px;
            }

            .phone-back-device::after {
                top: 92px;
                height: 34px;
            }

            .mock-task-list {
                padding: 0 0 160px;
            }

            .mock-overlay-card {
                width: min(112%, 350px);
                bottom: 95px;
                border-radius: 22px;
            }

            .mock-card-head {
                grid-template-columns: 84px 1fr 84px;
                font-size: 11px;
            }

            .mock-card-row {
                grid-template-columns: 16px 1fr 56px;
            }

            .mobile-bottom-panel {
                padding: 28px 14px 12px;
                min-height: 116px;
            }

            .mobile-round-bg {
                height: 50px;
            }

            .mobile-cta-btn {
                height: 56px;
                font-size: 16px;
            }

            .mobile-continue-link {
                font-size: 16px;
                margin-top: 14px;
            }

            .mobile-login-page {
                padding: 12px;
            }

            .mobile-login-card {
                padding: 20px 14px;
                border-radius: 20px;
            }
        }

        @media (max-width: 360px) {
            .mobile-top-content {
                padding: 24px 10px 0;
            }

            .mobile-scroll-area {
                padding-bottom: 220px;
            }

            .mobile-phone-wrap {
                max-width: 290px;
            }

            .phone-back-device {
                padding: 10px;
                border-radius: 28px;
            }

            .phone-screen {
                min-height: 500px;
                border-radius: 20px;
            }

            .mock-header {
                padding: 0 12px 10px;
            }

            .mock-list-strip {
                padding: 10px 12px;
            }

            .mock-task-row {
                padding: 10px 12px;
                gap: 8px;
            }

            .mock-task-text {
                font-size: 11px;
            }

            .mock-task-date {
                font-size: 10px;
            }

            .mock-overlay-card {
                bottom: 86px;
                padding: 6px;
            }

            .mobile-bottom-panel {
                min-height: 108px;
                padding: 24px 12px 10px;
            }

            .mobile-cta-btn {
                height: 52px;
                font-size: 15px;
            }

            .mobile-continue-link {
                font-size: 15px;
            }
        }

        @media (max-height: 760px) and (max-width: 991.98px) {
            .mobile-scroll-area {
                padding-bottom: 185px;
            }

            .mobile-top-content {
                padding-top: 22px;
            }

            .mobile-main-title {
                max-width: 290px;
                margin: 18px auto 10px;
            }

            .mobile-main-desc {
                line-height: 1.55;
                max-width: 286px;
                margin-bottom: 14px;
            }

            .playstore-badge img {
                height: 48px;
            }

            .mobile-phone-wrap {
                max-width: 285px;
            }

            .phone-back-device {
                padding: 11px;
                border-radius: 30px;
            }

            .phone-screen {
                min-height: 470px;
                border-radius: 22px;
            }

            .phone-status-bar {
                padding: 10px 14px 5px;
            }

            .phone-notch {
                margin-bottom: 10px;
            }

            .mock-header {
                padding: 0 14px 10px;
            }

            .mock-list-strip {
                padding: 12px 14px;
            }

            .mock-task-row {
                padding: 12px 14px;
            }

            .mock-task-list {
                padding-bottom: 138px;
            }

            .mock-overlay-card {
                bottom: 78px;
                max-width: 306px;
            }

            .mobile-bottom-panel {
                padding: 24px 12px 10px;
                min-height: 104px;
            }

            .mobile-round-bg {
                height: 46px;
            }

            .mobile-cta-btn {
                height: 50px;
                font-size: 15px;
            }

            .mobile-continue-link {
                font-size: 15px;
                margin-top: 12px;
            }
        }

        /* =========================================================
           MOCK APP COMPONENT STYLES
        ========================================================= */
        .mock-app-wrap {
            display: flex;
            flex-direction: column;
            width: 100%;
            flex-grow: 1;
            background: #f6f8fd;
            position: relative;
        }

        .mock-app-top {
            background: linear-gradient(135deg, #7c3aed 0%, #d946ef 55%, #f43f5e 100%);
            padding: 4px 10px 22px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            position: relative;
        }

        .mock-app-top .phone-status-bar {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 14px 4px;
            font-size: 11px;
            color: #ffffff;
            font-weight: 700;
            width: 100%;
        }

        .mock-app-top .phone-status-right {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #ffffff;
        }

        .mock-app-top .phone-notch {
            position: absolute;
            left: 50%;
            top: 7px;
            transform: translateX(-50%);
            width: 72px;
            height: 15px;
            background: #000000;
            border-radius: 99px;
            margin: 0;
            z-index: 10;
        }

        .mock-app-profile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .mock-profile-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mock-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.6);
            object-fit: cover;
        }

        .mock-profile-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            text-align: left;
        }

        .mock-user-name {
            color: #ffffff;
            font-weight: 800;
            font-size: 13px;
        }

        .mock-user-role {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            font-size: 10px;
        }

        .mock-user-id {
            color: rgba(255, 255, 255, 0.7);
            font-size: 9px;
            font-weight: 500;
        }

        .mock-bell-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 13px;
            position: relative;
            cursor: pointer;
        }

        .mock-bell-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #f59e0b;
            border: 1px solid #7c3aed;
        }

        .mock-app-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
            width: 100%;
        }

        .mock-stat-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 6px 3px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            min-height: 74px;
            text-align: center;
            color: #ffffff;
            backdrop-filter: blur(4px);
        }

        .mock-stat-card>i {
            font-size: 12px;
            opacity: 0.9;
        }

        .mock-stat-value {
            font-size: 10px;
            font-weight: 800;
            margin: 2px 0;
        }

        .mock-stat-label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            opacity: 0.9;
        }

        .mock-stat-sub {
            font-size: 6px;
            opacity: 0.7;
            font-weight: 500;
        }

        .mock-umbrella-ring-wrap {
            position: relative;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mock-umbrella-ring-wrap i {
            font-size: 9px;
            position: absolute;
            top: -2px;
            opacity: 0.9;
        }

        .mock-ring-circle {
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-top-color: #10b981;
            border-right-color: #10b981;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 8px;
        }

        .mock-ring-circle span {
            font-size: 8px;
            font-weight: 800;
            line-height: 1;
        }

        .mock-ring-circle small {
            font-size: 4px;
            font-weight: 500;
            line-height: 1;
            opacity: 0.8;
        }

        .mock-app-bottom-sheet {
            background: #ffffff;
            border-top-left-radius: 24px;
            border-top-right-radius: 24px;
            margin-top: -14px;
            padding: 10px 8px 50px;
            flex-grow: 1;
            position: relative;
            z-index: 5;
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
            max-height: calc(100% - 130px);
        }

        .mock-grab-handle {
            width: 30px;
            height: 3px;
            border-radius: 99px;
            background: #cbd5e1;
            margin: 0 auto 8px;
        }

        .mock-sheet-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 10px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 10px rgba(148, 163, 184, 0.03);
        }

        .mock-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .mock-card-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 12px;
        }

        .mock-card-icon.attendance-icon {
            background: linear-gradient(135deg, #ec4899, #8b5cf6);
        }

        .mock-card-icon.week-icon {
            background: linear-gradient(135deg, #db2777, #f43f5e);
        }

        .mock-card-title-wrap {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            margin-left: 8px;
            text-align: left;
        }

        .mock-card-title-wrap h6 {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
        }

        .mock-card-title-wrap span {
            font-size: 9px;
            color: #64748b;
            font-weight: 500;
        }

        .mock-status-badge {
            font-size: 8px;
            font-weight: 700;
            color: #6366f1;
            background: #f5f3ff;
            border: 1px solid rgba(99, 102, 241, 0.25);
            padding: 2px 6px;
            border-radius: 99px;
        }

        .mock-info-alert {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f1f0fb;
            color: #6366f1;
            padding: 6px 8px;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 10px;
            font-weight: 600;
            text-align: left;
        }

        .mock-work-modes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-top: 8px;
        }

        .mock-mode-btn {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 10px;
            padding: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            text-align: left;
        }

        .mock-mode-btn i {
            font-size: 12px;
            color: #94a3b8;
        }

        .mock-mode-btn.active {
            border-color: #8b5cf6;
            background: #f5f3ff;
        }

        .mock-mode-btn.active i {
            color: #8b5cf6;
        }

        .mock-mode-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .mock-mode-text strong {
            font-size: 10px;
            font-weight: 800;
            color: #334155;
        }

        .mock-mode-btn.active .mock-mode-text strong {
            color: #8b5cf6;
        }

        .mock-mode-text span {
            font-size: 7px;
            color: #64748b;
            font-weight: 500;
        }

        .mock-punch-btn {
            width: 100%;
            height: 36px;
            border-radius: 18px;
            background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-top: 8px;
            box-shadow: 0 3px 8px rgba(99, 102, 241, 0.15);
        }

        .mock-chevron-circle {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            font-size: 9px;
        }

        .mock-days-row {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            width: 100%;
        }

        .mock-day-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
        }

        .mock-day-lbl {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
        }

        .mock-day-num {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 800;
            color: #334155;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .mock-day-num.absent {
            border-color: #fca5a5;
            color: #ef4444;
            background: #fef2f2;
        }

        .mock-day-num.present {
            background: #6366f1;
            color: #ffffff;
            border-color: #6366f1;
        }

        .mock-legend-row {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
            font-size: 8px;
            color: #64748b;
            font-weight: 600;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .legend-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            display: inline-block;
        }

        .legend-dot.present {
            background: #10b981;
        }

        .legend-dot.absent {
            background: #ef4444;
        }

        .legend-dot.halfday {
            background: #f59e0b;
        }

        .legend-dot.future {
            background: #94a3b8;
        }

        .mock-app-nav {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-around;
            align-items: flex-start;
            padding-top: 6px;
            z-index: 10;
        }

        .mock-home-indicator {
            position: absolute;
            bottom: 3px;
            left: 50%;
            transform: translateX(-50%);
            width: 36px;
            height: 3px;
            border-radius: 99px;
            background: #cbd5e1;
        }

        .mock-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #94a3b8;
            gap: 2px;
        }

        .mock-nav-item i {
            font-size: 12px;
        }

        .mock-nav-item span {
            font-size: 7px;
            font-weight: 600;
        }

        .mock-nav-item.active {
            color: #6366f1;
        }
    </style>
</head>

<body>
    <div class="page-wrap">

        <div class="mobile-intro-page" @if($showWebLogin) style="display: none !important;" @endif>
            <div class="mobile-scroll-area">
                <div class="mobile-top-content">
                    <div class="mobile-brand-block">
                        <img src="{{ $branding['logo_url'] ?? asset('images/Picsart_26-04-02_12-19-10-396.png') }}" alt="{{ $branding['company_name'] ?? 'OrboOne Logo' }}">
                    </div>

                    <h1 class="mobile-main-title">
                        {{ $branding['company_name'] ?? config('app.name', 'OrboOne HRMS') }} is best experienced using our mobile app.
                    </h1>

                    <p class="mobile-main-desc">
                        Our HRMS mobile app gives employees faster access to attendance, leave, tasks, and daily office updates on smaller screens.
                    </p>

                    <a href="{{ route('mobile-app.download-latest') }}" class="playstore-badge" aria-label="Download {{ $branding['company_name'] ?? config('app.name', 'OrboOne HRMS') }} App">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Download {{ $branding['company_name'] ?? config('app.name', 'OrboOne HRMS') }} App">
                    </a>

                    <div class="mobile-phone-wrap">
                        <div class="mobile-phone-shell">
                            <div class="phone-back-device">
                                <div class="phone-screen">
                                    <div class="mock-app-wrap">
                                        <!-- Top Profile & Stats Section -->
                                        <div class="mock-app-top">
                                            <div class="phone-status-bar">
                                                <span>9:41</span>
                                                <div class="phone-notch"></div>
                                                <div class="phone-status-right">
                                                    <i class="fa-solid fa-signal"></i>
                                                    <i class="fa-solid fa-wifi"></i>
                                                    <i class="fa-solid fa-battery-three-quarters"></i>
                                                </div>
                                            </div>
                                            <div class="mock-app-profile" style="margin-top: 4px;">
                                                <div class="mock-profile-left">
                                                    <img src="{{ asset('images/profile.png') }}" class="mock-avatar" alt="Avatar">
                                                    <div class="mock-profile-info">
                                                        <span class="mock-user-name">Employee Name</span>
                                                        <span class="mock-user-role">Designation</span>
                                                        <span class="mock-user-id">Employee Code</span>
                                                    </div>
                                                </div>
                                                <div class="mock-bell-btn">
                                                    <i class="fa-regular fa-bell"></i>
                                                    <span class="mock-bell-dot"></span>
                                                </div>
                                            </div>

                                            <div class="mock-app-stats">
                                                <div class="mock-stat-card">
                                                    <i class="fa-regular fa-calendar-check"></i>
                                                    <div class="mock-stat-value">-- Days</div>
                                                    <div class="mock-stat-label">Attendance</div>
                                                    <div class="mock-stat-sub">-- Present</div>
                                                </div>
                                                <div class="mock-stat-card">
                                                    <div class="mock-umbrella-ring-wrap">
                                                        <i class="fa-solid fa-umbrella"></i>
                                                        <div class="mock-ring-circle">
                                                            <span>1</span>
                                                            <small>Left</small>
                                                        </div>
                                                    </div>
                                                    <div class="mock-stat-label">Leave</div>
                                                    <div class="mock-stat-sub">0 Used</div>
                                                </div>
                                                <div class="mock-stat-card">
                                                    <i class="fa-regular fa-clock"></i>
                                                    <div class="mock-stat-value">-- Logged</div>
                                                    <div class="mock-stat-label">Hours</div>
                                                    <div class="mock-stat-sub">This Month</div>
                                                </div>
                                                <div class="mock-stat-card">
                                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                                    <div class="mock-stat-value">0 Avail</div>
                                                    <div class="mock-stat-label">Comp Off</div>
                                                    <div class="mock-stat-sub">0 Expiring</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bottom Sheet Section -->
                                        <div class="mock-app-bottom-sheet">
                                            <div class="mock-grab-handle"></div>

                                            <!-- Card 1: Today Attendance -->
                                            <div class="mock-sheet-card">
                                                <div class="mock-card-header">
                                                    <div class="mock-card-icon attendance-icon">
                                                        <i class="fa-regular fa-calendar"></i>
                                                    </div>
                                                    <div class="mock-card-title-wrap">
                                                        <h6>Today Attendance</h6>
                                                        <span>9 June 2026</span>
                                                    </div>
                                                    <span class="mock-status-badge">Not In</span>
                                                </div>

                                                <div class="mock-info-alert">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                    <span>Punch-in is available.</span>
                                                </div>

                                                <div class="mock-work-modes">
                                                    <div class="mock-mode-btn active">
                                                        <i class="fa-solid fa-briefcase"></i>
                                                        <div class="mock-mode-text">
                                                            <strong>WFO</strong>
                                                            <span>Office location required</span>
                                                        </div>
                                                    </div>
                                                    <div class="mock-mode-btn">
                                                        <i class="fa-solid fa-house-laptop"></i>
                                                        <div class="mock-mode-text">
                                                            <strong>WFH</strong>
                                                            <span>Remote punch allowed</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button class="mock-punch-btn">
                                                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Punch In
                                                </button>
                                            </div>

                                            <!-- Card 2: This Week -->
                                            <div class="mock-sheet-card">
                                                <div class="mock-card-header">
                                                    <div class="mock-card-icon week-icon">
                                                        <i class="fa-regular fa-calendar-days"></i>
                                                    </div>
                                                    <div class="mock-card-title-wrap">
                                                        <h6>This Week</h6>
                                                        <span>Quick weekly view</span>
                                                    </div>
                                                    <div class="mock-chevron-circle">
                                                        <i class="fa-solid fa-chevron-down"></i>
                                                    </div>
                                                </div>

                                                <div class="mock-days-row">
                                                    <div class="mock-day-col">
                                                        <span class="mock-day-lbl">M</span>
                                                        <div class="mock-day-num absent">8</div>
                                                    </div>
                                                    <div class="mock-day-col">
                                                        <span class="mock-day-lbl">T</span>
                                                        <div class="mock-day-num present">9</div>
                                                    </div>
                                                    <div class="mock-day-col">
                                                        <span class="mock-day-lbl">W</span>
                                                        <div class="mock-day-num">10</div>
                                                    </div>
                                                    <div class="mock-day-col">
                                                        <span class="mock-day-lbl">T</span>
                                                        <div class="mock-day-num">11</div>
                                                    </div>
                                                    <div class="mock-day-col">
                                                        <span class="mock-day-lbl">F</span>
                                                        <div class="mock-day-num">12</div>
                                                    </div>
                                                    <div class="mock-day-col">
                                                        <span class="mock-day-lbl">S</span>
                                                        <div class="mock-day-num">13</div>
                                                    </div>
                                                    <div class="mock-day-col">
                                                        <span class="mock-day-lbl">S</span>
                                                        <div class="mock-day-num">14</div>
                                                    </div>
                                                </div>

                                                <div class="mock-legend-row">
                                                    <span class="legend-item"><span class="legend-dot present"></span>Present</span>
                                                    <span class="legend-item"><span class="legend-dot absent"></span>Absent</span>
                                                    <span class="legend-item"><span class="legend-dot halfday"></span>Half Day</span>
                                                    <span class="legend-item"><span class="legend-dot future"></span>Off/Future</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Navigation Bar -->
                                        <div class="mock-app-nav">
                                            <div class="mock-nav-item active">
                                                <i class="fa-solid fa-table-cells-large"></i>
                                                <span>Home</span>
                                            </div>
                                            <div class="mock-nav-item">
                                                <i class="fa-regular fa-calendar-days"></i>
                                                <span>Attendance</span>
                                            </div>
                                            <div class="mock-nav-item">
                                                <i class="fa-regular fa-calendar-check"></i>
                                                <span>Leave</span>
                                            </div>
                                            <div class="mock-nav-item">
                                                <i class="fa-regular fa-user"></i>
                                                <span>Profile</span>
                                            </div>
                                            <div class="mock-home-indicator"></div>
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

                    <a href="{{ route('mobile-app.download-latest') }}" class="mobile-cta-btn">
                        Download APK
                    </a>

                    <a href="{{ url('login?continue=1') }}" class="mobile-continue-link">
                        Continue on web anyway
                    </a>
                </div>
            </div>
        </div>

        <div class="mobile-login-page" @if(!$showWebLogin) style="display: none !important;" @endif>
            <div class="mobile-login-card">
                <div class="login-panel">

                    <div class="login-logo-wrap">
                        <div class="login-logo">
                            <img src="{{ $branding['logo_url'] ?? asset('images/Picsart_26-04-02_12-19-10-396.png') }}" alt="{{ $branding['company_name'] ?? 'OrboOne HRMS Logo' }}">
                        </div>
                    </div>

                    <!-- <div class="login-mini-badge-wrap">
                        <div class="login-mini-badge">
                            <i class="fa-solid fa-lock"></i>
                            Secure Employee Login
                        </div>
                    </div> -->

                    <h2 class="login-title">Welcome back</h2>
                    <p class="login-subtitle">
                        Sign in to continue to your employee dashboard.
                    </p>

                    <div class="mobile-apk-download">
                        <div class="mobile-apk-title">Download {{ $branding['company_name'] ?? config('app.name', 'OrboOne HRMS') }} App</div>
                        <div class="mobile-apk-text">Get the latest secure HRMS Android app.</div>
                        <a href="{{ route('mobile-app.download-latest') }}" class="mobile-apk-btn">
                            <i class="fa-solid fa-download"></i>
                            Download APK
                        </a>
                    </div>

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
                                    required>
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
                                    required>
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
                            Sign In
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

        <div class="desktop-page">
            <div class="desktop-shell">
                <div class="desktop-layout">

                    <div class="desktop-left">
                        <div class="desktop-left-top">
                            <!-- Logo and System Name -->
                            <div class="desk-logo-row">
                                <div class="desk-logo-box">
                                    <img src="{{ $branding['logo_url'] ?? asset('images/Picsart_26-04-02_12-19-10-396.png') }}" alt="{{ $branding['company_name'] ?? 'Logo' }}">
                                </div>
                                <!-- <div class="desk-logo-text">
                                    <h3 class="desk-system-title">HRMS</h3>
                                    <span class="desk-system-sub">HUMAN RESOURCE MANAGEMENT SYSTEM</span>
                                </div> -->
                            </div>

                            <!-- Headline and Description -->
                            <div class="desk-hero-content">
                                <h2 class="desktop-title">
                                    Empowering People.<br>Elevating <span class="text-primary-grad">Performance.</span>
                                </h2>
                                <p class="desktop-desc">
                                    {{ $company->company_name ?? $branding['company_name'] ?? 'OrboOne' }} HRMS simplifies HR processes and helps organizations build a better workplace.
                                </p>
                                <div class="desk-indicator-line"></div>
                            </div>
                        </div>

                        <!-- Vector Illustration -->
                        <div class="desk-vector-wrap">
                            <img src="{{ asset('images/hrms_vector.png') }}" alt="HRMS Vector Illustration" class="desk-vector-img">
                        </div>

                        <!-- Curve Background Wave -->
                        <div class="desktop-left-bottom-wave">
                            <svg viewBox="0 0 500 150" preserveAspectRatio="none" style="height: 100%; width: 100%;">
                                <path d="M0.00,49.98 C150.00,150.00 349.20,-49.98 500.00,49.98 L500.00,150.00 L0.00,150.00 Z" style="stroke: none; fill: url(#wave-grad);"></path>
                                <defs>
                                    <linearGradient id="wave-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#6d28d9;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#db2777;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>

                        <!-- Bottom Wave Features -->
                        <div class="desk-features-row">
                            <div class="desk-feature-item">
                                <div class="desk-feature-icon">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div class="desk-feature-title">Secure</div>
                                <div class="desk-feature-desc">Your data is safe<br>with us</div>
                            </div>
                            <div class="desk-feature-item">
                                <div class="desk-feature-icon">
                                    <i class="fa-regular fa-clock"></i>
                                </div>
                                <div class="desk-feature-title">Smart</div>
                                <div class="desk-feature-desc">Streamline HR<br>processes</div>
                            </div>
                            <div class="desk-feature-item">
                                <div class="desk-feature-icon">
                                    <i class="fa-solid fa-chart-simple"></i>
                                </div>
                                <div class="desk-feature-title">Scalable</div>
                                <div class="desk-feature-desc">Built for teams<br>of all sizes</div>
                            </div>
                        </div>
                    </div>

                    <div class="desktop-right">
                        <div class="login-card-wrapper">
                            <div class="login-panel">
                                <!-- Card Header -->
                                <h2 class="login-title">Welcome Back! 👋</h2>
                                <p class="login-subtitle">Sign in to your HRMS account</p>

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

                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <div class="input-group-custom">
                                            <span class="input-icon">
                                                <i class="fa-regular fa-user"></i>
                                            </span>
                                            <input
                                                type="email"
                                                name="email"
                                                class="form-control"
                                                placeholder="Enter your username"
                                                value="{{ old('email') }}"
                                                required>
                                        </div>
                                        @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label mb-0">Password</label>
                                            <a href="{{ route('password.forgot') }}" class="helper-link">Forgot Password?</a>
                                        </div>
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
                                                required>
                                            <span class="eye-icon" id="togglePasswordDesktop">
                                                <i class="fa-solid fa-eye"></i>
                                            </span>
                                        </div>
                                        @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <div class="form-check text-start">
                                            <input class="form-check-input" type="checkbox" name="remember" id="rememberDesktop">
                                            <label class="form-check-label" for="rememberDesktop" style="font-size: 13px; color: #475569; font-weight: 500;">
                                                Remember me
                                            </label>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-login-new">
                                        Login
                                    </button>
                                </form>

                                <!-- <div class="separator-text">
                                    <span>or</span>
                                </div> -->

                                <!-- <button class="btn-sso-login" onclick="alert('SSO Login integration mock demo')">
                                    <i class="fa-regular fa-circle-check" style="margin-right: 6px;"></i> SSO Login
                                </button> -->

                                <div class="signin-footer-new">
                                    Don't have an account? <a href="#" onclick="alert('Please contact your HR administrator to register on the platform.')">Contact HR Administrator</a>
                                </div>
                            </div>
                        </div>

                        <!-- Copyright Footer outside the card -->
                        <div class="desktop-copyright-footer">
                            <i class="fa-solid fa-shield-halved" style="margin-right: 6px; color: #6d28d9;"></i>
                            <span>© {{ date('Y') }} {{ $company->company_name ?? $branding['company_name'] ?? 'OrboOne' }}. All rights reserved.</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @include('auth.partials.forgot-password-modal')
</body>

</html>