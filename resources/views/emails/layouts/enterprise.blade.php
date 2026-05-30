<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $mailTitle ?? 'OrboOne HRMS' }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f3f5f9; font-family: Arial, Helvetica, sans-serif; color: #101828; }
        .wrap { width: 100%; padding: 20px 10px; box-sizing: border-box; }
        .card { max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 18px; overflow: hidden; border: 1px solid #e4e7ec; }
        .hero { padding: 24px 18px; text-align: center; background: linear-gradient(135deg, #4B00E8 0%, #FF5252 100%); color: #ffffff; }
        .hero h1 { margin: 0; font-size: 24px; }
        .hero p { margin: 8px 0 0; font-size: 13px; opacity: .95; }
        .body { padding: 24px 20px; }
        .footer { border-top: 1px solid #e4e7ec; background: #f8fafc; padding: 16px 20px; text-align: center; }
        .footer p { margin: 4px 0; color: #667085; font-size: 12px; }
        .badge { display: inline-block; background: #e6f0ff; color: #0b5fff; border: 1px solid #c8dcff; border-radius: 999px; padding: 6px 12px; font-size: 11px; font-weight: 700; letter-spacing: .3px; text-transform: uppercase; margin-bottom: 14px; }
        table.meta { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.meta td { border-bottom: 1px solid #eef2f6; padding: 10px 0; vertical-align: top; font-size: 14px; }
        table.meta td.label { width: 36%; color: #667085; font-weight: 700; padding-right: 10px; }
        table.meta td.value { color: #101828; font-weight: 600; word-break: break-word; }
        .btn-wrap { margin-top: 18px; text-align: center; }
        .btn { display: inline-block; background: #0b5fff; color: #fff !important; text-decoration: none; padding: 10px 16px; border-radius: 999px; font-weight: 700; font-size: 13px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="hero">
            <h1>OrboOne HRMS</h1>
            <p>Enterprise Human Resource Management System</p>
        </div>
        <div class="body">
            @yield('content')
        </div>
        <div class="footer">
            <p><strong>Orbosis Global Pvt Ltd</strong></p>
            <p>{{ config('app.url') }}</p>
            <p>Support: {{ config('hrms.emails.support') ?: config('mail.from.address') }}</p>
            <p>&copy; {{ date('Y') }} Orbosis Global Pvt Ltd. All rights reserved.</p>
        </div>
    </div>
</div>
</body>
</html>
