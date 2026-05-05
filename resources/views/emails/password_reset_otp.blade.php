<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
</head>

<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div
        style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">
        <div style="padding:22px;background:#4B00E8;color:#ffffff;text-align:center;">
            <h1 style="margin:0;font-size:22px;">Orbosis HRMS</h1>
        </div>
        <div style="padding:26px;">
            <h2 style="margin:0 0 12px;font-size:20px;color:#111827;">Password Reset OTP</h2>
            <p style="margin:0 0 16px;color:#4b5563;font-size:14px;">Use this OTP to reset your password. It expires in
                10 minutes.</p>
            <div
                style="font-size:30px;letter-spacing:8px;font-weight:800;color:#4B00E8;background:#F4F2FF;border-radius:10px;padding:16px;text-align:center;">
                {{ $otp }}
            </div>
            <p style="margin:18px 0 0;color:#6b7280;font-size:13px;">If you did not request this, you can ignore this
                email.</p>
        </div>
    </div>
</body>

</html>
