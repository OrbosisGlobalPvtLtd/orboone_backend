<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $employee['name'] }}'s Birthday Celebration | Orbosis Global Pvt. Ltd.</title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ $employee['name'] }}'s Birthday Celebration 🎉">
    <meta name="description" content="Celebrating a wonderful birthday with Orbosis Global Pvt. Ltd.! Wishing {{ $employee['name'] }} a year ahead filled with joy and success. 🎂✨">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="og:title" content="{{ $employee['name'] }}'s Birthday Celebration 🎉">
    <meta property="og:description" content="Celebrating a wonderful birthday with Orbosis Global Pvt. Ltd.! Wishing {{ $employee['name'] }} a fantastic year ahead. 🎂✨">
    <meta property="og:image" content="{{ $imageUrl }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Orbosis Global Pvt. Ltd.">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $shareUrl }}">
    <meta name="twitter:title" content="{{ $employee['name'] }}'s Birthday Celebration 🎉">
    <meta name="twitter:description" content="Celebrating a wonderful birthday with Orbosis Global Pvt. Ltd.! 🎂✨">
    <meta name="twitter:image" content="{{ $imageUrl }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1A004F 0%, #3A00B5 40%, #6500F0 70%, #8600EE 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #FFFFFF;
        }

        .card-container {
            max-width: 440px;
            width: 100%;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1.5px solid rgba(255, 255, 255, 0.28);
            border-radius: 32px;
            padding: 36px 28px;
            text-align: center;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
        }

        .company-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 177, 1, 0.22);
            border: 1px solid rgba(255, 213, 79, 0.65);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #FFD54F;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .emoji-banner {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .title {
            font-size: 30px;
            font-weight: 900;
            line-height: 1.15;
            color: #FFFFFF;
            margin-bottom: 8px;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .employee-name {
            font-size: 22px;
            font-weight: 800;
            color: #FFD54F;
            margin-bottom: 4px;
        }

        .department {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 20px;
        }

        .avatar-box {
            position: relative;
            width: 110px;
            height: 110px;
            margin: 0 auto 20px;
        }

        .avatar-img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3.5px solid #FFD54F;
            box-shadow: 0 8px 24px rgba(255, 177, 1, 0.40);
        }

        .avatar-fallback {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: #4B00E8;
            border: 3.5px solid #FFD54F;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            font-weight: 900;
            color: #FFFFFF;
            box-shadow: 0 8px 24px rgba(255, 177, 1, 0.40);
        }

        .crown-badge {
            position: absolute;
            bottom: -4px;
            right: 0;
            font-size: 18px;
            background: #FFB101;
            border: 2px solid #FFFFFF;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .message {
            font-size: 14.5px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.92);
            margin-bottom: 12px;
        }

        .team-signoff {
            font-size: 16px;
            font-weight: 800;
            color: #FFD54F;
            margin-bottom: 28px;
        }

        .action-btn {
            display: inline-block;
            width: 100%;
            padding: 14px 20px;
            background: #FFFFFF;
            color: #3A00B5;
            font-size: 15px;
            font-weight: 800;
            border-radius: 16px;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.20);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.30);
        }

        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.65);
        }

        .footer a {
            color: #FFD54F;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <div class="company-badge">
            ⚡ ORBOSIS GLOBAL PVT. LTD.
        </div>

        <div class="emoji-banner">🎉 🎂 🎊</div>

        <h1 class="title">Happy Birthday</h1>
        <div class="employee-name">{{ $employee['name'] }}</div>
        @if(!empty($employee['department']))
            <div class="department">{{ $employee['department'] }}</div>
        @endif

        <div class="avatar-box">
            @if(!empty($employee['image_url']))
                <img class="avatar-img" src="{{ $employee['image_url'] }}" alt="{{ $employee['name'] }}">
            @else
                <div class="avatar-fallback">{{ strtoupper(substr($employee['name'], 0, 1)) }}</div>
            @endif
            <div class="crown-badge">👑</div>
        </div>

        <p class="message">
            Wishing you joy, success, and a fantastic year ahead from everyone at <strong>Orbosis Global Pvt. Ltd.</strong>! 🎉
        </p>

        <div class="team-signoff">— Team Orbosis Global</div>

        <a href="{{ $companyWebsite }}" class="action-btn" target="_blank">
            Visit Orbosis Global 🚀
        </a>

        <div class="footer">
            Powered by <a href="{{ $companyWebsite }}" target="_blank">Orbosis Global Pvt. Ltd.</a>
        </div>
    </div>
</body>
</html>
