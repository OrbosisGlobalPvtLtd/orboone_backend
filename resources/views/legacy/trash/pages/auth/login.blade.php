<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Orbosis HRMS</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --orb-primary: #4b00e8;
            --orb-secondary: #8600ee;
            --orb-accent: #d400d5;
            --orb-gold: #ffb101;

            --text-dark: #0f172a;
            --text-soft: #64748b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --input-bg: #f8fafc;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(circle at top left, rgba(75, 0, 232, 0.14), transparent 28%),
                radial-gradient(circle at bottom right, rgba(255, 177, 1, 0.12), transparent 28%),
                linear-gradient(135deg, #f8faff 0%, #f3f4ff 45%, #fcf6ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            position: relative;
        }

        body::before,
        body::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
        }

        body::before {
            width: 220px;
            height: 220px;
            background: rgba(134, 0, 238, 0.10);
            top: -60px;
            left: -60px;
        }

        body::after {
            width: 240px;
            height: 240px;
            background: rgba(212, 0, 213, 0.08);
            bottom: -70px;
            right: -60px;
        }

        .login-shell {
            width: 100%;
            max-width: 1120px;
            height: calc(100vh - 32px);
            max-height: 760px;
            position: relative;
            z-index: 1;
        }

        .login-layout {
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-columns: 0.92fr 1fr;
            background: rgba(255, 255, 255, 0.60);
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.10);
            backdrop-filter: blur(18px);
        }

        /* left panel */
        .brand-panel {
            background: linear-gradient(160deg, #4b00e8 0%, #8600ee 58%, #d400d5 100%);
            color: #fff;
            padding: 28px 28px 24px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .brand-panel::before {
            content: "";
            position: absolute;
            width: 190px;
            height: 190px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            top: -55px;
            right: -55px;
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            width: 160px;
            height: 160px;
            background: rgba(255, 177, 1, 0.16);
            border-radius: 50%;
            bottom: -45px;
            left: -45px;
        }

        .brand-content,
        .brand-footer {
            position: relative;
            z-index: 2;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.14);
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .brand-title {
            font-size: 26px;
            font-weight: 800;
            line-height: 1.22;
            margin: 0 0 10px;
            letter-spacing: -0.4px;
            max-width: 380px;
        }

        .brand-desc {
            font-size: 13px;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.84);
            margin: 0 0 18px;
            max-width: 360px;
        }

        .brand-note-card {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
        }

        .brand-note-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.14);
            font-size: 15px;
        }

        .brand-note-card h6 {
            margin: 0 0 4px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        .brand-note-card p {
            margin: 0;
            font-size: 12px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.80);
        }

        .premium-download-card {
            margin-top: 16px;
            padding: 18px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0.10) 100%);
            border: 1px solid rgba(255,255,255,0.14);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
        }

        .download-card-top {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }

        .premium-download-icon {
            width: 46px;
            height: 46px;
            min-width: 46px;
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(255,255,255,0.24), rgba(255,255,255,0.10));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: 0 10px 18px rgba(0,0,0,0.10);
        }

        .download-badge {
            display: inline-flex;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.14);
            font-size: 10px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .premium-download-card h5 {
            margin: 0 0 5px;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        .premium-download-card p {
            margin: 0;
            font-size: 12px;
            line-height: 1.6;
            color: rgba(255,255,255,0.82);
        }

        .download-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .download-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
            font-weight: 600;
            color: rgba(255,255,255,0.86);
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.10);
        }

        .premium-download-btn {
            width: 100%;
            height: 50px;
            border-radius: 16px;
            padding: 0 16px 0 18px;
            background: #ffffff;
            color: var(--orb-primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 14px 28px rgba(0,0,0,0.12);
            transition: all 0.25s ease;
        }

        .premium-download-btn:hover {
            transform: translateY(-2px);
            color: var(--orb-primary);
            box-shadow: 0 18px 32px rgba(0,0,0,0.16);
        }

        .btn-download-content {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-download-arrow {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: rgba(75, 0, 232, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-footer {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.78);
            margin-top: 16px;
        }

        /* right panel */
        .form-panel {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(16px);
            padding: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-panel-inner {
            width: 100%;
            max-width: 430px;
        }

        .mobile-apk-bar {
            display: none;
        }

        .form-logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 12px;
            margin-top: 0;
        }

        .form-logo {
            display: block;
            text-align: center;
            line-height: 0;
        }

        .form-logo img {
            width: 180px;
            max-width: 100%;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(75, 0, 232, 0.10));
        }

        .form-top-label-wrap {
            display: flex;
            justify-content: center;
        }

        .form-top-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(75, 0, 232, 0.08);
            color: var(--orb-primary);
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .login-title {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.2;
            color: var(--text-dark);
            margin: 0 0 6px;
            text-align: center;
            letter-spacing: -0.4px;
        }

        .login-subtitle {
            text-align: center;
            font-size: 13px;
            color: var(--text-soft);
            line-height: 1.6;
            margin-bottom: 18px;
        }

        .alert {
            border: none;
            border-radius: 14px;
            padding: 12px 14px;
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
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 16px;
        }

        .input-group-custom .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            z-index: 2;
        }

        .form-control {
            height: 52px;
            border-radius: 15px;
            border: 1px solid var(--border);
            background: var(--input-bg);
            padding: 14px 46px 14px 46px;
            font-size: 14px;
            color: var(--text-dark);
            transition: all 0.25s ease;
            box-shadow: none;
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-control:focus {
            border-color: rgba(75, 0, 232, 0.34);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.08);
        }

        .eye-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            z-index: 3;
            width: 18px;
            text-align: center;
            transition: color 0.2s ease;
        }

        .eye-icon:hover {
            color: var(--orb-primary);
        }

        .helper-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin: 2px 0 18px;
        }

        .helper-info {
            font-size: 12px;
            color: var(--text-soft);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .helper-link {
            font-size: 12px;
            font-weight: 700;
            color: var(--orb-primary);
            text-decoration: none;
        }

        .helper-link:hover {
            color: var(--orb-accent);
        }

        .btn-login {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 50%, var(--orb-accent) 100%);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.2px;
            box-shadow: 0 14px 28px rgba(75, 0, 232, 0.18);
            transition: all 0.25s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            color: #fff;
            box-shadow: 0 18px 32px rgba(75, 0, 232, 0.24);
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
            text-decoration: none;
        }

        .signin-footer a:hover {
            color: var(--orb-accent);
        }

        .copyright-text {
            text-align: center;
            margin-top: 14px;
            color: var(--text-muted);
            font-size: 11px;
        }

        .text-danger {
            font-size: 12px;
            margin-top: 6px !important;
        }

        /* tablet and mobile only */
        @media (max-width: 768px) {
            body {
                min-height: 100vh;
                height: auto;
                overflow-x: hidden;
                overflow-y: auto;
                padding: 10px;
                align-items: flex-start;
            }

            .login-shell {
                height: auto;
                min-height: auto;
                max-width: 100%;
            }

            .login-layout {
                grid-template-columns: 1fr;
                height: auto;
                min-height: auto;
                border-radius: 24px;
                background: transparent;
                border: none;
                box-shadow: none;
                backdrop-filter: none;
            }

            .brand-panel {
                display: none;
            }

            .form-panel {
                padding: 14px;
                background: rgba(255, 255, 255, 0.96);
                border-radius: 24px;
                box-shadow: 0 18px 40px rgba(15, 23, 42, 0.10);
                border: 1px solid rgba(255, 255, 255, 0.9);
                align-items: flex-start;
            }

            .form-panel-inner {
                max-width: 100%;
            }

            .form-logo-wrap {
                margin-bottom: 8px;
            }

            .form-logo img {
                width: 148px;
            }

            .form-top-label {
                margin-bottom: 10px;
                font-size: 10px;
                padding: 7px 12px;
            }

            .login-title {
                font-size: 22px;
                margin-bottom: 4px;
            }

            .login-subtitle {
                font-size: 12px;
                margin-bottom: 16px;
            }

            .alert {
                margin-bottom: 12px;
                border-radius: 12px;
                padding: 11px 12px;
                font-size: 12px;
            }

            .form-label {
                font-size: 12px;
                margin-bottom: 7px;
            }

            .input-group-custom {
                margin-bottom: 14px;
            }

            .form-control,
            .btn-login {
                height: 48px;
                border-radius: 13px;
                font-size: 13px;
            }

            .helper-row {
                margin: 2px 0 14px;
            }

            .helper-info,
            .helper-link {
                font-size: 11px;
            }

            .signin-footer {
                margin-top: 14px;
                padding-top: 12px;
                font-size: 12px;
            }

            .copyright-text {
                margin-top: 10px;
                font-size: 10px;
            }

            .mobile-apk-bar {
                display: block;
                margin-top: 14px;
            }

            .mobile-apk-card {
                border-radius: 16px;
                padding: 12px;
                background: linear-gradient(135deg, #4b00e8 0%, #8600ee 55%, #d400d5 100%);
                color: #fff;
                box-shadow: 0 14px 28px rgba(75, 0, 232, 0.16);
            }

            .mobile-apk-top {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }

            .mobile-apk-icon {
                width: 38px;
                height: 38px;
                min-width: 38px;
                border-radius: 12px;
                background: rgba(255,255,255,0.14);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
            }

            .mobile-apk-card h6 {
                margin: 0;
                font-size: 13px;
                font-weight: 700;
            }

            .mobile-apk-card p {
                margin: 2px 0 0;
                font-size: 10px;
                line-height: 1.5;
                color: rgba(255,255,255,0.86);
            }

            .mobile-apk-btn {
                width: 100%;
                height: 42px;
                border-radius: 12px;
                background: #fff;
                color: var(--orb-primary);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                text-decoration: none;
                font-size: 12px;
                font-weight: 700;
                box-shadow: 0 8px 18px rgba(0,0,0,0.12);
            }

            .mobile-apk-btn:hover {
                color: var(--orb-primary);
            }
        }

        @media (max-width: 575.98px) {
            body {
                padding: 8px;
            }

            .form-panel {
                padding: 12px;
                border-radius: 20px;
            }

            .form-logo-wrap {
                margin-bottom: 6px;
            }

            .form-logo img {
                width: 136px;
            }

            .form-top-label {
                margin-bottom: 8px;
            }

            .login-title {
                font-size: 20px;
            }

            .login-subtitle {
                margin-bottom: 14px;
            }
        }

        /* short screen height */
        @media (max-height: 760px) and (min-width: 992px) {
            .login-shell {
                max-height: 700px;
            }

            .brand-panel,
            .form-panel {
                padding-top: 22px;
                padding-bottom: 20px;
            }

            .brand-title {
                font-size: 23px;
            }

            .brand-desc {
                margin-bottom: 14px;
                font-size: 12px;
            }

            .premium-download-card {
                margin-top: 14px;
                padding: 16px;
            }

            .form-logo img {
                width: 160px;
            }

            .form-top-label {
                margin-bottom: 10px;
            }

            .login-title {
                font-size: 24px;
            }

            .login-subtitle {
                margin-bottom: 16px;
            }

            .input-group-custom {
                margin-bottom: 14px;
            }

            .helper-row {
                margin-bottom: 14px;
            }

            .signin-footer {
                margin-top: 14px;
                padding-top: 12px;
            }

            .copyright-text {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

<div class="login-shell">
    <div class="login-layout">

        <!-- Desktop Left Panel -->
        <div class="brand-panel">
            <div class="brand-content">
                <div class="brand-badge">
                    <i class="fa-solid fa-building-shield"></i>
                    Office HRMS Portal
                </div>

                <h1 class="brand-title">Simple, secure and premium employee workspace.</h1>
                <p class="brand-desc">
                    Access your office account, manage attendance and continue your daily workflow from one clean HRMS portal.
                </p>

                <div class="brand-note-card">
                    <div class="brand-note-icon">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <div>
                        <h6>Built for office teams</h6>
                        <p>
                            Modern employee access with a smooth and professional login experience.
                        </p>
                    </div>
                </div>

                <div class="premium-download-card">
                    <div class="download-card-top">
                        <div class="premium-download-icon">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <div>
                            <span class="download-badge">Mobile Access</span>
                            <h5>Download Office App</h5>
                            <p>
                                Get the office app for quick employee access and attendance actions on mobile.
                            </p>
                        </div>
                    </div>

                    <div class="download-meta">
                        <span><i class="fa-solid fa-download"></i> Fast install</span>
                        <span><i class="fa-solid fa-shield-halved"></i> Secure access</span>
                    </div>

                    <a href="{{ url('downloads/orbosis-office.apk') }}" class="premium-download-btn" download>
                        <span class="btn-download-content">
                            <i class="fa-solid fa-download"></i>
                            Download APK
                        </span>
                        <span class="btn-download-arrow">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </a>
                </div>
            </div>

            <div class="brand-footer">
                © {{ date('Y') }} Orbosis HRMS. Office workforce platform.
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="form-panel">
            <div class="form-panel-inner">

                <div class="form-logo-wrap">
                    <div class="form-logo">
                        <img src="{{ asset('images/Picsart_26-04-02_12-19-10-396.png') }}" alt="Orbosis HRMS Logo">
                    </div>
                </div>

                <div class="form-top-label-wrap">
                    <div class="form-top-label">
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

                    <div class="mb-2">
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
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Password</label>
                        <div class="input-group-custom">
                            <span class="input-icon">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Enter your password"
                                required
                            >
                            <span class="eye-icon" id="togglePassword">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </div>
                        @error('password')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="helper-row">
                        <div class="helper-info">
                            <i class="fa-solid fa-shield-heart"></i>
                            Protected company access
                        </div>
                        <a href="#" class="helper-link">Need help?</a>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>
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

                <!-- Mobile only APK below form -->
                <div class="mobile-apk-bar">
                    <div class="mobile-apk-card">
                        <div class="mobile-apk-top">
                            <div class="mobile-apk-icon">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                            </div>
                            <div>
                                <h6>APK Download</h6>
                                <p>Download the office app for mobile employee access.</p>
                            </div>
                        </div>
                        <a href="{{ url('downloads/orbosis-office.apk') }}" class="mobile-apk-btn" download>
                            <i class="fa-solid fa-download"></i>
                            Download Office App
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    if (togglePassword && password) {
        togglePassword.addEventListener('click', function () {
            const type = password.type === 'password' ? 'text' : 'password';
            password.type = type;
            this.innerHTML = type === 'password'
                ? '<i class="fa-solid fa-eye"></i>'
                : '<i class="fa-solid fa-eye-slash"></i>';
        });
    }
</script>

</body>
</html>