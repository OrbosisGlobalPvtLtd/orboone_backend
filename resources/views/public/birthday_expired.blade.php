<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birthday Wish Card Expired | Orbosis Global Pvt. Ltd.</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #0f0c2a;
            background: radial-gradient(circle at center, #2e1065 0%, #0f0c2a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            color: #FFFFFF;
        }

        .expired-card {
            max-width: 440px;
            width: 100%;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.95) 0%, rgba(124, 58, 237, 0.95) 100%);
            border: 1.5px solid rgba(255, 255, 255, 0.28);
            border-radius: 32px;
            padding: 40px 28px 34px;
            text-align: center;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .company-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 213, 79, 0.22);
            border: 1px solid rgba(255, 213, 79, 0.65);
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.8px;
            color: #FFD54F;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .icon-box {
            width: 86px;
            height: 86px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            font-size: 38px;
            color: #FFD54F;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        }

        .title {
            font-size: 26px;
            font-weight: 900;
            margin-bottom: 12px;
            color: #FFFFFF;
            letter-spacing: -0.4px;
        }

        .desc {
            font-size: 14px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
            font-weight: 500;
        }

        .btn-visit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px 20px;
            background: #FFFFFF;
            color: #3A00B5;
            font-size: 15px;
            font-weight: 800;
            border-radius: 18px;
            text-decoration: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-visit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.35);
            color: #3A00B5;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="expired-card">
        <div class="company-badge">
            ⚡ ORBOSIS GLOBAL PVT. LTD.
        </div>
        
        <div class="icon-box">
            ⏳
        </div>
        
        <h1 class="title">Wish Card Link Expired</h1>
        
        <p class="desc">
            This birthday celebration link was valid for <strong>24 hours</strong> on the employee's birthday and has now expired.<br><br>
            Thank you for celebrating team milestones with <strong>Orbosis Global Pvt. Ltd.</strong>! 🚀
        </p>

        <a href="{{ $companyWebsite ?? 'https://orbosis.com' }}" class="btn-visit" target="_blank">
            Visit Orbosis Global 🚀
        </a>
    </div>
</body>
</html>
